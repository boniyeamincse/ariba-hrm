<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_challenge_token',
        'two_factor_challenge_expires_at',
        'failed_login_attempts',
        'lockout_until',
        'password_changed_at',
        'password_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
        'two_factor_secret',
        'two_factor_challenge_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_challenge_expires_at' => 'datetime',
            'lockout_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'password_expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission): void {
                $query->where('name', $permission);
            })
            ->exists();
    }
}
