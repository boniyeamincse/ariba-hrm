# RBAC Dashboard Implementation Guide

## Frontend Component Structure

Recommended structure:

- `layouts/DashboardLayout.tsx`
- `pages/dashboard/Dashboard.tsx`
- `pages/dashboard/ModulePlaceholderPage.tsx`
- `components/dashboard/*` (extend with cards/tables/charts)

Current implemented route modules:

- `/dashboard`
- `/dashboard/users`
- `/dashboard/patients`
- `/dashboard/appointments`
- `/dashboard/billing`
- `/dashboard/inventory`
- `/dashboard/reports`
- `/dashboard/settings`

## Role-Based Conditional Rendering Example

Use backend role payload to render widgets and modules:

```tsx
{widgets.map((widget) => (
  <WidgetCard key={widget.key} label={widget.label} value={widget.value} />
))}

{role === 'doctor' && <DoctorQuickPanel />}
{role === 'nurse' && <NurseVitalsPanel />}
{role === 'super-admin' && <PlatformHealthPanel />}
```

## Laravel API Endpoints (Sample)

Authenticated + tenant routes:

- `GET /api/dashboard/overview`
- `GET /api/dashboard/widgets`
- `GET /api/dashboard/menu`

Other related dashboard routes:

- `GET /api/dashboard/stats`
- `GET /api/menus`

## Example Payload: GET /api/dashboard/overview

```json
{
  "role": "doctor",
  "tenant_id": 1,
  "menus": [
    { "label": "Dashboard", "route": "/dashboard", "icon": "LayoutDashboard" },
    { "label": "Patients", "route": "/dashboard/patients", "icon": "UserRound" }
  ],
  "widgets": [
    { "key": "todays_appointments", "label": "Today's Appointments", "value": 12 },
    { "key": "recent_prescriptions", "label": "Recent Prescriptions", "value": 8 }
  ],
  "top_nav": {
    "can_switch_role": false,
    "notifications_count": 4
  }
}
```

## Production Notes

- Keep all role checks enforced on backend policies/middleware.
- Keep menu and widget config centralized to avoid frontend hard-coding.
- Add React Query cache keys per role + tenant for dashboard endpoints.
- Use websocket channels for queue and alert widgets where required.
