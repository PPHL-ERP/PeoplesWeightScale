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
            // New policy: frontend may provide a weighing_id (predicted if necessary)
            'weighing_id' => 'nullable|integer',
            'transaction_id' => 'nullable|string|max:128',
            'sector_id' => 'nullable|integer',
            'mode' => 'nullable|string|in:gross,tare',
            'camera_no' => 'required|string|max:16',
            'capture_datetime' => 'nullable|date',
            'capture_date' => 'nullable|date',
            'capture_time' => 'nullable|string',
            'image_base64' => 'required_without:image_file|string',
            'image_file' => 'nullable|file|mimes:png,jpeg,webp|sometimes|max:5120',
            'content_type' => 'nullable|string|in:image/png,image/jpeg,image/webp',
            'checksum' => 'nullable|string|size:64',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages()
    {
        return [
            'camera_no.required' => 'camera_no is required',
            'image_base64.required' => 'image_base64 is required',
            // checksum may be omitted; server will compute if absent
            // weighing_id is optional; frontend is encouraged to provide it if available
        ];
    }
}
