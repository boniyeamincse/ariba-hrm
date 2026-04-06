<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacySettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'dispense_prefix' => $this->dispense_prefix,
            'enable_batch_tracking' => $this->enable_batch_tracking,
            'enable_expiry_alert' => $this->enable_expiry_alert,
            'low_stock_threshold_mode' => $this->low_stock_threshold_mode,
            'allow_partial_dispense' => $this->allow_partial_dispense,
            'enforce_fefo' => $this->enforce_fefo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
