import { PageShell } from '../../components/ui/PageShell'

const items = [
  'Patient registration with UHID and visit timeline',
  'OPD queue, consultations, and e-prescriptions',
  'IPD admissions, beds, and nursing workflows',
  'Laboratory orders, results, and validation',
  'Pharmacy inventory, dispensing, and sales',
  'Billing, payments, insurance, and audit logs',
]

export function FeaturesPage() {
  return (
    <PageShell title="Hospital Modules That Actually Work Together" subtitle="Ariba HMS unifies clinical, operations, and revenue workflows in one SaaS platform.">
      <div className="grid gap-4 md:grid-cols-2">
        {items.map((item) => (
          <article key={item} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">{item}</h3>
            <p className="mt-2 text-sm text-slate-600">Built for scale, traceability, and fast adoption across departments.</p>
          </article>
        ))}
      </div>
    </PageShell>
  )
}