import { PageShell } from '../../components/ui/PageShell'

const plans = [
  { name: 'Starter', price: '$19', users: 'Up to 25 employees', cta: 'Start Trial' },
  { name: 'Growth', price: '$59', users: 'Up to 150 employees', cta: 'Choose Growth' },
  { name: 'Scale', price: '$149', users: 'Unlimited employees', cta: 'Contact Sales' },
]

export function PricingPage() {
  return (
    <PageShell title="Simple SaaS Pricing" subtitle="Flexible plans that scale with your company growth.">
      <div className="grid gap-5 md:grid-cols-3">
        {plans.map((plan) => (
          <article key={plan.name} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p className="text-sm font-semibold text-indigo-600">{plan.name}</p>
            <p className="mt-2 text-3xl font-bold text-slate-900">{plan.price}</p>
            <p className="text-sm text-slate-500">per workspace / month</p>
            <p className="mt-4 text-sm text-slate-700">{plan.users}</p>
            <button type="button" className="mt-6 w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
              {plan.cta}
            </button>
          </article>
        ))}
      </div>
    </PageShell>
  )
}