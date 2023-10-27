<?php

namespace App\Http\Middleware;

use App\helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\flagUser;
use Carbon\Carbon;


class common
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
        
                $flagCheck=flagUser::where('user_id',$user->id)->first();
                    if($flagCheck){
                        $tm=Carbon::parse($flagCheck->to);
                            if($tm->isPast()){
                                $flagCheck->delete();
                            }else{
                                return ApiResponse::globalResponse(false,999,401,trans('messages.flag_message',['from'=>$flagCheck->from, 'to'=>$flagCheck->to]),['flaginfo'=>$flagCheck,'users'=>$user]);
                            }
                    }
                return $next($request);           
        
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
