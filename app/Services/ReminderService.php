<?php

namespace App\Services;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Jobs\ReminderNotificationJob;
use App\Models\Reminders;
use App\Models\UserDevice;
use Carbon\Carbon;
use Exception;

class ReminderService
{
    protected $reminder;

    public function __construct(Reminders $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * create or update main function for reminder service
     * @param array $requestData
     * @return object
     */
    public function createOrUpdateReminder(array $requestData): object
    {
        try {

            $reminder = $this->createReminder($requestData);

            return $reminder;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * create reminders
     * @param array $data
     * @return object
     */
    protected function createReminder(array $data): object
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $reminder = $this->reminder::create($data);
        $notificationsDetails = self::sendingNotification($data['attorney_id'], $data['note'], $reminder->id);

        ReminderNotificationJob::dispatch($notificationsDetails['fcm_token'], $notificationsDetails['notification']);
        return $reminder;
    }


    /**
     * sending notification for job of create reminder
     * @param string $attorneyId
     * @param string $message
     * @return void
     */
    public static function sendingNotification(string $attorneyId, string $message, $reminderID): array
    {
        try {
            $userDevices = self::getAttorneysDevices($attorneyId);
            $fcmTokens = $userDevices->pluck('fcm_token')->toArray();
            //notification details
            $notificationDetails = FirebaseHelper::notificationSetup('case Reminder', $message, 'reminder', $reminderID);

            FirebaseHelper::storeNotification($notificationDetails, [$attorneyId]);

            //sending notification to all devices
            // FirebaseServices::sendNotification($fcmTokens, $notificationDetails);
            return ['fcm_token' => $fcmTokens, 'notification' => $notificationDetails];
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ['fcm_token' => [], 'notification' => []];
        }
    }

    protected static function getAttorneysDevices(string $attorneyId): object
    {
        return UserDevice::select('device_uid', 'fcm_token')->where('status', 1)->where('user_id', $attorneyId)->get();
    }
}
