<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize()
    {
        // Use middleware for auth; allow here
        return true;
    }

    public function rules()
    {
        return [
            'transaction_id' => 'required|string|max:128',
            'camera_no' => 'required|string|max:16',
            'capture_datetime' => 'nullable|date',
            'capture_date' => 'nullable|date',
            'capture_time' => 'nullable|string',
            'image_base64' => 'required|string',
            'content_type' => 'nullable|string',
            'checksum' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'transaction_id.required' => 'transaction_id is required',
            'camera_no.required' => 'camera_no is required',
            'image_base64.required' => 'image_base64 is required',
        ];
    }
}
