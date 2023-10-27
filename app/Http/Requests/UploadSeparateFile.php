<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadSeparateFile extends BaseRequest
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
            'id' => [
                'required',
                Rule::exists('law_cases', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'case_files' => 'required|array',
            'case_files.*.file' => 'required|file|mimes:png,jpeg,jpg,pdf,doc,docx,csv,avi,gl,mjpg,mov,moov,movie,mpeg,mpg,mp4,asf,xlsx,xlsm,xlc,xls,ppt,pptx,xml,text,txt|max:20480',
            'case_files.*.details' => 'sometimes|nullable|string',
        ];
    }
}
