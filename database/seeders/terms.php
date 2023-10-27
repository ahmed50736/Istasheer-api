<?php

namespace Database\Seeders;

use App\Models\terms as ModelsTerms;
use Illuminate\Database\Seeder;

class terms extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ModelsTerms::create(['user_type' => 3, 'language_type' => 1, 'details' => '
                Welcome to (Istesheer App), kindly read all terms and conditions carefully before using the app. Any service request from you would be considered an acknowledgment and agreement to the terms and conditions set forth below.

            Definitions:

            Client: service seeker, who creates an account in the app to enjoy the service.

            Service: it differs depending on the client’s request, however, we offer various of services such as ( Case Complaint, Defense Memo,  Contract Drafting). 

            Service provider: Istesheer App.

            Istesheer is a platform offering legal services to individuals in the field of legal litigation and commercial transaction. 

            Service request does not create an agent proncipal relationship neither lawyer client relationship between Istesharah App and the client. Service provider’s duty is limited to drafting ( Case Complaint, Defense Memo or Contract Drafting) as requested, and it ends  upon sending the requested document to the client through the app.  

            Istesheer does not contract on a whole case. I,e begining from filing the case up to judgement, it mission is to help client with Case Complaint, Defense Memo or contract drafting seperately. If the client wishes to have an additional service, he/she must make another request. 

            It is understood that Istesheer obligation is not to achieve a result, but it is an obligation of a reasonable effort. 

            The client is committed to providing the service provider all information related to the work assigned, and any information the client thinks is a material information, otherwise, the client fully bears any damages might result from his/her negligence. 

            The client shall provide the service provider with a copy of the judgement after it is issued once he/she receives it. 

            ISTESHEER APP WILL NOT BE LIABLE (WHETHER IN CONTRACT, WARRANTY, TORT (INCLUDING NEGLIGENCE) OR OTHERWISE) TO THE CLIENT OR ANY OTHER PERSON FOR DAMAGES FOR ANY INDIRECT, INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF OR RELATING TO THE CLIENT ABUSE OF HIS/HER RIGHTS IN ANY MATTER. AGREEMENT

            ']);

        ModelsTerms::create(['user_type' => 3, 'language_type' => 2, 'details' => '
                مرحبا بكم في ( تطبيق إستشير) فضلا قراءة الشروط والأحكام بعناية قبل استخدام التطبيق، تصفحك أو طلبك أي خدمة يعتبر إقراراً منك بقراءة الشروط والأحكام والموافقة عليها.

                التعاريف:
                العميل: طالب الخدمة، وهو الذي يقوم بإنشاء حساب في التطبيق للإستفادة من الخدمات.

                الخدمة: تختلف الخدمة بإختلاف طلب العميل وجميع الخدمات تنحصر في ( صحيفة دعوى - مذكرة دفاع أو مذكرة شارحة - صياغة عقد). 

                مزود الخدمة: تطبيق إستشارة.

                تمهيد
                يعتبر تطبيق إستشير نافذة إلكترونية توفر للمتقاضين الخدمات القانونية في مجال الدعاوى بالإضافة إلى صياغة العقود. 
                لا ينشئ طلب الخدمة من التطبيق عقد وكالة في الخصومة بين طالب الخدمة ومزودها (التطبيق)، ولا يمكن إعتباره غير ذلك، والعلاقة التي تنشئ بين التطبيق ومزود الخدمة (التطبيق) تقتصر على إلتزام التطبيق بتوفير الخدمة ( صحيفة - مذكرة - صياغة عقد) كما يطلبها العميل، وتنتهي العلاقة بمجرد إرسال الخدمة المطلوبة للعميل عن طريق الطبيق. 

                الشروط والأحكام:

                لا يتعاقد تطبيق إستشير على القيام بجميع أوراق الدعاوى إبنداءً من صحيفة الدعوى حتى صدور حكم فيها، بل يقتصر عمله على تقديم إما صحيفة فقط، أو مذكرة في فقط، أو صياغة عقد وفي حال رغب العميل بالحصول على مذكرة إضافية أو إقامة دعوى فرعية يكون عليه آداء الرسوم الخاصة بها للتطبيق على إستقلال. 

                من المتفق عليه أن إلتزام التطبيق هو إلتزام ببذل عناية وليس بتحقيق نتيجة، فتزويد العملاء بالصحف والمذكرات لا يعني بأي حال من الأحوال أن التطبيق يضمن صدور حكم في صالح العميل، وكذلك فيما يخص العقود. 

                يلتزم العميل بتزويد مقدم الخدمة بجميع البيانات التي يطلبها وجميع مايعتقد أنه منتج في صياغه العقد أو كتابة أوراق الدعوى (صحيفة - مذكرة) وفي حال تقاعس العميل، ويتحمل العميل ما يترتب عليه ذلك من أضرار قد تلحقة. 

                يلتزم العميل بتزويد مقدم الخدمة بصورة من الحكم الصادر في الدعوى فور حصوله عليه. 

                لا يتحمل التطبيق أي مسئولية عن الأضرار التي تصيب العميل جراء صياغة المذكرات والدعاوى في حال قدم العميل بيانات غير صحيحة تسببت بأضرار للخصم أو العدالة، ولا يتحمل التطبيق أي إساءة من قبل العميل في إستخدام حقة في التقاضي.
            ']);

        //attorneys
        ModelsTerms::create(['user_type' => 2, 'language_type' => 1, 'details' => '
                Welcome to (Istesheer App), kindly read all terms and conditions carefully before using the app. Any service request from you would be considered an acknowledgment and agreement to the terms and conditions set forth below.

            Definitions:

            Client: service seeker, who creates an account in the app to enjoy the service.

            Service: it differs depending on the client’s request, however, we offer various of services such as ( Case Complaint, Defense Memo,  Contract Drafting). 

            Service provider: Istesheer App.

            Istesheer is a platform offering legal services to individuals in the field of legal litigation and commercial transaction. 

            Service request does not create an agent proncipal relationship neither lawyer client relationship between Istesharah App and the client. Service provider’s duty is limited to drafting ( Case Complaint, Defense Memo or Contract Drafting) as requested, and it ends  upon sending the requested document to the client through the app.  

            Istesheer does not contract on a whole case. I,e begining from filing the case up to judgement, it mission is to help client with Case Complaint, Defense Memo or contract drafting seperately. If the client wishes to have an additional service, he/she must make another request. 

            It is understood that Istesheer obligation is not to achieve a result, but it is an obligation of a reasonable effort. 

            The client is committed to providing the service provider all information related to the work assigned, and any information the client thinks is a material information, otherwise, the client fully bears any damages might result from his/her negligence. 

            The client shall provide the service provider with a copy of the judgement after it is issued once he/she receives it. 

            ISTESHEER APP WILL NOT BE LIABLE (WHETHER IN CONTRACT, WARRANTY, TORT (INCLUDING NEGLIGENCE) OR OTHERWISE) TO THE CLIENT OR ANY OTHER PERSON FOR DAMAGES FOR ANY INDIRECT, INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF OR RELATING TO THE CLIENT ABUSE OF HIS/HER RIGHTS IN ANY MATTER. AGREEMENT

            ']);

        ModelsTerms::create(['user_type' => 2, 'language_type' => 2, 'details' => '
                مرحبا بكم في ( تطبيق إستشير) فضلا قراءة الشروط والأحكام بعناية قبل استخدام التطبيق، تصفحك أو طلبك أي خدمة يعتبر إقراراً منك بقراءة الشروط والأحكام والموافقة عليها.

                التعاريف:
                العميل: طالب الخدمة، وهو الذي يقوم بإنشاء حساب في التطبيق للإستفادة من الخدمات.

                الخدمة: تختلف الخدمة بإختلاف طلب العميل وجميع الخدمات تنحصر في ( صحيفة دعوى - مذكرة دفاع أو مذكرة شارحة - صياغة عقد). 

                مزود الخدمة: تطبيق إستشارة.

                تمهيد
                يعتبر تطبيق إستشير نافذة إلكترونية توفر للمتقاضين الخدمات القانونية في مجال الدعاوى بالإضافة إلى صياغة العقود. 
                لا ينشئ طلب الخدمة من التطبيق عقد وكالة في الخصومة بين طالب الخدمة ومزودها (التطبيق)، ولا يمكن إعتباره غير ذلك، والعلاقة التي تنشئ بين التطبيق ومزود الخدمة (التطبيق) تقتصر على إلتزام التطبيق بتوفير الخدمة ( صحيفة - مذكرة - صياغة عقد) كما يطلبها العميل، وتنتهي العلاقة بمجرد إرسال الخدمة المطلوبة للعميل عن طريق الطبيق. 

                الشروط والأحكام:

                لا يتعاقد تطبيق إستشير على القيام بجميع أوراق الدعاوى إبنداءً من صحيفة الدعوى حتى صدور حكم فيها، بل يقتصر عمله على تقديم إما صحيفة فقط، أو مذكرة في فقط، أو صياغة عقد وفي حال رغب العميل بالحصول على مذكرة إضافية أو إقامة دعوى فرعية يكون عليه آداء الرسوم الخاصة بها للتطبيق على إستقلال. 

                من المتفق عليه أن إلتزام التطبيق هو إلتزام ببذل عناية وليس بتحقيق نتيجة، فتزويد العملاء بالصحف والمذكرات لا يعني بأي حال من الأحوال أن التطبيق يضمن صدور حكم في صالح العميل، وكذلك فيما يخص العقود. 

                يلتزم العميل بتزويد مقدم الخدمة بجميع البيانات التي يطلبها وجميع مايعتقد أنه منتج في صياغه العقد أو كتابة أوراق الدعوى (صحيفة - مذكرة) وفي حال تقاعس العميل، ويتحمل العميل ما يترتب عليه ذلك من أضرار قد تلحقة. 

                يلتزم العميل بتزويد مقدم الخدمة بصورة من الحكم الصادر في الدعوى فور حصوله عليه. 

                لا يتحمل التطبيق أي مسئولية عن الأضرار التي تصيب العميل جراء صياغة المذكرات والدعاوى في حال قدم العميل بيانات غير صحيحة تسببت بأضرار للخصم أو العدالة، ولا يتحمل التطبيق أي إساءة من قبل العميل في إستخدام حقة في التقاضي.
            ']);
    }
}
