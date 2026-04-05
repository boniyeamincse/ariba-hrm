<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'category',
        'stock_on_hand',
        'reorder_level',
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];
}
