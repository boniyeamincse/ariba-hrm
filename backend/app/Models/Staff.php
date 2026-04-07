<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PROBATION = 'probation';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_RESIGNED = 'resigned';

    protected $table = 'staff';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'marital_status',
        'phone',
        'alternate_phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'zip_code',
        'photo_path',
        'branch_id',
        'facility_id',
        'department_id',
        'designation',
        'staff_type',
        'category',
        'manager_staff_id',
        'employment_type',
        'join_date',
        'confirmation_date',
        'probation_end_date',
        'exit_date',
        'status',
        'payroll_group',
        'basic_salary',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['full_name'];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'confirmation_date' => 'date',
        'probation_end_date' => 'date',
        'exit_date' => 'date',
        'basic_salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'manager_staff_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Staff::class, 'manager_staff_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(StaffLicense::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(StaffEmergencyContact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(StaffEmploymentHistory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
