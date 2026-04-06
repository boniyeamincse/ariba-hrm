import { useEffect, useMemo, useState } from 'react'
import { Activity, CalendarDays, FileText, HeartPulse, Pill, UsersRound } from 'lucide-react'
import { api } from '../../lib/api'
import { useAuth } from '../../context/useAuth'

type DashboardWidget = {
  key: string
  label: string
  value: string | number
}

type RoleOverviewResponse = {
  role: string
  widgets: DashboardWidget[]
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  total_hospitals: UsersRound,
  total_patients: UsersRound,
  total_staff: UsersRound,
  appointments_today: CalendarDays,
  todays_appointments: CalendarDays,
  recent_prescriptions: Pill,
  pending_tests: FileText,
  completed_reports: FileText,
  vitals_monitoring: HeartPulse,
  daily_revenue: Activity,
}

export function Dashboard() {
  const { user } = useAuth()
  const [role, setRole] = useState('tenant-admin')
  const [widgets, setWidgets] = useState<DashboardWidget[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const loadRoleDashboard = async () => {
      try {
        const response = await api.get<RoleOverviewResponse>('/dashboard/overview')
        setRole(response.data.role || 'tenant-admin')
        setWidgets(response.data.widgets || [])
      } catch {
        setRole('tenant-admin')
        setWidgets([
          { key: 'total_patients', label: 'Total Patients', value: 0 },
          { key: 'appointments_today', label: 'Appointments Today', value: 0 },
        ])
      } finally {
        setLoading(false)
      }
    }

    loadRoleDashboard()
  }, [])

  const title = useMemo(() => {
    const displayRole = role.replace(/-/g, ' ')
    return `${displayRole.charAt(0).toUpperCase()}${displayRole.slice(1)} Dashboard`
  }, [role])

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
          const Icon = iconMap[widget.key] ?? Activity

          return (
            <article key={widget.key} className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 shadow-sm">
              <div className="mb-4 flex items-center justify-between">
                <div className="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                  <Icon className="h-4 w-4" />
                </div>
                <span className="text-xs uppercase tracking-wide text-slate-400">Role widget</span>
              </div>
              <p className="text-sm text-slate-300">{widget.label}</p>
              <p className="mt-1 text-2xl font-semibold text-white">{widget.value}</p>
            </article>
          )
        })}
      </section>

      <section className="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article className="xl:col-span-2 rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-white">Activity Table</h2>
            <button className="rounded-lg border border-white/10 px-3 py-1 text-xs text-slate-300 hover:bg-white/5">
              Filter
            </button>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead>
                <tr className="border-b border-white/10 text-left text-slate-400">
                  <th className="px-2 py-2">Module</th>
                  <th className="px-2 py-2">Status</th>
                  <th className="px-2 py-2">Updated</th>
                </tr>
              </thead>
              <tbody>
                <tr className="border-b border-white/5 text-slate-200">
                  <td className="px-2 py-3">Appointments</td>
                  <td className="px-2 py-3">Active</td>
                  <td className="px-2 py-3">2 min ago</td>
                </tr>
                <tr className="border-b border-white/5 text-slate-200">
                  <td className="px-2 py-3">Billing</td>
                  <td className="px-2 py-3">Synced</td>
                  <td className="px-2 py-3">6 min ago</td>
                </tr>
                <tr className="text-slate-200">
                  <td className="px-2 py-3">Lab Reports</td>
                  <td className="px-2 py-3">Pending</td>
                  <td className="px-2 py-3">10 min ago</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
          <h2 className="mb-4 text-lg font-semibold text-white">Quick Notes</h2>
          <ul className="space-y-3 text-sm text-slate-300">
            <li>Role and menu rendering are API-driven.</li>
            <li>Tenant-scoped data is isolated server-side.</li>
            <li>Widget modules are reusable and extensible.</li>
          </ul>
        </article>
      </section>
    </div>
  )
}
