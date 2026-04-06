import { useForm } from 'react-hook-form'
import { NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/useAuth'
import { useState } from 'react'
import { motion } from 'framer-motion'

type RegisterInput = {
  companyName: string
  fullName: string
  email: string
  password: string
}

export function RegisterPage() {
  const { register: field, handleSubmit, formState: { errors } } = useForm<RegisterInput>()
  const { register } = useAuth()
  const navigate = useNavigate()
  const [isLoading, setIsLoading] = useState(false)

  const onSubmit = async (values: RegisterInput) => {
    setIsLoading(true)
    try {
      await register(values)
      navigate('/dashboard')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-white">Create Workspace</h1>
        <p className="mt-2 text-slate-400">Launch your clinical hub in just a few minutes.</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2 col-span-2 sm:col-span-1">
             <label className="text-sm font-medium text-slate-300 ml-1">Hospital / Clinic</label>
             <input 
              {...field('companyName', { required: "Required" })} 
              className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" 
              placeholder="Health Nexus" 
            />
             {errors.companyName && <p className="text-xs text-rose-500 ml-1">{errors.companyName.message}</p>}
          </div>
          <div className="space-y-2 col-span-2 sm:col-span-1">
             <label className="text-sm font-medium text-slate-300 ml-1">Administrator Name</label>
             <input 
              {...field('fullName', { required: "Required" })} 
              className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all text-sm" 
              placeholder="Dr. Jordan" 
            />
             {errors.fullName && <p className="text-xs text-rose-500 ml-1">{errors.fullName.message}</p>}
          </div>
        </div>

        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">Professional Email</label>
          <input 
            {...field('email', { required: "Email is required" })} 
            type="email"
            className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
            placeholder="admin@healthnexus.com" 
          />
          {errors.email && <p className="text-xs text-rose-500 ml-1">{errors.email.message}</p>}
        </div>

        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">Secure Password</label>
          <input 
            {...field('password', { required: "Password is required", minLength: { value: 8, message: "Min 8 characters" } })} 
            type="password"
            className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
            placeholder="••••••••" 
          />
          {errors.password && <p className="text-xs text-rose-500 ml-1">{errors.password.message}</p>}
        </div>

        <div className="flex items-center gap-2 px-1">
          <input type="checkbox" required id="terms" className="rounded border-white/20 bg-white/10 text-emerald-500 focus:ring-emerald-500" />
          <label htmlFor="terms" className="text-xs text-slate-400">I agree to the Clinical Governance & Privacy Policy.</label>
        </div>

        <button 
          type="submit" 
          disabled={isLoading}
          className="w-full rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] active:scale-[0.98] disabled:opacity-70"
        >
          {isLoading ? "Provisioning..." : "Provision HMS Workspace"}
        </button>
      </form>

      <div className="pt-2 text-center">
        <p className="text-sm text-slate-400">
          Managed by another admin?{" "}
          <NavLink to="/auth/login" className="font-bold text-emerald-400 hover:text-emerald-300">
            Sign In
          </NavLink>
        </p>
      </div>
    </div>
  )
}