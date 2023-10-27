<?php
namespace App\helpers;

use Exception;
use Illuminate\Support\Facades\Mail;

class HelperStoreFiles {

    public static $link = 'https://api.istesheer.com/';
    public static function soteSingleFile($destinationFolder,$file){
    
        try {
            $path = './'.$destinationFolder.'/';
            self::$link .= $destinationFolder.'/';
            $name = substr($file->getClientOriginalName(), 0, 5).'.'.$file->getClientOriginalExtension();
            $file->move($path, $name);  
            return  [
                'link' => self::$link.''.$name,
                'store_link' =>  $path.$name,
                'file_name' => $name,  
            ]; 
        }catch (Exception $e){
            ErrorMailSending::sendErrorMailToDev($e->getMessage(),$e->getFile(),$e->getLine());
        }
    }

    public static function storeMultipleFiles($destinationFolder,$files) {

    }
}