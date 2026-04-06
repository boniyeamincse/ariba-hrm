# MedCore HMS SaaS API Task Tracker

Project: Ariba HMS (Hospital Management System)

Status legend:
- Dev Status: Todo | In Progress | Done
- Test: Not Run | Pass | Fail
- Upload: Pending | Uploaded

## API Module Format Example

### Module 01: Auth

| Task ID | Task | Dev | Test | Git Upload |
|---|---|---|---|---|
| Task 01 | Login | Done | Pending | Pending |
| Task 02 | Registration | Todo | Pending | Pending |
| Task 03 | Forget Password | Todo | Pending | Pending |

### Module 02: Patient

| Task ID | Task | Dev | Test | Git Upload |
|---|---|---|---|---|
| Task 01 | Create Patient | Todo | Pending | Pending |
| Task 02 | Patient List | Todo | Pending | Pending |
| Task 03 | Patient Details | Todo | Pending | Pending |

### Module 03: Appointment

| Task ID | Task | Dev | Test | Git Upload |
|---|---|---|---|---|
| Task 01 | Book Appointment | Todo | Pending | Pending |
| Task 02 | Reschedule Appointment | Todo | Pending | Pending |
| Task 03 | Cancel Appointment | Todo | Pending | Pending |

## Dependency-First Development Sequence

Use this exact order for better development results. Start a module only when its dependency modules are stable.

| Order | Phase | Modules | Why First | Dependency Gate |
|---|---|---|---|---|
| 01 | Platform Foundation | Authentication, Role & Permission Management (RBAC), User Management, Tenant Management, Hospital Setup, System Configuration | Every secured API and tenant-scoped module depends on auth, RBAC, and tenant context. | Login, token, RBAC checks, tenant resolver, and core config must pass tests. |
| 02 | Core Clinical Master Data | Department Management, Staff Management, Doctor Management, Nurse Management, Patient Management, Medical Records Management, Consent Management | Clinical flows cannot run without staff, doctor, and patient master data. | CRUD + validation + permission tests all pass. |
| 03 | Scheduling and Queue | Calendar / Scheduling, Appointment Management, Queue Management, OPD Management, Consultation Management, Clinical Notes Management | OPD and consultation are driven by appointment and queue lifecycle. | Booking, queue transitions, and consultation save APIs must be stable. |
| 04 | Inpatient and Emergency | Admission / Transfer Management, IPD Management, Ward Management, Bed Management, Emergency / ER Management, Discharge Management, Care Plan Management, MAR | IPD/ER requires patient + doctor + ward/bed + admission rules from earlier phases. | Admit/transfer/discharge and bed state APIs must pass integration tests. |
| 05 | Diagnostics and Medication | Laboratory Management, Radiology / Imaging Management, E-Prescription, Prescription Management, Pharmacy Management, Blood Bank Management, OT / Surgery Management, Anesthesia Management | Orders are generated from consultations and admissions, so these depend on phases 03-04. | Order-to-result and order-to-dispense workflows tested end-to-end. |
| 06 | Billing and Revenue | Billing Management, Invoicing, Payment Management, Insurance / TPA Management, Claims Management, Revenue Cycle Management, Refund Management, Tax Management, Finance / Accounts Management | Financial workflows depend on clinical and pharmacy/lab events for charge capture. | Charge, invoice, payment, and claim APIs validated with real scenarios. |
| 07 | Operations and HR | Inventory / Store Management, Procurement / Purchase Management, Vendor / Supplier Management, Asset Management, Ambulance / Transport Management, Facility/Housekeeping/Maintenance, Biomedical Equipment, HR/Payroll/Attendance/Leave/Roster/Recruitment/Performance/Training | Operational modules support hospital execution and are safer after clinical-finance core is stable. | Stock, procurement, payroll, and service workflows pass module tests. |
| 08 | Platform Integrations and Security | API Management, Webhook Management, Integration Management, HL7 / FHIR, Third-party Integration, Security Management, MFA / SSO Management, Access Logs, Audit Logs, Data Privacy, Backup & Recovery, Disaster Recovery | Integrations/security hardening should layer on top of stable business APIs. | Security, audit, recovery, and integration contract tests pass. |
| 09 | Intelligence and Experience | Reports Management, Analytics Dashboard, BI Dashboard, Data Export / Import, CRM, Communication, SMS/Email/Notification Center, Help Desk, Patient Portal, Telemedicine, Localization, White-label/Branding, Template Management, Subscription/Plan/Pricing/Trial, AI Assistant, Workflow Automation, Background Job Queue, Predictive Analytics, OCR, API Client/Sandbox | Final optimization and productization layer after core platform reliability. | Reporting accuracy, notification reliability, and SLA smoke tests pass. |

### Development Rules

