# MedCore HMS API Endpoint Catalog

Source of truth: backend route registry in `backend/routes/api.php`.

Base URL (local):
- `http://localhost:8000/api`

Global middleware notes:
- Public endpoints: no auth required.
- Secured endpoints: `auth:sanctum`.
- Tenant-scoped endpoints: `tenant` middleware (subdomain-based).
- Audited endpoints: `audit` middleware.
- Permission checks: `permission:*` middleware.

## 1) Health

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| GET | `/health` | Public | API health check |

## 2) Auth Module

### 2.1 Public Auth

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| POST | `/auth/bootstrap-super-admin` | Public | Bootstrap super-admin |
| POST | `/auth/login` | Public | Login |
| POST | `/auth/forgot-password` | Public | Forgot password |
| POST | `/auth/reset-password` | Public | Reset password |
| POST | `/auth/2fa/verify` | Public | Verify 2FA challenge |

### 2.2 Protected Auth

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| GET | `/auth/me` | Sanctum | Current user profile |
| POST | `/auth/logout` | Sanctum | Logout |
| POST | `/auth/refresh` | Sanctum | Refresh token/session |
| GET | `/auth/sessions` | Sanctum | List active sessions |
| DELETE | `/auth/sessions` | Sanctum | Revoke all sessions |
| DELETE | `/auth/sessions/{tokenId}` | Sanctum | Revoke specific session |
| POST | `/auth/2fa/setup` | Sanctum | Setup 2FA |
| POST | `/auth/2fa/enable` | Sanctum | Enable 2FA |
| DELETE | `/auth/2fa` | Sanctum | Disable 2FA |

### 2.3 Auth Module (V1) - `/v1/auth`

#### Public Endpoints

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| POST | `/v1/auth/bootstrap-super-admin` | Public | Bootstrap super-admin (legacy controller bridge) |
| POST | `/v1/auth/login` | Public | Tenant-aware login |
| POST | `/v1/auth/forgot-password` | Public | Password reset email/link request |
| POST | `/v1/auth/reset-password` | Public | Reset password with token |
| POST | `/v1/auth/2fa/verify` | Public | Verify OTP/2FA challenge |
| POST | `/v1/auth/register-tenant-admin` | Public | Optional SaaS tenant admin registration |
| POST | `/v1/auth/refresh-token` | Public | Refresh access token |
| POST | `/v1/auth/resend-otp` | Public | Resend OTP code |
| POST | `/v1/auth/verify-email` | Public | Verify email hash/token |
| POST | `/v1/auth/resend-verification-email` | Public | Resend verification email |

#### Protected Endpoints

Middleware: `auth:sanctum`, `audit` (RBAC on selected endpoints).

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/auth/me` | authenticated user |
| POST | `/v1/auth/logout` | authenticated user |
| POST | `/v1/auth/logout-all-devices` | authenticated user |
| POST | `/v1/auth/change-password` | authenticated user |
| GET | `/v1/auth/sessions` | `auth.session.manage` |
| DELETE | `/v1/auth/sessions/{id}` | `auth.session.manage` |
| POST | `/v1/auth/2fa/enable` | `auth.2fa.manage` |
| POST | `/v1/auth/2fa/disable` | `auth.2fa.manage` |
| GET | `/v1/auth/2fa/status` | `auth.2fa.manage` |

## 3) Dashboard + Menu + Tasks

Middleware: `auth:sanctum`, `tenant`, permission scoped.

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/menus` | `dashboard.view` |
| GET | `/dashboard/stats` | `dashboard.view` |
| GET | `/dashboard/overview` | `dashboard.view` |
| GET | `/dashboard/super-admin/panel` | `dashboard.view` |
| GET | `/dashboard/super-admin/menu` | `dashboard.view` |
| GET | `/dashboard/widgets` | `dashboard.view` |
| GET | `/dashboard/menu` | `dashboard.view` |
| GET | `/reports/summary` | `reports.view` |
| GET | `/tasks` | auth+tenant |
| POST | `/tasks` | auth+tenant |
| GET | `/tasks/{task}` | auth+tenant |
| PUT/PATCH | `/tasks/{task}` | auth+tenant |
| DELETE | `/tasks/{task}` | auth+tenant |

