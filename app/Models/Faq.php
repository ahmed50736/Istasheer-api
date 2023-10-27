<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Faq extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'user_type',
        'language_type',
        'question',
        'answer',
        'details',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function getDataWithPagination(string $usertype, string $lang = null): object
    {
        $data = self::select(DB::raw('
                    id as id,
                    user_type as user_type,
                    question as question,
                    answer as answer
                '));

        if ($lang) {
            $data->where('language_type', $lang);
        }

        $list = $data->where('user_type', $usertype)
            ->paginate(15);

        return $list;
    }
}
