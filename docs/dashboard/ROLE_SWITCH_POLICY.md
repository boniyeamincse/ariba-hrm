# Role Switch Policy

## Purpose

Define when and how role switching is allowed in the dashboard.

## Allowed Role Switch Scope

- Super Admin: can assume platform and tenant-level supervisory views for diagnostics.
- Tenant Admin: can switch between assigned operational roles within same tenant.
- Hospital Manager: can switch to read-only manager sub-views when assigned.
- All other roles: no role switching in production.

## Security Constraints

- Role switch must never cross tenant boundaries.
- Every role switch event must be audit logged.
- Session must include original role and effective role.
- Sensitive actions require permission check on effective role.

## UX Rules

- Top navbar displays active role clearly.
- Switch panel only visible when permission allows.
- Switching role refreshes menu and widgets immediately.
- User can revert to original role in one click.

## Audit Requirements

Audit event payload fields:
- actor_user_id
- tenant_id
- source_role
- target_role
- switched_at
- ip_address
- user_agent

## Failure Handling

- If role switch validation fails, keep original role and show forbidden message.
- If role payload fails to load, fallback to source role and invalidate effective role token.
