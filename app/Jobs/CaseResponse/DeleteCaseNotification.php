<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\asigne_case;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseDetails;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $caseDetails)
    {
        $this->caseDetails = $caseDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $adminIds = User::whereIn('user_type', [1, 4])->pluck('id')->toArray();
            $attorneyIds = FirebaseHelper::getAttorneysIdByCaseId($this->caseDetails->id);
            $allIds = array_merge($adminIds, $attorneyIds);
            $this->sendNotification($allIds);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * sending notification
     * @param array $userIds
     */
    private function sendNotification(array $userIds): void
    {
        $messages = FirebaseHelper::getLanguageMessages();
        $notification = FirebaseHelper::notificationSetup('Case Deleted', $messages['english']['delete_case'], 'case', $this->caseDetails->id);
        $notification['body'] = str_replace([':caseno'], [$this->caseDetails->order_no], $notification['body']);
        FirebaseHelper::storeNotification($notification, $userIds);
        $getDevicesFcmToken = UserDevice::whereIn('user_id', $userIds)->pluck('fcm_token')->toArray();
        FirebaseServices::sendNotification($getDevicesFcmToken, $notification);
    }
}
