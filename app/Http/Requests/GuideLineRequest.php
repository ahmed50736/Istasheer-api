<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuideLineRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'sometimes|nullable|exists:guidelines,id,deleted_at,NULL',
            'title' => 'required|string|min:6',
            'description' => 'required|string|min:10',
            'user_type' => 'required|in:user,attorney,both',
            'video' => 'sometimes|file|mimes:mp4,avi,mov,mkv,webm,flv,wmv,3gp,ts,m3u8,m4v|max:50480'
        ];
    }
}
