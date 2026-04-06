# MedCore HMS SaaS API Task Tracker

Project: Ariba HMS (Hospital Management System)

Status legend:
- Dev Status: Todo | In Progress | Done
- Test: Not Run | Pass | Fail
- Upload: Pending | Uploaded

## What To Do Next (Module-Wise)

Use this section as your daily working list. Complete top to bottom.

| Step | Module | Primary Task IDs | What To Build Now | Current Status |
|---|---|---|---|---|
| 1 | Auth + RBAC | T038-T047, M006-M008 | Finish staff CRUD, role assignment, permission checks for all protected APIs | In Progress |
| 2 | Tenant + Settings | T014, T018, M002, X005-X007 | Complete tenant provisioning hardening and verify settings module per tenant | In Progress |
| 3 | Patient | T054-T061, M009 | Fix failing patient tests and stabilize patient CRUD/history/photo workflows | In Progress (Tests Fail) |
| 4 | Appointment + OPD | T067-T077, M011-M012, M019-M021 | Finish OPD flow end-to-end: queue, consultation, prescription, investigations | In Progress |
| 5 | IPD + Emergency | T085-T105, M013-M016 | Complete bed/ward/admission/discharge and emergency triage workflows | In Progress |
| 6 | Lab + Pharmacy + Blood Bank | T111-T133, M025-M028 | Complete order-to-result and order-to-dispense pipelines | In Progress |
| 7 | Billing + Insurance | T157-T160, M034-M040 | Complete invoice/payment/claims/refund cycle with validation and audit | In Progress |
| 8 | Inventory + HR | T170-T181, M029-M033, M043-M047 | Complete store/procurement and HR/payroll core APIs | In Progress |
| 9 | Reports + Portal | T195-T206, M010, M082-M085 | Build reports, analytics, and patient self-service APIs | In Progress |
| 10 | Security + Release | X001-X025, M073-M103 | Complete API standards, observability, CI gates, release checklist | In Progress |

## Module Checklist (Simple)

Mark each module after it is really complete (Dev Done + Test Pass + Upload Uploaded).

- [ ] M001 Dashboard
- [ ] M002 Tenant Management
- [ ] M003 Hospital Setup
- [ ] M004 Branch / Facility Management
- [ ] M005 Department Management
- [ ] M006 User Management
- [ ] M007 Role & Permission Management (RBAC)
- [ ] M008 Staff Management
- [ ] M009 Patient Management
- [ ] M010 Patient Portal
- [ ] M011 Appointment Management
- [ ] M012 OPD Management
- [ ] M013 IPD Management
- [ ] M014 Emergency / ER Management
- [ ] M015 Ward Management
- [ ] M016 Bed Management
- [ ] M017 Doctor Management
- [ ] M018 Nurse Management
- [ ] M019 Consultation Management
- [ ] M020 E-Prescription
- [ ] M021 Prescription Management
- [ ] M022 Medication Administration Record (MAR)
- [ ] M023 OT / Surgery Management
- [ ] M024 Anesthesia Management
- [ ] M025 Blood Bank Management
- [ ] M026 Laboratory Management
- [ ] M027 Radiology / Imaging Management
- [ ] M028 Pharmacy Management
- [ ] M029 Inventory / Store Management
- [ ] M030 Procurement / Purchase Management
- [ ] M031 Vendor / Supplier Management
- [ ] M032 Asset Management
- [ ] M033 Ambulance / Transport Management
- [ ] M034 Billing Management
- [ ] M035 Invoicing
- [ ] M036 Payment Management
- [ ] M037 Insurance / TPA Management
- [ ] M038 Claims Management
- [ ] M039 Revenue Cycle Management
- [ ] M040 Refund Management
- [ ] M041 Finance / Accounts Management
- [ ] M042 Tax Management
- [ ] M043 Payroll Management
- [ ] M044 HR Management
- [ ] M045 Attendance Management
- [ ] M046 Leave Management
- [ ] M047 Shift / Duty Roster Management
- [ ] M048 Recruitment Management
- [ ] M049 Performance Management
- [ ] M050 Training Management
- [ ] M051 Facility Management
- [ ] M052 Housekeeping Management
- [ ] M053 Maintenance Management
- [ ] M054 Biomedical Equipment Management
- [ ] M055 Help Desk / Support Ticketing
- [ ] M056 CRM / Patient Relationship Management
- [ ] M057 Communication Management
- [ ] M058 SMS / Email / Notification Center
- [ ] M059 Document Management
- [ ] M060 Medical Records Management
- [ ] M061 Consent Management
- [ ] M062 Queue Management
- [ ] M063 Calendar / Scheduling
- [ ] M064 Telemedicine
- [ ] M065 Clinical Notes Management
- [ ] M066 Care Plan Management
- [ ] M067 Discharge Management
- [ ] M068 Admission / Transfer Management
- [ ] M069 Infection Control Management
- [ ] M070 Incident Management
- [ ] M071 Quality Assurance Management
- [ ] M072 Compliance Management
- [ ] M073 Audit Logs
- [ ] M074 Access Logs
- [ ] M075 Security Management
- [ ] M076 MFA / SSO Management
- [ ] M077 API Management
- [ ] M078 Webhook Management
- [ ] M079 Integration Management
- [ ] M080 HL7 / FHIR Integration
- [ ] M081 Third-party Integration
- [ ] M082 Reports Management
- [ ] M083 Analytics Dashboard
- [ ] M084 BI / Intelligence Dashboard
- [ ] M085 Data Export / Import
- [ ] M086 Data Privacy Management
- [ ] M087 Backup & Recovery
- [ ] M088 Disaster Recovery
- [ ] M089 Subscription Management
- [ ] M090 Plan Management
- [ ] M091 Pricing Management
- [ ] M092 Trial Management
- [ ] M093 White-label / Branding Management
- [ ] M094 Localization Management
- [ ] M095 Language / Currency / Timezone Settings
- [ ] M096 System Configuration
- [ ] M097 Template Management
- [ ] M098 AI Assistant Module
- [ ] M099 Workflow Automation
- [ ] M100 Background Job / Task Queue
- [ ] M101 Predictive Analytics
- [ ] M102 OCR / Document Processing
- [ ] M103 API Client / Sandbox Module

