<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy extends BaseTenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('billing.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.view') && $this->sameTenant($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('billing.manage');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.manage') && $this->sameTenant($user, $invoice);
    }
}
