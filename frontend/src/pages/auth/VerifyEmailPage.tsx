export function VerifyEmailPage() {
  return (
    <div>
      <h1 className="text-2xl font-bold text-white">Verify your email</h1>
      <p className="mt-2 text-sm text-slate-300">
        We sent a verification link to your inbox. Please verify to activate all workspace features.
      </p>
      <button type="button" className="mt-6 rounded-lg bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-400">
        Resend Verification
      </button>
    </div>
  )
}