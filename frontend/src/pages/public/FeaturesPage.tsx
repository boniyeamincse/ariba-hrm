import { PageShell } from '../../components/ui/PageShell'

const items = [
  'Core HR & Employee Profiles',
  'Attendance, Shift, and Overtime Tracking',
  'Payroll with tax and deduction workflows',
  'Leave and holiday policy engine',
  'Recruitment pipeline and candidate scoring',
  'Performance review cycles and KPI tracking',
]

export function FeaturesPage() {
  return (
    <PageShell title="Feature-Rich HRM Modules" subtitle="Everything your HR team needs in one integrated SaaS experience.">
      <div className="grid gap-4 md:grid-cols-2">
        {items.map((item) => (
          <article key={item} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">{item}</h3>
            <p className="mt-2 text-sm text-slate-600">Built for scale, auditability, and modern employee experience.</p>
          </article>
        ))}
      </div>
    </PageShell>
  )
}