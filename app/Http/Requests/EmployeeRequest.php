<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
    public function rules()
    {
       // $employeeId = $this->route('employee');

        $rules = [

            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'doj' => 'date|max:255',
            // 'image'      => 'nullable|image',
            'email'      => 'nullable|email',
            // 'phone_number' => 'string|max:14|unique:employees,phone_number,'.$employeeId,
            'phone_number' => 'nullable|string|max:14',
            'family_number' => 'nullable|string|max:14',
            'nid' => 'nullable|string|max:20',
            'passport' => 'nullable|string|max:15',
            'dob' => 'date|max:55',
            'gender' => 'string|max:55',
            'marital_status' => 'string|max:255',
            'blood_group' => 'nullable|string|max:55',
            'permanent_address' => 'nullable|string|max:255',
            'current_address' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:55',
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            unset($rules['first_name'], $rules['last_name'], $rules['email']);
        }
        return $rules;
    }
}
