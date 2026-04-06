<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'invoice_prefix' => $this->invoice_prefix,
            'receipt_prefix' => $this->receipt_prefix,
            'estimate_prefix' => $this->estimate_prefix,
            'refund_prefix' => $this->refund_prefix,
            'tax_name' => $this->tax_name,
            'tax_percentage' => $this->tax_percentage,
            'invoice_footer' => $this->invoice_footer,
            'auto_generate_invoice_number' => $this->auto_generate_invoice_number,
            'allow_manual_discount' => $this->allow_manual_discount,
            'require_discount_approval' => $this->require_discount_approval,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
