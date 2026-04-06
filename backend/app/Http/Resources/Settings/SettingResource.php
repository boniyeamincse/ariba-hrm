<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'section' => $this['section'] ?? null,
            'data' => $this['data'] ?? [],
            'updated_at' => $this['updated_at'] ?? null,
            'updated_by' => $this['updated_by'] ?? null,
        ];
    }
}
