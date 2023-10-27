<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class caseresponse extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'response_id',
        'attorney_id',
        'case_no',
        'case_id',
        'file_staus',
        'reason',
        'deleted_at'
    ];


    public function caseDetails()
    {
        return $this->hasOne(law_case::class, 'order_no', 'case_no');
    }
    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable')->withTrashed();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'attorney_id');
    }

    public static function pendingApprovalWithPagination(string $type, string $attorneyId = ''): object
    {
        $domain = url('/');
        $query = self::join('law_cases', 'caseresponses.case_id', 'law_cases.id')
            ->join('case_categories', 'law_cases.category_id', 'case_categories.id')
            ->leftJoin('payment_details', 'law_cases.id', '=', 'payment_details.case_id')
            ->join('medias', function ($join) {
                $join->on('medias.mediaable_id', '=', 'caseresponses.id')
                    ->where('medias.mediaable_type', '=', self::class);
            })
            ->join('users', 'caseresponses.attorney_id', 'users.id')
            ->select(DB::raw('
                law_cases.id as id,
                medias.id as file_id,
                users.name as uploaded_by,
                users.id as uploaded_user_id,
                medias.file_name as file_name,
                law_cases.order_no as order_no,
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

        if ($type == 'attorney') {
            $query->where('caseresponses.attorney_id', $attorneyId);
        }

        $pendingApproval = $query->where('file_staus', 0)->groupBy('caseresponses.id')->paginate(15);

        return $pendingApproval;
    }
}
