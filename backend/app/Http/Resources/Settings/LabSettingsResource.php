<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'sample_prefix' => $this->sample_prefix,
            'report_prefix' => $this->report_prefix,
            'barcode_enabled' => $this->barcode_enabled,
            'qr_report_enabled' => $this->qr_report_enabled,
            'critical_alert_enabled' => $this->critical_alert_enabled,
            'result_approval_required' => $this->result_approval_required,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
