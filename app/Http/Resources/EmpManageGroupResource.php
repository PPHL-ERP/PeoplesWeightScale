<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpManageGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'empSalesGroup' => [
                'id' => $this->empSalesGroup->id ?? null,
                'groupName' => $this->empSalesGroup->groupName ?? null,

            ],
            'empId' => $this->empId,
            'user' => [
                'id' => $this->user->id ?? null,
                'username' => $this->user->username ?? null,

            ],

        ];
    }
}
