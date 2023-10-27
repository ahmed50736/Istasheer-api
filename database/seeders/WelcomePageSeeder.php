<?php

namespace Database\Seeders;

use App\Models\WelcomePage;
use Illuminate\Database\Seeder;

class WelcomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //attorney part
        WelcomePage::create(['lang' => 'ar', 'title' => 'اختبار 1 محامي', 'description' => 'هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها. الهدف من استخدام Lorem Ipsum هو أنه يحتوي على توزيع طبيعي للأحرف إلى حد ما
        ', 'user_type' => 'attorney']);
        WelcomePage::create(['lang' => 'en', 'title' => 'Test 1 attorney', 'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters', 'user_type' => 'attorney']);

        //user part
        WelcomePage::create(['lang' => 'ar', 'title' => 'اختبار 1 مستخدم', 'description' => 'لوريم إيبسوم هو ببساطة نص شكلي يستخدم في صناعة الطباعة والتنضيد. كان Lorem Ipsum هو النص الوهمي القياسي في الصناعة منذ القرن الخامس عشر الميلادي ، عندما أخذت طابعة غير معروفة لوحًا من النوع وتدافعت عليه لصنع كتاب عينة من النوع. لقد نجت ليس فقط خمسة قرون ، ولكن أيضًا القفزة في التنضيد الإلكتروني ، وظلت دون تغيير جوهري', 'user_type' => 'user']);
        WelcomePage::create(['lang' => 'en', 'title' => 'Test 1 user', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged', 'user_type' => 'user']);
    }
}
