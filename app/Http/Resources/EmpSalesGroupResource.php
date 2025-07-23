<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpSalesGroupResource extends JsonResource
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
            'groupName' => $this->groupName,
            'groupLocation' => $this->groupLocation,
            'groupLeader' => $this->groupLeader,
            'groupSup' => $this->groupSup,
            'note' => $this->note,
            'status' => $this->status,

            // 'employees' => $this->managedEmployees
            // ->map(fn ($mg) => $mg->user?->name)
            // ->filter() // remove nulls if any
            // ->values()
            // ->implode(', '), // return as string: "A, B, C"
            // // Optional: count of employees
            // 'employeeCount' => $this->managedEmployees->count(),

              // Send empIds, frontend will map them to names
            'employeeIds' => $this->managedEmployees
            ->pluck('empId')
            ->filter()
            ->values()
            ->toArray(),
            'employeeCount' => $this->managedEmployees->count(),
        ];
      }
}