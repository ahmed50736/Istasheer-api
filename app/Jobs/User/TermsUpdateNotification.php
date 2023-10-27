<?php

namespace App\Jobs\User;

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

class TermsUpdateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $terms;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $terms)
    {
        $this->terms = $terms;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try{
            $messages= $firebaseHelper::getLanguageMessages();

            $adminAndAttorneyIds = User::whereIn('user_type',[1,2,4])->pluck('id')->toArray();

            $notification = $firebaseHelper::notificationSetup('Terms Updated', $messages['english']['terms_update'], 'terms',$this->terms->id);

            $firebaseHelper::storeNotification($notification, $adminAndAttorneyIds);

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($adminAndAttorneyIds);

            FirebaseServices::sendNotification($fcmTokens, $notification);

        }catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
