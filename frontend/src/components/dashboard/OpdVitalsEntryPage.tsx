import { useMemo, useState } from 'react'
import { Activity, HeartPulse, Loader2, Ruler, Save, Scale, Stethoscope } from 'lucide-react'
import { api } from '../../lib/api'

type VitalsForm = {
  patient_id: string
  consultation_id: string
  bp_systolic: string
  bp_diastolic: string
  temperature_c: string
  pulse: string
  spo2: string
  weight_kg: string
  height_cm: string
  respiratory_rate: string
  pain_score: string
  notes: string
}

const initialForm: VitalsForm = {
  patient_id: '',
  consultation_id: '',
  bp_systolic: '',
  bp_diastolic: '',
  temperature_c: '',
  pulse: '',
  spo2: '',
  weight_kg: '',
  height_cm: '',
  respiratory_rate: '',
  pain_score: '',
  notes: '',
}

export function OpdVitalsEntryPage() {
  const [form, setForm] = useState<VitalsForm>(initialForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)

  const bmiPreview = useMemo(() => {
    const weight = Number(form.weight_kg)
    const heightCm = Number(form.height_cm)

    if (!weight || !heightCm || heightCm <= 0) {
      return '--'
    }

    const heightM = heightCm / 100
    const bmi = weight / (heightM * heightM)

    return bmi.toFixed(2)
  }, [form.height_cm, form.weight_kg])

  const setField = (key: keyof VitalsForm, value: string) => {
    setForm((prev) => ({ ...prev, [key]: value }))
  }

  const submitVitals = async () => {
    if (!form.patient_id.trim()) {
      setError('Patient ID is required.')
      return
    }

    setSubmitting(true)
    setError(null)
    setSuccess(null)

    try {
      await api.post('/clinical/opd/vitals', {
        patient_id: Number(form.patient_id),
        consultation_id: form.consultation_id ? Number(form.consultation_id) : undefined,
        bp_systolic: form.bp_systolic ? Number(form.bp_systolic) : undefined,
        bp_diastolic: form.bp_diastolic ? Number(form.bp_diastolic) : undefined,
        temperature_c: form.temperature_c ? Number(form.temperature_c) : undefined,
        pulse: form.pulse ? Number(form.pulse) : undefined,
        spo2: form.spo2 ? Number(form.spo2) : undefined,
        weight_kg: form.weight_kg ? Number(form.weight_kg) : undefined,
        height_cm: form.height_cm ? Number(form.height_cm) : undefined,
        respiratory_rate: form.respiratory_rate ? Number(form.respiratory_rate) : undefined,
        pain_score: form.pain_score ? Number(form.pain_score) : undefined,
        notes: form.notes.trim() || undefined,
      })

      setSuccess('Vitals recorded successfully.')
      setForm(initialForm)
    } catch {
      setError('Failed to submit vitals. Please verify input values and retry.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="mx-auto max-w-6xl space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-emerald-100 via-white to-emerald-50 p-6">
        <p className="mb-2 text-xs uppercase tracking-[0.2em] text-emerald-400">OPD Triage</p>
        <h1 className="text-3xl font-bold text-slate-900">Vitals Entry Console</h1>
        <p className="mt-2 text-slate-600">Capture nurse triage vitals before or during consultation.</p>
      </section>

      <section className="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="mb-2 flex items-center gap-2 text-slate-600"><HeartPulse className="h-4 w-4 text-rose-400" /> Blood Pressure</div>
          <p className="text-sm text-slate-900">{form.bp_systolic || '--'} / {form.bp_diastolic || '--'} mmHg</p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="mb-2 flex items-center gap-2 text-slate-600"><Activity className="h-4 w-4 text-cyan-400" /> SpO2 & Pulse</div>
          <p className="text-sm text-slate-900">SpO2 {form.spo2 || '--'}% | Pulse {form.pulse || '--'} bpm</p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="mb-2 flex items-center gap-2 text-slate-600"><Scale className="h-4 w-4 text-emerald-400" /> Weight & Height</div>
          <p className="text-sm text-slate-900">{form.weight_kg || '--'} kg | {form.height_cm || '--'} cm</p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="mb-2 flex items-center gap-2 text-slate-600"><Ruler className="h-4 w-4 text-amber-400" /> BMI Preview</div>
          <p className="text-sm text-slate-900">{bmiPreview}</p>
        </article>
      </section>

      <section className="rounded-2xl border border-slate-200 bg-white p-6">
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Patient ID *</span>
            <input value={form.patient_id} onChange={(e) => setField('patient_id', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" placeholder="e.g. 1001" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Consultation ID</span>
            <input value={form.consultation_id} onChange={(e) => setField('consultation_id', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" placeholder="optional" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Temperature (C)</span>
            <input type="number" step="0.1" value={form.temperature_c} onChange={(e) => setField('temperature_c', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">BP Systolic</span>
            <input type="number" value={form.bp_systolic} onChange={(e) => setField('bp_systolic', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">BP Diastolic</span>
            <input type="number" value={form.bp_diastolic} onChange={(e) => setField('bp_diastolic', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Pulse (bpm)</span>
            <input type="number" value={form.pulse} onChange={(e) => setField('pulse', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">SpO2 (%)</span>
            <input type="number" value={form.spo2} onChange={(e) => setField('spo2', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Weight (kg)</span>
            <input type="number" step="0.1" value={form.weight_kg} onChange={(e) => setField('weight_kg', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Height (cm)</span>
            <input type="number" step="0.1" value={form.height_cm} onChange={(e) => setField('height_cm', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Respiratory Rate</span>
            <input type="number" value={form.respiratory_rate} onChange={(e) => setField('respiratory_rate', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block">
            <span className="mb-1 block text-xs text-slate-600">Pain Score (0-10)</span>
            <input type="number" min={0} max={10} value={form.pain_score} onChange={(e) => setField('pain_score', e.target.value)} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" />
          </label>

          <label className="block md:col-span-2 xl:col-span-3">
            <span className="mb-1 block text-xs text-slate-600">Clinical Notes</span>
            <textarea value={form.notes} onChange={(e) => setField('notes', e.target.value)} rows={4} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400/50" placeholder="Additional triage notes..." />
          </label>
        </div>

        <div className="mt-5 flex flex-wrap items-center gap-3">
          <button onClick={submitVitals} disabled={submitting} className="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60">
            {submitting ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
            Save Vitals
          </button>

          <button onClick={() => setForm(initialForm)} disabled={submitting} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
            <Stethoscope className="h-4 w-4" />
            Reset Form
          </button>
        </div>

        {error && <p className="mt-3 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-300">{error}</p>}
        {success && <p className="mt-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-300">{success}</p>}
      </section>
    </div>
  )
}
