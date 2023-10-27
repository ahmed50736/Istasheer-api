<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class HearingSearchRequest extends BaseRequest
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
            'search' => 'sometimes|nullable|string',
            'from' => 'sometimes|nullable|required_if:search,null|date_format:Y-m-d',
            'to' => 'sometimes|nullable|required_if:search,null|date_format:Y-m-d|after_or_equal:from'

        ];
    }
}
