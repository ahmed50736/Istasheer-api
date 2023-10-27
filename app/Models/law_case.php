<?php

namespace App\Models;

use App\Http\Traits\UUID;
use App\Services\CaseCategoryService;
use App\Services\CaseService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class law_case extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'uid',
        'order_no',
        'category_id',
        'subcategory_id',
        'case_type',
        'client_name',
        'purpouse',
        'contract_term',
        'contract_ammount',
        'deadline',
        'document_details',
        'capacity',
        'against',
        'capacity2',
        'payment_id',
        'court_location',
        'expert_location',
        'chamber',
        'room',
        'automated_no',
        'court_case_no',
        'details',
        'case_status',
        'create_time',
        'case_voice',
        'other_case_price',
        'subject',
    ];

    public function category()
    {
        return $this->hasOne(CaseCategory::class, 'id', 'category_id');
    }

    public function subcategory()
    {
        return $this->hasOne(CaseSubCategory::class, 'id', 'subcategory_id');
    }

    public function attachments()
    {
        return $this->hasMany(casefile::class, 'case_id', 'id');
    }

    public function UploadResponse()
    {
        return $this->hasMany(uploadResponse::class, 'case_id', 'id');
    }

    public function responseFiles()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $this->hasMany(caseresponse::class, 'case_id', 'id')->withTrashed();
        } else {
            return $this->hasMany(caseresponse::class, 'case_id', 'id');
        }
    }

    public function hearings()
    {
        return $this->hasMany(hearings::class, 'case_id', 'id');
    }

    public function audios()
    {
        return $this->hasMany(CaseAudios::class, 'case_id', 'id');
    }

    public function attorneys()
    {
        $url = url('/');
        return $this->hasMany(asigne_case::class, 'case_id', 'id')
            ->join('users', 'asigne_cases.attorney_id', 'users.id')
            ->leftjoin('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'users.id')
                    ->where('medias.mediaable_type', '=', User::class)
                    ->whereNull('medias.deleted_at');
            })
            ->select('asigne_cases.*', 'users.id as user_id', 'users.name as name', DB::raw('concat("' . $url . '/", medias.photo) as image'))
            ->withTrashed();
    }

    public function attorneysWithoutTrashed()
    {
        return $this->hasMany(asigne_case::class, 'case_id', 'id');
    }

    public function extraServices()
    {
        return $this->hasMany(extraService::class, 'case_id', 'id');
    }


    /**
     * get user submited cases
     */
    public function userSubmittedCases($type)
    {

        $user = auth()->user();

        if ($user->isAdmin() || $user->isSuperAdmin()) { //getting data for admin
            $data = $this->getAdminCaseList($type);
        } else if ($user->isAttorney()) { //getting data for attorney
            $data = $this->getAttorneyCasesList($user->id, $type);
        } else { //getting data for users
            $data = $this->getUserCaseList($user->id, $type);
        }

        //count part 
        if ($user->isAttorney()) { //attorney part count
            $count = DB::select(DB::raw('
             SELECT
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 0 and attorney_id = "' . $user->id . '") AS new_count,
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 1 and attorney_id = "' . $user->id . '") AS open_count,
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 2 and attorney_id = "' . $user->id . '") AS closed_count
            '));
        } else if ($user->isUser()) { //user part count
            $count = DB::select(DB::raw('
             SELECT
                (SELECT COUNT(id) FROM law_cases where deleted_at is null and case_status between 0 and 1 and uid = "' . $user->id . '") AS open_count,
                (SELECT COUNT(id) FROM law_cases where deleted_at is null and case_status = 2 and uid = "' . $user->id . '") AS closed_count
            '));
        } else { //admin count for home data

            $count = DB::select(DB::raw('
             SELECT
                (SELECT COUNT(id) FROM law_cases where case_status = 0 ) AS new_count,
                (SELECT COUNT(id) FROM law_cases where case_status = 1 ) AS open_count,
                (SELECT COUNT(id) FROM law_cases where case_status = 2 ) AS closed_count
            '));
        }


        return [
            'results' => $data,
            'count' => [
                'open' => $count[0]->open_count,
                'closed' => $count[0]->closed_count,
                'new' => isset($count[0]->new_count) ? $count[0]->new_count : 0
            ]
        ];
    }

    /**
     * get attorney case list
     * @param string $attorneyId
     * @param int $type
     * @return object
     */
    public function getAttorneyCasesList(string $attorneyId, int $type): object
    {
        $query = asigne_case::join('law_cases', 'asigne_cases.case_id', 'law_cases.id')
            ->join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->select(DB::raw('
                        law_cases.id as id,
                        case_categories.id as category_id,
                        law_cases.order_no as order_no,
                        law_cases.subject as subject,
                        (CASE 
                        WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                        ELSE "unpaid"
                        END) AS payment_status,
                        (CASE 
                            WHEN asigne_cases.asigne_status = 2 THEN "closed"
                            WHEN asigne_cases.asigne_status = 0 THEN "new"
                            ELSE "open"
                        END) AS case_status,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as category_name_english,
                        case_categories.service_title_arabic as category_name_arabic,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.id
                        ELSE ""
                        END) as subcategory_id,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_english
                        ELSE null
                        END) as sub_category_title_english,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_arabic
                        ELSE null
                        END) as sub_category_title_arabic,
                        law_cases.deleted_at as deleted_at,
                        law_cases.create_time as created_at
                    '));

        //getting data for attorney
        $data = $query->where('asigne_cases.asigne_status', $type)
            ->where('asigne_cases.attorney_id', $attorneyId)
            ->groupBy('asigne_cases.id')
            ->orderBy('law_cases.create_time', 'desc')
            ->paginate(15);

        return $data;
    }

    /**
     * Get User cases
     * @param string $userId
     * @param int $type
     * @return object
     */
    public function getUserCaseList(string $userId, int $type): object
    {
        $query = self::join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->select(DB::raw('
                        law_cases.id as id,
                        case_categories.id as category_id,
                        law_cases.order_no as order_no,
                        law_cases.subject as subject,
                        (CASE 
                        WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                        ELSE "unpaid"
                        END) AS payment_status,
                        (CASE 
                            WHEN law_cases.case_status = 2 THEN "closed"
                            ELSE "open"
                        END) AS case_status,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as category_name_english,
                        case_categories.service_title_arabic as category_name_arabic,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.id
                        ELSE ""
                        END) as subcategory_id,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_english
                        ELSE null
                        END) as sub_category_title_english,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_arabic
                        ELSE null
                        END) as sub_category_title_arabic,
                        law_cases.deleted_at as deleted_at,
                        law_cases.create_time as created_at
                    '));

        $query->where('law_cases.uid', $userId);

        if ($type == 0 || $type == 1) {
            $query->whereBetween('law_cases.case_status', [0, 1])->groupBy('law_cases.id');
        } else {
            $query->where('law_cases.case_status', $type)->groupBy('law_cases.id');
        }

        $data =  $query->orderBy('law_cases.create_time', 'desc')->paginate(10);
        return $data;
    }

    /**
     * get user case list
     * @param int $type
     * @return object
     */
    public function getAdminCaseList(int $type): object
    {
        $query = self::join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->select(DB::raw('
                        law_cases.id as id,
                        case_categories.id as category_id,
                        law_cases.order_no as order_no,
                        law_cases.subject as subject,
                        (CASE 
                        WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                        ELSE "unpaid"
                        END) AS payment_status,
                        (CASE 
                            WHEN law_cases.case_status = 2 THEN "closed"
                            WHEN law_cases.case_status = 0 THEN "new"
                            ELSE "open"
                        END) AS case_status,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as category_name_english,
                        case_categories.service_title_arabic as category_name_arabic,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.id
                        ELSE ""
                        END) as subcategory_id,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_english
                        ELSE null
                        END) as sub_category_title_english,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_arabic
                        ELSE null
                        END) as sub_category_title_arabic,
                        law_cases.deleted_at as deleted_at,
                        law_cases.create_time as created_at
                    '));

        $data = $query->where('law_cases.case_status', $type)
            ->groupBy('law_cases.id')
            ->orderBy('law_cases.create_time', 'desc')
            ->withTrashed()
            ->paginate(15);

        return $data;
    }


    /**
     * advance search of cases
     * @param array $requestData
     * @return object
     */
    public static function advanceSearch(array $requestData): object
    {
        $user = auth()->user();

        $case = self::join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftjoin('asigne_cases', 'law_cases.id', '=', 'asigne_cases.case_id')
            ->select(DB::raw('
                        law_cases.id as id,
                        case_categories.id as category_id,
                        law_cases.order_no as order_no,
                        law_cases.subject as subject,
                        law_cases.automated_no as automated_no,
                        law_cases.client_name as client_name,
                        law_cases.court_case_no as case_no,
                        law_cases.against as against,
                        (CASE 
                            WHEN law_cases.case_status = 0 THEN "new"
                            WHEN law_cases.case_status = 1 THEN "open"
                            ELSE "closed"
                        END) AS case_status,
                        (CASE 
                        WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                        ELSE "unpaid"
                        END) AS payment_status,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as category_name_english,
                        case_categories.service_title_arabic as category_name_arabic,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.id
                        ELSE ""
                        END) as subcategory_id,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_english
                        ELSE null
                        END) as sub_category_title_english,
                        (CASE 
                            WHEN case_categories.id != "' . CaseCategoryService::OTHER_SERVICE_ID . '" THEN case_sub_categories.sub_category_title_arabic
                        ELSE null
                        END) as sub_category_title_arabic,
                        law_cases.create_time as created_at,
                        law_cases.deleted_at as deleted_at
                        
                    '));



        //searching by client_name 
        if (isset($requestData['client_name']) && $requestData['client_name'] != null) {
            $case->where('law_cases.client_name', 'like', '%' . $requestData['client_name'] . '%');
        }

        //searching by automated no
        if (isset($requestData['automated_no']) && $requestData['automated_no'] != null) {
            $case->where('law_cases.automated_no', $requestData['automated_no']);
        }

        //search by order number
        if (isset($requestData['order_no']) && $requestData['order_no'] != null) {
            $case->where('law_cases.order_no', 'like', '%' . $requestData['order_no'] . '%');
        }

        //searching by client name
        if (isset($requestData['client_name']) && $requestData['client_name'] != null) {
            $case->where('law_cases.client_name', 'like', '%' . $requestData['client_name'] . '%');
        }
        ///searcing by against name 
        if (isset($requestData['opposition']) && $requestData['opposition'] != null) {
            $case->where('law_cases.against', 'like', '%' . $requestData['opposition'] . '%');
        }

        //searching by court case number
        if (isset($requestData['case_no']) && $requestData['case_no'] != null) {
            $case->where('law_cases.court_case_no', $requestData['case_no']);
        }

        //filter for attorney
        if ($user->user_type == 2) {
            $case->where('asigne_cases.attorney_id', $user->id);
        }

        $data = $case->orderBy('law_cases.order_no', 'asc')
            ->groupBy('law_cases.id')
            ->withTrashed()
            ->paginate(15);
        return $data;
    }
}
