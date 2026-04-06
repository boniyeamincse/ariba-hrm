import { useForm } from 'react-hook-form'
import { NavLink, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/useAuth'
import { useState } from 'react'

type LoginInput = {
  email: string
  password: string
}

export function LoginPage() {
  const { register, handleSubmit, formState: { errors } } = useForm<LoginInput>()
  const { login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [showPassword, setShowPassword] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [authError, setAuthError] = useState<string | null>(null)

  const onSubmit = async (values: LoginInput) => {
    setIsLoading(true)
    setAuthError(null)
    try {
      await login(values)
      const from = (location.state as { from?: string } | null)?.from ?? '/dashboard'
      navigate(from)
    } catch (error) {
      setAuthError(error instanceof Error ? error.message : 'Unable to sign in.')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="space-y-2">
        <span className="inline-flex rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-300">
          Secure Staff Login
        </span>
        <h1 className="text-3xl font-bold tracking-tight text-white">Welcome back</h1>
        <p className="text-slate-400">Sign in to access your hospital workspace and live operations panel.</p>
      </div>

      <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 shadow-[0_20px_60px_-30px_rgba(16,185,129,0.35)]">
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">Work Email</label>
          <div className="relative group">
            <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500 group-focus-within:text-emerald-500 transition-colors">
              <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" /></svg>
            </div>
            <input 
              {...register('email', { required: "Email is required" })} 
              type="email"
              className="w-full rounded-2xl border border-white/10 bg-slate-900/80 py-3 pl-10 pr-4 text-white outline-none ring-offset-slate-950 transition-all focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20" 
              placeholder="name@hospital.com" 
            />
          </div>
          {errors.email && <p className="text-xs text-rose-500 ml-1">{errors.email.message}</p>}
        </div>

        <div className="space-y-2">
          <div className="flex items-center justify-between ml-1">
            <label className="text-sm font-medium text-slate-300">Password</label>
            <NavLink to="/auth/forgot-password" className="text-xs font-medium text-emerald-400 hover:text-emerald-300">
              Forgot?
            </NavLink>
          </div>
          <div className="relative group">
            <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500 group-focus-within:text-emerald-500 transition-colors">
              <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <input 
              {...register('password', { required: "Password is required" })} 
              type={showPassword ? 'text' : 'password'}
              className="w-full rounded-2xl border border-white/10 bg-slate-900/80 py-3 pl-10 pr-12 text-white outline-none ring-offset-slate-950 transition-all focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20" 
              placeholder="••••••••" 
            />
            <button 
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white transition-colors"
            >
              {showPassword ? (
                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
              ) : (
                <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              )}
            </button>
          </div>
          {errors.password && <p className="text-xs text-rose-500 ml-1">{errors.password.message}</p>}
        </div>

        <div className="flex items-center justify-between gap-2 px-1">
          <div className="flex items-center gap-2">
          <input type="checkbox" id="remember" className="rounded border-white/20 bg-white/10 text-emerald-500 focus:ring-emerald-500" />
          <label htmlFor="remember" className="text-xs text-slate-400">Keep me logged in for 30 days</label>
          </div>
          <span className="hidden text-[11px] text-slate-500 sm:inline">HIPAA-aware session guard enabled</span>
        </div>

        <button 
          type="submit" 
          disabled={isLoading}
          className="relative w-full overflow-hidden rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
        >
          {isLoading ? (
            <div className="flex items-center justify-center gap-2">
              <svg className="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <span>Authenticating...</span>
            </div>
          ) : (
            "Authenticate System"
          )}
        </button>

        {authError && (
          <p className="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs text-rose-300">
            {authError}
          </p>
        )}
        </form>

        <div className="mt-5 rounded-xl border border-white/10 bg-slate-900/60 px-3 py-2 text-[11px] text-slate-400">
          Tip: Use your assigned work email and role credentials. Super Admin accounts are granted global access across tenants.
        </div>
      </div>
    </div>
  )
}