import { PageShell } from '../../components/ui/PageShell'

const plans = [
  { name: 'Starter Clinic', price: '$49', users: 'Single branch, up to 25 beds', cta: 'Start Trial' },
  { name: 'Growth Hospital', price: '$149', users: 'Multi-department, up to 150 beds', cta: 'Choose Growth' },
  { name: 'Enterprise Network', price: '$399', users: 'Multi-branch, unlimited scale', cta: 'Contact Sales' },
]

export function PricingPage() {
  return (
    <PageShell title="Transparent Pricing For Every Hospital Size" subtitle="Start small, expand confidently, and keep full operational control.">
      <div className="grid gap-5 md:grid-cols-3">
        {plans.map((plan) => (
          <article key={plan.name} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p className="text-sm font-semibold text-sky-600">{plan.name}</p>
            <p className="mt-2 text-3xl font-bold text-slate-900">{plan.price}</p>
            <p className="text-sm text-slate-500">per hospital / month</p>
            <p className="mt-4 text-sm text-slate-700">{plan.users}</p>
            <button type="button" className="mt-6 w-full rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500">
              {plan.cta}
            </button>
          </article>
        ))}
      </div>
    </PageShell>
  )
}