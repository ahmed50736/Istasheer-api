<?php

namespace Database\Seeders;

use App\Models\CaseSubCategory;
use App\Services\CaseCategoryService;
use Illuminate\Database\Seeder;

class SubCategoryCreate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //contract drafting
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'individual', 'sub_category_title_english' => 'Draft Contract', 'sub_category_title_arabic' => 'مسودة العقد', 'price' => '10']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'corporate', 'sub_category_title_english' => 'Draft Contract', 'sub_category_title_arabic' => 'مسودة العقد', 'price' => '20']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'individual', 'sub_category_title_english' => 'Review Contract', 'sub_category_title_arabic' => 'مراجعة العقد', 'price' => '20']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'corporate', 'sub_category_title_english' => 'Review Contract', 'sub_category_title_arabic' => 'مراجعة العقد', 'price' => '30']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'individual', 'sub_category_title_english' => 'Review Comments', 'sub_category_title_arabic' => 'مراجعة التعليقات', 'price' => '15']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONTRACT_DRAFTING, 'case_type' => 'corporate', 'sub_category_title_english' => 'Review Comments', 'sub_category_title_arabic' => 'مراجعة التعليقات', 'price' => '20']);
        //case-lawsuit
        CaseSubCategory::create(['category_id' => CaseCategoryService::CASE_LAWSUIT, 'case_type' => 'individual', 'sub_category_title_english' => 'Complaint', 'sub_category_title_arabic' => 'شكوى', 'price' => '10']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CASE_LAWSUIT, 'case_type' => 'corporate', 'sub_category_title_english' => 'Complaint', 'sub_category_title_arabic' => 'شكوى', 'price' => '20']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CASE_LAWSUIT, 'case_type' => 'individual', 'sub_category_title_english' => 'Defense Memo', 'sub_category_title_arabic' => 'مذكرة الدفاع', 'price' => '100']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CASE_LAWSUIT, 'case_type' => 'corporate', 'sub_category_title_english' => 'Defense Memo', 'sub_category_title_arabic' => 'مذكرة الدفاع', 'price' => '200']);
        //consultation
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONSULTATION_ID, 'case_type' => 'individual', 'sub_category_title_english' => 'Consultation', 'sub_category_title_arabic' => 'التشاور', 'price' => '50']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::CONSULTATION_ID, 'case_type' => 'corporate', 'sub_category_title_english' => 'Consultation', 'sub_category_title_arabic' => 'التشاور', 'price' => '70']);
        //other service
        CaseSubCategory::create(['category_id' => CaseCategoryService::OTHER_SERVICE_ID, 'case_type' => 'individual', 'sub_category_title_english' => 'comin soon', 'sub_category_title_arabic' => 'قريبا', 'price' => '55']);
        CaseSubCategory::create(['category_id' => CaseCategoryService::OTHER_SERVICE_ID, 'case_type' => 'corporate', 'sub_category_title_english' => 'comin soon', 'sub_category_title_arabic' => 'قريبا', 'price' => '88']);
    }
}
