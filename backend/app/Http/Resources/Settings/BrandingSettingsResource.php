<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandingSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'theme_mode' => $this->theme_mode,
            'login_page_title' => $this->login_page_title,
            'footer_text' => $this->footer_text,
            'watermark_text' => $this->watermark_text,
            'white_label_enabled' => $this->white_label_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
