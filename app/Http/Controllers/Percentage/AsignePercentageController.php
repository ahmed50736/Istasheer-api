<?php

namespace App\Http\Controllers\Percentage;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Http\Requests\AsignPercentageRequest;
use App\helpers\ErrorMailSending;
use App\helpers\ApiResponse;
use App\Models\AsignAttorneyPercentage;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\JsonResponse;

class AsignePercentageController extends Controller
{
    protected $caseService, $asignAttorneyPercentage;

    public function __construct(CaseService $caseService, AsignAttorneyPercentage $asignAttorneyPercentage)
    {
        $this->caseService = $caseService;
        $this->asignAttorneyPercentage = $asignAttorneyPercentage;
    }

    /**
     * Assigne Attorney percentage to sub category
     * @param AsignPercentageRequest $request
     * @return JsonResponse
     */
    public function assigneAttorneyPercentage(AsignPercentageRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $data['admin_id'] = Auth::id();
            $chekAssignCategory = $this->asignAttorneyPercentage::where('subcategory_id', $data['subcategory_id'])->where('attorney_id', $data['attorney_id'])->first();
            if ($chekAssignCategory) {
                return ApiResponse::errorResponse(400, trans('messages.percentage_exist'));
            }
            $asignInfo = $this->caseService->asignePercntageToAttorney($data);
            return ApiResponse::sucessResponse(200, $asignInfo, trans('messages.attorney_percentage_assign'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Update Attorney Percentage
     * @authenticated
     * @group percentage
     * @param AsignPercentageRequest $request
     * @return JsonResponse
     */
    public function updateAssignAttorneyPercentage(AsignPercentageRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $updateinfo = $this->caseService->updatePercntageToAttorney($data);
            return ApiResponse::sucessResponse(200, $updateinfo, trans('messages.attorney_percentage_assign_update'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
