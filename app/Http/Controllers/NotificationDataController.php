<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Models\NotificationData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDataController extends Controller
{
    public function __construct()
    {
    }

    /**
     * notification list
     * @group Notification
     * @return JsonResponse
     */
    public function notificationList(): JsonResponse
    {
        try {
            $notifications = NotificationData::getNotificationWithPagination();
            return ApiResponse::sucessResponse(200, $notifications);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
