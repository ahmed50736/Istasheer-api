<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Http\Traits\SanitizeInput;

class WelcomePageRequest extends BaseRequest
{
    use SanitizeInput;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'sometimes|required|exists:welcome_pages,id',
            'lang' => 'required|in:ar,en',
            'title' => 'required|string|max:255',
            'user_type' => 'required|in:user,attorney',
            'description' => 'required',
            'image' => 'required_without:id|image'
        ];
    }
}
