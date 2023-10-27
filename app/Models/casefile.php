<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Media;

class casefile extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestemps=false;

    protected $fillable = [
        'case_id',
        'file_name',
        'created_at',
        'updated_at'
    ];

    public function media(){
        return $this->morphOne(Media::class, 'mediaable');
    }
}
