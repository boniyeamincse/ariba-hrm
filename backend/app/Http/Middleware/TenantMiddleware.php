<?php

namespace App\Http\Middleware;

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
            $tenant = $parts[0];
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
