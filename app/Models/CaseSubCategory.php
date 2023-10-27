<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseSubCategory extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'case_sub_categories';

    protected $fillable = [
        'category_id',
        'sub_category_title_english',
        'sub_category_title_arabic',
        'price',
        'admin_percentage',
        'case_type',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
