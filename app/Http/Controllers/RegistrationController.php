<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\UserRegistration;
use Illuminate\Support\Facades\DB;
use App\helpers\ApiResponse;
use App\Http\Resources\UserResource;
use App\helpers\ErrorMailSending;
use App\Http\Resources\RegistrationResource;
use App\Services\UserService;
use Exception;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create Attorney
     * @param UserRegistration $request
     * @group Admin
     * @return JsonResponse
     */
    public function attorneyRegistration(UserRegistration $request)
    {
        DB::beginTransaction();
        $data = $request->validated();
        $credentailsLogger = [];
        try {
            $data['user_type'] = 2;
            $user = $this->userService->createUser($data);

            //setting credentiallogger data
            $credentailsLogger['user_id'] = $user->id;
            $credentailsLogger['username'] = $data['username'];
            $credentailsLogger['password'] = $data['password'];

            $this->userService::storingAttorneyCredential($credentailsLogger);

            DB::commit();
            return ApiResponse::sucessResponse(200, new UserResource($user), trans('messages.create_Attorney'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Registration
     * @group auth
     * @param UserRegistration $request
     */
    public function userRegistration(UserRegistration $request)
    {
        DB::beginTransaction();
        $registerData = $request->all();
        try {
            $registerData['user_type'] = 3;
            $user = $this->userService->createUser($registerData);
            DB::commit();
            return ApiResponse::sucessResponse(200, new RegistrationResource($user), trans('messages.create_message'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Create admin
     * @authenticated
     * @group Admin
     * @param AdminCreateRequest $request
     * @return JsonResponse
     */
    public function adminRegistration(UserRegistration $request)
    {
        DB::beginTransaction();
        $auth = Auth::user();
        $requestData = $request->validated();
        try {
            if ($auth->user_type == 1) {
                $requestData['user_type'] = 4;
                $data = $this->userService->createUser($requestData);
                DB::commit();
                return ApiResponse::sucessResponse(200, new UserResource($data), trans('messages.admin_create'));
            } else {
                DB::rollBack();
                return ApiResponse::errorResponse(400, trans('messages.unauthorized_acess'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
