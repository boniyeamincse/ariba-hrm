<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelemedicineSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'appointment_id',
        'provider',
        'meeting_id',
        'meeting_url',
        'status',
    ];
}
