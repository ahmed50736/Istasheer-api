<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Models\caseresponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingApprovalController extends Controller
{
    private $caseResponses;

    public function __construct()
    {
        $this->caseResponses = new caseresponse();
    }

    /**
     * admin pending approval list with pagination
     * @return JsonResponse
     */
    public function adminPendingApproval(): JsonResponse
    {
        try {
            $pendingApproval = $this->caseResponses::pendingApprovalWithPagination('admin');
            return ApiResponse::sucessResponse(200, $pendingApproval);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * pending Approval list (Attorney)
     * @authenticated
     * @group Attorney
     * @return JsonResponse
     */
    public function attorneyPendingApproval(): JsonResponse
    {
        try {

            $pendingApproval = $this->caseResponses::pendingApprovalWithPagination('attorney', Auth::user()->id);
            return ApiResponse::sucessResponse(200, $pendingApproval);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
