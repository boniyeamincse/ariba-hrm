<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Consultation;
use App\Models\InsuranceClaim;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Menu;
use App\Models\AuditLog;
use App\Models\InvestigationOrder;
use App\Models\Invoice;
use App\Models\OpdQueue;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleDashboardController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $user = $request->user();
        $role = $this->normalizeRole($user?->primaryRole() ?? 'tenant-admin');
        $roleProfile = $this->roleProfiles()[$role] ?? $this->defaultRoleProfile($role);
        $metrics = $this->collectMetrics($tenantId, $user?->id);

        return response()->json([
            'role' => $role,
            'role_label' => (string) Arr::get($roleProfile, 'label', Str::headline($role)),
            'tenant_id' => $tenantId,
            'menus' => $this->menusForUser($user),
            'widgets' => $this->widgetsForRole($role, $metrics),
            'quick_actions' => Arr::get($roleProfile, 'quick_actions', []),
            'focus_areas' => Arr::get($roleProfile, 'focus_areas', []),
            'top_nav' => [
                'can_switch_role' => in_array($role, ['super-admin', 'tenant-admin', 'hospital-manager'], true),
                'notifications_count' => Task::query()
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->where('status', 'todo')
                    ->count(),
            ],
        ]);
    }

    public function widgets(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $user = $request->user();
        $role = $this->normalizeRole($user?->primaryRole() ?? 'tenant-admin');
        $metrics = $this->collectMetrics($tenantId, $user?->id);

        return response()->json([
            'role' => $role,
            'items' => $this->widgetsForRole($role, $metrics),
        ]);
    }

    public function superAdminPanel(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $this->normalizeRole($user?->primaryRole() ?? 'tenant-admin');

        if ($role !== 'super-admin') {
            return response()->json([
                'message' => 'Forbidden. Super Admin access required.',
            ], 403);
        }

        $today = today();
        $activeTenants = Tenant::query()->where('status', 'active')->count();
        $suspendedTenants = Tenant::query()->whereIn('status', ['suspended', 'inactive'])->count();
        $totalTenants = Tenant::query()->count();
        $totalUsers = User::query()->count();
        $paymentsToday = Payment::query()->whereDate('paid_at', $today)->count();
        $revenueToday = (float) Payment::query()->whereDate('paid_at', $today)->sum('amount');
        $invoicesToday = Invoice::query()->whereDate('issued_at', $today)->count();
        $outstandingInvoices = Invoice::query()->whereColumn('amount_paid', '<', 'total_due')->count();
        $apiCallsToday = AuditLog::query()->whereDate('created_at', $today)->where('path', 'like', '%api/%')->count();
        $webhookFailures = AuditLog::query()->whereDate('created_at', $today)->where('path', 'like', '%webhook%')->where('status_code', '>=', 400)->count();
        $securityAlerts = AuditLog::query()->whereDate('created_at', $today)->where('status_code', '>=', 400)->count();
        $enabledModules = Menu::query()->whereNull('parent_id')->where('is_active', true)->count();

        $moduleStatus = Menu::query()
            ->whereNull('parent_id')
            ->orderBy('label')
            ->get(['label', 'is_active'])
            ->map(fn (Menu $menu) => [
                'module' => $menu->label,
                'enabled' => (bool) $menu->is_active,
            ])
            ->values()
            ->all();

        $upcoming = collect();

        Invoice::query()
            ->whereColumn('amount_paid', '<', 'total_due')
            ->latest('issued_at')
            ->take(3)
            ->get(['invoice_no', 'tenant_id', 'total_due', 'amount_paid', 'issued_at'])
            ->each(function (Invoice $invoice) use ($upcoming): void {
                $balance = max((float) $invoice->total_due - (float) $invoice->amount_paid, 0);

                $upcoming->push([
                    'title' => 'Invoice Follow-up Required',
                    'detail' => sprintf('%s has outstanding balance of %.2f.', $invoice->invoice_no ?? 'Invoice', $balance),
                    'route' => '/dashboard/billing',
                    'priority' => 'medium',
                ]);
            });

        Task::query()
            ->where('status', 'todo')
            ->whereIn('priority', ['urgent', 'high'])
            ->latest('created_at')
            ->take(3)
            ->get(['title', 'priority'])
            ->each(function (Task $task) use ($upcoming): void {
                $upcoming->push([
                    'title' => 'Operational Task Pending',
                    'detail' => sprintf('%s (%s priority).', $task->title, Str::headline((string) $task->priority)),
                    'route' => '/dashboard/tasks',
                    'priority' => $task->priority === 'urgent' ? 'high' : 'medium',
                ]);
            });

        if ($suspendedTenants > 0) {
            $upcoming->push([
                'title' => 'Suspended Tenants Need Review',
                'detail' => sprintf('%d tenant(s) are suspended or inactive.', $suspendedTenants),
                'route' => '/dashboard/users',
                'priority' => 'high',
            ]);
        }

        return response()->json([
            'summary' => [
                'total_hospitals' => $totalTenants,
                'active_hospitals' => $activeTenants,
                'suspended_hospitals' => $suspendedTenants,
                'total_users' => $totalUsers,
                'revenue_today' => round($revenueToday, 2),
                'payments_today' => $paymentsToday,
            ],
            'system_control' => [
                'full_access' => true,
                'global_settings_configurable' => true,
                'enabled_modules' => $enabledModules,
                'maintenance_mode' => app()->isDownForMaintenance(),
            ],
            'tenant_management' => [
                'total_hospitals' => $totalTenants,
                'active_hospitals' => $activeTenants,
                'suspended_hospitals' => $suspendedTenants,
                'multi_branch_support' => true,
            ],
            'user_management' => [
                'total_users' => $totalUsers,
                'roles_count' => Role::query()->count(),
                'global_user_visibility' => true,
                'force_password_reset' => true,
            ],
            'subscription_billing' => [
                'plans_supported' => true,
                'payments_today' => $paymentsToday,
                'invoices_today' => $invoicesToday,
                'outstanding_invoices' => $outstandingInvoices,
                'renewal_monitoring' => true,
            ],
            'analytics_reports' => [
                'total_hospitals' => $totalTenants,
                'total_users' => $totalUsers,
                'revenue_today' => round($revenueToday, 2),
                'api_calls_today' => $apiCallsToday,
            ],
            'security_compliance' => [
                'activity_logs_today' => AuditLog::query()->whereDate('created_at', $today)->count(),
                'suspicious_activity' => $securityAlerts,
                'rbac_roles' => Role::query()->count(),
                'security_policy_enforcement' => true,
            ],
            'system_configuration' => [
                'smtp_configured' => (bool) config('mail.default'),
                'sms_gateway_configured' => (bool) config('services.twilio.sid'),
                'payment_gateway_configured' => (bool) config('services.stripe.key'),
                'api_configuration_ready' => true,
            ],
            'module_control' => [
                'feature_toggles_per_tenant' => true,
                'modules' => $moduleStatus,
            ],
            'backup_maintenance' => [
                'full_backup_supported' => true,
                'database_restore_supported' => true,
                'maintenance_mode' => app()->isDownForMaintenance(),
                'system_updates' => 'manual',
            ],
            'integration_control' => [
                'api_calls_today' => $apiCallsToday,
                'webhook_failures' => $webhookFailures,
                'third_party_integrations' => true,
            ],
            'ai_advanced' => [
                'ai_assistant_enabled' => (bool) env('AI_ASSISTANT_ENABLED', true),
                'ai_usage_monitoring' => true,
                'automation_rules' => true,
            ],
            'support_monitoring' => [
                'open_issues' => Task::query()->where('status', 'todo')->count(),
                'urgent_issues' => Task::query()->where('status', 'todo')->where('priority', 'urgent')->count(),
                'system_health_monitoring' => true,
            ],
            'upcoming' => $upcoming->take(6)->values()->all(),
        ]);
    }

    public function menu(Request $request): JsonResponse
    {
        $role = $this->normalizeRole($request->user()?->primaryRole() ?? 'tenant-admin');

        return response()->json([
            'role' => $role,
            'items' => $this->menusForUser($request->user()),
        ]);
    }

    public function superAdminMenu(Request $request): JsonResponse
    {
        $role = $this->normalizeRole($request->user()?->primaryRole() ?? 'tenant-admin');

        if ($role !== 'super-admin') {
            return response()->json([
                'message' => 'Forbidden. Super Admin access required.',
            ], 403);
        }

        return response()->json([
            'role' => $role,
            'items' => $this->superAdminMenuItems(),
        ]);
    }

    public function reportsSummary(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        return response()->json([
            'tenant_id' => $tenantId,
            'summary' => [
                'patients_total' => Patient::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count(),
                'appointments_today' => Appointment::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->whereDate('scheduled_at', today())->count(),
                'tasks_completed_today' => Task::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'completed')->whereDate('updated_at', today())->count(),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function menusForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $menus = Menu::query()
            ->with(['children' => fn ($query) => $query->active()->orderBy('order')])
            ->topLevel()
            ->active()
            ->orderBy('order')
            ->get()
            ->filter(function (Menu $menu) use ($user): bool {
                $children = $menu->children->filter(function (Menu $child) use ($user): bool {
                    if (! $child->permission) {
                        return true;
                    }

                    return $user->hasPermission($child->permission);
                })->values();

                $menu->setRelation('children', $children);

                if (! $menu->permission) {
                    return $children->isNotEmpty() || $menu->route !== null;
                }

                return $user->hasPermission($menu->permission);
            })
            ->values();

        return $menus->map(function (Menu $menu): array {
            return [
                'id' => $menu->id,
                'label' => $menu->label,
                'icon' => $menu->icon,
                'route' => $menu->route,
                'children' => $menu->children->map(fn (Menu $child) => [
                    'id' => $child->id,
                    'label' => $child->label,
                    'icon' => $child->icon,
                    'route' => $child->route,
                ])->values()->all(),
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function superAdminMenuItems(): array
    {
        return [
            [
                'id' => 1000,
                'label' => 'Dashboard',
                'icon' => 'LayoutDashboard',
                'route' => null,
                'children' => [
                    ['id' => 1001, 'label' => 'Overview / Stats', 'icon' => 'BarChart3', 'route' => '/dashboard'],
                    ['id' => 1002, 'label' => 'System Health', 'icon' => 'Activity', 'route' => '/dashboard/reports?tab=system-health'],
                    ['id' => 1003, 'label' => 'Revenue Analytics', 'icon' => 'LineChart', 'route' => '/dashboard/reports?tab=revenue-analytics'],
                ],
            ],
            [
                'id' => 1100,
                'label' => 'Tenants Management',
                'icon' => 'Building2',
                'route' => null,
                'children' => [
                    ['id' => 1101, 'label' => 'Tenant List (All Hospitals)', 'icon' => 'List', 'route' => '/dashboard/users?tab=tenants'],
                    ['id' => 1102, 'label' => 'Create Tenant', 'icon' => 'PlusCircle', 'route' => '/dashboard/users?tab=create-tenant'],
                    ['id' => 1103, 'label' => 'Tenant Details / Edit', 'icon' => 'SquarePen', 'route' => '/dashboard/users?tab=tenant-edit'],
                    ['id' => 1104, 'label' => 'Change Subscription Plan', 'icon' => 'CreditCard', 'route' => '/dashboard/billing?tab=change-plan'],
                    ['id' => 1105, 'label' => 'Suspend / Activate Tenant', 'icon' => 'Power', 'route' => '/dashboard/users?tab=tenant-status'],
                    ['id' => 1106, 'label' => 'Delete Tenant', 'icon' => 'Trash2', 'route' => '/dashboard/users?tab=tenant-delete'],
                ],
            ],
            [
                'id' => 1200,
                'label' => 'User Management',
                'icon' => 'Users',
                'route' => null,
                'children' => [
                    ['id' => 1201, 'label' => 'All Users (Across Tenants)', 'icon' => 'UsersRound', 'route' => '/dashboard/users?tab=all-users'],
                    ['id' => 1202, 'label' => 'Roles & Permissions', 'icon' => 'ShieldCheck', 'route' => '/dashboard/settings?tab=rbac'],
                    ['id' => 1203, 'label' => 'Assign Roles', 'icon' => 'UserCog', 'route' => '/dashboard/users?tab=assign-roles'],
                    ['id' => 1204, 'label' => 'Activity Logs', 'icon' => 'FileSearch', 'route' => '/dashboard/reports?tab=activity-logs'],
                ],
            ],
            [
                'id' => 1300,
                'label' => 'Modules & Features',
                'icon' => 'Boxes',
                'route' => null,
                'children' => [
                    ['id' => 1301, 'label' => 'Enable / Disable Modules', 'icon' => 'ToggleLeft', 'route' => '/dashboard/settings?tab=modules'],
                    ['id' => 1302, 'label' => 'OPD / IPD', 'icon' => 'Stethoscope', 'route' => '/dashboard/opd/queue'],
                    ['id' => 1303, 'label' => 'Pharmacy', 'icon' => 'Pill', 'route' => '/dashboard/inventory?tab=pharmacy'],
                    ['id' => 1304, 'label' => 'Lab', 'icon' => 'FlaskConical', 'route' => '/dashboard/reports?tab=lab'],
                    ['id' => 1305, 'label' => 'HRM', 'icon' => 'Users', 'route' => '/dashboard/employees'],
                    ['id' => 1306, 'label' => 'Billing & Finance', 'icon' => 'Receipt', 'route' => '/dashboard/billing'],
                    ['id' => 1307, 'label' => 'Inventory', 'icon' => 'Package', 'route' => '/dashboard/inventory'],
                    ['id' => 1308, 'label' => 'Feature Toggle Settings', 'icon' => 'SlidersHorizontal', 'route' => '/dashboard/settings?tab=feature-toggles'],
                ],
            ],
            [
                'id' => 1400,
                'label' => 'Subscription & Billing',
                'icon' => 'BadgeDollarSign',
                'route' => null,
                'children' => [
                    ['id' => 1401, 'label' => 'Plans Overview', 'icon' => 'FileText', 'route' => '/dashboard/billing?tab=plans-overview'],
                    ['id' => 1402, 'label' => 'Assign / Change Plans', 'icon' => 'Repeat', 'route' => '/dashboard/billing?tab=assign-plan'],
                    ['id' => 1403, 'label' => 'Payment Status', 'icon' => 'Wallet', 'route' => '/dashboard/billing?tab=payment-status'],
                    ['id' => 1404, 'label' => 'Billing History', 'icon' => 'History', 'route' => '/dashboard/billing?tab=history'],
                    ['id' => 1405, 'label' => 'Invoices', 'icon' => 'FileSpreadsheet', 'route' => '/dashboard/billing?tab=invoices'],
                ],
            ],
            [
                'id' => 1500,
                'label' => 'System Configuration',
                'icon' => 'Settings',
                'route' => null,
                'children' => [
                    ['id' => 1501, 'label' => 'General Settings', 'icon' => 'Settings2', 'route' => '/dashboard/settings'],
                    ['id' => 1502, 'label' => 'Email / SMS Configuration', 'icon' => 'Mail', 'route' => '/dashboard/settings?tab=communications'],
                    ['id' => 1503, 'label' => 'Payment Gateway Setup', 'icon' => 'CreditCard', 'route' => '/dashboard/settings?tab=payments'],
                    ['id' => 1504, 'label' => 'API & Integration Settings', 'icon' => 'PlugZap', 'route' => '/dashboard/settings?tab=integrations'],
                ],
            ],
            [
                'id' => 1600,
                'label' => 'Audit & Security',
                'icon' => 'Shield',
                'route' => null,
                'children' => [
                    ['id' => 1601, 'label' => 'Login Logs', 'icon' => 'LogIn', 'route' => '/dashboard/reports?tab=login-logs'],
                    ['id' => 1602, 'label' => 'Audit Logs', 'icon' => 'ScrollText', 'route' => '/dashboard/reports?tab=audit-logs'],
                    ['id' => 1603, 'label' => 'Access Control', 'icon' => 'Lock', 'route' => '/dashboard/settings?tab=access-control'],
                    ['id' => 1604, 'label' => 'Security Settings', 'icon' => 'ShieldAlert', 'route' => '/dashboard/settings?tab=security'],
                ],
            ],
            [
                'id' => 1700,
                'label' => 'Reports & Analytics',
                'icon' => 'ChartNoAxesCombined',
                'route' => null,
                'children' => [
                    ['id' => 1701, 'label' => 'Tenant Reports', 'icon' => 'Building2', 'route' => '/dashboard/reports?tab=tenant-reports'],
                    ['id' => 1702, 'label' => 'User Reports', 'icon' => 'UsersRound', 'route' => '/dashboard/reports?tab=user-reports'],
                    ['id' => 1703, 'label' => 'Revenue Reports', 'icon' => 'LineChart', 'route' => '/dashboard/reports?tab=revenue-reports'],
                    ['id' => 1704, 'label' => 'System Usage Analytics', 'icon' => 'BarChart3', 'route' => '/dashboard/reports?tab=usage-analytics'],
                ],
            ],
            [
                'id' => 1800,
                'label' => 'Support & Monitoring',
                'icon' => 'LifeBuoy',
                'route' => null,
                'children' => [
                    ['id' => 1801, 'label' => 'Tickets / Support Requests', 'icon' => 'MessagesSquare', 'route' => '/dashboard/tasks?tab=tickets'],
                    ['id' => 1802, 'label' => 'Notifications / Alerts', 'icon' => 'Bell', 'route' => '/dashboard/tasks?tab=alerts'],
                    ['id' => 1803, 'label' => 'System Maintenance', 'icon' => 'Wrench', 'route' => '/dashboard/settings?tab=maintenance'],
                ],
            ],
            [
                'id' => 1900,
                'label' => 'Advanced / AI',
                'icon' => 'Bot',
                'route' => null,
                'children' => [
                    ['id' => 1901, 'label' => 'AI Assistant Management', 'icon' => 'BotMessageSquare', 'route' => '/dashboard/tasks?tab=ai-assistant'],
                    ['id' => 1902, 'label' => 'Automation Rules', 'icon' => 'Workflow', 'route' => '/dashboard/settings?tab=automation'],
                    ['id' => 1903, 'label' => 'Advanced Analytics', 'icon' => 'Radar', 'route' => '/dashboard/reports?tab=advanced-analytics'],
                ],
            ],
            [
                'id' => 2000,
                'label' => 'Account',
                'icon' => 'UserRoundCog',
                'route' => null,
                'children' => [
                    ['id' => 2001, 'label' => 'Profile', 'icon' => 'UserRound', 'route' => '/dashboard/settings?tab=profile'],
                    ['id' => 2002, 'label' => 'Change Password', 'icon' => 'KeyRound', 'route' => '/dashboard/settings?tab=password'],
                    ['id' => 2003, 'label' => 'Logout', 'icon' => 'LogOut', 'route' => '/dashboard/settings?tab=logout'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function widgetsForRole(string $role, array $metrics): array
    {
        $profile = $this->roleProfiles()[$role] ?? $this->defaultRoleProfile($role);
        $widgetKeys = Arr::get($profile, 'widgets', ['total_patients', 'appointments_today', 'open_tasks', 'today_registrations']);

        return collect($widgetKeys)
            ->map(fn (string $key) => $this->makeWidget($key, $metrics))
            ->filter()
            ->values()
            ->all();
    }

    private function makeWidget(string $key, array $metrics): ?array
    {
        $definitions = [
            'total_hospitals' => ['label' => 'Total Hospitals', 'metric' => 'total_hospitals'],
            'active_tenants' => ['label' => 'Active Tenants', 'metric' => 'active_tenants'],
            'suspended_tenants' => ['label' => 'Suspended Tenants', 'metric' => 'suspended_tenants'],
            'global_users' => ['label' => 'Global Users', 'metric' => 'global_users'],
            'enabled_modules' => ['label' => 'Enabled Modules', 'metric' => 'enabled_modules'],
            'service_health' => ['label' => 'Service Health', 'metric' => 'service_health'],
            'total_patients' => ['label' => 'Total Patients', 'metric' => 'total_patients'],
            'total_staff' => ['label' => 'Total Staff', 'metric' => 'total_staff'],
            'appointments_today' => ['label' => 'Appointments Today', 'metric' => 'appointments_today'],
            'today_registrations' => ['label' => 'Registrations Today', 'metric' => 'today_registrations'],
            'open_tasks' => ['label' => 'Open Tasks', 'metric' => 'open_tasks'],
            'queue_waiting' => ['label' => 'Queue Waiting', 'metric' => 'queue_waiting'],
            'queue_with_doctor' => ['label' => 'In Consultation', 'metric' => 'queue_with_doctor'],
            'consultations_today' => ['label' => 'Consultations Today', 'metric' => 'consultations_today'],
            'prescriptions_today' => ['label' => 'Prescriptions Today', 'metric' => 'prescriptions_today'],
            'pending_investigations' => ['label' => 'Pending Investigations', 'metric' => 'pending_investigations'],
            'lab_pending' => ['label' => 'Lab Pending', 'metric' => 'lab_pending'],
            'lab_completed_today' => ['label' => 'Lab Completed Today', 'metric' => 'lab_completed_today'],
            'critical_labs' => ['label' => 'Critical Lab Alerts', 'metric' => 'critical_labs'],
            'pharmacy_queue' => ['label' => 'Prescription Queue', 'metric' => 'pharmacy_queue'],
            'inventory_low_stock' => ['label' => 'Low Stock Items', 'metric' => 'inventory_low_stock'],
            'invoices_today' => ['label' => 'Invoices Today', 'metric' => 'invoices_today'],
            'outstanding_bills' => ['label' => 'Outstanding Bills', 'metric' => 'outstanding_bills'],
            'revenue_today' => ['label' => 'Revenue Today', 'metric' => 'revenue_today'],
            'claims_open' => ['label' => 'Open Claims', 'metric' => 'claims_open'],
            'bed_occupied' => ['label' => 'Occupied Beds', 'metric' => 'bed_occupied'],
            'bed_available' => ['label' => 'Available Beds', 'metric' => 'bed_available'],
            'attendance_rate' => ['label' => 'Attendance Rate', 'metric' => 'attendance_rate'],
            'active_sessions' => ['label' => 'Active Sessions', 'metric' => 'active_sessions'],
            'security_alerts' => ['label' => 'Security Alerts', 'metric' => 'security_alerts'],
            'audit_events_today' => ['label' => 'Audit Events Today', 'metric' => 'audit_events_today'],
            'api_calls_today' => ['label' => 'API Calls Today', 'metric' => 'api_calls_today'],
            'webhook_failures' => ['label' => 'Webhook Failures', 'metric' => 'webhook_failures'],
            'payments_today' => ['label' => 'Payments Today', 'metric' => 'payments_today'],
        ];

        if (! isset($definitions[$key])) {
            return null;
        }

        $definition = $definitions[$key];

        return [
            'key' => $key,
            'label' => $definition['label'],
            'value' => $metrics[$definition['metric']] ?? 0,
        ];
    }

    private function normalizeRole(string $role): string
    {
        $normalized = Str::of($role)->lower()->replace([' ', '_'], '-')->toString();

        return match ($normalized) {
            'accountant-finance-manager' => 'accountant',
            'ambulance-driver-transport-staff' => 'ambulance-driver',
            'api-client-integration-role' => 'api-client',
            'auditor-compliance-officer' => 'auditor',
            'inventory-manager-store-manager' => 'inventory-manager',
            'it-admin-system-administrator' => 'it-admin',
            'patient-portal-user' => 'patient',
            'insurance-agent-partner' => 'insurance-agent',
            'tenant-admin-hospital-admin', 'hospital-admin' => 'tenant-admin',
            'ai-assistant-role' => 'ai-assistant',
            'lab-tech' => 'lab-technician',
            default => $normalized,
        };
    }

    private function defaultRoleProfile(string $role): array
    {
        return [
            'label' => Str::headline($role),
            'widgets' => ['total_patients', 'appointments_today', 'open_tasks', 'today_registrations'],
            'quick_actions' => [
                ['label' => 'Open Dashboard', 'route' => '/dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Patient List', 'route' => '/dashboard/patients', 'icon' => 'UserRound'],
            ],
            'focus_areas' => ['Operations', 'Patient Safety'],
        ];
    }

    private function roleProfiles(): array
    {
        return [
            'super-admin' => [
                'label' => 'Super Admin',
                'widgets' => ['total_hospitals', 'active_tenants', 'suspended_tenants', 'global_users', 'revenue_today', 'payments_today', 'api_calls_today', 'security_alerts', 'webhook_failures', 'enabled_modules'],
                'quick_actions' => [
                    ['label' => 'Tenant Management', 'route' => '/dashboard/users', 'icon' => 'Building2'],
                    ['label' => 'Global User Access', 'route' => '/dashboard/users', 'icon' => 'Users'],
                    ['label' => 'Subscription & Billing', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                    ['label' => 'System Analytics', 'route' => '/dashboard/reports', 'icon' => 'LineChart'],
                    ['label' => 'Security & RBAC', 'route' => '/dashboard/settings', 'icon' => 'ShieldCheck'],
                    ['label' => 'API & Integrations', 'route' => '/dashboard/settings', 'icon' => 'PlugZap'],
                    ['label' => 'Module Controls', 'route' => '/dashboard/settings', 'icon' => 'Boxes'],
                    ['label' => 'Backup & Maintenance', 'route' => '/dashboard/settings', 'icon' => 'Download'],
                    ['label' => 'AI Governance', 'route' => '/dashboard/tasks', 'icon' => 'Bot'],
                    ['label' => 'Support Monitoring', 'route' => '/dashboard/tasks', 'icon' => 'Bell'],
                ],
                'focus_areas' => [
                    'Global SaaS Operations',
                    'Tenant Lifecycle Management',
                    'Subscription Revenue Oversight',
                    'Security and Compliance Governance',
                    'Integrations and Platform Reliability',
                ],
            ],
            'tenant-admin' => [
                'label' => 'Tenant Admin',
                'widgets' => ['total_staff', 'total_patients', 'revenue_today', 'outstanding_bills'],
                'quick_actions' => [
                    ['label' => 'Manage Users', 'route' => '/dashboard/users', 'icon' => 'Users'],
                    ['label' => 'OPD Queue', 'route' => '/dashboard/opd/queue', 'icon' => 'ListOrdered'],
                    ['label' => 'Billing Console', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                ],
                'focus_areas' => ['Hospital Operations', 'Financial Control'],
            ],
            'hospital-manager' => [
                'label' => 'Hospital Manager',
                'widgets' => ['bed_occupied', 'bed_available', 'queue_waiting', 'open_tasks'],
                'quick_actions' => [
                    ['label' => 'Appointments', 'route' => '/dashboard/appointments', 'icon' => 'Bed'],
                    ['label' => 'OPD Queue', 'route' => '/dashboard/opd/queue', 'icon' => 'Activity'],
                    ['label' => 'Reports', 'route' => '/dashboard/reports', 'icon' => 'BarChart3'],
                ],
                'focus_areas' => ['Capacity', 'Clinical Throughput'],
            ],
            'operations-manager' => [
                'label' => 'Operations Manager',
                'widgets' => ['queue_waiting', 'lab_pending', 'pharmacy_queue', 'open_tasks'],
                'quick_actions' => [
                    ['label' => 'OPD Queue', 'route' => '/dashboard/opd/queue', 'icon' => 'Activity'],
                    ['label' => 'Vitals Console', 'route' => '/dashboard/opd/vitals', 'icon' => 'HeartPulse'],
                    ['label' => 'Task Board', 'route' => '/dashboard/tasks', 'icon' => 'Factory'],
                ],
                'focus_areas' => ['Queue SLA', 'Facility Flow'],
            ],
            'doctor' => [
                'label' => 'Doctor',
                'widgets' => ['appointments_today', 'queue_with_doctor', 'pending_investigations', 'prescriptions_today'],
                'quick_actions' => [
                    ['label' => 'OPD Queue', 'route' => '/dashboard/opd/queue', 'icon' => 'Stethoscope'],
                    ['label' => 'SOAP Editor', 'route' => '/dashboard/opd/consultations', 'icon' => 'FilePenLine'],
                    ['label' => 'Record Vitals', 'route' => '/dashboard/opd/vitals', 'icon' => 'HeartPulse'],
                    ['label' => 'Appointments', 'route' => '/dashboard/appointments', 'icon' => 'CalendarDays'],
                ],
                'focus_areas' => ['Consultations', 'Clinical Safety'],
            ],
            'nurse' => [
                'label' => 'Nurse',
                'widgets' => ['queue_waiting', 'queue_with_doctor', 'open_tasks', 'bed_occupied'],
                'quick_actions' => [
                    ['label' => 'Vitals Entry', 'route' => '/dashboard/opd/vitals', 'icon' => 'HeartPulse'],
                    ['label' => 'Queue Board', 'route' => '/dashboard/opd/queue', 'icon' => 'ListOrdered'],
                    ['label' => 'Patients', 'route' => '/dashboard/patients', 'icon' => 'UserRound'],
                ],
                'focus_areas' => ['Triage', 'Medication Compliance'],
            ],
            'receptionist' => [
                'label' => 'Receptionist',
                'widgets' => ['today_registrations', 'appointments_today', 'queue_waiting', 'invoices_today'],
                'quick_actions' => [
                    ['label' => 'Register Patient', 'route' => '/clinical/patients/register', 'icon' => 'UserPlus'],
                    ['label' => 'Appointments', 'route' => '/dashboard/appointments', 'icon' => 'CalendarDays'],
                    ['label' => 'Billing', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                ],
                'focus_areas' => ['Front Desk Throughput', 'Appointment Flow'],
            ],
            'pharmacist' => [
                'label' => 'Pharmacist',
                'widgets' => ['pharmacy_queue', 'inventory_low_stock', 'prescriptions_today', 'revenue_today'],
                'quick_actions' => [
                    ['label' => 'Prescription Queue', 'route' => '/dashboard/opd/queue', 'icon' => 'Pill'],
                    ['label' => 'Stock Alerts', 'route' => '/dashboard/inventory', 'icon' => 'AlertTriangle'],
                    ['label' => 'Dispense', 'route' => '/dashboard/billing', 'icon' => 'PackageCheck'],
                ],
                'focus_areas' => ['Dispense Safety', 'Stock Continuity'],
            ],
            'lab-technician' => [
                'label' => 'Lab Technician',
                'widgets' => ['lab_pending', 'lab_completed_today', 'critical_labs', 'open_tasks'],
                'quick_actions' => [
                    ['label' => 'Lab Queue', 'route' => '/dashboard/reports', 'icon' => 'FlaskConical'],
                    ['label' => 'Result Entry', 'route' => '/dashboard/tasks', 'icon' => 'FilePenLine'],
                    ['label' => 'Sample Tracking', 'route' => '/dashboard/patients', 'icon' => 'QrCode'],
                ],
                'focus_areas' => ['Turnaround Time', 'Critical Alerts'],
            ],
            'accountant' => [
                'label' => 'Accountant',
                'widgets' => ['revenue_today', 'invoices_today', 'outstanding_bills', 'claims_open'],
                'quick_actions' => [
                    ['label' => 'Invoices', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                    ['label' => 'Claims', 'route' => '/dashboard/reports', 'icon' => 'ShieldCheck'],
                    ['label' => 'Reports', 'route' => '/dashboard/reports', 'icon' => 'BarChart3'],
                ],
                'focus_areas' => ['Revenue Cycle', 'Receivables'],
            ],
            'ward-manager' => [
                'label' => 'Ward Manager',
                'widgets' => ['bed_occupied', 'bed_available', 'open_tasks', 'queue_with_doctor'],
                'quick_actions' => [
                    ['label' => 'Patient List', 'route' => '/dashboard/patients', 'icon' => 'Bed'],
                    ['label' => 'Queue Board', 'route' => '/dashboard/opd/queue', 'icon' => 'ClipboardList'],
                    ['label' => 'Task Board', 'route' => '/dashboard/tasks', 'icon' => 'DoorOpen'],
                ],
                'focus_areas' => ['Bed Utilization', 'Shift Coordination'],
            ],
            'ambulance-driver' => [
                'label' => 'Ambulance Driver',
                'widgets' => ['open_tasks', 'queue_waiting', 'appointments_today', 'active_sessions'],
                'quick_actions' => [
                    ['label' => 'Dispatch Board', 'route' => '/dashboard/appointments', 'icon' => 'Ambulance'],
                    ['label' => 'Task Board', 'route' => '/dashboard/tasks', 'icon' => 'Siren'],
                ],
                'focus_areas' => ['Dispatch Execution', 'Handover Timeliness'],
            ],
            'it-admin' => [
                'label' => 'IT Admin',
                'widgets' => ['service_health', 'security_alerts', 'active_sessions', 'api_calls_today'],
                'quick_actions' => [
                    ['label' => 'Access Logs', 'route' => '/dashboard/settings', 'icon' => 'FileSearch'],
                    ['label' => 'Integrations', 'route' => '/dashboard/reports', 'icon' => 'PlugZap'],
                    ['label' => 'System Users', 'route' => '/dashboard/users', 'icon' => 'Server'],
                ],
                'focus_areas' => ['Security Posture', 'Platform Reliability'],
            ],
            'inventory-manager' => [
                'label' => 'Inventory Manager',
                'widgets' => ['inventory_low_stock', 'open_tasks', 'invoices_today', 'claims_open'],
                'quick_actions' => [
                    ['label' => 'Stock Levels', 'route' => '/dashboard/inventory', 'icon' => 'Boxes'],
                    ['label' => 'Purchase Orders', 'route' => '/dashboard/tasks', 'icon' => 'ShoppingCart'],
                    ['label' => 'Suppliers', 'route' => '/dashboard/reports', 'icon' => 'Truck'],
                ],
                'focus_areas' => ['Stock Continuity', 'Procurement'],
            ],
            'hr-manager' => [
                'label' => 'HR Manager',
                'widgets' => ['total_staff', 'attendance_rate', 'open_tasks', 'active_sessions'],
                'quick_actions' => [
                    ['label' => 'Employees', 'route' => '/dashboard/employees', 'icon' => 'Users2'],
                    ['label' => 'Attendance', 'route' => '/dashboard/attendance', 'icon' => 'CalendarCheck2'],
                    ['label' => 'Payroll', 'route' => '/dashboard/payroll', 'icon' => 'BadgeDollarSign'],
                ],
                'focus_areas' => ['Workforce Readiness', 'Payroll Timeliness'],
            ],
            'patient' => [
                'label' => 'Patient',
                'widgets' => ['appointments_today', 'prescriptions_today', 'outstanding_bills', 'claims_open'],
                'quick_actions' => [
                    ['label' => 'My Appointments', 'route' => '/dashboard/appointments', 'icon' => 'CalendarDays'],
                    ['label' => 'My Billing', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                    ['label' => 'Profile Settings', 'route' => '/dashboard/settings', 'icon' => 'Settings'],
                ],
                'focus_areas' => ['Self-Service Care', 'Billing Transparency'],
            ],
            'insurance-agent' => [
                'label' => 'Insurance Agent',
                'widgets' => ['claims_open', 'outstanding_bills', 'invoices_today', 'revenue_today'],
                'quick_actions' => [
                    ['label' => 'Claims Desk', 'route' => '/dashboard/billing', 'icon' => 'ShieldCheck'],
                    ['label' => 'Policies', 'route' => '/dashboard/patients', 'icon' => 'FileBadge2'],
                    ['label' => 'Reports', 'route' => '/dashboard/reports', 'icon' => 'BarChart3'],
                ],
                'focus_areas' => ['Claim Turnaround', 'Settlement Status'],
            ],
            'auditor' => [
                'label' => 'Auditor',
                'widgets' => ['audit_events_today', 'security_alerts', 'claims_open', 'outstanding_bills'],
                'quick_actions' => [
                    ['label' => 'Audit Trails', 'route' => '/dashboard/settings', 'icon' => 'FileSearch'],
                    ['label' => 'Compliance Reports', 'route' => '/dashboard/reports', 'icon' => 'ShieldCheck'],
                ],
                'focus_areas' => ['Compliance Assurance', 'Risk Review'],
            ],
            'data-analyst' => [
                'label' => 'Data Analyst',
                'widgets' => ['revenue_today', 'appointments_today', 'lab_completed_today', 'open_tasks'],
                'quick_actions' => [
                    ['label' => 'Analytics', 'route' => '/dashboard/reports', 'icon' => 'LineChart'],
                    ['label' => 'Task Board', 'route' => '/dashboard/tasks', 'icon' => 'Download'],
                ],
                'focus_areas' => ['KPI Monitoring', 'Forecasting'],
            ],
            'api-client' => [
                'label' => 'API Client',
                'widgets' => ['api_calls_today', 'webhook_failures', 'active_sessions', 'service_health'],
                'quick_actions' => [
                    ['label' => 'API Keys', 'route' => '/dashboard/settings', 'icon' => 'KeyRound'],
                    ['label' => 'Webhooks', 'route' => '/dashboard/reports', 'icon' => 'Webhook'],
                ],
                'focus_areas' => ['API Reliability', 'Delivery Success'],
            ],
            'ai-assistant' => [
                'label' => 'AI Assistant',
                'widgets' => ['active_sessions', 'open_tasks', 'security_alerts', 'queue_waiting'],
                'quick_actions' => [
                    ['label' => 'Assistant Console', 'route' => '/dashboard/tasks', 'icon' => 'Bot'],
                    ['label' => 'Escalations', 'route' => '/dashboard/patients', 'icon' => 'TriangleAlert'],
                ],
                'focus_areas' => ['Clinical Assist', 'Safe Escalation'],
            ],
        ];
    }

    private function collectMetrics(?int $tenantId, ?int $userId): array
    {
        $totalHospitals = 0;
        if (Schema::hasTable('tenants')) {
            $totalHospitals = \DB::table('tenants')->count();
        }

        $activeTenants = 0;
        if (Schema::hasTable('tenants')) {
            $activeTenants = \DB::table('tenants')->where('status', 'active')->count();
        }

        $suspendedTenants = 0;
        if (Schema::hasTable('tenants')) {
            $suspendedTenants = \DB::table('tenants')
                ->whereIn('status', ['suspended', 'inactive'])
                ->count();
        }

        $enabledModules = Menu::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->count();

        $totalPatients = Patient::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->count();

        $todayRegistrations = Patient::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->count();

        $totalStaff = User::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->count();

        $appointmentsToday = Appointment::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('scheduled_at', today())
            ->count();

        $consultationsToday = Consultation::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->count();

        $prescriptionsToday = Prescription::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->count();

        $pendingInvestigations = InvestigationOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['ordered', 'pending'])
            ->count();

        $labPending = LabOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['ordered', 'sample_collected', 'in_progress'])
            ->count();

        $labCompletedToday = LabResult::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('updated_at', today())
            ->count();

        $criticalLabs = LabResult::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('is_abnormal', true)
            ->count();

        $queueWaiting = OpdQueue::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'waiting')
            ->count();

        $queueWithDoctor = OpdQueue::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'with_doctor')
            ->count();

        $pharmacyQueue = Prescription::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->count();

        $inventoryLowStock = InventoryItem::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereColumn('stock_on_hand', '<=', 'reorder_level')
            ->count();

        $revenueToday = (float) Invoice::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('issued_at', today())
            ->sum('amount_paid');

        $invoicesToday = Invoice::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('issued_at', today())
            ->count();

        $paymentsToday = 0;
        if (Schema::hasTable('payments')) {
            $paymentsToday = \DB::table('payments')
                ->whereDate('paid_at', today())
                ->count();
        }

        $outstandingBills = (float) Invoice::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->sum(\DB::raw('GREATEST(total_due - amount_paid, 0)'));

        $claimsOpen = InsuranceClaim::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        $bedOccupied = Bed::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('is_occupied', true)
            ->count();

        $bedAvailable = Bed::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('is_occupied', false)
            ->count();

        $openTasks = Task::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'todo')
            ->count();

        $attendanceRate = 0;
        if (Schema::hasTable('staff_profiles')) {
            $activeStaff = StaffProfile::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->where('status', 'active')
                ->count();

            if ($activeStaff > 0) {
                $attendanceRate = 100;
            }
        }

        $activeSessions = 0;
        if (Schema::hasTable('personal_access_tokens')) {
            $activeSessions = \DB::table('personal_access_tokens')
                ->when($tenantId, function ($q) use ($tenantId) {
                    $q->whereIn('tokenable_id', User::query()->where('tenant_id', $tenantId)->pluck('id'));
                })
                ->count();
        }

        $securityAlerts = Task::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('priority', 'urgent')
            ->where('status', 'todo')
            ->count();

        $auditEventsToday = 0;
        if (Schema::hasTable('audit_logs')) {
            $auditEventsToday = \DB::table('audit_logs')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())
                ->count();
        }

        $apiCallsToday = 0;
        if (Schema::hasTable('audit_logs')) {
            $apiCallsToday = \DB::table('audit_logs')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())
                ->where('path', 'like', '%api/%')
                ->count();
        }

        $webhookFailures = 0;
        if (Schema::hasTable('audit_logs')) {
            $webhookFailures = \DB::table('audit_logs')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())
                ->where('path', 'like', '%webhook%')
                ->where('status_code', '>=', 400)
                ->count();
        }

        $doctorAppointmentsToday = Appointment::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($userId, fn ($q) => $q->where('doctor_id', $userId))
            ->whereDate('scheduled_at', today())
            ->count();

        return [
            'total_hospitals' => $totalHospitals,
            'active_tenants' => $activeTenants,
            'suspended_tenants' => $suspendedTenants,
            'global_users' => User::count(),
            'enabled_modules' => $enabledModules,
            'service_health' => 'Healthy',
            'total_patients' => $totalPatients,
            'today_registrations' => $todayRegistrations,
            'total_staff' => $totalStaff,
            'appointments_today' => $doctorAppointmentsToday > 0 ? $doctorAppointmentsToday : $appointmentsToday,
            'consultations_today' => $consultationsToday,
            'prescriptions_today' => $prescriptionsToday,
            'pending_investigations' => $pendingInvestigations,
            'lab_pending' => $labPending,
            'lab_completed_today' => $labCompletedToday,
            'critical_labs' => $criticalLabs,
            'queue_waiting' => $queueWaiting,
            'queue_with_doctor' => $queueWithDoctor,
            'pharmacy_queue' => $pharmacyQueue,
            'inventory_low_stock' => $inventoryLowStock,
            'revenue_today' => round($revenueToday, 2),
            'invoices_today' => $invoicesToday,
            'payments_today' => $paymentsToday,
            'outstanding_bills' => round($outstandingBills, 2),
            'claims_open' => $claimsOpen,
            'bed_occupied' => $bedOccupied,
            'bed_available' => $bedAvailable,
            'open_tasks' => $openTasks,
            'attendance_rate' => $attendanceRate,
            'active_sessions' => $activeSessions,
            'security_alerts' => $securityAlerts,
            'audit_events_today' => $auditEventsToday,
            'api_calls_today' => $apiCallsToday,
            'webhook_failures' => $webhookFailures,
        ];
    }
}
