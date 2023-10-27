<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Http\Requests\PaymentRequest;
use App\Models\law_case;
use App\Models\PaymentDetail;
use App\Services\CaseCategoryService;
use Exception;
use Illuminate\Http\Request;
use MyFatoorah\Library\PaymentMyfatoorahApiV2;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MyFatoorahController extends Controller
{

    public $mfObj;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * create MyFatoorah object
     */
    public function __construct()
    {
        $this->mfObj = new PaymentMyfatoorahApiV2(config('myfatoorah.api_key'), config('myfatoorah.country_iso'), config('myfatoorah.test_mode'));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Create MyFatoorah invoice
     * @authenticated
     * @group Users
     * @param PaymentRequest $request
     * @return JsonResponse
     */
    public function index(PaymentRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $requestData['user'] = auth()->user();
        try {

            $paymentMethodId = 1; // 0 for MyFatoorah invoice or 1 for Knet in test mode

            $curlData = $this->getPayLoadData($requestData);
            $checkCaseValue = law_case::where('id', $curlData['CustomerReference'])->first();

            ////checking payment is done then return info that price paid///

            if ($curlData['ExtraServiceId'] != null) { // for checking payment of case extra service
                $ExtraServicePayment = PaymentDetail::where('case_id', $curlData['CustomerReference'])->where('extra_service_id', $curlData['ExtraServiceId'])->first();
                if ($ExtraServicePayment) {
                    return ApiResponse::sucessResponse(200, [], trans('messages.already_paid'));
                }
            } else { /// for checking payment of case only
                $CasePayment = PaymentDetail::where('case_id', $curlData['CustomerReference'])->whereNull('extra_service_id')->first();
                if ($CasePayment) {
                    if ($CasePayment->transection_status == 'Paid') {
                        return ApiResponse::sucessResponse(200, [], trans('messages.already_paid'));
                    }
                }
            }

            //checking pricing value of the case
            if ($checkCaseValue->category_id != CaseCategoryService::OTHER_SERVICE_ID) {
                if ($checkCaseValue->subcategory->price != $curlData['InvoiceValue']) {
                    return ApiResponse::errorResponse(400, trans('messages.price_not_matched'));
                }
            } else {
                if ($checkCaseValue->other_case_price == null) {
                    return ApiResponse::errorResponse(400, trans('messages.other_case_price_not_set'));
                } else {
                    if ($checkCaseValue->other_case_price != $curlData['InvoiceValue']) {
                        return ApiResponse::errorResponse(400, trans('messages.price_not_matched'));
                    }
                }
            }
            $data = $this->mfObj->getInvoiceURL($curlData, $paymentMethodId);

            return ApiResponse::sucessResponse(200, $data, trans('messages.invoice_create'));
        } catch (\Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param int|string $orderId
     * @return array
     */
    private function getPayLoadData($data)
    {
        $callbackURL = route('callback.myfatoorah');

        return [
            'CustomerName'       => $data['user']->name,
            'InvoiceValue'       => $data['amount'],
            'DisplayCurrencyIso' => 'KWD',
            'CustomerEmail'      => $data['user']->email ? $data['user']->email : null,
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => '+965',
            'CustomerMobile'     => $data['user']->phone_no,
            'Language'           => 'en',
            'CustomerReference'  => $data['case_id'],
            'ExtraServiceId'     => isset($data['extra_service_id']) ? $data['extra_service_id'] : null,
            'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get MyFatoorah payment information
     * 
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, PaymentDetail $paymentDetail)
    {

        try {
            $paymentId = request('paymentId');
            $paymentdata = $this->mfObj->getPaymentStatus($paymentId, 'PaymentId');

            if ($paymentdata->InvoiceStatus == 'Paid') {
                $msg = trans('messages.payment_confirmation');
                $code = 200;
                $sts = 'true';
                $paymentDetail->storePaymentDetails($paymentdata);
            } else if ($paymentdata->InvoiceStatus == 'Failed') {
                $msg = 'Invoice is not paid due to ' . $paymentdata->InvoiceError;
                $code = 400;
                $sts = 'false';
            } else {
                $msg = trans('messages.invoice_expired');
                $code = 400;
                $sts = 'false';
            }
            $redirectUrl = route('callback.response.myfatoorah') . '?status=' . $sts . '&code=' . $code . '&payment_status=' . $paymentdata->InvoiceStatus . '&message=' . urlencode($msg);
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            //DB::rollBack();
            $sts = 'false';
            ErrorMailSending::sendingErrorMail($e);
            $redirectUrl = route('callback.response.myfatoorah') . '?status=' . $sts . '&code=400&payment_status=Failed&message=' . urlencode(trans('messages.payment_failed'));
            return redirect($redirectUrl);
        }
    }

    /**
     * return query param to this route for handling in webview of app
     */
    public function callbackResponse(Request $request)
    {
        return;
        /* try{
            return ApiResponse::globalResponse($request->status, $request->code, $request->code, $request->message);
        } catch( Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        } */
    }
}
