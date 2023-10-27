<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\UUID;

class UserDevice extends Model
{
    use HasFactory,SoftDeletes,UUID;

    protected $fillable = [
        'user_id',
        'device_uid',
        'fcm_token',
        'device_os',
        'lang',
        'created_at',
        'updated_at'
    ];

}
