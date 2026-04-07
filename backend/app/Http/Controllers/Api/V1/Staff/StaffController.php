<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AssignStaffBranchRequest;
use App\Http\Requests\Staff\AssignStaffDepartmentRequest;
use App\Http\Requests\Staff\AssignStaffFacilityRequest;
use App\Http\Requests\Staff\AssignStaffManagerRequest;
use App\Http\Requests\Staff\AssignStaffUserAccountRequest;
use App\Http\Requests\Staff\ConfirmStaffRequest;
use App\Http\Requests\Staff\CreateStaffEmergencyContactRequest;
use App\Http\Requests\Staff\CreateStaffLicenseRequest;
use App\Http\Requests\Staff\CreateStaffRequest;
use App\Http\Requests\Staff\ProbationStaffRequest;
use App\Http\Requests\Staff\ReactivateStaffRequest;
use App\Http\Requests\Staff\ResignStaffRequest;
use App\Http\Requests\Staff\SuspendStaffRequest;
use App\Http\Requests\Staff\TerminateStaffRequest;
use App\Http\Requests\Staff\UpdateStaffEmergencyContactRequest;
use App\Http\Requests\Staff\UpdateStaffLicenseRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Requests\Staff\UpdateStaffStatusRequest;
use App\Http\Requests\Staff\UploadStaffDocumentRequest;
use App\Http\Resources\Staff\StaffDocumentResource;
use App\Http\Resources\Staff\StaffEmergencyContactResource;
use App\Http\Resources\Staff\StaffEmploymentHistoryResource;
use App\Http\Resources\Staff\StaffLicenseResource;
use App\Http\Resources\Staff\StaffResource;
use App\Http\Resources\Staff\StaffSummaryResource;
use App\Models\Staff;
use App\Services\Staff\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    private function service(): StaffService
    {
        return new StaffService((int) request()->attributes->get('tenant_id'));
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service()->listStaff($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff list retrieved successfully',
            'data' => StaffResource::collection($paginator->items()),
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    public function store(CreateStaffRequest $request): JsonResponse
    {
        $staff = $this->service()->createStaff($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff created successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff retrieved successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function update(UpdateStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStaff($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff updated successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service()->deleteStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff deleted successfully',
            'data' => null,
            'meta' => new \stdClass(),
        ]);
    }

    public function updateStatus(UpdateStaffStatusRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus(
            $id,
            $request->validated('status'),
            $request->validated('remarks'),
            $request->validated('effective_date')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Staff status updated successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function summary(int $id): JsonResponse
    {
        $staff = $this->service()->getSummary($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff summary retrieved successfully',
            'data' => new StaffSummaryResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $options = $this->service()->options($request->query('q'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff options retrieved successfully',
            'data' => $options,
            'meta' => new \stdClass(),
        ]);
    }

    public function assignBranch(AssignStaffBranchRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->assignBranch($id, (int) $request->validated('branch_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Branch assigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function assignFacility(AssignStaffFacilityRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->assignFacility($id, (int) $request->validated('facility_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Facility assigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function assignDepartment(AssignStaffDepartmentRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->assignDepartment($id, (int) $request->validated('department_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Department assigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function assignManager(AssignStaffManagerRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->assignManager($id, (int) $request->validated('manager_staff_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Manager assigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function assignUserAccount(AssignStaffUserAccountRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->assignUserAccount($id, (int) $request->validated('user_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'User account assigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function branch(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff branch retrieved successfully',
            'data' => [
                'staff_id' => $staff->id,
                'branch' => $staff->branch,
            ],
            'meta' => new \stdClass(),
        ]);
    }

    public function facility(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff facility retrieved successfully',
            'data' => [
                'staff_id' => $staff->id,
                'facility' => $staff->facility,
            ],
            'meta' => new \stdClass(),
        ]);
    }

    public function department(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);
        $department = null;

        if ($staff->department_id && DB::getSchemaBuilder()->hasTable('departments')) {
            $department = DB::table('departments')
                ->where('id', $staff->department_id)
                ->where('tenant_id', request()->attributes->get('tenant_id'))
                ->first();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Staff department retrieved successfully',
            'data' => [
                'staff_id' => $staff->id,
                'department_id' => $staff->department_id,
                'department' => $department,
            ],
            'meta' => new \stdClass(),
        ]);
    }

    public function manager(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff manager retrieved successfully',
            'data' => [
                'staff_id' => $staff->id,
                'manager' => $staff->manager,
            ],
            'meta' => new \stdClass(),
        ]);
    }

    public function userAccount(int $id): JsonResponse
    {
        $staff = $this->service()->getStaff($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff user account retrieved successfully',
            'data' => [
                'staff_id' => $staff->id,
                'user_account' => $staff->user,
            ],
            'meta' => new \stdClass(),
        ]);
    }

    public function confirm(ConfirmStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_ACTIVE, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff confirmed successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function probation(ProbationStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_PROBATION, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff moved to probation successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function suspend(SuspendStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_SUSPENDED, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff suspended successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function terminate(TerminateStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_TERMINATED, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff terminated successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function resign(ResignStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_RESIGNED, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff resigned successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function reactivate(ReactivateStaffRequest $request, int $id): JsonResponse
    {
        $staff = $this->service()->updateStatus($id, Staff::STATUS_ACTIVE, $request->validated('remarks'), $request->validated('effective_date'));

        return response()->json([
            'status' => 'success',
            'message' => 'Staff reactivated successfully',
            'data' => new StaffResource($staff),
            'meta' => new \stdClass(),
        ]);
    }

    public function employmentHistory(int $id): JsonResponse
    {
        $history = $this->service()->employmentHistory($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff employment history retrieved successfully',
            'data' => StaffEmploymentHistoryResource::collection($history),
            'meta' => new \stdClass(),
        ]);
    }

    public function licenses(int $id): JsonResponse
    {
        $licenses = $this->service()->listLicenses($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff licenses retrieved successfully',
            'data' => StaffLicenseResource::collection($licenses),
            'meta' => new \stdClass(),
        ]);
    }

    public function storeLicense(CreateStaffLicenseRequest $request, int $id): JsonResponse
    {
        $license = $this->service()->createLicense($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff license created successfully',
            'data' => new StaffLicenseResource($license),
            'meta' => new \stdClass(),
        ], 201);
    }

    public function updateLicense(UpdateStaffLicenseRequest $request, int $id, int $licenseId): JsonResponse
    {
        $license = $this->service()->updateLicense($id, $licenseId, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff license updated successfully',
            'data' => new StaffLicenseResource($license),
            'meta' => new \stdClass(),
        ]);
    }

    public function destroyLicense(int $id, int $licenseId): JsonResponse
    {
        $this->service()->deleteLicense($id, $licenseId);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff license deleted successfully',
            'data' => null,
            'meta' => new \stdClass(),
        ]);
    }

    public function emergencyContacts(int $id): JsonResponse
    {
        $contacts = $this->service()->listEmergencyContacts($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff emergency contacts retrieved successfully',
            'data' => StaffEmergencyContactResource::collection($contacts),
            'meta' => new \stdClass(),
        ]);
    }

    public function storeEmergencyContact(CreateStaffEmergencyContactRequest $request, int $id): JsonResponse
    {
        $contact = $this->service()->createEmergencyContact($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff emergency contact created successfully',
            'data' => new StaffEmergencyContactResource($contact),
            'meta' => new \stdClass(),
        ], 201);
    }

    public function updateEmergencyContact(UpdateStaffEmergencyContactRequest $request, int $id, int $contactId): JsonResponse
    {
        $contact = $this->service()->updateEmergencyContact($id, $contactId, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Staff emergency contact updated successfully',
            'data' => new StaffEmergencyContactResource($contact),
            'meta' => new \stdClass(),
        ]);
    }

    public function destroyEmergencyContact(int $id, int $contactId): JsonResponse
    {
        $this->service()->deleteEmergencyContact($id, $contactId);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff emergency contact deleted successfully',
            'data' => null,
            'meta' => new \stdClass(),
        ]);
    }

    public function documents(int $id): JsonResponse
    {
        $documents = $this->service()->listDocuments($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff documents retrieved successfully',
            'data' => StaffDocumentResource::collection($documents),
            'meta' => new \stdClass(),
        ]);
    }

    public function storeDocument(UploadStaffDocumentRequest $request, int $id): JsonResponse
    {
        $payload = $request->validated();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['file_path'] = $file->store('staff-documents');
            $payload['file_name'] = $payload['file_name'] ?? $file->getClientOriginalName();
            $payload['file_size'] = $payload['file_size'] ?? $file->getSize();
            $payload['mime_type'] = $payload['mime_type'] ?? $file->getClientMimeType();
        }

        unset($payload['file']);

        $document = $this->service()->uploadDocument($id, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff document uploaded successfully',
            'data' => new StaffDocumentResource($document),
            'meta' => new \stdClass(),
        ], 201);
    }

    public function destroyDocument(int $id, int $documentId): JsonResponse
    {
        $this->service()->deleteDocument($id, $documentId);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff document deleted successfully',
            'data' => null,
            'meta' => new \stdClass(),
        ]);
    }
}
