<?php

namespace App\helpers;

use App\Mail\ExceptionOccured;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Exception;
class ErrorMailSending{

    public static function sendErrorMailToDev($errorMessage,$file,$line){
        
            // get method, GET or POST
            $method = request()->method();
    
            // get full URL including query string
            $full_url = request()->fullUrl();
    
            // get route name
            $route = "";
    
            // get list of all middlewares attached to that route
            $middlewares = "";
    
            // data with the request
            $inputs = "";
            if (request()->route() != null)
            {
                $route = "uri: " . request()->route()->getName();
                $middlewares = json_encode(request()->route()->gatherMiddleware());
                $inputs = json_encode(request()->all());
            }
    
    
            // get user browser or request source
            $user_agent = request()->userAgent();
            $controllerName =  \Route::currentRouteAction();//request()->route()->parameter('controller');//request()->getCurrentRoute()->getActionName();
    
            // create email body
            $html = $errorMessage . "\n\n";
            $html .= "File: " . $file . "\n\n";
            $html .= "Line: " . $line . "\n\n";
            $html .= "Controller Name: " . $controllerName . "\n\n";
            $html .= "Inputs: " . $inputs . "\n\n";
            $html .= "Method: " . $method . "\n\n";
            $html .= "Full URL: " . $full_url . "\n\n";
            $html .= "Route: " . $route . "\n\n";
            $html .= "Middlewares: " . $middlewares . "\n\n";
            $html .= "User Agent: " . $user_agent . "\n\n";
            
    
            // for testing purpose only
            //Auth::loginUsingid(1);
    
            // check if user is logged in
            if (Auth::check())
            {
                // get email of user that faced this error
                $html .= "User: " . Auth::user()->email;
            }
    
            Mail::to('hassanshahriar18@gmail.com')->send(new ExceptionOccured($html));
    }

    public static function sendingErrorMail($exception)
    {
        $html = 'Error: '. $exception->getMessage() . "\n\n";
        $html .= "File: " . $exception->getFile() . "\n\n";
        $html .= "Line: " . $exception->getLine() . "\n\n";
        $html .= "Full URL: " . request()->fullUrl() . "\n\n";
        $html .= "Method: " . request()->method() . "\n\n";
        $html .= "Inputs: " .  json_encode(request()->all()) . "\n\n";
        Mail::to('hassanshahriar18@gmail.com')->send(new ExceptionOccured($html));
    }
}