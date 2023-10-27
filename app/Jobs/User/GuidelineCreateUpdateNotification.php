<?php

namespace App\Jobs\User;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\FirebaseServices;

class GuidelineCreateUpdateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $guideline,$actionType,$messages;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $guideline, string $actionType)
    {
        $this->guideline = $guideline;
        $this->actionType = $actionType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseHelper $firebaseHelper)
    {
        try{
            $userIds = User::where('user_type',3)->pluck('id')->toArray();

            $fcmTokens = $firebaseHelper::getUsersFcmTokens($userIds);

            $this->messages = $firebaseHelper::getLanguageMessages();

            $details = $this->setTitleAndBody();

            $notification = $firebaseHelper::notificationSetup($details['title'], $details['body'],'guideline',$this->guideline->id);

            $firebaseHelper::storeNotification($notification,$userIds);

            FirebaseServices::sendNotification($fcmTokens,$notification);
            
        }catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * notification title and body set by action type
     */
    private function setTitleAndBody():array
    {
        switch($this->actionType){
            case 'video':
                    $body = 'update_guideline_video';
                    $title = 'Guideline video updated';
                break;
            case 'create':
                $body = 'guideline_create';
                $title = 'New Guideline';
                break;
            case 'update':
                $body = 'guideline_update';
                $title = 'Guideline Update';
                break;
        }

        return [
            'body' => $this->messages['english'][$body],
            'title' => $title
        ];

    }
}
