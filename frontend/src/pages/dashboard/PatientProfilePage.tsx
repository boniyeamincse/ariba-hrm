import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import {
  ArrowLeft,
  User,
  Heart,
  Clock,
  Edit,
  Download,
  AlertCircle,
  Loader,
} from 'lucide-react'
import { api } from '../../lib/api'
import { PageShell } from '../../components/ui/PageShell'

type Patient = {
  id: number
  uhid: string
  first_name: string
  middle_name?: string
  last_name?: string
  date_of_birth?: string
  gender?: string
  blood_group?: string
  phone?: string
  email?: string
  national_id_no?: string
  passport_no?: string
  address?: string
  city?: string
  state?: string
  postal_code?: string
  country?: string
  marital_status?: string
  occupation?: string
  religion?: string
  emergency_contact_name?: string
  emergency_contact_phone?: string
  emergency_contact_relation?: string
  photo_thumb_url?: string
  created_at?: string
}

type PatientHistory = {
  id: number
  allergies?: string
  chronic_conditions?: string
  surgical_history?: string
  family_history?: string
  immunization_records?: string
}

type PatientVisit = {
  id: number
  visit_type: 'opd' | 'ipd' | 'emergency'
  reference_no?: string
  visit_at: string
  status: string
  summary?: string
}

type TabType = 'demographics' | 'history' | 'visits'

