import { motion } from 'framer-motion'
import { NavLink, Outlet } from 'react-router-dom'

export function AuthLayout() {
  return (
    <div className="relative grid min-h-screen place-items-center overflow-hidden bg-slate-950 px-4 py-8 text-slate-100">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(99,102,241,0.35),transparent_32%),radial-gradient(circle_at_80%_75%,rgba(168,85,247,0.32),transparent_28%)]" />
      <motion.div
        initial={{ opacity: 0, y: 12 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative w-full max-w-md rounded-2xl border border-white/10 bg-white/8 p-6 shadow-2xl backdrop-blur"
      >
        <NavLink to="/" className="mb-5 inline-flex text-sm font-medium text-indigo-200 hover:text-indigo-100">
          ← Back to homepage
        </NavLink>
        <Outlet />
      </motion.div>
    </div>
  )
}