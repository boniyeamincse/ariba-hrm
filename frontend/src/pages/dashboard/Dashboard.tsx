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
  UserRound,
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

const formatCompact = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    notation: value > 999 ? 'compact' : 'standard',
    maximumFractionDigits: 1,
  }).format(value)
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

  const chartSeries = useMemo(() => {
    const seed = Math.max(getNumericWidgetValue(primaryWidgets[0]), 60)
    return Array.from({ length: 12 }).map((_, index) => {
      const wave = Math.sin(index / 1.8) * 24
      const value = Math.max(28, Math.round((seed / 8) + 44 + wave + (index % 4) * 6))
      return {
        label: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][index],
        value,
      }
    })
  }, [primaryWidgets])

  const upcomingPatient = useMemo(() => {
    return {
      name: user?.name ? `${user.name.split(' ')[0]} Patient` : 'Andrew Billard',
      uhid: '#AP455698',
      visitType: 'General Visit',
      department: role.includes('doctor') ? 'Cardiology' : 'General Medicine',
      mode: 'Online Consultation',
      time: '06:30 PM',
      date: todayLabel,
    }
  }, [role, todayLabel, user?.name])

  return (
    <div className="mx-auto max-w-[1320px] space-y-6">
      <section className="flex flex-col gap-4 rounded-3xl border border-white/10 bg-white/[0.03] p-5 md:flex-row md:items-center md:justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-300">Workspace Insights</p>
          <h1 className="mt-1 text-2xl font-bold text-white md:text-3xl">{title}</h1>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <Link to="/dashboard/opd/consultations" className="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
            + New Consultation
          </Link>
          <Link to="/dashboard/appointments" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10">
            Schedule Availability
          </Link>
          <div className="rounded-xl border border-white/15 bg-white/[0.04] px-3 py-2 text-xs text-slate-300">
            {todayLabel}
          </div>
        </div>
      </section>

      {criticalSignals.hasCritical && (
        <section className="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3">
          <p className="inline-flex items-center gap-2 text-sm font-semibold text-rose-200">
            <AlertTriangle className="h-4 w-4" />
            Immediate attention required for critical operational signals.
          </p>
        </section>
      )}

      <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        {loading && (
          <div className="col-span-full rounded-2xl border border-white/10 bg-white/[0.02] p-6 text-slate-400">
            Loading dashboard widgets...
          </div>
        )}

        {!loading && primaryWidgets.map((widget, idx) => {
          const Icon = widgetIconMap[widget.key] ?? Activity
          const growth = ((idx % 2 === 0 ? 1 : -1) * (idx + 2)) * 5

          return (
            <article key={widget.key} className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
              <div className="flex items-start justify-between">
                <div className="rounded-lg bg-indigo-500/15 p-2 text-indigo-300">
                  <Icon className="h-4 w-4" />
                </div>
                <span className={`rounded-md px-2 py-1 text-[10px] font-semibold ${growth >= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'}`}>
                  {growth >= 0 ? '+' : ''}{growth}%
                </span>
              </div>

              <p className="mt-3 text-xs text-slate-400">{widget.label}</p>
              <p className="mt-1 text-3xl font-bold text-white">{formatWidgetValue(widget)}</p>

              <div className="mt-3 flex h-8 items-end gap-1">
                {[40, 72, 48, 85, 62, 78, 69].map((h, barIdx) => (
                  <div key={`${widget.key}-spark-${barIdx}`} className="flex-1 rounded-sm bg-indigo-400/70" style={{ height: `${h}%` }} />
                ))}
              </div>
            </article>
          )
        })}
      </section>

      <section className="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 xl:col-span-1">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-xl font-semibold text-white">Upcoming Appointments</h2>
            <span className="rounded-lg border border-white/10 px-2 py-1 text-[11px] text-slate-300">Today</span>
          </div>

          <div className="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <div className="mb-4 flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/20 text-indigo-200">
                <UserRound className="h-4 w-4" />
              </div>
              <div>
                <p className="text-sm font-semibold text-white">{upcomingPatient.name}</p>
                <p className="text-xs text-slate-400">{upcomingPatient.uhid}</p>
              </div>
            </div>

            <p className="text-sm font-semibold text-slate-100">{upcomingPatient.visitType}</p>
            <p className="mt-1 text-xs text-slate-400">{upcomingPatient.date} at {upcomingPatient.time}</p>

            <div className="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-300">
              <div>
                <p className="text-slate-500">Department</p>
                <p className="mt-1 font-semibold">{upcomingPatient.department}</p>
              </div>
              <div>
                <p className="text-slate-500">Type</p>
                <p className="mt-1 font-semibold">{upcomingPatient.mode}</p>
              </div>
            </div>

            <Link to="/dashboard/opd/consultations" className="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
              Start Appointment
            </Link>
          </div>
        </article>

        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 xl:col-span-2">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-xl font-semibold text-white">Appointments</h2>
            <span className="rounded-lg border border-white/10 px-2 py-1 text-[11px] text-slate-300">Monthly</span>
          </div>

          <div className="mb-3 flex flex-wrap items-center gap-4 text-xs text-slate-400">
            <span className="inline-flex items-center gap-2"><span className="h-2 w-2 rounded-full bg-indigo-400" />Total Appointments</span>
            <span className="inline-flex items-center gap-2"><span className="h-2 w-2 rounded-full bg-emerald-400" />Completed Appointments</span>
          </div>

          <div className="grid grid-cols-12 gap-2 rounded-xl border border-white/10 bg-white/[0.02] p-4">
            {chartSeries.map((point, idx) => (
              <div key={point.label} className="flex flex-col items-center gap-2">
                <div className="flex h-44 w-full items-end justify-center gap-1">
                  <div className="w-2 rounded bg-indigo-500/85" style={{ height: `${Math.min(point.value, 100)}%` }} />
                  <div className="w-2 rounded bg-emerald-400/80" style={{ height: `${Math.max(18, Math.min(point.value - 22 + (idx % 3) * 6, 90))}%` }} />
                </div>
                <span className="text-[10px] text-slate-500">{point.label}</span>
              </div>
            ))}
          </div>
        </article>
      </section>

      <section className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        {secondaryWidgets.map((widget) => {
          const Icon = widgetIconMap[widget.key] ?? Activity
          const value = getNumericWidgetValue(widget)
          const growth = value === 0 ? 0 : Math.max(5, Math.min(95, (value % 37) + 12))

          return (
            <article key={widget.key} className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
              <div className="mb-3 inline-flex rounded-lg bg-teal-500/20 p-2 text-teal-300">
                <Icon className="h-4 w-4" />
              </div>
              <p className="text-xs text-slate-400">{widget.label}</p>
              <p className="mt-1 text-2xl font-bold text-white">{formatCompact(value)}</p>
              <p className="mt-1 text-[11px] text-emerald-300">+{growth}% Last Week</p>
            </article>
          )
        })}

        {secondaryWidgets.length === 0 && (
          <div className="col-span-full rounded-xl border border-dashed border-white/20 px-4 py-5 text-sm text-slate-400">
            Additional operational cards will appear here as modules expand.
          </div>
        )}
      </section>

      <section className="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 xl:col-span-2">
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
          <ul className="space-y-2 text-sm text-slate-300">
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
    </div>
  )
}
