# ✅ MedCore HMS — Full Development Task List
**Laravel 12 + React 19 + MySQL 8 | ERP SaaS Hospital Management System**
> Author: Boni Yeamin | Total Tasks: 229 | Status: [ ] = Todo · [x] = Done · [~] = In Progress

## Current Status Audit (2026-04-05)

This audit reflects the actual repository implementation state after local commits through `complete phase 4 advanced modules`.

| Phase | Current State | Approx. Progress | Notes |
|---|---|---:|---|
| Phase 0 — Foundation | In Progress | 45% | Project scaffold, Docker base, CI checks, tenancy middleware, Sanctum done; advanced infra (ECR/CodeDeploy/Sentry/Slack/Soketi/Horizon/Meilisearch) pending |
| Phase 1 — Auth & RBAC | In Progress | 40% | Login/logout/reset/2FA skeleton, custom RBAC, audit middleware done; lockout/session management/staff management/frontend auth screens pending |
| Phase 2 — Patient | Done | 100% | Patient registration, UHID generation service, history and timeline APIs done; Meilisearch, duplicate detection, photo upload, full frontend flows implemented |
| Phase 3 — OPD | In Progress | 45% | Queue, consultations, prescriptions, investigation ordering APIs done; realtime queue, vitals, ICD search, PDF jobs, referrals, dedicated frontend screens pending |
| Phase 4 — IPD | In Progress | 40% | Admission, bed availability, ward rounds, nursing notes, MAR, discharge clearance APIs done; richer bed states, websocket events, diet/transfer/death summary + frontend UIs pending |
| Phase 5 — Emergency | In Progress | 30% | Triage workflow and levels implemented; rapid John Doe flow, resuscitation log, dedicated ER bed pool, MLC, focused frontend screens pending |
| Phase 6 — Pharmacy | In Progress | 45% | Drug master, batch stock, dispense/counter sale APIs done; FEFO, interactions, purchase workflow, alerts, full frontend module pending |
| Phase 7 — Laboratory | In Progress | 45% | Test catalog, sample collection, lab orders/results/validation/report APIs done; HL7/ASTM import, critical alerts, QR PDF, full frontend module pending |
| Phase 8 — Billing | In Progress | 40% | Charge master, invoices, payments, discount approval APIs done; gateway integrations, package/refund workflows, daily collection features pending |
| Phase 9 — Operations | In Progress | 35% | Inventory/procurement and HR/payroll skeleton APIs done; attendance/leave/loan, nursing assignment/call system, dashboard UIs pending |
| Phase 10 — Reports | Todo | 0% | Not started |
| Phase 11 — SaaS Admin | In Progress | 20% | Core tenant onboarding backend exists; dedicated super-admin portal + SaaS billing analytics pending |
| Phase 12 — Patient Portal | Todo | 0% | Not started |
| Phase 13 — QA & Launch | In Progress | 10% | Baseline test/lint/build pipelines exist; formal coverage/load/security/UAT/go-live hardening pending |

### Audit Notes
- Detailed checklist line-by-line `[x]` updates are not yet fully synchronized with implementation commits.
- Current practical focus has delivered backend-first vertical slices and route coverage through advanced modules.
- Workflow preference is commit-only; pushes are intentionally deferred.

### Completion Workflow (Mandatory)
- When a task is completed, immediately change `[ ]` or `[~]` to `[x]` in this file.
- After marking a task complete, always suggest the next logical task from the same phase.
- When all tasks in a phase are complete, create one dedicated phase completion commit.
- Use commit format: `phase-<number>: complete <phase-name>`.
- Include completed task IDs in commit body for traceability.

#### Task Completion Template
- Completed: `T###`
- Next Suggested Task: `T###`
- Commit: `<hash> <message>`

---

## 📦 PHASE 0 — Foundation & Infrastructure
> Weeks 1–3 | Prerequisites for all other phases

