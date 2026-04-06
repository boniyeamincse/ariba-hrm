import { AlertTriangle, Activity, Boxes, Building2, Shield, Webhook } from 'lucide-react'
import { Link } from 'react-router-dom'
import { useEffect, useMemo, useState } from 'react'
import { api } from '../../../../lib/api'

type DashboardWidget = {
	key: string
	label: string
	value: string | number
}

type SuperAdminDashboardProps = {
	widgets: DashboardWidget[]
	todayLabel: string
	formatCompact: (value: number) => string
	getNumericWidgetValue: (widget?: DashboardWidget) => number
}

type SuperAdminPanelResponse = {
	summary: {
		total_hospitals: number
		active_hospitals: number
		suspended_hospitals: number
		total_users: number
		revenue_today: number
		payments_today: number
	}
	system_control: {
		full_access: boolean
		global_settings_configurable: boolean
		enabled_modules: number
		maintenance_mode: boolean
	}
	tenant_management: {
		total_hospitals: number
		active_hospitals: number
		suspended_hospitals: number
		multi_branch_support: boolean
	}
	user_management: {
		total_users: number
		roles_count: number
		global_user_visibility: boolean
		force_password_reset: boolean
	}
	subscription_billing: {
		payments_today: number
		invoices_today: number
		outstanding_invoices: number
		renewal_monitoring: boolean
	}
	analytics_reports: {
		total_hospitals: number
		total_users: number
		revenue_today: number
		api_calls_today: number
	}
	security_compliance: {
		activity_logs_today: number
		suspicious_activity: number
		rbac_roles: number
		security_policy_enforcement: boolean
	}
	integration_control: {
		api_calls_today: number
		webhook_failures: number
		third_party_integrations: boolean
	}
	ai_advanced: {
		ai_assistant_enabled: boolean
		ai_usage_monitoring: boolean
		automation_rules: boolean
	}
	upcoming: Array<{
		title: string
		detail: string
		route: string
		priority: string
	}>
}

const capabilityTiles = [
	{ title: 'Global SaaS Operations', detail: 'Control every tenant, module, and platform-level workflow.' },
	{ title: 'Tenant Lifecycle Management', detail: 'Onboard, suspend, and govern hospitals across regions.' },
	{ title: 'Subscription Revenue Oversight', detail: 'Track plans, payments, and renewal performance centrally.' },
	{ title: 'Security and Compliance Governance', detail: 'Monitor alerts, enforce RBAC, and audit system posture.' },
	{ title: 'Integrations and Platform Reliability', detail: 'Manage API usage, webhook health, and uptime signals.' },
	{ title: 'Module Controls', detail: 'Enable or disable product modules for tenant-specific rollout.' },
]

