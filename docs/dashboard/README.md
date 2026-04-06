# Dashboard Documentation

This folder defines dashboard structure, menu hierarchy, permissions, capabilities, architecture, and design tasks for every system role.

## Folder Structure

```text
docs/dashboard/
├── README.md
├── ARCHITECTURE.md
├── DESIGN_TASKS.md
└── roles/
    ├── super-admin.md
    ├── tenant-admin-hospital-admin.md
    ├── hospital-manager.md
    ├── operations-manager.md
    ├── doctor.md
    ├── nurse.md
    ├── receptionist-front-desk.md
    ├── pharmacist.md
    ├── lab-technician.md
    ├── accountant-finance-manager.md
    ├── ward-manager.md
    ├── ambulance-driver-transport-staff.md
    ├── it-admin-system-administrator.md
    ├── inventory-manager-store-manager.md
    ├── hr-manager.md
    ├── patient-portal-user.md
    ├── insurance-agent-partner.md
    ├── auditor-compliance-officer.md
    ├── data-analyst.md
    ├── api-client-integration-role.md
    └── ai-assistant-role.md
```

## How To Use

- Read `ARCHITECTURE.md` for dashboard system design and access model.
- Read `DESIGN_TASKS.md` for implementation roadmap and UI/UX tasks.
- Open each role file under `roles/` for menu, submenu, permissions, and capability scope.

## Naming Convention

- Roles are stored in kebab-case file names.
- Role titles in files match business names used in the project plan.
- Permission keys use dotted format: `module.action.scope`.
