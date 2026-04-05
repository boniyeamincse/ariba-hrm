import { useForm } from 'react-hook-form'
import { NavLink, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/useAuth'

type LoginInput = {
  email: string
  password: string
}

export function LoginPage() {
  const { register, handleSubmit } = useForm<LoginInput>()
  const { login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()

  const onSubmit = async (values: LoginInput) => {
    await login(values)
    const from = (location.state as { from?: string } | null)?.from ?? '/dashboard'
    navigate(from)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Welcome back</h1>
      <p className="mt-1 text-sm text-slate-300">Login to your Ariba HMS workspace.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...register('email', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="you@hospital.com" />
        <input {...register('password', { required: true })} type="password" className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-white outline-none focus:border-emerald-500/50" placeholder="Password" />
        <button type="submit" className="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white transition-all hover:scale-[1.02] hover:bg-emerald-400">
          Login to System
        </button>
      </form>
      <div className="mt-4 flex justify-between text-xs text-slate-300">
        <NavLink to="/auth/forgot-password" className="hover:text-white">Forgot password?</NavLink>
        <NavLink to="/auth/register" className="hover:text-white">Create account</NavLink>
      </div>
    </div>
  )
}