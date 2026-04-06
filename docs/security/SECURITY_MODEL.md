# Security Model

## Objectives

- Protect patient privacy and tenant data boundaries.
- Enforce least privilege access control.
- Ensure traceability for sensitive operations.

## Core Controls

- Authentication via Sanctum/session and token modes.
- Authorization via role and permission matrix.
- Record-level checks via policy classes.
- API rate limiting and lockout controls.
- Audit logging on privileged endpoints.

## Tenant Boundary Rules

- Tenant context resolved before controller execution.
- Cross-tenant reads/writes are blocked unless platform role.
- File storage paths must include tenant prefix.

## Sensitive Operations

- Billing adjustments
- Prescription modifications
- Clinical note edits
- Role/permission updates
- Data exports

Each sensitive operation must produce an audit event with actor, action, entity, timestamp, and source IP.
