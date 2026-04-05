<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'period',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
