<?php

namespace App\helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{

    public static function sucessResponse($code = 200, $data, $message = '')
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    public static function otherResponse($code, $message, $data = [])
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], 200);
    }
    public static function errorResponse($code, $errorMessage)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $errorMessage,
            'data' => []
        ], 400);
    }

    public static function serverError()
    {
        return response()->json([
            'status' => false,
            'status_code' => 500,
            'message' => 'Server Problem',
            'data' => []
        ], 500);
    }

    public static function postResponse($message, $data)
    {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => $message,
            'data' => $data
        ], 201);
    }

    public static function globalResponse($status, $statusCode, $headerCode, $message, $data = [])
    {
        return response()->json([
            'status' => $status,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $headerCode);
    }

    public static function routeNotFound()
    {
        return response()->json([
            'status' => false,
            'status_code' => 404,
            'data' => [],
            'message' => trans('messages.404')
        ], 404);
    }

    public static function methodNotAllowed()
    {
        return response()->json([
            'status' => false,
            'status_code' => 405,
            'data' => [],
            'message' => trans('messages.405')
        ], 405);
    }

    public static function throwExceptionMessage($message = "")
    {
        throw new \Exception($message, Response::HTTP_BAD_REQUEST);
    }

    public static function exceptionMessage($message)
    {
        return new JsonResponse([
            'status' => false,
            'status_code' => 500,
            'message' => $message,
            'data' => []
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function dataNotFound()
    {
        return response()->json([
            'status' => false,
            'status_code' => 400,
            'data' => [],
            'message' => trans('messages.data_not_found')
        ], 400);
    }
}
