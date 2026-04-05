<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $payload = collect($request->except(['password', 'password_confirmation', 'code']))
                ->map(function ($value) {
                    if (is_array($value)) {
                        return '[array]';
                    }

                    return $value;
                })
                ->all();

            AuditLog::create([
                'user_id' => $request->user()?->id,
                'tenant_id' => $request->attributes->get('tenant_id'),
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'payload' => [
                    'route' => $request->route()?->uri(),
                    'changed_fields' => array_keys($payload),
                    'input' => $payload,
                ],
            ]);
        }

        return $response;
    }
}
