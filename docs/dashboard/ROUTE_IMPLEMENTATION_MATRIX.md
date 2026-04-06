# Route Implementation Matrix

## Purpose

Track menu-to-route implementation status for frontend and backend so teams can deliver dashboards without gaps.

## Status Legend

- Implemented: route exists and page is wired.
- Placeholder: route exists with scaffold page.
- Planned: route listed in menu but not yet wired.

## Matrix

| Module | Route | Frontend Status | Backend Status | Notes |
|---|---|---|---|---|
| Dashboard | /dashboard | Implemented | Implemented | Role overview API available |
| Dashboard Tasks | /dashboard/tasks | Implemented | Implemented | Task dashboard present |
| Users | /dashboard/users | Placeholder | Implemented | API exists, richer UI pending |
| Patients | /dashboard/patients | Implemented | Implemented | Search/profile/register done |
| Patients Register | /dashboard/patients/register | Implemented | Implemented | Duplicate detection integrated |
| Patients Profile | /dashboard/patients/:id | Implemented | Implemented | History and visits included |
| Appointments | /dashboard/appointments | Placeholder | Partial | Slots/book endpoints available |
| Billing | /dashboard/billing | Placeholder | Partial | Charges/invoice/payments API exists |
| Inventory | /dashboard/inventory | Placeholder | Partial | Item/procurement endpoints available |
| Reports | /dashboard/reports | Placeholder | Partial | Summary endpoint available |
| Pharmacy | /dashboard/pharmacy | Planned | Partial | API exists, dashboard pending |
| Laboratory | /dashboard/lab | Planned | Partial | API exists, dashboard pending |
| HRM | /dashboard/hr | Planned | Partial | Staff/payroll endpoints exist |
| Operations | /dashboard/operations | Planned | Partial | Ward/ambulance views pending |
| Integrations | /dashboard/integrations | Planned | Partial | API permission ready |
| AI Assistant | /dashboard/ai-assistant | Planned | Partial | Role + permission available |
| Security | /dashboard/security | Planned | Partial | Audit route pattern exists |
| Settings | /dashboard/settings | Implemented | Partial | Session management UI pending |

## Delivery Order

1. Appointments
2. Billing
3. Inventory
4. Pharmacy
5. Laboratory
6. HRM
7. Operations
8. Reports advanced widgets
9. Security and audit pages
10. Integrations and AI assistant panels

## Acceptance Rules

- Every visible menu route must resolve to a page.
- Every page must call only authorized endpoints.
- Every protected route must return 403 for unauthorized role.
