<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;

class ExtraServiceRequest extends BaseRequest
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
            'type' => 'required',
            'audio' => 'sometimes|nullable',
            'case_files' => 'sometimes|nullable|array',
            'case_files.*.file' =>
            'file|mimes:png,jpeg,jpg,pdf,doc,docx,csv,avi,gl,mjpg,mov,moov,movie,mpeg,mpg,mp4,asf,xlsx,xlsm,xlc,xls,ppt,pptx,xml,text,txt|max:20480|sometimes|required_if:case_files.*.details,!=,',

            'case_files.*.details' => 'string',
            'details' => 'required|string'
        ];
    }
}
