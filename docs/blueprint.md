# 🏥 MedCore HMS — Hospital Management System
## ERP SaaS Software Blueprint

---

| Field | Details |
|---|---|
| **Version** | v1.0.0 |
| **Stack** | Laravel 12 + React 19 + MySQL 8 |
| **Architecture** | Multi-Tenant SaaS (Subdomain) |
| **Author** | Boni Yeamin |
| **Classification** | Internal Blueprint Document |

> Built with **Laravel 12 · React 19 · MySQL 8 · Redis · Docker · AWS**

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [System Architecture](#2-system-architecture)
3. [Module Overview](#3-module-overview)
4. [Detailed Module Specifications](#4-detailed-module-specifications)
5. [Database Schema Overview](#5-database-schema-overview)
6. [API Architecture](#6-api-architecture)
7. [Frontend Architecture](#7-frontend-architecture)
8. [Security Architecture](#8-security-architecture)
9. [Integration Layer](#9-integration-layer)
10. [Deployment & DevOps](#10-deployment--devops)
11. [Reports & Analytics](#11-reports--analytics)
12. [Development Roadmap](#12-development-roadmap)
13. [Project File Structure](#13-project-file-structure)
14. [SaaS Subscription Plans](#14-saas-subscription-plans)
15. [Glossary](#15-glossary)

---

## 1. Executive Summary

**MedCore HMS** is a cloud-native, multi-tenant Hospital Management System delivered as an ERP SaaS platform. It consolidates every operational domain of a modern healthcare facility — patient care, clinical workflows, pharmacy, laboratory, billing, HR, inventory, and analytics — into a single unified system accessible via web and mobile browsers.

Designed for hospitals, clinics, diagnostic centers, and healthcare groups across South Asia and beyond, MedCore HMS targets institutions seeking to digitize paper-based processes, reduce operational costs, improve regulatory compliance, and enhance patient experience.

| Attribute | Details |
|---|---|
| **Target Market** | Hospitals, Clinics, Diagnostic Centers, Healthcare Groups |
| **Deployment Model** | Multi-Tenant SaaS — each tenant on dedicated subdomain |
| **Revenue Model** | Monthly/Annual subscription tiers per tenant |
| **Technology Stack** | Laravel 12 API · React 19 SPA · MySQL 8 · Redis · Docker |
| **Infrastructure** | AWS (EC2, RDS, S3, CloudFront, SES, SNS) |
| **Estimated Modules** | 20+ core modules, 150+ sub-features |
| **API Standard** | RESTful JSON API + WebSocket (Pusher/Soketi) |
| **Security Standards** | HIPAA-aligned, OWASP Top-10, JWT + Sanctum auth |

---

## 2. System Architecture

### 2.1 High-Level Architecture

MedCore HMS follows a **modular monolith** architecture on the backend (Laravel 12) with a decoupled React 19 single-page application frontend. Each hospital tenant is isolated at the application layer using tenant-scoped middleware and separate MySQL schemas, while sharing the same application server pool.

| Layer | Technology | Purpose |
|---|---|---|
| **Presentation** | React 19 + TypeScript + Tailwind CSS | SPA frontend — doctor portal, nurse portal, admin dashboard, patient self-service |
| **API Gateway** | Laravel 12 + Sanctum | RESTful API, auth, rate limiting, tenant resolution |
| **Business Logic** | Laravel Service / Repository pattern | All domain logic — patient, billing, pharmacy, lab |
| **Queue / Jobs** | Laravel Horizon + Redis | Background jobs — PDF generation, email, HL7 sync |
| **Real-time** | Laravel Echo + Pusher/Soketi | Live OPD queue, ICU alerts, nurse call system |
| **Database** | MySQL 8 (per-tenant schema) | Transactional data, medical records |
| **Cache** | Redis | Session, queue, rate limits, report caching |
| **File Storage** | AWS S3 + CloudFront CDN | DICOM images, lab reports, patient documents |
| **Search** | Meilisearch | Patient lookup, ICD/drug search |
| **Notification** | AWS SES + SNS + FCM | Email, SMS, push notifications |

---

### 2.2 Multi-Tenancy Design

Each hospital tenant is onboarded via **subdomain resolution** (e.g., `cityhospital.medcorehms.com`). Tenant context is resolved at the HTTP middleware layer before any request reaches the application. Database schemas are isolated per tenant.

| Concern | Strategy |
|---|---|
| **Tenant Identification** | Subdomain parsing via `TenantMiddleware` on every request |
| **Data Isolation** | Separate MySQL schema per tenant; connection switched at runtime |
| **File Isolation** | S3 bucket prefix per tenant (`tenants/{id}/...`) |
| **Config Isolation** | Per-tenant settings in central DB (features, branding, timezone) |
| **Billing** | Central SaaS billing DB tracks subscriptions, usage, invoices |
| **Super Admin** | Separate admin panel at `admin.medcorehms.com` for platform ops |

---

### 2.3 Technology Stack

| Component | Technology | Version |
|---|---|---|
| Backend Framework | Laravel | 12.x |
| Frontend Framework | React + TypeScript | 19.x |
| CSS Framework | Tailwind CSS | 4.x |
| State Management | Zustand | 5.x |
| Primary Database | MySQL | 8.0 |
| Cache / Queue | Redis | 7.x |
| Queue Manager | Laravel Horizon | latest |
| Search Engine | Meilisearch | 1.x |
| WebSocket | Soketi (self-hosted) / Pusher | latest |
| Auth | Laravel Sanctum + JWT | latest |
| ORM | Eloquent + Repository Pattern | built-in |
| API Docs | Scramble / Scribe | latest |
| Testing | Pest PHP + Vitest | latest |
| CI/CD | GitHub Actions | latest |
| Containerization | Docker + Docker Compose | latest |
| Cloud | AWS (EC2, RDS, S3, SES) | — |

---

## 3. Module Overview

MedCore HMS is composed of **20 core modules** organized into clinical, operational, financial, and administrative domains.

| # | Module | Domain | Priority |
|---|---|---|---|
| 01 | Super Admin & Tenant Management | Platform | P0 — Critical |
| 02 | Authentication & Role Management | Platform | P0 — Critical |
| 03 | Patient Registration & Demographics | Clinical | P0 — Critical |
| 04 | OPD (Out-Patient Department) | Clinical | P0 — Critical |
| 05 | IPD (In-Patient Department) | Clinical | P0 — Critical |
| 06 | Emergency & Triage | Clinical | P0 — Critical |
| 07 | Doctor & Appointment Scheduling | Clinical | P1 — High |
| 08 | Pharmacy Management | Operational | P0 — Critical |
| 09 | Laboratory & Diagnostics | Operational | P0 — Critical |
| 10 | Radiology & DICOM | Operational | P1 — High |
| 11 | Operating Theatre (OT) Management | Clinical | P1 — High |
| 12 | Billing & Invoice Management | Financial | P0 — Critical |
| 13 | Insurance & TPA Management | Financial | P1 — High |
| 14 | Inventory & Procurement | Operational | P1 — High |
| 15 | HR & Payroll | HR | P1 — High |
| 16 | Nursing & Ward Management | Clinical | P1 — High |
| 17 | Blood Bank | Operational | P2 — Medium |
| 18 | Mortuary Management | Operational | P2 — Medium |
| 19 | Reports & Analytics Dashboard | Intelligence | P1 — High |
| 20 | Patient Self-Service Portal | Patient-Facing | P1 — High |

---

## 4. Detailed Module Specifications

### 4.1 Super Admin & Tenant Management

The central platform layer manages the entire SaaS lifecycle: hospital onboarding, subscription management, feature flags, usage analytics, and billing.

| Feature | Description |
|---|---|
| **Tenant Onboarding** | Wizard-based signup — hospital details, admin user, subdomain assignment, DB schema provisioning |
| **Subscription Plans** | Starter / Professional / Enterprise tiers with feature flags per plan |
| **Billing Integration** | Stripe / SSL Commerz integration for automated monthly/annual invoicing |
| **Usage Monitoring** | Track patients, beds, users, API calls per tenant for usage-based billing |
| **Feature Flags** | Toggle modules per tenant without code deployment (e.g., Blood Bank, Radiology) |
| **Platform Analytics** | MRR, churn, active tenants, module adoption dashboards for SaaS ops |
| **Tenant Suspension** | Automated suspension on payment failure; grace period + data retention policy |

---

### 4.2 Authentication & Role-Based Access Control

Security is enforced at every layer via Laravel Sanctum tokens, fine-grained RBAC policies, and audit logging.

| Feature | Description |
|---|---|
| **Authentication** | Email/password + OTP 2FA; JWT tokens for API; Sanctum SPA cookies for web |
| **User Roles** | Super Admin · Hospital Admin · Doctor · Nurse · Receptionist · Pharmacist · Lab Tech · Accountant · Patient |
| **Permission Matrix** | Spatie Laravel Permission — granular permissions per role (e.g., `billing.view`, `prescription.create`) |
| **Staff Management** | Create / edit / suspend staff accounts; assign departments and roles |
| **Audit Log** | Every create/update/delete logged with user, IP, timestamp, before/after values |
| **Session Management** | Active session listing; remote logout; device trust management |
| **Password Policy** | Complexity rules, expiry, history enforcement, bcrypt hashing |

---

### 4.3 Patient Registration & Demographics

Comprehensive patient master data management with UHID generation, biometric integration support, and complete visit history.

| Feature | Description |
|---|---|
| **Patient Registration** | Walk-in and pre-registered; UHID auto-generation; photo capture; NID/passport scan |
| **Demographics** | Full demographic profile: name, DOB, gender, blood group, nationality, contact, next of kin |
| **Medical History** | Allergies, chronic conditions, surgical history, family history, immunization records |
| **Visit History** | Complete timeline of OPD, IPD, emergency, pharmacy, lab visits linked to patient |
| **Patient Search** | Full-text search by name, UHID, phone, NID — powered by Meilisearch |
| **Duplicate Detection** | Auto-flag potential duplicates on registration based on name + DOB + phone |
| **Patient Portal Link** | Auto-create self-service portal credentials on registration |

---

### 4.4 OPD — Out-Patient Department

End-to-end outpatient workflow from queue management to digital prescription generation and follow-up scheduling.

| Feature | Description |
|---|---|
| **Token / Queue Management** | Digital token system with real-time queue display; priority queue for elderly/emergency |
| **Digital Triage** | Vital signs recording: BP, temp, pulse, SpO2, weight, height, BMI calculation |
| **Doctor Consultation** | SOAP note editor: Subjective, Objective, Assessment, Plan with ICD-10 code selection |
| **E-Prescription** | Drug database with dose/frequency/duration; auto drug-allergy check; printable PDF |
| **Investigation Orders** | Order lab tests and radiology from consultation screen; auto-route to Lab/Radiology |
| **Sick Leave Certificate** | Automated sick leave certificate PDF with doctor digital signature |
| **Referral Management** | Internal referral (to IPD/specialist) and external referral letter generation |
| **Follow-up Scheduling** | Book follow-up appointment directly from consultation with auto-reminder SMS/email |

---

### 4.5 IPD — In-Patient Department

Full inpatient lifecycle from admission through discharge, covering ward management, nursing care, medication administration, and discharge summaries.

| Feature | Description |
|---|---|
| **Bed Management** | Real-time bed availability matrix by ward/room/bed; bed category (general/semi-private/private/ICU) |
| **Admission Process** | Admission from OPD/Emergency/direct; admission form; admitting doctor assignment; guarantor details |
| **Ward Rounds** | Doctor round notes with timestamp; multi-doctor ward round tracking; handover notes |
| **Medication Admin Record** | MAR — nurse medication administration recording with time, dose, route, nurse signature |
| **Nursing Notes** | Shift-wise nursing assessment, care plan, intervention notes |
| **Diet Management** | Diet order entry; kitchen integration; special dietary requirements |
| **Transfer Management** | Ward-to-ward transfer; ICU escalation; transfer notes |
| **Discharge Process** | Discharge summary generation; pending bills clearance; discharge instructions; follow-up booking |
| **Death Summary** | Death documentation: cause, time, attending physician, ICD-10 cause of death |

---

### 4.6 Emergency & Triage

| Feature | Description |
|---|---|
| **Triage Assessment** | Manchester Triage System (MTS) color coding: Red / Orange / Yellow / Green / Blue |
| **Rapid Registration** | Minimal-field emergency registration; unknown patient (John Doe) support |
| **Resuscitation Log** | Real-time timestamped resuscitation event logging |
| **Emergency Bed Matrix** | Dedicated emergency bay/bed management separate from IPD bed pool |
| **MLC Documentation** | Medico-Legal Case documentation with police intimation workflow |
| **Ambulance Tracking** | Incoming ambulance pre-notification; ETA-based preparation alerts |

---

### 4.7 Doctor & Appointment Scheduling

| Feature | Description |
|---|---|
| **Doctor Schedule** | Weekly schedule configuration per doctor; leave/off-day blocking |
| **Slot Management** | Configurable slot duration; max patients per slot; overbooking threshold |
| **Online Booking** | Patient portal appointment booking with real-time slot availability |
| **Reminder System** | Automated SMS/email/push reminders at 24hr and 2hr before appointment |
| **No-Show Tracking** | No-show flagging; auto-waitlist promotion when slot opens |
| **Telemedicine** | Video consultation link generation (Jitsi/Agora integration); teleconsult billing |

---

### 4.8 Pharmacy Management

Integrated pharmacy with real-time inventory, prescription fulfillment, drug interaction checking, and comprehensive drug master database.

| Feature | Description |
|---|---|
| **Drug Master** | Generic + brand drug database; categories; dosage forms; strength; manufacturer |
| **Stock Management** | Batch-wise stock with expiry tracking; FIFO/FEFO dispensing; low stock alerts |
| **Prescription Filling** | Scan UHID/prescription QR; auto-pull prescribed drugs; partial dispensing support |
| **Drug Interaction** | Real-time drug-drug and drug-allergy interaction alerts at point of dispensing |
| **Counter Sales** | OTC drug sales without prescription; retail billing |
| **Purchase Orders** | PO generation to suppliers; GRN (Goods Receipt Note); invoice matching |
| **Expiry Management** | Expiry dashboard; near-expiry alerts; quarantine and return workflows |
| **Pharmacy Reports** | Dispensing reports, consumption reports, stock valuation, slow-moving items |

---

### 4.9 Laboratory & Diagnostics

Complete laboratory information system covering sample collection, test processing, result entry, validation, and QR-verifiable report delivery.

| Feature | Description |
|---|---|
| **Test Master** | Test catalog with parameters, reference ranges (age/gender/unit-specific), critical values |
| **Sample Collection** | Barcode-labeled sample collection; specimen type; collection time; collector identity |
| **Workload Assignment** | Auto-route samples to analyzer/workstation by test type |
| **Result Entry** | Manual and HL7/ASTM analyzer interface result import; unit conversion |
| **Critical Alert** | Auto-alert doctor and nurse when results cross critical values via SMS/push |
| **Result Validation** | Pathologist review and digital signature before report release |
| **Report Generation** | QR-code verifiable PDF lab reports; online patient portal delivery |
| **Culture & Sensitivity** | Microbiology culture results with antibiogram table |
| **Panel Tests** | Test panels (CBC, LFT, KFT, Lipid Profile) with consolidated billing |

---

### 4.10 Billing & Invoice Management

Comprehensive revenue cycle management covering service charging, package billing, credit management, discount approval workflows, and financial reconciliation.

| Feature | Description |
|---|---|
| **Service Charge Master** | Configurable charge master: OPD/IPD/lab/radiology/procedure/bed charges per category |
| **Automated Billing** | Auto-accumulate charges from all modules (pharmacy, lab, procedures) to patient account |
| **Invoice Generation** | Itemized invoice with GST/VAT; corporate/insurance billing; advance payment tracking |
| **Discount Approval** | Discount request workflow with configurable approval tiers by percentage or amount |
| **Package Billing** | Pre-defined surgical/delivery/health check packages with fixed pricing |
| **Credit / Due Bills** | Corporate credit billing; due tracking; credit limit alerts |
| **Refund Management** | Partial/full refund with reason logging and approval workflow |
| **Payment Methods** | Cash, card, mobile banking (bKash/Nagad), insurance, corporate credit |
| **Daily Collection** | Shift-wise cashier collection summary; reconciliation with payment gateway |

---

### 4.11 HR & Payroll

| Feature | Description |
|---|---|
| **Staff Profiles** | Complete employee records: personal, educational, contract details, documents |
| **Attendance** | Biometric/manual attendance; shift management; overtime tracking |
| **Leave Management** | Leave types configuration; application/approval workflow; leave balance tracking |
| **Payroll Engine** | Gross/net salary computation; deductions (tax, provident fund, loan); salary slip PDF |
| **Loan Management** | Staff loan disbursement and installment deduction from payroll |
| **Performance Appraisal** | Annual KPI-based appraisal workflow with increment tracking |

---

### 4.12 Inventory & Procurement

| Feature | Description |
|---|---|
| **Item Master** | Categorized non-drug inventory: equipment, consumables, linen, stationery |
| **Indent / Requisition** | Department-wise indent requests; approval workflow before PO generation |
| **Purchase Orders** | Vendor-wise PO with delivery timeline; multi-item PO support |
| **GRN** | Goods receipt note with quality check; partial delivery handling |
| **Issue Management** | Department-wise item issue; consumption tracking |
| **AMC Tracking** | Annual Maintenance Contract tracking for medical equipment |

---

## 5. Database Schema Overview

### 5.1 Central Platform Database (`medcorehms_central`)

| Table | Key Columns | Purpose |
|---|---|---|
| `tenants` | id, name, subdomain, plan_id, status, db_name, created_at | Registered hospital tenants |
| `plans` | id, name, price_monthly, price_annual, features_json | Subscription plan definitions |
| `subscriptions` | id, tenant_id, plan_id, status, starts_at, ends_at, billing_cycle | Active subscriptions per tenant |
| `invoices` | id, tenant_id, amount, status, due_date, paid_at, gateway_ref | SaaS billing invoices |
| `admin_users` | id, name, email, password, role, 2fa_secret | Platform super admin users |

---

### 5.2 Tenant Database Core Tables

| Table | Key Columns | Purpose |
|---|---|---|
| `patients` | id, uhid, name, dob, gender, blood_group, phone, nid, photo_path | Patient master registry |
| `visits` | id, patient_id, visit_type, visit_date, department_id, doctor_id | All patient visits (OPD/IPD/Emergency) |
| `admissions` | id, patient_id, visit_id, bed_id, admitted_at, discharged_at, status | IPD admissions |
| `beds` | id, ward_id, room_no, bed_no, category, status | Bed inventory and status |
| `doctors` | id, user_id, specialty_id, department_id, reg_no, fee | Doctor profiles |
| `appointments` | id, patient_id, doctor_id, slot_id, status, notes | OPD appointment slots |
| `prescriptions` | id, visit_id, doctor_id, notes, created_at | Prescription header |
| `prescription_items` | id, prescription_id, drug_id, dose, frequency, duration, route | Prescribed drugs |
| `drug_master` | id, generic_name, brand_name, category, form, strength | Drug catalog |
| `pharmacy_stock` | id, drug_id, batch_no, expiry, qty, mrp, supplier_id | Pharmacy inventory |
| `lab_orders` | id, visit_id, test_id, ordered_by, status, priority | Lab test orders |
| `lab_results` | id, order_id, parameter_id, result_value, unit, is_critical | Test results |
| `bills` | id, patient_id, visit_id, total, discount, paid, balance, status | Billing header |
| `bill_items` | id, bill_id, service_id, qty, rate, amount, source_module | Itemized charges |
| `staff` | id, user_id, department_id, designation, join_date, salary | Staff/HR records |
| `inventory_items` | id, category_id, name, unit, reorder_level, current_stock | General inventory |

---

### 5.3 Key Relationships (ERD Summary)

```
tenants (1) ──────────── (∞) users
patients (1) ──────────── (∞) visits
visits (1) ──────────── (1) admissions
visits (1) ──────────── (∞) prescriptions
prescriptions (1) ──── (∞) prescription_items
visits (1) ──────────── (∞) lab_orders
lab_orders (1) ──────── (∞) lab_results
visits (1) ──────────── (1) bills
bills (1) ────────────── (∞) bill_items
beds (1) ──────────────── (∞) admissions
drug_master (1) ─────── (∞) pharmacy_stock
drug_master (1) ─────── (∞) prescription_items
```

---

## 6. API Architecture

### 6.1 API Design Principles

- RESTful JSON API with consistent response envelope: `{ status, data, message, meta }`
- All routes prefixed: `/api/v1/` — versioned for backward compatibility
- Tenant context resolved via `TenantMiddleware` on all tenant routes
- Rate limiting: 60 req/min (public), 300 req/min (authenticated), 1000 req/min (API key)
- Cursor-based pagination on all list endpoints
- Global exception handler returns structured error codes

### 6.2 Standard Response Format

```json
{
  "status": "success",
  "data": { ... },
  "message": "Patient retrieved successfully",
  "meta": {
    "pagination": {
      "cursor": "eyJpZCI6MTAwfQ==",
      "per_page": 20,
      "has_more": true
    }
  }
}
```

### 6.3 Core API Route Groups

| Route Group | Base Path | Auth | Key Endpoints |
|---|---|---|---|
| Auth | `/api/v1/auth` | Public | login, register, refresh, logout, 2fa/verify |
| Patients | `/api/v1/patients` | Sanctum | CRUD, search, visit-history, merge-duplicates |
| Appointments | `/api/v1/appointments` | Sanctum | slots, book, cancel, reschedule, today-schedule |
| OPD | `/api/v1/opd` | Sanctum | queue, vitals, consultation, prescription, orders |
| IPD | `/api/v1/ipd` | Sanctum | admit, bed-assign, rounds, nursing-notes, discharge |
| Pharmacy | `/api/v1/pharmacy` | Sanctum | dispense, stock, PO, GRN, expiry-alerts |
| Laboratory | `/api/v1/lab` | Sanctum | order, collect, result-entry, validate, report |
| Billing | `/api/v1/billing` | Sanctum | invoice, payment, discount-request, refund, reports |
| HR | `/api/v1/hr` | Sanctum | staff, attendance, leave, payroll, payslip |
| Inventory | `/api/v1/inventory` | Sanctum | items, PO, GRN, issues, stock-valuation |
| Reports | `/api/v1/reports` | Sanctum | census, revenue, MIS, custom-builder, export |
| Admin | `/api/v1/admin` | Sanctum+Admin | settings, users, roles, audit-log, modules |
| Super Admin | `/api/v1/super` | SuperAdmin | tenants, plans, subscriptions, platform-stats |

### 6.4 Sample API Endpoints

```
# Patient
GET    /api/v1/patients?search=john&limit=20
POST   /api/v1/patients
GET    /api/v1/patients/{uhid}
PUT    /api/v1/patients/{id}
GET    /api/v1/patients/{id}/visits

# OPD
GET    /api/v1/opd/queue?date=today&doctor_id=5
POST   /api/v1/opd/token
POST   /api/v1/opd/{visit_id}/vitals
POST   /api/v1/opd/{visit_id}/consultation
POST   /api/v1/opd/{visit_id}/prescription

# Billing
GET    /api/v1/billing/{patient_id}/current
POST   /api/v1/billing/{bill_id}/payment
POST   /api/v1/billing/{bill_id}/discount-request
GET    /api/v1/billing/reports/daily-collection
```

---

## 7. Frontend Architecture

### 7.1 Application Portals

| Portal | Users | Key Screens |
|---|---|---|
| **Super Admin Dashboard** | Platform admins | Tenant management, subscriptions, platform analytics, billing |
| **Hospital Admin Panel** | Hospital admin, managers | Dashboard, staff, config, reports, audit log, module settings |
| **Doctor Portal** | Doctors | Schedule, OPD queue, consultation editor, prescription, IPD rounds |
| **Nurse Dashboard** | Nurses, ward staff | Bed matrix, MAR, nursing notes, vitals chart, alerts |
| **Receptionist Desk** | Front desk staff | Patient registration, appointments, token management, billing |
| **Pharmacy Counter** | Pharmacists | Prescription queue, dispensing, stock, purchase orders |
| **Lab Portal** | Lab technicians | Sample queue, result entry, report generation, analyzer interface |
| **Accounts Module** | Accountants | Daily collection, invoices, insurance claims, financial reports |
| **Patient Portal** | Patients | Book appointments, view reports, prescriptions, bills, health timeline |

---

### 7.2 Frontend Technology Decisions

| Concern | Choice | Reason |
|---|---|---|
| Framework | React 19 + TypeScript | Strong ecosystem, concurrent rendering, type safety |
| Styling | Tailwind CSS 4 | Rapid UI development, consistent design tokens |
| State | Zustand + React Query | Simple global state; server state with caching, invalidation |
| Routing | React Router v7 | Code splitting, nested layouts, loader pattern |
| Forms | React Hook Form + Zod | Performance, validation, TypeScript schema integration |
| Charts | Recharts + ApexCharts | Hospital analytics dashboards, real-time vital charts |
| Tables | TanStack Table v8 | Virtualized, sortable, filterable clinical data grids |
| Real-time | Laravel Echo + Pusher | Live queue updates, critical lab alerts, bed status |
| PDF | React-PDF / jsPDF | Client-side lab/prescription/discharge PDF generation |
| i18n | i18next | English + Bangla + Arabic support |

---

### 7.3 Folder Structure (React Frontend)

```
src/
├── portals/
│   ├── admin/           # Hospital admin portal
│   ├── doctor/          # Doctor portal — schedule, consultation, prescription
│   ├── nurse/           # Nurse dashboard — bed matrix, MAR
│   ├── pharmacy/        # Pharmacy counter portal
│   ├── lab/             # Laboratory portal
│   ├── accounts/        # Accounts & billing portal
│   └── patient/         # Patient self-service portal
├── components/
│   └── ui/              # Shared UI: Button, Modal, Table, Form, Badge, Chart
├── hooks/               # usePatientSearch, useBedMatrix, useRealtimeQueue
├── stores/              # Zustand: authStore, tenantStore, notificationStore
├── api/                 # Axios client organized by module
├── types/               # TypeScript domain model definitions
└── utils/               # Formatters, validators, constants
```

---

## 8. Security Architecture

MedCore HMS is designed to meet **HIPAA-aligned** security standards. Medical data is classified as Protected Health Information (PHI) and handled with the highest levels of access control, encryption, and audit capability.

| Security Domain | Controls Implemented |
|---|---|
| **Authentication** | Bcrypt password hashing; JWT + Sanctum tokens; OTP-based 2FA; brute-force lockout after 5 failed attempts |
| **Authorization** | RBAC with Spatie Permission; policy-level object ownership checks; tenant boundary enforcement in all queries |
| **Transport Security** | TLS 1.3 enforced; HSTS headers; HTTPS-only cookies; SSL certificate auto-renewal via Let's Encrypt |
| **Data Encryption** | AES-256 encryption for PHI fields at rest (NID, diagnoses); encrypted S3 storage for documents/images |
| **SQL Injection** | Eloquent ORM parameterized queries exclusively; no raw SQL with user input; WAF at infrastructure layer |
| **XSS / CSRF** | React DOM escaping; Laravel CSRF tokens; Content-Security-Policy headers; X-Frame-Options: DENY |
| **Rate Limiting** | API rate limits per IP and per user token; DDoS protection at AWS CloudFront/Shield layer |
| **Audit Logging** | Immutable audit log for all PHI access/modification; logged to append-only table + S3 archive |
| **Vulnerability Mgmt** | Automated Dependabot + Snyk scanning; quarterly penetration testing; OWASP Top-10 checklist |
| **Backup & Recovery** | Automated daily DB snapshots; point-in-time recovery; cross-region S3 replication; RPO < 1hr, RTO < 4hr |

---

## 9. Integration Layer

| Integration | Protocol | Purpose |
|---|---|---|
| **Laboratory Analyzers** | HL7 v2 / ASTM | Auto-import results from hematology, biochemistry, immunology analyzers |
| **DICOM / PACS** | DICOM 3.0 | Radiology image transmission; link reports to patient record |
| **Payment Gateways** | REST API | bKash, Nagad, Stripe, SSL Commerz for patient/corporate billing |
| **SMS Gateway** | REST API | OTP, appointment reminders, lab result ready, discharge alerts |
| **Email** | AWS SES | Transactional emails, report delivery, billing statements |
| **Push Notifications** | FCM | Mobile push for patient portal app: appointments, reports ready |
| **Insurance / TPA** | REST / HL7 FHIR | Pre-authorization, claim submission, adjudication status |
| **Government DHIS2** | REST API | Disease surveillance data reporting to national health authority |
| **Biometric Devices** | SDK / USB HID | Fingerprint/iris patient identification at registration desk |
| **Accounting ERP** | REST API | Export financial data to Tally/SAP/QuickBooks for accounting teams |

---

## 10. Deployment & DevOps

### 10.1 AWS Infrastructure

| Service | AWS Component | Configuration |
|---|---|---|
| **Application Servers** | EC2 Auto Scaling Group | t3.medium baseline; scale out on CPU > 70%; ALB load balanced |
| **Database** | RDS MySQL 8.0 Multi-AZ | db.r6g.large; automated backups; read replicas for reporting |
| **Cache / Queue** | ElastiCache Redis | r6g.large cluster; sessions, queues, Horizon dashboard |
| **File Storage** | S3 + CloudFront | Standard storage for documents; CDN for static assets and lab PDFs |
| **Search** | EC2 (Meilisearch) | t3.small dedicated; patient and drug search indexes |
| **Email** | SES | Transactional email with DKIM/SPF; bounce/complaint handling |
| **DNS** | Route 53 | Wildcard `*.medcorehms.com` CNAME to ALB for tenant subdomains |
| **SSL** | ACM Wildcard Cert | Auto-renewed wildcard certificate for `*.medcorehms.com` |
| **Monitoring** | CloudWatch + Sentry | Infra metrics, app error tracking, uptime alerts, log aggregation |

---

### 10.2 Docker Compose (Development)

```yaml
services:
  nginx:        # Port 8080 — Reverse proxy to PHP-FPM
  app:          # Port 9000 — PHP 8.3 FPM, Laravel application
  mysql:        # Port 3306 — MySQL 8.0 with seed scripts
  redis:        # Port 6379 — Session, queue, cache
  meilisearch:  # Port 7700 — Full-text patient/drug search
  soketi:       # Port 6001 — Self-hosted WebSocket (Pusher-compatible)
  horizon:      # Daemon  — Laravel Horizon queue worker + dashboard
```

---

### 10.3 CI/CD Pipeline (GitHub Actions)

```
1. Developer pushes feature branch
       ↓
2. GitHub Actions triggers:
   - Pest PHP unit + feature tests
   - ESLint + TypeScript type check
   - Security audit (npm audit + composer audit)
       ↓
3. Build Docker image → push to AWS ECR (tag: git-sha)
       ↓
4. Deploy to STAGING → run smoke tests + Dusk browser tests
       ↓
5. PR review → merge to main
       ↓
6. Blue-green deploy to PRODUCTION via AWS CodeDeploy
       ↓
7. Slack notification: deployment success/failure + deploy URL
```

---

### 10.4 Laravel Backend Folder Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/     # API controllers by module
│   ├── Middleware/             # TenantMiddleware, RoleMiddleware, AuditLogMiddleware
│   └── Requests/               # Form request validation classes
├── Services/                   # Business logic (PatientService, BillingService...)
├── Repositories/               # Data access layer per entity
├── Models/                     # Eloquent models per DB table
├── Jobs/                       # Background: PDF, HL7 import, notifications
├── Events/ & Listeners/        # Real-time: LabResultReady, BedStatusChanged
├── Policies/                   # Authorization policies per model
└── Notifications/              # Mail, SMS, push notification classes

database/
├── migrations/                 # Schema migrations by module
└── seeders/                    # Demo data for dev/UAT

routes/
└── api.php                     # API routes with middleware groups

config/
└── tenancy.php                 # Multi-tenancy configuration
```

---

## 11. Reports & Analytics

| Report Category | Reports Included |
|---|---|
| **Patient Statistics** | Daily/monthly OPD census, IPD census, bed occupancy rate, ALOS |
| **Revenue Reports** | Daily collection, revenue by department, revenue by doctor, outstanding dues, insurance claims |
| **Pharmacy Reports** | Dispensing report, drug consumption, stock valuation, near-expiry, purchase vs consumption |
| **Lab Reports** | Test-wise volume, TAT analysis, critical value reports, analyzer performance |
| **HR Reports** | Attendance summary, leave balance, payroll register, headcount by department |
| **Clinical Reports** | Diagnosis frequency (ICD-10), procedure stats, doctor-wise patient load, readmission rate |
| **Financial Reports** | P&L by department, accounts receivable aging, insurance recovery rate, discount analysis |
| **Operational KPIs** | Executive dashboard: revenue, patient load, bed occupancy, pharmacy inventory value, open bills |

### Export Formats

- PDF (formatted, printable)
- Excel (.xlsx)
- CSV (raw data export)
- API JSON (for BI tool integration)

---

## 12. Development Roadmap

| Phase | Duration | Deliverables | Status |
|---|---|---|---|
| **Phase 0 — Foundation** | Weeks 1–3 | Project setup, Docker, CI/CD, DB migrations, auth, tenant middleware, RBAC | Planned |
| **Phase 1 — Core Clinical** | Weeks 4–10 | Patient registration, OPD queue, consultation, prescription, appointments | Planned |
| **Phase 2 — IPD** | Weeks 11–15 | IPD admission, bed matrix, ward rounds, nursing notes, MAR, discharge | Planned |
| **Phase 3 — Ancillary** | Weeks 16–20 | Pharmacy, Laboratory, Radiology (basic), Emergency module | Planned |
| **Phase 4 — Finance** | Weeks 21–24 | Billing engine, payment gateway, insurance/TPA, discount workflows | Planned |
| **Phase 5 — Operations** | Weeks 25–28 | Inventory, procurement, HR, payroll, attendance | Planned |
| **Phase 6 — Intelligence** | Weeks 29–31 | Analytics dashboard, MIS reports, custom report builder, data export | Planned |
| **Phase 7 — SaaS Layer** | Weeks 32–34 | Super admin, subscription billing, tenant onboarding wizard, feature flags | Planned |
| **Phase 8 — Patient Portal** | Weeks 35–36 | Patient self-service portal, appointment booking, report download, bill payment | Planned |
| **Phase 9 — QA & Launch** | Weeks 37–40 | UAT, performance testing, security audit, production deployment, go-live | Planned |

**Total Estimated Timeline: 40 weeks (~10 months)**

---

## 13. Project File Structure

### 13.1 Full Laravel Backend

```
medcore-hms-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Auth/
│   │   │           ├── Patient/
│   │   │           ├── OPD/
│   │   │           ├── IPD/
│   │   │           ├── Emergency/
│   │   │           ├── Pharmacy/
│   │   │           ├── Laboratory/
│   │   │           ├── Radiology/
│   │   │           ├── Billing/
│   │   │           ├── Inventory/
│   │   │           ├── HR/
│   │   │           ├── Reports/
│   │   │           └── Admin/
│   │   ├── Middleware/
│   │   │   ├── TenantMiddleware.php
│   │   │   ├── AuditLogMiddleware.php
│   │   │   └── CheckSubscription.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── Central/          # Tenant, Plan, Subscription
│   │   └── Tenant/           # Patient, Visit, Admission, Bill...
│   ├── Services/
│   ├── Repositories/
│   ├── Jobs/
│   ├── Events/
│   ├── Listeners/
│   ├── Policies/
│   └── Notifications/
├── database/
│   ├── migrations/
│   │   ├── central/
│   │   └── tenant/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── channels.php
├── config/
│   ├── tenancy.php
│   └── hms.php
├── tests/
│   ├── Unit/
│   └── Feature/
├── docker/
├── .github/workflows/
├── docker-compose.yml
├── Makefile
└── README.md
```

### 13.2 Full React Frontend

```
medcore-hms-web/
├── src/
│   ├── portals/
│   │   ├── superadmin/
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Tenants/
│   │   │   └── Subscriptions/
│   │   ├── admin/
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Staff/
│   │   │   ├── Reports/
│   │   │   └── Settings/
│   │   ├── doctor/
│   │   │   ├── Schedule.tsx
│   │   │   ├── OPDQueue.tsx
│   │   │   ├── Consultation.tsx
│   │   │   ├── Prescription.tsx
│   │   │   └── IPDRounds.tsx
│   │   ├── nurse/
│   │   │   ├── BedMatrix.tsx
│   │   │   ├── MAR.tsx
│   │   │   └── NursingNotes.tsx
│   │   ├── pharmacy/
│   │   │   ├── DispenseQueue.tsx
│   │   │   ├── Stock.tsx
│   │   │   └── PurchaseOrders.tsx
│   │   ├── lab/
│   │   │   ├── SampleQueue.tsx
│   │   │   ├── ResultEntry.tsx
│   │   │   └── Reports.tsx
│   │   └── patient/
│   │       ├── Dashboard.tsx
│   │       ├── BookAppointment.tsx
│   │       ├── MyReports.tsx
│   │       └── MyBills.tsx
│   ├── components/
│   │   └── ui/
│   │       ├── Button.tsx
│   │       ├── Modal.tsx
│   │       ├── DataTable.tsx
│   │       ├── Badge.tsx
│   │       ├── Chart.tsx
│   │       └── FormField.tsx
│   ├── hooks/
│   ├── stores/
│   ├── api/
│   ├── types/
│   └── utils/
├── public/
├── vite.config.ts
├── tailwind.config.ts
├── tsconfig.json
└── package.json
```

---

## 14. SaaS Subscription Plans

| Feature | Starter | Professional | Enterprise |
|---|---|---|---|
| **Monthly Price (USD)** | $49/mo | $149/mo | Custom |
| **Patient Records** | Up to 5,000 | Up to 50,000 | Unlimited |
| **Staff Users** | Up to 15 | Up to 100 | Unlimited |
| **Active Beds** | Up to 30 | Up to 200 | Unlimited |
| OPD Module | ✓ | ✓ | ✓ |
| IPD Module | ✗ | ✓ | ✓ |
| Pharmacy Module | ✓ | ✓ | ✓ |
| Laboratory Module | ✓ | ✓ | ✓ |
| Radiology Module | ✗ | ✓ | ✓ |
| Insurance/TPA Module | ✗ | ✓ | ✓ |
| HR & Payroll | ✗ | ✓ | ✓ |
| Custom Report Builder | ✗ | ✗ | ✓ |
| External API Access | ✗ | Read-only | Full |
| SLA Uptime | 99% | 99.5% | 99.9% |
| Support | Email | Email + Chat | Dedicated CSM |
| Onboarding Assistance | Self-service | Assisted | White-glove |
| Custom Domain | ✗ | ✓ | ✓ |
| Data Export | CSV | CSV + Excel | All formats + API |

---

## 15. Glossary

| Term | Definition |
|---|---|
| **UHID** | Unique Hospital Identification Number — a system-generated unique patient ID |
| **OPD** | Out-Patient Department — patients who visit without being admitted |
| **IPD** | In-Patient Department — patients admitted to hospital beds |
| **MAR** | Medication Administration Record — nurse's record of drug administration to inpatients |
| **ALOS** | Average Length of Stay — average days an inpatient stays per admission |
| **DICOM** | Digital Imaging and Communications in Medicine — standard for radiology images |
| **HL7** | Health Level Seven — international standard for healthcare data exchange |
| **FHIR** | Fast Healthcare Interoperability Resources — modern HL7 standard for APIs |
| **TPA** | Third Party Administrator — company managing health insurance claims |
| **ICD-10** | International Classification of Diseases, 10th Revision — diagnosis coding standard |
| **GRN** | Goods Receipt Note — document confirming receipt of ordered supplies |
| **TAT** | Turnaround Time — time from sample collection to lab result delivery |
| **PHI** | Protected Health Information — patient data protected under HIPAA-aligned rules |
| **SaaS** | Software as a Service — cloud-hosted subscription software model |
| **ERP** | Enterprise Resource Planning — integrated system managing all business operations |
| **RBAC** | Role-Based Access Control — permissions assigned based on user roles |
| **JWT** | JSON Web Token — secure token format used for API authentication |
| **FIFO** | First In, First Out — stock dispensing method by earliest batch |
| **FEFO** | First Expiry, First Out — stock dispensing method by nearest expiry date |
| **MTS** | Manchester Triage System — 5-level color-coded triage severity scale |
| **MLC** | Medico-Legal Case — cases requiring police notification by law |
| **SOC** | Security Operations Center — team monitoring security events |

---

*MedCore HMS — Hospital Management System ERP SaaS Blueprint*
*Confidential · Internal Document · Author: Boni Yeamin · Laravel 12 + React 19 + MySQL 8*
