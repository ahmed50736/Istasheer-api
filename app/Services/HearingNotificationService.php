<?php

namespace App\Services;

use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\helpers\FutureSmsIntegration;
use App\Models\law_case;
use App\Models\User;
use App\Models\UserDevice;
use Exception;

class HearingNotificationService
{
    /**
     * Sending hearing notificatio to client as inform
     * @param string $userId
     * @return void
     */
    public static function sendHearingNotification(string $caseId, object $hearingInformation): void
    {

        $hearingMessageInformation = [];
        try {

            ///getting case information
            $caseInfo = law_case::where('id', $caseId)->first();

            //getting user information
            $user = self::findingUser($caseInfo->uid);

            //getting users all devices
            $devices = self::gettingAllUsersDevices($user->id);
            //dd($devices);
            ///arrange data for hearing notification message body
            $hearingMessageInformation['court_location'] = $caseInfo->court_location;
            $hearingMessageInformation['chamber'] = $caseInfo->chamber;
            $hearingMessageInformation['room'] = $caseInfo->room;
            $hearingMessageInformation['date'] = $hearingInformation->date;
            $hearingMessageInformation['time'] = $hearingInformation->time;
            $hearingMessageInformation['session'] = $hearingInformation->session_type;
            $hearingMessageInformation['id'] = $hearingInformation->id;


            //sending notification sms to client phone
            //self::sendingHearingNotificationToClientPhone($user->phone_no, 'test'); off for test environment

            //sending notification to all users device
            //self::sendingNotificationToAllDevice($devices, $hearingMessageInformation);
        } catch (Exception $e) {
            //sending error message to developer mail if anything wrong
            ErrorMailSending::sendingErrorMail($e);
        }
    }

    /**
     * finding UserID from caseID
     */
    public static function findingUser(string $userId): object
    {
        $user = User::select('id', 'phone_no')->where('id', $userId)->first();
        return $user;
    }

    /**
     * getting all devices of users
     */
    public static function gettingAllUsersDevices(string $userId): object
    {
        return UserDevice::select('device_os', 'device_uid', 'fcm_token', 'lang')->where('user_id', $userId)->get();
    }

    /**
     * sending hearing notification to all device
     * @param array $deviceList
     */
    public static function sendingNotificationToAllDevice(object $deviceList, array $hearingInformation)
    {
        try {
            //getting all english messages
            $englishMessages =  require resource_path("lang/en/messages.php");

            //getting all arabic messages
            $arabicMessages =  require resource_path("lang/ar/messages.php");

            //sending all notification to all valid devices
            foreach ($deviceList as $key => $device) {

                //title modified
                $title = $device->lang == 'ar' ? $arabicMessages['hearing_notification_title'] : $englishMessages['hearing_notification_title'];

                //notification message modified according to 
                $notificationMessage = $device->lang == 'ar' ? $arabicMessages['hearing_notification_message'] : $englishMessages['hearing_notification_message'];

                //assign value to that notification message body
                $notificationBody = str_replace(
                    //selecting passing value name
                    [':court', ':date', ':time', ':room', ':chamber', ':session'],
                    //asign value
                    [
                        $hearingInformation['court_location'],
                        $hearingInformation['date'],
                        $hearingInformation['time'],
                        $hearingInformation['room'],
                        $hearingInformation['chamber'],
                        $hearingInformation['session']
                    ],
                    $notificationMessage
                );

                $notificationDetails = FirebaseHelper::notificationSetup($title, $notificationBody, 'hearing', $hearingInformation['id']);
                // $notificationDetails['body'] = $notificationBody;
                // $notificationDetails['title'] = $title;
                // $notificationDetails['body'] = ['action_type' => 'hearing', 'id' => $hearingInformation['id']];
                //send fire base notification
                FirebaseServices::sendNotification([$device->fcm_token], $notificationDetails);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * sending Notification to user phone number
     * @param string $phoneNumber
     * @param string $message
     * @return void
     */
    public static function sendingHearingNotificationToClientPhone(string $phoneNumber, string $message): void
    {
        FutureSmsIntegration::sendingNotification($phoneNumber, $message);
    }
}
