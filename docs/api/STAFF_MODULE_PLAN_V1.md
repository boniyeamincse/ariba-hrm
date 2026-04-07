# Staff Management Module Plan (Laravel 12 + MySQL 8)

## A. Module Overview

The Staff Management module is the tenant-scoped HR and operational identity base for all workforce members in the hospital network. It centralizes employment records and assignment metadata while preserving strict tenant isolation and auditability.

Core objectives:
- Maintain a complete staff master profile per tenant.
- Link staff to platform user account when applicable.
- Support assignment to branch, facility, department, and manager.
- Track employment lifecycle events and status transitions.
- Maintain professional licenses, emergency contacts, and documents.
- Provide search, filters, options, and summary outputs for admin dashboards.

Architecture compatibility:
- Multi-tenant resolution via subdomain + tenant middleware.
- Sanctum authentication (JWT optional extension).
- Spatie permission + existing permission middleware.
- Tenant-safe repositories/services and audit trail on all critical actions.

---

## B. Main Features

### Staff Master Profile
- Staff CRUD with soft delete.
- Employee code unique within tenant.
- Personal identity + contact + address + photo metadata.
- Employment basics: designation, staff type, category, employment type, salary metadata.

### Assignment Management
- Assign branch/facility/department with tenant consistency checks.
- Assign reporting manager (self-referential staff hierarchy).
- Link/unlink user account to staff.

### Employment Lifecycle
- Lifecycle endpoints for probation, confirmation, suspend, terminate, resign, reactivate.
- Valid transition enforcement.
- Employment history timeline per staff.

### License / Contact / Document Management
- Professional licenses CRUD with validity and expiry checks.
- Emergency contacts CRUD.
- Staff document references and metadata with upload/delete events.

### Analytics and Options
- Staff list with robust filtering and sorting.
- Staff options endpoint for dropdowns.
- Staff summary endpoint for dashboard cards.

### Governance
- Full audit logs for all sensitive operations.
- Strict tenant boundary checks on all linked entities.
- Policy-safe handling of suspended/terminated staff for downstream modules.

---

## C. Development Tasks

1. Create migrations
- `staff`
- `staff_licenses`
- `staff_emergency_contacts`
- `staff_documents`
- `staff_employment_histories`
- `staff_audit_logs`

2. Create Eloquent models and relationships
- `Staff`, `StaffLicense`, `StaffEmergencyContact`, `StaffDocument`, `StaffEmploymentHistory`, `StaffAuditLog`

3. Create Form Requests
- Staff CRUD, status update, assignment requests, lifecycle requests
- License/contact/document requests

4. Create API Resources
- `StaffResource`, `StaffSummaryResource`, `StaffLicenseResource`, `StaffEmergencyContactResource`, `StaffDocumentResource`, `StaffEmploymentHistoryResource`

5. Create controllers
- `StaffController`
- Optional child controllers:
  - `StaffLicenseController`
  - `StaffEmergencyContactController`
  - `StaffDocumentController`
  - `StaffLifecycleController`

6. Create repositories
- `StaffRepository`
- `StaffChildResourceRepository` (or separate repositories per child aggregate)

7. Create services
- `StaffService` with transitions, assignments, validation orchestration, and audit write logic

8. Implement assignment logic
- branch/facility/department/manager/user-account tenant checks

9. Implement lifecycle transitions
- status transition matrix + history records + audit events

10. Implement child resource handling
- licenses, emergency contacts, documents

11. Add routes with middleware and permission matrix

12. Add seed/reference values (optional)
- designation/staff_type/category reference seed tables or constants

13. Add summary and options endpoints for UI integration

14. Add feature tests
- tenant isolation, permissions, lifecycle transitions, assignment validity, audit events

---

## D. Database Design

