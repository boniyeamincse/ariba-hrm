<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterTenantAdminRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\VerifyTwoFactorRequest;
use App\Http\Resources\Auth\AuthSessionResource;
use App\Http\Resources\Auth\AuthUserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request, $request->validated());

        if (($result['2fa_required'] ?? false) === true) {
            return response()->json([
                'status' => 'success',
                'message' => '2FA verification required.',
                'data' => $result,
            ], 202);
        }

        return $this->loginResponse($result);
    }

    public function verifyTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        $result = $this->service->verifyTwoFactor($request, $request->validated());

        return $this->loginResponse($result);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'OTP resent successfully.',
            'data' => $this->service->resendOtp($request, $request->validated()),
        ]);
    }

    public function registerTenantAdmin(RegisterTenantAdminRequest $request): JsonResponse
    {
        $result = $this->service->registerTenantAdmin($request, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant admin registration completed.',
            'data' => [
                'user' => new AuthUserResource($result['user']),
                'tenant' => $result['tenant'],
            ],
        ], 201);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $result = $this->service->refreshToken($request);

        return $this->loginResponse($result);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->service->forgotPassword($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => __($status),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->service->resetPassword($request, $request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'error',
                'message' => __($status),
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => __($status),
        ]);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $this->service->verifyEmail($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.',
        ]);
    }

    public function resendVerificationEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $this->service->resendVerificationEmail($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email sent.',
            'data' => $data,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $data = $this->service->me($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Authenticated user fetched successfully.',
            'data' => [
                'user' => new AuthUserResource($data['user']),
                'tenant' => $data['tenant'],
                'roles' => $data['roles'],
                'permissions' => $data['permissions'],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful.',
        ]);
    }

    public function logoutAllDevices(Request $request): JsonResponse
    {
        $this->service->logoutAllDevices($request, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from all devices successfully.',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->changePassword($request, $request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ]);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Sessions fetched successfully.',
            'data' => AuthSessionResource::collection($this->service->sessions($request->user())),
        ]);
    }

    public function revokeSession(Request $request, int $id): JsonResponse
    {
        $this->service->revokeSession($request, $request->user(), $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Session revoked successfully.',
        ]);
    }

    public function enableTwoFactor(Request $request): JsonResponse
    {
        $data = $this->service->enableTwoFactor($request, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => '2FA enabled successfully.',
            'data' => $data,
        ]);
    }

    public function disableTwoFactor(Request $request): JsonResponse
    {
        $this->service->disableTwoFactor($request, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => '2FA disabled successfully.',
        ]);
    }

    public function twoFactorStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => '2FA status fetched.',
            'data' => [
                'enabled' => (bool) ($user->is_2fa_enabled ?? $user->two_factor_enabled),
                'confirmed_at' => $user->two_factor_confirmed_at,
            ],
        ]);
    }

    private function loginResponse(array $result): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => new AuthUserResource($result['user']),
                'token' => $result['token'],
                'tenant' => $result['tenant'],
                'roles' => $result['roles'],
                'permissions' => $result['permissions'],
                '2fa_required' => false,
            ],
        ]);
    }
}
