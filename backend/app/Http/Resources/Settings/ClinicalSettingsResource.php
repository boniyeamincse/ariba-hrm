<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicalSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'uhid_prefix' => $this->uhid_prefix,
            'opd_prefix' => $this->opd_prefix,
            'ipd_prefix' => $this->ipd_prefix,
            'prescription_prefix' => $this->prescription_prefix,
            'lab_order_prefix' => $this->lab_order_prefix,
            'radiology_order_prefix' => $this->radiology_order_prefix,
            'enable_eprescription' => $this->enable_eprescription,
            'enable_clinical_notes_template' => $this->enable_clinical_notes_template,
            'enable_icd10' => $this->enable_icd10,
            'enable_followup_reminder' => $this->enable_followup_reminder,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