### 0.1 Project Setup
- [x] T001 — Initialize Laravel 12 project (`medcore-hms-api`)
- [x] T002 — Initialize React 19 + TypeScript + Vite project (`medcore-hms-web`)
- [x] T003 — Configure `docker-compose.yml` (nginx, app, mysql, redis, meilisearch, soketi, horizon)
- [x] T004 — Write `Makefile` with shortcuts: `make dev`, `make migrate`, `make seed`, `make test`
- [x] T005 — Setup GitHub repository with branch strategy (`main`, `develop`, `feature/*`, `hotfix/*`)
- [x] T006 — Configure `.env.example` for both API and frontend projects
- [x] T007 — Setup EditorConfig, PHP-CS-Fixer, ESLint, Prettier configs

### 0.2 CI/CD Pipeline
- [~] T008 — Create GitHub Actions workflow: lint + Pest PHP tests on every PR
- [x] T009 — Create GitHub Actions workflow: TypeScript check + Vitest on every PR
- [ ] T010 — Create GitHub Actions workflow: build Docker image + push to AWS ECR on merge to `main`
- [ ] T011 — Configure blue-green deployment to AWS EC2 via CodeDeploy
- [ ] T012 — Setup Sentry error tracking for both Laravel and React apps
- [ ] T013 — Configure Slack webhook notifications for deployment success/failure

### 0.3 Database & Multi-Tenancy
- [~] T014 — Create central database schema migrations (`tenants`, `plans`, `subscriptions`, `invoices`, `admin_users`)
- [x] T015 — Install and configure multi-tenancy package (Stancl/Tenancy or custom `TenantMiddleware`)
- [x] T016 — Implement subdomain-based tenant resolution middleware
- [x] T017 — Implement per-tenant DB connection switching at runtime
- [~] T018 — Create tenant database migration runner (provision schema on new tenant signup)
- [ ] T019 — Write seeders: demo tenant, admin user, sample hospital data
- [ ] T020 — Configure S3 per-tenant file path isolation (`tenants/{id}/...`)

### 0.4 Base Laravel Configuration
- [~] T021 — Configure Laravel Sanctum for SPA cookie auth + API token auth
- [ ] T022 — Install Spatie Laravel Permission package and publish migrations
- [ ] T023 — Install Laravel Horizon and configure queue workers
- [ ] T024 — Install and configure Meilisearch driver (`laravel/scout`)
- [~] T025 — Configure AWS S3 filesystem driver with CloudFront CDN URL
- [ ] T026 — Configure Laravel Echo + Soketi WebSocket server
- [ ] T027 — Setup global API exception handler with structured JSON error responses
- [ ] T028 — Setup API response macro: `ApiResponse::success()`, `ApiResponse::error()`
- [ ] T029 — Configure CORS for React frontend domain(s)
- [ ] T030 — Setup rate limiting middleware (60/min public, 300/min auth, 1000/min API key)

### 0.5 Base React Configuration
- [x] T031 — Install and configure Tailwind CSS 4 with custom design tokens
- [x] T032 — Setup React Router v7 with nested layouts per portal
- [x] T033 — Configure Axios API client with interceptors (auth token, tenant header, error handling)
- [ ] T034 — Setup Zustand stores: `authStore`, `tenantStore`, `notificationStore`
- [ ] T035 — Setup React Query (TanStack Query) with global query client config
- [ ] T036 — Setup i18next with English and Bangla locale files
- [ ] T037 — Build shared UI component library: `Button`, `Modal`, `DataTable`, `Badge`, `FormField`, `Alert`, `Spinner`

---

## 🔐 PHASE 1 — Authentication & RBAC
> Weeks 4–5 | P0 Critical

### 1.1 Backend — Auth
- [x] T038 — Create `AuthController`: login, logout, refresh token, me
- [x] T039 — Implement OTP-based 2FA: generate secret, verify OTP, enable/disable per user
- [x] T040 — Implement brute-force lockout: max 5 failed attempts → 15-min lockout
- [x] T041 — Implement password policy: min 8 chars, complexity, expiry, history (last 5)
- [x] T042 — Implement session management: list active sessions, revoke specific session, revoke all
- [x] T043 — Create `AuditLogMiddleware`: log every API request with user, IP, method, route, payload diff
- [x] T044 — Seed all roles: Super Admin, Tenant Admin (Hospital Admin), Hospital Manager, Operations Manager, Doctor, Nurse, Receptionist / Front Desk, Pharmacist, Lab Technician, Accountant / Finance Manager, Ward Manager, Ambulance Driver / Transport Staff, IT Admin / System Administrator, Inventory Manager / Store Manager, HR Manager, Patient (Portal User), Insurance Agent / Partner, Auditor / Compliance Officer, Data Analyst, API Client / Integration Role, AI Assistant Role
- [x] T045 — Seed all permissions matrix (e.g., `patient.create`, `billing.view`, `prescription.create`, etc.)

