# Branch / Facility Management Module Plan (Laravel 12 + MySQL 8)

## A. Module Overview

This module enables tenant hospitals to manage multi-branch and multi-facility operations with strict tenant isolation, role-based access, auditability, and downstream workflow control.

Core goals:
- Tenant can manage one or many branches under the same tenant account.
- Each branch can manage one or many facilities (clinic, lab unit, emergency unit, pharmacy outlet, warehouse, etc.).
- Facility metadata supports building/floor/wing/service-point granularity.
- Facility assignments support tenant-scoped departments and users.
- Operational-hours support is built in for future scheduling and service availability checks.
- A full audit trail is available for branch/facility lifecycle events.

Architecture fit:
- Subdomain-based tenant resolution via existing tenant middleware.
- Auth via Sanctum (JWT optional extension).
- Permissions via Spatie Laravel Permission plus existing permission middleware.
- Soft-delete and safe-delete policies for operational integrity.

---

## B. Main Features

### Branch Management
- Create, list, view, update, soft-delete branches.
- Set and maintain a single main branch per tenant.
- Update branch status independently (active/inactive/suspended).
- Fetch branch summary and branch-specific facilities.
- Branch options endpoint for dropdowns.

### Facility Management
- Create, list, view, update, soft-delete facilities under branch.
- Update facility status independently (active/inactive/maintenance).
- Manage facility location metadata: building, floor, wing, room prefix.
- Fetch linked departments and users.
- Facility options endpoint for dropdowns.

### Facility Types
- System and tenant-defined facility types.
- CRUD facility types with soft governance:
  - System types (`is_system = true`) cannot be deleted by tenant.
  - Tenant can create custom types.

### Assignments and Operational Hours
- Assign departments to facility with tenant-bound validation.
- Assign users to facility with tenant-bound validation.
- Manage operational hours per day of week.
- Time validation: open < close unless `is_closed = true`.

### Governance and Security
- Enforce tenant ownership in every query and assignment.
- Prevent cross-tenant IDs in all create/update/assignment operations.
- Maintain branch/facility audit trail for compliance.
- Enforce status-driven operational restrictions for downstream modules.

---

## C. Development Tasks

1. Create migrations
- `branches`
- `facilities`
- `facility_types`
- `facility_operational_hours`
- `facility_department`
- `facility_user`
- `branch_audit_logs`

2. Create models + relationships
- `Branch`, `Facility`, `FacilityType`, `FacilityOperationalHour`, `BranchAuditLog`
- Add pivot relations to `Department` and `User`

3. Create Form Requests
- Branch CRUD + status
- Facility CRUD + status
- Facility type CRUD
- Assign departments/users
- Operational hours update

4. Create API Resources
- `BranchResource`, `BranchSummaryResource`, `FacilityResource`, `FacilityTypeResource`, `OperationalHourResource`

5. Create repositories
- `BranchRepository`, `FacilityRepository`, `FacilityTypeRepository`

6. Create services
- `BranchService`, `FacilityService`
- business rules: main-branch uniqueness, cross-tenant guards, status transitions, audit events

7. Create controllers
- `BranchController`
- `FacilityController`
- `FacilityTypeController`

8. Register routes and permission middleware

9. Add seeders
- default system facility types

10. Add feature tests
- tenant isolation
- permission checks
- main-branch uniqueness
- assignment validation
- status behavior
- operational hours validation

11. Add summary endpoints and optimized list queries

12. Add integration hooks for downstream filtering
- appointment, opd, ipd, billing modules consume `branch_id` / `facility_id`

---

## D. Database Design

