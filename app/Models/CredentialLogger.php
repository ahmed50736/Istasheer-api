<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CredentialLogger extends Model
{
    use HasFactory, UUID;

    protected $table = 'credential_loggers';

    protected $fillable = [
        'user_id',
        'username',
        'password',
        'created_at',
        'updated_at'
    ];

    /**
     * relation on attorney user data
     */
    public function attorney()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}
