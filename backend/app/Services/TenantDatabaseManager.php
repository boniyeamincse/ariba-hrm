<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    public function switchToTenant(Tenant $tenant): void
    {
        $base = Config::get('database.connections.mysql');

        if (! is_array($base)) {
            return;
        }

        $tenantConnection = array_merge($base, [
            'database' => $tenant->database_name,
        ]);

        Config::set('database.connections.tenant', $tenantConnection);

        DB::purge('tenant');
    }
}
