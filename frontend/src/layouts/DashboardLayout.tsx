import { useState, useEffect } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'
import { motion, AnimatePresence } from 'framer-motion'
import * as LucideIcons from 'lucide-react'
import { api } from '../lib/api'

type MenuItem = {
  id: number
  label: string
  icon: string | null
  route: string | null
  children?: MenuItem[]
}

type ApiMenuItem = {
  id?: number
  label: string
  icon: string | null
  route: string | null
  children?: ApiMenuItem[]
}

const superAdminMenus: MenuItem[] = [
  {
    id: 1000,
    label: 'Dashboard',
    icon: 'LayoutDashboard',
    route: null,
    children: [
      { id: 1001, label: 'Overview / Stats', icon: 'BarChart3', route: '/dashboard', children: [] },
      { id: 1002, label: 'System Health', icon: 'Activity', route: '/dashboard/reports', children: [] },
      { id: 1003, label: 'Revenue Analytics', icon: 'LineChart', route: '/dashboard/reports', children: [] },
    ],
  },
  {
    id: 1100,
    label: 'Tenants Management',
    icon: 'Building2',
    route: null,
    children: [
      { id: 1101, label: 'Tenant List (All Hospitals)', icon: 'List', route: '/dashboard/users', children: [] },
      { id: 1102, label: 'Create Tenant', icon: 'PlusCircle', route: '/dashboard/users?tab=create-tenant', children: [] },
      { id: 1103, label: 'Tenant Details / Edit', icon: 'SquarePen', route: '/dashboard/users?tab=tenant-details', children: [] },
      { id: 1104, label: 'Change Subscription Plan', icon: 'CreditCard', route: '/dashboard/billing?tab=plans', children: [] },
      { id: 1105, label: 'Suspend / Activate Tenant', icon: 'Power', route: '/dashboard/users?tab=tenant-status', children: [] },
      { id: 1106, label: 'Delete Tenant', icon: 'Trash2', route: '/dashboard/users?tab=tenant-delete', children: [] },
    ],
  },
  {
    id: 1200,
    label: 'User Management',
    icon: 'Users',
    route: null,
    children: [
      { id: 1201, label: 'All Users (Across Tenants)', icon: 'UsersRound', route: '/dashboard/users', children: [] },
      { id: 1202, label: 'Roles & Permissions', icon: 'ShieldCheck', route: '/dashboard/settings?tab=rbac', children: [] },
      { id: 1203, label: 'Assign Roles', icon: 'UserCog', route: '/dashboard/users?tab=assign-roles', children: [] },
      { id: 1204, label: 'Activity Logs', icon: 'FileSearch', route: '/dashboard/reports?tab=activity-logs', children: [] },
    ],
  },
  {
    id: 1300,
    label: 'Modules & Features',
    icon: 'Boxes',
    route: null,
    children: [
      { id: 1301, label: 'Enable / Disable Modules', icon: 'ToggleLeft', route: '/dashboard/settings?tab=modules', children: [] },
      { id: 1302, label: 'OPD / IPD', icon: 'Stethoscope', route: '/dashboard/opd/queue', children: [] },
      { id: 1303, label: 'Pharmacy', icon: 'Pill', route: '/dashboard/inventory?tab=pharmacy', children: [] },
      { id: 1304, label: 'Lab', icon: 'FlaskConical', route: '/dashboard/reports?tab=lab', children: [] },
      { id: 1305, label: 'HRM', icon: 'Users', route: '/dashboard/employees', children: [] },
      { id: 1306, label: 'Billing & Finance', icon: 'Receipt', route: '/dashboard/billing', children: [] },
      { id: 1307, label: 'Inventory', icon: 'Package', route: '/dashboard/inventory', children: [] },
      { id: 1308, label: 'Feature Toggle Settings', icon: 'SlidersHorizontal', route: '/dashboard/settings?tab=feature-toggles', children: [] },
    ],
  },
  {
    id: 1400,
    label: 'Subscription & Billing',
    icon: 'BadgeDollarSign',
    route: null,
    children: [
      { id: 1401, label: 'Plans Overview', icon: 'FileText', route: '/dashboard/billing?tab=plans-overview', children: [] },
      { id: 1402, label: 'Assign / Change Plans', icon: 'Repeat', route: '/dashboard/billing?tab=assign-plan', children: [] },
      { id: 1403, label: 'Payment Status', icon: 'Wallet', route: '/dashboard/billing?tab=payment-status', children: [] },
      { id: 1404, label: 'Billing History', icon: 'History', route: '/dashboard/billing?tab=history', children: [] },
      { id: 1405, label: 'Invoices', icon: 'FileSpreadsheet', route: '/dashboard/billing?tab=invoices', children: [] },
    ],
  },
  {
    id: 1500,
    label: 'System Configuration',
    icon: 'Settings',
    route: null,
    children: [
      { id: 1501, label: 'General Settings', icon: 'Settings2', route: '/dashboard/settings', children: [] },
      { id: 1502, label: 'Email / SMS Configuration', icon: 'Mail', route: '/dashboard/settings?tab=communications', children: [] },
      { id: 1503, label: 'Payment Gateway Setup', icon: 'CreditCard', route: '/dashboard/settings?tab=payments', children: [] },
      { id: 1504, label: 'API & Integration Settings', icon: 'PlugZap', route: '/dashboard/settings?tab=integrations', children: [] },
    ],
  },
  {
    id: 1600,
    label: 'Audit & Security',
    icon: 'Shield',
    route: null,
    children: [
      { id: 1601, label: 'Login Logs', icon: 'LogIn', route: '/dashboard/reports?tab=login-logs', children: [] },
      { id: 1602, label: 'Audit Logs', icon: 'ScrollText', route: '/dashboard/reports?tab=audit-logs', children: [] },
      { id: 1603, label: 'Access Control', icon: 'Lock', route: '/dashboard/settings?tab=access-control', children: [] },
      { id: 1604, label: 'Security Settings', icon: 'ShieldAlert', route: '/dashboard/settings?tab=security', children: [] },
    ],
  },
  {
    id: 1700,
    label: 'Reports & Analytics',
    icon: 'ChartNoAxesCombined',
    route: null,
    children: [
      { id: 1701, label: 'Tenant Reports', icon: 'Building2', route: '/dashboard/reports?tab=tenant-reports', children: [] },
      { id: 1702, label: 'User Reports', icon: 'UsersRound', route: '/dashboard/reports?tab=user-reports', children: [] },
      { id: 1703, label: 'Revenue Reports', icon: 'LineChart', route: '/dashboard/reports?tab=revenue-reports', children: [] },
      { id: 1704, label: 'System Usage Analytics', icon: 'BarChart3', route: '/dashboard/reports?tab=usage-analytics', children: [] },
    ],
  },
  {
    id: 1800,
    label: 'Support & Monitoring',
    icon: 'LifeBuoy',
    route: null,
    children: [
      { id: 1801, label: 'Tickets / Support Requests', icon: 'MessagesSquare', route: '/dashboard/tasks?tab=tickets', children: [] },
      { id: 1802, label: 'Notifications / Alerts', icon: 'Bell', route: '/dashboard/tasks?tab=alerts', children: [] },
      { id: 1803, label: 'System Maintenance', icon: 'Wrench', route: '/dashboard/settings?tab=maintenance', children: [] },
    ],
  },
  {
    id: 1900,
    label: 'Advanced / AI',
    icon: 'Bot',
    route: null,
    children: [
      { id: 1901, label: 'AI Assistant Management', icon: 'BotMessageSquare', route: '/dashboard/tasks?tab=ai-assistant', children: [] },
      { id: 1902, label: 'Automation Rules', icon: 'Workflow', route: '/dashboard/settings?tab=automation', children: [] },
      { id: 1903, label: 'Advanced Analytics', icon: 'Radar', route: '/dashboard/reports?tab=advanced-analytics', children: [] },
    ],
  },
  {
    id: 2000,
    label: 'Account',
    icon: 'UserRoundCog',
    route: null,
    children: [
      { id: 2001, label: 'Profile', icon: 'UserRound', route: '/dashboard/settings?tab=profile', children: [] },
      { id: 2002, label: 'Change Password', icon: 'KeyRound', route: '/dashboard/settings?tab=password', children: [] },
      { id: 2003, label: 'Logout', icon: 'LogOut', route: '/dashboard/settings?tab=logout', children: [] },
    ],
  },
]

