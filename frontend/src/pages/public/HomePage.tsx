import { motion } from 'framer-motion'
import { NavLink } from 'react-router-dom'
import heroImg from '../../assets/hero.png'
import dashboardImg from '../../assets/dashboard.png'
import securityImg from '../../assets/security.png'

const clinicalModules = [
  {
    title: 'Precision EHR',
    description: 'Unified electronic health records with secure, longitudinal patient histories.',
    icon: '🏥',
  },
  {
    title: 'Smart Scheduling',
    description: 'AI-driven appointment orchestration for clinics and multi-specialty hospitals.',
    icon: '📅',
  },
  {
    title: 'Digital Diagnostics',
    description: 'Integrated lab, radiology, and RIS with instant results delivery.',
    icon: '🔬',
  },
  {
    title: 'Revenue Cycle Management',
    description: 'Automated clinical billing, insurance claims, and transparent forensics.',
    icon: '💳',
  },
  {
    title: 'Pharmacy Operations',
    description: 'Real-time inventory, FEFO tracking, and e-prescription fulfillment.',
    icon: '💊',
  },
  {
    title: 'Advanced Analytics',
    description: 'Operational KPIs, bed occupancy, and clinical outcomes reporting.',
    icon: '📊',
  },
]

const safetyCertifications = ['HIPAA Compliant', 'GDPR Data Privacy', 'HL7/FHIR Ready', 'ISO 27001 Certified']

