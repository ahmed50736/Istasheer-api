<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\BaseRequest;
use App\Http\Traits\SanitizeInput;

class CaseRequest extends BaseRequest
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
            'id' => 'sometimes|required|exists:law_cases,id',
            'category_id' => 'required|exists:case_categories,id',
            'subcategory_id' => 'sometimes|required|exists:case_sub_categories,id',
            'case_type' => 'required|in:individual,corporate',
            'client_name' => 'required|string|max:255',
            'case_files' => 'sometimes|nullable|array',
            'case_files.*.file' => 'required|file|mimes:png,jpeg,jpg,pdf,doc,docx,csv,avi,gl,mjpg,mov,moov,movie,mpeg,mpg,mp4,asf,xlsx,xlsm,xlc,xls,ppt,pptx,xml,text,txt|max:20480',
            'case_files.*.details' => 'sometimes|nullable|string',
            'capacity' => 'sometimes|nullable|string|max:255',
            'purpouse' => 'sometimes|nullable|string|min:3',
            'contract_term' => 'sometimes|nullable|string|min:3',
            'contract_ammount' => 'sometimes|nullable|string',
            'deadline' => 'sometimes|nullable|date_format:Y-m-d',
            'case_voice' => 'sometimes|nullable|file|mimes:audio/mpeg,mpga,mp3,wav,aac|max:20480',
            'against' => 'sometimes|nullable|string|min:3',
            'capacity2' => 'sometimes|nullable|string|min:3',
            'court_location' => 'sometimes|nullable|string|min:3',
            'expert_location' => 'sometimes|nullable|string|min:3',
            'chamber' => 'sometimes|nullable|string|min:1',
            'room' => 'sometimes|nullable|string|min:3',
            'automated_no' => 'sometimes|nullable|string|min:3',
            'court_case_no' => 'sometimes|nullable|string|min:3',
            'details' => 'sometimes|nullable|string|min:3',
            'subject' => 'sometimes|nullable|string|max:255'
        ];
    }
}
