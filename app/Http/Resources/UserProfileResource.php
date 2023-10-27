<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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

            'id' => $this['user_info']->id,
            'email' => $this['user_info']->email,
            'name' => $this['user_info']->name,
            'username' => $this['user_info']->username,
            'phone_no' => $this['user_info']->phone_no ? '+965' . $this['user_info']->phone_no : null,
            'balance' => $this['user_info']->balance,
            'dob' => $this['user_info']->DOB,
            'gender' => $this['user_info']->gender,
            'account_status' => $this['user_info']->flag ? 'disable' : 'enable',
            'verified' =>  $this['user_info']->verified,
            'notes' => $this['user_info']->notes,
            'other_info' => $this['user_info']->other_info,
            'image' => $this['user_info']->media ? $this['user_info']->media->photo_url : null,
            $this->mergeWhen($this['user_info']->user_type == 2, function () {
                return [
                    'profile_stats' => $this['profile_stats']
                ];
            })
        ];
    }
}
