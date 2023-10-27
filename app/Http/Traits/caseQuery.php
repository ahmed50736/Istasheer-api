<?php
namespace App\Http\Traits;

use App\Models\asigne_case;
use App\Models\caseAction;
use App\Models\caseresponse;
use App\Models\hearings;
use App\Models\law_case;


trait caseQuery{

    public static function getCasedetails($caseId){
        $data=law_case::where('id',$caseId)->with('attachments','hearings','UploadResponse')->first();
        return $data;
    }

    public static function getUsercaseDetails($uid){
        $data=law_case::where('uid',$uid)->with('attachments','hearings','UploadResponse')->get();
        return $data;
    }
    public static function attorneyAssignecases($attorneyId){
      $data=asigne_case::where('attorney_id',$attorneyId)->with('casedetails','attachements','hearings')->get();
      return $data;
    }

    public static function updateAction($actionId,$data){
         $update=caseAction::where('id',$actionId)->update($data);
         if($update){
            return true;
         }else{
            return false;
         }
    }

    public static function updateHearings($hearingId,$data){
        return hearings::where('id',$hearingId)->update($data);
        
    }

    public static function actionInfo($actionId){
        $data=caseAction::where('id',$actionId)->first();
        return $data;
    }

    public static function uploadCaseResponse($data){
        caseresponse::create($data);
    }
    
}