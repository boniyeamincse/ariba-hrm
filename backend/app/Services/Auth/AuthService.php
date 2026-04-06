<?php

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Services\PasswordPolicy;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private readonly AuthRepository $repository,
        private readonly PasswordPolicy $passwordPolicy,
        private readonly TwoFactorAuthenticator $twoFactorAuthenticator,
    ) {
    }

    public function login(Request $request, array $data): array
    {
        $tenant = $this->repository->resolveTenantByHost((string) $request->getHost());
        $user = $this->repository->findUserByEmail($data['email']);
        $attempt = $this->repository->getLoginAttempt($data['email'], $request->ip());

        if ($attempt->locked_until && now()->lessThan($attempt->locked_until)) {
            throw ValidationException::withMessages([
                'email' => 'Account temporarily locked. Try again later.',
            ]);
        }

        if (! $user || ! password_verify($data['password'], $user->password)) {
            $attempt->attempts = min(($attempt->attempts ?? 0) + 1, 10);
            if ($attempt->attempts >= 5) {
                $attempt->locked_until = now()->addMinutes(15);
            }
            $attempt->save();

            $this->repository->audit(
                userId: $user?->id,
                tenantId: $user?->tenant_id,
                action: 'failed_login',
                ip: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: ['email' => $data['email']],
            );

            throw new AuthenticationException('Invalid credentials.');
        }

        $this->validateStatusAndTenant($user, $tenant);

        $this->repository->clearLoginAttempt($data['email'], $request->ip());

        if ((bool) ($user->is_2fa_enabled ?? $user->two_factor_enabled)) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $challengeToken = Str::random(64);

            $user->forceFill([
                'otp_code' => $code,
                'otp_expires_at' => now()->addMinutes(5),
                'two_factor_code' => $code,
                'two_factor_expires_at' => now()->addMinutes(5),
                'two_factor_challenge_token' => $challengeToken,
                'two_factor_challenge_expires_at' => now()->addMinutes(5),
            ])->save();

            $this->repository->audit(
                userId: $user->id,
                tenantId: $user->tenant_id,
                action: 'login_2fa_challenge',
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return [
                '2fa_required' => true,
                'challenge_token' => $challengeToken,
                'otp_expires_at' => $user->two_factor_expires_at,
                'debug_otp' => app()->environment(['local', 'testing']) ? $code : null,
            ];
        }

        return $this->buildAuthenticatedPayload($request, $user);
    }

    public function verifyTwoFactor(Request $request, array $data): array
    {
        $user = User::query()->where('two_factor_challenge_token', $data['challenge_token'])->first();

        if (! $user || ! $user->two_factor_challenge_expires_at || now()->greaterThan($user->two_factor_challenge_expires_at)) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired 2FA challenge token.']);
        }

        $codeValid = hash_equals((string) ($user->otp_code ?? $user->two_factor_code), (string) $data['code']);
        if (! $codeValid && $user->two_factor_secret) {
            $codeValid = $this->twoFactorAuthenticator->verifyCode($user->two_factor_secret, $data['code']);
        }

        if (! $codeValid) {
            $this->repository->audit($user->id, $user->tenant_id, 'failed_2fa', $request->ip(), $request->userAgent());
            throw ValidationException::withMessages(['code' => 'Invalid 2FA code.']);
        }

        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_challenge_token' => null,
            'two_factor_challenge_expires_at' => null,
        ])->save();

        return $this->buildAuthenticatedPayload($request, $user);
    }

    public function resendOtp(Request $request, array $data): array
    {
        $user = User::query()->where('two_factor_challenge_token', $data['challenge_token'])->first();

        if (! $user) {
            throw ValidationException::withMessages(['challenge_token' => 'Invalid challenge token.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->forceFill([
            'otp_code' => $code,
            'otp_expires_at' => now()->addMinutes(5),
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(5),
        ])->save();

        $this->repository->audit($user->id, $user->tenant_id, 'otp_resent', $request->ip(), $request->userAgent());

        return [
            'message' => 'OTP resent successfully.',
            'otp_expires_at' => $user->two_factor_expires_at,
            'debug_otp' => app()->environment(['local', 'testing']) ? $code : null,
        ];
    }

    public function logout(Request $request, User $user): void
    {
        $current = $user->currentAccessToken();
        if ($current) {
            $this->revokeTokenByHash($user, $current->token);
            $current->delete();
        }

        $this->repository->audit($user->id, $user->tenant_id, 'logout', $request->ip(), $request->userAgent());
    }

    public function logoutAllDevices(Request $request, User $user): void
    {
        $user->tokens()->delete();
        $this->repository->revokeAllSessions($user);
        $this->repository->audit($user->id, $user->tenant_id, 'logout_all_devices', $request->ip(), $request->userAgent());
    }

    public function changePassword(Request $request, User $user, array $data): void
    {
        if (! password_verify($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
        }

        $this->passwordPolicy->ensureNotRecentlyUsed($user, $data['password']);

        $this->passwordPolicy->applyPassword($user, $data['password']);

        $this->repository->audit($user->id, $user->tenant_id, 'password_changed', $request->ip(), $request->userAgent());
    }

    public function sessions(User $user)
    {
        return $this->repository->listSessions($user);
    }

    public function revokeSession(Request $request, User $user, int $sessionId): void
    {
        $session = $this->repository->revokeSession($user, $sessionId);

        if (! $session) {
            throw ValidationException::withMessages(['session' => 'Session not found.']);
        }

        $this->revokeTokenByHash($user, $session->token);
        $this->repository->audit($user->id, $user->tenant_id, 'session_revoked', $request->ip(), $request->userAgent(), ['session_id' => $sessionId]);
    }

    public function enableTwoFactor(Request $request, User $user): array
    {
        $secret = $this->twoFactorAuthenticator->generateSecret();
        $user->forceFill([
            'is_2fa_enabled' => true,
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->repository->audit($user->id, $user->tenant_id, '2fa_enabled', $request->ip(), $request->userAgent());

        return [
            'enabled' => true,
            'method' => 'app',
            'debug_code' => app()->environment(['local', 'testing']) ? $this->twoFactorAuthenticator->currentCode($secret) : null,
        ];
    }

    public function disableTwoFactor(Request $request, User $user): void
    {
        $user->forceFill([
            'is_2fa_enabled' => false,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'otp_code' => null,
            'otp_expires_at' => null,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_challenge_token' => null,
            'two_factor_challenge_expires_at' => null,
        ])->save();

        $this->repository->audit($user->id, $user->tenant_id, '2fa_disabled', $request->ip(), $request->userAgent());
    }

    public function forgotPassword(array $data): string
    {
        return Password::sendResetLink(['email' => $data['email']]);
    }

    public function resetPassword(Request $request, array $data): string
    {
        return Password::reset(
            $data,
            function (User $user, string $password) use ($request): void {
                $this->passwordPolicy->ensureNotRecentlyUsed($user, $password);
                $this->passwordPolicy->applyPassword($user, $password);
                $this->repository->audit($user->id, $user->tenant_id, 'password_reset', $request->ip(), $request->userAgent());
            }
        );
    }

    public function registerTenantAdmin(Request $request, array $data): array
    {
        return DB::transaction(function () use ($request, $data): array {
            $tenant = Tenant::query()->create([
                'name' => $data['hospital_name'],
                'subdomain' => strtolower($data['subdomain']),
                'database_name' => $data['database_name'],
                'status' => 'active',
            ]);

            $user = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
            ]);

            $this->passwordPolicy->applyPassword($user, $data['password']);

            $role = Role::query()->firstOrCreate(['name' => 'tenant-admin']);
            $user->roles()->syncWithoutDetaching([$role->id]);

            $this->repository->audit($user->id, $tenant->id, 'tenant_admin_registered', $request->ip(), $request->userAgent());

            return ['user' => $user, 'tenant' => $tenant];
        });
    }

    public function verifyEmail(array $data): void
    {
        $user = User::query()->findOrFail($data['user_id']);
        $expectedHash = sha1($user->email);

        if (! hash_equals($expectedHash, $data['hash'])) {
            throw ValidationException::withMessages(['hash' => 'Invalid verification hash.']);
        }

        $user->forceFill(['email_verified_at' => now()])->save();
    }

    public function resendVerificationEmail(array $data): array
    {
        $user = User::query()->where('email', $data['email'])->firstOrFail();

        return [
            'user_id' => $user->id,
            'hash' => sha1($user->email),
        ];
    }

    public function refreshToken(Request $request): array
    {
        $bearer = $request->bearerToken();
        if (! $bearer) {
            throw ValidationException::withMessages(['token' => 'Bearer token required.']);
        }

        $token = PersonalAccessToken::findToken($bearer);
        if (! $token) {
            throw ValidationException::withMessages(['token' => 'Invalid token.']);
        }

        /** @var User $user */
        $user = $token->tokenable;
        $token->delete();

        return $this->buildAuthenticatedPayload($request, $user);
    }

    public function me(User $user): array
    {
        $user->load('roles.permissions', 'tenant');

        return [
            'user' => $user,
            'tenant' => $user->tenant,
            'roles' => $user->roles->pluck('name')->values(),
            'permissions' => $user->roles->flatMap(fn ($role) => $role->permissions->pluck('name'))->unique()->values(),
        ];
    }

    private function buildAuthenticatedPayload(Request $request, User $user): array
    {
        $plainTextToken = $user->createToken(Str::limit($request->userAgent() ?: 'api-token', 80, ''))->plainTextToken;
        $tokenHash = hash('sha256', $plainTextToken);

        $this->repository->createSession(
            user: $user,
            deviceName: $request->header('X-Device-Name') ?: Str::limit($request->userAgent() ?? 'Unknown Device', 191, ''),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            tokenHash: $tokenHash,
        );

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->repository->audit($user->id, $user->tenant_id, 'login', $request->ip(), $request->userAgent());

        $user->load('roles.permissions', 'tenant');

        return [
            'user' => $user,
            'token' => $plainTextToken,
            'tenant' => $user->tenant,
            'roles' => $user->roles->pluck('name')->values(),
            'permissions' => $user->roles->flatMap(fn ($role) => $role->permissions->pluck('name'))->unique()->values(),
            '2fa_required' => false,
        ];
    }

    private function validateStatusAndTenant(User $user, ?Tenant $requestTenant): void
    {
        if (($user->status ?? 'active') !== 'active') {
            throw ValidationException::withMessages(['email' => 'Account is not active.']);
        }

        if ($user->tenant_id) {
            if (! $requestTenant || (int) $requestTenant->id !== (int) $user->tenant_id) {
                throw ValidationException::withMessages(['email' => 'Tenant mismatch for this account.']);
            }

            if (in_array((string) $requestTenant->status, ['suspended', 'inactive', 'archived'], true)) {
                throw ValidationException::withMessages(['email' => 'Tenant is not active.']);
            }
        }
    }

    private function revokeTokenByHash(User $user, string $tokenHash): void
    {
        $user->tokens()->where('token', $tokenHash)->delete();
    }
}