## 4) User Management

Middleware: `auth:sanctum`, `tenant`.

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/users` | `users.view` |
| POST | `/users` | `users.manage` |
| PATCH | `/users/{user}` | `users.manage` |

## 5) Tenant Context

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| GET | `/tenant-context` | tenant middleware | Resolve active tenant context |

## 6) Super Admin Tenant Management

Middleware: `auth:sanctum`, `audit`, `permission:super-admin.manage-tenants`.

### 6.1 Admin Prefix

| Method | Endpoint |
|---|---|
| GET | `/admin/tenants` |
| POST | `/admin/tenants` |
| GET | `/admin/tenants/{tenant}` |
| PATCH | `/admin/tenants/{tenant}` |
| PATCH | `/admin/tenants/{tenant}/metadata` |
| PATCH | `/admin/tenants/{tenant}/status` |
| DELETE | `/admin/tenants/{tenant}` |

### 6.2 Alias Prefix (Frontend Compatibility)

| Method | Endpoint |
|---|---|
| GET | `/tenants` |
| POST | `/tenants` |
| GET | `/tenants/{tenant}` |
| PATCH | `/tenants/{tenant}` |
| PATCH | `/tenants/{tenant}/metadata` |
| PATCH | `/tenants/{tenant}/status` |
| DELETE | `/tenants/{tenant}` |

## 7) Clinical Module (`/clinical`)

Middleware: `auth:sanctum`, `tenant`, `audit`.

### 7.1 Patient Management

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/patients` | `patient.view` |
| POST | `/clinical/patients` | `patient.create` |
| GET | `/clinical/patients/{patient}` | `patient.view` |
| PATCH | `/clinical/patients/{patient}` | `patient.update` |
| POST | `/clinical/patients/{patient}/photo` | `patient.update` |
| GET | `/clinical/patients/{patient}/history` | `patient.view` |
| PATCH | `/clinical/patients/{patient}/history` | `patient.update` |
| GET | `/clinical/patients/{patient}/visits` | `patient.view` |
| POST | `/clinical/patients/{patient}/visits` | `patient.update` |

### 7.2 OPD (Legacy-style)

| Method | Endpoint |
|---|---|
| GET | `/clinical/opd/queue` |
| POST | `/clinical/opd/queue` |
| POST | `/clinical/opd/consultations` |
| POST | `/clinical/opd/consultations/{consultation}/prescription` |
| POST | `/clinical/opd/consultations/{consultation}/investigations` |

