<?php

namespace App\Services;

use App\helpers\ErrorMailSending;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Mix;
use Illuminate\Http\JsonResponse;

class FirebaseServices
{

    /**
     * sending notification single device
     * @param string $fcmToken
     * @param string $notificationMessage
     * @param string $title
     */
    public static function sendNotification(array $fcmTokens, array $notificationDetails)
    {
        try {

            $postData = self::httpRequestSet($fcmTokens, $notificationDetails);
            self::executeFirebaseSend($postData);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }


    /**
     * setup post request data
     * @param array $tokens all fcm tokens
     * @param string $title
     * @param string $message
     * @return array
     */
    protected static function httpRequestSet(array $tokens,  $notificationDetails = []): array
    {
        $headers = [
            'Authorization' => 'key=' . config('firebase.service.server_key'),
            'Content-Type' => 'application/json'
        ];

        $body = json_encode([
            'registration_ids' => $tokens,
            'notification' => [
                'body' => $notificationDetails['body'],
                'title' => $notificationDetails['title'],
                'name' => 'Istesheer Notification',
                'action_type' => $notificationDetails['action_type'],
                'action_id' => $notificationDetails['action_id']
            ]
        ]);

        return ['headers' => $headers, 'body' => $body];
    }

    /**
     * execute sending firebase messages
     * @param array $postData
     * @return json
     */
    protected static function executeFirebaseSend(array $postData)
    {
        $client = new Client();
        $response = $client->post('https://fcm.googleapis.com/fcm/send', [
            'headers' => $postData['headers'],
            'body' => $postData['body'],
        ]);

        // You can handle the response as needed.
        return $response->getBody()->getContents();
    }
}
