<?php

namespace App\Http\Controllers\Faq;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqCreateRequest;
use App\Http\Requests\Faq\FaqListFilterRequest;
use App\Http\Requests\Faq\FaqUpdateRequest;
use App\Models\Faq;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * create frequently ask data
     * @group admin/faq
     * @param FaqCreateRequest $request
     * @return JsonResponse
     */
    public function createFaq(FaqCreateRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $faq = Faq::create($requestData);
            return ApiResponse::sucessResponse(200, $faq, trans('messages.faq.create'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * update faq
     * @group admin/faq
     * @param FaqUpdateRequest $request
     * @return JsonResponse
     */
    public function updateFaqData(FaqUpdateRequest $request): JsonResponse
    {
        $updateData = $request->validated();
        try {
            $faqData = Faq::where('id', $updateData['id'])->first();
            $faqData->update($updateData);
            $updateFaq = $faqData->refresh();
            return ApiResponse::sucessResponse(200, $updateFaq, trans('messages.faq.update'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * faq list
     * @param FaqListFilterRequest $request
     * @return JsonResponse
     */
    public function faqList(FaqListFilterRequest $request): JsonResponse
    {
        $filterData = $request->validated();
        $lang = app()->getLocale();
        try {

            if (isset($filterData['user_type']) && $filterData['user_type'] != null) {
                $userType = $filterData['user_type'];
            } else {
                $userType = 'user';
            }
            $faqData = Faq::getDataWithPagination($userType, $lang);
            return ApiResponse::sucessResponse(200, $faqData);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * delete faq
     */
    public function deleteFaq(Faq $faq)
    {
        try {
            $faq->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.faq.delete'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
