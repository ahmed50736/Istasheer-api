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
use Illuminate\Support\Carbon;

class userAdmin
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
       } catch (Exception $e) {
           if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return ApiResponse::globalResponse(false,401,401,'Token Invalid');            
           }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return ApiResponse::globalResponse(false,401,401,'Token is Expired');  
           }else if($e instanceof JWTException){
                return ApiResponse::globalResponse(false,401,401,'unauthorized User'); 
           }
           else{
                return ApiResponse::globalResponse(false,401,401,'Token Invalid'); 
           }
       }
       $user=Auth::user();
       if($user){
        $flagCheck=flagUser::where('user_id',$user->id)->first();
        if($flagCheck){
            $tm=Carbon::parse($flagCheck->to);
                if($tm->isPast()){
                    $flagCheck->delete();
                }else{
                    return ApiResponse::globalResponse(false,999,401,'Your account has been flaged from '.$flagCheck->from.'to '.$flagCheck->to.' by admin. please contact support for more information',['flaginfo'=>$flagCheck,'users'=>$user]);
                }
        }
        if($user->user_type==1 || $user->user_type=3){
            return $next($request);
           }else{
            return ApiResponse::globalResponse(false,403,403,'Forbiden user'); 
           }
       }else{
            return ApiResponse::globalResponse(false,401,401,'Forbiden user'); 
       }
       
    }
    
}
