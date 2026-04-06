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

const getNumericWidgetValue = (widget?: DashboardWidget): number => {
  if (!widget) {
    return 0
  }

  if (typeof widget.value === 'number') {
    return widget.value
  }

  const asNumber = Number(widget.value)
  return Number.isNaN(asNumber) ? 0 : asNumber
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

  const primaryWidgets = useMemo(() => widgets.slice(0, 4), [widgets])
  const secondaryWidgets = useMemo(() => widgets.slice(4, 10), [widgets])

  const criticalSignals = useMemo(() => {
    const securityAlerts = getNumericWidgetValue(widgets.find((w) => w.key === 'security_alerts'))
    const criticalLabs = getNumericWidgetValue(widgets.find((w) => w.key === 'critical_labs'))
    const pendingInvestigations = getNumericWidgetValue(widgets.find((w) => w.key === 'pending_investigations'))

    return {
      securityAlerts,
      criticalLabs,
      pendingInvestigations,
      hasCritical: securityAlerts > 0 || criticalLabs > 0,
    }
  }, [widgets])

  const todayLabel = useMemo(() => {
    return new Intl.DateTimeFormat('en-US', {
      weekday: 'long',
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    }).format(new Date())
  }, [])

  return (
    <div className="space-y-6">
      <section className="relative overflow-hidden rounded-3xl border border-white/10 bg-[linear-gradient(120deg,rgba(15,23,42,0.96),rgba(3,105,161,0.35)_45%,rgba(16,185,129,0.24))] p-6">
        <div className="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-emerald-400/20 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-sky-400/15 blur-3xl" />

        <div className="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p className="mb-2 text-[11px] uppercase tracking-[0.22em] text-emerald-300">MedCore Role Command</p>
            <h1 className="text-3xl font-bold text-white lg:text-4xl">{title}</h1>
            <p className="mt-2 max-w-2xl text-slate-200">
              Welcome {user?.name}. This view is generated from role-specific permissions, menu policies, and operational KPIs.
            </p>
          </div>

          <div className="grid grid-cols-2 gap-2 text-xs sm:w-auto">
            <div className="rounded-xl border border-white/15 bg-white/[0.06] px-3 py-2 text-slate-100">
              <p className="text-[10px] uppercase tracking-widest text-slate-300">Role</p>
              <p className="mt-1 font-semibold">{role}</p>
            </div>
            <div className="rounded-xl border border-white/15 bg-white/[0.06] px-3 py-2 text-slate-100">
              <p className="text-[10px] uppercase tracking-widest text-slate-300">Date</p>
              <p className="mt-1 font-semibold">{todayLabel}</p>
            </div>
          </div>
        </div>
      </section>

      {criticalSignals.hasCritical && (
        <section className="rounded-2xl border border-rose-500/35 bg-rose-500/10 px-4 py-3">
          <p className="inline-flex items-center gap-2 text-sm font-semibold text-rose-200">
            <AlertTriangle className="h-4 w-4" />
            Priority Alert: security/lab signals require immediate review.
          </p>
          <p className="mt-1 text-xs text-rose-100/90">
            Security alerts: {criticalSignals.securityAlerts} | Critical labs: {criticalSignals.criticalLabs} | Pending investigations: {criticalSignals.pendingInvestigations}
          </p>
        </section>
      )}

      <section className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {loading && (
          <div className="col-span-full rounded-2xl border border-white/10 bg-white/[0.02] p-6 text-slate-400">
            Loading dashboard widgets...
          </div>
        )}

        {!loading && primaryWidgets.map((widget) => {
          const Icon = widgetIconMap[widget.key] ?? Activity

          return (
            <article key={widget.key} className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 shadow-sm transition hover:bg-white/[0.05]">
              <div className="mb-4 flex items-center justify-between">
                <div className="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                  <Icon className="h-4 w-4" />
                </div>
                <span className="text-xs uppercase tracking-wide text-slate-400">KPI</span>
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
            <span className="text-xs uppercase tracking-wide text-slate-500">Role Authorized</span>
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

            {quickActions.length === 0 && (
              <div className="rounded-xl border border-dashed border-white/20 px-4 py-5 text-sm text-slate-400">
                No quick actions configured for this role yet.
              </div>
            )}
          </div>
        </article>

        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <h2 className="mb-4 text-lg font-semibold text-white">Focus Areas</h2>
          <ul className="space-y-3 text-sm text-slate-300">
            {focusAreas.map((area) => (
              <li key={area} className="rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">{area}</li>
            ))}
          </ul>
          {focusAreas.length === 0 && (
            <p className="rounded-lg border border-dashed border-white/20 px-3 py-2 text-sm text-slate-400">No focus areas configured yet.</p>
          )}
          <p className="mt-4 text-xs uppercase tracking-wider text-slate-500">Active Role: {role}</p>
        </article>
      </section>

      <section className="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article className="xl:col-span-2 rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-white">Operational Pulse</h2>
            <span className="text-xs uppercase tracking-wide text-slate-500">Live Snapshot</span>
          </div>

          <div className="space-y-3">
            {secondaryWidgets.map((widget) => {
              const Icon = widgetIconMap[widget.key] ?? Activity

              return (
                <div key={widget.key} className="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.02] px-4 py-3">
                  <p className="inline-flex items-center gap-2 text-sm text-slate-200">
                    <Icon className="h-4 w-4 text-emerald-400" />
                    {widget.label}
                  </p>
                  <span className="text-sm font-semibold text-white">{formatWidgetValue(widget)}</span>
                </div>
              )
            })}

            {secondaryWidgets.length === 0 && (
              <div className="rounded-xl border border-dashed border-white/20 px-4 py-5 text-sm text-slate-400">
                Additional operational widgets will appear here as modules expand.
              </div>
            )}
          </div>
        </article>

        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <h2 className="mb-4 text-lg font-semibold text-white">Governance Checks</h2>
          <ul className="space-y-2 text-sm text-slate-300">
            <li className="rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">Permission gating is backend-enforced.</li>
            <li className="rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">Menus are tenant-safe and role-filtered.</li>
            <li className="rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">Critical actions are audit-tagged.</li>
          </ul>
        </article>
      </section>
    </div>
  )
}
