<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Guideline extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable');
    }

    /**
     * getting list of guidelines with media file
     * @param string $type
     * @return object
     */
    public static function listOfGuidelines(string $type): object
    {
        $url = url('/');
        $data = self::leftjoin('medias', function ($join) {
            $join->on('medias.mediaable_id', '=', 'guidelines.id')
                ->where('medias.mediaable_type', '=', self::class);
        })
            ->select(DB::raw('
                guidelines.id as id,
                guidelines.title as title,
                guidelines.description as description,
                concat("' . $url . '/", medias.photo) as file_url,
                medias.id as file_id
            '))
            ->where('guidelines.user_type', $type)
            ->groupBy('guidelines.id')
            ->paginate(10);

        return $data;
    }
}
