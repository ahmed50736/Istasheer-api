<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Reminders extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'case_id',
        'attorney_id',
        'note',
        'created_at',
        'status'
    ];

    public function case()
    {
        return $this->hasOne(law_case::class, 'case_id', 'id');
    }

    public function attorneyDetails()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    /**
     * getting reminder list for admin and attorney
     * @param string $attorneyId
     * @return object
     */
    public static function getReminderList(string $attorneyId = null, string $status): object
    {
        $query = self::join('law_cases', 'reminders.case_id', 'law_cases.id')
            ->join('users', 'reminders.attorney_id', 'users.id')
            ->select(DB::raw('
                        reminders.id as id,
                        reminders.note as note,
                        reminders.attorney_id as attorney_id,
                        users.name as attorney_name,
                        law_cases.order_no as order_no,
                        law_cases.case_type as case_type,
                        law_cases.client_name as client_name,
                        law_cases.against as against,
                        law_cases.automated_no as automated_no,
                        reminders.created_at as reminder_created_at,
                        reminders.case_id as case_id,
                        law_cases.create_time as case_created_at
                    '));

        if ($attorneyId != null) {
            $query->where('reminders.attorney_id', $attorneyId);
        }

        $query->where('reminders.status', $status);

        $data = $query->groupBy('reminders.id')->paginate(20);

        return $data;
    }
}
