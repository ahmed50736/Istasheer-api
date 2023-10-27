<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\ReminderCreateRequest;
use App\Http\Requests\ReminderlistRequest;
use App\Http\Requests\UpdateReminderStatusRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminders;
use App\Services\ReminderService;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    protected $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * create reminder
     * @param ReminderCreateRequest $request
     * @return JsonResponse
     */
    public function createReminder(ReminderCreateRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $reminder = $this->reminderService->createOrUpdateReminder($requestData);
            return ApiResponse::sucessResponse(200, $reminder, trans('messages.reminder_create'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * reminder list
     * @param Request $request
     * @return JsonResponse
     */
    public function reminderList(ReminderlistRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $user = auth()->user();
            if ($user->isAttorney()) {
                $attorneyId = $user->id;
            } else {
                $attorneyId = null;
            }

            $reminderList = Reminders::getReminderList($attorneyId, $requestData['status']);

            return ApiResponse::sucessResponse(200, $reminderList);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * delete reminder
     * @param Reminders $reminder
     * @return JsonResponse
     */
    public function deleteReminder(Reminders $reminders): JsonResponse
    {

        try {
            $reminders->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.reminder_delete'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * update reminder status
     * @param UpdateReminderStatusRequest $request
     * @param Reminders $reminders
     * @return JsonResponse
     */
    public function updateReminderStatus(UpdateReminderStatusRequest $request, Reminders $reminders): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $reminders->update($requestData);
            $updateReminder = $reminders->refresh();
            return ApiResponse::sucessResponse(200, new ReminderResource($updateReminder), trans('messages.reminder_update'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
