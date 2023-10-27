<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\helpers\smsintegration;
use App\Models\User;
use Exception;
use App\helpers\ErrorMailSending;
use App\helpers\FutureSmsIntegration;
use App\helpers\OtpHelper;

class SmsController extends Controller
{

    public function sendOtp(Request $request){
        try {

            $response = array();
            $userId = Auth::user()->id;
        
            $users = User::where('id', $userId)->first();
        
            if ( isset($users['mobile']) && $users['mobile'] =="" ) {
                $response['error'] = 1;
                $response['message'] = 'Invalid mobile number';
                $response['loggedIn'] = 1;
            } else {
                $otp = rand(100000, 999999);
                $MSG91 = new smsintegration();
        
                $msg91Response = $MSG91->sendSMS($otp,$users['mobile']);
        
                if($msg91Response['error']){
                    $response['error'] = 1;
                    $response['message'] = $msg91Response['message'];
                    $response['loggedIn'] = 1;
                }else{
        
                    Session::put('OTP', $otp);
        
                    $response['error'] = 0;
                    $response['message'] = 'Your OTP is created.';
                    $response['OTP'] = $otp;
                    $response['loggedIn'] = 1;
                }
            }
            return ApiResponse::sucessResponse(200,$response);
        }catch (Exception $e){
            ErrorMailSending::sendErrorMailToDev($e->getMessage(),$e->getFile(),$e->getLine());
            return ApiResponse::serverError();
        }
    }

    public function verifyOtp(Request $request){

        $response = array();
    
        $enteredOtp = $request->input('otp');
        $userId = Auth::user()->id;  //Getting UserID.
    
        if($userId == "" || $userId == null){
            $response['error'] = 1;
            $response['message'] = 'You are logged out, Login again.';
            $response['loggedIn'] = 0;
        }else{
            $OTP = $request->session()->get('OTP');
            if($OTP === $enteredOtp){
    
                // Updating user's status "isVerified" as 1.
    
                User::where('id', $userId)->update(['isVerified' => 1]);
    
                /* //Removing Session variable
                Session::forget('OTP'); */
    
                $response['error'] = 0;
                $response['isVerified'] = 1;
                $response['loggedIn'] = 1;
                $response['message'] = "Your Number is Verified.";
            }else{
                $response['error'] = 1;
                $response['isVerified'] = 0;
                $response['loggedIn'] = 1;
                $response['message'] = "OTP does not match.";
            }
        }
        return response()->json(($response));
    }

    public function sendSMS(Request $request){
        $otp = OtpHelper::otpGeneration();
        $number = '96592220333';
        return FutureSmsIntegration::sendSMS($otp,$number);
    }
    
    
}
