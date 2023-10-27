<?php

namespace App\Jobs;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Models\hearings;
use App\Models\law_case;
use App\Services\FirebaseServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendingHearingNotificationBeforeOneDay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $firebaseHelper, $messages, $firebaseService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->firebaseHelper = new FirebaseHelper;
        $this->messages = $this->firebaseHelper::getLanguageMessages();
        $this->firebaseService = new FirebaseServices;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
        
            $hearingsTomorrow = hearings::with('caseInfo', 'attorneysDatas')
                ->where('date', Carbon::tomorrow()->format('Y-m-d'))
                ->whereHas('caseInfo', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->where(function ($query) {
                    $query->whereHas('attorneysDatas', function ($subquery) {
                        $subquery->whereNull('deleted_at');
                    })->orWhereDoesntHave('attorneysDatas');
                })
                ->get();

                  
            foreach($hearingsTomorrow as $hearing){

                $notification = $this->firebaseHelper::notificationSetup('Hearing Tommorow', $this->messages['english']['hearing_tommow'], 'hearing',$hearing->id);
                $notification['body'] = str_replace([':caseno'], [$hearing->caseInfo->order_no], $notification['body']);

                $attorneyIds = $hearing->attorneysDatas->pluck('attorney_id')->toArray();
                
                $userIds = array_merge($attorneyIds, [$hearing->caseInfo->uid]);
                
                $getUserFcmTokens = $this->firebaseHelper::getUsersFcmTokens($userIds);

                $this->firebaseHelper::storeNotification($notification,$userIds);

                $this->firebaseService::sendNotification($getUserFcmTokens, $notification);
            }
        } catch(Exception $e){
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
