<?php

namespace App\Services;

use App\Jobs\CaseResponse\UpdateCaseNotificationJob;
use App\Models\AsignAttorneyPercentage;
use App\Models\asigne_case;
use App\Models\extraService;
use App\Models\law_case;
use Carbon\Carbon;
use App\Models\UserDevice;

class CaseService
{

    public $caseInfo;

    public $caseId;

    public function createCase($data)
    {
        if (isset($data['id'])) {
            $this->caseId = $data['id'];
            unset($data['case_files']);
            $case = law_case::where('id', $data['id'])->first();
            //law_case::where('id', $this->caseId)->update($data);
            foreach ($data as $feildName => $value) {
                if ($case->$feildName !== $value) {
                    $case->$feildName = $value;
                }
            }
            if (!empty($case->getDirty())) {
                $case->update();
                UpdateCaseNotificationJob::dispatch('caseUpdate', $case);
            }
            return ['case_id' => $this->caseId];
        } else {
            $data['create_time'] = Carbon::now()->format('Y-m-d H:i:s');
            $caseData = law_case::create($data);
            return $caseData;
        }
    }

    public function asignCaseToAttorney($data)
    {
        return asigne_case::create($data);
    }

    public function asignePercntageToAttorney(array $data): object
    {
        return AsignAttorneyPercentage::create($data);
    }

    public function updatePercntageToAttorney(array $data): object
    {
        $percentage = AsignAttorneyPercentage::where('id', $data['id'])->first();
        $percentage->update($data);
        return $percentage->refresh();
    }

    /**
     * Assign other case price by admin
     * @param law_case $case
     * @return object
     */
    public function assignPriceToOtherServiceCase(law_case $case, float $ammount): object
    {
        $case->other_price = $ammount;
        return $case;
    }

    // /**
    //  * sending notification of case
    //  * @param string $adminID
    //  * @param FirebaseServices $firebase
    //  * @return bool
    //  */
    // public function sendingCaseNotificationToAdmin(string $adminID): bool
    // {
    //     $deviceData = UserDevice::select('device_uid', 'device_os', 'fcm_token', 'lang')->where('user_id', $adminID)->where('status', 1)->get()->toArray();
    //     if (!empty($deviceData)) {
    //         $fcmTokens = $deviceData->pluck('fcm_token')->toArray();
    //         FirebaseServices::sendNotification($fcmTokens, 'test', 'test');
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    /**
     * extra case service insertation
     * @param array $extraRequestData
     * @return object
     */
    public function insertExtraService(array $extraRequestData): object
    {
        return extraService::create($extraRequestData);
    }

    /**
     * Delete case
     * @param string $caseId
     * @return bool
     */
    public function deleteCase(string $caseId): bool
    {
        return law_case::where('id', $caseId)->delete();
    }
}
