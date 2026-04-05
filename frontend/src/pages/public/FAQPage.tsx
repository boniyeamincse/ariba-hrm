import { PageShell } from '../../components/ui/PageShell'

const faqs = [
  {
    question: 'Can I use Ariba HRM for multiple companies?',
    answer: 'Yes. The SaaS architecture supports multi-tenant workspaces with strict isolation.',
  },
  {
    question: 'Do you support role-based access?',
    answer: 'Yes. HR Admin, Manager, Employee, and custom role policies are supported.',
  },
  {
    question: 'Is payroll configurable for local compliance?',
    answer: 'Yes. Components, deductions, and policy rules are configurable per workspace.',
  },
]

export function FAQPage() {
  return (
    <PageShell title="Frequently Asked Questions" subtitle="Answers to common setup and product questions.">
      <div className="grid gap-4">
        {faqs.map((item) => (
          <article key={item.question} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">{item.question}</h3>
            <p className="mt-2 text-sm text-slate-600">{item.answer}</p>
          </article>
        ))}
      </div>
    </PageShell>
  )
}