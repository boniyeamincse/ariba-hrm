<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_order_id',
        'inventory_item_id',
        'item_name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];
}
