<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CaseFileResponseResource extends JsonResource
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
            'id' => $this->media->id,
            'file_name' => $this->media->file_name,
            'file_url' => $this->media->photo_url,
            'file_status' => $this->file_staus == 0 ? 'pending' : ($this->file_staus == 2 ? 'rejected' : 'accepted'),
            'uploaded_by' => $this->user->name,
            'user_type' => $this->user->user_type == 2 ? 'Attorney' : 'Admin',
            'details' => $this->media->details,
            'deleted' => $this->media->deleted_at ? true : false
        ];
    }
}
