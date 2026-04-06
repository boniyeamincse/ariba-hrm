<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy extends BaseTenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('appointment.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('appointment.view') && $this->sameTenant($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('appointment.manage');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('appointment.manage') && $this->sameTenant($user, $appointment);
    }
}
