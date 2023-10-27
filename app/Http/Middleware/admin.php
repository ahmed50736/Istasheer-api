<?php

namespace App\Http\Middleware;

use App\helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
            $user=Auth::user();
       
            if($user->user_type==1||$user->user_type==4){
                return $next($request);
            }else{
                return ApiResponse::globalResponse(false,403,403,trans('messages.unauthorized_acess'));
            }
       
       } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return ApiResponse::globalResponse(false,401,401,trans('messages.invalid_token'));
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return ApiResponse::globalResponse(false,401,401,trans('messages.token_expired'));
            }else if($e instanceof JWTException){
                return ApiResponse::globalResponse(false,401,401,trans('messages.invalid_token'));
            }
            else{
                return ApiResponse::globalResponse(false,401,401,trans('messages.invalid_token'));
            }
        }   
       
       
    }
}
