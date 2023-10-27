<?php

namespace App\Models;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Traits\UUID;
use App\Services\CaseCategoryService;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class asigne_case extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'case_id',
        'attorney_id',
        'assign_time',
        'asigne_by',
        'asigne_status',
        'submit_time',
        'deadline',
        'due_date',
    ];

    public function casedetails()
    {
        return $this->hasOne(law_case::class, 'id', 'case_id');
    }

    public function attachements()
    {
        return $this->hasMany(casefile::class, 'case_id', 'case_id');
    }

    public function hearings()
    {
        return $this->hasMany(hearings::class, 'case_id', 'id');
    }

    public function responseUpload()
    {
        return $this->hasMany(uploadResponse::class, 'case_id', 'case_id');
    }

    public function caseresponse()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $this->hasMany(caseresponse::class, 'case_id', 'id')->withTrashed();
        } else {
            return $this->hasMany(caseresponse::class, 'case_id', 'id');
        }
    }

    /**
     * getting details of case by type of cases for attorney 
     * @param string $type
     * @param string $attorneyId
     * @return mixed
     */
    public static function getAttorneyProfileCaseDetailsWithPagination(string $type, string $attorneyId = null)
    {

        $domain = url('/');
        $data = [];

        $otherData = caseresponse::join('law_cases', 'caseresponses.case_id', 'law_cases.id')
            ->join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->join('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'caseresponses.id')
                    ->where('medias.mediaable_type', '=', caseresponse::class);
            })
            ->select(DB::raw('
                medias.id as file_id,
                caseresponses.case_no as order_no,
                medias.file_name as file_name,
                law_cases.id as id,
                concat("' . $domain . '/", medias.photo) as file_url,
                (CASE 
                    WHEN law_cases.case_status = 2 THEN "closed"
                    ELSE "open"
                END) AS case_status,
                (CASE 
                    WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                    ELSE "unpaid"
                END) AS payment_status,
                case_categories.service_name as service_name,
                case_categories.service_title_english as category_name_english,
                case_categories.service_title_arabic as category_name_arabic,
                medias.created_at as upload_time
                
            '));

        if ($attorneyId != null) {
            $otherData->where('caseresponses.attorney_id', $attorneyId);
        }


        switch ($type) {
            case 'pending':
                $otherData->where('caseresponses.file_staus', 0);
                break;
            case 'over-due':
                $attorneyData = self::join('law_cases', 'asigne_cases.case_id', 'law_cases.id')
                    ->leftjoin('case_categories', 'law_cases.category_id', 'case_categories.id')
                    ->leftJoin('case_sub_categories', function ($join) {
                        $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                            ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
                    })
                    ->leftjoin('caseresponses', 'law_cases.order_no', '=', 'caseresponses.case_no')
                    ->select(DB::raw('
                        law_cases.id as id,
                        law_cases.order_no as order_no,
                        law_cases.case_type as case_type,
                        law_cases.client_name as client_name,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as service_title_english,
                        case_categories.service_title_arabic as service_title_arabic,
                        case_sub_categories.sub_category_title_english as sub_category_title_english,
                        case_sub_categories.sub_category_title_arabic as sub_category_title_arabic
                    '))

                    ->whereNotNull('asigne_cases.deadline')
                    ->where('asigne_cases.deadline', '<', date('Y-m-d H:i:s'));

                if ($attorneyId != null) {
                    $attorneyData->where('asigne_cases.attorney_id', $attorneyId);
                }

                $attorneyData = $attorneyData->groupBy('asigne_cases.id')
                    ->paginate(10);
                return $attorneyData;
                break;
            case 'rejected-service':
                $otherData->where('caseresponses.file_staus', 2);
                break;
            case 'accepted':
                $otherData->where('caseresponses.file_staus', 1);
                break;
            case 'total-task':

                $attorneyData = self::join('law_cases', 'asigne_cases.case_id', 'law_cases.id')
                    ->leftjoin('case_categories', 'law_cases.category_id', 'case_categories.id')
                    ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
                    ->leftJoin('case_sub_categories', function ($join) {
                        $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                            ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
                    })
                    ->leftjoin('caseresponses', 'law_cases.order_no', '=', 'caseresponses.case_no')
                    ->select(DB::raw('
                        law_cases.id as id,
                        law_cases.order_no as order_no,
                        law_cases.case_type as case_type,
                        law_cases.client_name as client_name,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as service_title_english,
                        case_categories.service_title_arabic as service_title_arabic,
                        case_sub_categories.sub_category_title_english as sub_category_title_english,
                        case_sub_categories.sub_category_title_arabic as sub_category_title_arabic,
                        (CASE 
                            WHEN law_cases.case_status = 2 THEN "closed"
                            ELSE "open"
                        END) AS case_status,
                        (CASE 
                            WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                            ELSE "unpaid"
                        END) AS payment_status,
                        law_cases.create_time as created_at
                    '));

                if ($attorneyId != null) {
                    $attorneyData->where('asigne_cases.attorney_id', $attorneyId);
                }

                $attorneyData = $attorneyData->groupBy('asigne_cases.id')
                    ->paginate(10);

                return $attorneyData;
                break;
        }

        $data['results'] = $otherData->groupBy('caseresponses.id')->paginate(10);

        if ($attorneyId != null) { ///count for attorney home 
            $data['count_data'] =  self::countAttorneyProfileData($attorneyId);
        }

        return $data;
    }

    public static function getOverDueAsignmentsWithPagination(string $type, string $attorneyId = null): object
    {
        $query = self::join('law_cases', 'asigne_cases.case_id', 'law_cases.id')
            ->leftjoin('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftjoin('caseresponses', 'law_cases.order_no', '=', 'caseresponses.case_no')
            ->select(DB::raw('
                        law_cases.id as id,
                        law_cases.order_no as order_no,
                        law_cases.case_type as case_type,
                        law_cases.client_name as client_name,
                        case_categories.service_name as service_name,
                        case_categories.service_title_english as service_title_english,
                        case_categories.service_title_arabic as service_title_arabic,
                        case_sub_categories.sub_category_title_english as sub_category_title_english,
                        case_sub_categories.sub_category_title_arabic as sub_category_title_arabic
                    '))
            ->whereNotNull('asigne_cases.due_date')->where('asigne_cases.due_date', '<', date('Y-m-d H:i:s'))->where('law_cases.case_status', '!=', 2);
        if ($type == 'attorney') {
            $query->where('asigne_cases.attorney_id', $attorneyId);
        }

        $overDueAsignemnets = $query->groupBy('law_cases.id')->paginate(15);
        
        return $overDueAsignemnets;
    }

    public static function countAttorneyProfileData(string $attorneyId)
    {
        $countData = DB::select(DB::raw('
                            SELECT
                                (SELECT COUNT(id) FROM caseresponses WHERE file_staus = 1 AND deleted_at IS NULL AND attorney_id = "' . $attorneyId . '") AS accepted_count,
                                (SELECT COUNT(id) FROM caseresponses WHERE file_staus = 2 AND deleted_at IS NULL AND attorney_id = "' . $attorneyId . '") AS rejected_count,
                                (SELECT COUNT(id) FROM caseresponses WHERE file_staus = 0 AND deleted_at IS NULL AND attorney_id = "' . $attorneyId . '") AS pending_count,
                                (SELECT COUNT(id) FROM asigne_cases WHERE deleted_at IS NULL AND attorney_id = "' . $attorneyId . '") as total_task
                        '));
        return $countData;
    }
}
