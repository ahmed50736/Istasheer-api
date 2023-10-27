<?php

namespace App\Jobs\User;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\UserDevice;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlagNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId, $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $userId, string $type)
    {
        $this->userId = $userId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $userDevices = FirebaseHelper::getUserDeviceTokenAndLang($this->userId);

            $notificationMessages = FirebaseHelper::getLanguageMessages();

            $notificationTypeMessage = $this->type == 'add' ? 'add_to_flag' : 'remove_from_flag';

            $notificationDetails = FirebaseHelper::notificationSetup('Flag User', $notificationMessages['english'][$notificationTypeMessage], 'flag', $this->userId);

            FirebaseHelper::storeNotification($notificationDetails, [$this->userId]);

            foreach ($userDevices as $device) {
                $message = $device->lang == 'ar' ? $notificationMessages['arabic'][$notificationTypeMessage] : $notificationMessages['english'][$notificationTypeMessage];
                $notificationDetails['body'] = $message;
                FirebaseServices::sendNotification([$device->fcm_token], $notificationDetails);
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
