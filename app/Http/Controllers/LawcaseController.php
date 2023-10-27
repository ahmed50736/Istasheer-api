<?php

namespace App\Http\Controllers;

use App\Models\law_case;
use App\helpers\ApiResponse;
use Exception;
use App\helpers\ErrorMailSending;
use App\Jobs\CaseResponse\DeleteCaseNotification;
use App\Services\CaseService;
use Illuminate\Http\JsonResponse;

class LawcaseController extends Controller
{

    /**
     * delete case
     * @param string $caseID
     * @return JsonResponse
     */
    public function deleteCase(string $caseID, CaseService $caseService): JsonResponse
    {
        $user = auth()->user();
        try {
            $checkCase = law_case::where('id', $caseID)->with('attorneysWithoutTrashed')->first();

            if (!$checkCase) {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }

            if ($user->user_type == 3) {
                if ($checkCase->uid != $user->id) {
                    return ApiResponse::errorResponse(400, trans('messages.unauthorized_acess'));
                }

                if (isset($checkCase->attorneysWithoutTrashed) && $checkCase->attorneysWithoutTrashed->isNotEmpty()) {
                    return ApiResponse::errorResponse(400, trans('messages.cant_delete_case'));
                } else {
                    $caseService->deleteCase($caseID);
                    DeleteCaseNotification::dispatch($checkCase);
                    return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
                }
            } else {

                if (isset($checkCase->attorneysWithoutTrashed) && $checkCase->attorneysWithoutTrashed->isNotEmpty()) {
                    return ApiResponse::errorResponse(400, trans('messages.case_admin_message'));
                } else {
                    $caseService->deleteCase($caseID);
                    DeleteCaseNotification::dispatch($checkCase);
                    return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
                }
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
