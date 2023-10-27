<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CaseActionResource extends JsonResource
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
            'id' => $this->id,
            'action_type' => $this->actionType,
            'action_status' => $this->actionStatus,
            'importance' => $this->importance,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'inform' => $this->inform == 1 ? true : false
        ];
    }
}
