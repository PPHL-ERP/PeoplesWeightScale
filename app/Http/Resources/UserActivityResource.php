<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_name' => User::find($this->user_id)->name,
            'module_name' => $this->module_name,
            'message' => $this->message,
            'module_details' => json_decode($this->module_details),
            'created_at' => $this->created_at->format('d M Y h:i:s a'),
            'updated_at' => $this->updated_at->format('d M Y h:i:s a'),
        ];
    }
}
