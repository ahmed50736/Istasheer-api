<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class extraService extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'type',
        'price',
        'transection_id',
        'status',
        'case_id',
        'details',
        'extra_order_no',
        'created_at',
        'updated_at'
    ];
    protected $hidden = ['transection_id'];
    
}
