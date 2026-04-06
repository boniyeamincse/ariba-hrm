# 🏥 MedCore HMS — Enterprise Hospital Management System

MedCore HMS is a cloud-native, multi-tenant SaaS application designed to consolidate the operational domains of modern healthcare facilities. Built with a Laravel 12 API backend and a scalable React 19 frontend, MedCore handles patient care, clinical workflows, pharmacy, laboratory, and billing as a unified platform.

## 📖 Complete Documentation

The complete documentation suite for MedCore HMS has been structured for international team enablement and ease of onboarding. For deep technical guidelines, product structures, and architecture, please start at the [Documentation Hub](docs/README.md).

**Quick Links:**
- [Development Setup](docs/development/SETUP.md)
- [Architecture Guidelines](docs/architecture/README.md)
- [API Standards](docs/api/README.md)
- [Brand & Product Guidelines](docs/product/README.md)

---

## 🔑 Test Credentials & Seed Data

For local development and testing, the database seeder automatically provisions standard test accounts for every system role. You can use these credentials to access role-specific portals and test workflows.

**Default Password for all accounts:** `password`

| Role | Email / Login ID | Portal / Access Level |
|---|---|---|
| **Super Admin** | `superadmin@medcore.com` | Global SaaS Operations & Tenant Management |
| **Tenant Admin (Hospital Admin)** | `admin@hospital.com` | Hospital Config & Full Data Access |
| **Hospital Manager** | `manager@hospital.com` | Hospital Management Dashboard |
| **Operations Manager** | `ops@hospital.com` | Operations Dashboard |
| **Doctor** | `doctor@hospital.com` | Clinical Portal & OPD/IPD Rounds |
| **Nurse** | `nurse@hospital.com` | Ward Management & Clinical Care |
| **Receptionist / Front Desk** | `reception@hospital.com` | Front Desk, OPD Queue & Appointments |
| **Pharmacist** | `pharmacist@hospital.com` | Pharmacy Dispensing & Stock |
| **Lab Technician** | `lab@hospital.com` | Laboratory Order Fulfillment & Results |
| **Accountant / Finance Manager** | `finance@hospital.com` | Billing, Invoices & Financial Reports |
| **Ward Manager** | `wardmanager@hospital.com` | IPD Ward & Bed Allocation |
| **Ambulance Driver / Transport Staff**| `transport@hospital.com` | Transport & Logistics Management |
| **IT Admin / System Administrator** | `itadmin@hospital.com` | Tenant-level System Configuration |
| **Inventory Manager / Store Manager** | `inventory@hospital.com` | Store, Assets, and Purchases |
| **HR Manager** | `hr@hospital.com` | Staff, Leave, Attendance & Payroll |
| **Patient (Portal User)** | `patient@example.com` (UHID: `PT-1001`) | Patient Self-Service Portal |
| **Insurance Agent / Partner** | `insurance@partner.com` | Review Claims & Insurance Billing |
| **Auditor / Compliance Officer** | `auditor@hospital.com` | Read-only System Audit |
| **Data Analyst** | `analyst@hospital.com` | Reports & Intelligence Analytics |
| **API Client / Integration Role** | `api@integration.local` | Third-party Integrations Sandbox |
| **AI Assistant Role** | `ai_agent@medcore.internal` | Background AI Processing Tasks |

---

## 🚀 Quick Start (Development)

1. Ensure Docker and Docker Compose are installed and running.
2. Clone the repository and configure `.env` based on `.env.example` in both `backend` and `frontend` folders.
3. Build and launch the containerized services using the provided Makefile:
   ```bash
   make dev
   ```
4. Run migrations and seed the data (this pushes the test accounts listed above into your database):
   ```bash
   make migrate
   make seed
   ```
5. The application will be accessible via:
   - Frontend SPA: `http://localhost:5173`
   - Backend API: `http://localhost:8000`