### 7.3 OPD (Detailed)

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/opd/appointments/slots` | `appointment.view` |
| POST | `/clinical/opd/appointments/book` | `appointment.manage` |
| POST | `/clinical/opd/appointments/{appointment}/cancel` | `appointment.manage` |
| POST | `/clinical/opd/appointments/{appointment}/reschedule` | `appointment.manage` |
| POST | `/clinical/opd/queue/tokens` | `appointment.manage` |
| GET | `/clinical/opd/queue/state` | `appointment.view` |
| POST | `/clinical/opd/queue/call-next` | `appointment.manage` |
| POST | `/clinical/opd/queue/{opdQueue}/skip` | `appointment.manage` |
| POST | `/clinical/opd/vitals` | `consultation.create` |
| POST | `/clinical/opd/consultations` | `consultation.create` |
| GET | `/clinical/opd/icd10/search` | `consultation.create` |
| POST | `/clinical/opd/consultations/{consultation}/prescriptions` | `prescription.create` |
| POST | `/clinical/opd/prescriptions/{prescription}/items` | `prescription.create` |
| GET | `/clinical/opd/prescriptions/{prescription}/pdf-url` | `prescription.create` |
| POST | `/clinical/opd/consultations/{consultation}/sick-leave-certificate` | `consultation.create` |
| GET | `/clinical/opd/sick-leave-certificates/{certificate}/pdf-url` | `consultation.create` |
| POST | `/clinical/opd/consultations/{consultation}/investigations` | `investigation.create` |
| POST | `/clinical/opd/referrals` | `consultation.create` |
| GET | `/clinical/opd/referrals/{referral}` | `consultation.create` |
| POST | `/clinical/opd/referrals/{referral}/letter` | `consultation.create` |

### 7.4 IPD

| Method | Endpoint |
|---|---|
| GET | `/clinical/ipd/beds` |
| POST | `/clinical/ipd/admissions` |
| POST | `/clinical/ipd/admissions/{admission}/ward-rounds` |
| POST | `/clinical/ipd/admissions/{admission}/nursing-notes` |
| POST | `/clinical/ipd/admissions/{admission}/medications` |
| POST | `/clinical/ipd/admissions/{admission}/discharge-clearance` | 

### 7.5 Emergency

| Method | Endpoint |
|---|---|
| GET | `/clinical/emergency/triage` |
| POST | `/clinical/emergency/triage` |

### 7.6 Pharmacy

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/pharmacy/drugs` | `pharmacy.view` |
| POST | `/clinical/pharmacy/drugs` | `pharmacy.manage` |
| POST | `/clinical/pharmacy/drugs/{drug}/batches` | `pharmacy.manage` |
| POST | `/clinical/pharmacy/dispense` | `pharmacy.manage` |

### 7.7 Laboratory

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/lab/tests` | `lab.view` |
| POST | `/clinical/lab/tests` | `lab.manage` |
| POST | `/clinical/lab/samples` | `lab.manage` |
| POST | `/clinical/lab/orders` | `lab.manage` |
| POST | `/clinical/lab/orders/{order}/results` | `lab.manage` |
| POST | `/clinical/lab/results/{result}/validate` | `lab.manage` |
| GET | `/clinical/lab/results/{result}/report` | `lab.view` |

### 7.8 Billing

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/billing/charges` | `billing.view` |
| POST | `/clinical/billing/charges` | `billing.manage` |
| POST | `/clinical/billing/invoices` | `billing.manage` |
| POST | `/clinical/billing/invoices/{invoice}/payments` | `billing.manage` |
| POST | `/clinical/billing/invoices/{invoice}/discount-approve` | `billing.manage` |

### 7.9 Appointment (General)

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/appointments/slots` | `appointment.view` |
| POST | `/clinical/appointments/slots` | `appointment.manage` |
| POST | `/clinical/appointments/book` | `appointment.manage` |
| POST | `/clinical/appointments/{appointment}/cancel` | `appointment.manage` |
| POST | `/clinical/appointments/{appointment}/reschedule` | `appointment.manage` |
| POST | `/clinical/appointments/{appointment}/telemedicine` | `appointment.manage` |

### 7.10 Insurance

| Method | Endpoint |
|---|---|
| GET | `/clinical/insurance/providers` |
| POST | `/clinical/insurance/providers` |
| POST | `/clinical/insurance/policies` |
| POST | `/clinical/insurance/claims` |
| POST | `/clinical/insurance/claims/{claim}/approve` |

### 7.11 Inventory

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/inventory/items` | `inventory.view` |
| POST | `/clinical/inventory/items` | `inventory.manage` |
| POST | `/clinical/inventory/procurement-orders` | `inventory.manage` |

