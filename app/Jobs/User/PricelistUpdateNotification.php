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

class PricelistUpdateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $subcategoryData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subcategoryData)
    {
        $this->subcategoryData = $subcategoryData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try{
            $message = $firebaseHelper::getLanguageMessages();
            
            $notification = $firebaseHelper::notificationSetup('Case fee update',$message['english']['price_update'],'price',$this->subcategoryData->id);
            
            $notification['body'] = str_replace([':subcategory'],[$this->subcategoryData->sub_category_title_english],$notification['body']);

            $adminIds = $firebaseHelper::getAllAdminIds();

            $firebaseHelper::storeNotification($notification,$adminIds);

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($adminIds);

            FirebaseServices::sendNotification($fcmTokens,$notification);
        }catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
