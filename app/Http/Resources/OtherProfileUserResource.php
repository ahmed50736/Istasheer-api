<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtherProfileUserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone_no,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'account_status' => $this->flag ? 'disable' : 'enable',
            'image' => $this->media ? $this->media->photo_url : null,
            'balance' => $this->balance
        ];
    }
}
