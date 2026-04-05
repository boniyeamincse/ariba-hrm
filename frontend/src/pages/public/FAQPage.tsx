import { PageShell } from '../../components/ui/PageShell'

const faqs = [
  {
    question: 'Can Ariba HMS support multiple hospitals under one group?',
    answer: 'Yes. The platform supports multi-tenant setups with strict data isolation and central governance.',
  },
  {
    question: 'Do you support role-based access for clinical teams?',
    answer: 'Yes. Super Admin, Hospital Admin, Doctor, Nurse, Pharmacist, Lab Tech, and custom roles are supported.',
  },
  {
    question: 'Is patient and billing data audit-ready?',
    answer: 'Yes. Every critical action can be traced with audit logs to support compliance and reviews.',
  },
]

export function FAQPage() {
  return (
    <PageShell title="Frequently Asked Questions" subtitle="Common questions from hospital teams and implementation leads.">
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