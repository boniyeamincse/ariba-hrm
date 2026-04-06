<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }
}
