# Dashboard Design Tasks

## Goal

Deliver role-specific dashboards with consistent UX, strict permission control, and measurable operational value.

## Execution Model

- Delivery style: phase-gated, API-first, role-by-role rollout.
- Priority order: platform roles, clinical roles, operations/finance roles, external/portal roles.
- Success metric: every role can only see authorized menu/actions and can complete core workflow from dashboard.

## Phase A: Information Architecture

1. Define top-level navigation groups per role.
2. Define submenu depth and route map.
3. Validate overlap between clinical, operations, and finance roles.
4. Publish permission-to-menu matrix.

### Deliverables

- Role navigation map (all roles).
- Route inventory by module (`/dashboard`, `/users`, `/patients`, `/appointments`, `/billing`, `/inventory`, `/reports`, `/settings`).
- Permission-to-menu matrix signed by product and engineering.

### Exit Criteria

- No orphan menu items.
- Every route has an owning module and permission.
- Shared menus are normalized across similar roles.

## Phase B: Permission and Capability Design

1. Finalize permission dictionary using dotted naming.
2. Map each UI action to backend permission checks.
3. Add policy tests for read/create/update/delete/export/approve flows.
4. Add audit tags for sensitive capabilities.

### Deliverables

- Permission dictionary (single source of truth).
- UI action to API endpoint to permission mapping sheet.
- Policy/gate coverage report.
- Sensitive action audit taxonomy.

### Exit Criteria

- All privileged UI actions have backend enforcement.
- Unauthorized calls return `403` consistently.
- Audit events generated for high-risk actions.

## Phase C: UX and Component Design

1. Build reusable dashboard shell.
2. Build widget library:
- KPI card
- trend chart
- queue list
- alert banner
- approval inbox
3. Design mobile behavior for each role.
4. Add accessibility checks (contrast, keyboard, aria labels).

### Deliverables

- Design system-ready dashboard shell.
- Role-widget catalog with component ownership.
- Responsive behavior specs (desktop/tablet/mobile).
- Accessibility checklist and test evidence.

### Exit Criteria

- Shared components reused across at least 3 roles.
- All primary dashboard interactions keyboard accessible.
- Color contrast meets WCAG AA for key text/UI controls.

## Phase D: Data and API Design

1. Define dashboard KPI endpoints by module.
2. Add aggregate query services and cache policies.
3. Add fallback behavior for partial failures.
4. Add CSV/PDF export contracts where needed.

### Deliverables

- KPI endpoint catalog and response contracts.
- Aggregation service spec and cache TTL policy.
- Error/fallback behavior matrix.
- Export contract spec (CSV/PDF).

### Exit Criteria

- KPI endpoints return tenant-safe data only.
- Fallback state implemented for API timeout/partial failures.
- Exported data obeys role permissions.

## Phase E: QA and Security

1. Build role-based navigation test matrix.
2. Verify hidden routes are blocked server-side.
3. Pen-test critical actions: billing, prescription, discharge, payroll.
4. Validate audit logs for all privileged events.

### Deliverables

- Automated role-route test suite.
- Security verification report.
- Pen-test findings and remediation log.
- Audit log validation checklist.

### Exit Criteria

- 100% critical routes covered by authz tests.
- No critical or high-severity auth bypass findings.
- Privileged actions include actor, role, tenant, and timestamp in logs.

## Role Delivery Checklist

- Menu and submenu defined.
- Permissions listed.
- Capabilities listed.
- KPI widgets identified.
- High-risk actions flagged.
- Acceptance criteria written.

## Suggested Delivery Sequence

1. Super Admin, Tenant Admin, Hospital Manager
2. Doctor, Nurse, Receptionist, Ward Manager
3. Pharmacist, Lab Technician, Inventory Manager, Accountant, HR Manager
4. Ambulance Driver, IT Admin, Auditor, Data Analyst
5. Patient, Insurance Agent, API Client, AI Assistant

## Acceptance Template (Per Role)

- Role: `<role-name>`
- Menus implemented: Yes/No
- Permission mapping complete: Yes/No
- Widgets delivered: Yes/No
- Restricted actions verified (`403`): Yes/No
- Audit events verified: Yes/No
- Mobile layout verified: Yes/No
