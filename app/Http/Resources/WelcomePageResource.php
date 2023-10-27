<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WelcomePageResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'image' =>  $this->media ? $this->media->photo_url :null,
            'user_type' => $this->user_type
        ];
    }
}
