<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\GuideLineRequest;
use App\Jobs\User\GuidelineCreateUpdateNotification;
use App\Models\Guideline;
use App\Services\GuidelineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuidelineController extends Controller
{
    private $guideline;

    public function __construct(Guideline $guideline)
    {
        $this->guideline = $guideline;
    }

    /**
     * create guideline
     * @param GuideLineRequest $request
     * @return JsonResponse
     */
    public function createGuideline(GuideLineRequest $request): JsonResponse
    {

        $requestData = $request->validated();
        DB::beginTransaction();
        try {

            $guideline = GuidelineService::storeGuideline($requestData);

            if ($request->hasFile('video')) {

                $uploadLink = Media::uploadVideo($requestData['video'], 'Guidelines');
                $mediaData['photo'] = $uploadLink;
                $mediaData['file_name'] = $requestData['video']->getClientOriginalName();
                $mediaData['details'] =  null;

                $guideline->media()->create($mediaData);
            }

            DB::commit();
            GuidelineCreateUpdateNotification::dispatch($guideline, 'create');
            return ApiResponse::sucessResponse(200, $guideline, trans('messages.guideline.create'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * guideline list
     * @queryParam type string The type of user. Possible values: user, admin. Default: user.
     * @return JsonResponse
     */
    public function guidelineList(Request $request): JsonResponse
    {
        try {
            if ($request->has('type')) {
                $type = $request->type;
            } else {
                $type = 'both';
            }
            $guidelineList = $this->guideline::listOfGuidelines($type);
            return ApiResponse::sucessResponse(200, $guidelineList);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * update guideline
     */
    public function updateGuideline(GuideLineRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        DB::beginTransaction();
        try {

            $guideline = GuidelineService::storeGuideline($requestData);

            if ($request->hasFile('video')) {

                $uploadLink = Media::uploadVideo($requestData['video'], 'Guidelines');
                $mediaData['photo'] = $uploadLink;
                $mediaData['file_name'] = $requestData['video']->getClientOriginalName();
                $mediaData['details'] =  null;
                $oldFile = $guideline->media->photo;
                if ($oldFile) {
                    Storage::delete($guideline->media->photo);
                }

                $guideline->media()->update($mediaData);
                GuidelineCreateUpdateNotification::dispatch($guideline, 'video');
            }

            if ($guideline->user_type == 'user' || 'both') {
                GuidelineCreateUpdateNotification::dispatch($guideline, 'update');
            }
            DB::commit();
            return ApiResponse::sucessResponse(200, $guideline, trans('messages.guideline.updadte'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
