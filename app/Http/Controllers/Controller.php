<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\OtpHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyTestMail;
use App\Models\jwttoken;
use App\Models\otp_management;

use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function sending_otp_to_phone($phone_number, $otp)
    {
    }

    public function terms_condition()
    {
        return view('component.terms');
    }

    public function sending_otp_to_email($email, $otp)
    {
        $details = [
            'title' => 'demo',
            'body' => 'this is a test mail for otp.this otp valid for 5 minutes',
            'otp' => $otp
        ];
        Mail::to($email)->send(new MyTestMail($details));
    }

    public function otp_insert($otp, $uid, $type)
    {
        otp_management::create(['otp' => $otp, 'uid' => $uid, 'otp_type' => $type, 'create_time' => Carbon::now()]);
        return true;
    }

    public function otp_confirm($otp)
    {
        $check_otp = otp_management::where('otp', $otp)->first();
        if ($check_otp) {
            $check_time = Carbon::parse($check_otp['create_time'])->addMinute(3);
            if ($check_time->isPast()) {
                $check_otp->otp = OtpHelper::otpGeneration();
                $check_otp->create_time = Carbon::now()->format('Y-m-d H:i:s');
                return ApiResponse::sucessResponse(201, $check_otp, trans('messages.otp_expired'));
            } else {
                return ApiResponse::sucessResponse(201, $check_otp, trans('messages.otp_confirmed'));
            }
        } else {
            return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
        }
    }


    public function validation_response($data)
    {
        foreach ($data as $key => $val) {
            $msg = $val[0];
            return $msg;
        }
        /* if(isset($data['email'])){
                $msg=$data['email'][0];
            }else if(isset($data['phone_no'])){
                $msg=$data['phone_no'][0];
            }else if(isset($data['name'])){
                $msg=$data['name'][0];
            }else if(isset($data['password'])){
                $msg=$data['password'][0];
            }else if(isset($data['account_type'])){
                $msg='Please Select a account type';
            }elseif(isset($data['Importance'])){
                $msg=$data['Importance'][0];
            }else if(isset($data['date'])){
                $msg=$data['date'][0];
            }else if(isset($data['time'])){
                $msg=$data['time'][0];
            }else if(isset($data['session'])){
                $msg=$data['session'][0];
            }else if(isset($data['note'])){
                $msg=$data['note'][0];
            }else if(isset($data['caseid'])){
                $msg=$data['caseid'][0];
            }else if(isset($data['decision'])){
                $msg=$data['decision'][0];
            }else if(isset($data['start'])){
                $msg=$data['start'][0];
            }elseif(isset($data['end'])){
                $msg=$data['end'][0];
            }
            else{
                $msg='there is something wrong';
            } */
        //return $msg;
    }
}
