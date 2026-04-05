<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'uhid',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'phone',
        'email',
        'address',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function history(): HasOne
    {
        return $this->hasOne(PatientHistory::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(PatientVisit::class)->latest('visit_at');
    }
}
