<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory, UUID;

    public $timestamps = false;


    protected $fillable = [
        'user_id',
        'token',
        'device_id',
        'device_os',
        'logged_in_at'
    ];

    public function  user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
