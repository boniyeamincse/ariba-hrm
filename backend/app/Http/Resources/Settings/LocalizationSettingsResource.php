<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocalizationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'default_language' => $this->default_language,
            'supported_languages' => $this->supported_languages,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'number_format' => $this->number_format,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
