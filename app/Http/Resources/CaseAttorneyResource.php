<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseAttorneyResource extends JsonResource
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
            'attorney_id' => $this->attorney_id,
            'attorney_name' => $this->name,
            'asign_time' => $this->asign_time,
            'deadline' => $this->deadline,
            'image' => $this->image,
            'submit_time' => $this->submit_time,
            'due_date' => $this->due_date,
            'removed' => $this->deleted_at ? true : false
        ];
    }
}
