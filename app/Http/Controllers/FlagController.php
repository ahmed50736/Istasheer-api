<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DisibleUserRequest;
use App\Models\User;
use App\Models\flagUser;
use App\Http\Resources\FlagUserResouce;
use App\helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\helpers\ErrorMailSending;
use App\Jobs\User\FlagNotificationJob;
use Exception;
use Illuminate\Http\JsonResponse;

class FlagController extends Controller
{
    /**
     * Flag User
     * @authenticated
     * @group Admin
     * @param DisibleUserRequest $request
     * @return JsonResponse
     */
    public function disableUser(DisibleUserRequest $request): JsonResponse
    {
        $user = User::where('id', $request['user_id'])->first();
        $data = $request->validated();
        try {
            $data['flag_by'] = Auth::user()->id;
            if ($user && $user->user_type != 1) {
                $flagData = flagUser::create($data);
                FlagNotificationJob::dispatch($user->id, 'add');
                return ApiResponse::sucessResponse(200, new FlagUserResouce($flagData), trans('messages.flag_message', ['from' => $data['from'], 'to' => $data['to']]));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.user_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * remove user from flag
     * @param User $user
     * @return JsonResponse
     */
    public function removeUserFromFlag(User $user): JsonResponse
    {
        try {
            $checkFlag = flagUser::where('user_id', $user->id)->first();

            if ($checkFlag) {
                $checkFlag->delete();
                FlagNotificationJob::dispatch($user->id, 'remove');
                return ApiResponse::sucessResponse(200, [], trans('messages.unfalg'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.flag_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
