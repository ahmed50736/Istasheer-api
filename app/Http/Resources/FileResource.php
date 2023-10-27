<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'details' => $this->media->details,
            'deleted' => $this->media->delted_at ? true : false

        ];
    }
}
