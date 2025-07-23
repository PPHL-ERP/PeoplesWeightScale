<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class LogActivityResource extends JsonResource
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
            'subject' => $this->subject,
            'user_id' => $this->user_id,
            'user_name' => User::find($this->user_id)->name,
            'url' => $this->url,
            'method' => $this->method,
            'ip' => $this->ip,
            'agent' => $this->agent,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at->format('d M Y h:i:s a'),
            'updated_at' => $this->updated_at->format('d M Y h:i:s a'),
        ];
    }
}
