<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\helpers\ErrorMailSending;
use App\helpers\ApiResponse;
use App\Models\asigne_case;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AsignCaseRequest;
use App\Http\Requests\RemoveAttorneyFromCase;
use App\Jobs\CaseResponse\AssignNotificationJob;
use App\Jobs\CaseResponse\RemoveAttorney;
use App\Services\CaseService;
use Illuminate\Http\JsonResponse;

class AsigneCaseController extends Controller
{
    /**
     * Remove attorney from a case
     * @authenticate 
     * @group Admin/case-asigne
     * @param  RemoveAttorneyFromCase $request
     * @return JsonResponse
     */
    public function removeAttorney(RemoveAttorneyFromCase $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            asigne_case::where('attorney_id', $requestData['attorney_id'])->where('case_id', $requestData['case_id'])->delete();
            RemoveAttorney::dispatch($requestData['case_id'], $requestData['attorney_id']);
            return ApiResponse::sucessResponse(200, [], trans('messages.remove_attorney'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * assign attorney to case
     * @authenticated
     * @group Admin/case-asigne
     * @param AsignCaseRequest $request
     * @return JsonResponse
     */
    public function asigneAttorneyToCase(AsignCaseRequest $request, CaseService $caseService): JsonResponse
    {
        $data = $request->validated();
        try {

            $asigneChecker = asigne_case::where('case_id', $data['case_id'])->where('attorney_id', $data['attorney_id'])->withTrashed()->first();

            if ($asigneChecker) { //restoring asign_information
                if ($asigneChecker->deleted_at) {

                    $asigneChecker->restore();

                    $asigneChecker->assign_time = Carbon::now()->format('Y-m-d H:i:s');
                    $asigneChecker->asigne_status = 0;
                    $asigneChecker->asigne_by = Auth::id();
                    $asigneChecker->save();

                    $assigneData = $asigneChecker;

                    AssignNotificationJob::dispatch(['case_id' => $data['case_id'], 'attorney_id' => $data['attorney_id'], 'type' => 'reassigned', 'notify_user' => $data['notify_user']??0]);
                } else {
                    return ApiResponse::errorResponse(400, trans('messages.case_asigned_attorney'));
                }
            } else { //creating new assigne process
                if ($asigneChecker) {
                    return ApiResponse::errorResponse(400, trans('messages.case_asigned_attorney'));
                }
                $data['assign_time'] = Carbon::now()->format('Y-m-d H:i:s');
                $data['asigne_by'] = Auth::id();
                $assigneData = $caseService->asignCaseToAttorney($data);
                AssignNotificationJob::dispatch(['case_id' => $data['case_id'], 'attorney_id' => $data['attorney_id'], 'type' => 'assigned', 'notify_user' => $data['notify_user']??0]);
            }

            return ApiResponse::sucessResponse(200, $assigneData, trans('messages.case_asigne_attorney'));
        } catch (Exception $e) {

            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
