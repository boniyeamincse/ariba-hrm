import { useState } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'

const navItems = [
  { to: '/dashboard', label: 'Overview' },
  { to: '/dashboard/employees', label: 'Employees' },
  { to: '/dashboard/attendance', label: 'Attendance' },
  { to: '/dashboard/payroll', label: 'Payroll' },
  { to: '/dashboard/leave', label: 'Leave' },
  { to: '/dashboard/recruitment', label: 'Recruitment' },
  { to: '/dashboard/settings', label: 'Settings' },
]

export function DashboardLayout() {
  const [open, setOpen] = useState(false)
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const onLogout = () => {
    logout()
    navigate('/auth/login')
  }

  return (
    <div className="grid min-h-screen bg-slate-100 md:grid-cols-[260px_1fr]">
      <aside
        className={`fixed inset-y-0 left-0 z-40 w-64 border-r border-slate-200 bg-white p-4 transition md:static md:w-auto ${
          open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        }`}
      >
        <div className="mb-6">
          <p className="text-lg font-semibold">Ariba HRM</p>
          <p className="text-xs text-slate-500">SaaS Workspace</p>
        </div>

        <nav className="grid gap-1">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              onClick={() => setOpen(false)}
              end={item.to === '/dashboard'}
              className={({ isActive }) =>
                `rounded-xl px-3 py-2 text-sm font-medium transition ${
                  isActive ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                }`
              }
            >
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
          <p className="font-semibold text-slate-800">{user?.name}</p>
          <p>{user?.email}</p>
          <button
            type="button"
            className="mt-3 rounded-lg bg-slate-900 px-3 py-1.5 font-medium text-white hover:bg-slate-800"
            onClick={onLogout}
          >
            Logout
          </button>
        </div>
      </aside>

      {open ? (
        <button
          type="button"
          className="fixed inset-0 z-30 bg-slate-950/30 md:hidden"
          onClick={() => setOpen(false)}
        />
      ) : null}

      <div className="flex min-h-screen flex-col">
        <header className="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:px-6">
          <button
            type="button"
            className="rounded-lg border border-slate-200 px-3 py-1 text-sm font-medium md:hidden"
            onClick={() => setOpen(true)}
          >
            Menu
          </button>
          <p className="text-sm font-medium text-slate-700">Ariba HRM Dashboard</p>
          <div className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
            Production
          </div>
        </header>
        <main className="flex-1 p-4 md:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}