### 1.2 Backend — Staff Management
- [ ] T046 — Create `StaffController`: CRUD staff accounts, assign role, assign department
- [ ] T047 — Implement staff suspension/reactivation with reason logging
- [ ] T048 — Create `DepartmentController`: CRUD departments per tenant

### 1.3 Frontend — Auth
- [x] T049 — Build Login page with email/password form + 2FA OTP step
- [x] T050 — Build "Forgot Password" and "Reset Password" flows
- [x] T051 — Implement route guards: redirect unauthenticated users to login
- [~] T052 — Implement role-based portal routing (doctor → `/doctor`, nurse → `/nurse`, etc.)
- [ ] T053 — Build Active Sessions page: list devices, revoke session button
- [~] T211 — Implement premium Emerald & Slate theme across auth portal [DONE]
- [x] T212 — Implement dynamic database-driven menu system with permission gating
- [x] T213 — Build Role-Based Task Dashboard with status/priority workflows

### 1.4 Next Suggested Task
- Suggested Now: `T053` — Build Active Sessions page: list devices, revoke session button
- After `T053`: continue to `T046` — StaffController CRUD (start Phase 1.2 completion)

---

## 👤 PHASE 2 — Patient Registration & Demographics
> Weeks 5–6 | P0 Critical

### 2.1 Backend
- [x] T054 — Create `patients` migration with all demographic fields + indexes
- [x] T055 — Implement UHID auto-generation (format: `HMS-YYYY-XXXXXX`, unique per tenant)
- [x] T056 — Create `PatientController`: create, read, update, search
- [x] T057 — Integrate Meilisearch index for patient search (name, UHID, phone, NID)
- [x] T058 — Implement duplicate detection: flag if name + DOB + phone match existing record
- [x] T059 — Create `PatientMedicalHistoryController`: allergies, chronic conditions, surgical history
- [x] T060 — Create `visits` migration and `VisitController` to list full patient visit timeline
- [x] T061 — Implement patient photo upload to S3 with thumbnail generation

### 2.2 Frontend
- [x] T062 — Build Patient Registration form (walk-in + pre-registered tabs)
- [x] T063 — Build Patient Search bar with live Meilisearch results
- [x] T064 — Build Patient Profile page: demographics, medical history, visit timeline tabs
- [x] T065 — Build duplicate patient warning modal with merge option
- [x] T066 — Build photo capture component (webcam + file upload)

---

## 🏥 PHASE 3 — OPD (Out-Patient Department)
> Weeks 6–8 | P0 Critical

### 3.1 Backend
- [x] T067 — Create `appointments` and `doctor_slots` migrations
- [x] T068 — Create `AppointmentController`: slot listing, book, cancel, reschedule
- [x] T069 — Create `OpdQueueController`: generate token, real-time queue state, call next, skip
- [x] T070 — Broadcast `OpdQueueUpdated` event via WebSocket on every queue change
- [x] T071 — Create `VitalsController`: record BP, temp, pulse, SpO2, weight, height, BMI calculation
- [x] T072 — Create `ConsultationController`: SOAP note save, ICD-10 code search via Meilisearch
- [x] T073 — Create `PrescriptionController` + `PrescriptionItemController`: e-prescription with drug-allergy check
- [x] T074 — Create `InvestigationOrderController`: order lab/radiology tests from consultation, auto-route to respective modules
- [x] T075 — Implement PDF generation job: e-prescription PDF → S3 → signed URL
- [x] T076 — Implement PDF generation job: sick leave certificate with doctor signature
- [x] T077 — Create `ReferralController`: internal referral to IPD/specialist + external referral letter PDF

