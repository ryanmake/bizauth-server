<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MicroSkillPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'micro_skill_label' => 'bail|required|max:50',
            'micro_skill_description' => 'max:100'
        ];
    }

    public function messages()
    {
        return [
            'micro_skill_label.required' => 'Label is required.',
            'micro_skill_label.max' => 'Label should not exceed 15 characters',
            'micro_skill_description.max' => 'Description should not exceed 100 characters'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response($validator->errors()->getMessages()));
    }
}
