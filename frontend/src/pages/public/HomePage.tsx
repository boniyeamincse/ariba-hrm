import { motion } from 'framer-motion'
import { NavLink } from 'react-router-dom'
import heroVisual from '../../assets/hms-hero-visual.svg'
import featurePreview from '../../assets/hms-feature-preview.svg'

const featureCards = [
  'Global Patient Registry & EHR',
  'OPD/IPD + Emergency Flow',
  'Lab, RIS, and Diagnostics',
  'Pharmacy FEFO & Safety Alerts',
  'Revenue Cycle + Claims Readiness',
  'Role-Based Security & Audit Trails',
]

const trustBadges = ['HIPAA-ready controls', 'GDPR-aware data handling', 'ISO-style audit logging', '99.98% uptime SLA']

export function HomePage() {
  return (
    <div>
      <section className="relative overflow-hidden bg-slate-950 px-5 py-24 text-white">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.3),transparent_30%),radial-gradient(circle_at_78%_75%,rgba(45,212,191,0.24),transparent_35%)]" />
        <div className="relative mx-auto grid w-full max-w-6xl gap-10 md:grid-cols-2 md:items-center">
          <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
            <p className="mb-3 inline-flex rounded-full border border-white/20 px-3 py-1 text-xs font-semibold tracking-wide text-sky-200">
              Ariba HMS - International Edition
            </p>
            <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
              International hospital management platform built for modern care networks
            </h1>
            <p className="mt-4 text-slate-300">
              Unify patient services, clinical workflows, and finance across branches with multilingual experiences and enterprise-grade security.
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
              <NavLink to="/auth/register" className="rounded-full bg-sky-500 px-5 py-2.5 font-semibold text-white hover:bg-sky-400">
                Start Free Trial
              </NavLink>
              <NavLink to="/features" className="rounded-full border border-white/20 px-5 py-2.5 font-semibold text-slate-200 hover:bg-white/10">
                Explore Features
              </NavLink>
            </div>

            <div className="mt-8 grid max-w-md grid-cols-3 gap-3 text-left">
              <div className="rounded-xl border border-white/15 bg-white/10 p-3 backdrop-blur">
                <p className="text-xs text-slate-300">Countries</p>
                <p className="text-lg font-semibold">14</p>
              </div>
              <div className="rounded-xl border border-white/15 bg-white/10 p-3 backdrop-blur">
                <p className="text-xs text-slate-300">Clinics/Hospitals</p>
                <p className="text-lg font-semibold">120+</p>
              </div>
              <div className="rounded-xl border border-white/15 bg-white/10 p-3 backdrop-blur">
                <p className="text-xs text-slate-300">Uptime</p>
                <p className="text-lg font-semibold">99.98%</p>
              </div>
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
                <p className="mt-1 text-xl font-bold">3,842</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Bed Occupancy</p>
                <p className="mt-1 text-xl font-bold">79%</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Lab TAT Avg</p>
                <p className="mt-1 text-xl font-bold">24 min</p>
              </div>
              <div className="rounded-xl bg-white/10 p-3">
                <p className="text-xs text-slate-300">Insurance Claims</p>
                <p className="mt-1 text-xl font-bold">94% clean</p>
              </div>
            </div>
          </motion.div>
        </div>

        <div className="relative mx-auto mt-12 w-full max-w-6xl">
          <img
            src={heroVisual}
            alt="Ariba hospital management dashboard interface"
            className="w-full rounded-3xl border border-white/10 shadow-2xl"
          />
        </div>
      </section>

      <section className="border-y border-slate-200 bg-white py-6">
        <div className="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-center gap-3 px-5 sm:gap-4">
          {trustBadges.map((badge) => (
            <span key={badge} className="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-700">
              {badge}
            </span>
          ))}
        </div>
      </section>

      <section className="mx-auto w-full max-w-6xl px-5 py-16">
        <h2 className="text-2xl font-bold tracking-tight text-slate-900">Built for international healthcare operations</h2>
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
              <p className="mt-2 text-sm text-slate-600">Production-grade workflows for safe, fast, and measurable care delivery.</p>
            </motion.article>
          ))}
        </div>

        <div className="mt-10 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
          <img
            src={featurePreview}
            alt="Hospital modules and workflow overview"
            className="w-full rounded-2xl border border-slate-100"
          />
        </div>
      </section>

      <section className="bg-white py-16">
        <div className="mx-auto w-full max-w-6xl px-5">
          <div className="grid gap-5 md:grid-cols-3">
            {[
              '"Ariba HMS unified OPD, lab, pharmacy, and billing across our two-country care network."',
              '"Our discharge process dropped from hours to minutes with live clinical and finance coordination."',
              '"Doctors and nurses onboarded fast thanks to role-focused dashboards and multilingual UI."',
            ].map((quote) => (
              <blockquote key={quote} className="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                {quote}
              </blockquote>
            ))}
          </div>

          <div className="mt-10 rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 p-8 text-white">
            <h3 className="text-2xl font-bold">Launch your international HMS rollout in days, not months</h3>
            <p className="mt-2 text-cyan-100">Start a free trial and configure departments, claims flows, and branch-level controls in one secure cloud.</p>
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