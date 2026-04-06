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

11. OPD
- Queue Dashboard
- Consultations
- Prescriptions
- Investigations

12. IPD
- Admissions
- Bed Matrix
- Ward Rounds
- Discharge

13. Emergency
- Triage Board
- Rapid Registration
- Resuscitation Log

14. Insurance
- Providers
- Policies
- Claims

15. Blood Bank
- Blood Stock
- Donations
- Transfusions

16. Mortuary
- Records
- Release Requests

17. SaaS Admin
- Tenant Management
- Plans & Billing
- Platform Analytics

18. Operations
- Ward Management
- Ambulance Dispatch

19. Integrations
- API Keys
- Webhooks

20. AI Assistant
- Assistant Console
- Escalations

21. Security & Audit
- Audit Trails
- Access Logs

22. Settings
- Profile Settings
- Session Management

## Role Visibility Summary

- Super Admin: all menus including SaaS Admin
- Tenant Admin: all operational menus except AI/Integration if permission not granted
- Hospital Admin / Manager: dashboard, users, patients, appointments, OPD, IPD, billing, reports
- Operations Manager: dashboard, patients, appointments, OPD, IPD, emergency, inventory, operations, reports
- Doctor: dashboard, patients, appointments, OPD, reports
- Nurse: dashboard, patients, appointments
- Receptionist: dashboard, patients, appointments, billing
- Pharmacist: dashboard, pharmacy, patients, billing
- Lab Technician: dashboard, laboratory, patients
- Accountant: dashboard, billing, insurance, reports
- Ward Manager: dashboard, patients, appointments, IPD, operations, reports
- Ambulance Driver: dashboard, emergency, operations
- IT Admin: dashboard, users, security & audit, integrations, reports
- Inventory Manager: dashboard, inventory, reports
- HR Manager: dashboard, users, HRM, reports
- Patient: dashboard, appointments, reports, billing, settings
- Insurance Agent: dashboard, insurance, patients, billing, reports
- Auditor: dashboard, security & audit, reports
- Data Analyst: dashboard, reports
- API Client: integrations only (or API scope only)
- AI Assistant: dashboard, AI Assistant, limited patient/appointment support panels

## Notes

- Final role visibility is controlled by backend permission middleware.
- Hidden menu in frontend does not replace backend authorization.
- Every submenu action must map to a permission key and audited endpoint where sensitive.
