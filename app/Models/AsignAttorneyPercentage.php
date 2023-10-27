<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignAttorneyPercentage extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'asign_attorney_percentages';

    protected $fillable = [
        'attorney_id',
        'subcategory_id',
        'admin_percentage',
        'admin_id',
        'created_at',
        'updated_at'
    ];
}
