<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'provider_name' => $this->provider_name,
            'api_key' => $this->api_key ? '********' : null,
            'api_secret' => $this->api_secret ? '********' : null,
            'sender_id' => $this->sender_id,
            'base_url' => $this->base_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
