const metrics = [
  { label: 'Total Employees', value: '1,248' },
  { label: 'Monthly Payroll', value: '$182,450' },
  { label: 'Open Positions', value: '19' },
  { label: 'Pending Leaves', value: '27' },
]

export function DashboardHomePage() {
  return (
    <div className="space-y-6">
      <section>
        <h1 className="text-2xl font-bold text-slate-900">Dashboard Overview</h1>
        <p className="mt-1 text-sm text-slate-600">Welcome to your HRM command center.</p>
      </section>

      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {metrics.map((metric) => (
          <article key={metric.label} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-sm text-slate-500">{metric.label}</p>
            <p className="mt-2 text-2xl font-bold text-slate-900">{metric.value}</p>
          </article>
        ))}
      </section>

      <section className="grid gap-4 lg:grid-cols-2">
        <article className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 className="font-semibold text-slate-900">Upcoming HR Actions</h2>
          <ul className="mt-3 space-y-2 text-sm text-slate-600">
            <li>Run payroll for April by 30th</li>
            <li>Complete Q2 performance review setup</li>
            <li>Approve pending leave requests</li>
          </ul>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 className="font-semibold text-slate-900">Team Activity</h2>
          <ul className="mt-3 space-y-2 text-sm text-slate-600">
            <li>12 new applicants entered recruitment pipeline</li>
            <li>5 employees completed onboarding checklist</li>
            <li>2 departments updated shift templates</li>
          </ul>
        </article>
      </section>
    </div>
  )
}