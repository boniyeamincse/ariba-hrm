# Dashboard Architecture

## Objective

Provide a role-aware dashboard framework where each user sees only the modules, widgets, and actions allowed by their permissions inside a tenant-safe context.

## Core Principles

- Tenant isolation first (all role data filtered by `tenant_id` unless platform role).
- Least privilege authorization.
- Config-driven menu generation.
- Reusable dashboard widgets with role-scoped data providers.
- Auditable access for sensitive modules.

## Layered Architecture

1. UI Layer
- React dashboard shell.
- Dynamic sidebar/top-nav from role menu config.
- Widget renderer for KPI cards, charts, tables, alerts.

2. Access Layer
- Authentication: Sanctum token/session.
- Authorization: permission middleware + frontend capability guards.
- Policy checks for record-level actions.

3. API Layer
- Role-based endpoint exposure.
- Tenant-aware query scopes.
- Rate limiting and audit logging for privileged operations.

4. Domain Layer
- Clinical domains (OPD, IPD, lab, pharmacy).
- Operational domains (HR, inventory, ambulance).
- Financial domains (billing, insurance, accounting).

5. Data Layer
- Tenant databases/schemas.
- Analytics materialized views for heavy dashboards.
- Cache layer (Redis) for dashboard KPI speed.

## Menu and Permission Model

- Menu item visibility is controlled by capability keys.
- Action buttons require specific permission keys.
- Super Admin can cross-tenant switch; tenant roles cannot.

Example mapping:

- Menu: `Clinical > OPD Queue`
- Required capability: `opd.queue.read`
- Required action permission for next token: `opd.queue.call`

## Dashboard Composition

- Base layout: header, quick search, notifications, role switch info, tenant badge.
- Widget zones:
  - Priority alerts
  - Operational KPIs
  - Task queue
  - Recent activities
  - Compliance reminders

## Security Controls

- Enforce backend permission checks for every endpoint.
- Do not rely only on hidden UI controls.
- Log sensitive events: export, delete, role change, financial approvals.
- Mask patient identity for non-clinical roles when required.

## Performance Strategy

- Cache KPI queries (short TTL).
- Use async loading for secondary widgets.
- Paginate heavy tables.
- Push live queue/alert updates via WebSocket.

## Extensibility

- Add new role by creating one role config and one role markdown spec.
- Add module by registering:
  - menu node
  - capability keys
  - backend policies
  - widget provider
