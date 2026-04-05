<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\PasswordPolicy;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
class AuthController extends Controller
{
    public function __construct(
        private readonly PasswordPolicy $passwordPolicy,
        private readonly TwoFactorAuthenticator $twoFactorAuthenticator,
    ) {
    }

    public function bootstrapSuperAdmin(Request $request): JsonResponse
    {
        $existingSuperAdmin = User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'super-admin');
            })
            ->exists();

        if ($existingSuperAdmin) {
            return response()->json([
                'message' => 'Super admin already exists.',
            ], 409);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordPolicy->rules(),
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $this->passwordPolicy->applyPassword($user, $data['password']);

        $role = Role::query()->firstOrCreate(['name' => 'super-admin']);
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return response()->json([
            'message' => 'Super admin bootstrap completed.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        $rateLimiterKey = Str::lower($data['email']).'|'.$request->ip();

        if ($user?->lockout_until && now()->lessThan($user->lockout_until)) {
            return response()->json([
                'message' => 'Account is locked due to repeated failed logins.',
                'locked_until' => $user->lockout_until,
            ], 423);
        }

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            if ($user) {
                $user->forceFill([
                    'lockout_until' => now()->addSeconds($seconds),
                    'failed_login_attempts' => 5,
                ])->save();
            }

            return response()->json([
                'message' => 'Account is locked due to repeated failed logins.',
                'locked_until' => now()->addSeconds($seconds),
            ], 423);
        }

        if (! $user || ! password_verify($data['password'], $user->password)) {
            RateLimiter::hit($rateLimiterKey, 900);

            if ($user) {
                $attempts = min(RateLimiter::attempts($rateLimiterKey), 5);
                $lockoutUntil = $attempts >= 5 ? now()->addMinutes(15) : null;

                $user->forceFill([
                    'failed_login_attempts' => $attempts,
                    'lockout_until' => $lockoutUntil,
                ])->save();

                if ($attempts >= 5) {
                    return response()->json([
                        'message' => 'Account is locked due to repeated failed logins.',
                        'locked_until' => $lockoutUntil,
                    ], 423);
                }
            }

            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($user->password_expires_at && now()->greaterThan($user->password_expires_at)) {
            return response()->json([
                'message' => 'Password expired. Please reset your password.',
            ], 423);
        }

        RateLimiter::clear($rateLimiterKey);
        $user->forceFill([
            'failed_login_attempts' => 0,
            'lockout_until' => null,
        ])->save();

        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $challengeToken = Str::random(64);

            $user->forceFill([
                'two_factor_challenge_token' => $challengeToken,
                'two_factor_challenge_expires_at' => now()->addMinutes(10),
            ])->save();

            return response()->json([
                'message' => '2FA code generated. Use /auth/2fa/verify to complete login.',
                'two_factor_required' => true,
                'challenge_token' => $challengeToken,
                'debug_code' => app()->environment(['local', 'testing'])
                    ? $this->twoFactorAuthenticator->currentCode($user->two_factor_secret)
                    : null,
            ]);
        }

        $token = $this->issueToken($user, $request);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'token' => $this->issueToken($user, $request),
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('roles.permissions'),
        ]);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json([
            'sessions' => $request->user()->tokens()
                ->orderByDesc('last_used_at')
                ->get(['id', 'name', 'last_used_at', 'created_at'])
                ->map(fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'current' => $request->user()->currentAccessToken()?->id === $token->id,
                ])
                ->values(),
        ]);
    }

    public function revokeSession(Request $request, string $tokenId): JsonResponse
    {
        $request->user()->tokens()->whereKey($tokenId)->delete();

        return response()->json(['message' => 'Session revoked successfully.']);
    }

    public function revokeAllSessions(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'All sessions revoked successfully.']);
    }

    public function setupTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();
        $secret = $this->twoFactorAuthenticator->generateSecret();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'secret' => $secret,
            'debug_code' => app()->environment(['local', 'testing'])
                ? $this->twoFactorAuthenticator->currentCode($secret)
                : null,
        ]);
    }

    public function enableTwoFactor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return response()->json(['message' => '2FA secret has not been generated.'], 400);
        }

        if (! $this->twoFactorAuthenticator->verifyCode($user->two_factor_secret, $data['code'])) {
            return response()->json(['message' => 'Invalid 2FA code.'], 400);
        }

        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json(['message' => '2FA enabled successfully.']);
    }

    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->user()->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_challenge_token' => null,
            'two_factor_challenge_expires_at' => null,
        ])->save();

        return response()->json(['message' => '2FA disabled successfully.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(['email' => $data['email']]);

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => $this->passwordPolicy->rules(confirmed: true),
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $this->passwordPolicy->ensureNotRecentlyUsed($user, $password);

                $user->forceFill([
                    'remember_token' => Str::random(60),
                ])->save();

                $this->passwordPolicy->applyPassword($user, $password);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 400);
        }

        return response()->json(['message' => __($status)]);
    }

    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_token' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::query()
            ->where('two_factor_challenge_token', $data['challenge_token'])
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid 2FA challenge.'], 400);
        }

        if (! $user->two_factor_enabled || ! $user->two_factor_secret) {
            return response()->json(['message' => '2FA is not enabled for this user.'], 400);
        }

        if (! $user->two_factor_challenge_expires_at || now()->greaterThan($user->two_factor_challenge_expires_at)) {
            return response()->json(['message' => '2FA code expired.'], 400);
        }

        if (! $this->twoFactorAuthenticator->verifyCode($user->two_factor_secret, $data['code'])) {
            return response()->json(['message' => 'Invalid 2FA code.'], 400);
        }

        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_challenge_token' => null,
            'two_factor_challenge_expires_at' => null,
        ])->save();

        $token = $this->issueToken($user, $request);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    private function issueToken(User $user, Request $request): string
    {
        $tokenName = Str::limit($request->userAgent() ?: 'api-token', 80, '');

        return $user->createToken($tokenName)->plainTextToken;
    }
}
