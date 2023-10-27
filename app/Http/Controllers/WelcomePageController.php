<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\WelcomePageRequest;
use App\Http\Resources\SettingPageResource;
use App\Http\Resources\WelcomePageResource;
use App\Models\Media;
use App\Models\WelcomePage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * @group Admin
 * @authenticated
 */
class WelcomePageController extends Controller
{
    private $welcomePage;
    public function __construct()
    {
        $this->welcomePage = new WelcomePage();
    }

    /**
     * Get User welcome page data
     * @unauthenticated
     * @group settings
     * @queryParam type string The type of user. Possible values: user, admin,attorney Default: user.
     * @return JsonResponse
     */
    public function getWelcomePage(string $userType): object
    {
        try {
            $data = $this->welcomePage->getWelcomeData($userType);
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Create welcome page data
     * @authenticated
     * @group Admin
     * @param WelcomePageRequest $request
     * @return JsonResponse
     */
    public function createWelcomePage(WelcomePageRequest $request): JsonResponse
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $pageData = $this->welcomePage->createOrUpdatePageData($data);

            if ($request->hasFile('image')) {
                $photo['photo'] = Media::uploadFileToMedia($request->file('image'), 'welcomepage');
                $photo['file_name'] = $request->file('image')->getClientOriginalName();
                $pageData->media()->create($photo);
            }
            DB::commit();
            return ApiResponse::sucessResponse(200, new WelcomePageResource($pageData), trans('messages.create_message'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete a welcome page
     * @param $pageID
     * @return JsonResponse
     */
    public function deleteWelcomePage($pageID): JsonResponse
    {
        $data = WelcomePage::where('id', $pageID)->with('media')->first();
        if (!$data) return ApiResponse::dataNotFound();
        DB::beginTransaction();
        try {
            if ($data->media->photo_url) {
                Media::deleteFileFromMedia($data->media->photo_url, config('setting.media.disc'), config('setting.media.path'), 'welcomepage');
                $data->media->delete();
            }

            $data->delete();
            DB::commit();
            return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Update welcome page data
     * @param WelcomePageRequest $request
     * @return JsonResponse
     */
    public function updateWelcomePage(WelcomePageRequest $request): JsonResponse
    {
        DB::beginTransaction();
        $requestData = $request->validated();
        try {
            $data = WelcomePage::with('media')->where('id', $requestData['id'])->first();
            $updateData = $this->welcomePage->createOrUpdatePageData($requestData);
            if ($request->hasFile('image')) {
                if ($data->media->photo_url) {
                    Media::deleteFileFromMedia($data->media->photo_url, config('setting.media.disc'), config('setting.media.path'), 'welcomepage');
                }
                $uploadPath =  Media::uploadFileToMedia($request->file('image'), 'welcomepage');
                $data->media->update(['photo' => $uploadPath]);
            }
            DB::commit();
            return ApiResponse::sucessResponse(200, $updateData, trans('messages.update_message'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * User setting data
     * @group settings
     * @return JsonResponse
     */
    public function settingPage(): JsonResponse
    {
        try {
            $data = $this->welcomePage->getSettingPageData();
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
