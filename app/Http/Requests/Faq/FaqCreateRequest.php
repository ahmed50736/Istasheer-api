<?php

namespace App\Http\Requests\Faq;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class FaqCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_type' => 'required|in:attorney,user',
            'language_type' => 'required|in:ar,en',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ];
    }
}
