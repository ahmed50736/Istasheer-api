<?php

namespace App\Http\Controllers;

use App\Models\caseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\helpers\ApiResponse;
use Exception;
use App\helpers\ErrorMailSending;
use App\Http\Requests\ActionListSearchRequest;
use Carbon\Carbon;
use App\Http\Requests\CreateActionRequest;
use App\Http\Resources\CaseActionResource;
use App\Models\ActionAttorneyAssign;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateActionRequest;
use App\Jobs\SendActionNotification;
use Illuminate\Support\Facades\Auth;

class CaseActionController extends Controller
{
    private $caseAction;

    public function __construct()
    {
        $this->caseAction = new caseAction();
    }

    /**
     * Admin Action List
     * @group Admin
     * @authenticated
     * @param $type 'today,tommorow & week need to send as param in route
     * @return JsonResponse
     */
    public function adminActionList(ActionListSearchRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $actionList = $this->caseAction::getActionListWithPagination($requestData);
            return ApiResponse::sucessResponse(200, $actionList);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Create Actio
     * @group Admin & Attorney
     * @param CreateActionRequest $request
     * @return JsonResponse
     */
    public function createAction(CreateActionRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $data['created_by'] = $user->id;
            $data['createTime'] = Carbon::now()->format('Y-m-d H:i:s');

            $caseAction = $this->caseAction::create($data);

            $inform = isset($data['inform']) ? $data['inform'] : 0;

            //sending notification
            // if (isset($data['inform']) && $data['inform'] == 1) { ///sending notification to client about hearing process
            //     SendActionNotification::dispatch($data['case_id'], $caseAction);
            // }

            //storing attorneys information
            if (isset($data['attorney_ids']) && !empty($data['attorney_ids'])) {
                foreach ($data['attorney_ids'] as $attorneyID) {
                    if ($attorneyID != null) {
                        ActionAttorneyAssign::create(['action_id' => $caseAction->id, 'attorney_id' => $attorneyID]);
                    }
                }
            }

            DB::commit();

            SendActionNotification::dispatch($inform, $data['attorney_ids'], $data['case_id']);

            return ApiResponse::sucessResponse(200, new CaseActionResource($caseAction), trans('messages.action_create'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }


    /**
     * Update case Action
     * @authenticated
     * @group Admin & Attorney
     * @param UpdateActionRequest $request
     * @return JsonResponse
     */
    public function updateAction(UpdateActionRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        try {
            $caseAction = $this->caseAction::where('id', $data['id'])->first();

            if ($caseAction && ($caseAction->created_by == $user->id || $user->user_type == 1)) {

                $attorney_ids = $data['attorney_ids'];
                unset($data['attorney_ids']);
                $actionUpdate = $this->caseAction::where('id', $data['id'])->update($data);
                //storing attorneys information
                if (!empty($attorney_ids) && $attorney_ids != null) {
                    foreach ($attorney_ids as $attorneyID) {
                        if ($attorneyID != null) {
                            $checkAttorney = ActionAttorneyAssign::where('action_id', $data['id'])->where('attorney_id', $attorneyID)->first();
                            if (!$checkAttorney) {
                                ActionAttorneyAssign::create(['action_id' => $caseAction->id, 'attorney_id' => $attorneyID]);
                            }
                        }
                    }
                }
                DB::commit();
                return ApiResponse::sucessResponse(200, new CaseActionResource($caseAction), trans('messages.action_create'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.unauthorized_acess'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete action
     * @authenticated
     * @group Admin & Attorney
     * @param string $actionID
     * @return JsonResponse
     */
    public function deleteAction(string $actionID): JsonResponse
    {
        try {
            $check = caseAction::where('id', $actionID)->first();
            if ($check) {
                $check->delete();
                return ApiResponse::sucessResponse(201, [], trans('messages.action_delete'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Attorney action list
     * @param ActionListSearchRequest $request
     * @return JsonResponse
     */
    public function attorneyActions(ActionListSearchRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $actionList = $this->caseAction::getActionListWithPagination($requestData, auth()->id());
            return ApiResponse::sucessResponse(200, $actionList);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
