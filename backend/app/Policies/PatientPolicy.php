<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy extends BaseTenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('patient.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patient.view') && $this->sameTenant($user, $patient);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('patient.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patient.update') && $this->sameTenant($user, $patient);
    }
}
