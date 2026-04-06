import { Dashboard } from '../../Dashboard'

// Super Admin: Global SaaS Operations
// Scope: All hospitals, all tenants, billing, security, integrations
// Widgets: total_hospitals, active_tenants, suspended_tenants, global_users,
//          revenue_today, payments_today, api_calls_today, security_alerts,
//          webhook_failures, enabled_modules
// Quick Actions: Tenant Management, Global User Access, Subscription & Billing,
//                System Analytics, Security & RBAC, API & Integrations,
//                Module Controls, Backup & Maintenance, AI Governance, Support Monitoring

export function SuperAdminDashboard() {
  return <Dashboard />
}
