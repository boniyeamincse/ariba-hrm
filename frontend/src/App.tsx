import { useState } from 'react'
import './App.css'

const navItems = [
  { label: 'Overview', active: true },
  { label: 'Tenants', active: false },
  { label: 'Revenue', active: false },
  { label: 'Operations', active: false },
  { label: 'Support', active: false },
  { label: 'Settings', active: false },
]

const metrics = [
  { title: 'Active Tenants', value: '42', delta: '+8.2% this month' },
  { title: 'MRR', value: '$118,400', delta: '+5.7% this month' },
  { title: 'Uptime', value: '99.98%', delta: 'No incidents in last 7 days' },
  { title: 'Open Tickets', value: '14', delta: '-23% this week' },
]

const activities = [
  {
    title: 'New tenant onboarded',
    description: 'Dhaka Care Hospital completed setup and first login.',
    time: '5m ago',
  },
  {
    title: 'Subscription upgraded',
    description: 'City Medica moved from Growth to Scale plan.',
    time: '27m ago',
  },
  {
    title: 'Billing alert resolved',
    description: 'Failed payment retried successfully for tenant #HMS-113.',
    time: '1h ago',
  },
  {
    title: 'Support ticket escalated',
    description: 'Pharmacy print issue escalated to Platform team.',
    time: '2h ago',
  },
]

function App() {
  const [drawerOpen, setDrawerOpen] = useState(false)

  return (
    <div className="saas-shell">
      <aside className={`drawer ${drawerOpen ? 'open' : ''}`}>
        <div className="brand">
          <div className="brand-mark">M</div>
          <div>
            <p className="brand-name">MedCore SaaS</p>
            <p className="brand-sub">Hospital Platform</p>
          </div>
        </div>

        <nav className="drawer-nav">
          {navItems.map((item) => (
            <button
              key={item.label}
              type="button"
              className={`nav-link ${item.active ? 'active' : ''}`}
              onClick={() => setDrawerOpen(false)}
            >
              {item.label}
            </button>
          ))}
        </nav>

        <section className="plan-card">
          <p className="plan-kicker">Current Plan</p>
          <h3>Scale Annual</h3>
          <p>12 hospitals, priority support, advanced analytics.</p>
          <button type="button">Manage Subscription</button>
        </section>
      </aside>

      <div className="content-area">
        <header className="topbar">
          <button
            type="button"
            className="menu-toggle"
            onClick={() => setDrawerOpen((state) => !state)}
          >
            Menu
          </button>
          <div>
            <p className="kicker">SaaS Command Center</p>
            <h1>Product Home</h1>
          </div>
          <div className="status-pill">All Systems Healthy</div>
        </header>

        <section className="metrics-grid">
          {metrics.map((metric) => (
            <article key={metric.title} className="metric-card">
              <p>{metric.title}</p>
              <strong>{metric.value}</strong>
              <span>{metric.delta}</span>
            </article>
          ))}
        </section>

        <section className="layout-grid">
          <article className="panel panel-wide">
            <div className="panel-head">
              <h2>Growth Pipeline</h2>
              <button type="button">View Report</button>
            </div>
            <div className="pipeline">
              <div>
                <p>Trial</p>
                <strong>18</strong>
              </div>
              <div>
                <p>Onboarding</p>
                <strong>7</strong>
              </div>
              <div>
                <p>Live</p>
                <strong>42</strong>
              </div>
              <div>
                <p>Expansion</p>
                <strong>11</strong>
              </div>
            </div>
          </article>

          <article className="panel">
            <h2>Release Tracker</h2>
            <ul className="clean-list">
              <li><span>v1.8.4</span><strong>Deploying</strong></li>
              <li><span>v1.8.3</span><strong>Stable</strong></li>
              <li><span>v1.9.0-beta</span><strong>QA</strong></li>
            </ul>
          </article>

          <article className="panel panel-wide">
            <h2>Recent Activity</h2>
            <ul className="activity-list">
              {activities.map((item) => (
                <li key={item.title}>
                  <div>
                    <p>{item.title}</p>
                    <span>{item.description}</span>
                  </div>
                  <time>{item.time}</time>
                </li>
              ))}
            </ul>
          </article>
        </section>
      </div>

      {drawerOpen && <button type="button" className="backdrop" onClick={() => setDrawerOpen(false)} />}
    </div>
  )
}

export default App
