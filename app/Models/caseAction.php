<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Http\Traits\UUID;
use App\Services\CaseCategoryService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class caseAction extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'case_id',
        'actionType',
        'actionStatus',
        'created_by',
        'importance',
        'startDate',
        'endDate',
        'createTime',
        'inform'
    ];


    public function attorneyName()
    {
        return $this->hasOne(User::class, 'id', 'attorney_id');
    }

    public function attorneysData()
    {
        return $this->hasMany(ActionAttorneyAssign::class, 'action_id', 'id');
    }

    /**
     * get admin action list 
     * @param array $requestData
     * @param string $attorneyId
     * @return object
     */
    public static function getActionListWithPagination(array $requestData, string $attorneyId = null): object
    {
        $searchText = isset($requestData['search']) ? $requestData['search'] : null;

        $query  = self::with('attorneysData.attorney')
            ->join('law_cases', 'case_actions.case_id', '=', 'law_cases.id')
            ->leftjoin('case_categories', 'law_cases.category_id', '=', 'case_categories.id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftjoin('users as created', 'case_actions.created_by', 'created.id')
            ->select(DB::raw('
                case_actions.id as id,
                case_actions.case_id as case_id,
                case_categories.service_name as category_name,
                case_sub_categories.sub_category_title_english,
                case_sub_categories.sub_category_title_arabic,
                case_actions.actionType as action_type,
                case_actions.importance as importance,
                case_actions.startDate as start_date,
                case_actions.endDate as end_date,
                created.name as created_by_name,
                created.id as created_by_id,
                law_cases.order_no as order_no,
                (CASE 
                    WHEN case_actions.actionStatus = 1  THEN "Pause" 
                    WHEN case_actions.actionStatus = 2 THEN "Done"
                    ELSE "Underway"
                END) AS action_status,
                law_cases.details as details,
                case_actions.createTime as create_time
                 
            '));

        //search 
        if ($searchText && $searchText != '') { //search by string
            $query->where(function ($query) use ($searchText) {
                ////searching 
                $query->where('law_cases.client_name', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.automated_no', 'like', '%' . $searchText . '%')
                    ->orWhere('case_actions.importance', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.court_location', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.order_no', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.capacity', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.room', 'like', '%' . $searchText . '%')
                    ->orwhere(function ($query) use ($searchText) {
                        $query->orWhereHas('attorneysData.attorney', function ($query) use ($searchText) {
                            $query->where('name', 'LIKE', '%' . $searchText . '%');
                        });
                    });
            });
        }
        if (isset($requestData['from']) && $requestData['from'] != null) { //search by date 
            $query->whereBetween('case_actions.endDate',  [$requestData['from'], $requestData['to']]);
        }

        if ($attorneyId != null) { //filter by attorney
            $query->whereHas('attorneysData', function ($query) use ($attorneyId) {

                $query->where('attorney_id', $attorneyId);
            });
        }


        $data = $query->groupBy('case_actions.id')->paginate(15);
        return $data;
    }

    /**
     * get action list by case id
     * @param string $caseId
     * @return object
     */
    public static function getActionListWithCaseId(string $caseId): object
    {
        $query  = self::with('attorneysData.attorney')
            ->join('law_cases', 'case_actions.case_id', '=', 'law_cases.id')
            ->leftjoin('case_categories', 'law_cases.category_id', '=', 'case_categories.id')
            ->leftJoin('case_sub_categories', function ($join) {
                $join->on('law_cases.subcategory_id', '=', 'case_sub_categories.id')
                    ->where('case_categories.id', '!=', CaseCategoryService::OTHER_SERVICE_ID);
            })
            ->leftjoin('users as created', 'case_actions.created_by', 'created.id')
            ->select(DB::raw('
                case_actions.id as id,
                case_actions.case_id as case_id,
                case_categories.service_name as category_name,
                case_sub_categories.sub_category_title_english,
                case_sub_categories.sub_category_title_arabic,
                case_actions.actionType as action_type,
                case_actions.importance as importance,
                case_actions.startDate as start_date,
                case_actions.endDate as end_date,
                created.name as created_by_name,
                created.id as created_by_id,
                (CASE 
                    WHEN case_actions.actionStatus = 1  THEN "Pause" 
                    WHEN case_actions.actionStatus = 2 THEN "Done"
                    ELSE "Underway"
                END) AS action_status,
                law_cases.details as details,
                case_actions.createTime as create_time
                 
            '));

        $query->where('case_actions.case_id', $caseId);

        $data = $query->groupBy('case_actions.id')->get();
        return $data;
    }
}