export function DashboardLayout() {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false)
  const [menus, setMenus] = useState<MenuItem[]>([])
  const [expandedMenus, setExpandedMenus] = useState<number[]>([])
  const [notificationsCount, setNotificationsCount] = useState(0)
  const [canSwitchRole, setCanSwitchRole] = useState(false)
  const [dashboardRole, setDashboardRole] = useState<string>('')
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    const loadNavState = async () => {
      try {
        const [menuRes, overviewRes] = await Promise.all([
          api.get('/dashboard/menu'),
          api.get('/dashboard/overview'),
        ])

        const roleMenu = menuRes.data?.items ?? []
        let fallbackId = 1
        const normalizeTree = (items: ApiMenuItem[]): MenuItem[] => items.map((item) => {
          const id = item.id ?? fallbackId++

          return {
            id,
            label: item.label,
            icon: item.icon,
            route: item.route,
            children: item.children ? normalizeTree(item.children) : [],
          }
        })
        const normalizedMenu = normalizeTree(roleMenu)
        const normalizedRole = String(overviewRes.data?.role ?? '')

        setDashboardRole(normalizedRole)

        if (normalizedRole === 'super-admin') {
          try {
            const superAdminMenuRes = await api.get('/dashboard/super-admin/menu')
            const backendSuperAdminMenu = normalizeTree(superAdminMenuRes.data?.items ?? [])
            setMenus(backendSuperAdminMenu.length > 0 ? backendSuperAdminMenu : superAdminMenus)
            setExpandedMenus([1000, 1100, 1200, 1300])
          } catch {
            setMenus(superAdminMenus)
            setExpandedMenus([1000, 1100, 1200, 1300])
          }
        } else {
          setMenus(normalizedMenu)
        }
        setNotificationsCount(overviewRes.data?.top_nav?.notifications_count ?? 0)
        setCanSwitchRole(Boolean(overviewRes.data?.top_nav?.can_switch_role))
      } catch (error) {
        console.error('Dashboard nav bootstrap failed', error)
      }
    }

    loadNavState()
  }, [])

  const onLogout = () => {
    logout()
    navigate('/auth/login')
  }

  const toggleExpand = (id: number) => {
    setExpandedMenus(prev => 
      prev.includes(id) ? prev.filter(m => m !== id) : [...prev, id]
    )
  }

  const renderIcon = (iconName: string | null) => {
    if (!iconName) return null
    const Icon = (LucideIcons as any)[iconName]
    return Icon ? <Icon className="h-4 w-4" /> : null
  }

  const renderNavItem = (item: MenuItem, isChild = false) => {
    const hasChildren = item.children && item.children.length > 0
    const isExpanded = expandedMenus.includes(item.id)

    return (
      <div key={item.id} className="space-y-1">
        {item.route ? (
          <NavLink
            to={item.route}
            onClick={() => setIsSidebarOpen(false)}
            end={item.route === '/dashboard'}
            className={({ isActive }) =>
              `group flex items-center gap-3 rounded-xl border px-3 py-2 text-sm font-medium transition-all ${
                isActive 
                  ? isChild
                    ? 'border-emerald-300 bg-emerald-100/90 text-emerald-900'
                    : 'border-white/40 bg-white/20 text-white shadow-[0_10px_25px_-18px_rgba(12,18,36,0.9)]'
                  : isChild
                    ? 'border-transparent text-emerald-50/80 hover:bg-white/10 hover:text-white'
                    : 'border-transparent text-emerald-100/85 hover:bg-white/10 hover:text-white'
              } ${isChild ? 'ml-4 py-1.5 text-[13px]' : ''}`
            }
          >
            <span className="opacity-90 group-hover:opacity-100">{renderIcon(item.icon)}</span>
            <span>{item.label}</span>
          </NavLink>
        ) : (
          <button
            onClick={() => toggleExpand(item.id)}
            className={`flex w-full items-center justify-between gap-3 rounded-xl border border-transparent px-3 py-2 text-sm font-semibold transition-all text-emerald-50/85 hover:bg-white/10 hover:text-white ${isChild ? 'ml-4 py-1.5 text-[13px] font-medium' : ''}`}
          >
            <div className="flex items-center gap-3">
              {renderIcon(item.icon)}
              <span>{item.label}</span>
            </div>
            {hasChildren && (
              <LucideIcons.ChevronRight className={`h-3 w-3 transition-transform ${isExpanded ? 'rotate-90' : ''}`} />
            )}
          </button>
        )}

        {hasChildren && (
          <AnimatePresence>
            {isExpanded && (
              <motion.div
                initial={{ height: 0, opacity: 0 }}
                animate={{ height: 'auto', opacity: 1 }}
                exit={{ height: 0, opacity: 0 }}
                className="overflow-hidden border-l border-white/15 ml-2 pl-1"
              >
                {item.children?.map(child => renderNavItem(child, true))}
              </motion.div>
            )}
          </AnimatePresence>
        )}
      </div>
    )
  }

  return (
    <div className="flex min-h-screen bg-gradient-to-br from-slate-200 via-slate-100 to-emerald-50 text-slate-800">
      {/* Sidebar - Desktop */}
      <aside className={`fixed inset-y-0 left-0 z-50 w-72 border-r border-emerald-900/20 bg-gradient-to-b from-emerald-900 via-emerald-800 to-teal-900 p-5 transition-transform md:static md:translate-x-0 md:rounded-r-3xl md:shadow-[0_20px_45px_-25px_rgba(6,78,59,0.85)] ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="mb-8 flex items-center gap-2 text-xl font-bold tracking-tight text-emerald-200">
          <div className="h-7 w-7 rounded-lg bg-emerald-300 shadow-[0_0_16px_rgba(110,231,183,0.7)]" />
          <span>ARIBA <span className="text-white text-base">HMS</span></span>
        </div>

        <nav className="space-y-1 pb-28">
          {menus.map(menu => renderNavItem(menu))}
        </nav>

        <div className="absolute bottom-6 left-6 right-6">
          <div className="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-xl">
             <div className="flex items-center gap-3 mb-4">
                <div className="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                  {user?.name?.[0]}
                </div>
                <div>
                   <p className="text-sm font-semibold text-white truncate max-w-[120px]">{user?.name}</p>
                   <p className="text-[10px] text-emerald-100/75 truncate max-w-[120px]">{user?.email}</p>
                </div>
             </div>
             <button
                onClick={onLogout}
                className="w-full rounded-xl border border-rose-200/30 bg-rose-100/10 py-2 text-xs font-bold text-rose-100 transition-all hover:bg-rose-500 hover:text-white"
              >
                Sign Out
              </button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex flex-1 flex-col">
        <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white/70 px-6 backdrop-blur-md">
          <div className="flex items-center gap-4">
            <button
               onClick={() => setIsSidebarOpen(true)}
               className="rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:text-slate-800 md:hidden"
            >
              <LucideIcons.Menu className="h-5 w-5" />
            </button>
            <h1 className="text-sm font-medium text-slate-500 uppercase tracking-widest">Workspace Terminal</h1>
          </div>
          
          <div className="flex items-center gap-4">
            {canSwitchRole && (
              <button className="hidden sm:inline-flex items-center rounded-full border border-teal-300 bg-teal-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-teal-700">
                <LucideIcons.UsersRound className="mr-1.5 h-3.5 w-3.5" />
                Role Switch
              </button>
            )}
            <div className="hidden sm:flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700">
              <LucideIcons.Bell className="h-3.5 w-3.5" />
              {notificationsCount} Notifications
            </div>
            <div className="hidden sm:inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-600">
              {dashboardRole || user?.role || 'user'}
            </div>
          </div>
        </header>

        <main className="flex-1 overflow-auto p-6 lg:p-8">
           <Outlet />
        </main>
      </div>

      {/* Mobile Sidebar Overlay */}
      {isSidebarOpen && (
        <div 
          className="fixed inset-0 z-40 bg-slate-900/45 backdrop-blur-sm md:hidden" 
          onClick={() => setIsSidebarOpen(false)}
        />
      )}
    </div>
  )
}