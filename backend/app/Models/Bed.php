<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ward_id',
        'bed_number',
        'bed_type',
        'is_occupied',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
