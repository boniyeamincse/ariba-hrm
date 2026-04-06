# Permission-to-Menu Matrix

## Purpose

This document maps dashboard menus and routes to backend permission keys and role access.

Source alignment:
- Route protection: `backend/routes/api.php`
- Permission seed map: `backend/database/seeders/RolePermissionSeeder.php`

## Menu to Route to Permission

| Menu Group | Submenu | Route | Backend Endpoint | Required Permission |
|---|---|---|---|---|
| Dashboard | Overview | /dashboard | GET /api/dashboard/overview | dashboard.view |
| Dashboard | Widgets | /dashboard | GET /api/dashboard/widgets | dashboard.view |
| Dashboard | Menu Feed | /dashboard | GET /api/dashboard/menu | dashboard.view |
| Users | User List | /dashboard/users | GET /api/users | users.view |
| Users | Create User | /dashboard/users | POST /api/users | users.manage |
| Users | Update User | /dashboard/users/:id | PATCH /api/users/{user} | users.manage |
| Patients | Patient List | /dashboard/patients | GET /api/clinical/patients | patient.view |
| Patients | Register Patient | /dashboard/patients/new | POST /api/clinical/patients | patient.create |
| Patients | Edit Patient | /dashboard/patients/:id | PATCH /api/clinical/patients/{patient} | patient.update |
| Patients | Medical History | /dashboard/patients/:id/history | GET/PATCH /api/clinical/patients/{patient}/history | patient.view / patient.update |
| Patients | Visit Timeline | /dashboard/patients/:id/visits | GET/POST /api/clinical/patients/{patient}/visits | patient.view / patient.update |
| Appointments | Slot Listing | /dashboard/appointments | GET /api/clinical/appointments/slots | appointment.view |
| Appointments | Booking/Manage | /dashboard/appointments | POST /api/clinical/appointments/book | appointment.manage |
| Billing | Summary | /dashboard/billing | GET /api/reports/summary | reports.view |
| Billing | Charges | /dashboard/billing/charges | GET /api/clinical/billing/charges | billing.view |
| Billing | Invoice Ops | /dashboard/billing/invoices | POST /api/clinical/billing/invoices | billing.manage |
| Inventory | Item List | /dashboard/inventory | GET /api/clinical/inventory/items | inventory.view |
| Inventory | Create Item | /dashboard/inventory/items/new | POST /api/clinical/inventory/items | inventory.manage |
| Inventory | Procurement | /dashboard/inventory/procurement | POST /api/clinical/inventory/procurement-orders | inventory.manage |
| Reports | Summary | /dashboard/reports | GET /api/reports/summary | reports.view |
| Reports | Export | /dashboard/reports/export | (module-specific export endpoints) | reports.export |
| Settings | Session Controls | /dashboard/settings/sessions | GET /api/auth/sessions | auth.view-sessions |

## Capability Permissions (Non-Menu Specific)

| Capability | Permission |
|---|---|
| Consultation write | consultation.create |
| Prescription write | prescription.create |
| Investigation order | investigation.create |
| Audit access | audit.view |
| API integration access | integration.api |
| AI assisted actions | ai.assist |
| Tenant lifecycle control | super-admin.manage-tenants |

## Role to Module Access Matrix

Legend: `R` Read, `W` Write/Manage, `-` No access.

| Role | Dashboard | Users | Patients | Appointments | Billing | Inventory | Pharmacy | Lab | HR | Reports | Audit |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Super Admin | W | W | W | W | W | W | W | W | W | W | W |
| Tenant Admin | W | W | W | W | W | W | W | W | W | W | R |
| Hospital Admin | W | W | W | W | W | - | - | - | - | R | - |
| Hospital Manager | W | - | R | R | R | - | - | - | - | R | - |
| Operations Manager | W | - | R | R | - | R | - | - | - | R | - |
| Doctor | W | - | R | R | - | - | - | - | - | R | - |
| Nurse | W | - | R | R | - | - | - | - | - | - | - |
| Receptionist | W | - | W | W | R | - | - | - | - | - | - |
| Pharmacist | W | - | R | - | R | - | W | - | - | - | - |
| Lab Technician | W | - | R | - | - | - | - | W | - | - | - |
| Accountant | W | - | - | - | W | - | - | - | - | W | - |
| Ward Manager | W | - | R | R | - | - | - | - | - | R | - |
| Ambulance Driver | W | - | R | - | - | - | - | - | - | - | - |
| IT Admin | W | W | - | - | - | - | - | - | - | R | R |
| Inventory Manager | W | - | - | - | - | W | - | - | - | R | - |
| HR Manager | W | W | - | - | - | - | - | - | W | R | - |
| Patient | W | - | R (self) | R (self) | R (self) | - | - | - | - | - | - |
| Insurance Agent | W | - | R | - | R | - | - | - | - | R | - |
| Auditor | W | - | - | - | - | - | - | - | - | W | W |
| Data Analyst | W | - | - | - | - | - | - | - | - | W | - |
| API Client | - | - | R | R | - | - | - | - | - | - | - |
| AI Assistant | W | - | R | R | - | - | - | - | - | - | - |

## Action-to-Permission Rules

| UI Action | Required Permission |
|---|---|
| Open dashboard | dashboard.view |
| View users table | users.view |
| Add or edit user | users.manage |
| Register patient | patient.create |
| Edit patient profile/history | patient.update |
| Book or reschedule appointment | appointment.manage |
| Create invoice/payment | billing.manage |
| View billing details | billing.view |
| Create inventory/procurement item | inventory.manage |
| View reports dashboard | reports.view |
| Export reports | reports.export |

## Sensitive Action Audit Tags

| Action | Audit Tag |
|---|---|
| User create/update role | auth.user.manage |
| Billing discount approval | billing.discount.approve |
| Invoice payment post | billing.payment.post |
| Prescription issue | prescription.issue |
| Discharge clearance | ipd.discharge.clearance |
| Payroll run | hr.payroll.run |
| Report export | reports.export |
| Tenant provisioning/suspension | tenant.lifecycle.manage |

## Validation Checklist

- Menu and route are permission-gated.
- Backend middleware uses same permission key as matrix.
- Role seed includes required permission.
- Unauthorized role receives HTTP 403.
- Sensitive actions include audit tag in logs.
