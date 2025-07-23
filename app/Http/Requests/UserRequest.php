<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;



class UserRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $userId = $this->route('id'); // Get the user ID from the route

        $rules = [
            'name' => 'required|string|max:255',
            'groupRole' => 'nullable',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'username' => 'nullable|string|max:255|unique:users,username,' . $userId,
            'status' => 'nullable|integer',
            'password' => ['sometimes', 'required', Rules\Password::defaults()],
        ];
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            unset($rules['name'], $rules['email'], $rules['phone_number']);
        }

        return $rules;
    }
}
