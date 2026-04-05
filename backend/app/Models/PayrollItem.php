<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'staff_profile_id',
        'basic',
        'allowance',
        'deduction',
        'net_pay',
    ];

    protected $casts = [
        'basic' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];
}
