<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class WCustomerRequest extends FormRequest
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
             'cId'         => [
                 'nullable', 'string', 'max:50',
                 Rule::unique('w_customer', 'cId')
                     ->ignore($id)                 // update-এ নিজের রেকর্ড বাদ
                     ->whereNull('deleted_at'),    // soft-deleted থাকলে allow
             ],
             'oldcId'      => ['nullable','string','max:50'],
             'cName'       => ['required','string','max:200'],
             'cNameBangla' => ['nullable','string','max:200'],
             'phone'       => ['nullable','string','max:30'],
             'address'     => ['nullable','string','max:500'],
             'note'        => ['nullable','string','max:1000'],
         ];
     }

     public function messages(): array
     {
         return [
             'cName.required' => 'Customer name is required.',
             'cId.unique'     => 'This Customer ID is already used.',
         ];
     }
}
