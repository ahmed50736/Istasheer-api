<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\DeleteFileRequest;
use App\Http\Requests\UploadSeparateFile;
use App\Jobs\CaseResponse\UpdateCaseNotificationJob;
use App\Jobs\CaseResponse\UploadResponseNotification;
use App\Models\Media;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\casefile;
use App\Models\law_case;

/**
 * @authenticated
 * @group common
 */
class CommonController extends Controller
{
    /**
     * Delete Files
     * @param DeleteFileRequest $request
     * @return JsonResponse
     */
    public function deleteFiles(DeleteFileRequest $request): JsonResponse
    {
        $data = $request->all();

        DB::beginTransaction();
        try {
            foreach ($data['file_ids'] as  $fileId) {
                // Step 1: Find the media file and its associated meddiable relationship
                $media = Media::where('id', $fileId)->first();

                $meddiableType = $media->mediaable_type;

                $meddiableId = $media->mediaable_id;

                // Step 2: Delete the associated meddiable relationship
                $relatedModel = $meddiableType::where('id', $meddiableId)->first();
                if ($relatedModel) {
                    $relatedModel->delete();
                    $media->delete();
                    DB::commit();
                    return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
                } else {
                    DB::rollBack();
                    return ApiResponse::errorResponse(400, trans('messages.already_deleted'));
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Upload case files separedted
     * @authnticated
     * @group common
     * @param UploadSeparateFile $request
     */
    public function uploadSeparateFiles(UploadSeparateFile $request)
    {
        $data = $request->validated();
        try {

            if ($request->has('case_files')) {
                $case = law_case::where('id', $data['id'])->first();

                foreach ($request->case_files as $filedetails) {
                    $uploadLink = Media::uploadFileToMedia($filedetails['file'], 'CaseUploadFiles');
                    $mediaData['photo'] = $uploadLink;
                    $mediaData['file_name'] = $filedetails['file']->getClientOriginalName();
                    $mediaData['details'] = isset($filedetails['details']) ? $filedetails['details'] : null;
                    $caseFilesData['case_id'] = $data['id'];
                    $caseFile = casefile::create($caseFilesData);
                    $caseFile->media()->create($mediaData);
                }

                UpdateCaseNotificationJob::dispatch('fileUpload', $case);
            }
            return ApiResponse::sucessResponse(200, [], trans('messages.file_upload'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
