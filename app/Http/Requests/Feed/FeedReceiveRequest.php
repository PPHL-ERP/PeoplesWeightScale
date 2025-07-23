<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class FeedReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'transferId' => 'required|integer',
            'transferFrom' => 'required|integer',
            'recHead' => 'required|string',
            'recStore' => 'required|integer',
            'chalanNo' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
            'unLoadBy' => 'nullable',
            'labourGroupId' => 'nullable',
            'labourBill' => 'nullable',
            'isLabourBill' => 'nullable',
            'details' => 'required|array',
            'details.*.productId' => 'required|integer',
           // 'details.*.trQty' => 'required|integer',
            //'details.*.rQty' => 'required|integer',
           // 'details.*.deviationQty' => 'required|integer',
            'details.*.batchNo' => 'nullable|string',
            'details.*.note' => 'nullable|string',
        ];
    }
}