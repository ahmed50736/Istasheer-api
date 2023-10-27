<?php

namespace App\Http\Resources;

use App\Models\caseAction;
use App\Models\hearings;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaymentDetail;
use App\Services\CaseCategoryService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class CaseDetailsViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth()->user();
        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'client_id' => $this->uid,
            $this->mergeWhen($user->user_type == 1 || $user->user_type == 4, function () {
                return [
                    'case_status' => ($this->case_status == 0) ? 'new' : (($this->case_status == 1) ? 'open' : 'closed'),
                ];
            }),

            $this->mergeWhen($user->user_type == 2, function () use ($user) {
                $attorneyAssign = $this->attorneys->where('attorney_id', $user->id)->first();

                return [
                    'case_status' => $attorneyAssign ? (($attorneyAssign->asigne_status == 1) ? 'open' : (($attorneyAssign->asigne_status == 0) ? 'new' : 'closed')) : 'new',
                ];
            }),

            $this->mergeWhen($user->user_type == 3, function () {
                return [
                    'case_status' => ($this->case_status == 2) ? 'closed' :  'open',
                ];
            }),

            'payment_status' => PaymentDetail::where('case_id', $this->id)->whereNull('extra_service_id')->first() ? 'paid' : 'unpaid',
            'price' => $this->category_id == CaseCategoryService::OTHER_SERVICE_ID ? $this->other_case_price : $this->subcategory->price,
            'category_id' => $this->category->id,
            'client_name' => $this->client_name,
            'service_name' => $this->category->service_name,
            'category_name_english' => $this->category->service_title_english,
            'category_name_arabic' => $this->category->service_title_arabic,
            'case_type' => $this->case_type,
            'subject' => $this->subject,

            $this->mergeWhen($this->category_id != CaseCategoryService::OTHER_SERVICE_ID, function () {
                return [
                    'subcategory_id' => $this->subcategory->id,
                    'subcategory_name_english' => $this->subcategory->sub_category_title_english,
                    'subcategory_name_arabic' => $this->subcategory->sub_category_title_arabic,
                ];
            }),


            $this->mergeWhen($this->category_id == CaseCategoryService::CASE_LAWSUIT, function () {
                return [
                    'against' => $this->against,
                    'room' => $this->room,
                    'chamber' => $this->chamber,
                    'expert_location' => $this->expert_location,
                    'court_case_no' => $this->court_case_no,
                    'court_location' => $this->court_location,
                    'capacity' => $this->capacity,
                    'capacity2' => $this->capacity2,
                    'automated_no' => $this->automated_no,
                ];
            }),

            $this->mergeWhen($this->category_id == CaseCategoryService::CONTRACT_DRAFTING, function () {
                return [
                    'purpouse' => $this->purpouse,
                    'contract_term' => $this->contract_term,
                    'contract_ammount' => $this->contract_ammount,
                    'deadline' => $this->deadline,
                    'court_location' => $this->court_location,
                ];
            }),

            $this->mergeWhen($this->category_id == CaseCategoryService::CONSULTATION_ID, function () {
                return [
                    'purpouse' => $this->purpouse,
                    'deadline' => $this->deadline,
                ];
            }),

            'details' => $this->details,
            'create_time' => $this->create_time,
            'attachments' => !empty($this->attachments) ? FileResource::collection($this->attachments) : [],
            'voice' => !empty($this->audios) ? FileResource::collection($this->audios) : [],
            'hearings' => hearings::getHearingsWithcaseID($this->id),
            'extra_services' =>  CaseExtraServiceResource::collection($this->extraServices),
            'case_response' => !empty($this->responseFiles) ? CaseFileResponseResource::collection($this->responseFiles) : [],
            'attorneys' => !empty($this->attorneys) ? CaseAttorneyResource::collection($this->attorneys) : [],
            'actions' => caseAction::getActionListWithCaseId($this->id)
        ];
    }
}
