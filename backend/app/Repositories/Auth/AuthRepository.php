<?php

namespace App\Repositories\Auth;

use App\Models\AuthAuditLog;
use App\Models\AuthSession;
use App\Models\LoginAttempt;
use App\Models\Tenant;
use App\Models\User;

class AuthRepository
{
    public function resolveTenantByHost(string $host): ?Tenant
    {
        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return null;
        }

        return Tenant::query()->where('subdomain', $parts[0])->first();
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function getLoginAttempt(string $email, ?string $ip): LoginAttempt
    {
        return LoginAttempt::query()->firstOrCreate(
            ['email' => strtolower($email), 'ip_address' => $ip],
            ['attempts' => 0]
        );
    }

    public function clearLoginAttempt(string $email, ?string $ip): void
    {
        LoginAttempt::query()->where('email', strtolower($email))->where('ip_address', $ip)->delete();
    }

    public function createSession(User $user, ?string $deviceName, ?string $ipAddress, ?string $userAgent, string $tokenHash): AuthSession
    {
        return AuthSession::query()->create([
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_active_at' => now(),
            'token' => $tokenHash,
        ]);
    }

    public function listSessions(User $user)
    {
        return AuthSession::query()->where('user_id', $user->id)->latest()->get();
    }

    public function revokeSession(User $user, int $sessionId): ?AuthSession
    {
        $session = AuthSession::query()->where('user_id', $user->id)->whereKey($sessionId)->first();

        if ($session) {
            $session->delete();
        }

        return $session;
    }

    public function revokeAllSessions(User $user): void
    {
        AuthSession::query()->where('user_id', $user->id)->delete();
    }

    public function audit(?int $userId, ?int $tenantId, string $action, ?string $ip, ?string $userAgent, array $metadata = []): void
    {
        AuthAuditLog::query()->create([
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'action' => $action,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
