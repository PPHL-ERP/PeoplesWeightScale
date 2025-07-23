<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resourceId, // Assuming resourceId is the ID of the resource
            'resource_name' => $this->resource->resourceName,
            'role' => [
                'id' => $this->roleId,
                'name' => $this->roleName,
            ],
            'permissions' => $this->permissions->map(function ($permission) {
                return [
                    'id' => $permission['permissionId'],
                    'name' => $permission['permissionName'],
                ];
            }),
        ];
    }
}
