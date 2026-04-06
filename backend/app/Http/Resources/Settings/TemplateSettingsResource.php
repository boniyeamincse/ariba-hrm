<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'prescription_template' => $this->prescription_template,
            'invoice_template' => $this->invoice_template,
            'lab_report_template' => $this->lab_report_template,
            'discharge_summary_template' => $this->discharge_summary_template,
            'sick_leave_template' => $this->sick_leave_template,
            'consent_form_template' => $this->consent_form_template,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
