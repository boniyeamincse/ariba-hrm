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
      <h1 className="text-2xl font-bold text-white">Create workspace</h1>
      <p className="mt-1 text-sm text-slate-300">Start your company trial in minutes.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...field('companyName', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="Company name" />
        <input {...field('fullName', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="Your full name" />
        <input {...field('email', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="Work email" />
        <input {...field('password', { required: true })} type="password" className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="Create password" />
        <button type="submit" className="rounded-lg bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-400">
          Start Free Trial
        </button>
      </form>
      <p className="mt-4 text-xs text-slate-300">
        Already have an account? <NavLink to="/auth/login" className="hover:text-white">Login</NavLink>
      </p>
    </div>
  )
}