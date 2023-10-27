<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\UUID;
use App\Models\CaseSubCategory;

class CaseCategory extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'case_categories';

    public function subCategories()
    {
        return $this->hasMany(CaseSubCategory::class, 'category_id', 'id');
    }
}
