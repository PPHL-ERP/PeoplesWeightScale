<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // update হলে রুটের {id} ধরব
        $id = $this->route('id');

        return [
            'vId'         => [
                'nullable', 'string', 'max:50',
                Rule::unique('w_vendor', 'vId')
                    ->ignore($id)                 // update-এ নিজের রেকর্ড বাদ
                    ->whereNull('deleted_at'),    // soft-deleted থাকলে allow
            ],
            'oldvId'      => ['nullable','string','max:50'],
            'vName'       => ['required','string','max:200'],
            'vNamebangla' => ['nullable','string','max:200'],
            'phone'       => ['nullable','string','max:30'],
            'address'     => ['nullable','string','max:500'],
            'note'        => ['nullable','string','max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'vName.required' => 'Vendor name is required.',
            'vId.unique'     => 'This Vendor ID is already used.',
        ];
    }
}