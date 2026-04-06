import { useForm } from 'react-hook-form'
import { NavLink, useNavigate } from 'react-router-dom'
import { useState } from 'react'

type ResetInput = {
  password: string
  confirmPassword: string
}

export function ResetPasswordPage() {
  const { register, handleSubmit, formState: { errors } } = useForm<ResetInput>()
  const navigate = useNavigate()
  const [isLoading, setIsLoading] = useState(false)

  const onSubmit = async (values: ResetInput) => {
    setIsLoading(true)
    await new Promise(resolve => setTimeout(resolve, 1500))
    console.log('reset password', values)
    setIsLoading(false)
    navigate('/auth/login')
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-white">Secure Reset</h1>
        <p className="mt-2 text-slate-400">Establish a new, high-entropy password for your HMS workspace.</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">New Password</label>
          <input 
            {...register('password', { required: "Required", minLength: 8 })} 
            type="password"
            className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all font-mono" 
            placeholder="••••••••" 
          />
        </div>

        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">Confirm New Password</label>
          <input 
            {...register('confirmPassword', { required: "Required" })} 
            type="password"
            className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all font-mono" 
            placeholder="••••••••" 
          />
        </div>

        <button 
          type="submit" 
          disabled={isLoading}
          className="w-full rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] active:scale-[0.98] disabled:opacity-70"
        >
          {isLoading ? "Updating Security..." : "Reset System Access"}
        </button>
      </form>

      <div className="text-center">
        <NavLink to="/auth/login" className="text-sm font-medium text-emerald-400 hover:text-emerald-300">
           Return to Terminal
        </NavLink>
      </div>
    </div>
  )
}