## D.1 staff

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index, fk -> tenants.id cascade delete
- `user_id` bigint unsigned nullable, fk -> users.id nullOnDelete
- `employee_code` varchar(50)
- `first_name` varchar(100)
- `last_name` varchar(100)
- `middle_name` varchar(100) nullable
- `gender` varchar(20) nullable
- `date_of_birth` date nullable
- `blood_group` varchar(10) nullable
- `marital_status` varchar(30) nullable
- `phone` varchar(30) nullable
- `alternate_phone` varchar(30) nullable
- `email` varchar(255) nullable
- `address_line_1` varchar(255) nullable
- `address_line_2` varchar(255) nullable
- `city` varchar(100) nullable
- `state` varchar(100) nullable
- `country` varchar(100) nullable
- `zip_code` varchar(20) nullable
- `photo_path` text nullable
- `branch_id` bigint unsigned nullable, fk -> branches.id nullOnDelete
- `facility_id` bigint unsigned nullable, fk -> facilities.id nullOnDelete
- `department_id` bigint unsigned nullable (fk optional based on department schema availability)
- `designation` varchar(150) nullable
- `staff_type` varchar(100) nullable
- `category` varchar(100) nullable
- `manager_staff_id` bigint unsigned nullable, fk -> staff.id nullOnDelete
- `employment_type` varchar(50) nullable
- `join_date` date
- `confirmation_date` date nullable
- `probation_end_date` date nullable
- `exit_date` date nullable
- `status` enum('active','inactive','probation','suspended','terminated','resigned') default 'probation'
- `payroll_group` varchar(100) nullable
- `basic_salary` decimal(12,2) nullable
- `notes` text nullable
- `created_by` bigint unsigned nullable, fk -> users.id nullOnDelete
- `updated_by` bigint unsigned nullable, fk -> users.id nullOnDelete
- timestamps
- soft deletes

Indexes/constraints:
- unique: (`tenant_id`,`employee_code`)
- unique optional for linked account: (`tenant_id`,`user_id`) where not null (enforced in service)
- index: (`tenant_id`,`status`)
- index: (`tenant_id`,`branch_id`)
- index: (`tenant_id`,`facility_id`)
- index: (`tenant_id`,`department_id`)
- index: (`tenant_id`,`manager_staff_id`)
- index: (`tenant_id`,`join_date`)

## D.2 staff_licenses

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `staff_id` bigint unsigned index, fk -> staff.id cascade delete
- `license_type` varchar(100)
- `license_number` varchar(100)
- `issuing_authority` varchar(150) nullable
- `issued_at` date nullable
- `expires_at` date nullable
- `is_verified` boolean default false
- `remarks` text nullable
- timestamps

Indexes/constraints:
- unique optional: (`tenant_id`,`license_type`,`license_number`)
- index: (`tenant_id`,`staff_id`)
- index: (`tenant_id`,`expires_at`)

## D.3 staff_emergency_contacts

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `staff_id` bigint unsigned index, fk -> staff.id cascade delete
- `name` varchar(150)
- `relationship` varchar(100)
- `phone` varchar(30)
- `alternate_phone` varchar(30) nullable
- `address` text nullable
- timestamps

Indexes:
- (`tenant_id`,`staff_id`)

## D.4 staff_documents

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `staff_id` bigint unsigned index, fk -> staff.id cascade delete
- `document_type` varchar(100)
- `file_path` text
- `file_name` varchar(255) nullable
- `file_size` bigint unsigned nullable
- `mime_type` varchar(150) nullable
- `uploaded_by` bigint unsigned nullable, fk -> users.id nullOnDelete
- `created_at` timestamp default current_timestamp

Indexes:
- (`tenant_id`,`staff_id`,`document_type`)

## D.5 staff_employment_histories

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `staff_id` bigint unsigned index, fk -> staff.id cascade delete
- `action` varchar(100)
- `old_status` varchar(30) nullable
- `new_status` varchar(30) nullable
- `remarks` text nullable
- `effective_date` date nullable
- `created_by` bigint unsigned nullable, fk -> users.id nullOnDelete
- `created_at` timestamp default current_timestamp

Indexes:
- (`tenant_id`,`staff_id`,`created_at`)
- (`tenant_id`,`action`,`created_at`)

## D.6 staff_audit_logs

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `user_id` bigint unsigned nullable index
- `action` varchar(100)
- `target_type` varchar(50)
- `target_id` bigint unsigned nullable
- `old_values` json nullable
- `new_values` json nullable
- `ip_address` varchar(45) nullable
- `user_agent` text nullable
- `created_at` timestamp default current_timestamp

Indexes:
- (`tenant_id`,`target_type`,`target_id`)
- (`tenant_id`,`action`,`created_at`)

---

## E. API Endpoints

Base route:
- `/api/v1/staff`

Middleware:
- `auth:sanctum`
- `tenant`
- `audit`
- route-level `permission:*`

