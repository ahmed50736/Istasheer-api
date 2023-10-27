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

class AssignNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $assignData)
    {
        $this->assignData = $assignData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $notificationMessages = FirebaseHelper::getLanguageMessages();

            $attorneyDeviceInfo = FirebaseHelper::getUserDeviceTokenAndLang($this->assignData['attorney_id']);

            $messageTypeAttorney = $this->assignData['type'] == 'assign' ? 'assign_case_to_attorney' : 'reassign_case_to_attorney';

            $attorneyNotificationDetails = FirebaseHelper::notificationSetup('Case asigne', $notificationMessages['english'][$messageTypeAttorney], 'case', $this->assignData['case_id']);

            //storing notification
            FirebaseHelper::storeNotification($attorneyNotificationDetails, [$this->assignData['attorney_id']]);

            foreach ($attorneyDeviceInfo as $device) {
                $message = $device->lang == 'ar' ? $notificationMessages['arabic'][$messageTypeAttorney] : $notificationMessages['english'][$messageTypeAttorney];
                $attorneyNotificationDetails['body'] = $message;
                FirebaseServices::sendNotification([$device->fcm_token], $attorneyNotificationDetails);
            }

            if ($this->assignData['notify_user'] == 1) {
                $messageTypeUser = $this->assignData['type'] == 'assign' ? 'assign_case_to_user' : 'reassign_case_to_user';
                $findUserID = law_case::select('uid')->where('id', $this->assignData['case_id'])->first();

                $userDeviceInfo = FirebaseHelper::getUserDeviceTokenAndLang($findUserID->uid);

                $userNotificationDetails = FirebaseHelper::notificationSetup('Case asigne', $notificationMessages['english'][$messageTypeUser], 'case', $this->assignData['case_id']);
                FirebaseHelper::storeNotification($userNotificationDetails, [$findUserID->uid]);

                foreach ($userDeviceInfo as $device) {
                    $message = $device->lang == 'ar' ? $notificationMessages['arabic'][$messageTypeUser] : $notificationMessages['english'][$messageTypeUser];
                    $userNotificationDetails['body'] = $message;
                    FirebaseServices::sendNotification([$device->fcm_token], $userNotificationDetails);
                }
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
