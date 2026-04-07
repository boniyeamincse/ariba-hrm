<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'staff_id' => $this->staff_id,
            'document_type' => $this->document_type,
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_by_user' => $this->whenLoaded('uploader', function () {
                return [
                    'id' => $this->uploader?->id,
                    'name' => $this->uploader?->name,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
