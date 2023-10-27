<?php

namespace App\Http\Controllers;

use App\Services\CaseCategoryService;
use Illuminate\Http\Request;
use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use Exception;
use Illuminate\Http\JsonResponse;

class PriceController extends Controller
{
    /**
     * get Price List
     * @queryParam case_type Field to sort by. Defaults to 'case_type'. Example: case_type
     * @return JsonResponse
     */
    public function priceList(Request $request, CaseCategoryService $category): JsonResponse
    {
        $data = $request->toArray();
        try {
            $requestType = isset($data['case_type'])  ? $data['case_type']  : 'company';
            $data = $category->getPriceList($requestType);
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
