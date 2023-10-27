<?php

namespace App\Http\Controllers\Voucher;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Controllers\Controller;
use App\Http\Requests\voucher\CreateVoucherRequest;
use App\Http\Requests\voucher\UpdateVoucherRequest;
use App\Http\Requests\voucher\VoucherSearchRequest;
use App\Models\Voucher;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;

class VoucherController extends Controller
{
    private $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * create voucher
     * @authenticated
     * @group Admin
     * @param Request $request
     * @return JsonResponse
     */
    public function createVoucher(CreateVoucherRequest $request)
    {
        $requestData = $request->validated();
        try {

            $voucherData = $this->voucherService->createOrUpdateVoucher($requestData);

            return ApiResponse::globalResponse(true, 200, 201, trans('messages.voucher_create'), $voucherData);
        } catch (Exception $e) {

            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * voucher list with pagination
     * @param VoucherSearchRequest $request
     * @return JsonResponse
     */
    public function voucherList(VoucherSearchRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        try {
            $voucherList = Voucher::getVoucherListWithPagination($requestData);
            return ApiResponse::sucessResponse(200, $voucherList);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * update voucher request
     * @param UpdateVoucherRequest $request
     * @return JsonResponse
     */
    public function voucherUpdate(UpdateVoucherRequest $request): JsonResponse
    {
        $updateData = $request->validated();
        try {
            $updateVoucher = $this->voucherService->createOrUpdateVoucher($updateData);
            return ApiResponse::sucessResponse(200, $updateVoucher, trans('messages.voucher_update'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * delete voucher
     * @urlParam voucher required voucher id
     * @return JsonResponse
     */
    public function deleteVoucher(Voucher $voucher): JsonResponse
    {
        try {
            $voucher->delete();
            return ApiResponse::sucessResponse(200, [], trans('messages.voucher_delete'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
