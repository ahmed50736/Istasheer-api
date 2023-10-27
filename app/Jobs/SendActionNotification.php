<?php

namespace App\Jobs;

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

class SendActionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $inform, $attorneyIds, $caseId, $notificationMessages, $orderno;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $inform, array $attorneyIds, string $caseId)
    {
        $this->inform = $inform;
        $this->attorneyIds = $attorneyIds;
        $this->caseId = $caseId;
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

            if ($this->inform == 1) {
                $this->sendNotificationToUser($this->caseId);
            }
            $this->sendNotificationToAttorneys($this->attorneyIds);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * notification send toi user
     * @param string $caseId
     * @return void
     */
    private function sendNotificationToUser(string $caseId): void
    {
        $case = law_case::select('uid', 'order_no')->where('id', $caseId)->first();

        $this->orderno = $case->order_no;

        $notification = $this->setupNotificationData($this->notificationMessages['english']['user_action'], $this->caseId);
        $notification['body'] = str_replace([':caseno'], [$this->orderno], $notification['body']);

        $userDevices = FirebaseHelper::getUserDeviceTokenAndLang($case->uid);

        FirebaseHelper::storeNotification($notification, [$case->uid]);

        foreach ($userDevices as $userDevice) {
            $type = $userDevice->lang == 'ar' ? 'arabic' : 'english';
            $notification['body'] = $this->notificationMessages[$type]['user_action'];
            $notification['body'] = str_replace([':caseno'], [$this->orderno], $notification['body']);
            FirebaseServices::sendNotification([$userDevice->fcm_token], $notification);
        }
    }

    /**
     * sending notification to attorneys
     * @param array $attorneyIds
     * @return void
     */
    private function sendNotificationToAttorneys(array $attorneyIds): void
    {
        $notification = $this->setupNotificationData($this->notificationMessages['english']['attorney_action'], $this->caseId);
        $notification['body'] = str_replace([':caseno'], [$this->orderno], $notification['body']);
        ///storing notification for attorneys

        FirebaseHelper::storeNotification($notification, $attorneyIds);


        $attorneysDevices = FirebaseHelper::getAllDeviceTokensAndLangByIds($attorneyIds);

        foreach ($attorneysDevices as $device) {
            $type = $device->lang == 'ar' ? 'arabic' : 'english';
            $notification['body'] = $this->notificationMessages[$type]['attorney_action'];
            $notification['body'] = str_replace([':caseno'], [$this->orderno], $notification['body']);
            FirebaseServices::sendNotification([$device->fcm_token], $notification);
        }
    }

    /**
     * setup notification data for notification
     * @param string $message
     * @param string $caseId
     * @return array
     */
    private function setupNotificationData(string $message, string $caseId): array
    {
        return FirebaseHelper::notificationSetup('New case Action', $message, 'case', $caseId);
    }
}
