<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\asigne_case;
use App\Models\User;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteCaseJobNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseId, $userId;
    private $firebaseHelper;
    private $firebaseServices;

    public function __construct(string $caseId, $userId)
    {
        $this->caseId = $caseId;
        $this->userId = $userId;
    }

    public function handle(FirebaseHelper $firebaseHelper, FirebaseServices $firebaseServices)
    {
        $this->firebaseHelper = $firebaseHelper;
        $this->firebaseServices = $firebaseServices;
        try {
            $notificationMessages = $this->firebaseHelper->getLanguageMessages();
            $getAdminsIds = User::whereIn('user_type', [1, 4])->pluck('id')->toArray();
            $getAttorneysIds = FirebaseHelper::getAttorneysIdByCaseId($this->caseId);
            $getAdminDevicesInfos = $this->firebaseHelper->getAllDeviceTokensAndLangByIds($getAdminsIds);
            $getUserDeviceInfo = $this->firebaseHelper->getUserDeviceTokenAndLang($this->userId);
            $getAttorneysDeviceInfo = $this->firebaseHelper->getAllDeviceTokensAndLangByIds($getAttorneysIds);

            $this->sendNotifications($getAdminsIds, $getAdminDevicesInfos, $notificationMessages['english']['service_complete'], 'Service Completed', $notificationMessages);
            $this->sendNotifications($getAttorneysIds, $getAttorneysDeviceInfo, $notificationMessages['english']['service_complete_attorney'], 'Service Completed', $notificationMessages);
            $this->sendNotifications([$this->userId], $getUserDeviceInfo, $notificationMessages['english']['service_complete'], 'Service Completed', $notificationMessages);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }


    /**
     * send notification
     */
    private function sendNotifications($userIds, $devicesInfo, $message, $title, $notificationMessages)
    {
        $notificationMessages = $this->firebaseHelper->getLanguageMessages();
        $notificationDetails = $this->firebaseHelper->notificationSetup($title, $message, 'case', $this->caseId);
        $this->firebaseHelper::storeNotification($notificationDetails, $userIds);

        foreach ($devicesInfo as $device) {
            $message = $device->lang == 'ar' ? $notificationMessages['arabic']['service_complete'] : $notificationMessages['english']['service_complete'];
            $notificationDetails['body'] = $message;
            $this->firebaseServices::sendNotification([$device->fcm_token], $notificationDetails);
        }
    }
}
