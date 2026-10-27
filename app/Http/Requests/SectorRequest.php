<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sectorId = $this->route('sector') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'companyId' => 'nullable|integer',
            'isFarm' => 'nullable',
            'isSalesPoint' => 'nullable',
            'salesPointName' => 'nullable|string|max:255',
            'feedDepotCost' => 'nullable',
            'chicksDepotCost' => 'nullable',
            'sectorType' => 'nullable',
            'inchargeName' => 'nullable',
            'inchargePhone' => 'nullable',
            'inchargeAddress' => 'nullable',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:255',

            // System-managed by HRM. Accepted only when an integration supplies it
            // (normally null on create and omitted on update so it is preserved).
            'sectorCode' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('sectors', 'sectorCode')->ignore($sectorId),
            ],
        ];
    }
}
