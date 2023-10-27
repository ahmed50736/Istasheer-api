<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\User;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $type, $caseDetails, $notificationMessages;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, object $caseDetails)
    {
        $this->type = $type;
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

            $this->notificationMessages = FirebaseHelper::getLanguageMessages();

            $getAdminIds = FirebaseHelper::getAllAdminIds();

            $getAttorneysIds = FirebaseHelper::getAttorneysIdByCaseId($this->caseDetails->id);

            $mergeIds = array_merge($getAdminIds, $getAttorneysIds);

            if ($this->type == 'fileUpload') {
                $this->uploadFileNotification($mergeIds);
            } else {
                $this->updateDetailsNotification($mergeIds);
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * sending upload new case file notification
     * @param array $userIds
     * @return void
     */
    private function uploadFileNotification(array $userIds): void
    {
        $body = $this->notificationMessages['english']['new_file_upload'];

        $body = str_replace([':caseno'], [$this->caseDetails->order_no], $body);

        $notification = FirebaseHelper::notificationSetup('New files', $body, 'case', $this->caseDetails->id);

        FirebaseHelper::storeNotification($notification, $userIds);

        $devices = FirebaseHelper::getAllDeviceTokensAndLangByIds($userIds);

        foreach ($devices as $device) {
            $type = $device->lang == 'ar' ? 'arabic' : 'english';
            $notification['body'] = str_replace([':caseno'], [$this->caseDetails->order_no], $this->notificationMessages[$type]['new_file_upload']);
            FirebaseServices::sendNotification([$device->fcm_token], $notification);
        }
    }

    /**
     * sending case modification details
     * @param array $userIds
     * @return void
     */
    private function updateDetailsNotification(array $userIds): void
    {
        $body = $this->notificationMessages['english']['update_case'];

        $body = str_replace([':caseno'], [$this->caseDetails->order_no], $body);

        $notification = FirebaseHelper::notificationSetup('Case Details Update', $body, 'case', $this->caseDetails->id);

        FirebaseHelper::storeNotification($notification, $userIds);

        $devices = FirebaseHelper::getAllDeviceTokensAndLangByIds($userIds);

        foreach ($devices as $device) {
            $type = $device->lang == 'ar' ? 'arabic' : 'english';
            $notification['body'] = str_replace([':caseno'], [$this->caseDetails->order_no], $this->notificationMessages[$type]['update_case']);
            FirebaseServices::sendNotification([$device->fcm_token], $notification);
        }
    }
}
