import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import {
  AlertTriangle,
  BadgeCheck,
  Building2,
  CheckCircle2,
  Copy,
  FileSpreadsheet,
  LockKeyhole,
  Mail,
  PlusCircle,
  Power,
  Receipt,
  RefreshCcw,
  Search,
  ShieldCheck,
  Sparkles,
  Trash2,
  UsersRound,
} from 'lucide-react'
import { api } from '../../lib/api'

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

type ApiTenant = {
  id: number
  name: string
  subdomain: string
  database_name: string
  status: string
  metadata?: {
    hospital?: Record<string, string>
    contact?: Record<string, string>
    billing?: Record<string, string>
    admin?: Record<string, string>
    technical?: Record<string, string>
    notes?: string
  }
  created_at?: string
  updated_at?: string
}

type TenantMeta = {
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
  plan: TenantFormState['plan']
  billingCycle: TenantFormState['billingCycle']
  userSeats: string
  invoiceEmail: string
  billingContact: string
  taxId: string
  adminFullName: string
  adminEmail: string
  tenantId: string
}

type TenantRecord = ApiTenant & {
  meta: TenantMeta
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
  { id: 'tenant-details', label: 'Tenant Details', icon: Sparkles },
  { id: 'tenant-status', label: 'Tenant Status', icon: Power },
  { id: 'tenant-delete', label: 'Delete Tenant', icon: Trash2 },
  { id: 'all-users', label: 'All Users', icon: UsersRound },
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

const seedRows = [
  { name: 'North Star Medical Center', code: 'TNT-NORTH-2026-1842', plan: 'enterprise' as const, city: 'Dhaka', status: 'active', subdomain: 'north-star-medical' },
  { name: 'Green Valley Hospital', code: 'TNT-GREEN-2026-1843', plan: 'growth' as const, city: 'Chattogram', status: 'provisioning', subdomain: 'green-valley-hospital' },
  { name: 'City Heart Clinic', code: 'TNT-CITYH-2026-1844', plan: 'starter' as const, city: 'Khulna', status: 'trial', subdomain: 'city-heart-clinic' },
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

const buildDatabaseName = (subdomain: string) => `tenant_${subdomain.replace(/-/g, '_')}`.slice(0, 64)

const buildTenantId = (hospitalName: string, suffix: string) => {
  const cleaned = normalizeSlug(hospitalName || 'tenant').replace(/-/g, '').slice(0, 6).toUpperCase() || 'TENANT'
  return `TNT-${cleaned}-${new Date().getFullYear()}-${suffix}`
}

const getRandomSuffix = () => String(Math.floor(1000 + Math.random() * 9000))

const getTabValue = (value: string | null): UsersTab => {
  if (value === 'tenant-edit') {
    return 'tenant-details'
  }

  if (value && validTabs.includes(value as UsersTab)) {
    return value as UsersTab
  }

  return 'tenants'
}

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

const buildMetaFromForm = (form: TenantFormState, tenantId: string): TenantMeta => ({
  legalEntityName: form.legalEntityName,
  hospitalType: form.hospitalType,
  registrationNumber: form.registrationNumber,
  totalBranches: form.totalBranches,
  bedCapacity: form.bedCapacity,
  country: form.country,
  city: form.city,
  timezone: form.timezone,
  contactEmail: form.contactEmail,
  contactPhone: form.contactPhone,
  address: form.address,
  plan: form.plan,
  billingCycle: form.billingCycle,
  userSeats: form.userSeats,
  invoiceEmail: form.invoiceEmail,
  billingContact: form.billingContact,
  taxId: form.taxId,
  adminFullName: form.adminFullName,
  adminEmail: form.adminEmail,
  tenantId,
})

const buildFallbackMeta = (tenant: ApiTenant, index: number): TenantMeta => {
  const seed = seedRows[index % seedRows.length]

  return {
    legalEntityName: `${tenant.name} Limited`,
    hospitalType: index % 2 === 0 ? 'General Hospital' : 'Specialized Hospital',
    registrationNumber: `REG-${new Date(tenant.created_at ?? Date.now()).getFullYear()}-${String(tenant.id).padStart(5, '0')}`,
    totalBranches: String((index % 4) + 1),
    bedCapacity: String(120 + index * 25),
    country: 'Bangladesh',
    city: seed.city,
    timezone: 'Asia/Dhaka',
    contactEmail: `ops@${tenant.subdomain}.com`,
    contactPhone: '+880 1712 345 678',
    address: `${seed.city} Clinical District, Main Avenue`,
    plan: seed.plan,
    billingCycle: index % 2 === 0 ? 'monthly' : 'annual',
    userSeats: String(planConfig[seed.plan].includedSeats),
    invoiceEmail: `billing@${tenant.subdomain}.com`,
    billingContact: 'Finance Controller',
    taxId: `VAT-${String(tenant.id).padStart(8, '0')}`,
    adminFullName: 'Hospital Admin',
    adminEmail: `admin@${tenant.subdomain}.com`,
    tenantId: buildTenantId(tenant.name, String(tenant.id).padStart(4, '0')),
  }
}

const metaFromApi = (tenant: ApiTenant, fallbackIndex: number): TenantMeta => {
  const m = tenant.metadata
  if (!m) return buildFallbackMeta(tenant, fallbackIndex)
  const seed = seedRows[fallbackIndex % seedRows.length]
  return {
    legalEntityName: m.hospital?.legal_entity_name ?? `${tenant.name} Limited`,
    hospitalType: m.hospital?.hospital_type ?? 'General Hospital',
    registrationNumber: m.hospital?.registration_number ?? '',
    totalBranches: m.hospital?.total_branches ?? '1',
    bedCapacity: m.hospital?.bed_capacity ?? '120',
    country: m.hospital?.country ?? 'Bangladesh',
    city: m.hospital?.city ?? seed.city,
    timezone: m.hospital?.timezone ?? 'Asia/Dhaka',
    contactEmail: m.contact?.email ?? `ops@${tenant.subdomain}.com`,
    contactPhone: m.contact?.phone ?? '+880 1712 345 678',
    address: m.contact?.address ?? `${seed.city} Clinical District, Main Avenue`,
    plan: (m.billing?.plan ?? 'growth') as TenantFormState['plan'],
    billingCycle: (m.billing?.cycle ?? 'monthly') as TenantFormState['billingCycle'],
    userSeats: m.billing?.user_seats ?? '80',
    invoiceEmail: m.billing?.invoice_email ?? `billing@${tenant.subdomain}.com`,
    billingContact: m.billing?.billing_contact ?? 'Finance Controller',
    taxId: m.billing?.tax_id ?? `VAT-${String(tenant.id).padStart(8, '0')}`,
    adminFullName: m.admin?.full_name ?? 'Hospital Admin',
    adminEmail: m.admin?.email ?? `admin@${tenant.subdomain}.com`,
    tenantId: m.technical?.tenant_id ?? buildTenantId(tenant.name, String(tenant.id).padStart(4, '0')),
  }
}

const normalizeTenantRecord = (tenant: ApiTenant, meta: TenantMeta): TenantRecord => ({
  ...tenant,
  meta,
})

const statusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'active':
      return 'border-emerald-200 bg-emerald-50 text-emerald-700'
    case 'trial':
    case 'provisioning':
      return 'border-cyan-200 bg-cyan-50 text-cyan-700'
    case 'suspended':
    case 'inactive':
      return 'border-rose-200 bg-rose-50 text-rose-700'
    case 'archived':
      return 'border-slate-200 bg-slate-100 text-slate-600'
    default:
      return 'border-amber-200 bg-amber-50 text-amber-700'
  }
}

