<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CaseHearingResource extends JsonResource
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
            'session_type' => $this->session_type,
            'informe' => $this->informe == 0 ? false : true,
            'date' => $this->date,
            'time' => $this->time,
            'decission' => $this->decission,
            'note' => $this->note,
            'action' => $this->action
        ];
    }
}
