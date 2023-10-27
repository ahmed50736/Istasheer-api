<?php

namespace App\Jobs\User;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewUserNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try {
            $message = $firebaseHelper::getLanguageMessages();

            $getAdminIds = $firebaseHelper::getAllAdminIds();

            $notification = $firebaseHelper::notificationSetup('New user registred', $message['english']['new_user'], 'profile', $this->user->id);

            $notification['body'] = str_replace([':name'], [$this->user->name], $notification['body']);

            $firebaseHelper::storeNotification($notification, $getAdminIds);

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($getAdminIds);

            FirebaseServices::sendNotification($fcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
