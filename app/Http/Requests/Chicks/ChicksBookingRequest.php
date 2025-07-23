<?php

namespace App\Http\Requests\Chicks;

use Illuminate\Foundation\Http\FormRequest;

class ChicksBookingRequest extends FormRequest
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

            'bookingType' => 'nullable',
            'isBookingMoney' => 'nullable',
            'isMultiDelivery' => 'nullable',
            'bookingDate' => 'date|max:55',
            'invoiceDate' => 'date|max:55',
            'discount' => 'nullable',
            'discountType' => 'nullable',
            'advanceAmount' => 'nullable',
            'totalAmount' => 'nullable',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',

        ];
    }
}
