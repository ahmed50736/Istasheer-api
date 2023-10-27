<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\CaseHelper;
use App\helpers\DirtyValueChecker;
use App\helpers\ErrorMailSending;
use App\Http\Requests\Attorney\ResetCredentialsRequest;
use App\Http\Requests\ProfilePasswordReset;
use App\Http\Requests\UpdateProfile;
use App\Http\Resources\CaseResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Jobs\User\UpdateProfileNotificationToAdmin;
use App\Models\asigne_case;
use App\Models\caseresponse;
use App\Models\law_case;
use App\Models\Media;
use App\Models\User;
use App\Services\CaseCategoryService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserToken;

class UserController extends Controller
{
    /**
     * User Profile
     * @authenticated
     * @group common
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        try {
            $check = User::where('id', Auth::id())->with('media')->first();

            $data = [];
            $data['user_info'] = $check;
            $data['current_device_info'] = $check->currentDevice();
            if ($check->user_type == 3) { //user

            } else if ($check->user_type == 2) { //attorney
                $data['profile_stats'] = asigne_case::countAttorneyProfileData($check->id);
            } else { //admin
            }
            return ApiResponse::sucessResponse(200, new UserProfileResource($data));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }





    /**
     * View other user profile
     * @authenticated
     * @group common
     * @param string $other_uid
     * @return JsonResponse
     */
    public function otherProfiles(string $other_uid)
    {
        try {
            $data = User::where('id', $other_uid)->first();
            $modifyData = [];
            if ($data && $data->user_type != 1) {
                $modifyData['user_info'] = $data;
                if ($data->user_type == 3) { //user
                    $casedata = law_case::where('uid', $data->id)->get();
                    $modifyData['cases'] = ['open' => CaseResource::collection($casedata->where('case_status', 0)), 'closed' => CaseResource::collection($casedata->where('case_status', 1))];
                } else { ///attorney

                    $caseInfo = law_case::join('asigne_cases', 'law_cases.id', 'asigne_cases.case_id')
                        ->join('case_categories', 'law_cases.category_id', 'case_categories.id')
                        ->select(DB::raw('
                                        law_cases.id as id,
                                        law_cases.order_no as order_no,
                                        case_categories.service_name as service_name,
                                        case_categories.service_title_english as category_name_english,
                                        case_categories.service_title_arabic as category_name_arabic
                                    '))
                        ->where('asigne_cases.attorney_id', $data->id)
                        ->get();

                    $modifyData['cases'] = [
                        'assigne_cases' => $caseInfo,
                        'pending' => $caseInfo->whereBetween('asigne_status', [0, 2]),
                        'over_due' => $caseInfo->where('due_date', '<', Carbon::now()->format('Y-m-d')),
                        'recent' => caseresponse::where('attorney_id', $data->id)->where('file_staus', 2)->with(['caseDetails' => function ($query) {
                            $query->select('id', 'service_name', 'order_no');
                        }])->get()

                    ];
                }
                return ApiResponse::sucessResponse(200, $modifyData);
            } else {
                return ApiResponse::otherResponse(200, 'No such user exist');
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Update profile Data
     * @authenticated
     * @group common
     * @param UpdateProfile $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfile $request): JsonResponse
    {
        $data = $request->validated();

        try {
            //offf changing phone number
            /* if ($user->phone_no == $data['phone_no']) {
                unset($data['phone_no']); 
            } */
            $user = User::where('id', Auth::id())->first();

            $data['DOB'] = isset($data['dob']) ? $data['dob'] : null;

            if (DirtyValueChecker::dirtyValueChecker($user, $data)) {

                $user->update($data);
                $user = auth()->user();
                $user->refresh();
                if ($user->user_type == 3) {
                    UpdateProfileNotificationToAdmin::dispatch($user);
                }
            }


            return ApiResponse::sucessResponse(201, new UserResource($user), trans('messages.update_message'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    public function submitedCases($type, law_case $lawCases)
    {

        try {
            $type = CaseHelper::getCaseTypeOnCaseOrders($type);
            $data = $lawCases->userSubmittedCases($type);
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    public function delete_case($case_id)
    {
        try {
            $check = law_case::where('id', $case_id)->first();
            if ($check) {
                $check->delete();
                return ApiResponse::otherResponse(200, trans('messages.delete_message'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    public function delete_image()
    {
        $user = Auth::user();
        if ($user->social_img != null) {
        } else {
        }
    }

    /**
     * Delete Account
     */
    public function deleteUser()
    {
        try {
            $user = Auth::user();
            if ($user) {
                //need to check if user is normal or not then delete every case he registred
                User::where('id', $user->id)->delete();
                return ApiResponse::sucessResponse(201, [], trans('messages.delete_message'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.user_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Metting schedule
     * @authenticated
     * @group Users
     * @return JsonResponse
     */
    public function  clientMettingSchedule()
    {
        $user = auth()->user();
        try {
            $caseIDs = law_case::where('uid', $user->id)->pluck('id')->toArray();
            $scheduleList = $user->userScheduleCalander($caseIDs);
            return ApiResponse::sucessResponse(200, $scheduleList);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete profile photo
     * @authenticated
     * @group common
     * @return JsonResponse
     */
    public function DeletePhoto(): JsonResponse
    {

        $user = Auth::user();
        try {
            if ($user->media) {
                Media::deleteFileFromMedia($user->media->photo_url, config('setting.media.disc'), config('setting.media.path'), 'profile');
                Media::where('id', $user->media->id)->forceDelete();
                return ApiResponse::sucessResponse(201, new UserResource($user), trans('messages.delete_message'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.image_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Password reset from profile
     * @authenticated 
     * @group common
     * @param ProfilePasswordReset $request
     * @return JsonResponse
     */
    public function userPasswordReset(ProfilePasswordReset $request): JsonResponse
    {
        $data = $request->validated();

        $user = Auth::user();
        try {
            if (!Hash::check($data['current_password'], $user->password)) return ApiResponse::errorResponse(400, trans('messages.wrong_current_password'));
            if ($user->login_type == 1) {
                $user->password = bcrypt($data['new_password']);
                $user->save();
                return ApiResponse::otherResponse(200, trans('messages.update_message'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.social_password_change'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }



    /**
     * Get Service list
     * @authenticated
     * @group Users
     * @param CaseCategoryService $categoryService
     * @return JsonResponse
     */
    public function serviceList(CaseCategoryService $categoryService)
    {
        try {
            $data = $categoryService->getUserServiceList();
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }



    /**
     * Delete user
     * @authenticated
     * @group common
     * @return JsonResponse
     */
    public function deleteAccount(UserService $userService): JsonResponse
    {
        try {
            return $userService->userAccountDelete();
        } catch (Exception $e) {
            return ApiResponse::exceptionMessage($e->getMessage());
        }
    }

    /**
     *  Home Data
     * @authnticated
     * @group common
     * @return JsonResponse
     */
    public function home()
    {
        try {
            $user = Auth::user();
            $homeData = $user->getHomeData();
            return ApiResponse::sucessResponse(200, $homeData);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * attoreny credentails reset
     * @group admin
     * @param ResetCredentialsRequest $request
     * @return JsonResponse
     */
    public function resetAttorneyCredentails(ResetCredentialsRequest $request): JsonResponse
    {
        $requestData = $request->validated();

        try {
            UserService::resetCredentailsAttorney($requestData);

            return ApiResponse::sucessResponse(200, [], trans('messages.attorney_credentilas_change.reset_password'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
