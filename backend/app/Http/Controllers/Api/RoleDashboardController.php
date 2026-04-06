<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RoleDashboardController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $user = $request->user();
        $role = $user?->primaryRole() ?? 'tenant-admin';

        return response()->json([
            'role' => $role,
            'tenant_id' => $tenantId,
            'menus' => $this->menusForRole($role),
            'widgets' => $this->widgetsForRole($role, $tenantId, $user?->id),
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
        $role = $user?->primaryRole() ?? 'tenant-admin';

        return response()->json([
            'role' => $role,
            'items' => $this->widgetsForRole($role, $tenantId, $user?->id),
        ]);
    }

    public function menu(Request $request): JsonResponse
    {
        $role = $request->user()?->primaryRole() ?? 'tenant-admin';

        return response()->json([
            'role' => $role,
            'items' => $this->menusForRole($role),
        ]);
    }

    public function reportsSummary(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        return response()->json([
            'tenant_id' => $tenantId,
            'summary' => [
                'patients_total' => Patient::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count(),
                'appointments_today' => Appointment::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->whereDate('appointment_date', today())->count(),
                'tasks_completed_today' => Task::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'completed')->whereDate('updated_at', today())->count(),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function menusForRole(string $role): array
    {
        $base = [
            ['label' => 'Dashboard', 'route' => '/dashboard', 'icon' => 'LayoutDashboard'],
            ['label' => 'Users', 'route' => '/dashboard/users', 'icon' => 'Users'],
            ['label' => 'Patients', 'route' => '/dashboard/patients', 'icon' => 'UserRound'],
            ['label' => 'Appointments', 'route' => '/dashboard/appointments', 'icon' => 'CalendarDays'],
            ['label' => 'Billing', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
            ['label' => 'Inventory', 'route' => '/dashboard/inventory', 'icon' => 'Boxes'],
            ['label' => 'Reports', 'route' => '/dashboard/reports', 'icon' => 'BarChart3'],
            ['label' => 'Settings', 'route' => '/dashboard/settings', 'icon' => 'Settings'],
        ];

        return match ($role) {
            'doctor' => array_values(array_filter($base, fn ($item) => in_array($item['label'], ['Dashboard', 'Patients', 'Appointments', 'Reports'], true))),
            'nurse' => array_values(array_filter($base, fn ($item) => in_array($item['label'], ['Dashboard', 'Patients', 'Appointments'], true))),
            'patient' => [
                ['label' => 'Dashboard', 'route' => '/dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Appointments', 'route' => '/dashboard/appointments', 'icon' => 'CalendarDays'],
                ['label' => 'Reports', 'route' => '/dashboard/reports', 'icon' => 'FileText'],
                ['label' => 'Billing', 'route' => '/dashboard/billing', 'icon' => 'Receipt'],
                ['label' => 'Settings', 'route' => '/dashboard/settings', 'icon' => 'Settings'],
            ],
            default => $base,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function widgetsForRole(string $role, ?int $tenantId, ?int $userId): array
    {
        $patientCount = Patient::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count();
        $staffCount = User::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count();
        $appointmentsToday = Appointment::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('appointment_date', today())
            ->count();

        $dailyRevenue = 0;
        if (Schema::hasTable('invoices')) {
            $dailyRevenue = (int) \DB::table('invoices')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())
                ->sum('total_amount');
        }

        return match ($role) {
            'super-admin' => [
                ['key' => 'total_hospitals', 'label' => 'Total Hospitals', 'value' => User::query()->whereNull('tenant_id')->count()],
                ['key' => 'active_subscriptions', 'label' => 'Active Subscriptions', 'value' => 0],
                ['key' => 'revenue_analytics', 'label' => 'Revenue Analytics', 'value' => 'Chart'],
                ['key' => 'system_health', 'label' => 'System Health Status', 'value' => 'Healthy'],
                ['key' => 'global_users', 'label' => 'Global User Count', 'value' => User::count()],
            ],
            'tenant-admin', 'hospital-manager', 'operations-manager' => [
                ['key' => 'total_patients', 'label' => 'Total Patients', 'value' => $patientCount],
                ['key' => 'total_staff', 'label' => 'Total Staff', 'value' => $staffCount],
                ['key' => 'daily_revenue', 'label' => 'Daily Revenue', 'value' => $dailyRevenue],
                ['key' => 'appointments_today', 'label' => 'Appointments Today', 'value' => $appointmentsToday],
                ['key' => 'department_overview', 'label' => 'Department Overview', 'value' => 'Operational'],
            ],
            'doctor' => [
                ['key' => 'todays_appointments', 'label' => "Today's Appointments", 'value' => Appointment::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('doctor_id', $userId)->whereDate('appointment_date', today())->count()],
                ['key' => 'patient_list', 'label' => 'Patient List', 'value' => $patientCount],
                ['key' => 'recent_prescriptions', 'label' => 'Recent Prescriptions', 'value' => 0],
                ['key' => 'notifications', 'label' => 'Notifications', 'value' => Task::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('assigned_to_id', $userId)->where('status', 'todo')->count()],
            ],
            'nurse' => [
                ['key' => 'assigned_patients', 'label' => 'Assigned Patients', 'value' => 0],
                ['key' => 'vitals_monitoring', 'label' => 'Vitals Monitoring', 'value' => 'Live'],
                ['key' => 'medication_schedule', 'label' => 'Medication Schedule', 'value' => 0],
            ],
            'receptionist' => [
                ['key' => 'patient_registration', 'label' => 'Patient Registration', 'value' => Patient::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->whereDate('created_at', today())->count()],
                ['key' => 'appointment_booking', 'label' => 'Appointment Booking', 'value' => $appointmentsToday],
                ['key' => 'queue_management', 'label' => 'Queue Management', 'value' => 'Active'],
            ],
            'pharmacist' => [
                ['key' => 'prescription_queue', 'label' => 'Prescription Queue', 'value' => 0],
                ['key' => 'medicine_stock_alerts', 'label' => 'Medicine Stock Alerts', 'value' => 0],
                ['key' => 'sales_today', 'label' => 'Sales Today', 'value' => $dailyRevenue],
            ],
            'lab-technician' => [
                ['key' => 'pending_tests', 'label' => 'Pending Tests', 'value' => 0],
                ['key' => 'completed_reports', 'label' => 'Completed Reports', 'value' => 0],
                ['key' => 'sample_tracking', 'label' => 'Sample Tracking', 'value' => 'In Progress'],
            ],
            'accountant' => [
                ['key' => 'income_vs_expense', 'label' => 'Income vs Expense Chart', 'value' => 'Chart'],
                ['key' => 'billing_records', 'label' => 'Billing Records', 'value' => 0],
                ['key' => 'pending_payments', 'label' => 'Pending Payments', 'value' => 0],
            ],
            'ward-manager' => [
                ['key' => 'bed_availability', 'label' => 'Bed Availability', 'value' => 0],
                ['key' => 'admitted_patients', 'label' => 'Admitted Patients', 'value' => 0],
                ['key' => 'discharge_list', 'label' => 'Discharge List', 'value' => 0],
            ],
            'hr-manager' => [
                ['key' => 'employee_list', 'label' => 'Employee List', 'value' => $staffCount],
                ['key' => 'attendance_overview', 'label' => 'Attendance Overview', 'value' => 'On Track'],
                ['key' => 'leave_requests', 'label' => 'Leave Requests', 'value' => 0],
            ],
            'inventory-manager' => [
                ['key' => 'stock_levels', 'label' => 'Stock Levels', 'value' => 0],
                ['key' => 'purchase_orders', 'label' => 'Purchase Orders', 'value' => 0],
                ['key' => 'supplier_list', 'label' => 'Supplier List', 'value' => 0],
            ],
            'patient' => [
                ['key' => 'my_appointments', 'label' => 'My Appointments', 'value' => 0],
                ['key' => 'prescriptions', 'label' => 'Prescriptions', 'value' => 0],
                ['key' => 'reports_download', 'label' => 'Reports Download', 'value' => 0],
                ['key' => 'billing_history', 'label' => 'Billing History', 'value' => 0],
            ],
            'auditor' => [
                ['key' => 'system_logs', 'label' => 'System Logs', 'value' => 0],
                ['key' => 'activity_reports', 'label' => 'Activity Reports', 'value' => 0],
                ['key' => 'compliance_alerts', 'label' => 'Compliance Alerts', 'value' => 0],
            ],
            'data-analyst' => [
                ['key' => 'advanced_charts', 'label' => 'Advanced Charts (BI)', 'value' => 'Chart'],
                ['key' => 'trends_predictions', 'label' => 'Trends & Predictions', 'value' => 'Model Ready'],
            ],
            default => [
                ['key' => 'total_patients', 'label' => 'Total Patients', 'value' => $patientCount],
                ['key' => 'appointments_today', 'label' => 'Appointments Today', 'value' => $appointmentsToday],
            ],
        };
    }
}
