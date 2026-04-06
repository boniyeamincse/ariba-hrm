<?php

namespace App\Policies;

use App\Models\User;

abstract class BaseTenantPolicy
{
    protected function sameTenant(User $user, object $resource): bool
    {
        if (! isset($resource->tenant_id)) {
            return true;
        }

        return (int) $user->tenant_id === (int) $resource->tenant_id;
    }
}
