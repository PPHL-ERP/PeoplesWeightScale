<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EggReceiveRequest extends FormRequest
{
    public function rules()
{
    return [
        //'transferId' => 'required|integer',
        //'transferFrom' => 'required|array', // Expect transferFrom to be an array
       // 'transferFrom.id' => 'required|integer', // Ensure transferFrom contains an id
        //'transferFrom.name' => 'required|string', // Ensure transferFrom contains a name


        'transferFrom' => 'required|integer',
        'recHead' => 'required|string',
        'recStore' => 'required|integer',
        'chalanNo' => 'required|string',
        'date' => 'required|date',
        'remarks' => 'nullable|string|max:500',
        'unLoadBy' => 'required|string',
        'labourGroupId' => 'nullable',
        'labourBill' => 'nullable',
        'details' => 'required|array',
        'details.*.productId' => 'required|integer',
       // 'details.*.trQty' => 'required|integer',
        //'details.*.rQty' => 'required|integer',
       // 'details.*.deviationQty' => 'required|integer',
        'details.*.batchNo' => 'nullable|string',
        'details.*.note' => 'nullable|string',
    ];
}

    public function authorize(): bool
    {
        return true;
    }
}
