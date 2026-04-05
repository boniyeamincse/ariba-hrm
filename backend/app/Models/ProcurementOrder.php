<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'po_no',
        'supplier_name',
        'status',
        'total_amount',
        'ordered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementOrderItem::class);
    }
}
