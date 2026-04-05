import { PageShell } from '../../components/ui/PageShell'

export function AboutPage() {
  return (
    <PageShell title="About Ariba Hospital Management SaaS" subtitle="We build healthcare operations software that helps teams deliver better care faster.">
      <div className="grid gap-5 md:grid-cols-2">
        <article className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 className="font-semibold text-slate-900">Our Mission</h3>
          <p className="mt-3 text-sm text-slate-600">
            Build a dependable SaaS platform that connects clinical and administrative teams in one source of truth.
          </p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 className="font-semibold text-slate-900">Why We Built It</h3>
          <p className="mt-3 text-sm text-slate-600">
            Legacy hospital tools are fragmented and slow. Ariba HMS delivers modern UX with enterprise-grade control.
          </p>
        </article>
      </div>
    </PageShell>
  )
}