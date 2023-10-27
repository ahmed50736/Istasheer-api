<?php

namespace App\Http\Traits;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Models\aboutus;
use App\Models\asigne_case;
use App\Models\caseAction;
use App\Models\casefile;
use App\Models\caseresponse;
use App\Models\flagUser;
use App\Models\hearings;
use App\Models\law_case;
use App\Models\Reminders;
use App\Models\terms;
use App\Models\uploadResponse;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

trait adminTrait
{

    public function deleteVoucher($voucherID)
    {
        $check = Voucher::where('id', $voucherID)->first();
        if ($check) {
            $check->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.voucher_delete'));
        } else {
            return ApiResponse::errorResponse(200, trans('messages.voucher_not_found'));
        }
    }

    public function updateTerms($data, $termsID)
    {
        $checkTerms = terms::where('id', $termsID)->first();
        if ($checkTerms) {
            $checkTerms->details = $data;
            $checkTerms->save();
            return ApiResponse::sucessResponse(200, $checkTerms, trans('messages.terms_update'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.terms_not_found'));
        }
    }

    public function updateAbout($data)
    {
        $checkAbout = aboutus::where('id', $data['id'])->first();
        if ($checkAbout) {
            $checkAbout->details = $data['details'];
            $checkAbout->save();
            return ApiResponse::sucessResponse(200, $checkAbout, trans('messages.about_update'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.about_not_found'));
        }
    }

    public function voucherCreate($data)
    {

        return Voucher::create(['voucher_number' => $data['code'], 'amount' => $data['amount'], 'createTime' => Carbon::now()->format('Y-m-d H:i:s')]);
    }

    public function updateVoucher($data, $voucherID)
    {
        $voucherCheck = Voucher::where('id', $voucherID)->first();
        if ($voucherCheck) {
            $voucherCheck->voucher_number = $data['Code'];
            $voucherCheck->ammount = $data['Ammount'];
            $voucherCheck->save();
            return ApiResponse::sucessResponse(200, $voucherCheck, trans('messages.voucher_update'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.voucher_not_found'));
        }
    }

    /**
     * Delete Attorney
     * @authenticated
     * @group Admin
     * @param string $attorneyID
     * @return JsonResponse
     */
    public function deleteAttorney(string $attorneyID)
    {
        DB::beginTransaction();
        try {
            $check = User::where('id', $attorneyID)->where('type', 2)->first();
            if ($check) {
                asigne_case::where('attorney_id', $check->id)->delete();
                caseresponse::where('attorney_id', $attorneyID)->delete();
                uploadResponse::where('attorney_id', $attorneyID)->delete();
                $check->delete();
                DB::commit();
                return ApiResponse::sucessResponse(200, [], trans('messages.delete_Attorney'));
            } else {
                DB::rollBack();
                return ApiResponse::errorResponse(400, trans('messages.attorney_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    public function createAdminBySuperAdmin($data)
    {
        $user = [];
        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        $user['phone_no'] = $data['phone'];
        $user['login_type'] = 1;
        $user['verified'] = 1;
        $user['password'] = bcrypt($data['password']);
        $user['user_type'] = 4;
        $user['other_info'] = $data['other'];
        $user['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $user['notes'] = $data['note'];
        $user = User::create($user);
        return ApiResponse::sucessResponse(200, $user, trans('messages.admin_create'));
    }

    public function flagUser(array $requesData, $userID)
    {

        $data = [];
        $data['user_id'] = $userID;
        $data['from'] = $requesData['from'];
        $data['to'] = $requesData['to'];
        $data['flag_by'] = Auth::user()->id;
        $flagcheck = flagUser::where('user_id', $userID)->first();
        if ($flagcheck) {
            $flagcheck->delete();
        }
        $flagData = flagUser::create($data);
        return $flagData;
        // return ['status_code'=>200,'message'=>'successfully Flagged user from '.$requesData["from"].' to '.$requesData["to"]];
    }

    public function unFlagUser($flagID)
    {
        $check = flagUser::where('id', $flagID)->first();
        if ($check) {
            $check->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.unflag'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.flag_not_found'));
        }
    }

    public function adminDueAssignments()
    {
        $data = asigne_case::where('due_date', '<', Carbon::now()->format('Y-m-d'))->with('caseDetails')->get();
        return ['status_coe' => 200, 'data' => $data];
    }

    public function adminPendingApproval()
    {
        $caseDetails = asigne_case::where('asigne_status', '!=', 3)->with('attachements')->get();
        $data = ['pending' => $caseDetails->where('asigne_status', 0), 'rejected' => $caseDetails->where('asigne_status', 2), 'accepted' => $caseDetails->where('asigne_status', 1)];
        return ['status_code' => 200, 'data' => $data];
    }

    public function removeAttorneyFromCase($asigneID)
    {
        $check = asigne_case::where('id', $asigneID)->first();
        if ($check) {
            $check->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.remove_attorney'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.attorney_not_found_case'));
        }
    }

    public function deleteCaseDetails($caseID)
    {
        $check = law_case::where('id', $caseID)->first();
        if ($check) {
            try {
                DB::beginTransaction();
                $casefiles = casefile::where('case_id', $check->id)->pluck('storeLink')->toArray();
                $caseResponseFiles = caseresponse::where('case_no', $check->order_no)->pluck('storeLink')->toArray();
                if (!empty($casefiles)) {
                    File::delete($casefiles);
                    casefile::where('case_id', $check->id)->delete();
                }
                if (!empty($caseResponseFiles)) {
                    File::delete($caseResponseFiles);
                    caseresponse::where('case_no', $check->order_no)->delete();
                }
                uploadResponse::where('case_id', $caseID)->delete();
                hearings::where('case_id', $caseID)->delete();
                caseAction::where('case_id', $caseID)->delete();
                $check->delete();
                DB::commit();
                return ApiResponse::sucessResponse(200, [], trans('messages.case_delete'));
            } catch (Exception $e) {
                DB::rollBack();
                ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
                return ApiResponse::serverError();
            }
        } else {
            return ApiResponse::errorResponse(400, trans('messages.case_not_exist'));
        }
    }

    public function createReminders($data, $caseID, $orderNO)
    {
        $reminders = [];
        $reminders['case_id'] = $caseID;
        $reminders['order_no'] = $orderNO;
        $reminders['note'] = $data['note'];
        $reminders['attorney_id'] = $data['attorneyID'];
        $reminders['createTime'] = Carbon::now();
        return Reminders::create($reminders);
    }

    public function updateReminer($data, $reminerID)
    {
        $check = Reminders::where('id', $reminerID)->first();
        if ($check) {
            $check->note = $data['note'];
            $check->save();
            return ApiResponse::sucessResponse(200, $check, trans('messages.reminder_update'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.reminder_not_found'));
        }
    }

    public function deleteReminder($reminerID)
    {
        $check = Reminders::where('id', $reminerID)->first();
        if ($check) {
            $check->delete();
            return ApiResponse::errorResponse(400, trans('messages.reminder_delete'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.reminder_not_found'));
        }
    }

    public function acceptCaseResponseFile($response_id)
    {
        $check = caseresponse::where('id', $response_id)->first();
        if ($check) {
            $law = law_case::where('order_no', $check->case_no)->first();
            $law->case_status = 1;
            $check->file_staus = 1;
            asigne_case::where('attorney_id', $check->attorney_id)->where('case_id', $law->id)->update(['asigne_status' => 3]);
            $law->save();
            $check->save();
            return ApiResponse::sucessResponse(200, $check, trans('messages.file_accept'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.response_not_found'));
        }
    }

    public function rejectFileofResponse($fileid)
    {
        $check = caseresponse::where('id', $fileid)->first();
        if ($check) {
            $check->file_staus = 2;
            $check->save();
            return ApiResponse::sucessResponse(200, $check, trans('messages.file_reject'));
        } else {
            return ApiResponse::errorResponse(400, trans('messages.response_not_found'));
        }
    }
}
