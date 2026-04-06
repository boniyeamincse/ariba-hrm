import { motion, AnimatePresence } from 'framer-motion'
import { AlertTriangle, X, Check, Link as LinkIcon } from 'lucide-react'

type DuplicatePatient = {
  id: number
  uhid: string
  name: string
  phone: string
  date_of_birth?: string
}

type DuplicatePatientModalProps = {
  isOpen: boolean
  duplicate: DuplicatePatient | null
  newPatient: any
  onContinue: () => void
  onMerge: () => void
  onCancel: () => void
}

export function DuplicatePatientModal({
  isOpen,
  duplicate,
  newPatient,
  onContinue,
  onMerge,
  onCancel,
}: DuplicatePatientModalProps) {
  return (
    <AnimatePresence>
      {isOpen && duplicate && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onCancel}
            className="fixed inset-0 z-40 bg-black/80"
          />
          <motion.div
            initial={{ opacity: 0, scale: 0.9, y: 20 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.9, y: 20 }}
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
          >
            <div className="relative w-full max-w-lg rounded-3xl border border-white/10 bg-slate-950 p-8">
              <button
                onClick={onCancel}
                className="absolute right-4 top-4 rounded-lg p-1 hover:bg-white/10"
              >
                <X className="h-5 w-5 text-slate-400" />
              </button>

              {/* Header */}
              <div className="mb-6 flex items-start gap-4">
                <div className="rounded-full bg-orange-500/20 p-3">
                  <AlertTriangle className="h-6 w-6 text-orange-500" />
                </div>
                <div>
                  <h3 className="text-xl font-bold text-white">Potential Duplicate Patient</h3>
                  <p className="mt-1 text-sm text-slate-400">
                    A patient with similar information already exists in the system
                  </p>
                </div>
              </div>

              {/* Comparison */}
              <div className="mb-6 space-y-4">
                <div className="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/[0.02] p-4">
                  <div className="flex-1">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      Existing Patient
                    </p>
                    <p className="mt-2 text-lg font-bold text-white">{duplicate.name}</p>
                    <div className="mt-2 space-y-1 text-sm text-slate-400">
                      <p>
                        <span className="text-slate-500">UHID:</span>{' '}
                        <span className="font-mono text-emerald-400">{duplicate.uhid}</span>
                      </p>
                      <p>
                        <span className="text-slate-500">Phone:</span> {duplicate.phone}
                      </p>
                      {duplicate.date_of_birth && (
                        <p>
                          <span className="text-slate-500">DOB:</span> {duplicate.date_of_birth}
                        </p>
                      )}
                    </div>
                  </div>

                  <div className="flex-shrink-0">
                    <LinkIcon className="h-6 w-6 text-slate-600" />
                  </div>

                  <div className="flex-1">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      New Registration
                    </p>
                    <p className="mt-2 text-lg font-bold text-white">
                      {newPatient.first_name} {newPatient.last_name || ''}
                    </p>
                    <div className="mt-2 space-y-1 text-sm text-slate-400">
                      <p>
                        <span className="text-slate-500">UHID:</span>{' '}
                        <span className="font-mono text-emerald-400">{newPatient.uhid}</span>
                      </p>
                      <p>
                        <span className="text-slate-500">Phone:</span> {newPatient.phone}
                      </p>
                      {newPatient.date_of_birth && (
                        <p>
                          <span className="text-slate-500">DOB:</span>{' '}
                          {new Date(newPatient.date_of_birth).toLocaleDateString()}
                        </p>
                      )}
                    </div>
                  </div>
                </div>

                <div className="rounded-xl border border-yellow-500/20 bg-yellow-500/5 p-4">
                  <p className="text-sm text-yellow-300">
                    <span className="font-semibold">Match criteria:</span> Same first name, date of
                    birth, and phone number
                  </p>
                </div>
              </div>

              {/* Actions */}
              <div className="space-y-3">
                <button
                  onClick={onMerge}
                  className="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 font-semibold text-white transition-all hover:bg-emerald-600"
                >
                  <LinkIcon className="h-4 w-4" />
                  Merge Records
                </button>

                <button
                  onClick={onContinue}
                  className="w-full flex items-center justify-center gap-2 rounded-xl border border-white/10 px-6 py-3 font-semibold text-white transition-all hover:bg-white/5"
                >
                  <Check className="h-4 w-4" />
                  Create New Record Anyway
                </button>

                <button
                  onClick={onCancel}
                  className="w-full rounded-xl border border-white/10 px-6 py-2 font-semibold text-slate-400 transition-all hover:text-white hover:bg-white/5"
                >
                  Cancel
                </button>
              </div>

              <p className="mt-4 text-center text-xs text-slate-500">
                Contact your administrator if you believe this is an error.
              </p>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  )
}
