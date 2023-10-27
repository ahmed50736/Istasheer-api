<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AboutResource extends JsonResource
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
            'language_type' => $this->language_type == 1 ? 'en' : 'ar',
            'details' => $this->details,
            'user_type' => $this->user_type == 3 ? 'user' : 'attorney'
        ];
    }
}
