import { useForm } from 'react-hook-form'
import { NavLink } from 'react-router-dom'
import { useState } from 'react'

type ForgotInput = {
  email: string
}

export function ForgotPasswordPage() {
  const { register, handleSubmit, formState: { errors } } = useForm<ForgotInput>()
  const [isSubmitted, setIsSubmitted] = useState(false)
  const [isLoading, setIsLoading] = useState(false)

  const onSubmit = async (values: ForgotInput) => {
    setIsLoading(true)
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    console.log('forgot password', values)
    setIsSubmitted(true)
    setIsLoading(false)
  }

  if (isSubmitted) {
    return (
      <div className="space-y-6 text-center py-4">
        <div className="inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.2)]">
          <svg className="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white">Check your Inbox</h1>
          <p className="mt-2 text-slate-400">If an account exists, you'll receive a recovery link shortly.</p>
        </div>
        <NavLink to="/auth/login" className="inline-block w-full rounded-2xl bg-slate-800 py-3 font-bold text-white transition-all hover:bg-slate-700">
          Back to Terminal
        </NavLink>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-white">Passkey Recovery</h1>
        <p className="mt-2 text-slate-400">Locked out? Enter your registered email to restore clinical access.</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="space-y-2">
          <label className="text-sm font-medium text-slate-300 ml-1">Registered Email</label>
          <input 
            {...register('email', { required: "Email is required" })} 
            type="email"
            className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 px-4 text-white outline-none focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
            placeholder="admin@healthnexus.com" 
          />
          {errors.email && <p className="text-xs text-rose-500 ml-1">{errors.email.message}</p>}
        </div>

        <button 
          type="submit" 
          disabled={isLoading}
          className="w-full rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] active:scale-[0.98] disabled:opacity-70"
        >
          {isLoading ? "Validating..." : "Request Recovery Link"}
        </button>
      </form>

      <div className="text-center pt-2">
        <NavLink to="/auth/login" className="text-sm font-medium text-emerald-400 hover:text-emerald-300">
          Cancel and return to login
        </NavLink>
      </div>
    </div>
  )
}