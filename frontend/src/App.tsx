import { type FormEvent, useMemo, useState } from 'react'
import './App.css'

type TenantDraft = {
  name: string
  subdomain: string
  database_name: string
}

type PatientCard = {
  uhid: string
  name: string
  gender: string
  bloodGroup: string
  timeline: string[]
}

function App() {
  const [tenant, setTenant] = useState<TenantDraft>({
    name: '',
    subdomain: '',
    database_name: '',
  })
  const [queue, setQueue] = useState<TenantDraft[]>([])
  const [patients, setPatients] = useState<PatientCard[]>([
    {
      uhid: 'UHID-20260405-000101',
      name: 'Amina Rahman',
      gender: 'Female',
      bloodGroup: 'B+',
      timeline: ['OPD Consultation - 2026-04-01', 'Lab Order (CBC) - 2026-04-01'],
    },
    {
      uhid: 'UHID-20260405-000102',
      name: 'Imran Hossain',
      gender: 'Male',
      bloodGroup: 'O+',
      timeline: ['Emergency Triage (Orange) - 2026-04-03', 'IPD Admission - 2026-04-03'],
    },
  ])

  const canAdd = useMemo(() => {
    return tenant.name.trim() && tenant.subdomain.trim() && tenant.database_name.trim()
  }, [tenant])

  const onSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!canAdd) {
      return
    }

    setQueue((current) => [...current, tenant])
    setTenant({ name: '', subdomain: '', database_name: '' })
  }

  const [newPatientName, setNewPatientName] = useState('')

  const registerPatient = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    if (!newPatientName.trim()) {
      return
    }

    const sequence = String(patients.length + 103).padStart(6, '0')
    const uhid = `UHID-20260405-${sequence}`

    setPatients((current) => [
      {
        uhid,
        name: newPatientName,
        gender: 'Unknown',
        bloodGroup: 'N/A',
        timeline: ['Patient registration completed'],
      },
      ...current,
    ])
    setNewPatientName('')
  }

  return (
    <main className="layout">
      <header className="hero">
        <p className="kicker">MedCore HMS</p>
        <h1>Super Admin Console</h1>
        <p className="subtitle">
          Phase 1 shell for tenant onboarding, access governance, and platform operations.
        </p>
      </header>

      <section className="grid">
        <article className="card">
          <h2>Tenant Onboarding Queue</h2>
          <form onSubmit={onSubmit} className="form">
            <label>
              Tenant Name
              <input
                value={tenant.name}
                onChange={(event) => setTenant((state) => ({ ...state, name: event.target.value }))}
                placeholder="City Hospital"
              />
            </label>
            <label>
              Subdomain
              <input
                value={tenant.subdomain}
                onChange={(event) => setTenant((state) => ({ ...state, subdomain: event.target.value }))}
                placeholder="cityhospital"
              />
            </label>
            <label>
              Database Name
              <input
                value={tenant.database_name}
                onChange={(event) =>
                  setTenant((state) => ({ ...state, database_name: event.target.value }))
                }
                placeholder="tenant_cityhospital"
              />
            </label>
            <button type="submit" disabled={!canAdd}>
              Add to Queue
            </button>
          </form>

          <ul className="queue">
            {queue.length === 0 && <li>No pending onboarding requests.</li>}
            {queue.map((item, index) => (
              <li key={`${item.subdomain}-${index}`}>
                <strong>{item.name}</strong>
                <span>{item.subdomain}.medcorehms.com</span>
                <code>{item.database_name}</code>
              </li>
            ))}
          </ul>
        </article>

        <article className="card">
          <h2>Platform Controls</h2>
          <ul className="features">
            <li>Tenant isolation middleware enabled</li>
            <li>Dynamic tenant database switching enabled</li>
            <li>Sanctum-based API authentication enabled</li>
            <li>Role/permission matrix enforcement enabled</li>
            <li>Audit logs for mutating API requests enabled</li>
          </ul>
        </article>

        <article className="card">
          <h2>Next Delivery Target</h2>
          <p className="target">Patient registration to OPD consult to invoice</p>
          <p>Use this shell as the super-admin entrypoint while Phase 2 modules are developed.</p>
        </article>

        <article className="card card-wide">
          <h2>Clinical Foundation Console</h2>
          <form onSubmit={registerPatient} className="form inline-form">
            <label>
              Quick Register Patient
              <input
                value={newPatientName}
                onChange={(event) => setNewPatientName(event.target.value)}
                placeholder="Patient full name"
              />
            </label>
            <button type="submit">Generate UHID</button>
          </form>

          <div className="clinical-grid">
            <section>
              <h3>Patient Demographics</h3>
              <ul className="queue compact">
                {patients.map((patient) => (
                  <li key={patient.uhid}>
                    <strong>{patient.name}</strong>
                    <span>{patient.uhid}</span>
                    <span>{patient.gender} | {patient.bloodGroup}</span>
                  </li>
                ))}
              </ul>
            </section>

            <section>
              <h3>Visit Timeline</h3>
              <ul className="features">
                {patients.slice(0, 2).flatMap((patient) =>
                  patient.timeline.map((event, index) => (
                    <li key={`${patient.uhid}-${index}`}>{patient.name}: {event}</li>
                  ))
                )}
              </ul>
            </section>

            <section>
              <h3>Live Clinical Boards</h3>
              <ul className="features">
                <li>OPD Queue: Token 17 waiting, Token 18 with doctor</li>
                <li>E-Prescription: 6 generated today</li>
                <li>Lab/Radiology Orders: 9 pending</li>
                <li>IPD Beds: 14 available, 22 occupied</li>
                <li>Emergency Triage: 1 Red, 2 Orange active</li>
              </ul>
            </section>
          </div>
        </article>
      </section>
    </main>
  )
}

export default App