## API Module Format Example

### Module 01: Auth

| Task ID | Task | Dev | Test | Git Upload |
|---|---|---|---|---|
| Task 01 | Login | Done | Pass | Pending |
| Task 02 | Registration | Done | Pass | Pending |
| Task 03 | Forget Password | Done | Pass | Pending |

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
| Patient Registration & Demographics | T056 | PatientController create/read/update/search | In Progress | Fail | Pending |
| Patient Registration & Demographics | T058 | Duplicate detection logic | In Progress | Fail | Pending |
| Patient Registration & Demographics | T059 | Medical history API | In Progress | Fail | Pending |
| Patient Registration & Demographics | T060 | Visit timeline API | In Progress | Fail | Pending |
| Patient Registration & Demographics | T061 | Patient photo upload API | In Progress | Fail | Pending |
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
| Dashboard | M001 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Tenant Management | M002 | Implement module APIs and contracts | In Progress | Pass | Pending |
| Hospital Setup | M003 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Branch / Facility Management | M004 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Department Management | M005 | Implement module APIs and contracts | Todo | Not Run | Pending |
| User Management | M006 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Role & Permission Management (RBAC) | M007 | Implement module APIs and contracts | In Progress | Pass | Pending |
| Staff Management | M008 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Patient Management | M009 | Implement module APIs and contracts | In Progress | Fail | Pending |
| Patient Portal | M010 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Appointment Management | M011 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| OPD Management | M012 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| IPD Management | M013 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Emergency / ER Management | M014 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Ward Management | M015 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Bed Management | M016 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Doctor Management | M017 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Nurse Management | M018 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Consultation Management | M019 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| E-Prescription | M020 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Prescription Management | M021 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Medication Administration Record (MAR) | M022 | Implement module APIs and contracts | Todo | Not Run | Pending |
| OT / Surgery Management | M023 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Anesthesia Management | M024 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Blood Bank Management | M025 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Laboratory Management | M026 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Radiology / Imaging Management | M027 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Pharmacy Management | M028 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Inventory / Store Management | M029 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Procurement / Purchase Management | M030 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Vendor / Supplier Management | M031 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Asset Management | M032 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Ambulance / Transport Management | M033 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Billing Management | M034 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Invoicing | M035 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Payment Management | M036 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Insurance / TPA Management | M037 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Claims Management | M038 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| Revenue Cycle Management | M039 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Refund Management | M040 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Finance / Accounts Management | M041 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Tax Management | M042 | Implement module APIs and contracts | Todo | Not Run | Pending |
| Payroll Management | M043 | Implement module APIs and contracts | In Progress | Not Run | Pending |
| HR Management | M044 | Implement module APIs and contracts | In Progress | Not Run | Pending |
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

