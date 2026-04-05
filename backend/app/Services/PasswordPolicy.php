<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordPolicy
{
    public function rules(bool $confirmed = false): array
    {
        $rules = [
            'required',
            'string',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[^A-Za-z0-9]/',
        ];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }

    public function ensureNotRecentlyUsed(User $user, string $plainPassword): void
    {
        $recentHashes = $user->passwordHistories()
            ->latest('created_at')
            ->limit(5)
            ->pluck('password_hash');

        foreach ($recentHashes as $passwordHash) {
            if (Hash::check($plainPassword, $passwordHash)) {
                throw ValidationException::withMessages([
                    'password' => ['Password cannot match any of the last 5 passwords.'],
                ]);
            }
        }
    }

    public function applyPassword(User $user, string $plainPassword, int $expiryDays = 90): void
    {
        $user->forceFill([
            'password' => $plainPassword,
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays($expiryDays),
        ])->save();

        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => $user->password,
            'created_at' => now(),
        ]);
    }
}