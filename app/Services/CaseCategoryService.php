<?php

namespace App\Services;

use App\helpers\DirtyValueChecker;
use App\Jobs\User\PricelistUpdateNotification;
use App\Models\CaseCategory;
use App\Models\CaseSubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaseCategoryService
{

    const CASE_LAWSUIT = '86f26c63-c49f-4ac6-972e-92fa7c28dc28';
    const CONTRACT_DRAFTING = '324179bf-533e-4066-a866-ad285ac6acaf';
    const CONSULTATION_ID = 'd96af4b5-8206-40cd-95f3-bb5936a18db5';
    const OTHER_SERVICE_ID = 'f5c8145f-2c57-46e4-8a69-96186d497bc7';

    public function createSubCategory($data)
    {
        return CaseSubCategory::create($data);
    }


    public function updateSubCategoryData(array $data): object
    {
        $subcategoryData = CaseSubCategory::where('id', $data['sub_category_id'])->withTrashed()->first();
        if ($subcategoryData->deleted_at == null) {
            $subcategoryData->restore();
        }
        unset($data['sub_category_id']);
        if(DirtyValueChecker::dirtyValueChecker($subcategoryData,$data)){
            $subcategoryData->update($data);
            $subcategoryData = $subcategoryData->refresh();
            PricelistUpdateNotification::dispatch($subcategoryData);
        }
       
        return $subcategoryData;
    }

    public function getUserServiceList()
    {
        $serviceData = CaseCategory::with('subCategories')->get()->keyBy('service_name');
        return $serviceData;
    }

    public function getPriceList(string $caseType)
    {
        $user = Auth::user();
        $query = CaseSubCategory::join('case_categories', 'case_sub_categories.category_id', 'case_categories.id');
        $query->where('case_sub_categories.case_type', $caseType);
        if ($user->isAttorney()) {
            $query->join('asign_attorney_percentages', 'case_sub_categories.id', '=', 'asign_attorney_percentages.subcategory_id')
                ->where('asign_attorney_percentages.attorney_id', $user->id);
            $query->select(DB::raw('
                case_sub_categories.id as id,
                case_sub_categories.sub_category_title_english as sub_category_title_english,
                case_sub_categories.sub_category_title_arabic as sub_category_title_arabic,
                case_sub_categories.case_type as case_type,
                case_categories.id as category_id,
                case_categories.service_name as category_name,
                case_categories.service_title_english as service_title_english,
                case_categories.service_title_arabic as service_title_arabic,
                case_sub_categories.price as price
            '));
        } else {
            $query->select(DB::raw('
                case_sub_categories.id as id,
                case_sub_categories.sub_category_title_english as sub_category_title_english,
                case_sub_categories.sub_category_title_arabic as sub_category_title_arabic,
                case_sub_categories.price as price,
                case_sub_categories.case_type as case_type,
                case_categories.id as category_id,
                case_categories.service_name as category_name,
                case_categories.service_title_english as service_title_english,
                case_categories.service_title_arabic as service_title_arabic
            '));
        }
        $data = $query->groupBy('case_sub_categories.id')->get();

        return $data;
    }
}
