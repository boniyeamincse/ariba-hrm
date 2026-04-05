<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'prescribed_by',
        'instructions',
        'printable_content',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
