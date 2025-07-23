<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'employeeId' => $this->employeeId,
            'isSuperAdmin' => $this->isSuperAdmin,
            'isAdmin' => $this->isAdmin,
            'email_verified_at' => $this->email_verified_at,
            'isBanned' => $this->isBanned,
            'image' =>$this->image ? config('app.url') . "/" . config('imagepath.user') . $this->image : asset('images/blank-image.png'),
            'ipAddress' => $this->ipAddress,
            'signature' =>$this->signature ? config('app.url') . "/" . config('imagepath.signature') . $this->signature : asset('images/blank-image.png'),
            'note' => $this->note,
            'status' => $this->status,
            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'userId' => $this->id,
                    'roleId' => $role->roleId,
                    'roleName' => $role->role->roleName ?? 'null',
                ];
            }),
            'sectors' => $this->sectors->map(function ($sector) {
                return [
                    'id' => $sector->id,
                    'userId' => $this->id,
                    'sectorId' => $sector->sectorId,
                    'name' => $sector->sector->name ?? 'null',
                ];
            }),
            'categories' => $this->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'userId' => $this->id,
                    'productCategoryId' => $category->productCategoryId,
                    'name' => $category->category->name ?? 'null',
                ];
            }),
            'empSalesGroup' => [
                'id' => $this->empSalesGroup->id ?? null,
                'groupName' => $this->empSalesGroup->groupName ?? null,

            ],
            'groupRole' => $this->groupRole,

        ];
    }
}
