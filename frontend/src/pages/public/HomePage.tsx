import { motion } from 'framer-motion'
import { NavLink } from 'react-router-dom'

const featureCards = [
  'OPD & IPD Workflows',
  'Digital Prescriptions',
  'Laboratory Integrations',
  'Pharmacy Inventory',
  'Billing & Insurance',
  'Role-Based Access',
]

export function HomePage() {
  return (
    <div>
      <section className="relative overflow-hidden bg-slate-950 px-5 py-24 text-white">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.3),transparent_30%),radial-gradient(circle_at_78%_75%,rgba(45,212,191,0.24),transparent_35%)]" />
        <div className="relative mx-auto grid w-full max-w-6xl gap-10 md:grid-cols-2 md:items-center">
          <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
            <p className="mb-3 inline-flex rounded-full border border-white/20 px-3 py-1 text-xs font-semibold tracking-wide text-sky-200">
              Ariba Hospital Management SaaS
            </p>
            <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
              One modern operating system for hospitals, clinics, and care networks
            </h1>
            <p className="mt-4 text-slate-300">
              Manage patient journeys, departments, diagnostics, pharmacy, and billing in a secure multi-tenant cloud platform.
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
              <NavLink to="/auth/register" className="rounded-full bg-sky-500 px-5 py-2.5 font-semibold text-white hover:bg-sky-400">
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
            <p className="text-sm font-medium text-sky-200">Live Operations Preview</p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Patients Today</p>
                <p className="mt-1 text-xl font-bold">1,284</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Bed Occupancy</p>
                <p className="mt-1 text-xl font-bold">82%</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Lab TAT Avg</p>
                <p className="mt-1 text-xl font-bold">31 min</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Revenue Today</p>
                <p className="mt-1 text-xl font-bold">$24,900</p>
              </div>
            </div>
          </motion.div>
        </div>
      </section>

      <section className="mx-auto w-full max-w-6xl px-5 py-16">
        <h2 className="text-2xl font-bold tracking-tight text-slate-900">Built for real hospital workflows</h2>
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
              <p className="mt-2 text-sm text-slate-600">Production-grade modules for fast, accurate, and auditable care delivery.</p>
            </motion.article>
          ))}
        </div>
      </section>

      <section className="bg-white py-16">
        <div className="mx-auto w-full max-w-6xl px-5">
          <div className="grid gap-5 md:grid-cols-3">
            {[
              '“Ariba HMS unified OPD, lab, pharmacy, and billing in one clear workflow.”',
              '“Our discharge cycle became faster with real-time financial clearance.”',
              '“Doctors and nurses adopted the system quickly because the UI is clean.”',
            ].map((quote) => (
              <blockquote key={quote} className="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                {quote}
              </blockquote>
            ))}
          </div>

          <div className="mt-10 rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 p-8 text-white">
            <h3 className="text-2xl font-bold">Launch your hospital cloud in days, not months</h3>
            <p className="mt-2 text-cyan-100">Start a free trial and configure your departments, teams, and billing flows in one platform.</p>
            <NavLink
              to="/auth/register"
              className="mt-5 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-sky-700"
            >
              Start Free Trial
            </NavLink>
          </div>
        </div>
      </section>
    </div>
  )
}