### E.1 Staff Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/staff` | `staff.view` |
| POST | `/v1/staff` | `staff.create` |
| GET | `/v1/staff/{id}` | `staff.view` |
| PUT | `/v1/staff/{id}` | `staff.update` |
| DELETE | `/v1/staff/{id}` | `staff.delete` |
| PATCH | `/v1/staff/{id}/status` | `staff.status.update` |
| GET | `/v1/staff/{id}/summary` | `staff.view` |
| GET | `/v1/staff/options` | `staff.view` |

### E.2 Assignment Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| POST | `/v1/staff/{id}/assign-branch` | `staff.assign.branch` |
| POST | `/v1/staff/{id}/assign-facility` | `staff.assign.facility` |
| POST | `/v1/staff/{id}/assign-department` | `staff.assign.department` |
| POST | `/v1/staff/{id}/assign-manager` | `staff.assign.manager` |
| POST | `/v1/staff/{id}/assign-user-account` | `staff.assign.user` |
| GET | `/v1/staff/{id}/branch` | `staff.view` |
| GET | `/v1/staff/{id}/facility` | `staff.view` |
| GET | `/v1/staff/{id}/department` | `staff.view` |
| GET | `/v1/staff/{id}/manager` | `staff.view` |
| GET | `/v1/staff/{id}/user-account` | `staff.view` |

### E.3 Employment / Lifecycle Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| POST | `/v1/staff/{id}/confirm` | `staff.status.update` |
| POST | `/v1/staff/{id}/probation` | `staff.status.update` |
| POST | `/v1/staff/{id}/suspend` | `staff.status.update` |
| POST | `/v1/staff/{id}/terminate` | `staff.status.update` |
| POST | `/v1/staff/{id}/resign` | `staff.status.update` |
| POST | `/v1/staff/{id}/reactivate` | `staff.status.update` |
| GET | `/v1/staff/{id}/employment-history` | `staff.employment-history.view` |

### E.4 License / Contact / Document Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/staff/{id}/licenses` | `staff.view` |
| POST | `/v1/staff/{id}/licenses` | `staff.license.manage` |
| PUT | `/v1/staff/{id}/licenses/{licenseId}` | `staff.license.manage` |
| DELETE | `/v1/staff/{id}/licenses/{licenseId}` | `staff.license.manage` |
| GET | `/v1/staff/{id}/emergency-contacts` | `staff.view` |
| POST | `/v1/staff/{id}/emergency-contacts` | `staff.emergency-contact.manage` |
| PUT | `/v1/staff/{id}/emergency-contacts/{contactId}` | `staff.emergency-contact.manage` |
| DELETE | `/v1/staff/{id}/emergency-contacts/{contactId}` | `staff.emergency-contact.manage` |
| GET | `/v1/staff/{id}/documents` | `staff.view` |
| POST | `/v1/staff/{id}/documents` | `staff.document.manage` |
| DELETE | `/v1/staff/{id}/documents/{documentId}` | `staff.document.manage` |

---

## F. Validation Rules

### F.1 Staff Create/Update
- `employee_code`: required|string|max:50|unique in tenant scope
- `first_name`: required|string|max:100
- `last_name`: required|string|max:100
- `join_date`: required|date
- `status`: required|in:active,inactive,probation,suspended,terminated,resigned
- `email`: nullable|email
- `phone`: nullable|string|max:30
- `branch_id`: nullable|exists:branches,id in same tenant
- `facility_id`: nullable|exists:facilities,id in same tenant
- `department_id`: nullable|exists:departments,id in same tenant (if table present)
- `manager_staff_id`: nullable|exists:staff,id in same tenant and not self
- `user_id`: nullable|exists:users,id in same tenant

### F.2 Assignment Requests
- `branch_id`: required and tenant-owned branch
- `facility_id`: required and tenant-owned facility
- `department_id`: required and tenant-owned department
- `manager_staff_id`: required, tenant-owned staff, not same as target staff
- `user_id`: required, tenant-owned user, not linked to another active staff unless reassignment policy

### F.3 License Validation
- `license_type`: required|string|max:100
- `license_number`: required|string|max:100
- `issued_at`: nullable|date
- `expires_at`: nullable|date|after:issued_at
- `is_verified`: boolean

### F.4 Emergency Contact Validation
- `name`: required|string|max:150
- `relationship`: required|string|max:100
- `phone`: required|string|max:30
- `alternate_phone`: nullable|string|max:30