### 3.2 Frontend
- [ ] T078 — Build OPD Queue dashboard with real-time token board (WebSocket)
- [ ] T079 — Build Vitals entry form (triage screen)
- [ ] T080 — Build SOAP Consultation editor with ICD-10 autocomplete
- [ ] T081 — Build E-Prescription builder: drug search, dose/frequency/duration fields, allergy alert banner
- [ ] T082 — Build Investigation Order panel (checkboxes for lab/radiology tests)
- [ ] T083 — Build Follow-up Appointment booking modal (from consultation screen)
- [ ] T084 — Build Appointment Scheduler for receptionist: calendar view, slot grid, booking form

---

## 🛏️ PHASE 4 — IPD (In-Patient Department)
> Weeks 9–12 | P0 Critical

### 4.1 Backend
- [ ] T085 — Create `wards`, `rooms`, `beds` migrations with bed category (general/semi-private/private/ICU)
- [ ] T086 — Create `BedController`: real-time bed status (available/occupied/reserved/maintenance)
- [ ] T087 — Broadcast `BedStatusChanged` event via WebSocket
- [ ] T088 — Create `AdmissionController`: admit patient, assign bed, assign doctor, record guarantor
- [ ] T089 — Create `WardRoundController`: doctor round notes with timestamp and multi-doctor support
- [ ] T090 — Create `MARController` (Medication Administration Record): nurse records each drug administration
- [ ] T091 — Create `NursingNoteController`: shift-wise nursing assessment and care plan
- [ ] T092 — Create `DietOrderController`: diet type entry, kitchen module integration
- [ ] T093 — Create `TransferController`: ward-to-ward transfer with transfer note
- [ ] T094 — Create `DischargeController`: generate discharge summary PDF, clear pending bills, book follow-up
- [ ] T095 — Create `DeathSummaryController`: death documentation with ICD-10 cause of death

### 4.2 Frontend
- [ ] T096 — Build real-time Bed Matrix (grid view by ward/room/bed, color-coded by status)
- [ ] T097 — Build Admission form (patient search + bed selector + doctor assignment)
- [ ] T098 — Build Ward Round notes editor with timestamp
- [ ] T099 — Build MAR (Medication Administration Record) sheet — nurse view
- [ ] T100 — Build Nursing Notes form with shift selector
- [ ] T101 — Build Patient Transfer modal
- [ ] T102 — Build Discharge Summary wizard (auto-populate from visit data + editable fields)

---

## 🚨 PHASE 5 — Emergency & Triage
> Weeks 12–13 | P0 Critical

- [ ] T103 — Create `EmergencyRegistrationController`: rapid minimal-field registration + John Doe support
- [ ] T104 — Implement MTS triage color coding (Red/Orange/Yellow/Green/Blue) with auto-priority queue
- [ ] T105 — Create `ResuscitationLogController`: real-time timestamped event recording
- [ ] T106 — Create dedicated emergency bed pool separate from IPD beds
- [ ] T107 — Create `MLCController`: medico-legal case documentation + police intimation flag
- [ ] T108 — Build Emergency Registration rapid form (< 5 fields for speed)
- [ ] T109 — Build Triage Assessment screen with MTS color selector
- [ ] T110 — Build Emergency bed matrix (ER-specific view)

---

## 💊 PHASE 6 — Pharmacy Management
> Weeks 13–16 | P0 Critical

### 6.1 Backend
- [ ] T111 — Create `drug_master`, `pharmacy_stock` migrations (batch, expiry, FIFO/FEFO)
- [ ] T112 — Create `DrugMasterController`: CRUD drug catalog with Meilisearch indexing
- [ ] T113 — Create `PharmacyStockController`: batch-wise stock management, FEFO dispensing logic
- [ ] T114 — Create `DispenseController`: scan UHID/prescription QR, fulfill prescription, partial dispensing
- [ ] T115 — Implement drug-drug and drug-allergy interaction check at dispensing
- [ ] T116 — Create `OTCSaleController`: counter sale without prescription + retail billing
- [ ] T117 — Create `PharmacyPurchaseOrderController`: PO to supplier, GRN, invoice matching
- [ ] T118 — Create scheduled job: daily expiry alert for stock expiring in < 30/60/90 days
- [ ] T119 — Create scheduled job: low stock alert when qty falls below reorder level