## D.1 branches

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index, fk -> tenants.id cascade delete
- `code` varchar(50)
- `name` varchar(255)
- `slug` varchar(255)
- `type` varchar(100) nullable
- `is_main` boolean default false
- `status` enum('active','inactive','suspended') default 'active'
- `registration_no` varchar(100) nullable
- `license_no` varchar(100) nullable
- `tax_no` varchar(100) nullable
- `email` varchar(255) nullable
- `phone` varchar(30) nullable
- `emergency_phone` varchar(30) nullable
- `website` varchar(255) nullable
- `address_line_1` varchar(255)
- `address_line_2` varchar(255) nullable
- `city` varchar(100)
- `state` varchar(100) nullable
- `country` varchar(100)
- `zip_code` varchar(20) nullable
- `latitude` decimal(10,7) nullable
- `longitude` decimal(10,7) nullable
- `timezone` varchar(100)
- `currency` varchar(10)
- `opening_date` date nullable
- `notes` text nullable
- `created_by` bigint unsigned nullable
- `updated_by` bigint unsigned nullable
- timestamps
- soft deletes

Indexes/constraints:
- unique: (`tenant_id`,`code`)
- unique: (`tenant_id`,`slug`)
- index: (`tenant_id`,`status`)
- index: (`tenant_id`,`is_main`)
- optional generated-safe rule via service: only one `is_main = true` per tenant

## D.2 facilities

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index, fk -> tenants.id cascade delete
- `branch_id` bigint unsigned index, fk -> branches.id restrict/cascade-by-policy
- `facility_type_id` bigint unsigned nullable, fk -> facility_types.id nullOnDelete
- `code` varchar(50)
- `name` varchar(255)
- `slug` varchar(255)
- `building_name` varchar(150) nullable
- `floor_no` varchar(30) nullable
- `wing` varchar(100) nullable
- `room_prefix` varchar(20) nullable
- `service_point_type` varchar(100) nullable
- `status` enum('active','inactive','maintenance') default 'active'
- `email` varchar(255) nullable
- `phone` varchar(30) nullable
- `extension` varchar(20) nullable
- `address_line_1` varchar(255) nullable
- `address_line_2` varchar(255) nullable
- `city` varchar(100) nullable
- `state` varchar(100) nullable
- `country` varchar(100) nullable
- `zip_code` varchar(20) nullable
- `latitude` decimal(10,7) nullable
- `longitude` decimal(10,7) nullable
- `notes` text nullable
- `created_by` bigint unsigned nullable
- `updated_by` bigint unsigned nullable
- timestamps
- soft deletes

Indexes/constraints:
- unique: (`tenant_id`,`slug`)
- unique option A (recommended): (`branch_id`,`code`)
- unique option B (alternate): (`tenant_id`,`code`)
- index: (`tenant_id`,`branch_id`,`status`)
- index: (`tenant_id`,`facility_type_id`)

## D.3 facility_types

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned nullable (null means platform/system type)
- `key` varchar(100)
- `name` varchar(150)
- `description` text nullable
- `is_system` boolean default false
- `is_active` boolean default true
- timestamps

Indexes/constraints:
- unique system key: (`tenant_id`,`key`) where tenant null/system allowed by service rule
- index: (`is_system`,`is_active`)

Deletion rules:
- cannot delete system type from tenant context
- cannot delete type if referenced by active facilities

## D.4 facility_operational_hours

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `facility_id` bigint unsigned index, fk -> facilities.id cascade delete
- `day_of_week` tinyint unsigned (0..6)
- `opens_at` time nullable
- `closes_at` time nullable
- `is_closed` boolean default false
- timestamps

Indexes/constraints:
- unique: (`facility_id`,`day_of_week`)
- check/application rule:
  - if `is_closed = true` => `opens_at` and `closes_at` nullable
  - else both required and `opens_at < closes_at`

## D.5 facility_department

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `facility_id` bigint unsigned index
- `department_id` bigint unsigned index
- `created_at` timestamp

Indexes/constraints:
- unique: (`facility_id`,`department_id`)
- service-level tenant check for department ownership

## D.6 facility_user

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `facility_id` bigint unsigned index
- `user_id` bigint unsigned index
- `created_at` timestamp

Indexes/constraints:
- unique: (`facility_id`,`user_id`)
- service-level tenant check for user ownership

## D.7 branch_audit_logs

