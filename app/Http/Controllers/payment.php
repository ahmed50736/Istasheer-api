<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class payment extends Controller
{
    public function makepay(){

        try{
            $payment = request()->user()->pay(request()->amount, [
                'udf1' => request()->user()->name,
                'udf2' => request()->user()->email
            ]);
        } catch(\Asciisd\Knet\Exceptions\PaymentActionRequired $exception) {
            // do whatever you want with this 
            $payment = $exception->payment;
            
        }/*  finally {
            // redirect user to payment url to complete the payment
            return  $payment->actionUrl();
        } */
        return response()->json( $payment->actionUrl());

    }
}
