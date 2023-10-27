<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\helpers\FirebaseHelper;
use App\Http\Requests\HearingSearchRequest;
use App\Models\hearings;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateHearingRequest;
use App\Http\Resources\CaseHearingResource;
use App\Jobs\SendingHearingNotification;
use App\Models\HearingAttorneys;
use App\Http\Requests\UpdateHearingRequest;
use App\Models\law_case;
use App\Services\FirebaseServices;
use App\Services\HearingNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HearingController extends Controller
{
    private $hearings;

    public function __construct()
    {
        $this->hearings = new hearings();
    }

    /**
     * admin hearing list
     * @param HearingSearchRequest $request
     * @return JsonResponse
     */
    public function hearingList(HearingSearchRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $hearings = $this->hearings::getHearingsWithPagination($requestData);
            return ApiResponse::sucessResponse(200, $hearings);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Add Hearings
     * @aunthenticated
     * @group Admin & Attorney
     * @param CreateHearingRequest $request
     * @return JsonResponse
     */
    public function addHearings(CreateHearingRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        DB::beginTransaction();
        try {                         
            $data['created_by'] = $user->id;
            $data['informe'] = isset($data['inform']) ? $data['inform'] : 0;
            $hearing = $this->hearings::create($data);

            $case = law_case::where('id', $data['case_id'])->first();

            if (!empty($data['attorney_ids']) && $data['attorney_ids'] != null) { /// storing assigned attorney to this hearing

                foreach ($data['attorney_ids'] as $attorney_id) {
                    if ($attorney_id) {
                        HearingAttorneys::create(['attorney_id' => $attorney_id, 'hearing_id' => $hearing->id]);
                    }
                }
            }
            DB::commit();

            SendingHearingNotification::dispatch($case, $data['inform'], $hearing);

            return ApiResponse::sucessResponse(201, new CaseHearingResource($hearing), trans('messages.hearing_create'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * Update Hearing
     * @authenticated
     * @group Admin & Attorney
     * @param UpdateHearingRequest $request
     * @return JsonResponse
     */
    public function updateHearing(UpdateHearingRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        try {
            $checkHearing = $this->hearings::where('id', $data['id'])->first();
            if ($checkHearing && ($checkHearing->created_by == $user->id || $user->user_type == 1)) {

                //$data['created_by'] = $user->id;
                $data['informe'] = isset($data['inform']) ? $data['inform'] : 0;

                $attorney_ids = $data['attorney_ids'];
                unset($data['inform'], $data['attorney_ids']);
                $hearing = $this->hearings::where('id', $data['id'])->update($data);

                // if (isset($data['informe']) && $data['informe'] == 1) { ///sending notification to client about hearing process on background
                //     dispatch(new SendingHearingNotification($data['case_id'], $checkHearing));
                // }

                if (!empty($attorney_ids) && $attorney_ids != null) { /// storing assigned attorney to this hearing

                    foreach ($attorney_ids as $attorney_id) {
                        if ($attorney_id) {
                            $assignCheck = HearingAttorneys::where('attorney_id', $attorney_id)->where('hearing_id', $data['id'])->first();
                            if (!$assignCheck) {
                                HearingAttorneys::create(['attorney_id' => $attorney_id, 'hearing_id' => $data['id']]);
                            }
                        }
                    }
                }
                DB::commit();

                return ApiResponse::sucessResponse(201, new CaseHearingResource($checkHearing), trans('messages.hearing_update'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * attorney hearing list
     * @param HearingSearchRequest $request
     * @return JsonResponse
     */
    public function getAttorneyHearingList(HearingSearchRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $hearings = $this->hearings::getHearingsWithPagination($requestData, auth()->id());
            return ApiResponse::sucessResponse(200, $hearings);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