Columns:
- `id` bigint unsigned primary
- `tenant_id` bigint unsigned index
- `user_id` bigint unsigned nullable index
- `action` varchar(100)
- `target_type` varchar(50)  // branch, facility, facility_type, assignment, operational_hours
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

Base route groups:
- `/api/v1/branches`
- `/api/v1/facilities`

All protected by middleware:
- `auth:sanctum`
- `tenant`
- `audit`
- permission middleware per endpoint

### E.1 Branches
- `GET /branches` permission: `branch.view`
- `POST /branches` permission: `branch.create`
- `GET /branches/{id}` permission: `branch.view`
- `PUT /branches/{id}` permission: `branch.update`
- `DELETE /branches/{id}` permission: `branch.delete`
- `PATCH /branches/{id}/status` permission: `branch.status.update`
- `GET /branches/{id}/facilities` permission: `branch.view`
- `GET /branches/{id}/summary` permission: `branch.view`
- `GET /branches/options` permission: `branch.view`

### E.2 Facilities
- `GET /facilities` permission: `facility.view`
- `POST /facilities` permission: `facility.create`
- `GET /facilities/{id}` permission: `facility.view`
- `PUT /facilities/{id}` permission: `facility.update`
- `DELETE /facilities/{id}` permission: `facility.delete`
- `PATCH /facilities/{id}/status` permission: `facility.status.update`
- `GET /facilities/{id}/departments` permission: `facility.view`
- `GET /facilities/{id}/users` permission: `facility.view`
- `GET /facilities/options` permission: `facility.view`

### E.3 Facility Types
- `GET /facilities/types` permission: `facility.view`
- `POST /facilities/types` permission: `facility.create`
- `GET /facilities/types/{id}` permission: `facility.view`
- `PUT /facilities/types/{id}` permission: `facility.update`
- `DELETE /facilities/types/{id}` permission: `facility.delete`

### E.4 Assignments / Utilities
- `POST /facilities/{id}/assign-departments` permission: `facility.assignment.manage`
- `POST /facilities/{id}/assign-users` permission: `facility.assignment.manage`
- `GET /facilities/{id}/operational-hours` permission: `facility.view`
- `PUT /facilities/{id}/operational-hours` permission: `facility.assignment.manage`

---

## F. Validation Rules

## F.1 Branch Create/Update
- `name`: required|string|max:255
- `code`: required|string|max:50
- `status`: required|in:active,inactive,suspended
- `country`: required|string|max:100
- `timezone`: required|timezone
- `currency`: required|string|max:10
- `email`: nullable|email
- `phone`: nullable|string|max:30
- `slug`: nullable|string|max:255|unique in tenant scope
- `is_main`: boolean

Extra business rules:
- if `is_main=true`, unset previous main branch inside transaction.
- cannot suspend the only active/main operational branch without explicit policy.

## F.2 Facility Create/Update
- `branch_id`: required|exists:branches,id (must belong to same tenant)
- `name`: required|string|max:255
- `code`: required|string|max:50 (unique by chosen scope)
- `status`: required|in:active,inactive,maintenance
- `facility_type_id`: nullable|exists:facility_types,id (system or same tenant)
- `floor_no`: nullable|string|max:30
- `email`: nullable|email
- `slug`: nullable|string|max:255|unique in tenant scope

## F.3 Facility Type
- `key`: required|string|max:100|alpha_dash
- `name`: required|string|max:150
- `description`: nullable|string
- `is_active`: boolean

Rules:
- prevent tenant from modifying protected system types (policy flag).

## F.4 Operational Hours
Payload format:
- `hours`: array min 1 max 7
- `hours.*.day_of_week`: required|integer|between:0,6
- `hours.*.is_closed`: required|boolean
- `hours.*.opens_at`: required_if:is_closed,false|date_format:H:i
- `hours.*.closes_at`: required_if:is_closed,false|date_format:H:i

Rule:
- if not closed then `opens_at < closes_at`.

## F.5 Assign Departments / Users
- `department_ids`: array|min:1
- `department_ids.*`: exists:departments,id and department.tenant_id == request tenant
- `user_ids`: array|min:1
- `user_ids.*`: exists:users,id and user.tenant_id == request tenant

