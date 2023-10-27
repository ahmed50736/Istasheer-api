<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Jobs\CredentailsSender;
use App\Mail\CredentailsMailer;
use App\Models\CredentialLogger;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;


class CredentialLoggerController extends Controller
{
    /**
     * send credential to attorney
     * @return JsonResponse
     */
    public function sendCredentail(CredentialLogger $credentialLogger): JsonResponse
    {

        try {

            CredentailsSender::dispatch($credentialLogger->username, $credentialLogger->password);

            return ApiResponse::sucessResponse(200, [], trans('messages.attorney_credentilas_change.success_mailer'));
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
