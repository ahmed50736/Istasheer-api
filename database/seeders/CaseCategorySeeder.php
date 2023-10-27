<?php

namespace Database\Seeders;

use App\Models\CaseCategory;
use Illuminate\Database\Seeder;

class CaseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CaseCategory::create(['id' => '86f26c63-c49f-4ac6-972e-92fa7c28dc28', 'service_name' => 'Case Lawsuit', 'service_title_english' => 'Case Lawsuit', 'service_title_arabic' => 'قضية']);
        CaseCategory::create(['id' => '324179bf-533e-4066-a866-ad285ac6acaf', 'service_name' => 'contract Drafting', 'service_title_english' => 'contract Drafting', 'service_title_arabic' => 'صياغة عقد']);
        CaseCategory::create(['id' => 'd96af4b5-8206-40cd-95f3-bb5936a18db5', 'service_name' => 'Consultation', 'service_title_english' => 'Consultation', 'service_title_arabic' => 'إستشارة']);
        CaseCategory::create(['id' => 'f5c8145f-2c57-46e4-8a69-96186d497bc7', 'service_name' => 'Other Sevices', 'service_title_english' => 'Other Sevices', 'service_title_arabic' => 'خدمات أخرى']);
    }
}
