<?php

namespace App\Http\Requests\Faq;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class FaqListFilterRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_type' => 'sometimes|nullable|in:user|attorney'
        ];
    }
}
