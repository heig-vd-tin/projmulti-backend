<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectFormRequest extends FormRequest
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
            'title' => 'required|max:100',
            'orientations' => 'required|array|min:1',
            'orientations.*.id' => 'required',
            'orientations.*.importance' => 'required|integer',
            'tags' => 'array|max:3',
        ];
    }
}
