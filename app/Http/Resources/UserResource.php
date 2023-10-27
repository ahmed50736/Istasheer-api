<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            'username' => $this->username,
            'phone_no' => $this->phone_no ? '+965' . $this->phone_no : null,
            'balance' => $this->balance,
            'dob' => $this->DOB,
            'gender' => $this->gender,
            'account_status' => $this->flag ? 'disable' : 'enable',
            'verified' =>  $this->verified,
            'notes' => $this->notes,
            'other_info' => $this->other_info,
            'image' => $this->media ? $this->media->photo_url : null
        ];
    }
}
