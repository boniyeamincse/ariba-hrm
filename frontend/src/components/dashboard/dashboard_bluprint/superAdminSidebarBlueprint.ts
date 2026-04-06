export type SidebarMenuItem = {
  id: string
  label: string
  icon: string
  route?: string
  collapsible?: boolean
  defaultExpanded?: boolean
  children?: SidebarMenuItem[]
}

export type SidebarBlueprint = {
  role: 'super-admin'
  dynamic: boolean
  showOnlyForRole: string[]
  menu: SidebarMenuItem[]
}

// Single source of truth for Super Admin sidebar architecture.
// Use this in the dashboard layout to render dynamic menus with icons and collapsible groups.
export const superAdminSidebarBlueprint: SidebarBlueprint = {
  role: 'super-admin',
  dynamic: true,
  showOnlyForRole: ['super-admin'],
  menu: [
    {
      id: 'dashboard',
      label: 'Dashboard',
      icon: 'LayoutDashboard',
      collapsible: true,
      defaultExpanded: true,
      children: [
        { id: 'dashboard-overview', label: 'Overview / Stats', icon: 'BarChart3', route: '/dashboard' },
        { id: 'dashboard-health', label: 'System Health', icon: 'Activity', route: '/dashboard/reports?tab=system-health' },
        { id: 'dashboard-revenue', label: 'Revenue Analytics', icon: 'LineChart', route: '/dashboard/reports?tab=revenue-analytics' },
      ],
    },
    {
      id: 'tenants-management',
      label: 'Tenants Management',
      icon: 'Building2',
      collapsible: true,
      defaultExpanded: true,
      children: [
        { id: 'tenant-list', label: 'Tenant List (All Hospitals)', icon: 'List', route: '/dashboard/users?tab=tenants' },
        { id: 'tenant-create', label: 'Create Tenant', icon: 'PlusCircle', route: '/dashboard/users?tab=create-tenant' },
        { id: 'tenant-edit', label: 'Tenant Details / Edit', icon: 'SquarePen', route: '/dashboard/users?tab=tenant-edit' },
        { id: 'tenant-plan', label: 'Change Subscription Plan', icon: 'CreditCard', route: '/dashboard/billing?tab=change-plan' },
        { id: 'tenant-status', label: 'Suspend / Activate Tenant', icon: 'Power', route: '/dashboard/users?tab=tenant-status' },
        { id: 'tenant-delete', label: 'Delete Tenant', icon: 'Trash2', route: '/dashboard/users?tab=tenant-delete' },
      ],
    },
    {
      id: 'user-management',
      label: 'User Management',
      icon: 'Users',
      collapsible: true,
      defaultExpanded: true,
      children: [
        { id: 'users-all', label: 'All Users (Across Tenants)', icon: 'UsersRound', route: '/dashboard/users?tab=all-users' },
        { id: 'users-rbac', label: 'Roles & Permissions', icon: 'ShieldCheck', route: '/dashboard/settings?tab=rbac' },
        { id: 'users-assign', label: 'Assign Roles', icon: 'UserCog', route: '/dashboard/users?tab=assign-roles' },
        { id: 'users-logs', label: 'Activity Logs', icon: 'FileSearch', route: '/dashboard/reports?tab=activity-logs' },
      ],
    },
    {
      id: 'modules-features',
      label: 'Modules & Features',
      icon: 'Boxes',
      collapsible: true,
      defaultExpanded: true,
      children: [
        {
          id: 'modules-enable-disable',
          label: 'Enable / Disable Modules',
          icon: 'ToggleLeft',
          collapsible: true,
          children: [
            { id: 'module-opd-ipd', label: 'OPD / IPD', icon: 'Stethoscope', route: '/dashboard/opd/queue' },
            { id: 'module-pharmacy', label: 'Pharmacy', icon: 'Pill', route: '/dashboard/inventory?tab=pharmacy' },
            { id: 'module-lab', label: 'Lab', icon: 'FlaskConical', route: '/dashboard/reports?tab=lab' },
            { id: 'module-hrm', label: 'HRM', icon: 'Users', route: '/dashboard/employees' },
            { id: 'module-billing', label: 'Billing & Finance', icon: 'Receipt', route: '/dashboard/billing' },
            { id: 'module-inventory', label: 'Inventory', icon: 'Package', route: '/dashboard/inventory' },
          ],
        },
        { id: 'feature-toggles', label: 'Feature Toggle Settings', icon: 'SlidersHorizontal', route: '/dashboard/settings?tab=feature-toggles' },
      ],
    },
    {
      id: 'subscription-billing',
      label: 'Subscription & Billing',
      icon: 'BadgeDollarSign',
      collapsible: true,
      children: [
        { id: 'plans-overview', label: 'Plans Overview', icon: 'FileText', route: '/dashboard/billing?tab=plans-overview' },
        { id: 'plans-assign', label: 'Assign / Change Plans', icon: 'Repeat', route: '/dashboard/billing?tab=assign-plan' },
        { id: 'payments-status', label: 'Payment Status', icon: 'Wallet', route: '/dashboard/billing?tab=payment-status' },
        { id: 'billing-history', label: 'Billing History', icon: 'History', route: '/dashboard/billing?tab=history' },
        { id: 'invoices', label: 'Invoices', icon: 'FileSpreadsheet', route: '/dashboard/billing?tab=invoices' },
      ],
    },
    {
      id: 'system-configuration',
      label: 'System Configuration',
      icon: 'Settings',
      collapsible: true,
      children: [
        { id: 'config-general', label: 'General Settings', icon: 'Settings2', route: '/dashboard/settings' },
        { id: 'config-email-sms', label: 'Email / SMS Configuration', icon: 'Mail', route: '/dashboard/settings?tab=communications' },
        { id: 'config-payment-gateway', label: 'Payment Gateway Setup', icon: 'CreditCard', route: '/dashboard/settings?tab=payments' },
        { id: 'config-api-integration', label: 'API & Integration Settings', icon: 'PlugZap', route: '/dashboard/settings?tab=integrations' },
      ],
    },
    {
      id: 'audit-security',
      label: 'Audit & Security',
      icon: 'Shield',
      collapsible: true,
      children: [
        { id: 'security-login-logs', label: 'Login Logs', icon: 'LogIn', route: '/dashboard/reports?tab=login-logs' },
        { id: 'security-audit-logs', label: 'Audit Logs', icon: 'ScrollText', route: '/dashboard/reports?tab=audit-logs' },
        { id: 'security-access-control', label: 'Access Control', icon: 'Lock', route: '/dashboard/settings?tab=access-control' },
        { id: 'security-settings', label: 'Security Settings', icon: 'ShieldAlert', route: '/dashboard/settings?tab=security' },
      ],
    },
    {
      id: 'reports-analytics',
      label: 'Reports & Analytics',
      icon: 'ChartNoAxesCombined',
      collapsible: true,
      defaultExpanded: true,
      children: [
        { id: 'reports-tenant', label: 'Tenant Reports', icon: 'Building2', route: '/dashboard/reports?tab=tenant-reports' },
        { id: 'reports-user', label: 'User Reports', icon: 'UsersRound', route: '/dashboard/reports?tab=user-reports' },
        { id: 'reports-revenue', label: 'Revenue Reports', icon: 'LineChart', route: '/dashboard/reports?tab=revenue-reports' },
        { id: 'reports-usage', label: 'System Usage Analytics', icon: 'BarChart3', route: '/dashboard/reports?tab=usage-analytics' },
      ],
    },
    {
      id: 'support-monitoring',
      label: 'Support & Monitoring',
      icon: 'LifeBuoy',
      collapsible: true,
      children: [
        { id: 'support-tickets', label: 'Tickets / Support Requests', icon: 'MessagesSquare', route: '/dashboard/tasks?tab=tickets' },
        { id: 'support-alerts', label: 'Notifications / Alerts', icon: 'Bell', route: '/dashboard/tasks?tab=alerts' },
        { id: 'support-maintenance', label: 'System Maintenance', icon: 'Wrench', route: '/dashboard/settings?tab=maintenance' },
      ],
    },
    {
      id: 'advanced-ai',
      label: 'Advanced / AI',
      icon: 'Bot',
      collapsible: true,
      children: [
        { id: 'ai-management', label: 'AI Assistant Management', icon: 'BotMessageSquare', route: '/dashboard/tasks?tab=ai-assistant' },
        { id: 'ai-automation-rules', label: 'Automation Rules', icon: 'Workflow', route: '/dashboard/settings?tab=automation' },
        { id: 'ai-analytics', label: 'Advanced Analytics', icon: 'Radar', route: '/dashboard/reports?tab=advanced-analytics' },
      ],
    },
    {
      id: 'account',
      label: 'Account',
      icon: 'UserRoundCog',
      collapsible: true,
      children: [
        { id: 'account-profile', label: 'Profile', icon: 'UserRound', route: '/dashboard/settings?tab=profile' },
        { id: 'account-password', label: 'Change Password', icon: 'KeyRound', route: '/dashboard/settings?tab=password' },
        { id: 'account-logout', label: 'Logout', icon: 'LogOut', route: '/dashboard/settings?tab=logout' },
      ],
    },
  ],
}
