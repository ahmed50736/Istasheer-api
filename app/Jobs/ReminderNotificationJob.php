<?php

namespace App\Jobs;

use App\Services\FirebaseServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fcmTokens, $notificationdetails;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $fcmTokens, array $notificationdetails)
    {
        $this->fcmTokens = $fcmTokens;
        $this->notificationdetails = $notificationdetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FirebaseServices::sendNotification($this->fcmTokens, $this->notificationdetails);
    }
}
