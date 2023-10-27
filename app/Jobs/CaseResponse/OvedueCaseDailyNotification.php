<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\asigne_case;
use App\Models\law_case;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class OvedueCaseDailyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $messages, $adminIds, $firebaseHelper, $adminFcmsTokens;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->firebaseHelper = new FirebaseHelper;
        $this->messages = $this->firebaseHelper::getLanguageMessages();
        $this->adminIds = $this->firebaseHelper::getAllAdminIds();
        $this->adminFcmsTokens = $this->firebaseHelper::getUsersFcmTokens($this->adminIds);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $dueAssignments = asigne_case::join('law_cases', 'asigne_cases.case_id', 'law_cases.id')
                                            ->select(DB::raw('
                                                law_cases.order_no as order_no,
                                                asigne_cases.attorney_id as attorney_id,
                                                law_cases.id as case_id
                                            '))
                                            ->where('asigne_cases.deadline','<',date('Y-m-d'))
                                            ->where('asigne_cases.asigne_status','!=',2)
                                            ->get();

            
            foreach($dueAssignments as $dueAssignment){
               $this->sendAdminNotification($dueAssignment);
               $this->sendAttorneyNotification($dueAssignment);
            }
                
        }catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * admin notification send
     * @param object $dueAsignment
     * @return void
     */
    private function sendAdminNotification(object $dueAsignment):void
    {
        $adminNotification = $this->firebaseHelper::notificationSetup('Over Due Assignment', $this->messages['english']['over_due_admin'], 'case', $dueAsignment->case_id);
        
        $adminNotification['body'] = str_replace([':caseno'], [$dueAsignment->order_no], $adminNotification['body']);
        
        $this->firebaseHelper::storeNotification($adminNotification, $this->adminIds);

        if (!empty($this->adminFcmsTokens)) {
            FirebaseServices::sendNotification($this->adminFcmsTokens, $adminNotification);
        }
    }

    /**
     * attorney notification
     * @param object $dueAsignment
     * @return void
     */
    private function sendAttorneyNotification(object $dueAsignment):void
    {
        $notification = $this->firebaseHelper::notificationSetup('Over Due Assignment',$this->messages['english']['over_due_attorney'], 'case', $dueAsignment->case_id);
        
        $notification['body'] = str_replace([':caseno'], [$dueAsignment->order_no], $notification['body']);
        
        $this->firebaseHelper::storeNotification($notification,[$dueAsignment->attorney_id]);
        
        $fcmTokens = $this->firebaseHelper::getUsersFcmTokens([$dueAsignment->attorney_id]);

        if (!empty($fcmTokens)) {
            FirebaseServices::sendNotification($fcmTokens, $notification);
        }
        
    }

}