### 7.12 HR

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/clinical/hr/staff` | `hr.view` |
| POST | `/clinical/hr/staff` | `hr.manage` |
| POST | `/clinical/hr/payroll/runs` | `hr.manage` |

### 7.13 Blood Bank

| Method | Endpoint |
|---|---|
| GET | `/clinical/blood-bank/stock` |
| POST | `/clinical/blood-bank/donations` |
| POST | `/clinical/blood-bank/transfusions` |

### 7.14 Mortuary

| Method | Endpoint |
|---|---|
| GET | `/clinical/mortuary/records` |
| POST | `/clinical/mortuary/records` |
| POST | `/clinical/mortuary/records/{record}/release` |

## 8) Settings Module (`/v1/settings`)

Middleware: `auth:sanctum`, `tenant`, `audit`, RBAC.

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/settings/general` | `settings.read` |
| PUT | `/v1/settings/general` | `settings.update` |
| GET | `/v1/settings/branding` | `settings.read` |
| PUT | `/v1/settings/branding` | `settings.branding.update` |
| GET | `/v1/settings/localization` | `settings.read` |
| PUT | `/v1/settings/localization` | `settings.update` |
| GET | `/v1/settings/notifications` | `settings.read` |
| PUT | `/v1/settings/notifications` | `settings.notification.update` |
| GET | `/v1/settings/email-config` | `settings.read` |
| PUT | `/v1/settings/email-config` | `settings.update` |
| POST | `/v1/settings/email-config/test` | `settings.update` |
| GET | `/v1/settings/sms-config` | `settings.read` |
| PUT | `/v1/settings/sms-config` | `settings.update` |
| POST | `/v1/settings/sms-config/test` | `settings.update` |
| GET | `/v1/settings/billing` | `settings.read` |
| PUT | `/v1/settings/billing` | `settings.billing.update` |
| GET | `/v1/settings/clinical` | `settings.read` |
| PUT | `/v1/settings/clinical` | `settings.clinical.update` |
| GET | `/v1/settings/appointments` | `settings.read` |
| PUT | `/v1/settings/appointments` | `settings.update` |
| GET | `/v1/settings/ipd` | `settings.read` |
| PUT | `/v1/settings/ipd` | `settings.update` |
| GET | `/v1/settings/pharmacy` | `settings.read` |
| PUT | `/v1/settings/pharmacy` | `settings.update` |
| GET | `/v1/settings/lab` | `settings.read` |
| PUT | `/v1/settings/lab` | `settings.update` |
| GET | `/v1/settings/integrations` | `settings.read` |
| PUT | `/v1/settings/integrations` | `settings.integration.update` |
| GET | `/v1/settings/security` | `settings.read` |
| PUT | `/v1/settings/security` | `settings.security.update` |
| GET | `/v1/settings/templates` | `settings.read` |
| PUT | `/v1/settings/templates` | `settings.update` |
| GET | `/v1/settings/audit-logs` | `settings.audit.read` |

## 9) Branch / Facility Module (`/v1/branches`, `/v1/facilities`)

Middleware (planned): `auth:sanctum`, `tenant`, `audit`, RBAC.

### 9.1 Branch Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/branches` | `branch.view` |
| POST | `/v1/branches` | `branch.create` |
| GET | `/v1/branches/{id}` | `branch.view` |
| PUT | `/v1/branches/{id}` | `branch.update` |
| DELETE | `/v1/branches/{id}` | `branch.delete` |
| PATCH | `/v1/branches/{id}/status` | `branch.status.update` |
| GET | `/v1/branches/{id}/facilities` | `branch.view` |
| GET | `/v1/branches/{id}/summary` | `branch.view` |
| GET | `/v1/branches/options` | `branch.view` |

### 9.2 Facility Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/facilities` | `facility.view` |
| POST | `/v1/facilities` | `facility.create` |
| GET | `/v1/facilities/{id}` | `facility.view` |
| PUT | `/v1/facilities/{id}` | `facility.update` |
| DELETE | `/v1/facilities/{id}` | `facility.delete` |
| PATCH | `/v1/facilities/{id}/status` | `facility.status.update` |
| GET | `/v1/facilities/{id}/departments` | `facility.view` |
| GET | `/v1/facilities/{id}/users` | `facility.view` |
| GET | `/v1/facilities/options` | `facility.view` |

