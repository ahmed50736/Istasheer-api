<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\terms;
use App\helpers\ApiResponse;
use App\helpers\DirtyValueChecker;
use App\helpers\ErrorMailSending;
use App\Http\Requests\UpdateTermRequest;
use App\Http\Resources\TermsResource;
use App\Jobs\User\TermsUpdateNotification;
use Exception;
use Illuminate\Http\JsonResponse;

class TermsController extends Controller
{

    /**
     * update terms
     * @param UpdateTermRequest $request
     * @return JsonResponse
     */
    public function updateTerm(UpdateTermRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {

            $checkTerms = terms::where('id', $data['id'])->first();
            if ($checkTerms) {
                if(DirtyValueChecker::dirtyValueChecker($checkTerms,$data)){
                    $checkTerms->details = $data['details'];
                    $checkTerms->save();
                    TermsUpdateNotification::dispatch($checkTerms);
                }
                
                return ApiResponse::sucessResponse(200, new TermsResource($checkTerms), trans('messages.terms_update'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.terms_not_found'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }


    /**
     * Terms & Condition
     * @group setting
     * @urlParam user string required The user type. Possible values: user, attorney.
     * @return JsonResponse
     */
    public function termsandcondition(string $user): JsonResponse
    {
        try {
            $lang = app()->getLocale();
            if ($lang == 'ar') {
                $lang = 2;
            } else {
                $lang = 1;
            }
            if ($user == 'user') {
                $usr = 3;
            } else {
                $usr = 2;
            }

            $data = terms::select('id', 'details')->where('user_type', $usr)->where('language_type', $lang)->first();

            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
