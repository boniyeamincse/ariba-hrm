import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import {
  BadgeCheck,
  Building2,
  CheckCircle2,
  Copy,
  FileSpreadsheet,
  LockKeyhole,
  Mail,
  PlusCircle,
  RefreshCcw,
  Receipt,
  ShieldCheck,
  Sparkles,
  UsersRound,
} from 'lucide-react'

type UsersTab =
  | 'tenants'
  | 'create-tenant'
  | 'tenant-details'
  | 'tenant-status'
  | 'tenant-delete'
  | 'all-users'
  | 'assign-roles'

type TenantFormState = {
  hospitalName: string
  legalEntityName: string
  hospitalType: string
  registrationNumber: string
  totalBranches: string
  bedCapacity: string
  country: string
  city: string
  timezone: string
  contactEmail: string
  contactPhone: string
  address: string
  subdomain: string
  plan: 'starter' | 'growth' | 'enterprise'
  billingCycle: 'monthly' | 'annual'
  userSeats: string
  invoiceEmail: string
  billingContact: string
  taxId: string
  adminFullName: string
  adminEmail: string
  adminPassword: string
  confirmPassword: string
}

const validTabs: UsersTab[] = [
  'tenants',
  'create-tenant',
  'tenant-details',
  'tenant-status',
  'tenant-delete',
  'all-users',
  'assign-roles',
]

const tabOptions: Array<{ id: UsersTab; label: string; icon: typeof Building2 }> = [
  { id: 'tenants', label: 'Tenant List', icon: Building2 },
  { id: 'create-tenant', label: 'Create Tenant', icon: PlusCircle },
  { id: 'all-users', label: 'All Users', icon: UsersRound },
  { id: 'tenant-status', label: 'Tenant Status', icon: ShieldCheck },
]

const planConfig = {
  starter: {
    label: 'Starter Care',
    monthlyPrice: 299,
    annualPrice: 2990,
    includedSeats: 25,
    invoiceNote: 'Best for a single-site clinic or small specialty center.',
  },
  growth: {
    label: 'Growth Network',
    monthlyPrice: 649,
    annualPrice: 6490,
    includedSeats: 75,
    invoiceNote: 'Built for hospitals scaling outpatient and inpatient operations.',
  },
  enterprise: {
    label: 'Enterprise Command',
    monthlyPrice: 1199,
    annualPrice: 11990,
    includedSeats: 150,
    invoiceNote: 'For multi-branch systems with deep integration and reporting needs.',
  },
} satisfies Record<TenantFormState['plan'], {
  label: string
  monthlyPrice: number
  annualPrice: number
  includedSeats: number
  invoiceNote: string
}>

const tenantRows = [
  { name: 'North Star Medical Center', code: 'TNT-NORTH-2026-1842', plan: 'Enterprise Command', city: 'Dhaka', status: 'Active' },
  { name: 'Green Valley Hospital', code: 'TNT-GREEN-2026-1843', plan: 'Growth Network', city: 'Chattogram', status: 'Provisioning' },
  { name: 'City Heart Clinic', code: 'TNT-CITYH-2026-1844', plan: 'Starter Care', city: 'Khulna', status: 'Trial' },
]

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(amount)

const normalizeSlug = (value: string) =>
  value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

const getTabValue = (value: string | null): UsersTab => {
  if (value && validTabs.includes(value as UsersTab)) {
    return value as UsersTab
  }

  return 'tenants'
}

const buildTenantId = (hospitalName: string, suffix: string) => {
  const cleaned = normalizeSlug(hospitalName || 'tenant').replace(/-/g, '').slice(0, 6).toUpperCase() || 'TENANT'
  return `TNT-${cleaned}-${new Date().getFullYear()}-${suffix}`
}

const getRandomSuffix = () => String(Math.floor(1000 + Math.random() * 9000))

const initialFormState: TenantFormState = {
  hospitalName: '',
  legalEntityName: '',
  hospitalType: 'General Hospital',
  registrationNumber: '',
  totalBranches: '1',
  bedCapacity: '120',
  country: 'Bangladesh',
  city: 'Dhaka',
  timezone: 'Asia/Dhaka',
  contactEmail: '',
  contactPhone: '',
  address: '',
  subdomain: '',
  plan: 'growth',
  billingCycle: 'monthly',
  userSeats: '80',
  invoiceEmail: '',
  billingContact: '',
  taxId: '',
  adminFullName: '',
  adminEmail: '',
  adminPassword: '',
  confirmPassword: '',
}

