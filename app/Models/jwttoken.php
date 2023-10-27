<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class jwttoken extends Model
{
    use HasFactory,UUID,SoftDeletes;
    public $timestamps = false;
    protected $fillable = [
        'uid',
        'token'
    ];
    protected $hidden = ['token'];
}
