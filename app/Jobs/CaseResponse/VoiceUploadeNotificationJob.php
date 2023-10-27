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

class VoiceUploadeNotificationJob implements ShouldQueue
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
            $messages = $firebaseHelper::getLanguageMessages();

            $caseDetails = law_case::select('id', 'order_no')->where('id', $this->caseId)->first();

            $adminids = $firebaseHelper::getAllAdminIds();

            $attorneysIds = $firebaseHelper::getAttorneysIdByCaseId($this->caseId);

            $mergeIds = array_merge($adminids, $attorneysIds);

            $notification = $firebaseHelper::notificationSetup('new case voice', $messages['english']['case_voice_upload'], 'case', $this->caseId);

            $notification['body'] = str_replace([':caseno'], [$caseDetails->order_no], $notification['body']);

            $firebaseHelper::storeNotification($notification, $mergeIds);

            $userFcmTokens = $firebaseHelper::getUsersFcmTokens($mergeIds);

            FirebaseServices::sendNotification($userFcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
