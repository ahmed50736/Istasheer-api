<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class UploadResponseRequest extends BaseRequest
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
            'case_id' => 'required|uuid|exists:law_cases,id',
            'responsefiles' => 'required|array',
            'responsefiles.*.file' => 'required|file|mimes:png,jpeg,jpg,pdf,doc,docx,csv,avi,gl,mjpg,mov,moov,movie,mpeg,mpg,mp4,asf,xlsx,xlsm,xlc,xls,ppt,pptx,xml,text,txt|max:20480',
            'responsefiles.*.details' => 'sometimes|nullable|string|max:100'
        ];
    }
}