export function SuperAdminDashboard({
	widgets,
	todayLabel,
	formatCompact,
	getNumericWidgetValue,
}: SuperAdminDashboardProps) {
	const [panel, setPanel] = useState<SuperAdminPanelResponse | null>(null)

	useEffect(() => {
		const loadPanel = async () => {
			try {
				const response = await api.get<SuperAdminPanelResponse>('/dashboard/super-admin/panel')
				setPanel(response.data)
			} catch {
				setPanel(null)
			}
		}

		loadPanel()
	}, [])

	const signalValues = [
		{ label: 'Security Alerts', icon: Shield, value: getNumericWidgetValue(widgets.find((w) => w.key === 'security_alerts')) },
		{ label: 'Webhook Failures', icon: Webhook, value: getNumericWidgetValue(widgets.find((w) => w.key === 'webhook_failures')) },
		{ label: 'API Calls Today', icon: Activity, value: getNumericWidgetValue(widgets.find((w) => w.key === 'api_calls_today')) },
		{ label: 'Active Tenants', icon: Building2, value: getNumericWidgetValue(widgets.find((w) => w.key === 'active_tenants')) },
		{ label: 'Enabled Modules', icon: Boxes, value: getNumericWidgetValue(widgets.find((w) => w.key === 'enabled_modules')) },
	]

	const hasCriticalSignal = signalValues[0].value > 0 || signalValues[1].value > 0

	const commandMatrix = useMemo(() => {
		if (!panel) {
			return []
		}

		return [
			{
				title: 'System Control',
				detail: `Full access active • ${panel.system_control.enabled_modules} modules enabled`,
			},
			{
				title: 'Tenant Management',
				detail: `${panel.tenant_management.total_hospitals} hospitals • ${panel.tenant_management.suspended_hospitals} suspended`,
			},
			{
				title: 'Global User Management',
				detail: `${panel.user_management.total_users} users • ${panel.user_management.roles_count} roles`,
			},
			{
				title: 'Subscription & Billing',
				detail: `${panel.subscription_billing.payments_today} payments • ${panel.subscription_billing.outstanding_invoices} outstanding invoices`,
			},
			{
				title: 'Security & Compliance',
				detail: `${panel.security_compliance.activity_logs_today} logs today • ${panel.security_compliance.suspicious_activity} suspicious events`,
			},
			{
				title: 'Integrations & AI',
				detail: `${panel.integration_control.api_calls_today} API calls • AI ${panel.ai_advanced.ai_assistant_enabled ? 'enabled' : 'disabled'}`,
			},
		]
	}, [panel])

	return (
		<section className="grid grid-cols-1 gap-4 xl:grid-cols-3">
			<article className="rounded-2xl border border-indigo-400/30 bg-[linear-gradient(140deg,rgba(30,27,75,0.55),rgba(37,99,235,0.2),rgba(15,23,42,0.92))] p-5 xl:col-span-2">
				<div className="mb-4 flex items-center justify-between">
					<div>
						<h2 className="text-xl font-semibold text-white">Super Admin Control Center</h2>
						<p className="mt-1 text-xs text-indigo-100">Global Scope • {todayLabel}</p>
					</div>
					<div className="flex items-center gap-2">
						<Link to="/dashboard/users" className="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
							Tenant Management
						</Link>
						<Link to="/dashboard/settings" className="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-white/20">
							Global Settings
						</Link>
					</div>
				</div>

				{hasCriticalSignal && (
					<div className="mb-4 rounded-xl border border-rose-500/40 bg-rose-500/15 px-3 py-2 text-sm text-rose-100">
						<span className="inline-flex items-center gap-2 font-semibold">
							<AlertTriangle className="h-4 w-4" />
							Critical platform signals detected. Review security and webhook health immediately.
						</span>
					</div>
				)}

				<div className="grid grid-cols-1 gap-3 md:grid-cols-2">
					{(commandMatrix.length > 0 ? commandMatrix : capabilityTiles).map((tile) => (
						<div key={tile.title} className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
							<p className="text-sm font-semibold text-white">{tile.title}</p>
							<p className="mt-1 text-xs text-slate-300">{tile.detail}</p>
						</div>
					))}
				</div>
			</article>

			<article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
				<div className="mb-4 flex items-center justify-between">
					<h2 className="text-lg font-semibold text-white">System Health Signals</h2>
					<span className="rounded-lg border border-white/10 px-2 py-1 text-[11px] text-slate-300">Live</span>
				</div>

				<div className="space-y-3">
					{signalValues.map((signal) => {
						const SignalIcon = signal.icon
						const isDanger = (signal.label === 'Security Alerts' || signal.label === 'Webhook Failures') && signal.value > 0

						return (
							<div key={signal.label} className="flex items-center justify-between rounded-lg border border-white/10 bg-white/[0.02] px-3 py-2">
								<span className="inline-flex items-center gap-2 text-sm text-slate-300">
									<SignalIcon className={`h-4 w-4 ${isDanger ? 'text-rose-300' : 'text-indigo-300'}`} />
									{signal.label}
								</span>
								<span className={`text-sm font-semibold ${isDanger ? 'text-rose-300' : 'text-white'}`}>
									{formatCompact(signal.value)}
								</span>
							</div>
						)
					})}
				</div>
			</article>

			<article className="rounded-2xl border border-white/10 bg-white/[0.03] p-5 xl:col-span-3">
				<div className="mb-4 flex items-center justify-between">
					<h2 className="text-lg font-semibold text-white">Upcoming Actions</h2>
					<span className="rounded-lg border border-white/10 px-2 py-1 text-[11px] text-slate-300">Priority Queue</span>
				</div>

				<div className="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
					{(panel?.upcoming ?? []).map((item, idx) => (
						<Link
							key={`${item.title}-${idx}`}
							to={item.route}
							className="rounded-xl border border-white/10 bg-white/[0.02] p-3 transition hover:bg-white/[0.06]"
						>
							<p className="text-sm font-semibold text-white">{item.title}</p>
							<p className="mt-1 text-xs text-slate-300">{item.detail}</p>
							<p className={`mt-2 text-[11px] font-semibold uppercase tracking-wide ${item.priority === 'high' ? 'text-rose-300' : 'text-amber-300'}`}>
								{item.priority} priority
							</p>
						</Link>
					))}

					{(panel?.upcoming?.length ?? 0) === 0 && (
						<div className="rounded-xl border border-dashed border-white/20 px-4 py-5 text-sm text-slate-400">
							No urgent upcoming actions. System is operating within expected thresholds.
						</div>
					)}
				</div>
			</article>
		</section>
	)
}
