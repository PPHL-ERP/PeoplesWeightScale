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
            'weighing_id' => 'nullable|integer',
            'transaction_id' => 'nullable|string|max:128',
            'mode' => 'nullable|string|in:gross,tare',
            'camera_no' => 'required|string|max:16',
            'capture_datetime' => 'nullable|date',
            'capture_date' => 'nullable|date',
            'capture_time' => 'nullable|string',
            'image_base64' => 'required|string',
            'content_type' => 'required|string|in:image/png,image/jpeg',
            'checksum' => 'required|string|size:64',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages()
    {
        return [
            'camera_no.required' => 'camera_no is required',
            'image_base64.required' => 'image_base64 is required',
            'checksum.required' => 'checksum is required and must be sha256 hex of raw bytes',
        ];
    }
}
