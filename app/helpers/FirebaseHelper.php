<?php

namespace App\helpers;

use App\Models\asigne_case;
use App\Models\NotificationData;
use App\Models\User;
use App\Models\UserDevice;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FirebaseHelper
{
    /**
     * notification details setup
     * @param string $title
     * @param string $body
     * @param array $action
     * @return array
     */
    public static function notificationSetup(string $title, string $body, string $actionType, string $actionId): array
    {
        return [
            'title' => $title,
            'body' => $body,
            'action_type' => $actionType,
            'action_id' => $actionId
        ];
    }

    /**
     * find user device fcm token and lang
     * @param string $userId
     * @return object
     */
    public static function getUserDeviceTokenAndLang(string $userId): object
    {
        return UserDevice::select('fcm_token', 'lang')->where('user_id', $userId)->get();
    }

    /**
     * get notification languages messages
     * This langagues messages return notifcation messages along with english and arabic for sending notification according 
     * to device language type
     * 
     * @return array
     */
    public static function getLanguageMessages(): array
    {
        $englishMessages =  require resource_path("lang/en/notification.php");

        $arabicMessages =  require resource_path("lang/ar/notification.php");

        return [
            'english' => $englishMessages,
            'arabic' => $arabicMessages
        ];
    }

    /**
     * get admin devices id
     * @param array $adminIds
     * @return object
     */
    public static function getAllDeviceTokensAndLangByIds(array $adminIds): object
    {
        return UserDevice::select('fcm_token', 'lang')->whereIn('user_id', $adminIds)->get();
    }

    /**
     * store notification
     * @param array $notifiationData
     * @param string $userID
     * @return void
     */
    public static function storeNotification(array $notifiationData, array $userIDs): void
    {
        try {
            $data = $notifiationData;
            foreach ($userIDs as $userId) {
                $data['user_id'] = $userId;
                NotificationData::create($data);
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * get fcm tokens by users ids
     * @param array $userIds
     * @return array
     */
    public static function getUsersFcmTokens(array $userIds): array
    {
        return UserDevice::whereIn('user_id', $userIds)->pluck('fcm_token')->toArray();
    }

    /**
     * get All adminids
     * @return array
     */
    public static function getAllAdminIds(): array
    {
        return User::whereIn('user_type', [1, 4])->pluck('id')->toArray();
    }

    /**
     * get Attorneysids by caase id
     * @param string $caseId
     * @return array
     */
    public static function getAttorneysIdByCaseId(string $caseId): array
    {
        return asigne_case::where('case_id', $caseId)->pluck('attorney_id')->toArray();
    }
}
