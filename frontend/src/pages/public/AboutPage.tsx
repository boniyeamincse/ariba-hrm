import { PageShell } from '../../components/ui/PageShell'

export function AboutPage() {
  return (
    <PageShell title="About Ariba HRM" subtitle="We help teams run HR operations with speed, visibility, and confidence.">
      <div className="grid gap-5 md:grid-cols-2">
        <article className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 className="font-semibold text-slate-900">Our Mission</h3>
          <p className="mt-3 text-sm text-slate-600">
            Build a reliable SaaS HR platform that empowers growing businesses to automate repetitive HR work and improve people decisions.
          </p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 className="font-semibold text-slate-900">Why We Built It</h3>
          <p className="mt-3 text-sm text-slate-600">
            Most HR systems are either too complex or too limited. Ariba HRM balances enterprise power with startup usability.
          </p>
        </article>
      </div>
    </PageShell>
  )
}