<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'batch_no',
        'expiry_date',
        'quantity_received',
        'quantity_available',
        'purchase_price',
        'selling_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }
}
