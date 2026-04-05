import { type FormEvent, useMemo, useState } from 'react'
import './App.css'

type TenantDraft = {
  name: string
  subdomain: string
  database_name: string
}

function App() {
  const [tenant, setTenant] = useState<TenantDraft>({
    name: '',
    subdomain: '',
    database_name: '',
  })
  const [queue, setQueue] = useState<TenantDraft[]>([])

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
      </section>
    </main>
  )
}

export default App
