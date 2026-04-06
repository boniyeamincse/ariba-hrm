import { useState, useCallback, useRef, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { Search, Plus, User, Phone, Calendar, ArrowRight } from 'lucide-react'
import { api } from '../../lib/api'
import { PageShell } from '../../components/ui/PageShell'

type Patient = {
  id: number
  uhid: string
  first_name: string
  last_name?: string
  phone?: string
  date_of_birth?: string
  gender?: string
  blood_group?: string
}

export function PatientSearchPage() {
  const [query, setQuery] = useState('')
  const [patients, setPatients] = useState<Patient[]>([])
  const [isLoading, setIsLoading] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)
  const searchTimeoutRef = useRef<NodeJS.Timeout>()
  const navigate = useNavigate()

  // Debounced search
  useEffect(() => {
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current)
    }

    if (!query.trim()) {
      setPatients([])
      setHasSearched(false)
      return
    }

    setIsLoading(true)
    setHasSearched(true)

    searchTimeoutRef.current = setTimeout(async () => {
      try {
        const response = await api.get(`/clinical/patients?q=${encodeURIComponent(query)}`)
        setPatients(response.data.data || [])
      } catch (error) {
        console.error('Search failed:', error)
      } finally {
        setIsLoading(false)
      }
    }, 300)
  }, [query])

  const handlePatientSelect = (patient: Patient) => {
    navigate(`/clinical/patients/${patient.id}`)
  }

  return (
    <PageShell
      title="Patient Search"
      subtitle="Find existing patients or register a new one"
    >
      <div className="space-y-8">
        {/* Search & Register Bar */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          <div className="relative flex-1">
            <Search className="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />
            <input
              type="text"
              placeholder="Search by name, UHID, phone, or ID number..."
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              className="w-full rounded-2xl border border-white/10 bg-white/5 py-3 pl-12 pr-4 text-white placeholder-slate-500 transition-all hover:bg-white/10 focus:bg-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            />
          </div>
          <button
            onClick={() => navigate('/clinical/patients/register')}
            className="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-6 py-3 font-semibold text-white transition-all hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20"
          >
            <Plus className="h-5 w-5" />
            New Patient
          </button>
        </div>

        {/* Search Results */}
        <AnimatePresence mode="wait">
          {isLoading ? (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="flex items-center justify-center py-16"
            >
              <div className="h-8 w-8 rounded-full border-2 border-emerald-500/20 border-t-emerald-500 animate-spin" />
            </motion.div>
          ) : hasSearched && patients.length === 0 ? (
            <motion.div
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-500/30 py-16 text-center"
            >
              <User className="mb-4 h-12 w-12 text-slate-600" />
              <p className="mb-2 text-lg font-semibold text-white">No patients found</p>
              <p className="mb-6 text-sm text-slate-400">Try refining your search or register a new patient</p>
              <button
                onClick={() => navigate('/clinical/patients/register')}
                className="inline-flex items-center gap-2 rounded-xl bg-emerald-500/20 px-4 py-2 font-semibold text-emerald-400 transition-all hover:bg-emerald-500/30"
              >
                <Plus className="h-4 w-4" />
                Register New Patient
              </button>
            </motion.div>
          ) : hasSearched && patients.length > 0 ? (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="space-y-3"
            >
              <p className="text-sm font-semibold text-slate-400">{patients.length} result(s) found</p>
              {patients.map((patient, i) => (
                <motion.div
                  key={patient.id}
                  initial={{ opacity: 0, x: -10 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.05 }}
                  onClick={() => handlePatientSelect(patient)}
                  className="group cursor-pointer rounded-2xl border border-white/10 bg-white/[0.02] p-5 transition-all hover:border-emerald-500/30 hover:bg-white/5"
                >
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4 flex-1">
                      <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <User className="h-6 w-6" />
                      </div>
                      <div className="flex-1">
                        <p className="font-semibold text-white">
                          {patient.first_name} {patient.last_name || ''}
                        </p>
                        <div className="mt-1 flex flex-wrap gap-3 text-xs text-slate-400">
                          <span className="inline-flex items-center gap-1">
                            <span className="font-mono text-emerald-500">{patient.uhid}</span>
                          </span>
                          {patient.phone && (
                            <span className="inline-flex items-center gap-1">
                              <Phone className="h-3 w-3" />
                              {patient.phone}
                            </span>
                          )}
                          {patient.date_of_birth && (
                            <span className="inline-flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              {new Date(patient.date_of_birth).toLocaleDateString()}
                            </span>
                          )}
                          {patient.blood_group && (
                            <span className="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2 text-rose-400">
                              {patient.blood_group}
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                    <ArrowRight className="h-5 w-5 text-slate-600 transition-all group-hover:text-emerald-500 group-hover:translate-x-1" />
                  </div>
                </motion.div>
              ))}
            </motion.div>
          ) : null}
        </AnimatePresence>

        {/* Quick Actions */}
        {!hasSearched && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="rounded-3xl border border-white/5 bg-white/[0.02] p-8"
          >
            <h3 className="mb-6 font-semibold text-white">Quick Actions</h3>
            <div className="grid gap-4">
              <button
                onClick={() => navigate('/clinical/patients/register?tab=walk-in')}
                className="rounded-xl border border-white/10 bg-white/5 p-4 text-left transition-all hover:border-emerald-500/30 hover:bg-white/10"
              >
                <p className="font-semibold text-white">Register Walk-in Patient</p>
                <p className="mt-1 text-sm text-slate-400">Quick registration for new patients arriving without appointment</p>
              </button>
              <button
                onClick={() => navigate('/clinical/patients/register?tab=pre-registered')}
                className="rounded-xl border border-white/10 bg-white/5 p-4 text-left transition-all hover:border-emerald-500/30 hover:bg-white/10"
              >
                <p className="font-semibold text-white">Register Pre-Registered Patient</p>
                <p className="mt-1 text-sm text-slate-400">Complete registration for patients who signed up online</p>
              </button>
            </div>
          </motion.div>
        )}
      </div>
    </PageShell>
  )
}