### F.5 Document Validation
- `document_type`: required|string|max:100
- `file`: required|file|max:<policy-defined>
- Alternative mode:
  - `file_path` required if async upload or pre-signed flow

### F.6 Lifecycle Validation
- enforce state machine:
  - probation -> active
  - active -> suspended|resigned|terminated
  - suspended -> active
  - terminated -> final unless explicit restore policy

---

## G. Permissions

Required permissions:
- `staff.view`
- `staff.create`
- `staff.update`
- `staff.delete`
- `staff.status.update`
- `staff.assign.branch`
- `staff.assign.facility`
- `staff.assign.department`
- `staff.assign.manager`
- `staff.assign.user`
- `staff.license.manage`
- `staff.document.manage`
- `staff.emergency-contact.manage`
- `staff.employment-history.view`

Suggested role mapping:
- Tenant Admin: all staff permissions
- HR Manager: full staff + lifecycle + docs/licenses + assignments
- Operations Manager: view + selected assignment and status permissions
- Department Manager: view subset (team-level by policy)
- Super Admin: platform metadata view only (cross-tenant aggregated)

---

## H. Sample API Requests & Responses

### H.1 Create Staff

Request:
```http
POST /api/v1/staff
Content-Type: application/json
```

```json
{
  "employee_code": "EMP-0001",
  "first_name": "Amina",
  "last_name": "Rahman",
  "designation": "Senior Nurse",
  "staff_type": "clinical",
  "employment_type": "full-time",
  "join_date": "2026-04-01",
  "status": "active",
  "branch_id": 1,
  "facility_id": 4,
  "department_id": 7,
  "phone": "01710000000",
  "email": "amina.rahman@alpha.test"
}
```

Response:
```json
{
  "status": "success",
  "message": "Staff created successfully",
  "data": {
    "id": 1,
    "employee_code": "EMP-0001",
    "first_name": "Amina",
    "last_name": "Rahman",
    "designation": "Senior Nurse",
    "status": "active"
  },
  "meta": {}
}
```

### H.2 Assign Manager

Request:
```http
POST /api/v1/staff/1/assign-manager
```

```json
{
  "manager_staff_id": 15
}
```

Response:
```json
{
  "status": "success",
  "message": "Manager assigned successfully",
  "data": {
    "staff_id": 1,
    "manager_staff_id": 15
  },
  "meta": {}
}
```

### H.3 Confirm Staff (Probation -> Active)

Request:
```http
POST /api/v1/staff/1/confirm
```

```json
{
  "remarks": "Completed probation successfully",
  "effective_date": "2026-07-01"
}
```

Response:
```json
{
  "status": "success",
  "message": "Staff confirmed successfully",
  "data": {
    "id": 1,
    "old_status": "probation",
    "new_status": "active"
  },
  "meta": {}
}
```

### H.4 Add License

Request:
```http
POST /api/v1/staff/1/licenses
```

```json
{
  "license_type": "nursing-council",
  "license_number": "NMC-2026-8891",
  "issuing_authority": "National Nursing Council",
  "issued_at": "2024-01-10",
  "expires_at": "2027-01-09",
  "is_verified": true
}
```

Response:
```json
{
  "status": "success",
  "message": "License added successfully",
  "data": {
    "id": 4,
    "staff_id": 1,
    "license_type": "nursing-council",
    "license_number": "NMC-2026-8891"
  },
  "meta": {}
}
```

### H.5 Staff List with Filters

Request:
```http
GET /api/v1/staff?status=active&branch_id=1&search=amina&sort=-join_date&per_page=20
```

