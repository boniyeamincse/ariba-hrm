import { useState, useEffect } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'
import { motion, AnimatePresence } from 'framer-motion'
import * as LucideIcons from 'lucide-react'

type MenuItem = {
  id: number
  label: string
  icon: string | null
  route: string | null
  children?: MenuItem[]
}

export function DashboardLayout() {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false)
  const [menus, setMenus] = useState<MenuItem[]>([])
  const [expandedMenus, setExpandedMenus] = useState<number[]>([])
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    // Fetch menus from API
    fetch('/api/menus')
      .then(res => res.json())
      .then(data => setMenus(data))
      .catch(err => console.error('Menu fetch failed', err))
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
              `flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-all ${
                isActive 
                  ? 'bg-emerald-500 text-white shadow-[0_0_15px_rgba(16,185,129,0.3)]' 
                  : 'text-slate-400 hover:bg-white/5 hover:text-white'
              } ${isChild ? 'ml-4 py-1.5 opacity-80' : ''}`
            }
          >
            {renderIcon(item.icon)}
            <span>{item.label}</span>
          </NavLink>
        ) : (
          <button
            onClick={() => toggleExpand(item.id)}
            className={`flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-all text-slate-400 hover:bg-white/5 hover:text-white ${isChild ? 'ml-4 py-1.5 opacity-80' : ''}`}
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
                className="overflow-hidden"
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
    <div className="flex min-h-screen bg-slate-950 text-slate-100">
      {/* Sidebar - Desktop */}
      <aside className={`fixed inset-y-0 left-0 z-50 w-64 border-r border-white/5 bg-slate-950 p-6 transition-transform md:static md:translate-x-0 ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="mb-10 flex items-center gap-2 text-xl font-bold tracking-tight text-emerald-400">
          <div className="h-6 w-6 rounded-md bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.4)]" />
          <span>ARIBA <span className="text-white text-base">HMS</span></span>
        </div>

        <nav className="space-y-1">
          {menus.map(menu => renderNavItem(menu))}
        </nav>

        <div className="absolute bottom-6 left-6 right-6">
          <div className="rounded-2xl border border-white/5 bg-white/[0.02] p-4 backdrop-blur-xl">
             <div className="flex items-center gap-3 mb-4">
                <div className="h-10 w-10 rounded-full bg-slate-800 flex items-center justify-center text-emerald-400 font-bold">
                  {user?.name?.[0]}
                </div>
                <div>
                   <p className="text-sm font-semibold truncate max-w-[120px]">{user?.name}</p>
                   <p className="text-[10px] text-slate-500 truncate max-w-[120px]">{user?.email}</p>
                </div>
             </div>
             <button
                onClick={onLogout}
                className="w-full rounded-xl bg-rose-500/10 py-2 text-xs font-bold text-rose-500 transition-all hover:bg-rose-500 hover:text-white"
              >
                Sign Out
              </button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex flex-1 flex-col">
        <header className="flex h-16 items-center justify-between border-b border-white/5 bg-slate-950/50 px-6 backdrop-blur-md">
          <div className="flex items-center gap-4">
            <button
               onClick={() => setIsSidebarOpen(true)}
               className="rounded-lg border border-white/10 p-2 text-slate-400 hover:text-white md:hidden"
            >
              <LucideIcons.Menu className="h-5 w-5" />
            </button>
            <h1 className="text-sm font-medium text-slate-400 uppercase tracking-widest">Workspace Terminal</h1>
          </div>
          
          <div className="flex gap-4">
            <div className="hidden sm:flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-500">
               <div className="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
               LIVE CLINICAL SESSION
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
          className="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm md:hidden" 
          onClick={() => setIsSidebarOpen(false)}
        />
      )}
    </div>
  )
}