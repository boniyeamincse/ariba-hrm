<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'hospital_name' => $this->hospital_name,
            'hospital_code' => $this->hospital_code,
            'registration_no' => $this->registration_no,
            'license_no' => $this->license_no,
            'email' => $this->email,
            'phone' => $this->phone,
            'emergency_phone' => $this->emergency_phone,
            'website' => $this->website,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'language' => $this->language,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'logo_url' => $this->logo_url,
            'favicon_url' => $this->favicon_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