export function UsersPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const activeTab = getTabValue(searchParams.get('tab'))
  const [form, setForm] = useState<TenantFormState>(initialFormState)
  const [tenantSuffix, setTenantSuffix] = useState(getRandomSuffix)
  const [tenantId, setTenantId] = useState(buildTenantId('', getRandomSuffix()))
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [copiedTenantId, setCopiedTenantId] = useState(false)
  const [submitState, setSubmitState] = useState<null | {
    tenantId: string
    hospitalName: string
    adminEmail: string
    subdomain: string
    total: number
  }>(null)
  const [submitError, setSubmitError] = useState('')

  useEffect(() => {
    setTenantId(buildTenantId(form.hospitalName, tenantSuffix))
  }, [form.hospitalName, tenantSuffix])

  useEffect(() => {
    if (!form.subdomain && form.hospitalName) {
      setForm((current) => ({
        ...current,
        subdomain: normalizeSlug(form.hospitalName).slice(0, 24),
      }))
    }
  }, [form.hospitalName, form.subdomain])

  const selectedPlan = planConfig[form.plan]
  const seats = Number(form.userSeats || 0)
  const extraSeats = Math.max(seats - selectedPlan.includedSeats, 0)
  const seatUnitPrice = form.billingCycle === 'monthly' ? 8 : 80
  const baseAmount = form.billingCycle === 'monthly' ? selectedPlan.monthlyPrice : selectedPlan.annualPrice
  const extraSeatAmount = extraSeats * seatUnitPrice
  const setupAmount = form.plan === 'enterprise' ? 799 : 299
  const subtotal = baseAmount + extraSeatAmount + setupAmount
  const taxAmount = Math.round(subtotal * 0.05)
  const grandTotal = subtotal + taxAmount

  const readinessChecks = useMemo(() => [
    { label: 'Hospital profile complete', complete: Boolean(form.hospitalName && form.legalEntityName && form.registrationNumber) },
    { label: 'Contact and address captured', complete: Boolean(form.contactEmail && form.contactPhone && form.address) },
    { label: 'Billing destination confirmed', complete: Boolean(form.invoiceEmail && form.billingContact) },
    { label: 'Initial admin secured', complete: Boolean(form.adminFullName && form.adminEmail && form.adminPassword && form.confirmPassword) },
  ], [form])

  const completedChecks = readinessChecks.filter((item) => item.complete).length

  const setTab = (tab: UsersTab) => {
    if (tab === 'tenants') {
      setSearchParams({})
      return
    }

    setSearchParams({ tab })
  }

  const updateField = <K extends keyof TenantFormState>(field: K, value: TenantFormState[K]) => {
    setForm((current) => ({ ...current, [field]: value }))
  }

  const regenerateTenantId = () => {
    const next = getRandomSuffix()
    setTenantSuffix(next)
    setCopiedTenantId(false)
  }

  const copyTenantId = async () => {
    await navigator.clipboard.writeText(tenantId)
    setCopiedTenantId(true)
    window.setTimeout(() => setCopiedTenantId(false), 1600)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setSubmitError('')

    if (form.adminPassword !== form.confirmPassword) {
      setSubmitError('Admin password and confirmation must match.')
      return
    }

    if (form.adminPassword.length < 8) {
      setSubmitError('Admin password must be at least 8 characters.')
      return
    }

    setIsSubmitting(true)

    await new Promise((resolve) => window.setTimeout(resolve, 900))

    setSubmitState({
      tenantId,
      hospitalName: form.hospitalName || 'New Hospital',
      adminEmail: form.adminEmail,
      subdomain: form.subdomain,
      total: grandTotal,
    })
    setIsSubmitting(false)
  }

  const renderPlaceholderPanel = (title: string, description: string) => (
    <div className="rounded-3xl border border-slate-200 bg-white p-8 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
      <div className="max-w-2xl">
        <p className="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700">
          Tenant Operations Workspace
        </p>
        <h2 className="mt-4 text-2xl font-semibold text-slate-900">{title}</h2>
        <p className="mt-2 text-slate-600">{description}</p>
      </div>
      <div className="mt-8 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
        Frontend shell is ready. API integration, live search, and workflow actions can be wired next.
      </div>
    </div>
  )

  return (
    <div className="space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-emerald-100 via-white to-cyan-100 p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div className="max-w-3xl">
            <p className="inline-flex rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-emerald-700 uppercase">
              Super Admin Tenant Factory
            </p>
            <h1 className="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Create tenant workspaces with billing, invoice, and admin provisioning in one flow.</h1>
            <p className="mt-3 text-slate-600">
              Capture full hospital information, verify subscription billing, issue the first invoice preview, assign the admin email and password,
              and generate a unique tenant ID before activation.
            </p>
          </div>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:min-w-[420px]">
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Total Hospitals</p>
              <p className="mt-2 text-3xl font-black text-slate-900">128</p>
              <p className="mt-1 text-xs text-slate-500">8 new workspaces this month</p>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Billing Ready</p>
              <p className="mt-2 text-3xl font-black text-slate-900">94%</p>
              <p className="mt-1 text-xs text-slate-500">Provisioning quality score</p>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Draft Invoices</p>
              <p className="mt-2 text-3xl font-black text-slate-900">9</p>
              <p className="mt-1 text-xs text-slate-500">Awaiting confirmation</p>
            </div>
          </div>
        </div>
      </section>

      <div className="rounded-2xl border border-slate-200 bg-white p-1 shadow-[0_10px_30px_-25px_rgba(15,23,42,0.25)]">
        <div className="flex flex-wrap gap-1">
          {tabOptions.map((tab) => {
            const Icon = tab.icon
            const isActive = activeTab === tab.id

            return (
              <button
                key={tab.id}
                onClick={() => setTab(tab.id)}
                className={[
                  'flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition-all',
                  isActive
                    ? 'border-b-2 border-emerald-500 bg-emerald-50 text-emerald-900'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                ].join(' ')}
              >
                <Icon className="h-4 w-4" />
                {tab.label}
              </button>
            )
          })}
        </div>
      </div>

      {activeTab === 'tenants' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.45fr_0.85fr]">
          <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-2xl font-semibold text-slate-900">Tenant List</h2>
                <p className="mt-1 text-sm text-slate-500">Live hospital workspaces with provisioning and billing status.</p>
              </div>
              <button
                onClick={() => setTab('create-tenant')}
                className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500"
              >
                Create Tenant
              </button>
            </div>

            <div className="mt-6 overflow-hidden rounded-2xl border border-slate-200">
              <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead className="bg-slate-50 text-slate-500">
                  <tr>
                    <th className="px-4 py-3 font-medium">Hospital</th>
                    <th className="px-4 py-3 font-medium">Tenant ID</th>
                    <th className="px-4 py-3 font-medium">Plan</th>
                    <th className="px-4 py-3 font-medium">City</th>
                    <th className="px-4 py-3 font-medium">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200 bg-white">
                  {tenantRows.map((row) => (
                    <tr key={row.code} className="hover:bg-slate-50">
                      <td className="px-4 py-3 font-medium text-slate-900">{row.name}</td>
                      <td className="px-4 py-3 font-mono text-xs text-slate-600">{row.code}</td>
                      <td className="px-4 py-3 text-slate-600">{row.plan}</td>
                      <td className="px-4 py-3 text-slate-600">{row.city}</td>
                      <td className="px-4 py-3">
                        <span className="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                          {row.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Portfolio Snapshot</h3>
              <div className="mt-4 grid grid-cols-1 gap-3">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">ARR Pipeline</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">{formatCurrency(487200)}</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Invoices Due This Week</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">14</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Pending Activations</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">3</p>
                </div>
              </div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Next Recommended Action</h3>
              <p className="mt-2 text-sm text-slate-600">Create the next tenant with the guided workflow so billing, invoice data, and first admin access are provisioned together.</p>
              <button
                onClick={() => setTab('create-tenant')}
                className="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
              >
                Open Create Tenant Workspace
              </button>
            </div>
          </aside>
        </div>
      )}

      {activeTab === 'create-tenant' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.45fr_0.85fr]">
          <form onSubmit={handleSubmit} className="space-y-6">
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <p className="inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-700">
                    Hospital Information
                  </p>
                  <h2 className="mt-3 text-2xl font-semibold text-slate-900">Total hospital information</h2>
                  <p className="mt-2 text-sm text-slate-600">Capture the hospital identity, legal registration, branch count, and operational setup before creating the tenant.</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-right">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Unique Tenant ID</p>
                  <p className="mt-1 font-mono text-sm font-semibold text-slate-900">{tenantId}</p>
                </div>
              </div>

              <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Hospital Name</span>
                  <input
                    value={form.hospitalName}
                    onChange={(event) => updateField('hospitalName', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="MedCore Central Hospital"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Legal Entity Name</span>
                  <input
                    value={form.legalEntityName}
                    onChange={(event) => updateField('legalEntityName', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="MedCore Health Services Ltd."
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Hospital Type</span>
                  <select
                    value={form.hospitalType}
                    onChange={(event) => updateField('hospitalType', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  >
                    <option>General Hospital</option>
                    <option>Specialized Hospital</option>
                    <option>Clinic Network</option>
                    <option>Diagnostic Center</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Registration Number</span>
                  <input
                    value={form.registrationNumber}
                    onChange={(event) => updateField('registrationNumber', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="REG-2026-00192"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Total Branches</span>
                  <input
                    type="number"
                    min="1"
                    value={form.totalBranches}
                    onChange={(event) => updateField('totalBranches', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Bed Capacity</span>
                  <input
                    type="number"
                    min="0"
                    value={form.bedCapacity}
                    onChange={(event) => updateField('bedCapacity', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Country</span>
                  <input
                    value={form.country}
                    onChange={(event) => updateField('country', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">City</span>
                  <input
                    value={form.city}
                    onChange={(event) => updateField('city', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Contact Email</span>
                  <input
                    type="email"
                    value={form.contactEmail}
                    onChange={(event) => updateField('contactEmail', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="ops@hospital.com"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Contact Phone</span>
                  <input
                    value={form.contactPhone}
                    onChange={(event) => updateField('contactPhone', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="+880 1712 345 678"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Timezone</span>
                  <select
                    value={form.timezone}
                    onChange={(event) => updateField('timezone', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  >
                    <option>Asia/Dhaka</option>
                    <option>Asia/Kolkata</option>
                    <option>UTC</option>
                  </select>
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Hospital Address</span>
                  <textarea
                    value={form.address}
                    onChange={(event) => updateField('address', event.target.value)}
                    rows={3}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="Full hospital address"
                    required
                  />
                </label>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div>
                <p className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold tracking-wide text-amber-700">
                  Billing Check
                </p>
                <h2 className="mt-3 text-2xl font-semibold text-slate-900">Plan, billing, and invoice setup</h2>
                <p className="mt-2 text-sm text-slate-600">Check billing readiness before tenant creation. The invoice preview updates live as you select the plan and user count.</p>
              </div>

              <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Subscription Plan</span>
                  <select
                    value={form.plan}
                    onChange={(event) => updateField('plan', event.target.value as TenantFormState['plan'])}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                  >
                    <option value="starter">Starter Care</option>
                    <option value="growth">Growth Network</option>
                    <option value="enterprise">Enterprise Command</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Billing Cycle</span>
                  <select
                    value={form.billingCycle}
                    onChange={(event) => updateField('billingCycle', event.target.value as TenantFormState['billingCycle'])}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                  >
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Licensed User Seats</span>
                  <input
                    type="number"
                    min="1"
                    value={form.userSeats}
                    onChange={(event) => updateField('userSeats', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Tenant Subdomain</span>
                  <input
                    value={form.subdomain}
                    onChange={(event) => updateField('subdomain', normalizeSlug(event.target.value))}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                    placeholder="medcore-central"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Billing Contact</span>
                  <input
                    value={form.billingContact}
                    onChange={(event) => updateField('billingContact', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                    placeholder="Finance Controller"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Invoice Email</span>
                  <input
                    type="email"
                    value={form.invoiceEmail}
                    onChange={(event) => updateField('invoiceEmail', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                    placeholder="billing@hospital.com"
                    required
                  />
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Tax / VAT ID</span>
                  <input
                    value={form.taxId}
                    onChange={(event) => updateField('taxId', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100"
                    placeholder="VAT-99887766"
                  />
                </label>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div>
                <p className="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700">
                  User Email and Password
                </p>
                <h2 className="mt-3 text-2xl font-semibold text-slate-900">Create the first tenant admin account</h2>
                <p className="mt-2 text-sm text-slate-600">This admin gets the initial login after the tenant is created. Capture the email and password now so provisioning stays complete.</p>
              </div>

              <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Admin Full Name</span>
                  <input
                    value={form.adminFullName}
                    onChange={(event) => updateField('adminFullName', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="Amina Rahman"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Admin Email</span>
                  <input
                    type="email"
                    value={form.adminEmail}
                    onChange={(event) => updateField('adminEmail', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="admin@hospital.com"
                    required
                  />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Temporary Password</span>
                  <input
                    type="password"
                    value={form.adminPassword}
                    onChange={(event) => updateField('adminPassword', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="Minimum 8 characters"
                    required
                  />
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Confirm Password</span>
                  <input
                    type="password"
                    value={form.confirmPassword}
                    onChange={(event) => updateField('confirmPassword', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                    placeholder="Retype password"
                    required
                  />
                </label>
              </div>

              {submitError && (
                <div className="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                  {submitError}
                </div>
              )}

              <div className="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-sm text-slate-500">
                  Submit the create tenant request once hospital information, billing, invoice, and admin access are complete.
                </div>
                <div className="flex gap-3">
                  <button
                    type="button"
                    onClick={() => {
                      setForm(initialFormState)
                      setSubmitError('')
                      setSubmitState(null)
                      setTenantSuffix(getRandomSuffix())
                    }}
                    className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                  >
                    Reset Form
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {isSubmitting ? 'Creating Tenant...' : 'Submit Create Tenant'}
                  </button>
                </div>
              </div>
            </section>
          </form>

          <aside className="space-y-6">
            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <h3 className="text-lg font-semibold text-slate-900">Tenant ID and workspace</h3>
                  <p className="mt-1 text-sm text-slate-500">Unique identifier used for billing, onboarding, and environment setup.</p>
                </div>
                <Sparkles className="h-5 w-5 text-emerald-600" />
              </div>
              <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p className="text-xs uppercase tracking-wide text-slate-500">Generated Tenant ID</p>
                <p className="mt-2 font-mono text-sm font-semibold text-slate-900">{tenantId}</p>
                <p className="mt-3 text-xs text-slate-500">Workspace URL</p>
                <p className="mt-1 text-sm text-slate-700">https://{form.subdomain || 'hospital-name'}.medcore.local</p>
              </div>
              <div className="mt-4 flex gap-3">
                <button
                  type="button"
                  onClick={copyTenantId}
                  className="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                >
                  <span className="inline-flex items-center gap-2">
                    <Copy className="h-4 w-4" />
                    {copiedTenantId ? 'Copied' : 'Copy ID'}
                  </span>
                </button>
                <button
                  type="button"
                  onClick={regenerateTenantId}
                  className="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500"
                >
                  <span className="inline-flex items-center gap-2">
                    <RefreshCcw className="h-4 w-4" />
                    Regenerate
                  </span>
                </button>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-center justify-between gap-3">
                <h3 className="text-lg font-semibold text-slate-900">Billing and invoice check</h3>
                <Receipt className="h-5 w-5 text-amber-600" />
              </div>
              <div className="mt-4 space-y-3">
                {readinessChecks.map((item) => (
                  <div key={item.label} className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <span className="text-slate-700">{item.label}</span>
                    <span className={item.complete ? 'text-emerald-700' : 'text-slate-400'}>
                      <CheckCircle2 className="h-4 w-4" />
                    </span>
                  </div>
                ))}
              </div>
              <div className="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p className="text-xs uppercase tracking-wide text-emerald-700">Readiness Score</p>
                <p className="mt-1 text-2xl font-bold text-emerald-900">{completedChecks}/4</p>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <h3 className="text-lg font-semibold text-slate-900">Invoice preview</h3>
                  <p className="mt-1 text-sm text-slate-500">First invoice generated from the selected plan and seat count.</p>
                </div>
                <FileSpreadsheet className="h-5 w-5 text-cyan-600" />
              </div>
              <div className="mt-4 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div className="flex items-center justify-between text-sm text-slate-700">
                  <span>{selectedPlan.label}</span>
                  <span>{formatCurrency(baseAmount)}</span>
                </div>
                <div className="flex items-center justify-between text-sm text-slate-700">
                  <span>Extra seats ({extraSeats})</span>
                  <span>{formatCurrency(extraSeatAmount)}</span>
                </div>
                <div className="flex items-center justify-between text-sm text-slate-700">
                  <span>Implementation setup</span>
                  <span>{formatCurrency(setupAmount)}</span>
                </div>
                <div className="flex items-center justify-between text-sm text-slate-700">
                  <span>Tax (5%)</span>
                  <span>{formatCurrency(taxAmount)}</span>
                </div>
                <div className="border-t border-slate-200 pt-3">
                  <div className="flex items-center justify-between text-base font-semibold text-slate-900">
                    <span>Total</span>
                    <span>{formatCurrency(grandTotal)}</span>
                  </div>
                </div>
              </div>
              <p className="mt-3 text-xs text-slate-500">{selectedPlan.invoiceNote}</p>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-center gap-2">
                <Mail className="h-4 w-4 text-emerald-600" />
                <h3 className="text-lg font-semibold text-slate-900">Provisioning summary</h3>
              </div>
              <div className="mt-4 space-y-3 text-sm">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Billing Destination</p>
                  <p className="mt-1 font-medium text-slate-900">{form.invoiceEmail || 'billing@hospital.com'}</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Admin Login</p>
                  <p className="mt-1 font-medium text-slate-900">{form.adminEmail || 'admin@hospital.com'}</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Password Policy</p>
                  <p className="mt-1 font-medium text-slate-900 inline-flex items-center gap-2">
                    <LockKeyhole className="h-4 w-4 text-slate-500" />
                    Minimum 8 characters with secure handoff
                  </p>
                </div>
              </div>
            </section>

            {submitState && (
              <section className="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
                <div className="flex items-center gap-2">
                  <BadgeCheck className="h-5 w-5 text-emerald-700" />
                  <h3 className="text-lg font-semibold text-emerald-900">Tenant request prepared</h3>
                </div>
                <div className="mt-4 space-y-2 text-sm text-emerald-900">
                  <p><span className="font-semibold">Hospital:</span> {submitState.hospitalName}</p>
                  <p><span className="font-semibold">Tenant ID:</span> {submitState.tenantId}</p>
                  <p><span className="font-semibold">Admin Email:</span> {submitState.adminEmail}</p>
                  <p><span className="font-semibold">Workspace:</span> https://{submitState.subdomain}.medcore.local</p>
                  <p><span className="font-semibold">Invoice Total:</span> {formatCurrency(submitState.total)}</p>
                </div>
              </section>
            )}
          </aside>
        </div>
      )}

      {activeTab === 'all-users' && renderPlaceholderPanel(
        'Cross-tenant user directory',
        'This tab can host the global user directory, password resets, invite flows, and role-level provisioning after the tenant is created.'
      )}

      {activeTab === 'tenant-status' && renderPlaceholderPanel(
        'Tenant activation and suspension',
        'This tab can manage status transitions such as trial, active, suspended, and archived once the create-tenant flow is connected to the backend.'
      )}

      {activeTab === 'tenant-details' && renderPlaceholderPanel(
        'Tenant details and edits',
        'Use this tab for branch updates, contract amendments, and hospital metadata changes.'
      )}

      {activeTab === 'tenant-delete' && renderPlaceholderPanel(
        'Tenant decommissioning',
        'This tab will hold archive, export, and deletion safeguards for hospital workspaces.'
      )}

      {activeTab === 'assign-roles' && renderPlaceholderPanel(
        'Assign roles across tenants',
        'This tab can later host super-admin role assignment and privileged user provisioning tools.'
      )}
    </div>
  )
}