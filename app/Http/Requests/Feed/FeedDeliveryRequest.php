<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedDeliveryRequest extends FormRequest
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
        return [
            'deliveryPointDetails' => 'nullable',
            'deliveryDate' => 'date|max:55',
            'transportType' => 'nullable|string|max:255',
            'roadInfo' => 'nullable',
            'driverName' => 'nullable',
            'mobile' => 'nullable',
            'vehicleNo' => 'nullable',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}