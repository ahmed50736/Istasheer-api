<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\UploadResponseRequest;
use App\Jobs\CaseResponse\UploadResponseNotification;
use App\Models\asigne_case;
use App\Models\caseresponse as ModelsCaseresponse;
use App\Models\law_case;
use Exception;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class uploadResponse extends Controller
{
    /**
     * Upload Case Response file
     * @param UploadResponseRequest $request
     * @return JsonResponse
     */
    public function uploadCaseResponse(UploadResponseRequest $request): JsonResponse
    {
        $requestData = $request->validated();

        DB::beginTransaction();
        try {
            $case = law_case::where('id', $requestData['case_id'])->first();

            if ($case) {
                $user = auth()->user();
                if ($user->user_type == 2) {
                    $checkingAssign = asigne_case::where('case_id', $case->id)->where('attorney_id', $user->id)->first();
                    if (!$checkingAssign) {
                        DB::rollBack();
                        return ApiResponse::errorResponse(400, trans('messages.unauthorized_acess'));
                    }
                }
                $createResponseData = ['attorney_id' => $user->id, 'case_id' => $case->id, 'case_no' => $case->order_no];
                foreach ($request->responsefiles as $file) {
                    $caseResponse = ModelsCaseresponse::create($createResponseData);
                    $uploadLink = Media::uploadFileToMedia($file['file'], 'CaseResponseFiles');
                    $mediaData['photo'] = $uploadLink;
                    $mediaData['file_name'] = $file['file']->getClientOriginalName();
                    $mediaData['details'] = isset($file['details']) ? $file['details'] : null;
                    $caseResponse->media()->create($mediaData);
                    $caseResponse->media->create();
                }
                DB::commit();
                UploadResponseNotification::dispatch($case);

                return ApiResponse::sucessResponse(200, [], trans('messages.file_upload'));
            } else {
                DB::rollBack();
                return ApiResponse::errorResponse(400, trans('messages.case_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete case response file
     * @authenticated
     * @group Admin
     * @param string $fileID
     * @return JsonResponse
     */
    public function delteResponseFiles(string $fileID)
    {
        try {
            return $this->deleteResponseFile($fileID);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
