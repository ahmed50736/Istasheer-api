<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\law_case;
use App\helpers\ApiResponse;
use Exception;
use App\helpers\ErrorMailSending;
use App\Http\Requests\AdvanceSearchRequest;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Advance Search
     * @authenticated
     * @group common
     * @param AdvanceSearchRequest $request
     * @return JsonResponse
     */
    public function advanceSearch(AdvanceSearchRequest $request): JsonResponse
    {
        $queryData = $request->validated();
        try {
            if (!empty($queryData)) {

                //checking all offset value is null or not
                $allOffsetsAreNull = empty(array_filter($queryData, fn ($value) => $value !== null));

                if ($allOffsetsAreNull) {
                    return ApiResponse::errorResponse(400, trans('messages.advance_search_error'));
                } else {
                    $data = law_case::advanceSearch($queryData);
                    return ApiResponse::sucessResponse(200, $data);
                }
            } else {
                return ApiResponse::errorResponse(400, trans('messages.advance_search_error'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
