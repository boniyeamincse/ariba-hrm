import { useForm } from 'react-hook-form'

type ResetInput = {
  password: string
  confirmPassword: string
}

export function ResetPasswordPage() {
  const { register, handleSubmit } = useForm<ResetInput>()

  const onSubmit = (values: ResetInput) => {
    console.log('reset password', values)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Reset password</h1>
      <p className="mt-1 text-sm text-slate-300">Choose a strong new password for your workspace.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...register('password', { required: true })} type="password" className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="New password" />
        <input {...register('confirmPassword', { required: true })} type="password" className="rounded-lg border border-white/20 bg-white/5 px-3 py-2" placeholder="Confirm password" />
        <button type="submit" className="rounded-lg bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-400">
          Update Password
        </button>
      </form>
    </div>
  )
}