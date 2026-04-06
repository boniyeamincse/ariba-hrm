import { motion } from 'framer-motion'
import { NavLink, Outlet } from 'react-router-dom'

export function AuthLayout() {
  return (
    <div className="flex min-h-screen overflow-hidden bg-slate-950 text-slate-100">
      {/* Hero Section - Split Screen Left */}
      <div className="relative hidden w-1/2 flex-col lg:flex">
        <img 
          src="/assets/images/login_bg.png" 
          alt="Medical Diagnostic Hub" 
          className="absolute inset-0 h-full w-full object-cover opacity-60 mix-blend-overlay"
        />
        <div className="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900/40 to-transparent" />
        
        <div className="relative z-10 flex flex-1 flex-col justify-between p-12">
          <div>
            <div className="flex items-center gap-2 text-2xl font-bold tracking-tight text-emerald-400">
              <div className="h-8 w-8 rounded-lg bg-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.4)]" />
              <span>ARIBA <span className="text-white">HMS</span></span>
            </div>
          </div>

          <motion.div 
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.2 }}
            className="max-w-lg"
          >
            <h1 className="text-5xl font-extrabold leading-tight tracking-tighter text-white">
              Next-Gen Medical <br />
              <span className="text-emerald-400">Intelligence.</span>
            </h1>
            <p className="mt-6 text-lg leading-relaxed text-slate-300">
              Experience the power of a unified hospital management ecosystem. 
              Efficiency, accuracy, and care—synchronized in real-time.
            </p>
          </motion.div>

          <div className="text-sm text-slate-500">
            © 2026 Ariba Health Systems. Secure Clinical Environment.
          </div>
        </div>
      </div>

      {/* Form Section - Split Screen Right */}
      <div className="relative flex w-full flex-col items-center justify-center bg-slate-950 lg:w-1/2">
        <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_50%,rgba(16,185,129,0.15),transparent_70%)]" />
        
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.4 }}
          className="relative w-full max-w-md px-8"
        >
          <div className="mb-8 flex lg:hidden">
             <div className="flex items-center gap-2 text-2xl font-bold tracking-tight text-emerald-400">
              <div className="h-8 w-8 rounded-lg bg-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.4)]" />
              <span>ARIBA <span className="text-white">HMS</span></span>
            </div>
          </div>
          
          <NavLink to="/" className="mb-10 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition-colors hover:text-emerald-400">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Return to Gateway
          </NavLink>

          <div className="mb-6 inline-flex rounded-xl border border-white/10 bg-white/5 p-1">
            <NavLink
              to="/auth/login"
              className={({ isActive }) =>
                `rounded-lg px-4 py-2 text-sm font-semibold transition ${isActive ? 'bg-emerald-500 text-white' : 'text-slate-300 hover:text-white'}`
              }
            >
              Login
            </NavLink>
          </div>

          <div className="">
             <Outlet />
          </div>
        </motion.div>
      </div>
    </div>
  )
}