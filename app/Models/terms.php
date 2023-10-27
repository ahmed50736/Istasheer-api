<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class terms extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'user_type',
        'language_type',
        'details',
        'created_at',
        'updated_at'
    ];
}
