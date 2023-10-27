<?php

namespace App\helpers;

use Exception;
use GuzzleHttp\Client;


class FutureSmsIntegration
{

    /*  public static $userName = 'Test_Istesheer';
    private static $password = 'Fcc123';
    private  static $accountID = 1178;
    private static $senderID = 'InfoText'; */

    public static $userName = 'usristeshr';
    private static $password = 'FG123FVHghj';
    private  static $accountID = 2377;
    private static $senderID = 'Istesheer';
    private static $apiUrl = 'https://smsapi.future-club.com/mqsmsapi/fccsmsmq.aspx';

    private static $params = [
        'IID' => self::$accountID,
        'UID' => self::$userName,
        'P' => self::$password,
        'S' => self::$senderID,
        'L' => 'L'
    ];

    /**
     * sending sms
     */
    private static function sendSmsRequest()
    {
        try {
            $client = new Client();
            $request = $client->request(
                'POST',
                self::$apiUrl,
                [
                    'form_params' => self::$params
                ]
            );

            return $request->getBody()->getContents();
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    public static function sendSMS($OTP, $mobileNumber, $type)
    {
        if ($type == 'reset') {
            $message = trans('messages.password_reset_otp_phone', ['otp' => $OTP]);
        } else if ($type == 'test') {
            $message = 'Test message from istesheer';
        } else {
            $message = trans('messages.account_verification_otp_phone', ['otp' => $OTP]);;
        }
        $client =  new Client();
        $paramss = [
            'IID' => self::$accountID,
            'UID' => self::$userName,
            'P' => self::$password,
            'G' => '965' . $mobileNumber,
            'S' => self::$senderID,
            'M' => $message,
            'L' => 'L'
        ];
        $url = "https://smsapi.future-club.com/mqsmsapi/fccsmsmq.aspx";
        $request = $client->request(
            'POST',
            $url,
            [
                'form_params' => $paramss
            ]
        );
        $response = $request->getBody()->getContents();
        return $response;
    }

    public static function sendingRegistrationCredentials(string $mobileNumber, string $userName, string $password, string $userType): void
    {
        $message = trans('messages.credentails_sending_message', ['username' => $userName, 'password' => $password, 'usertype' => $userType]);
        $client =  new Client();
        $params = [
            'IID' => self::$accountID,
            'UID' => self::$userName,
            'P' => self::$password,
            'G' => '965' . $mobileNumber,
            'S' => self::$senderID,
            'M' => $message,
            'L' => 'L'
        ];
        $url = "https://smsapi.future-club.com/mqsmsapi/fccsmsmq.aspx";

        $request = $client->request(
            'POST',
            $url,
            [
                'form_params' => $params
            ]
        );
        $response = $request->getBody()->getContents();
    }

    public static function sendingNotification(string $number, string $message)
    {
        try {

            $client =  new Client();
            $params = [
                'IID' => self::$accountID,
                'UID' => self::$userName,
                'P' => self::$password,
                'G' => '965' . $number,
                'S' => self::$senderID,
                'M' => $message,
                'L' => 'L'
            ];
            $url = "https://smsapi.future-club.com/mqsmsapi/fccsmsmq.aspx";

            $request = $client->request(
                'POST',
                $url,
                [
                    'form_params' => $params
                ]
            );
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * sending changed credentials message to attorney
     */
    public static function credentailsChangeMessageSender(string $phone, string $userName, string $password)
    {
        $message = trans('messages.attorney_credentilas_change.credentails_sending_message', ['username' => $userName, 'password' => $password]);
        self::$params['G'] = '965' . $phone;
        self::$params['M'] = $message;
        self::sendSmsRequest();
    }
}