export function UsersPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const activeTab = getTabValue(searchParams.get('tab'))
  const [form, setForm] = useState<TenantFormState>(initialFormState)
  const [tenantSuffix, setTenantSuffix] = useState(getRandomSuffix)
  const [tenantId, setTenantId] = useState(buildTenantId('', getRandomSuffix()))
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [tenants, setTenants] = useState<TenantRecord[]>([])
  const [selectedTenantId, setSelectedTenantId] = useState<number | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
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
  const [actionMessage, setActionMessage] = useState('')
  const [deleteConfirmation, setDeleteConfirmation] = useState('')
  const [pagination, setPagination] = useState<{
    total: number
    per_page: number
    current_page: number
    last_page: number
  } | null>(null)
  const [isStatusUpdating, setIsStatusUpdating] = useState(false)

  const refreshTenants = useCallback(async (page = 1) => {
    setIsLoading(true)
    setLoadError('')

    try {
      const params: Record<string, string | number> = { page }
      if (search) params.search = search
      if (statusFilter !== 'all') params.status = statusFilter

      const response = await api.get<{
        data: ApiTenant[]
        pagination: { total: number; per_page: number; current_page: number; last_page: number }
      }>('/tenants', { params })
      const nextTenants = (response.data.data ?? []).map((tenant, index) =>
        normalizeTenantRecord(tenant, metaFromApi(tenant, index))
      )

      setTenants(nextTenants)
      setPagination(response.data.pagination ?? null)
      setSelectedTenantId((current) => current ?? nextTenants[0]?.id ?? null)
    } catch (error: any) {
      setLoadError(error.response?.data?.message ?? 'Unable to refresh tenants right now.')
    } finally {
      setIsLoading(false)
    }
  }, [search, statusFilter])

  const searchDebounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    const delay = search.length > 0 ? 400 : 0
    if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current)
    searchDebounceRef.current = setTimeout(() => refreshTenants(1), delay)
    return () => {
      if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current)
    }
  }, [search, statusFilter, refreshTenants])

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

  const visibleTenants = tenants

  const selectedTenant = useMemo(
    () => tenants.find((tenant) => tenant.id === selectedTenantId) ?? null,
    [selectedTenantId, tenants],
  )

  const allUsers = useMemo(() => tenants.map((tenant) => ({
    id: `${tenant.id}-admin`,
    name: tenant.meta.adminFullName,
    email: tenant.meta.adminEmail,
    hospital: tenant.name,
    role: 'Hospital Admin',
    status: tenant.status,
  })), [tenants])

  const setTab = (tab: UsersTab) => {
    if (tab === 'tenants') {
      setSearchParams({ tab: 'tenants' })
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
    setActionMessage('')

    if (form.adminPassword !== form.confirmPassword) {
      setSubmitError('Admin password and confirmation must match.')
      return
    }

    if (form.adminPassword.length < 8) {
      setSubmitError('Admin password must be at least 8 characters.')
      return
    }

    if (!form.subdomain) {
      setSubmitError('Subdomain is required before tenant creation.')
      return
    }

    setIsSubmitting(true)

    try {
      const response = await api.post<{ tenant: ApiTenant }>('/tenants', {
        name: form.hospitalName,
        subdomain: form.subdomain,
        database_name: buildDatabaseName(form.subdomain),
        admin_name: form.adminFullName,
        admin_email: form.adminEmail,
        admin_password: form.adminPassword,
        metadata: {
          hospital: {
            legal_entity_name: form.legalEntityName,
            hospital_type: form.hospitalType,
            registration_number: form.registrationNumber,
            total_branches: form.totalBranches,
            bed_capacity: form.bedCapacity,
            country: form.country,
            city: form.city,
            timezone: form.timezone,
          },
          contact: {
            email: form.contactEmail,
            phone: form.contactPhone,
            address: form.address,
          },
          billing: {
            plan: form.plan,
            cycle: form.billingCycle,
            user_seats: form.userSeats,
            invoice_email: form.invoiceEmail,
            billing_contact: form.billingContact,
            tax_id: form.taxId,
          },
          admin: {
            full_name: form.adminFullName,
            email: form.adminEmail,
          },
          technical: {
            tenant_id: tenantId,
          },
        },
      })

      const createdTenant = response.data.tenant
      const nextRecord = normalizeTenantRecord(createdTenant, buildMetaFromForm(form, tenantId))

      setTenants((current) => [nextRecord, ...current])
      setSelectedTenantId(createdTenant.id)
      setSubmitState({
        tenantId,
        hospitalName: form.hospitalName || 'New Hospital',
        adminEmail: form.adminEmail,
        subdomain: form.subdomain,
        total: grandTotal,
      })
      setActionMessage('Tenant created and initial admin account provisioned successfully.')
      setForm(initialFormState)
      setTenantSuffix(getRandomSuffix())
      setSearchParams({ tab: 'tenant-details' })
    } catch (error: any) {
      setSubmitError(error.response?.data?.message ?? 'Tenant creation failed. Please review the required fields and try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

  const applyStatusChange = async (status: string) => {
    if (!selectedTenant) {
      return
    }

    setIsStatusUpdating(true)
    setActionMessage('')
    try {
      await api.patch(`/tenants/${selectedTenant.id}/status`, { status })
      setTenants((current) =>
        current.map((tenant) => (tenant.id === selectedTenant.id ? { ...tenant, status } : tenant))
      )
      setActionMessage(`${selectedTenant.name} status updated to ${status}.`)
    } catch (error: any) {
      setActionMessage(error.response?.data?.message ?? 'Status update failed. Please try again.')
    } finally {
      setIsStatusUpdating(false)
    }
  }

  const archiveSelectedTenant = async () => {
    if (!selectedTenant) {
      return
    }

    if (deleteConfirmation.trim().toLowerCase() !== selectedTenant.name.trim().toLowerCase()) {
      setActionMessage('Type the exact hospital name to confirm archival.')
      return
    }

    try {
      await api.delete(`/tenants/${selectedTenant.id}`, { data: { mode: 'archive' } })
      setTenants((current) =>
        current.map((tenant) =>
          tenant.id === selectedTenant.id ? { ...tenant, status: 'archived' } : tenant
        )
      )
      setDeleteConfirmation('')
      setActionMessage(`${selectedTenant.name} has been archived successfully.`)
    } catch (error: any) {
      setActionMessage(error.response?.data?.message ?? 'Archive failed. Please try again.')
    }
  }

  const activeCount = tenants.filter((tenant) => tenant.status.toLowerCase() === 'active').length
  const suspendedCount = tenants.filter((tenant) => ['suspended', 'inactive'].includes(tenant.status.toLowerCase())).length
  const draftInvoices = tenants.filter((tenant) => ['trial', 'provisioning'].includes(tenant.status.toLowerCase())).length

  return (
    <div className="space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-emerald-100 via-white to-cyan-100 p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div className="max-w-3xl">
            <p className="inline-flex rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-emerald-700 uppercase">
              Super Admin Tenant Factory
            </p>
            <h1 className="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Tenant management workspace for hospital onboarding, billing control, and admin provisioning.</h1>
            <p className="mt-3 text-slate-600">
              Review live tenants, create new hospital workspaces, inspect details, manage activation state, and control archival from one screen.
            </p>
          </div>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:min-w-[420px]">
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Total Hospitals</p>
              <p className="mt-2 text-3xl font-black text-slate-900">{tenants.length}</p>
              <p className="mt-1 text-xs text-slate-500">{activeCount} active workspaces</p>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Suspended / Inactive</p>
              <p className="mt-2 text-3xl font-black text-slate-900">{suspendedCount}</p>
              <p className="mt-1 text-xs text-slate-500">Needs review and follow-up</p>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs uppercase tracking-wide text-slate-500">Draft Invoices</p>
              <p className="mt-2 text-3xl font-black text-slate-900">{draftInvoices}</p>
              <p className="mt-1 text-xs text-slate-500">Trial and provisioning tenants</p>
            </div>
          </div>
        </div>
      </section>

      {actionMessage && (
        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
          {actionMessage}
        </div>
      )}

      {loadError && (
        <div className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
          {loadError}
        </div>
      )}

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
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.55fr_0.85fr]">
          <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h2 className="text-2xl font-semibold text-slate-900">Tenant List</h2>
                <p className="mt-1 text-sm text-slate-500">Live hospital workspaces with provisioning, billing, and activation status.</p>
              </div>
              <div className="flex flex-col gap-3 sm:flex-row">
                <div className="relative">
                  <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                  <input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search hospital, subdomain, or tenant ID"
                    className="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-10 text-sm text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100 sm:w-72"
                  />
                  {isLoading && search.length > 0 && (
                    <span className="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 animate-spin rounded-full border-2 border-slate-300 border-t-emerald-500" />
                  )}
                </div>
                <select
                  value={statusFilter}
                  onChange={(event) => setStatusFilter(event.target.value)}
                  className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                >
                  <option value="all">All statuses</option>
                  <option value="active">Active</option>
                  <option value="provisioning">Provisioning</option>
                  <option value="trial">Trial</option>
                  <option value="suspended">Suspended</option>
                  <option value="inactive">Inactive</option>
                  <option value="archived">Archived</option>
                </select>
                <button
                  onClick={() => refreshTenants(1)}
                  className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                >
                  Refresh
                </button>
                <button
                  onClick={() => setTab('create-tenant')}
                  className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500"
                >
                  Create Tenant
                </button>
              </div>
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
                    <th className="px-4 py-3 font-medium">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200 bg-white">
                  {isLoading && (
                    <tr>
                      <td colSpan={6} className="px-4 py-10 text-center text-slate-500">Loading tenants...</td>
                    </tr>
                  )}
                  {!isLoading && visibleTenants.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-4 py-10 text-center text-slate-500">No tenant matched the current filters.</td>
                    </tr>
                  )}
                  {!isLoading && visibleTenants.map((tenant) => (
                    <tr key={tenant.id} className={selectedTenantId === tenant.id ? 'bg-emerald-50/50' : 'hover:bg-slate-50'}>
                      <td className="px-4 py-3">
                        <div>
                          <p className="font-medium text-slate-900">{tenant.name}</p>
                          <p className="text-xs text-slate-500">{tenant.subdomain}.medcore.local</p>
                        </div>
                      </td>
                      <td className="px-4 py-3 font-mono text-xs text-slate-600">{tenant.meta.tenantId}</td>
                      <td className="px-4 py-3 text-slate-600">{planConfig[tenant.meta.plan].label}</td>
                      <td className="px-4 py-3 text-slate-600">{tenant.meta.city}</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full border px-2.5 py-1 text-xs font-semibold capitalize ${statusTone(tenant.status)}`}>
                          {tenant.status}
                        </span>
                      </td>
                      <td className="px-4 py-3">
                        <button
                          onClick={() => {
                            setSelectedTenantId(tenant.id)
                            setTab('tenant-details')
                          }}
                          className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                        >
                          View
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {pagination && pagination.last_page > 1 && (
              <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                <span>{pagination.total} hospitals — page {pagination.current_page} of {pagination.last_page}</span>
                <div className="flex gap-2">
                  <button
                    disabled={pagination.current_page <= 1 || isLoading}
                    onClick={() => refreshTenants(pagination.current_page - 1)}
                    className="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 font-medium transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                  >
                    Previous
                  </button>
                  <button
                    disabled={pagination.current_page >= pagination.last_page || isLoading}
                    onClick={() => refreshTenants(pagination.current_page + 1)}
                    className="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 font-medium transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                  >
                    Next
                  </button>
                </div>
              </div>
            )}
          </section>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Portfolio Snapshot</h3>
              <div className="mt-4 grid grid-cols-1 gap-3">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">ARR Pipeline</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">{formatCurrency(tenants.length * 6490)}</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Invoices Due This Week</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">{draftInvoices + 5}</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <p className="text-xs uppercase tracking-wide text-slate-500">Pending Activations</p>
                  <p className="mt-2 text-2xl font-bold text-slate-900">{tenants.filter((tenant) => tenant.status === 'provisioning').length}</p>
                </div>
              </div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Selected Tenant</h3>
              {selectedTenant ? (
                <div className="mt-3 space-y-3">
                  <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p className="font-semibold text-slate-900">{selectedTenant.name}</p>
                    <p className="mt-1 text-sm text-slate-500">{selectedTenant.meta.tenantId}</p>
                  </div>
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                      <p className="text-slate-500">Plan</p>
                      <p className="mt-1 font-medium text-slate-900">{planConfig[selectedTenant.meta.plan].label}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                      <p className="text-slate-500">Seats</p>
                      <p className="mt-1 font-medium text-slate-900">{selectedTenant.meta.userSeats}</p>
                    </div>
                  </div>
                  <button
                    onClick={() => setTab('tenant-status')}
                    className="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                  >
                    Open Tenant Status Controls
                  </button>
                </div>
              ) : (
                <p className="mt-3 text-sm text-slate-500">Select a tenant from the list to inspect details and management actions.</p>
              )}
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
                  <input value={form.hospitalName} onChange={(event) => updateField('hospitalName', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="MedCore Central Hospital" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Legal Entity Name</span>
                  <input value={form.legalEntityName} onChange={(event) => updateField('legalEntityName', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="MedCore Health Services Ltd." required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Hospital Type</span>
                  <select value={form.hospitalType} onChange={(event) => updateField('hospitalType', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100">
                    <option>General Hospital</option>
                    <option>Specialized Hospital</option>
                    <option>Clinic Network</option>
                    <option>Diagnostic Center</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Registration Number</span>
                  <input value={form.registrationNumber} onChange={(event) => updateField('registrationNumber', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="REG-2026-00192" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Total Branches</span>
                  <input type="number" min="1" value={form.totalBranches} onChange={(event) => updateField('totalBranches', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Bed Capacity</span>
                  <input type="number" min="0" value={form.bedCapacity} onChange={(event) => updateField('bedCapacity', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Country</span>
                  <input value={form.country} onChange={(event) => updateField('country', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">City</span>
                  <input value={form.city} onChange={(event) => updateField('city', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Contact Email</span>
                  <input type="email" value={form.contactEmail} onChange={(event) => updateField('contactEmail', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="ops@hospital.com" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Contact Phone</span>
                  <input value={form.contactPhone} onChange={(event) => updateField('contactPhone', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="+880 1712 345 678" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Timezone</span>
                  <select value={form.timezone} onChange={(event) => updateField('timezone', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100">
                    <option>Asia/Dhaka</option>
                    <option>Asia/Kolkata</option>
                    <option>UTC</option>
                  </select>
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Hospital Address</span>
                  <textarea value={form.address} onChange={(event) => updateField('address', event.target.value)} rows={3} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="Full hospital address" required />
                </label>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div>
                <p className="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold tracking-wide text-amber-700">Billing Check</p>
                <h2 className="mt-3 text-2xl font-semibold text-slate-900">Plan, billing, and invoice setup</h2>
                <p className="mt-2 text-sm text-slate-600">Check billing readiness before tenant creation. The invoice preview updates live as you select the plan and user count.</p>
              </div>

              <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Subscription Plan</span>
                  <select value={form.plan} onChange={(event) => updateField('plan', event.target.value as TenantFormState['plan'])} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100">
                    <option value="starter">Starter Care</option>
                    <option value="growth">Growth Network</option>
                    <option value="enterprise">Enterprise Command</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Billing Cycle</span>
                  <select value={form.billingCycle} onChange={(event) => updateField('billingCycle', event.target.value as TenantFormState['billingCycle'])} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100">
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                  </select>
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Licensed User Seats</span>
                  <input type="number" min="1" value={form.userSeats} onChange={(event) => updateField('userSeats', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Tenant Subdomain</span>
                  <input value={form.subdomain} onChange={(event) => updateField('subdomain', normalizeSlug(event.target.value))} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100" placeholder="medcore-central" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Billing Contact</span>
                  <input value={form.billingContact} onChange={(event) => updateField('billingContact', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100" placeholder="Finance Controller" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Invoice Email</span>
                  <input type="email" value={form.invoiceEmail} onChange={(event) => updateField('invoiceEmail', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100" placeholder="billing@hospital.com" required />
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Tax / VAT ID</span>
                  <input value={form.taxId} onChange={(event) => updateField('taxId', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-teal-400/50 focus:ring-2 focus:ring-teal-100" placeholder="VAT-99887766" />
                </label>
              </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div>
                <p className="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700">User Email and Password</p>
                <h2 className="mt-3 text-2xl font-semibold text-slate-900">Create the first tenant admin account</h2>
                <p className="mt-2 text-sm text-slate-600">This admin gets the initial login after the tenant is created. Capture the email and password now so provisioning stays complete.</p>
              </div>

              <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Admin Full Name</span>
                  <input value={form.adminFullName} onChange={(event) => updateField('adminFullName', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="Amina Rahman" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Admin Email</span>
                  <input type="email" value={form.adminEmail} onChange={(event) => updateField('adminEmail', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="admin@hospital.com" required />
                </label>
                <label className="space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Temporary Password</span>
                  <input type="password" value={form.adminPassword} onChange={(event) => updateField('adminPassword', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="Minimum 8 characters" required />
                </label>
                <label className="space-y-1.5 md:col-span-2">
                  <span className="text-sm font-medium text-slate-700">Confirm Password</span>
                  <input type="password" value={form.confirmPassword} onChange={(event) => updateField('confirmPassword', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100" placeholder="Retype password" required />
                </label>
              </div>

              {submitError && (
                <div className="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{submitError}</div>
              )}

              <div className="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-sm text-slate-500">Submit the create tenant request once hospital information, billing, invoice, and admin access are complete.</div>
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
                  <button type="submit" disabled={isSubmitting} className="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60">
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
                <p className="mt-3 text-xs text-slate-500">Database Name</p>
                <p className="mt-1 text-sm text-slate-700">{buildDatabaseName(form.subdomain || 'hospital-name')}</p>
              </div>
              <div className="mt-4 flex gap-3">
                <button type="button" onClick={copyTenantId} className="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100">
                  <span className="inline-flex items-center gap-2"><Copy className="h-4 w-4" />{copiedTenantId ? 'Copied' : 'Copy ID'}</span>
                </button>
                <button type="button" onClick={regenerateTenantId} className="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-500">
                  <span className="inline-flex items-center gap-2"><RefreshCcw className="h-4 w-4" />Regenerate</span>
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
                    <span className={item.complete ? 'text-emerald-700' : 'text-slate-400'}><CheckCircle2 className="h-4 w-4" /></span>
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
                <div className="flex items-center justify-between text-sm text-slate-700"><span>{selectedPlan.label}</span><span>{formatCurrency(baseAmount)}</span></div>
                <div className="flex items-center justify-between text-sm text-slate-700"><span>Extra seats ({extraSeats})</span><span>{formatCurrency(extraSeatAmount)}</span></div>
                <div className="flex items-center justify-between text-sm text-slate-700"><span>Implementation setup</span><span>{formatCurrency(setupAmount)}</span></div>
                <div className="flex items-center justify-between text-sm text-slate-700"><span>Tax (5%)</span><span>{formatCurrency(taxAmount)}</span></div>
                <div className="border-t border-slate-200 pt-3">
                  <div className="flex items-center justify-between text-base font-semibold text-slate-900"><span>Total</span><span>{formatCurrency(grandTotal)}</span></div>
                </div>
              </div>
              <p className="mt-3 text-xs text-slate-500">{selectedPlan.invoiceNote}</p>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <div className="flex items-center gap-2"><Mail className="h-4 w-4 text-emerald-600" /><h3 className="text-lg font-semibold text-slate-900">Provisioning summary</h3></div>
              <div className="mt-4 space-y-3 text-sm">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="text-xs uppercase tracking-wide text-slate-500">Billing Destination</p><p className="mt-1 font-medium text-slate-900">{form.invoiceEmail || 'billing@hospital.com'}</p></div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="text-xs uppercase tracking-wide text-slate-500">Admin Login</p><p className="mt-1 font-medium text-slate-900">{form.adminEmail || 'admin@hospital.com'}</p></div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="text-xs uppercase tracking-wide text-slate-500">Password Policy</p><p className="mt-1 inline-flex items-center gap-2 font-medium text-slate-900"><LockKeyhole className="h-4 w-4 text-slate-500" />Minimum 8 characters with secure handoff</p></div>
              </div>
            </section>

            {submitState && (
              <section className="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
                <div className="flex items-center gap-2"><BadgeCheck className="h-5 w-5 text-emerald-700" /><h3 className="text-lg font-semibold text-emerald-900">Tenant request completed</h3></div>
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

      {activeTab === 'tenant-details' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.25fr_0.95fr]">
          <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <div className="flex items-center justify-between gap-4">
              <div>
                <h2 className="text-2xl font-semibold text-slate-900">Tenant details and edit view</h2>
                <p className="mt-1 text-sm text-slate-500">Operational, billing, and technical details for the selected hospital workspace.</p>
              </div>
              <button onClick={() => setTab('tenants')} className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100">Back to Tenant List</button>
            </div>

            {selectedTenant ? (
              <div className="mt-6 space-y-6">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                  <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                    <p className="text-xs uppercase tracking-wide text-slate-500">Hospital</p>
                    <p className="mt-2 text-2xl font-bold text-slate-900">{selectedTenant.name}</p>
                    <p className="mt-1 text-sm text-slate-500">{selectedTenant.meta.legalEntityName}</p>
                  </div>
                  <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p className="text-xs uppercase tracking-wide text-slate-500">Current Status</p>
                    <span className={`mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-semibold capitalize ${statusTone(selectedTenant.status)}`}>{selectedTenant.status}</span>
                    <p className="mt-3 text-xs text-slate-500">Created {selectedTenant.created_at ? new Date(selectedTenant.created_at).toLocaleDateString() : 'recently'}</p>
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div className="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 className="text-lg font-semibold text-slate-900">Hospital Profile</h3>
                    <dl className="mt-4 space-y-3 text-sm">
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Tenant ID</dt><dd className="font-mono text-slate-900">{selectedTenant.meta.tenantId}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Type</dt><dd className="text-slate-900">{selectedTenant.meta.hospitalType}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Registration</dt><dd className="text-slate-900">{selectedTenant.meta.registrationNumber}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Branches</dt><dd className="text-slate-900">{selectedTenant.meta.totalBranches}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Bed Capacity</dt><dd className="text-slate-900">{selectedTenant.meta.bedCapacity}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">City</dt><dd className="text-slate-900">{selectedTenant.meta.city}</dd></div>
                    </dl>
                  </div>
                  <div className="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 className="text-lg font-semibold text-slate-900">Contact and Workspace</h3>
                    <dl className="mt-4 space-y-3 text-sm">
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Ops Email</dt><dd className="text-slate-900">{selectedTenant.meta.contactEmail}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Phone</dt><dd className="text-slate-900">{selectedTenant.meta.contactPhone}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Subdomain</dt><dd className="text-slate-900">{selectedTenant.subdomain}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Database</dt><dd className="font-mono text-slate-900">{selectedTenant.database_name}</dd></div>
                      <div className="flex justify-between gap-4"><dt className="text-slate-500">Timezone</dt><dd className="text-slate-900">{selectedTenant.meta.timezone}</dd></div>
                    </dl>
                    <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">{selectedTenant.meta.address}</div>
                  </div>
                </div>
              </div>
            ) : (
              <div className="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">Select a tenant from Tenant List to inspect details.</div>
            )}
          </section>

          <aside className="space-y-4">
            {selectedTenant && (
              <>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
                  <h3 className="text-lg font-semibold text-slate-900">Billing Summary</h3>
                  <div className="mt-4 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm">
                    <div className="flex justify-between"><span className="text-slate-500">Plan</span><span className="text-slate-900">{planConfig[selectedTenant.meta.plan].label}</span></div>
                    <div className="flex justify-between"><span className="text-slate-500">Cycle</span><span className="text-slate-900 capitalize">{selectedTenant.meta.billingCycle}</span></div>
                    <div className="flex justify-between"><span className="text-slate-500">Seats</span><span className="text-slate-900">{selectedTenant.meta.userSeats}</span></div>
                    <div className="flex justify-between"><span className="text-slate-500">Invoice Email</span><span className="text-slate-900">{selectedTenant.meta.invoiceEmail}</span></div>
                    <div className="flex justify-between"><span className="text-slate-500">Billing Contact</span><span className="text-slate-900">{selectedTenant.meta.billingContact}</span></div>
                  </div>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
                  <h3 className="text-lg font-semibold text-slate-900">Initial Admin</h3>
                  <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm">
                    <p className="font-medium text-slate-900">{selectedTenant.meta.adminFullName}</p>
                    <p className="mt-1 text-slate-600">{selectedTenant.meta.adminEmail}</p>
                  </div>
                  <button onClick={() => setTab('all-users')} className="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100">View Cross-Tenant Users</button>
                </div>
              </>
            )}
          </aside>
        </div>
      )}

      {activeTab === 'tenant-status' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
          <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <h2 className="text-2xl font-semibold text-slate-900">Suspend or activate tenant</h2>
            <p className="mt-1 text-sm text-slate-500">Select a tenant from the list, then use the controls below to activate, suspend, or move it to trial.</p>

            {selectedTenant ? (
              <div className="mt-6 space-y-5">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                  <p className="text-sm text-slate-500">Selected Tenant</p>
                  <p className="mt-1 text-xl font-semibold text-slate-900">{selectedTenant.name}</p>
                  <span className={`mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-semibold capitalize ${statusTone(selectedTenant.status)}`}>{selectedTenant.status}</span>
                </div>
                <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                  <button onClick={() => applyStatusChange('active')} disabled={isStatusUpdating} className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-left transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60">
                    <p className="font-semibold text-emerald-900">Activate</p>
                    <p className="mt-1 text-sm text-emerald-700">Re-enable billing and user sign-in.</p>
                  </button>
                  <button onClick={() => applyStatusChange('suspended')} disabled={isStatusUpdating} className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-left transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60">
                    <p className="font-semibold text-rose-900">Suspend</p>
                    <p className="mt-1 text-sm text-rose-700">Block workspace access temporarily.</p>
                  </button>
                  <button onClick={() => applyStatusChange('trial')} disabled={isStatusUpdating} className="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 text-left transition hover:bg-cyan-100 disabled:cursor-not-allowed disabled:opacity-60">
                    <p className="font-semibold text-cyan-900">Move to Trial</p>
                    <p className="mt-1 text-sm text-cyan-700">Return to onboarding and sales review.</p>
                  </button>
                </div>
              </div>
            ) : (
              <div className="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">Select a tenant from Tenant List first.</div>
            )}
          </section>

          <aside className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <h3 className="text-lg font-semibold text-slate-900">Status guidance</h3>
            <div className="mt-4 space-y-3 text-sm">
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="font-medium text-slate-900">Active</p><p className="mt-1 text-slate-600">Normal production access with billing and core modules enabled.</p></div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="font-medium text-slate-900">Suspended</p><p className="mt-1 text-slate-600">Used for overdue invoices, compliance review, or contract hold.</p></div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="font-medium text-slate-900">Trial / Provisioning</p><p className="mt-1 text-slate-600">Sales-to-implementation handoff before full activation.</p></div>
            </div>
          </aside>
        </div>
      )}

      {activeTab === 'tenant-delete' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.1fr_0.9fr]">
          <section className="rounded-3xl border border-rose-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <div className="flex items-center gap-2 text-rose-700">
              <AlertTriangle className="h-5 w-5" />
              <h2 className="text-2xl font-semibold text-slate-900">Tenant archive and delete safeguards</h2>
            </div>
            <p className="mt-2 text-sm text-slate-600">Archive sets the tenant status to archived and can be reversed. Permanent delete removes all records and requires the tenant to have no active users.</p>

            {selectedTenant ? (
              <div className="mt-6 space-y-5">
                <div className="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                  <p className="text-sm text-rose-700">Selected tenant</p>
                  <p className="mt-1 text-xl font-semibold text-rose-900">{selectedTenant.name}</p>
                  <p className="mt-1 text-sm text-rose-700">Type the exact hospital name below to archive this workspace in the frontend state.</p>
                </div>
                <label className="block space-y-1.5">
                  <span className="text-sm font-medium text-slate-700">Confirm hospital name</span>
                  <input
                    value={deleteConfirmation}
                    onChange={(event) => setDeleteConfirmation(event.target.value)}
                    className="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                    placeholder={selectedTenant.name}
                  />
                </label>
                <button onClick={archiveSelectedTenant} className="rounded-xl bg-rose-600 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-rose-500">Archive Tenant</button>
              </div>
            ) : (
              <div className="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">Select a tenant from Tenant List first.</div>
            )}
          </section>

          <aside className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <h3 className="text-lg font-semibold text-slate-900">Recommended deletion checklist</h3>
            <div className="mt-4 space-y-3 text-sm">
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">Export invoices and audit logs.</div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">Disable user sign-in and integrations.</div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">Snapshot tenant database and attachments.</div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">Get dual approval before permanent delete.</div>
            </div>
          </aside>
        </div>
      )}

      {activeTab === 'all-users' && (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr_0.9fr]">
          <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
            <h2 className="text-2xl font-semibold text-slate-900">All users across tenants</h2>
            <p className="mt-1 text-sm text-slate-500">Cross-tenant admin directory derived from the tenant management records.</p>
            <div className="mt-6 overflow-hidden rounded-2xl border border-slate-200">
              <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead className="bg-slate-50 text-slate-500">
                  <tr>
                    <th className="px-4 py-3 font-medium">User</th>
                    <th className="px-4 py-3 font-medium">Hospital</th>
                    <th className="px-4 py-3 font-medium">Role</th>
                    <th className="px-4 py-3 font-medium">Tenant Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200 bg-white">
                  {allUsers.map((user) => (
                    <tr key={user.id} className="hover:bg-slate-50">
                      <td className="px-4 py-3">
                        <p className="font-medium text-slate-900">{user.name}</p>
                        <p className="text-xs text-slate-500">{user.email}</p>
                      </td>
                      <td className="px-4 py-3 text-slate-600">{user.hospital}</td>
                      <td className="px-4 py-3 text-slate-600">{user.role}</td>
                      <td className="px-4 py-3"><span className={`rounded-full border px-2.5 py-1 text-xs font-semibold capitalize ${statusTone(user.status)}`}>{user.status}</span></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Access summary</h3>
              <div className="mt-4 grid grid-cols-1 gap-3">
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="text-xs uppercase tracking-wide text-slate-500">Admins Provisioned</p><p className="mt-2 text-2xl font-bold text-slate-900">{allUsers.length}</p></div>
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p className="text-xs uppercase tracking-wide text-slate-500">Active Logins</p><p className="mt-2 text-2xl font-bold text-slate-900">{activeCount}</p></div>
              </div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
              <h3 className="text-lg font-semibold text-slate-900">Next step</h3>
              <p className="mt-2 text-sm text-slate-600">If you want deeper user management, the next frontend step is a dedicated table for invites, resets, and role assignments using real backend data.</p>
            </div>
          </aside>
        </div>
      )}

      {activeTab === 'assign-roles' && (
        <div className="rounded-3xl border border-slate-200 bg-white p-8 shadow-[0_20px_45px_-30px_rgba(15,23,42,0.25)]">
          <h2 className="text-2xl font-semibold text-slate-900">Assign roles across tenants</h2>
          <p className="mt-2 max-w-2xl text-slate-600">This shell is ready for the next step: wiring cross-tenant role assignment once a backend endpoint exists for global user-role mapping.</p>
        </div>
      )}
    </div>
  )
}