export function HomePage() {
  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-emerald-100 selection:text-emerald-900">
      {/* Hero Section */}
      <section className="relative overflow-hidden bg-slate-950 px-6 py-24 text-white lg:py-32">
        {/* Background Gradients */}
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.22),transparent_35%),radial-gradient(circle_at_80%_80%,rgba(14,165,233,0.18),transparent_35%)]" />
        
        <div className="relative mx-auto max-w-7xl">
          <div className="grid items-center gap-16 lg:grid-cols-2">
            <motion.div 
              initial={{ opacity: 0, y: 20 }} 
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
            >
              <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-bold tracking-wider text-emerald-400 uppercase">
                <span className="relative flex h-2 w-2">
                  <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                  <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                State-of-the-Art Care Management
              </div>
              
              <h1 className="text-4xl font-extrabold leading-tight tracking-tight lg:text-6xl">
                The Future of <span className="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">Patient-Centric</span> Hospital Operations
              </h1>
              
              <p className="mt-6 max-w-xl text-lg leading-relaxed text-slate-300">
                Ariba HMS provides clinical-grade SaaS tools to unify patient data, streamline surgeries, and automate financials for clinics and international hospital chains.
              </p>
              
              <div className="mt-10 flex flex-wrap gap-4">
                <NavLink 
                  to="/auth/register" 
                  className="rounded-full bg-emerald-500 px-8 py-4 font-bold text-white shadow-lg shadow-emerald-500/25 transition-all hover:scale-105 hover:bg-emerald-400"
                >
                  Request Demo
                </NavLink>
                <NavLink 
                  to="/features" 
                  className="rounded-full border border-white/20 bg-white/5 px-8 py-4 font-bold text-slate-200 backdrop-blur transition-all hover:bg-white/10"
                >
                  Explore Platform
                </NavLink>
              </div>

              {/* Trust Indicators */}
              <div className="mt-12 flex items-center gap-8 grayscale opacity-50 contrast-125">
                <p className="text-xs font-medium uppercase tracking-widest text-slate-500">Trusted By</p>
                <div className="flex gap-6 font-bold text-xl tracking-tighter">
                  <span>METROCARE</span>
                  <span>MEDIVISION</span>
                  <span>HEALNET</span>
                </div>
              </div>
            </motion.div>

            <motion.div 
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="relative"
            >
              <div className="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-emerald-500/20 to-sky-500/20 blur-3xl" />
              <img 
                src={heroImg} 
                alt="Ariba HMS Futuristic Interface" 
                className="relative rounded-2xl border border-white/10 shadow-2xl"
              />
            </motion.div>
          </div>
        </div>
      </section>

      {/* Stats / Dashboard Preview Section */}
      <section className="mx-auto -mt-12 max-w-7xl px-6">
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
          {[
            { label: 'Patient Consultations', value: '4,120+', trend: '+12% this week', color: 'text-emerald-500' },
            { label: 'Avg. Bed Occupancy', value: '89.4%', trend: 'Operational Peak', color: 'text-sky-500' },
            { label: 'Clinical TAT', value: '18 min', trend: '-22% efficiency', color: 'text-teal-500' },
            { label: 'Global Availability', value: '99.98%', trend: 'SLA Guaranteed', color: 'text-indigo-400' },
          ].map((stat, i) => (
            <motion.div 
              key={stat.label}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.1 }}
              className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-100"
            >
              <p className="text-xs font-bold uppercase tracking-widest text-slate-500">{stat.label}</p>
              <p className={`mt-2 text-3xl font-extrabold tracking-tight ${stat.color}`}>{stat.value}</p>
              <p className="mt-1 text-xs font-medium text-slate-400">{stat.trend}</p>
            </motion.div>
          ))}
        </div>
      </section>

      {/* Main Feature Showcase */}
      <section className="py-24 lg:py-32">
        <div className="mx-auto max-w-7xl px-6">
          <div className="text-center">
            <h2 className="text-sm font-bold tracking-widest text-emerald-600 uppercase">Modular Architecture</h2>
            <p className="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 lg:text-5xl">
              Enterprise-Grade Clinical Workflows
            </p>
            <p className="mx-auto mt-5 max-w-2xl text-lg text-slate-600">
              Ariba HMS scales with your institution, providing secure modules designed for specialized clinicians and high-volume administrators.
            </p>
          </div>

          <div className="mt-20 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            {clinicalModules.map((module) => (
              <motion.div 
                key={module.title}
                whileHover={{ y: -5 }}
                className="group relative rounded-3xl border border-slate-200 bg-white p-8 transition-all hover:border-emerald-200 hover:shadow-xl"
              >
                <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-3xl transition-colors group-hover:bg-emerald-50">
                  {module.icon}
                </div>
                <h3 className="text-xl font-bold text-slate-900">{module.title}</h3>
                <p className="mt-3 text-slate-600 leading-relaxed">{module.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Security & Compliance Section */}
      <section className="bg-slate-950 py-24 text-white lg:py-32">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid items-center gap-16 lg:grid-cols-2">
            <div className="order-2 lg:order-1">
              <img 
                src={securityImg} 
                alt="HIPAA Compliant Healthcare Security" 
                className="rounded-3xl border border-white/10 shadow-2xl"
              />
            </div>
            <div className="order-1 lg:order-2">
              <h2 className="text-sm font-bold tracking-widest text-emerald-400 uppercase">Security First</h2>
              <p className="mt-3 text-3xl font-extrabold tracking-tight lg:text-5xl">
                Uncompromising Data Privacy
              </p>
              <p className="mt-6 text-lg text-slate-400 leading-relaxed">
                Patient data is your most sensitive asset. Our cloud environment is built on zero-trust principles, featuring end-to-end encryption and real-time audit logging.
              </p>
              
              <div className="mt-10 grid gap-4 sm:grid-cols-2">
                {safetyCertifications.map((cert) => (
                  <div key={cert} className="flex items-center gap-3">
                    <div className="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                      ✓
                    </div>
                    <span className="font-semibold text-slate-200">{cert}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Dashboard Sneak Peek */}
      <section className="py-24 lg:py-32 bg-white">
        <div className="mx-auto max-w-7xl px-6 text-center">
          <h2 className="text-3xl font-extrabold text-slate-900 lg:text-5xl">Clean UI for Critical Care</h2>
          <p className="mt-4 text-slate-600">Reduce clinician burnout with our distraction-free, high-performance interface.</p>
          <div className="mt-16 rounded-3xl border border-slate-200 p-2 shadow-2xl lg:p-4">
            <img 
              src={dashboardImg} 
              alt="Ariba HMS Dashboard Preview" 
              className="rounded-2xl"
            />
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="py-24 bg-slate-50">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid gap-8 md:grid-cols-3">
            {[
              {
                quote: "Ariba HMS cut our bed management wait times by 40%. The real-time synchronization is a game changer for our ER team.",
                author: "Dr. Sarah Chen",
                role: "Clinical Director, MetroCare"
              },
              {
                quote: "The billing transparency and insurance claim success rate improved significantly in just the first quarter of deployment.",
                author: "Robert Miller",
                role: "Operations Head, HealNet"
              },
              {
                quote: "Finally, a cloud HMS that doctors actually enjoy using. The mobile-responsive interface is perfect for rounds.",
                author: "Nurse Elena V.",
                role: "Senior Staff Nurse, Medivision"
              }
            ].map((t) => (
              <blockquote key={t.author} className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-shadow hover:shadow-md">
                <p className="text-slate-700 italic">“{t.quote}”</p>
                <div className="mt-6">
                  <p className="font-bold text-slate-900">{t.author}</p>
                  <p className="text-sm text-slate-500">{t.role}</p>
                </div>
              </blockquote>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="px-6 py-24 lg:py-32">
        <div className="mx-auto max-w-5xl rounded-[3rem] bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 px-8 py-16 text-center text-white shadow-2xl relative overflow-hidden">
          <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,rgba(16,185,129,0.15),transparent_70%)]" />
          <div className="relative z-10">
            <h2 className="text-3xl font-extrabold lg:text-5xl">Launch Your Digital Hospital Today</h2>
            <p className="mx-auto mt-6 max-w-xl text-lg text-emerald-100/60 leading-relaxed">
              Join leading healthcare institutions transforming their operations with the Ariba Cloud Hospital Management System.
            </p>
            <div className="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
              <NavLink 
                to="/auth/register" 
                className="w-full rounded-full bg-emerald-500 px-10 py-4 font-bold text-white transition-all hover:bg-emerald-400 sm:w-auto"
              >
                Request Custom Demo
              </NavLink>
              <button className="text-emerald-400 font-bold hover:text-emerald-300">
                Contact Sales Support →
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Footer Branding */}
      <footer className="border-t border-slate-200 py-10 text-center text-sm text-slate-500">
        <p>&copy; 2026 Ariba Healthcare Systems. Empowering clinical excellence through technology.</p>
      </footer>
    </div>
  )
}