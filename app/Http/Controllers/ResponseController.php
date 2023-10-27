<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Http\Requests\CaseResponse\AcceptResponseRequest;
use App\Http\Requests\CaseResponse\RejectResponseFileRequest;
use App\Jobs\CaseResponse\FileAcceptRejectJob;
use App\Models\asigne_case;
use App\Models\caseresponse;
use App\Models\law_case;
use App\Models\Media;
use App\Notifications\CaseFileNotification;

class ResponseController extends Controller
{
    /**
     * accept response file 
     * @group admin/response
     * @param AcceptResponseRequest $request
     * @return JsonResponse
     */
    public function acceptResponseFile(AcceptResponseRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $mediaCheck = Media::where('id', $requestData['file_id'])->withTrashed()->first();

            if ($mediaCheck) {

                $meddiableType = $mediaCheck->mediaable_type;

                $meddiableId = $mediaCheck->mediaable_id;

                //related model data update
                $relatedModel = $meddiableType::where('id', $meddiableId)->withTrashed()->first();

                $case = law_case::where('id', $relatedModel->case_id)->first();

                if($case->deleted_at){
                    return ApiResponse::errorResponse(400, trans('messages.case_not_exist'));

                }else{
                    if ($relatedModel->deleted_at) {
                        $mediaCheck->restore();
                        $relatedModel->restore();
                    }

                    if ($relatedModel->file_staus == 1) {
                        return ApiResponse::errorResponse(400, trans('messages.file_already_accepted'));
                    }

                    $relatedModel->file_staus = 1;
                    $relatedModel->reason = $requestData['reason'];
                    $relatedModel->save();

                    FileAcceptRejectJob::dispatch($case, 'accepted', $requestData['notify_client'], $requestData['reason']);

                    return ApiResponse::sucessResponse(200, [], trans('messages.file_accept'));
                }

                

                //casedetails 
                
                
                
              
                //need to implement here if system  want to accept the whole request as law case accepted
                /* $law = law_case::where('order_no', $check->case_no)->first();
                    $law->case_status = 1;
                    $check->file_staus = 1;
                    asigne_case::where('attorney_id', $check->attorney_id)->where('case_id', $law->id)->update(['asigne_status' => 3]);
                    $law->save();
                    $check->save(); */

                  
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * reject response media file for admin
     * @group admin/response
     * @param RejectResponseFileRequest $request
     * @return JsonResponse
     */
    public function rejectReponseFile(RejectResponseFileRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $mediaCheck = Media::where('id', $requestData['file_id'])->withTrashed()->first();

            if ($mediaCheck) {

                $meddiableType = $mediaCheck->mediaable_type;

                $meddiableId = $mediaCheck->mediaable_id;

                $relatedModel = $meddiableType::where('id', $meddiableId)->withTrashed()->first();

                $case = law_case::where('id', $relatedModel->case_id)->first();

                if($case->deleted_at){
                    return ApiResponse::errorResponse(400,trans('messages.case_not_exist'));
                }else{

                    if ($relatedModel->deleted_at) {
                        $mediaCheck->restore();
                        $relatedModel->restore();
                    }
                    if($relatedModel->file_staus == 2) {
                        return ApiResponse::errorResponse(400,trans('messages.file_already_rejected'));
                    }
                    $relatedModel->file_staus = 2;
                    $relatedModel->reason = $requestData['reason'];
                    $relatedModel->save();
                    FileAcceptRejectJob::dispatch($case, 'rejected', $requestData['notify_client'], $requestData['reason']);

                    return ApiResponse::sucessResponse(200, [], trans('messages.file_reject'));
                }
               

                //need to implement here if client want to accept the whole request as law case accepted
                /* $law = law_case::where('order_no', $check->case_no)->first();
                    $law->case_status = 1;
                    $check->file_staus = 1;
                    asigne_case::where('attorney_id', $check->attorney_id)->where('case_id', $law->id)->update(['asigne_status' => 3]);
                    $law->save();
                    $check->save(); */                
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
