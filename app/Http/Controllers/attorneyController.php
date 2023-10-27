<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\CreateActionRequest;
use App\Http\Requests\CreateHearingRequest;
use App\Http\Requests\UpdateActionRequest;
use App\Http\Requests\UpdateHearingList;
use App\Http\Requests\UpdateHearingRequest;
use App\Http\Resources\CaseActionResource;
use App\Http\Resources\CaseHearingResource;
use App\Jobs\CaseResponse\CaseStatusUpdateNotification;
use App\Models\asigne_case;
use App\Models\caseAction;
use App\Models\hearings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\law_case;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class attorneyController extends Controller
{

  public function mytasks(law_case $cases, Request $request)
  {
    try {
      $type = $request->type ? $request->type : 0;
      $caseData = $cases->userSubmittedCases($type);
      return ApiResponse::sucessResponse(200, $caseData);
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * delete case Hearing
   * @authenticated
   * @group Admin & Attorney
   * @param string $hearingId
   * @return JsonResponse
   */
  public function deleteHearings($hearingId): JsonResponse
  {
    try {
      $check = hearings::where('id', $hearingId)->first();
      if ($check) {
        $check->delete();
        return ApiResponse::sucessResponse(200, [], trans('messages.hearing_delete'));
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * Due Assignments (Attorney)
   * @authenticated
   * @group Attorney
   * @return JsonResponse
   */
  public function dueAssignments(): JsonResponse
  {
    try {
      $attorneyID = Auth::user()->id;
      $data = asigne_case::where('attorney_id', $attorneyID)->where('due_date', '<', Carbon::now()->format('Y-m-d'))->with('caseDetails')->get();
      return ApiResponse::sucessResponse(200, $data);
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }



  public function UploadCaseResponse(Request $request, $caseId)
  {
    $validation = Validator::make($request->all(), [
      'responseFiles.*' => 'required|mimes:jpeg,png,pdf,doc,docx',
      'description' => 'string'
    ]);
    if ($validation->fails()) return ApiResponse::errorResponse(400, $validation->errors()->first());
    try {

      $checkcase = User::getCasedetails($caseId);
      if ($checkcase) {
        DB::beginTransaction();
        $data = User::uploadCaseResponse($request->all());
        DB::commit();
        return ApiResponse::sucessResponse(201, $data, trans('messages.create_message'));
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }
    } catch (Exception $e) {
      DB::rollBack();
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }




  /**
   * Update Case Action Status
   * @param $actionType
   * @param $actionId
   * @return JsonResponse
   */
  public function updateActionStatus($actionType, $actionId): JsonResponse
  {
    try {
      $actionType = strtolower($actionType);
      $checkAction = caseAction::where('id', $actionId)->first();
      switch ($actionType) {
        case 'underway':
          $value = 0;
          break;
        case 'pause':
          $value = 1;
          break;
        case 'done':
          $value = 2;
          break;
        default:
          $value = 0;
      }
      if ($checkAction) {

        if ($checkAction->actionStatus !== $value) {

          $checkAction->actionStatus = $value;
        }

        if (!empty($checkAction->getDirty())) {

          $checkAction->save();
          CaseStatusUpdateNotification::dispatch($checkAction, $actionType, request()->inform ?? 0);
        }

        return ApiResponse::sucessResponse(201, $checkAction, trans('messages.update_message'));
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * attorney data view
   * @param string $type
   * @return JsonResponse
   */
  public function attorneyDataView(string $type): JsonResponse
  {
    try {
      $data = asigne_case::getAttorneyProfileCaseDetailsWithPagination($type, auth()->id());

      return ApiResponse::sucessResponse(200, $data);
    } catch (Exception $e) {
      ErrorMailSending::sendingErrorMail($e);
      return ApiResponse::serverError();
    }
  }
}
