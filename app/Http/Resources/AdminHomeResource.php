<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminHomeResource extends JsonResource
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
            'clients' => $this[0]->total_client,
            'attorneys' => $this[0]->total_attorney,
            'actions' => $this[0]->total_action,
            'hearings' => $this[0]->total_hearings,
            'orders' => $this[0]->total_case,
            'pending_approval' => $this[0]->pending_approval,
            'due_assignments' => $this[0]->due_assignments
        ];
    }
}
