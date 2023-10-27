<?php

namespace App\Http\Controllers;

use App\Http\Traits\caseHearings;
use App\Models\asigne_case;
use App\Models\casefile;
use App\Models\law_case;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\adminTrait;
use Exception;
use App\helpers\ErrorMailSending;
use App\helpers\ApiResponse;
use App\helpers\CaseHelper;
use App\Http\Requests\CaseRequest;
use App\Http\Requests\CaseVoiceStoreRequest;
use App\Http\Requests\ExtraServiceRequest;
use App\Http\Resources\CaseDetailsViewResource;
use App\Http\Resources\CaseExtraServiceResource;
use App\Http\Resources\CaseResource;
use App\Http\Resources\FileResource;
use App\Jobs\CaseResponse\CaseCreateNotification;
use App\Jobs\CaseResponse\CompleteCaseJobNotification;
use App\Jobs\CaseResponse\ExtraServiceNotificationJob;
use App\Jobs\CaseResponse\VoiceUploadeNotificationJob;
use App\Models\CaseAudios;
use App\Models\Media;
use App\Services\CaseCategoryService;
use App\Services\CaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class caseController extends Controller
{
  use caseHearings, adminTrait;

  private $caseService;

  public function __construct()
  {
    $this->caseService = new CaseService();
  }

  /**
   * delete case
   * @param string $caseID
   * @return JsonResponse
   */
  public function deleteCase(string $caseID, CaseService $caseService): JsonResponse
  {
    $user = auth()->user();
    try {
      $checkCase = law_case::where('id', $caseID)->with('attorneys')->first();
      if ($checkCase) {
        $checkCase->setRelation('attorneys', $checkCase->attorneys()->withoutTrashed()->get());
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }

      if ($user->user_type == 3) {
        if ($checkCase->uid != $user->id) {
          return ApiResponse::errorResponse(400, trans('messages.unauthorized_acess'));
        }

        if (isset($checkCase->attorneys) && $checkCase->attorneys->isNotEmpty()) {
          return ApiResponse::errorResponse(400, trans('messages.cant_delete_case'));
        } else {
          $caseService->deleteCase($checkCase->id);
          return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
        }
      } else {

        if (isset($checkCase->attorneys) && $checkCase->attorneys->isNotEmpty()) {
          return ApiResponse::errorResponse(400, trans('messages.case_admin_message'));
        } else {
          $caseService->deleteCase($checkCase->id);
          return ApiResponse::sucessResponse(200, [], trans('messages.delete_message'));
        }
      }
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * upload case audio to a specific case for all user
   * @authneticated
   * @group common
   * @param CaseVoiceStoreRequest $request
   * @return JsonResponse
   */
  public function storeVoice(CaseVoiceStoreRequest $request): JsonResponse
  {
    $data = $request->validated();
    try {
      $mediaData['photo'] = Media::uploadFileToMedia($request->file('case_voice'), 'CaseAudios');
      $mediaData['file_name'] = $request->file('case_voice')->getClientOriginalName();
      $caseAudio = CaseAudios::create(['case_id' => $data['case_id'], 'create_time' => Carbon::now()->format('Y-m-d H:i:s')]);
      $caseAudio->media()->create($mediaData);
      $data['case_voice'] = new FileResource($caseAudio);

      VoiceUploadeNotificationJob::dispatch($data['case_id']);

      return ApiResponse::sucessResponse(201, $data, trans('messages.upload_message'));
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * Update case
   * @authenticated
   * @group Admin & User
   * @param CaseRequest $request
   * @return JsonResponse
   */
  public function updateCaseAdminUser(CaseRequest $request, CaseService $caseService): JsonResponse
  {
    $data = $request->validated();

    DB::beginTransaction();
    try {
      $checkCase = law_case::where('id', $data['id'])->withTrashed()->first();
      if ($checkCase->deleted_at != null) {
        $checkCase->restore();
      }

      unset($data['case_voice']);

      if ($data['category_id'] == CaseCategoryService::OTHER_SERVICE_ID) {
        //on create case send notify to admin
        unset($data['subcategory_id']);
      }

      $caseService->createCase($data);
      $updateData = law_case::where('id', $data['id'])
        ->with('attachments', 'hearings', 'responseFiles', 'attorneys', 'extraServices', 'audios', 'category')->first();

      //storing case files
      if ($request->has('case_files')) {
        foreach ($request->case_files as $filedetails) {
          $uploadLink = Media::uploadFileToMedia($filedetails['file'], 'CaseUploadFiles');
          $mediaData['photo'] = $uploadLink;
          $mediaData['file_name'] = $filedetails['file']->getClientOriginalName();
          $mediaData['details'] = isset($filedetails['details']) ? $filedetails['details'] : null;
          $caseFilesData['case_id'] = $checkCase->id;
          $caseFile = casefile::create($caseFilesData);
          $caseFile->media()->create($mediaData);
        }
      }

      //storing case voice file
      if ($request->hasFile('case_voice')) {
        $audioLink = Media::uploadFileToMedia($request->file('case_voice'), 'CaseAudios');
        $mediaData['photo'] = $audioLink;
        $mediaData['file_name'] = $request->file('case_voice')->getClientOriginalName();
        $audio = CaseAudios::create(['case_id' => $checkCase->id, 'create_time' => Carbon::now()->format('Y-m-d H:i:s')]);
        $audio->media()->create($mediaData);
      }
      DB::commit();
      return ApiResponse::sucessResponse(201, new CaseResource($updateData), trans('messages.update_message'));
    } catch (Exception $e) {
      DB::rollBack();
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * create case
   * @authenticate
   * @group Users
   * @param CaseRequest $request
   * @param CaseService $caseService
   * @return JsonResponse
   */
  public function createCase(CaseRequest $request, CaseService $caseService)
  {

    $caseData = $request->validated();

    DB::beginTransaction();

    try {
      if (!$request->id) {
        $caseData['order_no'] = CaseHelper::createOrderNumber();
        $caseData['uid'] = Auth::id();
      } else {
        $caseExistData = law_case::where('id', $request->id)->first();
        $caseData['order_no'] = $caseExistData->order_no;
      }

      unset($caseData['case_voice']);

      if ($caseData['category_id'] == CaseCategoryService::OTHER_SERVICE_ID) {
        //on create case send notify to admin
        unset($caseData['subcategory_id']);
      }

      $data = $caseService->createCase($caseData);

      //sending notification on create case
      CaseCreateNotification::dispatch($data->id);

      //storing case files
      if ($request->has('case_files')) {
        foreach ($request->case_files as $filedetails) {
          $uploadLink = Media::uploadFileToMedia($filedetails['file'], 'CaseUploadFiles');
          $mediaData['photo'] = $uploadLink;
          $mediaData['file_name'] = $filedetails['file']->getClientOriginalName();
          $mediaData['details'] = isset($filedetails['details']) ? $filedetails['details'] : null;
          $caseFilesData['case_id'] = $data->id;
          $caseFile = casefile::create($caseFilesData);
          $caseFile->media()->create($mediaData);
        }
      }

      //storing case voice file
      if ($request->hasFile('case_voice')) {
        $audioLink = Media::uploadFileToMedia($request->file('case_voice'), 'CaseAudios');
        $mediaData['photo'] = $audioLink;
        $mediaData['file_name'] = $request->file('case_voice')->getClientOriginalName();
        $audio = CaseAudios::create(['case_id' => $data->id, 'create_time' => Carbon::now()->format('Y-m-d H:i:s')]);
        $audio->media()->create($mediaData);
      }

      DB::commit();
      return ApiResponse::sucessResponse(200, new CaseResource($data), trans('messages.case_create'));
    } catch (Exception $e) {
      DB::rollBack();
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * view case details 
   * @param string $case_id
   * @return JsonResponse
   */
  public function viewCase(string $case_id)
  {
    $user = auth()->user();
    try {
      $data = law_case::where('id', $case_id)
        ->with('attachments', 'responseFiles', 'attorneys', 'extraServices', 'audios', 'category');
      if ($user->isAdmin() || $user->isSuperAdmin()) {
        $data = $data->withTrashed()->first();
      } else {
        $data = $data->first();
      }

      if ($data) {
        if ($user->isAdmin() || $user->isSuperAdmin()) { // admin case status part update

          if ($data->case_status == 0) {
            $data->case_status = 1;
            $data->save();
          }
        }
        if ($user->isAttorney()) { //attorney case satus part update

          $checkCaseStatus = asigne_case::where('case_id', $data->id)->where('attorney_id', $user->id)->first();
          if ($checkCaseStatus) {

            if ($checkCaseStatus->asigne_status == 0) {
              $checkCaseStatus->asigne_status = 1;
              $checkCaseStatus->save();
            }
          }
        }

        return ApiResponse::sucessResponse(200, new CaseDetailsViewResource($data));
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * Attorney hearing List
   * @authenticated
   * @group Attorney
   * @return JsonResponse
   */
  public function getAttorneyHearingList()
  {
    try {
      $userId = Auth::user()->id;
      $data = $this->getHearingAttorney($userId);
      return ApiResponse::sucessResponse(200, $data);
    } catch (Exception $e) {
      DB::rollBack();
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * Get hearing list
   * @authenticated
   * @group Admin
   * @return JsonResponse
   */
  public function adminHearingList()
  {
    try {
      $data = $this->getAdminHearingList();
      return ApiResponse::sucessResponse(200, $data);
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * Case Extra service create
   * @authenticated
   * @group Users
   * @param ExtraServiceRequest $request
   * @return JsonResponse
   */
  public function extraServiceCreate(ExtraServiceRequest $request): JsonResponse
  {
    DB::beginTransaction();
    $data = $request->validated();
    $caseData = law_case::where('id', $data['case_id'])->first();
    try {
      //assign order id for creating extra order
      $data['extra_order_no'] = CaseHelper::createExtraServiceOrderNo($data['case_id']);

      //create extra service for case
      $extraserviceData = $this->caseService->insertExtraService($data);

      ///inserting audo file on case details 
      if ($request->hasFile('audio')) {
        $audioLink = Media::uploadFileToMedia($request->file('audio'), 'CaseAudios');
        $mediaData['photo'] = $audioLink;
        $mediaData['file_name'] = $request->file('audio')->getClientOriginalName();
        $audio = CaseAudios::create(['case_id' => $caseData->id, 'create_time' => Carbon::now()]);
        $audio->media()->create($mediaData);
      }

      ///uploading case files
      if ($request->has('case_files')) {
        foreach ($request->case_files as $file) {
          if (isset($file['file']) && $file['file']->hasFile()) {
            $mediaData['photo'] = Media::uploadFileToMedia($file['file'], 'CaseUploadFiles');
            $mediaData['file_name'] = $file['file']->getClientOriginalName();
            $mediaData['details'] = isset($file['details']) ? $file['details'] : null;
            $caseFilesData['case_id'] = $caseData->id;
            $caseFile = casefile::create($caseFilesData);
            $caseFile->media()->create($mediaData);
          }
        }
      }
      ExtraServiceNotificationJob::dispatch($data['case_id']);
      DB::commit();
      return ApiResponse::sucessResponse(200, new CaseExtraServiceResource($extraserviceData), trans('messages.extra_service_create'));
    } catch (Exception $e) {
      DB::rollBack();
      ErrorMailSending::sendingErrorMail($e);
      return ApiResponse::serverError();
    }
  }

  /**
   * Get all Cases
   * @authenticated
   * @group common
   * @param $type
   * @param law_case $cases
   * @return JsonResponse
   */
  public function allCases($type, law_case $cases)
  {
    try {
      switch ($type) {
        case 'open':
          $type = 1;
          break;
        case 'closed':
          $type = 2;
          break;
        case 'new':
          $type = 0;
          break;
        default:
          $type = 1;
      }
      $caseData =  $cases->userSubmittedCases($type);
      return ApiResponse::sucessResponse(200, $caseData);
    } catch (Exception $e) {
      ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
      return ApiResponse::serverError();
    }
  }

  /**
   * update case status to closed
   * @param $statusType open for open case close for close case
   * @param $caseId law case id
   * @return JsonResponse
   */
  public function updateCaseStatus(string $statusType, string $caseId): JsonResponse
  {
    try {
      switch ($statusType) {
        case 'open':
          $status = 1;
          $statusName = 'open';
          break;
        case 'close':
          $status = 2;
          $statusName = 'closed';
          break;
        default:
          $status = 1;
          $statusName = 'open';
      }
      $case = law_case::where('id', $caseId)->first();
      if ($case) {
        //update case status of attorney also
        if ($status == 2) {

          asigne_case::where('case_id', $caseId)->update(['asigne_status' => 2]);
        }
        if ($case->case_status == $status) {
          return ApiResponse::errorResponse(400, trans('messages.case_status_already_close', ['status' => $statusName]));
        } else {
          $case->case_status = $status;
          $case->save();

          if ($status == 2) {
            CompleteCaseJobNotification::dispatch($caseId, $case->uid);
          }
          return ApiResponse::sucessResponse(200, [], trans('messages.case_status_close', ['status' => $statusName]));
        }
      } else {
        return ApiResponse::errorResponse(400, trans('messages.data_not_found'));
      }
    } catch (Exception $e) {
      ErrorMailSending::sendingErrorMail($e);
      return ApiResponse::serverError();
    }
  }

  /**
   * get admin service list
   */
  public function adminServiceList(string $type)
  {
    try {
      switch ($type) {
        case 'accepted':
          $type = 'accepted';
          break;
        case 'rejected':
          $type = 'rejected-service';
          break;
        default:
          $type = 'accepted';
      }

      $list = asigne_case::getAttorneyProfileCaseDetailsWithPagination($type, null);
      return ApiResponse::sucessResponse(200, $list);
    } catch (Exception $e) {
      ErrorMailSending::sendingErrorMail($e);
      return ApiResponse::serverError();
    }
  }
}