### 6.2 Frontend
- [ ] T120 — Build Pharmacy Dispense Queue (prescription list with UHID lookup)
- [ ] T121 — Build Drug Master management CRUD table
- [ ] T122 — Build Stock management table with batch/expiry view
- [ ] T123 — Build Purchase Order creation form + GRN entry form
- [ ] T124 — Build Expiry Dashboard with near-expiry alerts and quarantine workflow

---

## 🔬 PHASE 7 — Laboratory & Diagnostics
> Weeks 16–19 | P0 Critical

### 7.1 Backend
- [ ] T125 — Create `lab_tests`, `lab_parameters`, `lab_orders`, `lab_results` migrations
- [ ] T126 — Create `LabTestMasterController`: CRUD test catalog with reference ranges (age/gender specific)
- [ ] T127 — Create `LabOrderController`: create order from OPD/IPD, barcode label generation
- [ ] T128 — Create `SampleCollectionController`: record collection, specimen type, collector
- [ ] T129 — Create `LabResultController`: manual result entry + HL7/ASTM file import parser
- [ ] T130 — Implement critical value detection: auto-broadcast `CriticalLabAlert` event to doctor + nurse
- [ ] T131 — Create `LabValidationController`: pathologist digital sign-off before report release
- [ ] T132 — Implement lab report PDF generation with QR verification code → S3
- [ ] T133 — Create `CultureSensitivityController`: antibiogram table result entry

### 7.2 Frontend
- [ ] T134 — Build Lab Sample Queue (ordered by priority, status tracking)
- [ ] T135 — Build Result Entry form with parameter grid + reference range highlight
- [ ] T136 — Build Critical Alert notification banner (real-time WebSocket)
- [ ] T137 — Build Pathologist Validation screen (review + sign-off + reject with comment)
- [ ] T138 — Build Lab Report PDF preview with QR code

---

## 💰 PHASE 8 — Billing & Invoice Management
> Weeks 19–22 | P0 Critical

### 8.1 Backend
- [ ] T139 — Create `service_charge_master`, `bills`, `bill_items`, `payments` migrations
- [ ] T140 — Create `ChargeMasterController`: configurable per category (OPD/IPD/Lab/Pharmacy/Procedure/Bed)
- [ ] T141 — Implement auto-charge accumulation: hook into pharmacy dispense, lab result, procedures → bill_items
- [ ] T142 — Create `BillController`: generate itemized invoice with GST/VAT, advance payment tracking
- [ ] T143 — Create `PaymentController`: record payment (cash/card/bKash/Nagad), reconciliation
- [ ] T144 — Integrate bKash API for mobile payment collection
- [ ] T145 — Integrate SSL Commerz for card payment gateway
- [ ] T146 — Create `DiscountController`: request workflow → configurable approval tiers → apply
- [ ] T147 — Create `PackageBillingController`: pre-defined packages (surgical/delivery/health check)
- [ ] T148 — Create `RefundController`: partial/full refund with reason + approval workflow
- [ ] T149 — Create `DailyCollectionController`: shift-wise cashier summary + reconciliation report

### 8.2 Frontend
- [ ] T150 — Build Patient Bill view (itemized charges grouped by module)
- [ ] T151 — Build Payment collection screen (method selector + amount entry)
- [ ] T152 — Build Discount Request form with approval status tracker
- [ ] T153 — Build Package Billing selector modal
- [ ] T154 — Build Daily Collection summary dashboard for cashiers

---

## 🏗️ PHASE 9 — Operations (Inventory, HR, Nursing)
> Weeks 23–27 | P1 High

### 9.1 Inventory & Procurement
- [ ] T155 — Create `inventory_items`, `inventory_stock`, `purchase_orders`, `grn` migrations
- [ ] T156 — Create `InventoryItemController`: categorized non-drug items (equipment, consumables, linen)
- [ ] T157 — Create `IndentController`: department requisition → approval → PO generation
- [ ] T158 — Create `InventoryPOController`: vendor-wise PO + GRN with partial delivery support
- [ ] T159 — Create `IssueController`: department-wise item issue + consumption tracking
- [ ] T160 — Build Inventory dashboard (stock levels, reorder alerts, pending POs)

