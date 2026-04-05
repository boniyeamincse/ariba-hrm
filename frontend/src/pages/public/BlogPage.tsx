import { PageShell } from '../../components/ui/PageShell'

export function BlogPage() {
  return (
    <PageShell title="Product & Healthcare Operations Blog" subtitle="Implementation stories, product releases, and hospital optimization guides.">
      <div className="grid gap-4 md:grid-cols-2">
        {[1, 2, 3, 4].map((item) => (
          <article key={item} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-semibold uppercase tracking-wide text-sky-600">Healthcare SaaS</p>
            <h3 className="mt-2 font-semibold text-slate-900">Reducing OPD waiting time with live queue workflows</h3>
            <p className="mt-2 text-sm text-slate-600">A practical approach to improve patient throughput without adding operational overhead.</p>
          </article>
        ))}
      </div>
    </PageShell>
  )
}