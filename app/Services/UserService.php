<?php

namespace App\Services;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\UserDevice;
use App\helpers\OtpHelper;
use App\helpers\RegisterHelper as HelpersRegisterHelper;
use App\Jobs\CredentailsSender;
use App\Jobs\InvokeJwtTokens;
use App\Models\asigne_case;
use App\Models\CredentialLogger;
use App\Models\law_case;
use App\Models\otp_management;
use App\Models\UserToken;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class UserService
{

    protected $registerData;

    public function createUser($data)
    {
        if ($data['user_type'] == 3) { //user
            $data['verified'] = 0;
        } else { //for attorney & admin
            $data['verified'] = 1;
        }
        $this->registerData = $data;
        $this->registerData['password'] = bcrypt($data['password']);
        $user = User::where('phone_no', $data['phone_no'])->withTrashed()->first();
        $otp = OtpHelper::otpGeneration();
        if ($user) {
            $user->restore();
            if ($user->verified == 0) {
                $this->otpInsert($otp, $user, 1);
                OtpHelper::otpSenderMailOrPhone($user->id, $otp);
            } else {
                unset($this->registerData['verified']);
            }
            $user->update($this->registerData);
        } else {
            $user = User::create($this->registerData);

            $this->registerData['user_id'] = $user->id;

            if ($data['user_type'] == 3) {
                OtpHelper::otpSenderMailOrPhone($user->id, $otp);
                $this->otpInsert($otp, $user, 1);
            }
        }
        if ($data['user_type'] != 3) {
            HelpersRegisterHelper::sendingUserNameAndPasswordToUser($user, $data['username'], $data['password']);
        }

        $devicedata = $this->userDevice();
        return $user;
    }

    public function userDevice()
    {
        return UserDevice::create($this->registerData);
    }

    public function otpInsert($otp, $user, $type)
    {
        $checker = otp_management::where('uid', $user['id'])->where('otp_type', $type)->first();
        if ($checker) {
            $checker->otp = $otp;
            $checker->create_time = Carbon::now();
            $checker->save();
        } else {
            otp_management::create(['otp' => $otp, 'uid' => $user['id'], 'otp_type' => $type, 'create_time' => Carbon::now()]);
        }
        /* if($type == 1) {//for account verification
            Mail::to($user['email'])->send(new OtpSendMailer($otp));
        }else{ //for reset password

        } */
    }

    public function storeDeviceInfo($data)
    {
        $device = UserDevice::where('user_id', $data['user_id'])->where('device_uid', $data['device_uid'])->withTrashed()->first();
        if ($device) {

            if ($device->deleted_at != null) {
                $device->restore();
            }
            $device->fcm_token = $data['fcm_token'];
            $device->lang = $data['lang'];
            $device->status = 1;
            $device->save();
        } else {
            $device = UserDevice::create($data);
        }
        return $device;
    }

    public function removeDeviceInfo($userId, $deviceID)
    {
        return UserDevice::where('user_id', $userId)->where('device_uid', $deviceID)->delete();
    }

    /**
     * Account deletion process of user
     * @return boolean
     */
    public function userAccountDelete()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            otp_management::where('uid', $user->id)->forceDelete();
            if ($user->isUser()) {
                $this->userAllCaseDelete($user->id);
            } else if ($user->isAttorney()) {
                $this->deleteAssignCasesOfAttorney($user->id);
            } else {
                DB::rollBack();
                return new JsonResponse([
                    'status' => false,
                    'status_code' => 400,
                    'data' => [],
                    'messages' => trans('messages.admin_delete_account')
                ], 400);
            }
            auth()->invalidate(); // revoke current user token
            $user->delete(); // delete user
            DB::commit();
            return ApiResponse::sucessResponse(200, [], trans('messages.account_delete'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::throwExceptionMessage(trans('messages.try_again'));
        }
    }

    /**
     * Delete usr all case details
     * @param $userID userID
     * @return boolean
     */
    public function userAllCaseDelete($userID)
    {
        return law_case::where('uid', $userID)->delete();
    }

    /**
     * Delete attorney asign cases
     * @param $attorneyId
     * @return boolean
     */
    public function deleteAssignCasesOfAttorney($attorneyId)
    {
        return asigne_case::where('attorney_id', $attorneyId)->delete();
    }

    /**
     * resetting attorney credentials
     * @param array $requestData
     * @return object
     */
    public static function resetCredentailsAttorney(array $requestData)
    {
        $credentailslog = [];

        DB::beginTransaction();

        try {

            $attorney = User::where('id', $requestData['id'])->first();
            $resetCredetials = [];
            $resetCredetials['username'] = $requestData['username'];
            $resetCredetials['password'] = bcrypt($requestData['password']);
            $attorney->update($resetCredetials);
            self::invokeAllTokenOfaUser($attorney->id);

            ///setting data for storing log
            $credentailslog['user_id'] = $requestData['id'];
            $credentailslog['username'] = $requestData['username'];
            $credentailslog['password'] = $requestData['password'];
            self::storingAttorneyCredential($credentailslog);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }


        //CredentailsSender::dispatch($requestData['username'], $requestData['password']);
    }

    /**
     * store jwt login token
     * @param array $deviceInfo
     * @param string $userID
     * @param string $token
     * @return void
     */
    public static function storeLoggedinToken(array $deviceInfo, string $userID, string $token): void
    {
        try {

            $storeData = [];
            $storeData['user_id'] = $userID;
            $storeData['token'] = $token;
            $storeData['device_os'] = $deviceInfo['os'] ?? 'unknown';
            $storeData['device_id'] = $deviceInfo['id'];
            $storeData['logged_in_at'] = Carbon::now()->format('Y-m-d H:i:s');

            UserToken::create($storeData);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            throw $e;
        }
    }

    /**
     * logout all tokens of a user for reset password
     * @param object $user
     * @return void
     */
    public static function invokeAllTokenOfaUser(string $userId): void
    {
        InvokeJwtTokens::dispatch($userId);
    }

    /**
     * storing credentials for attorney
     * @param array $data
     * @return void
     */
    public static function storingAttorneyCredential(array $data): void
    {
        $logChecker = CredentialLogger::where('user_id', $data['user_id'])->first();

        if ($logChecker) {
            $logChecker->update($data);
        } else {
            CredentialLogger::create($data);
        }
    }
}
