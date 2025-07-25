<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UpazilaResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'bn_name' => $this->bn_name,
        ];
    }
}