1. Do not start a later phase if dependency gate of earlier phase is failing.
2. For each module, finish API contract, validation, auth, and tests before frontend integration.
3. Mark Dev as Done only when Test is Pass.
4. Mark Git Upload as Uploaded only after branch merge or approved release commit.
5. If a dependency changes (example: RBAC, tenant middleware), re-test all dependent modules.

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

## Full Software Modules List - Task Register

| Module | Task ID | Task | Dev Status | Test | Upload |
|---|---|---|---|---|---|
| Dashboard | M001 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Tenant Management | M002 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Hospital Setup | M003 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Branch / Facility Management | M004 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Department Management | M005 | Implement module APIs and contracts | Todo | Not Run | Pending |
| User Management | M006 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Role & Permission Management (RBAC) | M007 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Staff Management | M008 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Patient Management | M009 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Patient Portal | M010 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Appointment Management | M011 | Implement module APIs and contracts | Todo | Not Run | Pending |
| OPD Management | M012 | Implement module APIs and contracts | Todo | Not Run | Pending |
| IPD Management | M013 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Emergency / ER Management | M014 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Ward Management | M015 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Bed Management | M016 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Doctor Management | M017 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Nurse Management | M018 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Consultation Management | M019 | Implement module APIs and contracts | Todo | Not Run | Pending |
| E-Prescription | M020 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Prescription Management | M021 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Medication Administration Record (MAR) | M022 | Implement module APIs and contracts | Todo | Not Run | Pending |
| OT / Surgery Management | M023 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Anesthesia Management | M024 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Blood Bank Management | M025 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Laboratory Management | M026 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Radiology / Imaging Management | M027 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Pharmacy Management | M028 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Inventory / Store Management | M029 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Procurement / Purchase Management | M030 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Vendor / Supplier Management | M031 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Asset Management | M032 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Ambulance / Transport Management | M033 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Billing Management | M034 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Invoicing | M035 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Payment Management | M036 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Insurance / TPA Management | M037 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Claims Management | M038 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Revenue Cycle Management | M039 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Refund Management | M040 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Finance / Accounts Management | M041 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Tax Management | M042 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Payroll Management | M043 | Implement module APIs and contracts | Todo | Not Run | Pending |
| HR Management | M044 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Attendance Management | M045 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Leave Management | M046 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Shift / Duty Roster Management | M047 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Recruitment Management | M048 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Performance Management | M049 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Training Management | M050 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Facility Management | M051 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Housekeeping Management | M052 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Maintenance Management | M053 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Biomedical Equipment Management | M054 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Help Desk / Support Ticketing | M055 | Implement module APIs and contracts | Todo | Not Run | Pending |
| CRM / Patient Relationship Management | M056 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Communication Management | M057 | Implement module APIs and contracts | Todo | Not Run | Pending |
| SMS / Email / Notification Center | M058 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Document Management | M059 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Medical Records Management | M060 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Consent Management | M061 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Queue Management | M062 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Calendar / Scheduling | M063 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Telemedicine | M064 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Clinical Notes Management | M065 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Care Plan Management | M066 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Discharge Management | M067 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Admission / Transfer Management | M068 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Infection Control Management | M069 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Incident Management | M070 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Quality Assurance Management | M071 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Compliance Management | M072 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Audit Logs | M073 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Access Logs | M074 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Security Management | M075 | Implement module APIs and contracts | Todo | Not Run | Pending |
| MFA / SSO Management | M076 | Implement module APIs and contracts | Todo | Not Run | Pending |
| API Management | M077 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Webhook Management | M078 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Integration Management | M079 | Implement module APIs and contracts | Todo | Not Run | Pending |
| HL7 / FHIR Integration | M080 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Third-party Integration | M081 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Reports Management | M082 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Analytics Dashboard | M083 | Implement module APIs and contracts | Todo | Not Run | Pending |
| BI / Intelligence Dashboard | M084 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Data Export / Import | M085 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Data Privacy Management | M086 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Backup & Recovery | M087 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Disaster Recovery | M088 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Subscription Management | M089 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Plan Management | M090 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Pricing Management | M091 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Trial Management | M092 | Implement module APIs and contracts | Todo | Not Run | Pending |
| White-label / Branding Management | M093 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Localization Management | M094 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Language / Currency / Timezone Settings | M095 | Implement module APIs and contracts | Todo | Not Run | Pending |
| System Configuration | M096 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Template Management | M097 | Implement module APIs and contracts | Todo | Not Run | Pending |
| AI Assistant Module | M098 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Workflow Automation | M099 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Background Job / Task Queue | M100 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Predictive Analytics | M101 | Implement module APIs and contracts | Todo | Not Run | Pending |
| OCR / Document Processing | M102 | Implement module APIs and contracts | Todo | Not Run | Pending |
| API Client / Sandbox Module | M103 | Implement module APIs and contracts | Todo | Not Run | Pending |
