<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpdSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'admission_prefix' => $this->admission_prefix,
            'discharge_prefix' => $this->discharge_prefix,
            'bed_transfer_prefix' => $this->bed_transfer_prefix,
            'enable_bed_reservation' => $this->enable_bed_reservation,
            'allow_direct_admission' => $this->allow_direct_admission,
            'require_guarantor_info' => $this->require_guarantor_info,
            'enable_discharge_approval' => $this->enable_discharge_approval,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
