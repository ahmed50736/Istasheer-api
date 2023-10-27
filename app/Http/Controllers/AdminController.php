<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Resources\AdminHomeResource;
use App\Models\asigne_case;
use App\Models\casefile;
use App\Models\law_case;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseServices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    /**
     * all users
     * @authenticated
     * @group Admin
     * @return JsonResponse
     */
    public function allUser(): JsonResponse
    {
        try {
            $usersList = User::getUserListWithPagination(3, 15);

            return ApiResponse::sucessResponse(200, $usersList);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * All Attorney
     * @group admin
     * @authenticated
     * @return JsonResponse
     */
    public function allAttorney(): JsonResponse
    {
        try {
            $data = User::getAttorneyListWithPagination();
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * specific User Cases
     * @authenticated
     * @group Admin
     * @param string $uid
     * @return JsonResponse
     */
    public function userCases(string $uid): JsonResponse
    {
        try {
            $data =  law_case::where('uid', $uid)->get();
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }


    /**
     * Case Search by code
     * @authenticated
     * @group Admin
     * @param $code
     * @return JsonResponse
     */
    public function searchByCode($code): JsonResponse
    {
        try {
            $data = law_case::where('case_code', $code)->first();
            if ($data) {
                return ApiResponse::sucessResponse(200, $data);
            } else {
                return ApiResponse::errorResponse(400, trans('messages.case_not_exist'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }


    /* public function editPrice(Request $request, $pid){
        try {
            $data=pricelist::where('id',$pid)->first();
            if($data){
                $data->name=$request['name'];
                $data->price=$request['price'];
                $data->save();
                return ApiResponse::sucessResponse(200,$data,trans('messages.price_update'));
            }else{
                return ApiResponse::errorResponse(400,trans('messages.price_not_found'));
            }
        } catch (Exception $e){
            ErrorMailSending::sendErrorMailToDev($e->getMessage(),$e->getFile(),$e->getLine());
             return ApiResponse::serverError();
        }
    } */


    /**
     * delete case fiel
     * @param string $caseFileID
     * @return JsonResponse
     */
    public function deleteCaseFiles($caseFileID): JsonResponse
    {
        try {
            $check = casefile::where('id', $caseFileID)->first();
            if ($check) {
                ///need to impliment function for delete case file
                return ApiResponse::sucessResponse(200, [], trans('messages.file_delete'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.file_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }


    /**
     * Admin Home Data
     * @return JsonResponse
     */
    public function hommeData(): JsonResponse
    {
        try {
            $homeData = Auth::user()->getAdminHomeData();
            return ApiResponse::sucessResponse(200, new AdminHomeResource($homeData), '');
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete user and attorney
     * @param User $user
     * @return JsonResponse
     */
    public function deleteUser(User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            //deleting all user cases
            if ($user->isUser()) {
                law_case::where('uid', $user->id)->delete();
            } else if ($user->isAttorney()) {
                asigne_case::where('attorney_id', $user->id)->delete();
            } else {
            }
            $user->delete();
            DB::commit();
            return ApiResponse::sucessResponse(200, [], trans('messages.delete_account'));
        } catch (Exception $e) {
            DB::rollBack();
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    public function testNotification()
    {
        return FirebaseServices::sendNotification(['fRdOhXyMrkR7tt8oyGo-99:APA91bHgCi21RPyl7HoRaLg2arRtPWmxfizR78qdvPUyxrTpAFUpd6NMXxwKgSrUJz6qH58aixDlW4A1KND9ZnrxyT7r7OAK-IocAMlWQ7B7d1nmjnhQsfHJog4e6h_2V-zJ50SjkuDV'], 'test 100000', 'test 5000000');
    }

    /**
     * admin list
     * @group admin
     * @return JsonResponse
     */
    public function allAdmins(): JsonResponse
    {
        try {
            $admins = User::getAllAdminsWithPagination();
            return ApiResponse::sucessResponse(200, $admins, '');
        } catch (Exception $e) {
            return ErrorMailSending::sendingErrorMail($e);
        }
    }
}
