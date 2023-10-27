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
use App\Models\User;


class RemoveAttorney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseId, $attorneyId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $caseId, string $attorneyId)
    {
        $this->caseId = $caseId;
        $this->attorneyId = $attorneyId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $userID = law_case::select('uid')->where('id', $this->caseId)->first();

            //get user devices info
            $userDevicesInfo = FirebaseHelper::getUserDeviceTokenAndLang($userID->uid);

            //get attorneys devices info
            $attorneysDevicesInfo = FirebaseHelper::getUserDeviceTokenAndLang($this->attorneyId);

            $attorneyName = User::select('name')->where('id', $this->attorneyId)->first();

            //get language messages
            $notificationMessages = FirebaseHelper::getLanguageMessages();

            $attorneyNotification = FirebaseHelper::notificationSetup('Remove Attorney', $notificationMessages['english']['remove_attorney'], 'case', $this->caseId);

            $userMessage = $notificationMessages['english']['remove_attorney_to_user'];
            $userNotificationBody = str_replace([':username'], [$attorneyName->name], $userMessage);
            $userNotification = FirebaseHelper::notificationSetup('Remove Attorney', $userNotificationBody, 'case', $this->caseId);


            //store notification
            FirebaseHelper::storeNotification($userNotification, [$userID->uid]);
            FirebaseHelper::storeNotification($attorneyNotification, [$this->attorneyId]);

            //sending notification to attorney
            foreach ($attorneysDevicesInfo as $attorney) {
                $message = $attorney->lang == 'ar' ? $notificationMessages['arabic']['remove_attorney'] : $notificationMessages['english']['remove_attorney'];
                $attorneyNotification['body'] = $message;
                FirebaseServices::sendNotification([$attorney->fcm_token], $attorneyNotification);
            }

            //sending notification to user
            foreach ($userDevicesInfo as $user) {
                $message = $user->lang == 'ar' ? $notificationMessages['arabic']['remove_attorney_to_user'] : $notificationMessages['english']['remove_attorney_to_user'];
                $message = str_replace([':username'], [$attorneyName->name], $message);
                $userNotification['body'] = $message;
                FirebaseServices::sendNotification([$user->fcm_token], $userNotification);
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * sending notification
     */
}
