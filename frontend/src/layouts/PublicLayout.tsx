import { NavLink, Outlet } from 'react-router-dom'

const links = [
  { to: '/', label: 'Home' },
  { to: '/features', label: 'Features' },
  { to: '/pricing', label: 'Pricing' },
  { to: '/about', label: 'About' },
  { to: '/contact', label: 'Contact' },
  { to: '/faq', label: 'FAQ' },
]

export function PublicLayout() {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <header className="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur">
        <div className="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-3">
          <NavLink to="/" className="text-lg font-semibold tracking-tight text-slate-900">
            Ariba HRM
          </NavLink>
          <nav className="hidden items-center gap-5 md:flex">
            {links.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                className={({ isActive }) =>
                  `text-sm font-medium ${isActive ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900'}`
                }
              >
                {link.label}
              </NavLink>
            ))}
          </nav>
          <div className="flex items-center gap-3">
            <NavLink to="/auth/login" className="text-sm font-medium text-slate-600 hover:text-slate-900">
              Login
            </NavLink>
            <NavLink
              to="/auth/register"
              className="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
              Start Free Trial
            </NavLink>
          </div>
        </div>
      </header>

      <main>
        <Outlet />
      </main>

      <footer className="border-t border-slate-200 bg-white">
        <div className="mx-auto flex w-full max-w-6xl flex-col gap-3 px-5 py-8 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
          <p>© {new Date().getFullYear()} Ariba HRM. Built for modern teams.</p>
          <div className="flex items-center gap-4">
            <NavLink to="/pricing" className="hover:text-slate-700">Pricing</NavLink>
            <NavLink to="/blog" className="hover:text-slate-700">Blog</NavLink>
            <NavLink to="/contact" className="hover:text-slate-700">Contact</NavLink>
          </div>
        </div>
      </footer>
    </div>
  )
}