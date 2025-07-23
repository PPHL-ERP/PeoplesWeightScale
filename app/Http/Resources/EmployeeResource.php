<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'emp_id' => $this->emp_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            // 'facility' => $this->withFac ? new EmployeeFacilityResource($this->whenLoaded('facility')) : null,
            'doj' => $this->doj,
            'image' =>$this->image ? config('app.url') . "/" . config('imagepath.employee') . $this->image : asset('images/blank-image.png'),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'family_number' => $this->family_number,
            'nid' => $this->nid,
            'passport' => $this->passport,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'blood_group' => $this->blood_group,
            'current_address' => $this->current_address,
            'permanent_address' => $this->permanent_address,
            'status' => $this->status,
          ];
  }
}
