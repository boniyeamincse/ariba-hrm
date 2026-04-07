<?php

namespace App\Repositories\Staff;

use App\Models\Staff;
use App\Models\StaffAuditLog;
use App\Models\StaffDocument;
use App\Models\StaffEmergencyContact;
use App\Models\StaffEmploymentHistory;
use App\Models\StaffLicense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StaffRepository
{
    public function __construct(private readonly int $tenantId)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Staff::query()
            ->tenant($this->tenantId)
            ->with(['manager', 'branch', 'facility', 'user'])
            ->withCount(['licenses', 'documents', 'emergencyContacts', 'subordinates']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['facility_id'])) {
            $query->where('facility_id', (int) $filters['facility_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        if (! empty($filters['designation'])) {
            $query->where('designation', $filters['designation']);
        }

        if (! empty($filters['staff_type'])) {
            $query->where('staff_type', $filters['staff_type']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['manager_staff_id'])) {
            $query->where('manager_staff_id', (int) $filters['manager_staff_id']);
        }

        if (! empty($filters['join_date_from'])) {
            $query->whereDate('join_date', '>=', $filters['join_date_from']);
        }

        if (! empty($filters['join_date_to'])) {
            $query->whereDate('join_date', '<=', $filters['join_date_to']);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function ($q) use ($term): void {
                $q->where('employee_code', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $sortBy = (string) ($filters['sort_by'] ?? 'created_at');
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'updated_at', 'join_date', 'employee_code', 'first_name', 'last_name'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDir);

        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 15)));

        return $query->paginate($perPage);
    }

    public function findOrFail(int $staffId): Staff
    {
        return Staff::query()
            ->tenant($this->tenantId)
            ->with(['manager', 'branch', 'facility', 'user'])
            ->withCount(['licenses', 'documents', 'emergencyContacts', 'subordinates'])
            ->findOrFail($staffId);
    }

    public function create(array $data): Staff
    {
        $data['tenant_id'] = $this->tenantId;

        return Staff::query()->create($data)->fresh();
    }

    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);

        return $staff->fresh();
    }

    public function softDelete(Staff $staff): void
    {
        $staff->delete();
    }

    public function options(?string $q = null): Collection
    {
        $query = Staff::query()
            ->tenant($this->tenantId)
            ->select(['id', 'employee_code', 'first_name', 'middle_name', 'last_name', 'status'])
            ->orderBy('first_name');

        if ($q) {
            $query->where(function ($sub) use ($q): void {
                $sub->where('employee_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        return $query->limit(100)->get();
    }

    public function listLicenses(int $staffId): Collection
    {
        return StaffLicense::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function createLicense(int $staffId, array $data): StaffLicense
    {
        return StaffLicense::query()->create([
            ...$data,
            'tenant_id' => $this->tenantId,
            'staff_id' => $staffId,
        ])->fresh();
    }

    public function updateLicense(int $staffId, int $licenseId, array $data): StaffLicense
    {
        $license = StaffLicense::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->findOrFail($licenseId);

        $license->update($data);

        return $license->fresh();
    }

    public function deleteLicense(int $staffId, int $licenseId): void
    {
        StaffLicense::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->findOrFail($licenseId)
            ->delete();
    }

    public function listEmergencyContacts(int $staffId): Collection
    {
        return StaffEmergencyContact::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function createEmergencyContact(int $staffId, array $data): StaffEmergencyContact
    {
        return StaffEmergencyContact::query()->create([
            ...$data,
            'tenant_id' => $this->tenantId,
            'staff_id' => $staffId,
        ])->fresh();
    }

    public function updateEmergencyContact(int $staffId, int $contactId, array $data): StaffEmergencyContact
    {
        $contact = StaffEmergencyContact::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->findOrFail($contactId);

        $contact->update($data);

        return $contact->fresh();
    }

    public function deleteEmergencyContact(int $staffId, int $contactId): void
    {
        StaffEmergencyContact::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->findOrFail($contactId)
            ->delete();
    }

    public function listDocuments(int $staffId): Collection
    {
        return StaffDocument::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();
    }

    public function createDocument(int $staffId, array $data): StaffDocument
    {
        return StaffDocument::query()->create([
            ...$data,
            'tenant_id' => $this->tenantId,
            'staff_id' => $staffId,
        ])->fresh();
    }

    public function deleteDocument(int $staffId, int $documentId): void
    {
        StaffDocument::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->findOrFail($documentId)
            ->delete();
    }

    public function listEmploymentHistory(int $staffId): Collection
    {
        return StaffEmploymentHistory::query()
            ->where('tenant_id', $this->tenantId)
            ->where('staff_id', $staffId)
            ->with('creator')
            ->orderByDesc('created_at')
            ->get();
    }

    public function addEmploymentHistory(int $staffId, array $data): StaffEmploymentHistory
    {
        return StaffEmploymentHistory::query()->create([
            ...$data,
            'tenant_id' => $this->tenantId,
            'staff_id' => $staffId,
            'created_by' => auth()->id(),
        ])->fresh();
    }

    public function logAudit(
        string $action,
        string $targetType,
        ?int $targetId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        StaffAuditLog::query()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
