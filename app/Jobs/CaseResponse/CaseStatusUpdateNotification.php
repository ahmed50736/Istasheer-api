<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\asigne_case;
use App\Models\law_case;
use App\Models\User;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaseStatusUpdateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $actionDetails, $actionType, $informUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $actionDetails, string $actionType, int $informUser)
    {
        $this->actionDetails = $actionDetails;
        $this->actionType = $actionType;
        $this->informUser = $informUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try {
            $messages = $firebaseHelper::getLanguageMessages();

            $caseDetails = law_case::select('id', 'uid', 'order_no')->where('id', $this->actionDetails->case_id)->first();

            $adminIds = $firebaseHelper::getAllAdminIds();;

            $attorneysIds = $firebaseHelper::getAttorneysIdByCaseId($caseDetails->id);

            if ($this->informUser == 1) {
                $adminIds = array_merge($adminIds, [$caseDetails->uid]);
            }

            $allIds = array_merge($adminIds, $attorneysIds);

            $notification = $firebaseHelper::notificationSetup('Action Status', $messages['english']['action_status_update'], 'case', $caseDetails->id);

            $notification['body'] = str_replace([':caseno', ':status'], [$caseDetails->order_no, $this->actionType], $notification['body']);

            $firebaseHelper::storeNotification($notification, $allIds);

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($allIds);


            FirebaseServices::sendNotification($fcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
