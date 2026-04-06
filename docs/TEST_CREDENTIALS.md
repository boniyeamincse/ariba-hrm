# Test Credentials and Seed Data

For local development and testing, the database seeder provisions standard accounts for all system roles.

## Default Password

All seeded accounts use:

- `password`

## Seeded Accounts

| Role | Email / Login ID | Portal / Access Level |
| --- | --- | --- |
| Super Admin | superadmin@medcore.com | Global SaaS Operations and Tenant Management |
| Tenant Admin (Hospital Admin) | admin@hospital.com | Hospital Configuration and Full Data Access |
| Hospital Manager | manager@hospital.com | Hospital Management Dashboard |
| Operations Manager | ops@hospital.com | Operations Dashboard |
| Doctor | doctor@hospital.com | Clinical Portal and OPD/IPD Rounds |
| Nurse | nurse@hospital.com | Ward Management and Clinical Care |
| Receptionist / Front Desk | reception@hospital.com | Front Desk, OPD Queue, and Appointments |
| Pharmacist | pharmacist@hospital.com | Pharmacy Dispensing and Stock |
| Lab Technician | lab@hospital.com | Laboratory Order Fulfillment and Results |
| Accountant / Finance Manager | finance@hospital.com | Billing, Invoices, and Financial Reports |
| Ward Manager | wardmanager@hospital.com | IPD Ward and Bed Allocation |
| Ambulance Driver / Transport Staff | transport@hospital.com | Transport and Logistics Management |
| IT Admin / System Administrator | itadmin@hospital.com | Tenant-level System Configuration |
| Inventory Manager / Store Manager | inventory@hospital.com | Store, Assets, and Purchases |
| HR Manager | hr@hospital.com | Staff, Leave, Attendance, and Payroll |
| Patient (Portal User) | patient@example.com (UHID: PT-1001) | Patient Self-Service Portal |
| Insurance Agent / Partner | insurance@partner.com | Review Claims and Insurance Billing |
| Auditor / Compliance Officer | auditor@hospital.com | Read-only System Audit |
| Data Analyst | analyst@hospital.com | Reports and Intelligence Analytics |
| API Client / Integration Role | api@integration.local | Third-party Integrations Sandbox |
| AI Assistant Role | ai_agent@medcore.internal | Background AI Processing Tasks |

## Run Seeder

```bash
php artisan db:seed
```

If you need to reset and reseed from scratch:

```bash
php artisan migrate:fresh --seed
```
