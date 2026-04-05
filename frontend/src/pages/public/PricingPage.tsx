import { PageShell } from '../../components/ui/PageShell'

const plans = [
  {
    name: 'Starter Clinic',
    price: '$79',
    users: 'Single branch, up to 30 beds',
    cta: 'Start Trial',
    features: ['OPD + appointment + billing essentials', 'Basic lab and pharmacy workflow', 'Email support, weekly backup'],
  },
  {
    name: 'Growth Hospital',
    price: '$219',
    users: 'Multi-department, up to 200 beds',
    cta: 'Choose Growth',
    features: ['Full OPD/IPD, emergency, and nursing modules', 'Claims-ready billing and package pricing', 'Priority support and role-based analytics'],
  },
  {
    name: 'Enterprise Network',
    price: 'Custom',
    users: 'Multi-branch, unlimited scale',
    cta: 'Contact Sales',
    features: ['Dedicated environment and SSO options', 'Advanced integrations (LIS/RIS/ERP/Gateway)', 'Implementation squad and SLA governance'],
  },
]

export function PricingPage() {
  return (
    <PageShell
      title="International Pricing For Clinics To Hospital Networks"
      subtitle="Flexible SaaS packages with implementation support, security controls, and scale-ready architecture."
    >
      <div className="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        Prices are indicative and may vary by region, compliance scope, integrations, and onboarding complexity.
      </div>

      <div className="grid gap-5 md:grid-cols-3">
        {plans.map((plan) => (
          <article key={plan.name} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p className="text-sm font-semibold text-sky-600">{plan.name}</p>
            <p className="mt-2 text-3xl font-bold text-slate-900">{plan.price}</p>
            <p className="text-sm text-slate-500">{plan.price === 'Custom' ? 'annual enterprise contract' : 'per hospital / month'}</p>
            <p className="mt-4 text-sm text-slate-700">{plan.users}</p>

            <ul className="mt-4 space-y-2">
              {plan.features.map((item) => (
                <li key={item} className="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                  {item}
                </li>
              ))}
            </ul>

            <button type="button" className="mt-6 w-full rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500">
              {plan.cta}
            </button>
          </article>
        ))}
      </div>

      <p className="mt-6 text-sm text-slate-600">
        Need local currency billing (BDT/USD/EUR), data residency, or custom migration? Contact sales for a tailored rollout plan.
      </p>
    </PageShell>
  )
}