import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import {
  Activity,
  AlertTriangle,
  BarChart3,
  Bed,
  Bell,
  Bot,
  Boxes,
  Building2,
  CalendarDays,
  ClipboardList,
  Download,
  Factory,
  FileText,
  FilePenLine,
  FileSearch,
  FlaskConical,
  HeartPulse,
  KeyRound,
  LineChart,
  ListOrdered,
  PackageCheck,
  Pill,
  PlugZap,
  QrCode,
  Receipt,
  Server,
  Settings,
  Shield,
  ShieldCheck,
  ShoppingCart,
  Siren,
  Stethoscope,
  TriangleAlert,
  Truck,
  UserPlus,
  Users,
  Users2,
  Webhook,
} from 'lucide-react'
import { api } from '../../lib/api'
import { useAuth } from '../../context/useAuth'

type DashboardWidget = {
  key: string
  label: string
  value: string | number
}

type RoleOverviewResponse = {
  role: string
  role_label?: string
  widgets: DashboardWidget[]
  quick_actions?: Array<{ label: string; route: string; icon?: string }>
  focus_areas?: string[]
}

const widgetIconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  total_hospitals: Users,
  active_tenants: Building2,
  global_users: Users,
  service_health: Activity,
  total_patients: Users,
  total_staff: Users,
  open_tasks: ClipboardList,
  today_registrations: UserPlus,
  appointments_today: CalendarDays,
  todays_appointments: CalendarDays,
  queue_waiting: ListOrdered,
  queue_with_doctor: Stethoscope,
  consultations_today: Stethoscope,
  prescriptions_today: Pill,
  pending_investigations: AlertTriangle,
  pending_tests: FileText,
  lab_pending: FlaskConical,
  completed_reports: FileText,
  lab_completed_today: FlaskConical,
  critical_labs: AlertTriangle,
  pharmacy_queue: Pill,
  inventory_low_stock: Boxes,
  revenue_today: Receipt,
  invoices_today: Receipt,
  outstanding_bills: Receipt,
  claims_open: ShieldCheck,
  bed_occupied: Bed,
  bed_available: Bed,
  attendance_rate: Users2,
  active_sessions: KeyRound,
  security_alerts: Shield,
  audit_events_today: FileSearch,
  api_calls_today: PlugZap,
  webhook_failures: Webhook,
  vitals_monitoring: HeartPulse,
  daily_revenue: Activity,
}

const quickActionIconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  Building2,
  LineChart,
  Shield,
  Users,
  ListOrdered,
  Receipt,
  Bed,
  Activity,
  BarChart3,
  HeartPulse,
  Factory,
  Stethoscope,
  CalendarDays,
  UserPlus,
  Pill,
  AlertTriangle,
  PackageCheck,
  FlaskConical,
  FilePenLine,
  QrCode,
  ShieldCheck,
  ClipboardList,
  Siren,
  FileSearch,
  PlugZap,
  Server,
  Boxes,
  ShoppingCart,
  Truck,
  Settings,
  Download,
  KeyRound,
  Webhook,
  Bot,
  TriangleAlert,
  Users2,
  Bell,
}

const formatWidgetValue = (widget: DashboardWidget): string => {
  if (typeof widget.value === 'number' && (widget.key.includes('revenue') || widget.key.includes('bills'))) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      maximumFractionDigits: 0,
    }).format(widget.value)
  }

  return String(widget.value)
}

export function Dashboard() {
  const { user } = useAuth()
  const [role, setRole] = useState('tenant-admin')
  const [roleLabel, setRoleLabel] = useState('Tenant Admin')
  const [widgets, setWidgets] = useState<DashboardWidget[]>([])
  const [quickActions, setQuickActions] = useState<Array<{ label: string; route: string; icon?: string }>>([])
  const [focusAreas, setFocusAreas] = useState<string[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const loadRoleDashboard = async () => {
      try {
        const response = await api.get<RoleOverviewResponse>('/dashboard/overview')
        setRole(response.data.role || 'tenant-admin')
        setRoleLabel(response.data.role_label || 'Tenant Admin')
        setWidgets(response.data.widgets || [])
        setQuickActions(response.data.quick_actions || [])
        setFocusAreas(response.data.focus_areas || [])
      } catch {
        setRole('tenant-admin')
        setRoleLabel('Tenant Admin')
        setWidgets([
          { key: 'total_patients', label: 'Total Patients', value: 0 },
          { key: 'appointments_today', label: 'Appointments Today', value: 0 },
        ])
        setQuickActions([
          { label: 'Open Patients', route: '/dashboard/patients', icon: 'UserRound' },
          { label: 'Open Appointments', route: '/dashboard/appointments', icon: 'CalendarDays' },
        ])
        setFocusAreas(['Operations', 'Quality'])
      } finally {
        setLoading(false)
      }
    }

    loadRoleDashboard()
  }, [])

  const title = useMemo(() => {
    return `${roleLabel} Dashboard`
  }, [roleLabel])

  return (
    <div className="space-y-8">
      <section className="rounded-3xl border border-white/10 bg-gradient-to-r from-slate-900 via-slate-900 to-emerald-900/40 p-6">
        <p className="mb-2 text-xs uppercase tracking-[0.2em] text-emerald-400">Multi-tenant SaaS HMS</p>
        <h1 className="text-3xl font-bold text-white">{title}</h1>
        <p className="mt-2 text-slate-300">
          Welcome {user?.name}. This dashboard is rendered dynamically from role-based API payloads.
        </p>
      </section>

      <section className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {loading && (
          <div className="col-span-full rounded-2xl border border-white/10 bg-white/[0.02] p-6 text-slate-400">
            Loading dashboard widgets...
          </div>
        )}

        {!loading && widgets.map((widget) => {
          const Icon = widgetIconMap[widget.key] ?? Activity

          return (
            <article key={widget.key} className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 shadow-sm">
              <div className="mb-4 flex items-center justify-between">
                <div className="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                  <Icon className="h-4 w-4" />
                </div>
                <span className="text-xs uppercase tracking-wide text-slate-400">Role widget</span>
              </div>
              <p className="text-sm text-slate-300">{widget.label}</p>
              <p className="mt-1 text-2xl font-semibold text-white">{formatWidgetValue(widget)}</p>
            </article>
          )
        })}
      </section>

      <section className="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article className="xl:col-span-2 rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-white">Quick Actions</h2>
          </div>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            {quickActions.map((action) => {
              const Icon = quickActionIconMap[action.icon || ''] ?? Activity

              return (
                <Link
                  key={`${action.route}-${action.label}`}
                  to={action.route}
                  className="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.02] px-4 py-3 text-sm text-slate-200 transition hover:bg-white/[0.06]"
                >
                  <span className="flex items-center gap-2">
                    <Icon className="h-4 w-4 text-emerald-400" />
                    {action.label}
                  </span>
                  <span className="text-xs text-slate-400">Open</span>
                </Link>
              )
            })}
          </div>
        </article>

        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <h2 className="mb-4 text-lg font-semibold text-white">Focus Areas</h2>
          <ul className="space-y-3 text-sm text-slate-300">
            {focusAreas.map((area) => (
              <li key={area} className="rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">{area}</li>
            ))}
          </ul>
          <p className="mt-4 text-xs uppercase tracking-wider text-slate-500">Active Role: {role}</p>
        </article>
      </section>
    </div>
  )
}
