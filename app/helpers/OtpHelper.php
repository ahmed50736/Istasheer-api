<?php

namespace App\helpers;

use App\Mail\MyTestMail;
use Illuminate\Support\Facades\Mail;
use App\Models\otp_management;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Mail\OtpSendMailer;


class OtpHelper
{
    public static function otpGeneration()
    {
        if (config('setting.developement')) {
            return 1234;
        } else {
            return rand(1010, 9999);
        }
    }

    public static function otpMailSender($otp, $email)
    {

        Mail::to($email)->send(new OtpSendMailer($otp));
    }

    public static function otpPhoneSender($otp, $phone, $otpType)
    {
        $response = FutureSmsIntegration::sendSMS($otp, $phone, $otpType);
        return substr($response, 0, 2);
    }

    public static function otpInsert($otp, $userId, $otpType)
    {
        return otp_management::create(['otp' => $otp, 'uid' => $userId, 'otp_type' => $otpType, 'create_time' => Carbon::now()]);
    }

    /**
     * By user id we send otp to either to phone or email on account verify resend otp
     * @param $userId
     * @param $otp
     * @return string $return;
     */
    public static function otpSenderMailOrPhone(string $userId, int $otp): string
    {
        $user = User::select('email', 'phone_no')->where('id', $userId)->first();

        if ($user->email) {
            $return = 'email';
            self::otpMailSender($otp, $user->email);
        } else {
            $return = 'phone';
            //self::otpPhoneSender($otp, $user->phone_no, 'verify');            
        }
        return $return;
    }
}
