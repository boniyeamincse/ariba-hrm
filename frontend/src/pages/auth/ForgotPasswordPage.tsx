import { useForm } from 'react-hook-form'

type ForgotInput = {
  email: string
}

export function ForgotPasswordPage() {
  const { register, handleSubmit } = useForm<ForgotInput>()

  const onSubmit = (values: ForgotInput) => {
    console.log('forgot password', values)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Forgot password</h1>
      <p className="mt-1 text-sm text-slate-300">We will send a reset link to your email.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...register('email', { required: true })} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="you@company.com" />
        <button type="submit" className="rounded-lg bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-400">
          Send Reset Link
        </button>
      </form>
    </div>
  )
}