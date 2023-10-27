<?php 
namespace App\helpers;

use App\Mail\OtpSendMailer;
use App\Models\UserDevice;
use App\Models\otp_management;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class LoginHelper {
    public static function storeDeviceInfo($data){
        return UserDevice::create($data);
    }

    public static function unverifiedOtpSender($email,$userID) {
        $otp = OtpHelper::otpGeneration();
        $checkOTP = otp_management::where('uid',$userID)->where('otp_type',1)->first();
        if($checkOTP){
            $checkOTP->otp = $otp;
            $checkOTP->create_time = Carbon::now();
            $checkOTP->save();
        }else{
            otp_management::create(['otp' => $otp,'otp_type' => 1, 'uid' => $userID, 'create_time' => Carbon::now()]);
        }
        $mailDetails = [];
      Mail::to($email)->send(new OtpSendMailer($mailDetails));
    }

}