### 9.2 HR & Payroll
- [ ] T161 — Create `staff_profiles`, `attendance`, `leaves`, `payroll`, `loans` migrations
- [ ] T162 — Create `AttendanceController`: biometric/manual attendance + shift management
- [ ] T163 — Create `LeaveController`: leave types, application → approval workflow, balance tracking
- [ ] T164 — Create `PayrollController`: gross/net computation with deductions, payslip PDF generation
- [ ] T165 — Build Attendance management table with shift selector
- [ ] T166 — Build Leave application form + approval queue
- [ ] T167 — Build Payroll run screen + payslip preview

### 9.3 Nursing & Ward Management
- [ ] T168 — Create `NurseAssignmentController`: assign nurses to wards/shifts
- [ ] T169 — Create real-time nurse call system: patient-triggered alert → nurse dashboard notification
- [ ] T170 — Build Nurse Assignment schedule (shift-wise ward assignments)
- [ ] T171 — Build Nurse Call alert panel (real-time WebSocket)

---

## 📊 PHASE 10 — Reports & Analytics
> Weeks 28–31 | P1 High

- [ ] T172 — Build report engine base: dynamic query builder with filters (date range, department, doctor)
- [ ] T173 — Implement OPD/IPD census reports with PDF + Excel export
- [ ] T174 — Implement Bed Occupancy Rate and ALOS calculation reports
- [ ] T175 — Implement Revenue reports: by department, by doctor, daily collection trend
- [ ] T176 — Implement Pharmacy reports: dispensing, consumption, stock valuation, near-expiry
- [ ] T177 — Implement Lab reports: test volume, TAT analysis, critical values, analyzer performance
- [ ] T178 — Implement HR reports: attendance summary, leave balance, payroll register
- [ ] T179 — Implement Clinical reports: diagnosis frequency (ICD-10), readmission rate
- [ ] T180 — Implement Financial reports: P&L by department, AR aging, insurance recovery rate
- [ ] T181 — Build Executive Dashboard: KPI widgets (revenue, patient load, bed occupancy, open bills)
- [ ] T182 — Implement custom report builder (drag-and-drop field selector + filter builder)
- [ ] T183 — Implement report export: PDF, Excel (.xlsx), CSV, JSON API

---

## ☁️ PHASE 11 — SaaS Super Admin & Tenant Layer
> Weeks 32–34 | P0 Critical

- [ ] T184 — Build Super Admin portal at `admin.medcorehms.com` (separate React app/portal)
- [ ] T185 — Build Tenant listing + detail view (usage stats, subscription status, module flags)
- [ ] T186 — Build Tenant onboarding wizard: hospital info → admin user → subdomain → plan → DB provisioning
- [ ] T187 — Build Subscription management: plan change, billing cycle switch, cancellation
- [ ] T188 — Integrate Stripe for automated SaaS subscription invoicing and payment collection
- [ ] T189 — Implement automated tenant suspension on payment failure + grace period logic
- [ ] T190 — Build Feature Flags UI: toggle modules per tenant (Radiology, Blood Bank, etc.)
- [ ] T191 — Build Platform Analytics dashboard: MRR, churn rate, active tenants, module adoption
- [ ] T192 — Implement usage monitoring: patient count, bed count, API calls per tenant

---

## 👨‍⚕️ PHASE 12 — Patient Self-Service Portal
> Weeks 35–36 | P1 High

- [ ] T193 — Build Patient Portal login (auto-credentials created on registration)
- [ ] T194 — Build Appointment booking: doctor search → slot selection → confirmation → SMS reminder
- [ ] T195 — Build My Reports page: downloadable lab/radiology PDFs with QR verification
- [ ] T196 — Build My Prescriptions page: current + historical prescriptions
- [ ] T197 — Build My Bills page: outstanding and paid invoices + online payment (bKash/card)
- [ ] T198 — Build Health Timeline: chronological view of all visits, diagnoses, medications

---

## 🧪 PHASE 13 — QA, Security & Launch
> Weeks 37–40

