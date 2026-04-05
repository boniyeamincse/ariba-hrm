import { PageShell } from '../../components/ui/PageShell'

const moduleGroups = [
  {
    title: 'Patient & Access',
    items: ['Digital registration and unique MRN/UHID', 'Appointment, queue, and tele-consult support', 'Patient timeline with clinical attachments'],
  },
  {
    title: 'Clinical Operations',
    items: ['OPD/IPD/Emergency workflows', 'Doctor, nursing, and ward rounds tools', 'ePrescription with allergy and interaction alerts'],
  },
  {
    title: 'Diagnostics & Pharmacy',
    items: ['LIS-ready sample, result, and validation flow', 'Radiology and report lifecycle support', 'Pharmacy stock, FEFO, and dispensing controls'],
  },
  {
    title: 'Revenue & Administration',
    items: ['Multi-step billing, package, and discount rules', 'Insurance and payment reconciliation', 'HR, attendance, and payroll baseline modules'],
  },
]

const platformHighlights = [
  'Role-based permissions with tenant boundaries',
  'Real-time events for queue, beds, and alerts',
  'API-first architecture for device and third-party integrations',
  'Audit trails across clinical and financial actions',
  'English/Bangla-ready frontend foundation',
  'Cloud multi-branch deployment model',
]

export function FeaturesPage() {
  return (
    <PageShell
      title="International HMS Features For Modern Hospitals"
      subtitle="Ariba HMS combines patient care, diagnostics, operations, and revenue in one connected SaaS platform."
    >
      <div className="grid gap-5 md:grid-cols-2">
        {moduleGroups.map((group) => (
          <article key={group.title} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 className="text-lg font-semibold text-slate-900">{group.title}</h3>
            <ul className="mt-3 space-y-2 text-sm text-slate-600">
              {group.items.map((item) => (
                <li key={item} className="rounded-lg bg-slate-50 px-3 py-2">
                  {item}
                </li>
              ))}
            </ul>
          </article>
        ))}
      </div>

      <section className="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 className="text-lg font-semibold text-slate-900">Platform Highlights</h3>
        <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {platformHighlights.map((item) => (
            <p key={item} className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
              {item}
            </p>
          ))}
        </div>
      </section>

      <section className="mt-8 rounded-2xl border border-sky-100 bg-sky-50 p-6">
        <h3 className="text-lg font-semibold text-slate-900">Enterprise Readiness</h3>
        <p className="mt-2 text-sm text-slate-700">
          Designed for hospitals that need secure tenancy, regulatory visibility, and cross-department interoperability without rebuilding existing workflows.
        </p>
      </section>
    </PageShell>
  )
}