Response:
```json
{
  "status": "success",
  "message": "Staff list retrieved successfully",
  "data": [
    {
      "id": 1,
      "employee_code": "EMP-0001",
      "full_name": "Amina Rahman",
      "designation": "Senior Nurse",
      "status": "active"
    }
  ],
  "meta": {
    "pagination": {
      "total": 1,
      "per_page": 20,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

---

## I. Suggested Implementation Order

### Phase 1: Schema
1. Create 6 migrations.
2. Add all required indexes, foreign keys, soft deletes.
3. Run migration and verify constraints.

### Phase 2: Models
4. Create staff aggregate models.
5. Add tenant scopes and relationships.
6. Add status constants and optional helper methods.

### Phase 3: Validation + Resources
7. Add Form Requests for all endpoint groups.
8. Add API Resources for consistent response envelope.

### Phase 4: Repository + Service
9. Build tenant-safe repository queries.
10. Add lifecycle transition engine in service layer.
11. Add assignment handlers with cross-tenant protection.
12. Add history and audit logging.

### Phase 5: Controllers + Routes
13. Build `StaffController` and child handlers.
14. Register routes under `/v1/staff` with permissions.
15. Add options and summary endpoints.

### Phase 6: Test + Seed + Hardening
16. Feature tests for auth, permission, tenant isolation, lifecycle transitions.
17. Seed reference data for staff types/categories if adopted.
18. Add integration guards for downstream modules.

---

## J. Extra Production Notes

### J.1 Tenant Isolation
- Every read/write query must be tenant-scoped.
- Every linked ID must be tenant-owned.
- Reject cross-tenant assignment attempts with 422/403.

### J.2 Lifecycle Safety
- Enforce transition matrix in a central service method.
- Write `staff_employment_histories` row for each transition.
- Mirror to `staff_audit_logs` with old/new status and metadata.

### J.3 Soft Delete + Downstream Integrity
- Soft delete staff only.
- Block deletion for active staff by default.
- For suspended/terminated staff, expose policy hooks to downstream modules.

### J.4 Assignment Integrity
- Validate facility belongs to assigned branch if both present.
- Validate manager is not self and optional no-cycle policy (recommended).
- Validate linked user account belongs to same tenant.

### J.5 Search and Performance
- Add searchable composite indexes where needed.
- Use query builder with filter map and whitelisted sorting keys.
- Eager load related manager/branch/facility/department in list endpoints.

### J.6 Documents
- Prefer object storage + signed URL flow.
- Keep file metadata in DB and storage path abstracted.
- Enforce MIME and size policies server-side.

### J.7 Payroll and Shift Linkage
- Keep `payroll_group` in staff for direct payroll integration.
- Keep assignment IDs ready for shift/roster module linkage.
- Add integration service hooks rather than direct coupling.

### J.8 Super Admin Platform Metadata
- Optional endpoint:
  - `GET /api/v1/platform/staff/overview`
- Return only aggregated counts (no sensitive staff details).
- Protect by super-admin permission.

---

## Proposed Production-Ready Backend Structure

app/
- Http/Controllers/Api/V1/Staff/
  - StaffController.php
  - StaffLicenseController.php
  - StaffEmergencyContactController.php
  - StaffDocumentController.php
  - StaffLifecycleController.php
- Http/Requests/Staff/
  - CreateStaffRequest.php
  - UpdateStaffRequest.php
  - UpdateStaffStatusRequest.php
  - AssignStaffBranchRequest.php
  - AssignStaffFacilityRequest.php
  - AssignStaffDepartmentRequest.php
  - AssignStaffManagerRequest.php
  - AssignStaffUserAccountRequest.php
  - ConfirmStaffRequest.php
  - ProbationStaffRequest.php
  - SuspendStaffRequest.php
  - TerminateStaffRequest.php
  - ResignStaffRequest.php
  - ReactivateStaffRequest.php
  - CreateStaffLicenseRequest.php
  - UpdateStaffLicenseRequest.php
  - CreateStaffEmergencyContactRequest.php
  - UpdateStaffEmergencyContactRequest.php
  - UploadStaffDocumentRequest.php
- Http/Resources/Staff/
  - StaffResource.php
  - StaffSummaryResource.php
  - StaffLicenseResource.php
  - StaffEmergencyContactResource.php
  - StaffDocumentResource.php
  - StaffEmploymentHistoryResource.php
- Services/Staff/
  - StaffService.php
- Repositories/Staff/
  - StaffRepository.php
- Models/
  - Staff.php
  - StaffLicense.php
  - StaffEmergencyContact.php
  - StaffDocument.php
  - StaffEmploymentHistory.php
  - StaffAuditLog.php

database/
- migrations/
  - create_staff_table.php
  - create_staff_licenses_table.php
  - create_staff_emergency_contacts_table.php
  - create_staff_documents_table.php
  - create_staff_employment_histories_table.php
  - create_staff_audit_logs_table.php
- seeders/
  - StaffReferenceSeeder.php (optional)
