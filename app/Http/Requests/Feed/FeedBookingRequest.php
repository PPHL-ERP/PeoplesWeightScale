<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedBookingRequest extends FormRequest
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
            'bookingType' => 'nullable|string|max:255',
            'bookingDate' => 'date|max:55',
            'invoiceDate' => 'date|max:55',
            'isBookingMoney' => 'nullable',
            'discount' => 'nullable',
            'discountType' => 'nullable|string|max:255',
            'advanceAmount' => 'nullable',
            'totalAmount' => 'nullable',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',
        ];
    }
}
