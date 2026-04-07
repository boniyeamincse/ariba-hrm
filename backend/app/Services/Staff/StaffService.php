<?php

namespace App\Services\Staff;

use App\Models\Facility;
use App\Models\Staff;
use App\Repositories\Staff\StaffRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffService
{
    private StaffRepository $repository;

    public function __construct(private readonly int $tenantId)
    {
        $this->repository = new StaffRepository($tenantId);
    }

    public function listStaff(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->list($filters);
    }

    public function createStaff(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->prepareStaffPayload($data);
            $this->guardFacilityBranchConsistency($payload);

            $staff = $this->repository->create($payload);

            $this->repository->addEmploymentHistory($staff->id, [
                'action' => 'staff_created',
                'old_status' => null,
                'new_status' => $staff->status,
                'remarks' => null,
                'effective_date' => $staff->join_date,
            ]);

            $this->repository->logAudit(
                action: 'staff_created',
                targetType: 'staff',
                targetId: $staff->id,
                oldValues: null,
                newValues: $staff->toArray()
            );

            return $staff;
        });
    }

    public function getStaff(int $staffId): Staff
    {
        return $this->repository->findOrFail($staffId);
    }

    public function updateStaff(int $staffId, array $data): Staff
    {
        return DB::transaction(function () use ($staffId, $data) {
            $staff = $this->repository->findOrFail($staffId);
            $old = $staff->toArray();

            $payload = $this->prepareStaffPayload($data);
            $this->guardFacilityBranchConsistency($payload);

            $updated = $this->repository->update($staff, $payload);

            if (($old['status'] ?? null) !== ($updated->status ?? null)) {
                $this->repository->addEmploymentHistory($updated->id, [
                    'action' => 'staff_status_changed',
                    'old_status' => $old['status'] ?? null,
                    'new_status' => $updated->status,
                    'remarks' => null,
                    'effective_date' => now()->toDateString(),
                ]);
            }

            $this->repository->logAudit(
                action: 'staff_updated',
                targetType: 'staff',
                targetId: $updated->id,
                oldValues: $old,
                newValues: $updated->toArray()
            );

            return $updated;
        });
    }

    public function deleteStaff(int $staffId): void
    {
        DB::transaction(function () use ($staffId): void {
            $staff = $this->repository->findOrFail($staffId);

            if ($staff->status === Staff::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'staff' => 'Active staff cannot be deleted. Change status before deletion.',
                ]);
            }

            $old = $staff->toArray();
            $this->repository->softDelete($staff);

            $this->repository->addEmploymentHistory($staff->id, [
                'action' => 'staff_deleted',
                'old_status' => $old['status'] ?? null,
                'new_status' => null,
                'remarks' => 'Soft deleted',
                'effective_date' => now()->toDateString(),
            ]);

            $this->repository->logAudit(
                action: 'staff_deleted',
                targetType: 'staff',
                targetId: $staff->id,
                oldValues: $old,
                newValues: null
            );
        });
    }

    public function updateStatus(int $staffId, string $newStatus, ?string $remarks = null, ?string $effectiveDate = null): Staff
    {
        return DB::transaction(function () use ($staffId, $newStatus, $remarks, $effectiveDate) {
            $staff = $this->repository->findOrFail($staffId);
            $old = $staff->toArray();

            $this->assertTransitionAllowed($staff->status, $newStatus);

            $payload = [
                'status' => $newStatus,
                'updated_by' => auth()->id(),
            ];

            if (in_array($newStatus, [Staff::STATUS_TERMINATED, Staff::STATUS_RESIGNED], true)) {
                $payload['exit_date'] = $effectiveDate ?: now()->toDateString();
            }

            $updated = $this->repository->update($staff, $payload);

            $this->repository->addEmploymentHistory($updated->id, [
                'action' => 'staff_status_changed',
                'old_status' => $old['status'] ?? null,
                'new_status' => $newStatus,
                'remarks' => $remarks,
                'effective_date' => $effectiveDate ?: now()->toDateString(),
            ]);

            $this->repository->logAudit(
                action: 'staff_status_changed',
                targetType: 'staff',
                targetId: $updated->id,
                oldValues: $old,
                newValues: $updated->toArray()
            );

            return $updated;
        });
    }

    public function assignBranch(int $staffId, int $branchId): Staff
    {
        return $this->updateStaff($staffId, ['branch_id' => $branchId]);
    }

    public function assignFacility(int $staffId, int $facilityId): Staff
    {
        return DB::transaction(function () use ($staffId, $facilityId) {
            $staff = $this->repository->findOrFail($staffId);

            $facility = Facility::query()
                ->where('tenant_id', $this->tenantId)
                ->findOrFail($facilityId);

            $data = [
                'facility_id' => $facilityId,
                'branch_id' => $facility->branch_id,
            ];

            $old = $staff->toArray();
            $updated = $this->repository->update($staff, [
                ...$data,
                'updated_by' => auth()->id(),
            ]);

            $this->repository->logAudit(
                action: 'staff_assigned_facility',
                targetType: 'staff',
                targetId: $updated->id,
                oldValues: $old,
                newValues: $updated->toArray()
            );

            return $updated;
        });
    }

    public function assignDepartment(int $staffId, int $departmentId): Staff
    {
        return $this->updateStaff($staffId, ['department_id' => $departmentId]);
    }

    public function assignManager(int $staffId, int $managerStaffId): Staff
    {
        if ($staffId === $managerStaffId) {
            throw ValidationException::withMessages([
                'manager_staff_id' => 'Staff cannot be assigned as their own manager.',
            ]);
        }

        $this->repository->findOrFail($managerStaffId);

        return $this->updateStaff($staffId, ['manager_staff_id' => $managerStaffId]);
    }

    public function assignUserAccount(int $staffId, int $userId): Staff
    {
        return $this->updateStaff($staffId, ['user_id' => $userId]);
    }

    public function getSummary(int $staffId): Staff
    {
        return $this->repository->findOrFail($staffId);
    }

    public function options(?string $q = null): Collection
    {
        return $this->repository->options($q);
    }

    public function listLicenses(int $staffId): Collection
    {
        $this->repository->findOrFail($staffId);

        return $this->repository->listLicenses($staffId);
    }

    public function createLicense(int $staffId, array $data)
    {
        return DB::transaction(function () use ($staffId, $data) {
            $this->repository->findOrFail($staffId);

            $license = $this->repository->createLicense($staffId, $data);
            $this->repository->logAudit('staff_license_created', 'staff_license', $license->id, null, $license->toArray());

            return $license;
        });
    }

    public function updateLicense(int $staffId, int $licenseId, array $data)
    {
        return DB::transaction(function () use ($staffId, $licenseId, $data) {
            $this->repository->findOrFail($staffId);

            $old = $this->repository->listLicenses($staffId)->firstWhere('id', $licenseId)?->toArray();
            $license = $this->repository->updateLicense($staffId, $licenseId, $data);

            $this->repository->logAudit('staff_license_updated', 'staff_license', $license->id, $old, $license->toArray());

            return $license;
        });
    }

    public function deleteLicense(int $staffId, int $licenseId): void
    {
        DB::transaction(function () use ($staffId, $licenseId): void {
            $this->repository->findOrFail($staffId);

            $old = $this->repository->listLicenses($staffId)->firstWhere('id', $licenseId)?->toArray();
            $this->repository->deleteLicense($staffId, $licenseId);

            $this->repository->logAudit('staff_license_deleted', 'staff_license', $licenseId, $old, null);
        });
    }

    public function listEmergencyContacts(int $staffId): Collection
    {
        $this->repository->findOrFail($staffId);

        return $this->repository->listEmergencyContacts($staffId);
    }

    public function createEmergencyContact(int $staffId, array $data)
    {
        return DB::transaction(function () use ($staffId, $data) {
            $this->repository->findOrFail($staffId);

            $contact = $this->repository->createEmergencyContact($staffId, $data);
            $this->repository->logAudit('staff_emergency_contact_created', 'staff_emergency_contact', $contact->id, null, $contact->toArray());

            return $contact;
        });
    }

    public function updateEmergencyContact(int $staffId, int $contactId, array $data)
    {
        return DB::transaction(function () use ($staffId, $contactId, $data) {
            $this->repository->findOrFail($staffId);

            $old = $this->repository->listEmergencyContacts($staffId)->firstWhere('id', $contactId)?->toArray();
            $contact = $this->repository->updateEmergencyContact($staffId, $contactId, $data);

            $this->repository->logAudit('staff_emergency_contact_updated', 'staff_emergency_contact', $contact->id, $old, $contact->toArray());

            return $contact;
        });
    }

    public function deleteEmergencyContact(int $staffId, int $contactId): void
    {
        DB::transaction(function () use ($staffId, $contactId): void {
            $this->repository->findOrFail($staffId);

            $old = $this->repository->listEmergencyContacts($staffId)->firstWhere('id', $contactId)?->toArray();
            $this->repository->deleteEmergencyContact($staffId, $contactId);

            $this->repository->logAudit('staff_emergency_contact_deleted', 'staff_emergency_contact', $contactId, $old, null);
        });
    }

    public function listDocuments(int $staffId): Collection
    {
        $this->repository->findOrFail($staffId);

        return $this->repository->listDocuments($staffId);
    }

    public function uploadDocument(int $staffId, array $data)
    {
        return DB::transaction(function () use ($staffId, $data) {
            $this->repository->findOrFail($staffId);

            $document = $this->repository->createDocument($staffId, [
                ...$data,
                'uploaded_by' => auth()->id(),
            ]);

            $this->repository->logAudit('staff_document_uploaded', 'staff_document', $document->id, null, $document->toArray());

            return $document;
        });
    }

    public function deleteDocument(int $staffId, int $documentId): void
    {
        DB::transaction(function () use ($staffId, $documentId): void {
            $this->repository->findOrFail($staffId);

            $old = $this->repository->listDocuments($staffId)->firstWhere('id', $documentId)?->toArray();
            $this->repository->deleteDocument($staffId, $documentId);

            $this->repository->logAudit('staff_document_deleted', 'staff_document', $documentId, $old, null);
        });
    }

    public function employmentHistory(int $staffId): Collection
    {
        $this->repository->findOrFail($staffId);

        return $this->repository->listEmploymentHistory($staffId);
    }

    private function prepareStaffPayload(array $data): array
    {
        return [
            ...$data,
            'updated_by' => auth()->id(),
            'created_by' => $data['created_by'] ?? auth()->id(),
        ];
    }

    private function assertTransitionAllowed(string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $allowed = [
            Staff::STATUS_PROBATION => [Staff::STATUS_ACTIVE],
            Staff::STATUS_ACTIVE => [Staff::STATUS_INACTIVE, Staff::STATUS_SUSPENDED, Staff::STATUS_TERMINATED, Staff::STATUS_RESIGNED],
            Staff::STATUS_INACTIVE => [Staff::STATUS_ACTIVE, Staff::STATUS_PROBATION],
            Staff::STATUS_SUSPENDED => [Staff::STATUS_ACTIVE, Staff::STATUS_TERMINATED],
            Staff::STATUS_TERMINATED => [],
            Staff::STATUS_RESIGNED => [],
        ];

        $allowedTargets = $allowed[$oldStatus] ?? [];
        if (! in_array($newStatus, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'status' => "Invalid status transition from '{$oldStatus}' to '{$newStatus}'.",
            ]);
        }
    }

    private function guardFacilityBranchConsistency(array $payload): void
    {
        if (empty($payload['facility_id']) || empty($payload['branch_id'])) {
            return;
        }

        $facility = Facility::query()
            ->where('tenant_id', $this->tenantId)
            ->find($payload['facility_id']);

        if (! $facility) {
            throw ValidationException::withMessages([
                'facility_id' => 'Facility not found in current tenant.',
            ]);
        }

        if ((int) $facility->branch_id !== (int) $payload['branch_id']) {
            throw ValidationException::withMessages([
                'facility_id' => 'Selected facility does not belong to the selected branch.',
            ]);
        }
    }
}
