# Dashboard Documentation

This folder defines dashboard structure, menu hierarchy, permissions, capabilities, architecture, and design tasks for every system role.

## Folder Structure

```text
docs/dashboard/
├── README.md
├── ARCHITECTURE.md
├── DESIGN_TASKS.md
├── IMPLEMENTATION_GUIDE.md
├── MASTER_MENU_CATALOG.md
├── PERMISSION_MENU_MATRIX.md
├── ROLE_SWITCH_POLICY.md
├── ROUTE_IMPLEMENTATION_MATRIX.md
├── WIDGET_LIBRARY_SPEC.md
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
- Read `IMPLEMENTATION_GUIDE.md` for frontend/backend implementation mapping.
- Read `MASTER_MENU_CATALOG.md` for full dashboard menu/submenu tree.
- Read `PERMISSION_MENU_MATRIX.md` for route-to-permission and role access mapping.
- Read `ROUTE_IMPLEMENTATION_MATRIX.md` for frontend/backend route completion tracking.
- Read `WIDGET_LIBRARY_SPEC.md` for reusable dashboard widget standards.
- Read `ROLE_SWITCH_POLICY.md` for role switching governance and audit rules.
- Open each role file under `roles/` for menu, submenu, permissions, and capability scope.

## Naming Convention

- Roles are stored in kebab-case file names.
- Role titles in files match business names used in the project plan.
- Permission keys use dotted format: `module.action.scope`.