## Additional Must-Have Tasks (Recommended)

These are extra tasks to improve software quality, stability, and release success.

| Task ID | Area | Task | Dev Status | Test | Upload |
|---|---|---|---|---|---|
| X001 | API Standards | Define OpenAPI/Swagger contract for every module endpoint | Todo | Not Run | Pending |
| X002 | API Standards | Standardize error response format (code, message, details, trace_id) | Todo | Not Run | Pending |
| X003 | API Standards | Version all public APIs (`/api/v1`) and add deprecation policy | Todo | Not Run | Pending |
| X004 | Validation | Create reusable request validators and shared rule sets | Todo | Not Run | Pending |
| X005 | Security | Add permission matrix test per endpoint (allow + deny cases) | In Progress | Pass | Pending |
| X006 | Security | Enforce audit trail on create/update/delete critical resources | In Progress | Pass | Pending |
| X007 | Security | Add tenant-isolation tests for all tenant-scoped queries | Todo | Not Run | Pending |
| X008 | Security | Add API rate-limit rules by role and endpoint type | Todo | Not Run | Pending |
| X009 | Performance | Add pagination/filters/sorting to all listing endpoints | In Progress | Pass | Pending |
| X010 | Performance | Add database indexes based on real query paths | Todo | Not Run | Pending |
| X011 | Performance | Add cache strategy for heavy dashboards/reports | Todo | Not Run | Pending |
| X012 | Reliability | Add idempotency keys for payment/claim/refund APIs | Todo | Not Run | Pending |
| X013 | Reliability | Add retry policy and dead-letter handling for queued jobs | Todo | Not Run | Pending |
| X014 | Observability | Add request correlation id and structured logs | Todo | Not Run | Pending |
| X015 | Observability | Add health, readiness, and dependency-check endpoints | In Progress | Pass | Pending |
| X016 | Testing | Add happy-path + edge-case + failure-case tests per endpoint | In Progress | Fail | Pending |
| X017 | Testing | Add module integration tests (cross-module workflows) | In Progress | Fail | Pending |
| X018 | Testing | Add smoke test suite for pre-release validation | Todo | Not Run | Pending |
| X019 | Data | Add seed packs: minimal, demo, and load-test datasets | Todo | Not Run | Pending |
| X020 | CI/CD | Add CI gate: lint + unit + feature + migration check | In Progress | Pass | Pending |
| X021 | CI/CD | Add contract-test step for frontend/backend compatibility | Todo | Not Run | Pending |
| X022 | Documentation | Add endpoint examples (request/response) for each module | Todo | Not Run | Pending |
| X023 | Documentation | Add rollback steps and data migration notes per release | Todo | Not Run | Pending |
| X024 | Release | Add pre-release checklist and go-live sign-off matrix | Todo | Not Run | Pending |
| X025 | Post-Release | Add post-deploy verification checklist and incident playbook | Todo | Not Run | Pending |

## Definition of Done (Per API Task)

Mark a task Done only if all items below are complete:

| Check | Required |
|---|---|
| API endpoint implemented | Yes |
| Input validation complete | Yes |
| Permission and tenant-scope enforced | Yes |
| Unit/feature tests passed | Yes |
| Error responses standardized | Yes |
| Docs updated with examples | Yes |
| Commit merged/uploaded | Yes |

## Auto-Audit Notes (2026-04-06)

Statuses auto-updated to In Progress/Pass where implementation evidence exists:

- Permission middleware and guarded routes exist in backend routes.
- Audit middleware/model exists and is wired on protected route groups.
- Health endpoint exists (`/api/health`), but readiness/dependency checks are still pending.
- Pagination is implemented in several listing controllers (tenant, patient, billing, lab, inventory, pharmacy, users).
- Feature tests exist for auth, tenant management, and patient workflows.
- CI workflow exists and runs backend tests + frontend lint/test/build.
- Current gap: `PatientModuleTest` has failing scenarios (403 responses) and is marked as Fail in related rows until fixed.
