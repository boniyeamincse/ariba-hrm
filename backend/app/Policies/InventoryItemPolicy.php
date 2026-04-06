<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy extends BaseTenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('inventory.view');
    }

    public function view(User $user, InventoryItem $item): bool
    {
        return $user->hasPermission('inventory.view') && $this->sameTenant($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('inventory.manage');
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return $user->hasPermission('inventory.manage') && $this->sameTenant($user, $item);
    }
}
