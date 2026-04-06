<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'hl7_enabled' => $this->hl7_enabled,
            'fhir_enabled' => $this->fhir_enabled,
            'webhook_enabled' => $this->webhook_enabled,
            'api_access_enabled' => $this->api_access_enabled,
            'third_party_integration_enabled' => $this->third_party_integration_enabled,
            'pacs_enabled' => $this->pacs_enabled,
            'payment_gateway_enabled' => $this->payment_gateway_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
