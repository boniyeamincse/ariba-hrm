import { useForm } from 'react-hook-form'
import { NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/useAuth'

type RegisterInput = {
  companyName: string
  fullName: string
  email: string
  password: string
}

export function RegisterPage() {
  const { register: field, handleSubmit } = useForm<RegisterInput>()
  const { register } = useAuth()
  const navigate = useNavigate()

  const onSubmit = async (values: RegisterInput) => {
    await register(values)
    navigate('/dashboard')
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Create HMS Workspace</h1>
      <p className="mt-1 text-sm text-slate-300">Launch your clinical trial in minutes.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...field('companyName', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="Hospital or Clinic Name" />
        <input {...field('fullName', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="Your Full Name" />
        <input {...field('email', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="Work Email" />
        <input {...field('password', { required: true })} type="password" className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="Create Password" />
        <button type="submit" className="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white transition-all hover:scale-[1.02] hover:bg-emerald-400">
          Start Medical Trial
        </button>
      </form>
      <p className="mt-4 text-xs text-slate-300">
        Already have an account? <NavLink to="/auth/login" className="hover:text-white">Login</NavLink>
      </p>
    </div>
  )
}