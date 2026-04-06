<?php

namespace App\Http\Resources\Api\V1\Rbac;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'permissions_count' => $this->permissions_count ?? $this->permissions->count(),
            'created_by' => $this->creator?->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