- [ ] T199 — Write Pest PHP feature tests for all P0 module APIs (patient, OPD, IPD, pharmacy, lab, billing) — target 80% coverage
- [ ] T200 — Write Vitest unit tests for React utility functions and Zustand stores
- [ ] T201 — Perform load testing with k6: simulate 500 concurrent users on OPD queue + billing endpoints
- [ ] T202 — Conduct OWASP Top-10 security audit: SQL injection, XSS, CSRF, broken auth, IDOR checks
- [ ] T203 — Conduct penetration testing on auth endpoints, file upload, and tenant boundary enforcement
- [ ] T204 — Configure AWS WAF rules: rate limiting, geo-blocking, OWASP managed rule groups
- [ ] T205 — Setup automated DB backup: daily RDS snapshots + cross-region S3 replication
- [ ] T206 — Conduct UAT (User Acceptance Testing) with 2 pilot hospital clients
- [ ] T207 — Fix UAT-identified bugs and UI/UX issues
- [ ] T208 — Write `README.md` + developer onboarding guide + API documentation (Scramble)
- [ ] T209 — Configure production AWS infrastructure (EC2 Auto Scaling, RDS Multi-AZ, ElastiCache, CloudFront)
- [ ] T210 — Execute production go-live: DNS switch, SSL cert, smoke test, monitoring alert setup

---

## 📚 PHASE 14 — Documentation, Internationalization & Team Enablement
> Completed Documentation Work

- [x] T214 — Create unified documentation hub index (`docs/README.md`) with clear navigation
- [x] T215 — Create architecture guide for technical boundaries and cross-cutting concerns
- [x] T216 — Create development onboarding docs (`SETUP.md`, `CONTRIBUTING.md`)
- [x] T217 — Create API standards documentation for endpoint contracts and versioning
- [x] T218 — Create security model documentation for tenant boundary and sensitive operations
- [x] T219 — Create operations runbook for incident response and release-day checks
- [x] T220 — Create internationalization/localization documentation for multi-country deployment
- [x] T221 — Create brand guidelines documentation for international product consistency
- [x] T222 — Create testing strategy documentation for quality gates and risk coverage
- [x] T223 — Create release process documentation for staged rollout and rollback
- [x] T224 — Create permission-to-menu matrix (`docs/dashboard/PERMISSION_MENU_MATRIX.md`) mapped to backend permission keys
- [x] T225 — Add complete dashboard menu/submenu catalog and backend seed structure for all modules
- [x] T226 — Create dashboard route implementation matrix (`docs/dashboard/ROUTE_IMPLEMENTATION_MATRIX.md`)
- [x] T227 — Create reusable widget library spec (`docs/dashboard/WIDGET_LIBRARY_SPEC.md`)
- [x] T228 — Create role switch policy and governance doc (`docs/dashboard/ROLE_SWITCH_POLICY.md`)
- [x] T229 — Expand dashboard menu/submenu tree with OPD, IPD, Emergency, Insurance, Blood Bank, Mortuary, and SaaS Admin modules

---

## 📈 Progress Tracker

| Phase | Tasks | Done | Progress |
|---|---|---|---|
| Phase 0 — Foundation | 37 | 15 | 41% |
| Phase 1 — Auth & RBAC | 16 | 13 | 81% |
| Phase 2 — Patient | 13 | 13 | 100% |
| Phase 3 — OPD | 18 | 11 | 61% |
| Phase 4 — IPD | 18 | 0 | 0% |
| Phase 5 — Emergency | 8 | 0 | 0% |
| Phase 6 — Pharmacy | 14 | 0 | 0% |
| Phase 7 — Laboratory | 14 | 0 | 0% |
| Phase 8 — Billing | 16 | 0 | 0% |
| Phase 9 — Operations | 17 | 0 | 0% |
| Phase 10 — Reports | 12 | 0 | 0% |
| Phase 11 — SaaS Admin | 9 | 0 | 0% |
| Phase 12 — Patient Portal | 6 | 0 | 0% |
| Phase 13 — QA & Launch | 12 | 0 | 0% |
| Phase 14 — Documentation | 16 | 16 | 100% |
| **TOTAL** | **229** | **69** | **30%** |

---

*MedCore HMS Task List · Boni Yeamin · Laravel 12 + React 19 · Update status as tasks complete*
