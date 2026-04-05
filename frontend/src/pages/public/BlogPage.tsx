import { PageShell } from '../../components/ui/PageShell'

export function BlogPage() {
  return (
    <PageShell title="Blog" subtitle="Product updates, HR insights, and team operations playbooks.">
      <div className="grid gap-4 md:grid-cols-2">
        {[1, 2, 3, 4].map((item) => (
          <article key={item} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">Product</p>
            <h3 className="mt-2 font-semibold text-slate-900">How to build a transparent review cycle</h3>
            <p className="mt-2 text-sm text-slate-600">A practical framework to run quarterly performance discussions with less stress.</p>
          </article>
        ))}
      </div>
    </PageShell>
  )
}