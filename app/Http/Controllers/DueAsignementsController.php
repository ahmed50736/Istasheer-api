<?php

namespace App\Http\Controllers;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Models\asigne_case;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DueAsignementsController extends Controller
{
    private $asignCase;

    public function __construct()
    {
        $this->asignCase = new asigne_case();
    }
    /**
     * admin du asignments list with pagination
     * @return JsonResponse
     */
    public function adminDueAsignementsList(): JsonResponse
    {
        try {
            $asignments = $this->asignCase::getOverDueAsignmentsWithPagination('admin');
            return ApiResponse::sucessResponse(200, $asignments);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
