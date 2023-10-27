<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;
use App\Http\Traits\SanitizeInput;

class CaseVoiceStoreRequest extends BaseRequest
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
            'case_id' => 'required|exists:law_cases,id,deleted_at,NULL',
            'case_voice' => 'required|file|mimes:audio/mpeg,mpga,mp3,wav,aac|max:20480'
        ];
    }
}
