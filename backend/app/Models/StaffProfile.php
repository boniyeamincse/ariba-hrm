<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_code',
        'department',
        'designation',
        'base_salary',
        'joined_at',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'joined_at' => 'date',
    ];
}
