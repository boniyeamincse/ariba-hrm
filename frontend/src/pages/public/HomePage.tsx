import { motion } from 'framer-motion'
import { NavLink } from 'react-router-dom'

const featureCards = [
  'Employee Records',
  'Payroll & Tax',
  'Attendance & Shifts',
  'Leave Management',
  'Recruitment Pipeline',
  'Performance Reviews',
]

export function HomePage() {
  return (
    <div>
      <section className="relative overflow-hidden bg-slate-950 px-5 py-24 text-white">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(59,130,246,0.25),transparent_30%),radial-gradient(circle_at_75%_75%,rgba(147,51,234,0.28),transparent_35%)]" />
        <div className="relative mx-auto grid w-full max-w-6xl gap-10 md:grid-cols-2 md:items-center">
          <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
            <p className="mb-3 inline-flex rounded-full border border-white/20 px-3 py-1 text-xs font-semibold tracking-wide text-indigo-200">
              SaaS Human Resource Management
            </p>
            <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
              Build a high-performing workforce with Ariba HRM
            </h1>
            <p className="mt-4 text-slate-300">
              Centralize people operations, automate payroll, and streamline hiring in one modern platform.
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
              <NavLink to="/auth/register" className="rounded-full bg-indigo-500 px-5 py-2.5 font-semibold text-white hover:bg-indigo-400">
                Start Free Trial
              </NavLink>
              <NavLink to="/features" className="rounded-full border border-white/20 px-5 py-2.5 font-semibold text-slate-200 hover:bg-white/10">
                Explore Features
              </NavLink>
            </div>
          </motion.div>
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            className="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur"
          >
            <p className="text-sm font-medium text-indigo-200">Dashboard Preview</p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Monthly Payroll</p>
                <p className="mt-1 text-xl font-bold">$74,120</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Attendance Rate</p>
                <p className="mt-1 text-xl font-bold">96.4%</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Open Positions</p>
                <p className="mt-1 text-xl font-bold">12</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Leave Requests</p>
                <p className="mt-1 text-xl font-bold">18</p>
              </div>
            </div>
          </motion.div>
        </div>
      </section>

      <section className="mx-auto w-full max-w-6xl px-5 py-16">
        <h2 className="text-2xl font-bold tracking-tight text-slate-900">HR Modules Designed For SaaS Teams</h2>
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {featureCards.map((card, index) => (
            <motion.article
              key={card}
              initial={{ opacity: 0, y: 10 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: index * 0.04 }}
              className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
              <h3 className="font-semibold text-slate-900">{card}</h3>
              <p className="mt-2 text-sm text-slate-600">Enterprise-grade workflows with startup speed and simplicity.</p>
            </motion.article>
          ))}
        </div>
      </section>

      <section className="bg-white py-16">
        <div className="mx-auto w-full max-w-6xl px-5">
          <div className="grid gap-5 md:grid-cols-3">
            {[
              '“Ariba HRM cut our payroll processing from 2 days to 2 hours.”',
              '“Recruitment and onboarding are finally unified in one flow.”',
              '“The clean dashboard helped every manager adopt quickly.”',
            ].map((quote) => (
              <blockquote key={quote} className="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                {quote}
              </blockquote>
            ))}
          </div>

          <div className="mt-10 rounded-3xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-8 text-white">
            <h3 className="text-2xl font-bold">Ready to modernize your HR operations?</h3>
            <p className="mt-2 text-indigo-100">Start your free trial and launch your HR SaaS workspace in minutes.</p>
            <NavLink
              to="/auth/register"
              className="mt-5 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700"
            >
              Start Free Trial
            </NavLink>
          </div>
        </div>
      </section>
    </div>
  )
}