---

## G. Permissions

Required permissions:
- `branch.view`
- `branch.create`
- `branch.update`
- `branch.delete`
- `branch.status.update`
- `facility.view`
- `facility.create`
- `facility.update`
- `facility.delete`
- `facility.status.update`
- `facility.assignment.manage`

Suggested role mapping:
- Tenant Admin: all above
- Operations Manager: branch/facility view + status + assignment manage (optional)
- Department Admin: facility.view only
- Super Admin: platform metadata read endpoint only (cross-tenant summary view)

---

## H. Sample API Requests & Responses

## H.1 Create Branch

Request:
```http
POST /api/v1/branches
Content-Type: application/json
```

```json
{
  "name": "Dhaka Main Hospital",
  "code": "DMH",
  "slug": "dhaka-main-hospital",
  "status": "active",
  "is_main": true,
  "country": "Bangladesh",
  "city": "Dhaka",
  "address_line_1": "Road 11, Dhanmondi",
  "timezone": "Asia/Dhaka",
  "currency": "BDT"
}
```

Response:
```json
{
  "status": "success",
  "message": "Branch created successfully",
  "data": {
    "id": 1,
    "name": "Dhaka Main Hospital",
    "code": "DMH",
    "status": "active",
    "is_main": true
  },
  "meta": {}
}
```

## H.2 Update Branch Status

Request:
```http
PATCH /api/v1/branches/1/status
```

```json
{
  "status": "suspended"
}
```

Response:
```json
{
  "status": "success",
  "message": "Branch status updated successfully",
  "data": {
    "id": 1,
    "status": "suspended"
  },
  "meta": {}
}
```

## H.3 Create Facility

Request:
```http
POST /api/v1/facilities
```

```json
{
  "branch_id": 1,
  "facility_type_id": 2,
  "name": "Emergency Unit - Block A",
  "code": "ER-A",
  "slug": "er-a",
  "building_name": "Block A",
  "floor_no": "Ground",
  "wing": "East",
  "status": "active"
}
```

Response:
```json
{
  "status": "success",
  "message": "Facility created successfully",
  "data": {
    "id": 10,
    "branch_id": 1,
    "name": "Emergency Unit - Block A",
    "code": "ER-A",
    "status": "active"
  },
  "meta": {}
}
```

## H.4 Assign Users to Facility

Request:
```http
POST /api/v1/facilities/10/assign-users
```

```json
{
  "user_ids": [5, 8, 11]
}
```

Response:
```json
{
  "status": "success",
  "message": "Facility users assigned successfully",
  "data": {
    "facility_id": 10,
    "assigned_count": 3
  },
  "meta": {}
}
```

## H.5 Update Operational Hours

Request:
```http
PUT /api/v1/facilities/10/operational-hours
```

```json
{
  "hours": [
    {"day_of_week": 1, "is_closed": false, "opens_at": "08:00", "closes_at": "20:00"},
    {"day_of_week": 2, "is_closed": false, "opens_at": "08:00", "closes_at": "20:00"},
    {"day_of_week": 5, "is_closed": true}
  ]
}
```

Response:
```json
{
  "status": "success",
  "message": "Operational hours updated successfully",
  "data": {
    "facility_id": 10,
    "days_configured": 3
  },
  "meta": {}
}
```

---

## I. Suggested Implementation Order

Phase 1 (Data Layer)
1. Create migrations for all 7 tables.
2. Add indexes + foreign keys + unique constraints.
3. Run migrations and verify schema.

Phase 2 (Domain Layer)
4. Create models and relationships.
5. Add casts, scopes (`scopeTenant`, `scopeActive`, etc.).
6. Add soft-delete behavior for branch/facility.

Phase 3 (Application Layer)
7. Implement repositories with strict tenant filters.
8. Implement services:
- `setMainBranch()` transaction-safe uniqueness
- status transitions
- assignment + operational hours upsert
- audit event writer

