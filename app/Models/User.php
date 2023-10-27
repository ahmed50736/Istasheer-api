<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Http\Traits\caseQuery;
use App\Http\Traits\UUID;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Media;
use App\Services\CaseCategoryService;
use Illuminate\Support\Carbon;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, caseQuery, UUID, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'user_type',
        'social_email',
        'DOB',
        'gender',
        'balance',
        'account_sts',
        'notes',
        'social_id',
        'phone_no',
        'verified',
        'other_info'

    ];
    protected $table = 'users';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'social_id',
        'account_sts',
        'created_at',
        'updated_at',
        'pass'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable');
    }

    public function submitcases()
    {
        return $this->hasMany(law_case::class, 'uid', 'id');
    }
    public function device()
    {
        return $this->hasMany(UserDevice::class, 'user_id', 'id');
    }

    public function flag()
    {
        return $this->hasOne(flagUser::class, 'user_id', 'id');
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class, 'user_id', 'id');
    }

    public function credentail()
    {
        return $this->hasOne(CredentialLogger::class, 'id', 'user_id');
    }

    public function attorneyData()
    {
        return $this->hasOne(asigne_case::class, 'case_id', 'id')
            ->select(DB::raw('
                count(id) as total_task
            '));
    }

    public function currentDevice()
    {
        $deviceUid = request()->hasHeader('device_uid') ? request()->header('device_uid') : null;
        return UserDevice::where('user_id', $this->id)->where('device_uid', $deviceUid)->first();
    }

    public function userScheduleCalander($caseIDS)
    {
        $hearingsData = hearings::whereIn('case_id', $caseIDS)->groupBy('date')->get()->toArray();
        $caseAction = caseAction::whereIn('case_id', $caseIDS)->groupBy('startDate')->get()->toArray();
        return array_merge($hearingsData, $caseAction);
    }

    public function notifications()
    {
        return $this->hasMany(NotificationData::class, 'user_id', 'id');
    }

    public function isAdmin()
    {
        return $this->user_type == 4;
    }

    public function isAttorney()
    {
        return $this->user_type == 2;
    }

    public function isSuperAdmin()
    {
        return $this->user_type == 1;
    }
    public function isUser()
    {
        return $this->user_type == 3;
    }

    /**
     * Get User Home Data
     * @return array
     */
    public function getHomeData()
    {
        $recentData = law_case::join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->select(DB::raw('
            law_cases.id as id,
            law_cases.order_no as order_no,
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
            law_cases.create_time as date,
            law_cases.deleted_at as deleted_at
        '));
        if ($this->isAttorney()) {

            $recentData = $recentData->join('asigne_cases', 'law_cases.id', 'asigne_cases.case_id')
                ->addSelect(DB::raw('
                    (CASE 
                    WHEN asigne_cases.asigne_status = 0 THEN "new"
                    WHEN asigne_cases.asigne_status = 2 THEN "closed"
                    ELSE "open"
                    END) AS case_status
                '))
                ->where('law_cases.case_status', 0)->where('asigne_cases.attorney_id', $this->id);
        } else if ($this->isUser()) {
            $recentData->addSelect(DB::raw('
                (CASE 
                WHEN law_cases.case_status = 2 THEN "closed"
                ELSE "open"
                END) AS case_status
            '));
            $recentData->where('law_cases.uid', $this->id);
        } else {
            $recentData->addSelect(DB::raw('
                (CASE 
                WHEN law_cases.case_status = 0 THEN "new"
                WHEN law_cases.case_status = 1 THEN "open"
                ELSE "closed"
                END) AS case_status
            '));
        }

        $recentData = $recentData->groupBy('law_cases.id')->orderBy('law_cases.create_time', 'desc')->limit(10)->get();
        $countData = $this->countCaseData();


        return [
            'open' => $countData['open'],
            'closed' => $countData['closed'],
            'new' => $countData['new'],
            'total' => $countData['total'],
            'recent_orders' =>  $recentData
        ];
    }
    public function countCaseData()
    {
        if ($this->isAttorney()) {
            $count = DB::select(DB::raw('
             SELECT
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 0 and attorney_id = "' . $this->id . '") AS new_count,
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 1 and attorney_id = "' . $this->id . '") AS open_count,
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and asigne_status = 2 and attorney_id = "' . $this->id . '") AS closed_count,
                (SELECT COUNT(id) FROM asigne_cases where deleted_at is null and attorney_id = "' . $this->id . '") AS total_count
            '));
        } else { //user count for home data
            $count = DB::select(DB::raw('
             SELECT
                (SELECT COUNT(id) FROM law_cases where deleted_at is null and (case_status = 0 OR case_status = 1) and uid = "' . $this->id . '") AS open_count,
                (SELECT COUNT(id) FROM law_cases where deleted_at is null and case_status = 2 and uid = "' . $this->id . '") AS closed_count,
                (SELECT COUNT(id) FROM law_cases where deleted_at is null and uid = "' . $this->id . '") AS total_count
            '));
        }

        return [
            'open' => $count[0]->open_count,
            'new' => $count[0]->new_count ?? 0,
            'closed' => $count[0]->closed_count,
            'total' => $count[0]->total_count
        ];
    }

    /**
     * Getting Admin home data allowed for admin only
     */
    public function getAdminHomeData()
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $homeData = DB::select(
            DB::raw('
                SELECT
                (SELECT COUNT(id) FROM law_cases where deleted_at is null) AS total_case,
                (SELECT COUNT(id) FROM users WHERE user_type = 2 and deleted_at is null) AS total_attorney,
                (SELECT COUNT(id) FROM users WHERE user_type = 3 and deleted_at is null) AS total_client,
                (SELECT COUNT(id) FROM case_actions where deleted_at is null) AS total_action,
                (SELECT COUNT(id) FROM hearings where deleted_at is null) AS total_hearings,
                (SELECT COUNT(id) FROM caseresponses WHERE file_staus = 0  and deleted_at is null) AS pending_approval,
                (SELECT COUNT(id) FROM asigne_cases WHERE due_date IS NOT NULL and deleted_at is null AND due_date < :currentDateTime) AS due_assignments
            '),
            ['currentDateTime' => $currentDateTime]
        );
        return $homeData;
    }

    /**
     * get user list
     * @param int $type
     * @param int $paginateNumber
     * @return object
     */
    public static function getUserListWithPagination(int $type, int $paginateNumber = 10): object
    {
        $domain = url('/');
        $data = self::where('user_type', $type)
            ->leftjoin('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'users.id')
                    ->where('medias.mediaable_type', '=', self::class);
            })
            ->select(DB::raw('
                users.id as id,
                users.name as name,
                users.email as email,
                CONCAT("965", users.phone_no) as phone,
                concat("' . $domain . '/", medias.photo) as image
            '))
            ->paginate($paginateNumber);
        return $data;
    }

    public static function getAttorneyListWithPagination(): object
    {
        $domain = url('/');
        $currentDateTime = date('Y-m-d H:i:s');
        $data = self::where('user_type', 2)
            ->leftjoin('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'users.id')
                    ->where('medias.mediaable_type', '=', self::class);
            })
            ->select(
                DB::raw('
                users.id as id,
                users.name as name,
                users.email as email,
                CONCAT("965", users.phone_no) as phone,
                concat("' . $domain . '/", medias.photo) as image,
                (SELECT COUNT(id) FROM asigne_cases WHERE deadline IS NOT NULL and deleted_at is null AND attorney_id = users.id) AS total_task,
                (SELECT COUNT(id) FROM caseresponses WHERE file_staus = 0 and attorney_id = users.id and deleted_at is null) AS pending_approval,
                (SELECT COUNT(id) FROM asigne_cases WHERE  deadline IS NOT NULL and attorney_id = users.id and deleted_at is null AND deadline < "' . $currentDateTime . '") AS due_assignments
                
            ')
            )
            ->paginate(15);
        return $data;
    }

    /**
     * get user cases data with pagination for other profile
     * @param string $requestType
     * @return object
     */
    public function getUserCasesAccordingToRequestTypeWithPagination($requestType = 'new'): object
    {
        $cases = law_case::leftjoin('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
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
                    WHEN payment_details.id IS NOT NULL and payment_details.extra_service_id IS NULL THEN "paid" 
                    ELSE "unpaid"
                    END) AS payment_status,
                    law_cases.create_time as created_at,
                    law_cases.deleted_at as deleted_at
                '))
            ->where('law_cases.uid', $this->id);
        switch ($requestType) {
            case 'new':
                $cases->where('law_cases.case_status', 0);
                break;
            case 'open':
                $cases->where('law_cases.case_status', 1);
                break;
            case 'closed':
                $cases->where('law_cases.case_status', 2);
                break;
        }

        $data = $cases->paginate(10);
        return $data;
    }

    /**
     * admin list 
     */
    public static function getAllAdminsWithPagination(): object
    {
        $domain = url('/');
        $data = self::where('user_type', 4)
            ->leftjoin('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'users.id')
                    ->where('medias.mediaable_type', '=', self::class);
            })
            ->select(DB::raw('
                users.id as id,
                users.name as name,
                users.email as email,
                users.phone_no as phone,
                concat("' . $domain . '/", medias.photo) as image
            '))
            ->paginate(15);
        return $data;
    }
}
