<?php

namespace App\Http\Resources;

use App\Models\caseresponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OtherProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'user' => new OtherProfileUserResource($this['user']),
            $this->mergeWhen($this['user']->user_type == 3, function () { //user
                return [
                    'open_count' => $this['open_count'],
                    'new_count' => $this['new_count'],
                    'close_count' => $this['closed_count'],
                    'recent' => OtherProfileCaseResponse::collection($this['recent'])
                ];
            }),

            'send_credential_mailer' => auth()->user()->user_type == 1 && $this['user']->user_type == 2 ? true : false,

            $this->mergeWhen($this['user']->user_type == 2, function () { //attorney
                return [
                    'total_task' => $this['total_task'],
                    'pending_assignments' => count($this['result']->where('case_status', 0)),
                    'overdue_assignments' => count($this['result']->whereNotNull('due_date')->where('due_date', '<', date('Y-m-d H:i:s'))),
                    'service_rejected' => $this['service_rejected'],
                    'service_approved' => $this['service_approved'],
                    'recent' => OtherProfileCaseResponse::collection($this['recent'])
                ];
            }),
        ];
    }
}
