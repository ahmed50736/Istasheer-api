<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Resources\OtherProfileResource;
use App\Models\asigne_case;
use App\Models\caseresponse;
use App\Models\law_case;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Http\Requests\UploadImageRequest;

class ProfileController extends Controller
{
    /**
     * Other profile view
     * @param User $user
     * @return JsonResponse
     */
    public function otherProfile(User $user): JsonResponse
    {
        try {

            $result = [];
            if ($user) {

                $casesData = law_case::with('category', 'subcategory');

                if ($user->user_type == 3) { //user part
                    $casesData->where('uid', $user->id);
                } else if ($user->user_type == 2) { //attorney part
                    $casesData->whereHas('attorneys')->with(['attorneys' => function ($query) use ($user) {
                        $query->where('attorney_id', $user->id);
                    }]);
                    $result['service_rejected'] = caseresponse::where('attorney_id', $user->id)->where('file_staus', 2)->count();
                    $result['service_approved'] = caseresponse::where('attorney_id', $user->id)->where('file_staus', 1)->count();
                } else { //?: admin part need to impliment

                }
                $casesData = $casesData->get();

                $result['result'] = $casesData;

                //counting total task
                $result['total_task'] = count($casesData);

                //counting new cases
                $result['new_count'] = count($result['result']->where('case_status', 0));
                $result['closed_count'] = count($result['result']->where('case_status', 2));
                $result['open_count'] = count($result['result']->where('case_status', 1));

                //setup user information
                $result['user'] = $user;

                //getting 10 recent order for user & attorney
                $result['recent'] = $casesData->sortByDesc('create_time')->take(10);

                return ApiResponse::sucessResponse(200, new OtherProfileResource($result));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * view separate attorney profile details view
     * @param string $type
     * @return JsonResponse
     */
    public function attorneyDataView(string $type, string $attorneyId): JsonResponse
    {
        try {
            $attorneyChecker = User::where('id', $attorneyId)->where('user_type', 2)->first();
            if (!$attorneyChecker) {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
            $data = asigne_case::getAttorneyProfileCaseDetailsWithPagination($type, $attorneyId);

            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * viewUserOrder
     * @authenticated
     * @group commom
     * @queryParam type string The type of user. Possible values: new, open,closed Default: new.
     * @param User $user
     * @return JsonResponse
     */
    public function viewUserOrder(string $type, User $user): JsonResponse
    {
        try {
            $data = $user->getUserCasesAccordingToRequestTypeWithPagination($type);
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Upload profile Image
     * @authenticated
     * @group common
     * @param UploadImageRequest $request
     * @return Jsonresponse
     */
    public function uploadProfileImage(UploadImageRequest $request): JsonResponse
    {
        $request->validated();
        $user = User::where('id', Auth::id())->first();
        try {
            if ($request->file('photo')) { ///here will be user image options
                $uploadPath = Media::uploadFileToMedia($request->file('photo'), 'profile');
                $media['photo'] = $uploadPath;
                if ($user->media == null) {
                    $user->media()->create($media);
                } else {
                    Media::deleteFileFromMedia($user->media->photo_url, config('setting.media.disc'), config('setting.media.path'), 'profile');
                    $user->media->update(['photo' => $uploadPath]);
                }
            }
            return ApiResponse::sucessResponse(200, new UserResource($user), trans('messages.upload_message'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
