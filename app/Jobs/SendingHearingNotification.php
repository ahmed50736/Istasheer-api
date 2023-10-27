<?php

namespace App\Jobs;

use App\helpers\ErrorMailSending;
use App\Services\HearingNotificationService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FirebaseServices;
use App\helpers\FirebaseHelper;
use App\Models\asigne_case;
use App\Models\UserDevice;
use Exception;

class SendingHearingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseDetails, $inform;

    private  $hearingInformation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $caseDetails, int $inform,  object $hearingInformation)
    {
        $this->caseDetails = $caseDetails;
        $this->hearingInformation = $hearingInformation;
        $this->inform = $inform;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try {
            $attorneysIds = $firebaseHelper::getAttorneysIdByCaseId($this->caseDetails->id);
            $messages = $firebaseHelper::getLanguageMessages();
            if ($this->inform == 1) {
                array_push($attorneysIds, $this->caseDetails->uid);
            }

            $notificationDetails = $firebaseHelper::notificationSetup('New Hearing', $messages['english']['hearing_notification_message'], 'hearing', $this->hearingInformation->id);

            $notification = $this->notificationMessageSetup($notificationDetails);

            $firebaseHelper::storeNotification($notification, $attorneysIds);

            $fcmTokens = UserDevice::whereIn('user_id', $attorneysIds)->pluck('fcm_token')->toArray();

            FirebaseServices::sendNotification($fcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * send attorney notifications
     * @param array $notification
     * @return array
     */
    private function notificationMessageSetup(array $notification): array
    {
        $notification['body'] = str_replace(
            //selecting passing value name
            [':caseno', ':court', ':date', ':time', ':room', ':chamber', ':session'],
            //asign value
            [
                $this->caseDetails->order_no,
                $this->hearingInformation['court_location'],
                $this->hearingInformation['date'],
                $this->hearingInformation['time'],
                $this->hearingInformation['room'],
                $this->hearingInformation['chamber'],
                $this->hearingInformation['session']
            ],
            $notification['body']
        );

        return $notification;
    }
}
