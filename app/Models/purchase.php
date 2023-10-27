<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class purchase extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'transection_id',
        'uid',
        'ammount',
        'case_id',
        'purchase_time'
    ];
}
