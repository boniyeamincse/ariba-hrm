<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'blood_group',
        'units_available',
    ];
}