export function PatientProfilePage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [activeTab, setActiveTab] = useState<TabType>('demographics')
  const [patient, setPatient] = useState<Patient | null>(null)
  const [history, setHistory] = useState<PatientHistory | null>(null)
  const [visits, setVisits] = useState<PatientVisit[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const fetchPatientData = async () => {
      if (!id) return

      setIsLoading(true)
      setError('')

      try {
        // Fetch patient details
        const patientRes = await api.get(`/clinical/patients/${id}`)
        setPatient(patientRes.data.patient)
        setHistory(patientRes.data.patient.history)

        // Fetch visits
        const visitsRes = await api.get(`/clinical/patients/${id}/visits`)
        setVisits(visitsRes.data.data || [])
      } catch (err: any) {
        setError(err.response?.data?.message || 'Failed to load patient profile')
      } finally {
        setIsLoading(false)
      }
    }

    fetchPatientData()
  }, [id])

  if (isLoading) {
    return (
      <PageShell title="Loading" subtitle="Please wait...">
        <div className="flex items-center justify-center py-16">
          <Loader className="h-8 w-8 animate-spin text-emerald-500" />
        </div>
      </PageShell>
    )
  }

  if (error || !patient) {
    return (
      <PageShell title="Error" subtitle="Unable to load patient profile">
        <div className="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-6">
          <div className="flex gap-3">
            <AlertCircle className="h-6 w-6 flex-shrink-0 text-rose-500" />
            <div>
              <h3 className="font-semibold text-white">Failed to Load Profile</h3>
              <p className="mt-1 text-sm text-slate-300">{error}</p>
              <button
                onClick={() => navigate('/clinical/patients')}
                className="mt-4 rounded-lg bg-rose-500/20 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/30"
              >
                Back to Search
              </button>
            </div>
          </div>
        </div>
      </PageShell>
    )
  }

  return (
    <PageShell
      title={`${patient.first_name} ${patient.last_name || ''}`}
      subtitle={`Patient Profile • ${patient.uhid}`}
    >
      <div className="space-y-8">
        {/* Header with Photo */}
        <div className="flex flex-col gap-6 sm:flex-row sm:items-start">
          <button
            onClick={() => navigate('/clinical/patients')}
            className="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/5"
          >
            <ArrowLeft className="h-4 w-4" />
            Back
          </button>

          <div className="flex-1">
            <div className="flex flex-col gap-6 sm:flex-row">
              {patient.photo_thumb_url && (
                <img
                  src={patient.photo_thumb_url}
                  alt={patient.first_name}
                  className="h-32 w-32 rounded-2xl object-cover"
                />
              )}

              <div className="flex-1">
                <div className="mb-4 flex flex-wrap items-center gap-3">
                  <h2 className="text-3xl font-bold text-white">
                    {patient.first_name} {patient.last_name || ''}
                  </h2>
                  {patient.blood_group && (
                    <span className="inline-flex rounded-full bg-rose-500/10 px-3 py-1 text-sm font-semibold text-rose-400">
                      {patient.blood_group}
                    </span>
                  )}
                  {patient.gender && (
                    <span className="inline-flex rounded-full bg-blue-500/10 px-3 py-1 text-sm font-semibold text-blue-400">
                      {patient.gender}
                    </span>
                  )}
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-3">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      UHID
                    </p>
                    <p className="mt-1 font-mono text-lg font-bold text-emerald-400">
                      {patient.uhid}
                    </p>
                  </div>
                  {patient.phone && (
                    <div className="rounded-lg border border-white/10 bg-white/[0.02] p-3">
                      <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                        Phone
                      </p>
                      <p className="mt-1 text-lg font-semibold text-white">{patient.phone}</p>
                    </div>
                  )}
                </div>
              </div>

              <div className="flex flex-col gap-2 sm:w-fit">
                <button className="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 py-2 font-semibold text-white transition-all hover:bg-white/10">
                  <Edit className="h-4 w-4" />
                  Edit
                </button>
                <button className="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 py-2 font-semibold text-white transition-all hover:bg-white/10">
                  <Download className="h-4 w-4" />
                  Export
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="flex gap-2 border-b border-white/10">
          {(['demographics', 'history', 'visits'] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`flex items-center gap-2 px-4 py-3 font-semibold transition-all ${
                activeTab === tab
                  ? 'border-b-2 border-emerald-500 text-white'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              {tab === 'demographics' && <User className="h-4 w-4" />}
              {tab === 'history' && <Heart className="h-4 w-4" />}
              {tab === 'visits' && <Clock className="h-4 w-4" />}
              {tab.charAt(0).toUpperCase() + tab.slice(1)}
            </button>
          ))}
        </div>

        {/* Tab Content */}
        <AnimatePresence mode="wait">
          {activeTab === 'demographics' && (
            <motion.div
              key="demographics"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="space-y-4"
            >
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                  <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                    Date of Birth
                  </p>
                  <p className="mt-2 text-lg font-semibold text-white">
                    {patient.date_of_birth
                      ? new Date(patient.date_of_birth).toLocaleDateString()
                      : 'Not provided'}
                  </p>
                </div>

                {patient.email && (
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      Email
                    </p>
                    <p className="mt-2 text-lg font-semibold text-white">{patient.email}</p>
                  </div>
                )}

                {patient.national_id_no && (
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      National ID
                    </p>
                    <p className="mt-2 font-mono text-lg font-semibold text-white">
                      {patient.national_id_no}
                    </p>
                  </div>
                )}

                {patient.passport_no && (
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      Passport
                    </p>
                    <p className="mt-2 font-mono text-lg font-semibold text-white">
                      {patient.passport_no}
                    </p>
                  </div>
                )}

                {patient.marital_status && (
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      Marital Status
                    </p>
                    <p className="mt-2 text-lg font-semibold text-white">{patient.marital_status}</p>
                  </div>
                )}

                {patient.occupation && (
                  <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                    <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                      Occupation
                    </p>
                    <p className="mt-2 text-lg font-semibold text-white">{patient.occupation}</p>
                  </div>
                )}
              </div>

              {patient.address && (
                <div className="rounded-lg border border-white/10 bg-white/[0.02] p-4">
                  <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">
                    Address
                  </p>
                  <p className="mt-2 text-white">
                    {patient.address}
                    {patient.city && `, ${patient.city}`}
                    {patient.state && `, ${patient.state}`}
                    {patient.postal_code && ` ${patient.postal_code}`}
                  </p>
                </div>
              )}

              {patient.emergency_contact_name && (
                <div className="rounded-lg border border-orange-500/20 bg-orange-500/5 p-4">
                  <p className="text-xs font-semibold uppercase tracking-widest text-orange-500">
                    Emergency Contact
                  </p>
                  <div className="mt-3 space-y-1 text-white">
                    <p className="font-semibold">{patient.emergency_contact_name}</p>
                    {patient.emergency_contact_phone && (
                      <p className="text-sm text-slate-300">{patient.emergency_contact_phone}</p>
                    )}
                    {patient.emergency_contact_relation && (
                      <p className="text-sm text-slate-400">{patient.emergency_contact_relation}</p>
                    )}
                  </div>
                </div>
              )}
            </motion.div>
          )}

          {activeTab === 'history' && (
            <motion.div
              key="history"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="space-y-4"
            >
              {history ? (
                <>
                  {history.allergies && (
                    <div className="rounded-lg border border-rose-500/20 bg-rose-500/5 p-4">
                      <p className="text-xs font-semibold uppercase tracking-widest text-rose-500">
                        Allergies
                      </p>
                      <p className="mt-2 text-white">{history.allergies}</p>
                    </div>
                  )}

                  {history.chronic_conditions && (
                    <div className="rounded-lg border border-blue-500/20 bg-blue-500/5 p-4">
                      <p className="text-xs font-semibold uppercase tracking-widest text-blue-500">
                        Chronic Conditions
                      </p>
                      <p className="mt-2 text-white">{history.chronic_conditions}</p>
                    </div>
                  )}

                  {history.surgical_history && (
                    <div className="rounded-lg border border-purple-500/20 bg-purple-500/5 p-4">
                      <p className="text-xs font-semibold uppercase tracking-widest text-purple-500">
                        Surgical History
                      </p>
                      <p className="mt-2 text-white">{history.surgical_history}</p>
                    </div>
                  )}

                  {history.family_history && (
                    <div className="rounded-lg border border-amber-500/20 bg-amber-500/5 p-4">
                      <p className="text-xs font-semibold uppercase tracking-widest text-amber-500">
                        Family History
                      </p>
                      <p className="mt-2 text-white">{history.family_history}</p>
                    </div>
                  )}

                  {history.immunization_records && (
                    <div className="rounded-lg border border-green-500/20 bg-green-500/5 p-4">
                      <p className="text-xs font-semibold uppercase tracking-widest text-green-500">
                        Immunization Records
                      </p>
                      <p className="mt-2 text-white">{history.immunization_records}</p>
                    </div>
                  )}

                  {!history.allergies &&
                    !history.chronic_conditions &&
                    !history.surgical_history &&
                    !history.family_history &&
                    !history.immunization_records && (
                      <div className="rounded-lg border border-dashed border-white/10 p-8 text-center">
                        <p className="text-slate-400">No medical history recorded</p>
                      </div>
                    )}
                </>
              ) : (
                <div className="rounded-lg border border-dashed border-white/10 p-8 text-center">
                  <p className="text-slate-400">No medical history available</p>
                </div>
              )}
            </motion.div>
          )}

          {activeTab === 'visits' && (
            <motion.div
              key="visits"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="space-y-3"
            >
              {visits.length > 0 ? (
                visits.map((visit) => (
                  <div
                    key={visit.id}
                    className="rounded-lg border border-white/10 bg-white/[0.02] p-4"
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div className="flex-1">
                        <div className="flex items-center gap-2">
                          <span className="inline-flex rounded-full bg-emerald-500/20 px-2 py-1 text-xs font-semibold text-emerald-400">
                            {visit.visit_type.toUpperCase()}
                          </span>
                          {visit.reference_no && (
                            <span className="text-sm font-mono text-slate-400">{visit.reference_no}</span>
                          )}
                        </div>
                        <p className="mt-2 text-white font-semibold">
                          {new Date(visit.visit_at).toLocaleDateString()} at{' '}
                          {new Date(visit.visit_at).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </p>
                        {visit.summary && (
                          <p className="mt-2 text-sm text-slate-400">{visit.summary}</p>
                        )}
                      </div>
                      <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                        visit.status === 'active'
                          ? 'bg-emerald-500/20 text-emerald-400'
                          : 'bg-slate-500/20 text-slate-400'
                      }`}>
                        {visit.status}
                      </span>
                    </div>
                  </div>
                ))
              ) : (
                <div className="rounded-lg border border-dashed border-white/10 p-8 text-center">
                  <p className="text-slate-400">No visits recorded</p>
                </div>
              )}
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </PageShell>
  )
}
