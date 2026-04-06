import { Dashboard } from '../../Dashboard'

// Tenant Admin: Hospital Configuration & Full Data Access
// Scope: Full hospital data, user management, billing, module configuration
// Widgets: total_staff, total_patients, revenue_today, outstanding_bills
// Quick Actions: Manage Users, OPD Queue, Billing Console

export function TenantAdminDashboard() {
  return <Dashboard />
}
