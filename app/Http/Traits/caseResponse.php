<?php 
namespace App\Http\Traits;

use App\helpers\ApiResponse;
use App\helpers\ErrorMailSending;
use App\Models\caseresponse as ModelsCaseresponse;
use App\Models\uploadResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait caseResponse{

    public function deleteResponseFile($caseFileId){
        $check=ModelsCaseresponse::where('id',$caseFileId)->first();
        if($check){
            unlink($check->storeLink);
            $check->delete();
            return ApiResponse::sucessResponse(200,[],trans('messages.delete_message'));
            
        }else{
            return ApiResponse::errorResponse(400,trans('messages.data_not_found'));
        }
    }

    public function insertCaseResponse(array $data,$caseID,$caseNO){
        
        try{
            DB::beginTransaction();
            $userID=Auth::user()->id;
            $ResponseData=[];
            $ResponseData['case_id']=$caseID;
            $ResponseData['note']=$data['note'];
            $ResponseData['attorney_id']=$userID;
            $ResponseData['submissionTime']=Carbon::now()->format('Y-m-d H:i:s');
            $ResponseData = uploadResponse::create($ResponseData);
            $return= $this->uploadResponseFile($data['responsefiles'],$ResponseData->id,$caseNO,$userID);
            DB::commit();
            return $return;
            
        }catch (Exception $e){
            DB::rollBack();
            return ApiResponse::serverError();
        }

    }

    public function uploadResponseFile($files,$reponseID,$caseNO,$userID){
    
        try{
            $dataFilesInsert=[];
            $uploadPath='./caseResponse/';
            $url='https://api.istesheer.com/caseResponse/';
            DB::beginTransaction();
            foreach ($files as $key=>$val){
                $extension=$val->getClientOriginalExtension();
                $fileName=$val->getClientOriginalName();
                $UploadName='res'.$reponseID.'-'.$key.'.'.$extension;
                $val->move($uploadPath,$UploadName);
                $dataFilesInsert=[
                    'response_id'=>$reponseID,
                    'fileName'=>$fileName,
                    'fileLink'=>$url.$UploadName,
                    'storeLink'=>$uploadPath.$UploadName,
                    'case_no'=>$caseNO,
                    'attorney_id'=>$userID
                ];
               $data = ModelsCaseresponse::create($dataFilesInsert);
               $responsefiles[] = $data;
                DB::commit();
            }
            return ApiResponse::otherResponse(200,trans('messages.upload_message'),$responsefiles);
        }catch(Exception $e){
            ErrorMailSending::sendErrorMailToDev($e->getMessage(),$e->getFile(),$e->getLine());
            DB::rollBack();
            return ApiResponse::errorResponse(400,trans('messages.failed'));
        }
    }

    public function getAllResponseFilesByCaseID($caseID){
        $data = uploadResponse::where('case_id',$caseID)->with('responseFiles')->first();
        return $data;
    }

    public function getPendingApprovalAttorney($attorneyID){}

    public function getAllPendingApprovalAdmin(){

    }

}