### 9.3 Facility Type Endpoints

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/v1/facilities/types` | `facility.view` |
| POST | `/v1/facilities/types` | `facility.create` |
| GET | `/v1/facilities/types/{id}` | `facility.view` |
| PUT | `/v1/facilities/types/{id}` | `facility.update` |
| DELETE | `/v1/facilities/types/{id}` | `facility.delete` |

### 9.4 Assignments and Utilities

| Method | Endpoint | Permission |
|---|---|---|
| POST | `/v1/facilities/{id}/assign-departments` | `facility.assignment.manage` |
| POST | `/v1/facilities/{id}/assign-users` | `facility.assignment.manage` |
| GET | `/v1/facilities/{id}/operational-hours` | `facility.view` |
| PUT | `/v1/facilities/{id}/operational-hours` | `facility.assignment.manage` |

Status values:
- Branch: `active`, `inactive`, `suspended`
- Facility: `active`, `inactive`, `maintenance`

Business constraints:
- One main branch per tenant.
- Branch code unique per tenant.
- Facility code unique per branch (current schema).
- Slug uniqueness in tenant scope.
- Cross-tenant assignment is blocked at validation/service layer.

## 10) Notes

- Some appointment and OPD endpoints exist in both general and OPD-prefixed paths for compatibility.
- For tenant-scoped APIs, send requests through tenant subdomain host to resolve tenant context.

## 11) RBAC Module (`/v1/rbac`)

Middleware: `auth:sanctum`, `tenant`, `audit`, RBAC-permission-based access control.

### Roles

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/v1/rbac/roles` | `rbac:view_roles` | List all roles with filters (search, active) |
| POST | `/v1/rbac/roles` | `rbac:create_role` | Create new role with permissions |
| GET | `/v1/rbac/roles/{id}` | `rbac:view_roles` | Get role with permissions |
| PATCH | `/v1/rbac/roles/{id}` | `rbac:update_role` | Update role metadata and sync permissions |
| DELETE | `/v1/rbac/roles/{id}` | `rbac:delete_role` | Delete custom role (system roles protected) |

### Permissions

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/v1/rbac/permissions` | `rbac:view_permissions` | List permissions with module filtering |
| POST | `/v1/rbac/permissions` | `rbac:create_permission` | Create new permission |
| GET | `/v1/rbac/permissions/{id}` | `rbac:view_permissions` | Get permission details |
| PATCH | `/v1/rbac/permissions/{id}` | `rbac:update_permission` | Update permission metadata |
| DELETE | `/v1/rbac/permissions/{id}` | `rbac:delete_permission` | Delete custom permissions |

### Role-Permission Mapping

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| PUT | `/v1/rbac/roles/{id}/permissions` | `rbac:sync_permissions` | Bulk sync role permissions |

### Permission Groups

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/v1/rbac/permission-groups` | None | List permission groups (by key: rbac, auth, patient, etc.) |
| POST | `/v1/rbac/permission-groups` | `rbac:manage_groups` | Create permission group |

### User-Role Assignment

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| POST | `/v1/rbac/users/{userId}/roles` | `rbac:assign_role` | Assign one or more roles to user |
| DELETE | `/v1/rbac/users/{userId}/roles/{roleId}` | `rbac:assign_role` | Remove role from user |

### RBAC Matrix (Dashboard)

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/v1/rbac/matrix` | `rbac:view_matrix` | Get full permission matrix for dashboard UI |

### System Roles

Pre-seeded roles:
- `super-admin` (system, all permissions)
- `tenant-admin` (system, management permissions)
- `hospital-admin`, `doctor`, `nurse`, `receptionist`, `pharmacist`, `lab-technician` (custom)
