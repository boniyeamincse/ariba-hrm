import { useEffect, useMemo, useState } from 'react'
import { CalendarClock, FileText, Loader2, Search, Save, Stethoscope } from 'lucide-react'
import { api } from '../../lib/api'

type IcdResult = {
  code: string
  label: string
}

type ConsultationForm = {
  patient_id: string
  opd_queue_id: string
  subjective: string
  objective: string
  assessment: string
  plan: string
  icd10_code: string
  follow_up_at: string
}

const initialForm: ConsultationForm = {
  patient_id: '',
  opd_queue_id: '',
  subjective: '',
  objective: '',
  assessment: '',
  plan: '',
  icd10_code: '',
  follow_up_at: '',
}

export function OpdConsultationPage() {
  const [form, setForm] = useState<ConsultationForm>(initialForm)
  const [icdQuery, setIcdQuery] = useState('')
  const [icdResults, setIcdResults] = useState<IcdResult[]>([])
  const [icdLoading, setIcdLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)

  const setField = (key: keyof ConsultationForm, value: string) => {
    setForm((prev) => ({ ...prev, [key]: value }))
  }

  const selectedDiagnosis = useMemo(() => {
    if (!form.icd10_code) return null

    return icdResults.find((item) => item.code === form.icd10_code) ?? null
  }, [form.icd10_code, icdResults])

  useEffect(() => {
    if (icdQuery.trim().length < 2) {
      setIcdResults([])
      return
    }

    const timer = window.setTimeout(async () => {
      setIcdLoading(true)

      try {
        const response = await api.get<{ data: IcdResult[] }>('/clinical/opd/icd10/search', {
          params: { q: icdQuery.trim() },
        })

        setIcdResults(response.data.data ?? [])
      } catch {
        setIcdResults([])
      } finally {
        setIcdLoading(false)
      }
    }, 350)

    return () => window.clearTimeout(timer)
  }, [icdQuery])

  const submitConsultation = async () => {
    if (!form.patient_id.trim()) {
      setError('Patient ID is required.')
      return
    }

    setSubmitting(true)
    setError(null)
    setSuccess(null)

    try {
      const response = await api.post('/clinical/opd/consultations', {
        patient_id: Number(form.patient_id),
        opd_queue_id: form.opd_queue_id ? Number(form.opd_queue_id) : undefined,
        subjective: form.subjective.trim() || undefined,
        objective: form.objective.trim() || undefined,
        assessment: form.assessment.trim() || undefined,
        plan: form.plan.trim() || undefined,
        icd10_code: form.icd10_code || undefined,
        follow_up_at: form.follow_up_at || undefined,
      })

      setSuccess(`Consultation saved successfully (ID: ${response.data?.consultation?.id ?? 'N/A'}).`)
      setForm(initialForm)
      setIcdQuery('')
      setIcdResults([])
    } catch {
      setError('Failed to save consultation. Please review required fields and retry.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="mx-auto max-w-6xl space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-teal-100 via-white to-emerald-50 p-6">
        <p className="mb-2 text-xs uppercase tracking-[0.2em] text-teal-400">OPD Consultation</p>
        <h1 className="text-3xl font-bold text-slate-900">SOAP Consultation Editor</h1>
        <p className="mt-2 text-slate-600">Capture structured clinical notes with ICD-10 assisted diagnosis lookup.</p>
      </section>

      <section className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <p className="text-xs uppercase tracking-wider text-slate-500">Current Diagnosis Code</p>
          <p className="mt-2 text-xl font-bold text-slate-900">{form.icd10_code || '--'}</p>
          <p className="mt-1 text-xs text-slate-500">{selectedDiagnosis?.label ?? 'No diagnosis selected'}</p>
        </article>

        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <p className="text-xs uppercase tracking-wider text-slate-500">Follow-up Date</p>
          <p className="mt-2 text-xl font-bold text-slate-900">{form.follow_up_at || '--'}</p>
          <p className="mt-1 text-xs text-slate-500">Set next review schedule after consultation.</p>
        </article>
      </section>

      <section className="rounded-2xl border border-slate-200 bg-white p-6">
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Patient ID *</span>
            <input
              value={form.patient_id}
              onChange={(e) => setField('patient_id', e.target.value)}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="e.g. 1001"
            />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">OPD Queue ID (optional)</span>
            <input
              value={form.opd_queue_id}
              onChange={(e) => setField('opd_queue_id', e.target.value)}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="e.g. 24"
            />
          </label>
        </div>

        <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
          <label className="block md:col-span-2">
            <span className="mb-1 block text-xs text-slate-600">Subjective</span>
            <textarea
              value={form.subjective}
              onChange={(e) => setField('subjective', e.target.value)}
              rows={3}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="Patient complaints and history..."
            />
          </label>

          <label className="block md:col-span-2">
            <span className="mb-1 block text-xs text-slate-600">Objective</span>
            <textarea
              value={form.objective}
              onChange={(e) => setField('objective', e.target.value)}
              rows={3}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="Exam findings and measurable signs..."
            />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Assessment</span>
            <textarea
              value={form.assessment}
              onChange={(e) => setField('assessment', e.target.value)}
              rows={4}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="Clinical impression..."
            />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Plan</span>
            <textarea
              value={form.plan}
              onChange={(e) => setField('plan', e.target.value)}
              rows={4}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
              placeholder="Treatment and follow-up plan..."
            />
          </label>
        </div>

        <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">ICD-10 Search</span>
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-500" />
              <input
                value={icdQuery}
                onChange={(e) => setIcdQuery(e.target.value)}
                className="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-teal-400/50"
                placeholder="Type diagnosis keyword or code"
              />
            </div>
            {icdLoading && (
              <p className="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                <Loader2 className="h-3.5 w-3.5 animate-spin" />
                Searching ICD-10...
              </p>
            )}
            {!icdLoading && icdResults.length > 0 && (
              <div className="mt-2 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white">
                {icdResults.map((item) => (
                  <button
                    key={`${item.code}-${item.label}`}
                    type="button"
                    onClick={() => {
                      setField('icd10_code', item.code)
                      setIcdQuery(`${item.code} - ${item.label}`)
                    }}
                    className="block w-full border-b border-white/5 px-3 py-2 text-left text-xs text-slate-200 last:border-b-0 hover:bg-slate-50"
                  >
                    <span className="font-semibold text-teal-300">{item.code}</span>
                    <span className="ml-2">{item.label}</span>
                  </button>
                ))}
              </div>
            )}
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Follow-up At</span>
            <input
              type="datetime-local"
              value={form.follow_up_at}
              onChange={(e) => setField('follow_up_at', e.target.value)}
              className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-teal-400/50"
            />
          </label>
        </div>

        <div className="mt-5 flex flex-wrap items-center gap-3">
          <button
            onClick={submitConsultation}
            disabled={submitting}
            className="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
            Save Consultation
          </button>

          <button
            onClick={() => {
              setForm(initialForm)
              setIcdQuery('')
              setIcdResults([])
              setError(null)
              setSuccess(null)
            }}
            disabled={submitting}
            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <Stethoscope className="h-4 w-4" />
            Reset Form
          </button>

          <button
            onClick={() => setField('plan', `${form.plan}${form.plan ? '\n' : ''}Review labs and continue treatment.`)}
            disabled={submitting}
            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <FileText className="h-4 w-4" />
            Add Plan Template
          </button>

          <button
            onClick={() => {
              const nextDate = new Date()
              nextDate.setDate(nextDate.getDate() + 7)
              const local = new Date(nextDate.getTime() - nextDate.getTimezoneOffset() * 60000)
              setField('follow_up_at', local.toISOString().slice(0, 16))
            }}
            disabled={submitting}
            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <CalendarClock className="h-4 w-4" />
            +7 Days Follow-up
          </button>
        </div>

        {error && <p className="mt-3 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-300">{error}</p>}
        {success && <p className="mt-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-300">{success}</p>}
      </section>
    </div>
  )
}
