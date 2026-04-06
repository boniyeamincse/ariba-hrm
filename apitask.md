# MedCore HMS SaaS API Task Tracker

Project: Ariba HMS (Hospital Management System)

Status legend:
- Dev Status: Todo | In Progress | Done
- Test: Not Run | Pass | Fail
- Upload: Pending | Uploaded

## Module-wise API Tasks

| Module | Task ID | Task | Dev Status | Test | Upload |
|---|---|---|---|---|---|
| Super Admin & Tenant Management | T014 | Central tenant schema migrations | In Progress | Not Run | Pending |
| Super Admin & Tenant Management | T018 | Tenant DB provision runner | In Progress | Not Run | Pending |
| Super Admin & Tenant Management | T038 | AuthController (login/logout/refresh/me) | Done | Pass | Pending |
| Super Admin & Tenant Management | T044 | Seed all roles | Done | Pass | Pending |
| Super Admin & Tenant Management | T045 | Seed permissions matrix | Done | Pass | Pending |
| Authentication & RBAC | T039 | OTP 2FA enable/verify flow | Done | Pass | Pending |
| Authentication & RBAC | T040 | Brute-force lockout | Done | Pass | Pending |
| Authentication & RBAC | T041 | Password policy and history | Done | Pass | Pending |
| Authentication & RBAC | T042 | Session management APIs | Done | Pass | Pending |
| Authentication & RBAC | T043 | Audit log middleware | Done | Pass | Pending |
| Authentication & RBAC | T046 | StaffController CRUD | Todo | Not Run | Pending |
| Authentication & RBAC | T047 | Staff suspension/reactivation | Todo | Not Run | Pending |
| Patient Registration & Demographics | T054 | Patients migration + indexes | Done | Pass | Pending |
| Patient Registration & Demographics | T055 | UHID generation service | Done | Pass | Pending |
| Patient Registration & Demographics | T056 | PatientController create/read/update/search | Done | Pass | Pending |
| Patient Registration & Demographics | T058 | Duplicate detection logic | Done | Pass | Pending |
| Patient Registration & Demographics | T059 | Medical history API | Done | Pass | Pending |
| Patient Registration & Demographics | T060 | Visit timeline API | Done | Pass | Pending |
| Patient Registration & Demographics | T061 | Patient photo upload API | Done | Pass | Pending |
| OPD (Out-Patient) | T067 | Appointments + doctor slots migrations | Done | Pass | Pending |
| OPD (Out-Patient) | T068 | AppointmentController | Done | Pass | Pending |
| OPD (Out-Patient) | T069 | OPD queue API | Done | Pass | Pending |
| OPD (Out-Patient) | T070 | Queue update event broadcast | Done | Pass | Pending |
| OPD (Out-Patient) | T071 | Vitals API | Done | Pass | Pending |
| OPD (Out-Patient) | T072 | SOAP consultation API | Done | Pass | Pending |
| OPD (Out-Patient) | T073 | E-prescription API | Done | Pass | Pending |
| OPD (Out-Patient) | T074 | Investigation order API | Done | Pass | Pending |
| OPD (Out-Patient) | T075 | Prescription PDF job | Done | Pass | Pending |
| OPD (Out-Patient) | T076 | Sick leave PDF job | Done | Pass | Pending |
| OPD (Out-Patient) | T077 | Referral API + letter PDF | Done | Pass | Pending |
| IPD (In-Patient) | T085 | Wards/rooms/beds migrations | Todo | Not Run | Pending |
| IPD (In-Patient) | T086 | BedController | Todo | Not Run | Pending |
| IPD (In-Patient) | T088 | AdmissionController | Todo | Not Run | Pending |
| IPD (In-Patient) | T094 | DischargeController | Todo | Not Run | Pending |
| Emergency & Triage | T103 | Emergency registration API | Todo | Not Run | Pending |
| Emergency & Triage | T104 | MTS triage priority logic | Todo | Not Run | Pending |
| Emergency & Triage | T105 | Resuscitation log API | Todo | Not Run | Pending |
| Pharmacy Management | T111 | Drug + stock migrations | Todo | Not Run | Pending |
| Pharmacy Management | T112 | Drug master API | Todo | Not Run | Pending |
| Pharmacy Management | T113 | Batch stock + FEFO API | Todo | Not Run | Pending |
| Pharmacy Management | T114 | Dispense API | Todo | Not Run | Pending |
| Laboratory & Diagnostics | T125 | Lab core migrations | Todo | Not Run | Pending |
| Laboratory & Diagnostics | T127 | Lab order API | Todo | Not Run | Pending |
| Laboratory & Diagnostics | T129 | Lab result API | Todo | Not Run | Pending |
| Laboratory & Diagnostics | T131 | Lab validation API | Todo | Not Run | Pending |
| Billing & Invoice Management | T157 | Charge master API | Todo | Not Run | Pending |
| Billing & Invoice Management | T158 | Invoice generation API | Todo | Not Run | Pending |
| Billing & Invoice Management | T159 | Payment collection API | Todo | Not Run | Pending |
| Billing & Invoice Management | T160 | Discount approval API | Todo | Not Run | Pending |
| Inventory & Procurement | T170 | Inventory items + stock APIs | Todo | Not Run | Pending |
| Inventory & Procurement | T171 | Supplier + PO APIs | Todo | Not Run | Pending |
| HR & Payroll | T180 | Employee master APIs | Todo | Not Run | Pending |
| HR & Payroll | T181 | Attendance + leave APIs | Todo | Not Run | Pending |
| Reports & Analytics | T195 | Reports export API set | Todo | Not Run | Pending |
| Reports & Analytics | T196 | Dashboard analytics APIs | Todo | Not Run | Pending |
| Patient Self-Service Portal | T205 | Portal profile + records APIs | Todo | Not Run | Pending |
| Patient Self-Service Portal | T206 | Appointment + bill self-service APIs | Todo | Not Run | Pending |

## Current Focus (Suggested)

| Priority | Task ID | Module | Next Action |
|---|---|---|---|
| P0 | T046 | Authentication & RBAC | Implement StaffController CRUD with role assignment |
| P0 | T085 | IPD | Create wards/rooms/beds migrations and BedController |
| P0 | T103 | Emergency & Triage | Build rapid emergency registration API |
| P0 | T111 | Pharmacy | Start drug/stock schema and stock API |
| P0 | T125 | Laboratory | Create lab core migrations and order API |
