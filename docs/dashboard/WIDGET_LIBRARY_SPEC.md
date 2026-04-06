# Widget Library Specification

## Goal

Define reusable dashboard widgets with shared props, loading/error states, and role-aware usage rules.

## Core Widgets

1. KPI Card
- Purpose: quick metric summary.
- Props: title, value, delta, icon, trendDirection.
- States: loading skeleton, empty value, warning.

2. Trend Chart
- Purpose: time-series metric visualization.
- Props: title, series, xKey, yKey, period.
- States: no-data state, API timeout fallback.

3. Queue List
- Purpose: OPD/pharmacy/lab queue visualization.
- Props: title, items, statusColorMap, actions.
- States: stale-data indicator, pagination.

4. Alert Banner
- Purpose: urgent alerts (critical lab, audit, security).
- Props: level, message, actionLabel, actionRoute.
- States: dismissed, auto-expire.

5. Approval Inbox
- Purpose: discount/payroll/leave/claims approvals.
- Props: title, pendingCount, records, onApprove, onReject.
- States: optimistic updates, audit-required actions.

## Shared Behavior

- All widgets support tenant-safe filtering.
- All widgets support role-based visibility checks.
- All widgets support dark and light themes.
- All widgets support keyboard navigation and screen reader labels.

## Data Contract Rules

- Widget API responses must include:
  - widget key
  - title
  - payload
  - last_updated timestamp
- Errors should include machine-readable code and user-friendly message.

## Quality Requirements

- Render performance target: under 100ms for cached widget payload.
- Visual consistency: common spacing and typography tokens.
- Accessibility: WCAG AA for text contrast and focus states.
