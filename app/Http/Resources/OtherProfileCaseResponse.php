<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtherProfileCaseResponse extends JsonResource
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
            'oder_no' => $this->order_no,
            'service_name' => $this->category->service_name,
            'category_name_english' => $this->category->service_title_english,
            'category_name_arabic' => $this->category->service_title_arabic,
            'created_at' => $this->create_time,
            'deleted_at' => $this->deleted_at
        ];
    }
}
