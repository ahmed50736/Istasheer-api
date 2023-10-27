<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\helpers\FutureSmsIntegration;
use App\helpers\LoginHelper;
use App\helpers\OtpHelper;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AttorneyLoginRequest;
use App\Http\Requests\LoginValidation;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\PasswordChangeOtp;
use App\Http\Requests\UserRegistration;
use App\Http\Resources\LoginResource;
use App\Http\Resources\UserResource;
use App\Jobs\User\NewUserNotificationJob;
use App\Models\flagUser;
use App\Models\otp_management;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Socialite;
use Illuminate\Support\Facades\Validator;


/**
 * @group auth
 */
class LoginController extends Controller
{

    /**
     * Default login
     * @param LoginValidation $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function login(LoginValidation $request, UserService $userService)
    {
        $credentials = $request->only('username', 'password');
        DB::beginTransaction();
        try {

            if (!$token = auth()->attempt($credentials)) {
                return ApiResponse::globalResponse(false, 400, 401, trans('messages.unauthorized_user'));
            }
            $userID = Auth::id();
            $user = User::where('id', $userID)->first();
            $requestData = $request->all();
            $requestData['user_id'] = $userID;
            $data['user'] = $user;
            $data['token'] = $token;
            $userService::storeLoggedinToken(['os' => $requestData['device_os'], 'id' => $requestData['device_uid']], $user->id, $token);
            if ($user->verified != 0) {
                $requestData['fcm_token'] = $request->fcm_token ? $request->fcm_token : null;
                $requestData['lang'] = app()->getLocale();
                $deviceData = $userService->storeDeviceInfo($requestData);
                DB::commit();
                return ApiResponse::sucessResponse(201, new LoginResource($data), trans('messages.login_message'));
            } else { ////here will send mobile otp or gmail otp
                $otp = OtpHelper::otpGeneration();
                $userService->otpInsert($otp, $user, 1);
                DB::commit();
                return ApiResponse::sucessResponse(888, [], trans('messages.otp_account_send'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }


    /**
     * logout
     * @group common routes for all users
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function logout(LogoutRequest $request, UserService $userService)
    {
        try {
            $userService->removeDeviceInfo(Auth::id(), $request['device_uid']);
            auth()->logout();
            return ApiResponse::sucessResponse(200, [], trans('messages.logout_message'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * jwt token data create
     * @param $token
     * @param $message
     * @return JsonResponse
     */
    protected function createNewToken($token, $message = '')
    {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL(),
                'user' => new UserResource(auth()->user())
            ],
            'message' => $message
        ], 200);
    }

    /**
     * Social Login
     * @param string $type
     * @param string $social_token
     * @return JsonResponse
     */
    public function socialLogin($type, $social_token)
    {

        $login_type = 0;
        if ($type == 'facebook') {
            $login_type = 1;
        } else if ($type == 'google') {
            $login_type = 2;
        } else {
            $login_type = 3;
        }

        try {
            $user_check = Socialite::driver($type)->stateless()->userFromToken($social_token);
            $checking_user = User::where('social_id', $user_check->id)->where('login_type', $login_type)->first();

            if ($checking_user) {

                if (!$token = Auth::login($checking_user)) {
                    return ApiResponse::errorResponse(400, trans('messages.unauthorized_user'));
                }
                return $this->createNewToken($token);
            } else {

                $chek_deuplicate_email = User::where('email', $user_check->user['email'])->orwhere('social_email', $user_check->user['email'])->first();
                if ($chek_deuplicate_email) {
                    return ApiResponse::errorResponse(400, trans('messages.duplicate_email'));
                }
                DB::beginTransaction();
                $data = [];
                $data['name'] = $user_check->user['name'];
                $data['social_id'] = $user_check->user['id'];
                $data['social_email'] = $user_check->user['email'];
                $data['login_type'] = $login_type;

                $data['verified'] = 1;
                $user = User::create($data);

                if (!$token = Auth::login($user)) {
                    return ApiResponse::globalResponse(false, 400, 401, trans('messages.unauthorized_user'));
                }
                DB::commit();
                return $this->createNewToken($token);
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }


    public function google($driver)
    { //for testing
        return Socialite::driver($driver)->redirect();
    }

    public function callback($driver)
    { //for testing
        $user = Socialite::driver($driver)->user();
    }

    /**
     * Reset password otp sender
     * @param string $reset_type
     * @param $reset_destination
     * @return JsonResponse
     */
    public function resetPassordOtpSend($reset_type, $reset_destination)
    {
        try {
            DB::beginTransaction();
            $otp = OtpHelper::otpGeneration();
            switch ($reset_type) {
                case 'email':
                    $check_user = User::where('email', $reset_destination)->where('user_type', '!=', 2)->first();
                    if ($check_user) {
                        $this->sending_otp_to_email($check_user->email, $otp);
                        $data = OtpHelper::otpInsert($otp, $check_user->id, 2);
                        DB::commit();
                        return ApiResponse::otherResponse(888, trans('messages.otp_send', ['attribute' => 'email']));
                    } else {
                        return ApiResponse::errorResponse(400, trans('messages.recongnized_message', ['attribute' => 'email']));
                    }
                    break;
                case 'phone':
                    $check_user = User::where('phone_no', $reset_destination)->whereNot('user_type', 2)->first();
                    if ($check_user) {
                        $data = OtpHelper::otpInsert($otp, $check_user->id, 2);
                        $otpResponse = OtpHelper::otpPhoneSender($otp, $check_user->phone_no, 'reset');
                        if ($otpResponse != '00') {
                            DB::rollBack();
                            return ApiResponse::errorResponse(400, trans('messages.otp_error'));
                        }
                        DB::commit();
                        return ApiResponse::otherResponse(888, trans('messages.otp_send', ['attribute' => 'phone']));
                    } else {
                        return ApiResponse::errorResponse(400, trans('messages.recongnized_message', ['attribute' => 'phone number']));
                    }
                    break;
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Reset password
     * @param PasswordChangeOtp $request
     * @return JsonResponse
     */
    public function resetPassword(PasswordChangeOtp $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::where('id', $data['user_id'])->first();
            $user->password = bcrypt($data['password']);
            $user->save();

            otp_management::where('otp', $data['otp'])->where('uid', $data['user_id'])->where('otp_type', 2)->forceDelete();

            UserService::invokeAllTokenOfaUser($user->id);

            DB::commit();
            return ApiResponse::sucessResponse(200, [], trans('messages.password_changed'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Reset Password Otp Checker
     * @group auth
     * @urlParam otp integer required The user value. Possible values: 1234, 1235.
     * @return JsonResponse
     */
    public function otpCheck(int $otp)
    {
        try {
            DB::beginTransaction();
            $check = otp_management::where('otp', $otp)->where('otp_type', 2)->first();
            if ($check) {
                if ($check->delete_time->isPast()) {
                    $check->create_time = Carbon::now();
                    $check->otp = OtpHelper::otpGeneration();
                    $check->save();
                    DB::commit();
                    return ApiResponse::otherResponse(888, trans('messages.resend_otp', ['attribute' => trans('messages.email')]), []);
                } else {
                    DB::rollBack();
                    return ApiResponse::otherResponse(555, '', [
                        'user_id' => $check->uid,
                        'otp' => $otp
                    ]);
                }
            } else {
                DB::rollBack();
                return ApiResponse::errorResponse(400, trans('messages.wrong_otp'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Account verification
     * @group auth
     * @urlParam otp int required The otp value. Possible values: 1234, 1235.
     * @return JsonResponse
     */
    public function accountVerification(int $otp): JsonResponse
    {
        DB::beginTransaction();

        $env = env('APP_ENV');

        try {

            if ($env == 'local') {
                $OtpCheck = otp_management::where('otp', $otp)->where('otp_type', 1)->first();
            } else {
                $OtpCheck = otp_management::where('otp', $otp)->where('otp_type', 1)->first();
            }

            if ($OtpCheck) {

                if ($OtpCheck->delete_time->isPast()) {
                    $newOtp = $env == 'local' ? 1234 : OtpHelper::otpGeneration();
                    $OtpCheck->create_time = Carbon::now();
                    $OtpCheck->otp = $newOtp;
                    $OtpCheck->save();
                    DB::commit();
                    OtpHelper::otpSenderMailOrPhone($OtpCheck->uid, $newOtp);
                    return ApiResponse::otherResponse(888, trans('messages.resend_otp', ['attribute' => trans('messages.email')]), []);
                } else {
                    $user = User::where('id', $OtpCheck->uid)->first();
                    $user->verified = 1;
                    $user->save();
                    if (!$token = Auth::login($user)) {
                        DB::rollBack();
                        return ApiResponse::errorResponse(400, trans('messages.unauthorized_user'));
                    }
                    $OtpCheck->delete();
                    otp_management::whereNotNull('deleted_at')->forceDelete();
                    NewUserNotificationJob::dispatch($user);
                    DB::commit();
                    return $this->createNewToken($token);
                }
            } else {
                DB::rollBack();
                return ApiResponse::dataNotFound();
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    public function smsTest()
    {
        return FutureSmsIntegration::sendSMS(0000, 96592220333, 'test');
    }

    /**
     * admin login process
     * @param AdminLoginRequest $request
     * @return JsonResponse
     */
    public function adminLogin(AdminLoginRequest $request, UserService $userService): JsonResponse
    {
        $requestData = $request->validated();
        $credentials = $request->only('username', 'password');
        DB::beginTransaction();
        try {
            if (!$token = auth()->attempt($credentials)) {
                return ApiResponse::globalResponse(false, 400, 401, trans('messages.unauthorized_user'));
            }
            $userID = Auth::id();
            $user = User::where('id', $userID)->first();

            $requestData['user_id'] = $userID;
            $data['user'] = $user;
            $data['token'] = $token;
            $userService::storeLoggedinToken(['os' => $requestData['device_os'], 'id' => $requestData['device_uid']], $user->id, $token);
            if ($user->verified != 0) {

                $requestData['fcm_token'] = $request->fcm_token ? $request->fcm_token : null;
                $requestData['lang'] = app()->getLocale();

                $userService->storeDeviceInfo($requestData);
                DB::commit();
                return ApiResponse::sucessResponse(201, new LoginResource($data), trans('messages.login_message'));
            } else { ////here will send mobile otp or gmail otp
                $otp = OtpHelper::otpGeneration();
                $userService->otpInsert($otp, $user, 1);
                DB::commit();
                return ApiResponse::sucessResponse(888, [], trans('messages.otp_account_send'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * attorney login process
     * @param AttorneyLoginRequest $request
     * @return JsonResponse
     */
    public function attorneyLogin(AttorneyLoginRequest $request, UserService $userService): JsonResponse
    {
        $requestData = $request->validated();
        $credentials = $request->only('username', 'password');
        DB::beginTransaction();
        try {
            if (!$token = auth()->attempt($credentials)) {
                return ApiResponse::globalResponse(false, 400, 401, trans('messages.unauthorized_user'));
            }
            $userID = Auth::id();
            $user = User::where('id', $userID)->first();

            $requestData['user_id'] = $userID;
            $data['user'] = $user;
            $data['token'] = $token;
            $userService::storeLoggedinToken(['os' => $requestData['device_os'], 'id' => $requestData['device_uid']], $user->id, $token);
            if ($user->verified != 0) {

                $requestData['fcm_token'] = $request->fcm_token ? $request->fcm_token : null;
                $requestData['lang'] = app()->getLocale();

                $userService->storeDeviceInfo($requestData);

                DB::commit();
                return ApiResponse::sucessResponse(201, new LoginResource($data), trans('messages.login_message'));
            } else { ////here will send mobile otp or gmail otp
                $otp = OtpHelper::otpGeneration();
                $userService->otpInsert($otp, $user, 1);
                DB::commit();
                return ApiResponse::sucessResponse(888, [], trans('messages.otp_account_send'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
