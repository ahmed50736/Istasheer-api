<?php

namespace App\Jobs\CaseResponse;

use App\helpers\ErrorMailSending;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\helpers\FirebaseHelper;

class CaseCreateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $caseId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $caseId)
    {
        $this->caseId = $caseId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {


            $getAdminIds = FirebaseHelper::getAllAdminIds();

            $getDevices = UserDevice::select('fcm_token', 'lang')->whereIn('user_id', $getAdminIds)->get();

            $englishMessages =  require resource_path("lang/en/notification.php");

            $arabicMessages =  require resource_path("lang/ar/notification.php");

            //storing notification
            $notificationStoreDetails = FirebaseHelper::notificationSetup('New Case ', $englishMessages['create_case'], 'case', $this->caseId);

            FirebaseHelper::storeNotification($notificationStoreDetails, $getAdminIds);



            foreach ($getDevices as $key => $device) {

                $message = $device->lang == 'ar' ? $arabicMessages['create_case'] : $englishMessages['create_case'];
                $notificationStoreDetails['body'] = $message;
                FirebaseServices::sendNotification([$device->fcm_token], $notificationStoreDetails);
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
