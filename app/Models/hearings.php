<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class hearings extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'case_id',
        'created_by',
        'session_type',
        'date',
        'informe',
        'time',
        'decission',
        'note',
        'action'
    ];

    public function caseInfo()
    {
        return $this->hasOne(law_case::class, 'id', 'case_id');
    }

    public function attorneysDatas()
    {
        return  $this->hasMany(HearingAttorneys::class, 'hearing_id', 'id');
    }



    /**
     * hearing list with pagination
     * @param string $type
     * @param string $attorneyId
     * @return object
     */
    public static function getHearingsWithPagination(array $requestData, string $attorneyId = null): object
    {
        $query = self::with('attorneysDatas.attorney')
            ->join('law_cases', 'hearings.case_id', 'law_cases.id')
            ->leftjoin('users as created', 'hearings.created_by', '=', 'created.id')
            ->select(DB::raw('
                        law_cases.id as case_id,
                        hearings.id as id,
                        created.name as created_by_name,
                        (CASE 
                        WHEN created.user_type=2 THEN "Attorney" 
                        ELSE "Admin"
                        END) AS created_user_type,
                        created.id as created_by_id,
                        law_cases.client_name as client_name,
                        law_cases.against as against,
                        law_cases.capacity as capacity1,
                        law_cases.court_location as court_location,
                        law_cases.chamber as chamber,
                        law_cases.room as room,
                        law_cases.order_no as order_no,
                        law_cases.automated_no as automated_no,
                        hearings.session_type as session_type,
                        hearings.date as hearing_date,
                        hearings.time as hearing_time,
                        hearings.decission as hearing_decission,
                        hearings.note as hearing_note,
                        hearings.informe as informe
                       
                    '));


        //seach based on seach text
        if (isset($requestData['search']) && $requestData['search'] != null) {
            $searchText = $requestData['search'];

            $query->where(function ($query) use ($searchText) {
                ////searching 
                $query->where('law_cases.client_name', 'like', '%' . $searchText . '%')
                    //->orWhere('att.name', 'like', '%' . $requestData['search'] . '%')
                    ->orWhere('law_cases.automated_no', 'like', '%' . $searchText . '%')
                    ->orWhere('hearings.session_type', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.court_location', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.order_no', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.capacity', 'like', '%' . $searchText . '%')
                    ->orWhere('law_cases.room', 'like', '%' . $searchText . '%')
                    ->orWhere('hearings.decission', 'like', '%' . $searchText . '%')
                    ->orwhere(function ($query) use ($searchText) {
                        $query->orWhereHas('attorneysDatas.attorney', function ($query) use ($searchText) {
                            $query->where('name', 'LIKE', '%' . $searchText . '%');
                        });
                    });
            });
        }

        ///search by attorney
        if ($attorneyId != null) {
            $query->whereHas('attorneysDatas', function ($query) use ($attorneyId) {

                $query->where('attorney_id', $attorneyId);
            });
        }

        //search by date
        if (isset($requestData['from']) && isset($requestData['to'])) {
            $query->whereBetween('hearings.date', [$requestData['from'], $requestData['to']]);
        }


        $hearingsData = $query->groupBy('hearings.id')->paginate(15);

        return $hearingsData;
    }


    /**
     * hearing list with pagination
     * @param string $type
     * @param string $attorneyId
     * @return object
     */
    public static function getHearingsWithcaseID(string $caseID): object
    {
        $query = self::with('attorneysDatas.attorney')
            ->join('law_cases', 'hearings.case_id', 'law_cases.id')
            ->leftjoin('users as created', 'hearings.created_by', '=', 'created.id')
            ->select(DB::raw('
                        law_cases.id as case_id,
                        hearings.id as id,
                        created.name as created_by_name,
                        (CASE 
                        WHEN created.user_type=2 THEN "Attorney" 
                        ELSE "Admin"
                        END) AS created_user_type,
                        created.id as created_by_id,
                        law_cases.client_name as client_name,
                        law_cases.against as against,
                        law_cases.capacity as capacity1,
                        law_cases.court_location as court_location,
                        law_cases.chamber as chamber,
                        law_cases.room as room,
                        law_cases.order_no as order_no,
                        law_cases.automated_no as automated_no,
                        hearings.session_type as session_type,
                        hearings.date as hearing_date,
                        hearings.time as hearing_time,
                        hearings.decission as hearing_decission,
                        hearings.note as hearing_note,
                        hearings.informe as informe
                       
                    '));




        //search by date

        $query->where('hearings.case_id', $caseID);



        $hearingsData = $query->groupBy('hearings.id')->get();

        return $hearingsData;
    }
}
