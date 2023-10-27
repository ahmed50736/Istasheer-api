<?php

namespace Database\Seeders;

use App\Models\aboutus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class about extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //users
        aboutus::create([
            'user_type' => 3, 'language_type' => 1, 'details' =>
            'Istesheer is a platform where you can seek help with respect to drafting all kind of contracts, crime reports, case complaints and defense Memos. 
                Our team is hilghly experienced and professional experts who are committed to providing clients with effective means to achieve their needs.', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
        ]);
        aboutus::create([
            'user_type' => 3, 'language_type' => 2, 'details' => '
                تطبيق إستشير يحتوي على مميزات تساعد المتقاضين على الحصول على ما يحتاجونه من مساعدة في قضاياهم بأقل التكاليف المادية. يضمن التطبيق مجموعة من الخبراء القانونيين المتخصصين في مجالات الدعاوى وصياغة العقود والبلاغات ومذكرات الدفاع.
                ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
        ]);

        ///attorney
        aboutus::create([
            'user_type' => 2, 'language_type' => 1, 'details' =>
            'Istesheer is a platform where you can seek help with respect to drafting all kind of contracts, crime reports, case complaints and defense Memos. 
                Our team is hilghly experienced and professional experts who are committed to providing clients with effective means to achieve their needs.', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
        ]);
        aboutus::create([
            'user_type' => 2, 'language_type' => 2, 'details' => '
                تطبيق إستشير يحتوي على مميزات تساعد المتقاضين على الحصول على ما يحتاجونه من مساعدة في قضاياهم بأقل التكاليف المادية. يضمن التطبيق مجموعة من الخبراء القانونيين المتخصصين في مجالات الدعاوى وصياغة العقود والبلاغات ومذكرات الدفاع.
                ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
        ]);
    }
}
