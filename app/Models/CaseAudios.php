<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseAudios extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'case_id',
        'create_time',
    ];

    public function media() {
        return $this->morphOne(Media::class, 'mediaable');
    }

}
