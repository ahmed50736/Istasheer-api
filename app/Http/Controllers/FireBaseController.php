<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\NotificationRequest;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Http\JsonResponse;


class FireBaseController extends Controller
{


    /**
     * Notification send
     * @param NotificationRequest $request
     * @return JsonResponse
     */
    public function notification(NotificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $deviceData = UserDevice::select('device_uid', 'device_os', 'fcm_token', 'lang')->where('user_id', $data['user_id'])->where('status', 1)->get()->toArray();
            if (empty($deviceData)) {
                return ApiResponse::errorResponse(400, trans('messages.devices_not_found'));
            }
            $fcmTokens = $deviceData->pluck('fcm_token')->toArray();
            FirebaseServices::sendNotification($fcmTokens, 'Istesher Notification', $data['message']);
            return ApiResponse::sucessResponse(200, [], trans());
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
