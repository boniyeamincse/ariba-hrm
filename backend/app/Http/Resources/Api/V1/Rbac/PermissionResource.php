<?php

namespace App\Http\Resources\Api\V1\Rbac;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'module_key' => $this->module_key,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'created_at' => $this->created_at,
        ];
    }
}
