<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Resolve tenant from subdomain and attach context to request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = (string) $request->getHost();
        $parts = explode('.', $host);

        $tenant = null;

        if (count($parts) > 2) {
            $tenant = Tenant::query()->where('subdomain', $parts[0])->first();

            if ($tenant) {
                app(TenantDatabaseManager::class)->switchToTenant($tenant);
                $request->attributes->set('tenant_id', $tenant->id);
            }
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
