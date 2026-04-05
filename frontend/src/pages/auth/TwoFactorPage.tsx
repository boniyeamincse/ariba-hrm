import { useForm } from 'react-hook-form'

type TwoFactorInput = {
  code: string
}

export function TwoFactorPage() {
  const { register, handleSubmit } = useForm<TwoFactorInput>()

  const onSubmit = (values: TwoFactorInput) => {
    console.log('2fa verify', values)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Two-factor verification</h1>
      <p className="mt-1 text-sm text-slate-300">Enter the 6-digit code from your authenticator app.</p>
      <form onSubmit={handleSubmit(onSubmit)} className="mt-6 grid gap-4">
        <input {...register('code', { required: true })} maxLength={6} className="rounded-lg border border-white/20 bg-white/5 px-3 py-2 tracking-widest" placeholder="000000" />
        <button type="submit" className="rounded-lg bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-400">
          Verify Code
        </button>
      </form>
    </div>
  )
}