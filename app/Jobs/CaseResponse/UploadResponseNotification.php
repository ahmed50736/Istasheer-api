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

class UploadResponseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseDetails, $notificationMessages;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($caseDetails)
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
            $this->notificationMessages = FirebaseHelper::getLanguageMessages();

            $notification = FirebaseHelper::notificationSetup('New Response File', $this->notificationMessages['english']['response_file_upload'], 'case', $this->caseDetails->id);

            $notification['body'] = str_replace([':caseno'], [$this->caseDetails->order_no], $notification['body']);

            $adminIds = FirebaseHelper::getAllAdminIds();

            FirebaseHelper::storeNotification($notification, $adminIds);

            $adminDevices = FirebaseHelper::getAllDeviceTokensAndLangByIds($adminIds);

            $this->sendNotificationToAdmin($adminDevices, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * sending notification
     * @param array $devices
     * @return void
     */
    private function sendNotificationToAdmin(object $devices, array $notification): void
    {
        foreach ($devices as $device) {
            $type = $device->lang == 'ar' ? 'arabic' : 'english';
            $notification['body'] = str_replace([':caseno'], [$this->caseDetails->order_no], $this->notificationMessages[$type]['response_file_upload']);
            FirebaseServices::sendNotification([$device->fcm_token], $notification);
        }
    }
}
