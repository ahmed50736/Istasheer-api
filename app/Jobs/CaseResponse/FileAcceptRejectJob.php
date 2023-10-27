<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\UserDevice;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FileAcceptRejectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseDetails, $type, $notifyUser, $reason;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $caseDetails, string $type, int $notifyUser, string $reason)
    {
        $this->caseDetails = $caseDetails;
        $this->type = $type;
        $this->notifyUser = $notifyUser;
        $this->reason = $reason;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try{
            $messages = $firebaseHelper::getLanguageMessages();
            $getAttorneyIds = $firebaseHelper::getAttorneysIdByCaseId($this->caseDetails->id);

            if($this->notifyUser == 1){
                $getAttorneyIds = array_merge([$this->caseDetails->uid], $getAttorneyIds);
            }
          
            $fcmTokens = $firebaseHelper::getUsersFcmTokens($getAttorneyIds);
            
            $notification = $firebaseHelper::notificationSetup('Response file '.$this->type, $messages['english']['response_file'], 'case', $this->caseDetails->id);
            $notification['body'] = str_replace([':caseno',':type', ':reason'],[$this->caseDetails->order_no, $this->type, $this->reason],$notification['body']);

            $firebaseHelper::storeNotification($notification,$getAttorneyIds);

            FirebaseServices::sendNotification($fcmTokens,$notification);
            
        }catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
        
    }
}