Phase 4 (HTTP Layer)
9. Implement Form Requests.
10. Implement Resources.
11. Implement controllers with standardized response format.
12. Register routes and permission middleware.

Phase 5 (Seed + Policy)
13. Seed default system facility types.
14. Add safety policies for delete operations.
15. Add option endpoints and summary endpoints.

Phase 6 (Testing)
16. Feature tests for all CRUD/status/assignment/tenant-isolation.
17. Add negative tests for cross-tenant IDs and duplicate-main branch.
18. Add authorization tests for each permission.

---

## J. Extra Production Notes

### J.1 Tenant Isolation
- Every repository query must include tenant filter.
- Every route-bound ID must be validated against tenant ownership.
- Assignment endpoints must hard-fail cross-tenant IDs with 422/403.

### J.2 Main Branch Consistency
- Implement in transaction:
  - set existing main branch `is_main=false`
  - set target branch `is_main=true`
  - write audit log event `main_branch_changed`

### J.3 Safe Delete Policy
- Soft delete branch/facility only.
- Prevent delete when actively referenced in operational modules.
- Add `forceDelete` only for super-admin maintenance tasks.

### J.4 Downstream Operational Blocking
- If branch is `suspended`, expose helper guard for downstream modules:
  - patient booking, OPD/IPD admission, billing issue, inventory transactions
  - return 423/422 with clear message

### J.5 Performance
- Add composite indexes used by filters.
- Use selective columns in list endpoints.
- Eager load branch/type in facility list to avoid N+1.
- Cache options endpoints (short TTL, tenant-keyed).

### J.6 Audit and Compliance
- Log delta-only old/new values for updates.
- Include actor, IP, user-agent, target_type, target_id.
- Keep immutable audit entries (no updates).

### J.7 API Stability
- Use versioned routes (`/api/v1`).
- Keep response envelope consistent across endpoints.
- Add machine-readable error codes for frontend handling.

### J.8 Super Admin Metadata View
- Optional platform endpoint:
  - `GET /api/v1/platform/facilities/overview`
- Return tenant-level counts only (no PHI).
- protect with super-admin permission.

---

## Proposed Backend Structure (Production-Ready)

app/
- Http/Controllers/Api/V1/Branch/BranchController.php
- Http/Controllers/Api/V1/Facility/FacilityController.php
- Http/Controllers/Api/V1/Facility/FacilityTypeController.php
- Http/Requests/Branch/
  - CreateBranchRequest.php
  - UpdateBranchRequest.php
  - UpdateBranchStatusRequest.php
- Http/Requests/Facility/
  - CreateFacilityRequest.php
  - UpdateFacilityRequest.php
  - UpdateFacilityStatusRequest.php
  - AssignFacilityDepartmentsRequest.php
  - AssignFacilityUsersRequest.php
  - UpdateFacilityOperationalHoursRequest.php
- Http/Requests/FacilityType/
  - CreateFacilityTypeRequest.php
  - UpdateFacilityTypeRequest.php
- Http/Resources/Branch/
  - BranchResource.php
  - BranchCollection.php
  - BranchSummaryResource.php
- Http/Resources/Facility/
  - FacilityResource.php
  - FacilityCollection.php
  - FacilityTypeResource.php
  - OperationalHourResource.php
- Services/Branch/
  - BranchService.php
- Services/Facility/
  - FacilityService.php
- Repositories/Branch/
  - BranchRepository.php
- Repositories/Facility/
  - FacilityRepository.php
  - FacilityTypeRepository.php
- Models/
  - Branch.php
  - Facility.php
  - FacilityType.php
  - FacilityOperationalHour.php
  - BranchAuditLog.php

database/
- migrations/
  - create_branches_table.php
  - create_facilities_table.php
  - create_facility_types_table.php
  - create_facility_operational_hours_table.php
  - create_facility_department_table.php
  - create_facility_user_table.php
  - create_branch_audit_logs_table.php
- seeders/
  - FacilityTypeSeeder.php

routes/
- api.php
  - v1 branch/facility route groups with permission middleware
