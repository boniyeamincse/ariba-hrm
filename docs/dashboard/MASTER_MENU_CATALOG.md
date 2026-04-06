# Master Dashboard Menu Catalog

## Goal

Provide a complete, role-aware dashboard menu and submenu blueprint for all modules.

This catalog is aligned with:
- `backend/database/seeders/MenuSeeder.php`
- `docs/dashboard/PERMISSION_MENU_MATRIX.md`

## Global Menu Tree

1. Dashboard
- Overview
- My Tasks

2. Users
- User List
- Create User
- Roles & Permissions

3. Patients
- Patient List
- Register Patient
- Medical History
- Visit Timeline

4. Appointments
- Schedule
- Book Appointment
- Queue Board

5. Billing
- Invoices
- Payments
- Discount Approvals

6. Inventory
- Stock Levels
- Purchase Orders
- Suppliers

7. Pharmacy
- Prescription Queue
- Dispense
- Stock Alerts

8. Laboratory
- Pending Tests
- Result Entry
- Sample Tracking

9. HRM
- Employees
- Attendance
- Leave Requests
- Payroll

10. Reports
- Summary
- Export Center

11. Operations
- Ward Management
- Ambulance Dispatch

12. Integrations
- API Keys
- Webhooks

13. AI Assistant
- Assistant Console
- Escalations

14. Security & Audit
- Audit Trails
- Access Logs

15. Settings
- Profile Settings
- Session Management

## Role Visibility Summary

- Super Admin: all menus
- Tenant Admin: all operational menus except AI/Integration if permission not granted
- Hospital Admin / Manager: dashboard, users, patients, appointments, billing, reports
- Operations Manager: dashboard, patients, appointments, inventory, operations, reports
- Doctor: dashboard, patients, appointments, reports
- Nurse: dashboard, patients, appointments
- Receptionist: dashboard, patients, appointments, billing
- Pharmacist: dashboard, pharmacy, patients, billing
- Lab Technician: dashboard, laboratory, patients
- Accountant: dashboard, billing, reports
- Ward Manager: dashboard, patients, appointments, operations, reports
- Ambulance Driver: dashboard, operations
- IT Admin: dashboard, users, security & audit, integrations, reports
- Inventory Manager: dashboard, inventory, reports
- HR Manager: dashboard, users, HRM, reports
- Patient: dashboard, appointments, reports, billing, settings
- Insurance Agent: dashboard, patients, billing, reports
- Auditor: dashboard, security & audit, reports
- Data Analyst: dashboard, reports
- API Client: integrations only (or API scope only)
- AI Assistant: dashboard, AI Assistant, limited patient/appointment support panels

## Notes

- Final role visibility is controlled by backend permission middleware.
- Hidden menu in frontend does not replace backend authorization.
- Every submenu action must map to a permission key and audited endpoint where sensitive.
