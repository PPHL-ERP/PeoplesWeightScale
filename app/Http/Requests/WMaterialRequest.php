<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // update হলে রুটের {id} ধরব
        $id = $this->route('id');

        return [
            'mId'         => [
                'nullable', 'string', 'max:50',
                Rule::unique('w_material', 'mId')
                    ->ignore($id)                 // update-এ নিজের রেকর্ড বাদ
                    ->whereNull('deleted_at'),    // soft-deleted থাকলে allow
            ],
            'oldmId'      => ['nullable','string','max:50'],
            'mName'       => ['required','string','max:200'],
            'mNameBangla' => ['nullable','string','max:200'],
            'categoryType' => ['nullable','string','max:200'],
            'note'        => ['nullable','string','max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mName.required' => 'Material name is required.',
            'mId.unique'     => 'This Material ID is already used.',
        ];
    }
}