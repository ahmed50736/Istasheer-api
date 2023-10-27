<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\law_case;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtraServiceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $caseId)
    {
        $this->caseId = $caseId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try {
            $caseDetails = law_case::select('id', 'order_no', 'uid')->where('id', $this->caseId)->first();

            $adminIds = $firebaseHelper::getAllAdminIds();

            $attorneyIds = $firebaseHelper::getAttorneysIdByCaseId($this->caseId);

            $mergeIds = array_merge($adminIds, $attorneyIds);

            $messages = $firebaseHelper::getLanguageMessages();

            $notification = $firebaseHelper::notificationSetup('Extra service Added', $messages['english']['extra_service'], 'case', $this->caseId);
            $notification['body'] = str_replace([':caseno'], [$caseDetails->order_no], $notification['body']);

            $firebaseHelper::storeNotification($notification, $mergeIds);

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($mergeIds);

            FirebaseServices::sendNotification($fcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
