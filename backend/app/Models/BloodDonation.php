<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'donor_name',
        'blood_group',
        'units',
        'donated_on',
    ];

    protected $casts = [
        'donated_on' => 'date',
    ];
}
