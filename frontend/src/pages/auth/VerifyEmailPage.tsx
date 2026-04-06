import { motion } from 'framer-motion'
import { NavLink } from 'react-router-dom'
import { useState } from 'react'

export function VerifyEmailPage() {
  const [isResending, setIsResending] = useState(false)

  const handleResend = async () => {
    setIsResending(true)
    await new Promise(resolve => setTimeout(resolve, 2000))
    setIsResending(false)
  }

  return (
    <div className="space-y-8 text-center py-4">
      <div className="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-500 shadow-[0_0_25px_rgba(16,185,129,0.2)]">
        <svg className="h-10 w-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
      </div>
      
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-white">Inbox Validation</h1>
        <p className="mt-4 text-slate-400 leading-relaxed">
          We have dispatched a cryptographic activation link to your registered inbox. 
          Please confirm your identity to initialize the workspace.
        </p>
      </div>

      <div className="space-y-4 pt-4">
        <button 
          onClick={handleResend}
          disabled={isResending}
          className="w-full rounded-2xl bg-emerald-500 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
        >
          {isResending ? (
            <div className="flex items-center justify-center gap-2 text-white">
              <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <span>Resending Activation Code...</span>
            </div>
          ) : (
            "Resend Activation Link"
          )}
        </button>

        <NavLink to="/auth/login" className="inline-block w-full text-sm font-medium text-slate-500 hover:text-emerald-400 transition-colors">
          Return to Login Terminal
        </NavLink>
      </div>

      <div className="pt-6 border-t border-white/5">
        <p className="text-xs text-slate-500 italic">
          Check your spam folder if you haven't received the link within 2 minutes.
        </p>
      </div>
    </div>
  )
}