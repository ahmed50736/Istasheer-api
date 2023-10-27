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

class UpdateProfileNotificationToAdmin implements ShouldQueue
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
            $getAdminIds = $firebaseHelper::getAllAdminIds();

            $messages = $firebaseHelper::getLanguageMessages();

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($getAdminIds);

            $notification = $firebaseHelper::notificationSetup('user profile Update', $messages['english']['profile_update'], 'profile', $this->user->id);

            $notification['body'] = str_replace([':username'], [$this->user->name], $notification['body']);

            $firebaseHelper::storeNotification($notification, $getAdminIds);

            FirebaseServices::sendNotification($fcmTokens, $notification);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
