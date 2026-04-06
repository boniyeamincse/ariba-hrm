<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::query()->delete();

        $menuTree = [
            [
                'label' => 'Dashboard',
                'icon' => 'LayoutDashboard',
                'route' => '/dashboard',
                'permission' => 'dashboard.view',
                'order' => 1,
                'children' => [
                    ['label' => 'Overview', 'icon' => 'Gauge', 'route' => '/dashboard', 'permission' => 'dashboard.view', 'order' => 1],
                    ['label' => 'My Tasks', 'icon' => 'CheckSquare', 'route' => '/dashboard/tasks', 'permission' => 'dashboard.view', 'order' => 2],
                ],
            ],
            [
                'label' => 'Users',
                'icon' => 'Users',
                'route' => '/dashboard/users',
                'permission' => 'users.view',
                'order' => 2,
                'children' => [
                    ['label' => 'User List', 'icon' => 'UsersRound', 'route' => '/dashboard/users', 'permission' => 'users.view', 'order' => 1],
                    ['label' => 'Create User', 'icon' => 'UserPlus', 'route' => '/dashboard/users/new', 'permission' => 'users.manage', 'order' => 2],
                    ['label' => 'Roles & Permissions', 'icon' => 'Shield', 'route' => '/dashboard/users/roles', 'permission' => 'auth.manage-roles', 'order' => 3],
                ],
            ],
            [
                'label' => 'Patients',
                'icon' => 'UserRound',
                'route' => '/dashboard/patients',
                'permission' => 'patient.view',
                'order' => 3,
                'children' => [
                    ['label' => 'Patient List', 'icon' => 'ClipboardList', 'route' => '/dashboard/patients', 'permission' => 'patient.view', 'order' => 1],
                    ['label' => 'Register Patient', 'icon' => 'UserRoundPlus', 'route' => '/dashboard/patients/register', 'permission' => 'patient.create', 'order' => 2],
                    ['label' => 'Medical History', 'icon' => 'HeartPulse', 'route' => '/dashboard/patients/history', 'permission' => 'patient.view', 'order' => 3],
                    ['label' => 'Visit Timeline', 'icon' => 'History', 'route' => '/dashboard/patients/visits', 'permission' => 'patient.view', 'order' => 4],
                ],
            ],
            [
                'label' => 'Appointments',
                'icon' => 'CalendarDays',
                'route' => '/dashboard/appointments',
                'permission' => 'appointment.view',
                'order' => 4,
                'children' => [
                    ['label' => 'Schedule', 'icon' => 'Calendar', 'route' => '/dashboard/appointments', 'permission' => 'appointment.view', 'order' => 1],
                    ['label' => 'Book Appointment', 'icon' => 'CalendarPlus', 'route' => '/dashboard/appointments/book', 'permission' => 'appointment.manage', 'order' => 2],
                    ['label' => 'Queue Board', 'icon' => 'ListOrdered', 'route' => '/dashboard/appointments/queue', 'permission' => 'appointment.view', 'order' => 3],
                ],
            ],
            [
                'label' => 'Billing',
                'icon' => 'Receipt',
                'route' => '/dashboard/billing',
                'permission' => 'billing.view',
                'order' => 5,
                'children' => [
                    ['label' => 'Invoices', 'icon' => 'FileText', 'route' => '/dashboard/billing/invoices', 'permission' => 'billing.view', 'order' => 1],
                    ['label' => 'Payments', 'icon' => 'Wallet', 'route' => '/dashboard/billing/payments', 'permission' => 'billing.manage', 'order' => 2],
                    ['label' => 'Discount Approvals', 'icon' => 'BadgePercent', 'route' => '/dashboard/billing/discounts', 'permission' => 'billing.manage', 'order' => 3],
                ],
            ],
            [
                'label' => 'Inventory',
                'icon' => 'Boxes',
                'route' => '/dashboard/inventory',
                'permission' => 'inventory.view',
                'order' => 6,
                'children' => [
                    ['label' => 'Stock Levels', 'icon' => 'PackageSearch', 'route' => '/dashboard/inventory', 'permission' => 'inventory.view', 'order' => 1],
                    ['label' => 'Purchase Orders', 'icon' => 'ShoppingCart', 'route' => '/dashboard/inventory/purchase-orders', 'permission' => 'inventory.manage', 'order' => 2],
                    ['label' => 'Suppliers', 'icon' => 'Truck', 'route' => '/dashboard/inventory/suppliers', 'permission' => 'inventory.manage', 'order' => 3],
                ],
            ],
            [
                'label' => 'Pharmacy',
                'icon' => 'Pill',
                'route' => '/dashboard/pharmacy',
                'permission' => 'pharmacy.view',
                'order' => 7,
                'children' => [
                    ['label' => 'Prescription Queue', 'icon' => 'ClipboardCheck', 'route' => '/dashboard/pharmacy/queue', 'permission' => 'pharmacy.view', 'order' => 1],
                    ['label' => 'Dispense', 'icon' => 'Syringe', 'route' => '/dashboard/pharmacy/dispense', 'permission' => 'pharmacy.manage', 'order' => 2],
                    ['label' => 'Stock Alerts', 'icon' => 'TriangleAlert', 'route' => '/dashboard/pharmacy/stock-alerts', 'permission' => 'pharmacy.manage', 'order' => 3],
                ],
            ],
            [
                'label' => 'Laboratory',
                'icon' => 'FlaskConical',
                'route' => '/dashboard/lab',
                'permission' => 'lab.view',
                'order' => 8,
                'children' => [
                    ['label' => 'Pending Tests', 'icon' => 'TestTube', 'route' => '/dashboard/lab/pending', 'permission' => 'lab.view', 'order' => 1],
                    ['label' => 'Result Entry', 'icon' => 'FilePenLine', 'route' => '/dashboard/lab/results', 'permission' => 'lab.manage', 'order' => 2],
                    ['label' => 'Sample Tracking', 'icon' => 'ScanLine', 'route' => '/dashboard/lab/samples', 'permission' => 'lab.manage', 'order' => 3],
                ],
            ],
            [
                'label' => 'HRM',
                'icon' => 'BriefcaseBusiness',
                'route' => '/dashboard/hr',
                'permission' => 'hr.view',
                'order' => 9,
                'children' => [
                    ['label' => 'Employees', 'icon' => 'IdCard', 'route' => '/dashboard/hr/employees', 'permission' => 'hr.view', 'order' => 1],
                    ['label' => 'Attendance', 'icon' => 'Clock3', 'route' => '/dashboard/hr/attendance', 'permission' => 'hr.manage', 'order' => 2],
                    ['label' => 'Leave Requests', 'icon' => 'CalendarClock', 'route' => '/dashboard/hr/leave', 'permission' => 'hr.manage', 'order' => 3],
                    ['label' => 'Payroll', 'icon' => 'HandCoins', 'route' => '/dashboard/hr/payroll', 'permission' => 'hr.manage', 'order' => 4],
                ],
            ],
            [
                'label' => 'Reports',
                'icon' => 'BarChart3',
                'route' => '/dashboard/reports',
                'permission' => 'reports.view',
                'order' => 10,
                'children' => [
                    ['label' => 'Summary', 'icon' => 'ChartColumnBig', 'route' => '/dashboard/reports', 'permission' => 'reports.view', 'order' => 1],
                    ['label' => 'Export Center', 'icon' => 'Download', 'route' => '/dashboard/reports/export', 'permission' => 'reports.export', 'order' => 2],
                ],
            ],
            [
                'label' => 'OPD',
                'icon' => 'Stethoscope',
                'route' => '/dashboard/opd',
                'permission' => 'appointment.view',
                'order' => 11,
                'children' => [
                    ['label' => 'Queue Dashboard', 'icon' => 'ListChecks', 'route' => '/dashboard/opd/queue', 'permission' => 'appointment.view', 'order' => 1],
                    ['label' => 'Vitals Entry', 'icon' => 'HeartPulse', 'route' => '/dashboard/opd/vitals', 'permission' => 'consultation.create', 'order' => 2],
                    ['label' => 'Consultations', 'icon' => 'NotebookPen', 'route' => '/dashboard/opd/consultations', 'permission' => 'consultation.create', 'order' => 3],
                    ['label' => 'Prescriptions', 'icon' => 'PillBottle', 'route' => '/dashboard/opd/prescriptions', 'permission' => 'prescription.create', 'order' => 4],
                    ['label' => 'Investigations', 'icon' => 'Microscope', 'route' => '/dashboard/opd/investigations', 'permission' => 'investigation.create', 'order' => 5],
                ],
            ],
            [
                'label' => 'IPD',
                'icon' => 'BedDouble',
                'route' => '/dashboard/ipd',
                'permission' => 'patient.view',
                'order' => 12,
                'children' => [
                    ['label' => 'Admissions', 'icon' => 'Hospital', 'route' => '/dashboard/ipd/admissions', 'permission' => 'patient.view', 'order' => 1],
                    ['label' => 'Bed Matrix', 'icon' => 'Grid2X2', 'route' => '/dashboard/ipd/beds', 'permission' => 'patient.view', 'order' => 2],
                    ['label' => 'Ward Rounds', 'icon' => 'ClipboardPlus', 'route' => '/dashboard/ipd/ward-rounds', 'permission' => 'patient.view', 'order' => 3],
                    ['label' => 'Discharge', 'icon' => 'LogOut', 'route' => '/dashboard/ipd/discharge', 'permission' => 'billing.manage', 'order' => 4],
                ],
            ],
            [
                'label' => 'Emergency',
                'icon' => 'Siren',
                'route' => '/dashboard/emergency',
                'permission' => 'patient.view',
                'order' => 13,
                'children' => [
                    ['label' => 'Triage Board', 'icon' => 'AlarmClockCheck', 'route' => '/dashboard/emergency/triage', 'permission' => 'patient.view', 'order' => 1],
                    ['label' => 'Rapid Registration', 'icon' => 'UserPlus2', 'route' => '/dashboard/emergency/register', 'permission' => 'patient.create', 'order' => 2],
                    ['label' => 'Resuscitation Log', 'icon' => 'HeartCrack', 'route' => '/dashboard/emergency/resuscitation', 'permission' => 'patient.update', 'order' => 3],
                ],
            ],
            [
                'label' => 'Insurance',
                'icon' => 'ShieldEllipsis',
                'route' => '/dashboard/insurance',
                'permission' => 'billing.view',
                'order' => 14,
                'children' => [
                    ['label' => 'Providers', 'icon' => 'Building', 'route' => '/dashboard/insurance/providers', 'permission' => 'billing.view', 'order' => 1],
                    ['label' => 'Policies', 'icon' => 'FileBadge', 'route' => '/dashboard/insurance/policies', 'permission' => 'billing.view', 'order' => 2],
                    ['label' => 'Claims', 'icon' => 'FileStack', 'route' => '/dashboard/insurance/claims', 'permission' => 'billing.manage', 'order' => 3],
                ],
            ],
            [
                'label' => 'Blood Bank',
                'icon' => 'Droplets',
                'route' => '/dashboard/blood-bank',
                'permission' => 'patient.view',
                'order' => 15,
                'children' => [
                    ['label' => 'Blood Stock', 'icon' => 'Droplet', 'route' => '/dashboard/blood-bank/stock', 'permission' => 'patient.view', 'order' => 1],
                    ['label' => 'Donations', 'icon' => 'HandHeart', 'route' => '/dashboard/blood-bank/donations', 'permission' => 'patient.view', 'order' => 2],
                    ['label' => 'Transfusions', 'icon' => 'Activity', 'route' => '/dashboard/blood-bank/transfusions', 'permission' => 'patient.view', 'order' => 3],
                ],
            ],
            [
                'label' => 'Mortuary',
                'icon' => 'Archive',
                'route' => '/dashboard/mortuary',
                'permission' => 'patient.view',
                'order' => 16,
                'children' => [
                    ['label' => 'Records', 'icon' => 'FolderSearch', 'route' => '/dashboard/mortuary/records', 'permission' => 'patient.view', 'order' => 1],
                    ['label' => 'Release Requests', 'icon' => 'FileCheck2', 'route' => '/dashboard/mortuary/releases', 'permission' => 'patient.view', 'order' => 2],
                ],
            ],
            [
                'label' => 'SaaS Admin',
                'icon' => 'Crown',
                'route' => '/dashboard/saas',
                'permission' => 'super-admin.manage-tenants',
                'order' => 17,
                'children' => [
                    ['label' => 'Tenant Management', 'icon' => 'Building2', 'route' => '/dashboard/saas/tenants', 'permission' => 'super-admin.manage-tenants', 'order' => 1],
                    ['label' => 'Plans & Billing', 'icon' => 'CreditCard', 'route' => '/dashboard/saas/plans', 'permission' => 'super-admin.manage-tenants', 'order' => 2],
                    ['label' => 'Platform Analytics', 'icon' => 'LineChart', 'route' => '/dashboard/saas/analytics', 'permission' => 'super-admin.manage-tenants', 'order' => 3],
                ],
            ],
            [
                'label' => 'Operations',
                'icon' => 'Workflow',
                'route' => '/dashboard/operations',
                'permission' => 'dashboard.view',
                'order' => 18,
                'children' => [
                    ['label' => 'Ward Management', 'icon' => 'Building2', 'route' => '/dashboard/operations/wards', 'permission' => 'dashboard.view', 'order' => 1],
                    ['label' => 'Ambulance Dispatch', 'icon' => 'Ambulance', 'route' => '/dashboard/operations/ambulance', 'permission' => 'dashboard.view', 'order' => 2],
                ],
            ],
            [
                'label' => 'Integrations',
                'icon' => 'PlugZap',
                'route' => '/dashboard/integrations',
                'permission' => 'integration.api',
                'order' => 19,
                'children' => [
                    ['label' => 'API Keys', 'icon' => 'KeyRound', 'route' => '/dashboard/integrations/api-keys', 'permission' => 'integration.api', 'order' => 1],
                    ['label' => 'Webhooks', 'icon' => 'Webhook', 'route' => '/dashboard/integrations/webhooks', 'permission' => 'integration.api', 'order' => 2],
                ],
            ],
            [
                'label' => 'AI Assistant',
                'icon' => 'Bot',
                'route' => '/dashboard/ai-assistant',
                'permission' => 'ai.assist',
                'order' => 20,
                'children' => [
                    ['label' => 'Assistant Console', 'icon' => 'MessageSquare', 'route' => '/dashboard/ai-assistant', 'permission' => 'ai.assist', 'order' => 1],
                    ['label' => 'Escalations', 'icon' => 'MessagesSquare', 'route' => '/dashboard/ai-assistant/escalations', 'permission' => 'ai.assist', 'order' => 2],
                ],
            ],
            [
                'label' => 'Security & Audit',
                'icon' => 'ShieldCheck',
                'route' => '/dashboard/security',
                'permission' => 'audit.view',
                'order' => 21,
                'children' => [
                    ['label' => 'Audit Trails', 'icon' => 'History', 'route' => '/dashboard/security/audit', 'permission' => 'audit.view', 'order' => 1],
                    ['label' => 'Access Logs', 'icon' => 'LockKeyhole', 'route' => '/dashboard/security/access-logs', 'permission' => 'audit.view', 'order' => 2],
                ],
            ],
            [
                'label' => 'Settings',
                'icon' => 'Settings',
                'route' => '/dashboard/settings',
                'permission' => 'dashboard.view',
                'order' => 99,
                'children' => [
                    ['label' => 'Profile Settings', 'icon' => 'UserCog', 'route' => '/dashboard/settings/profile', 'permission' => 'dashboard.view', 'order' => 1],
                    ['label' => 'Session Management', 'icon' => 'MonitorSmartphone', 'route' => '/dashboard/settings/sessions', 'permission' => 'auth.view-sessions', 'order' => 2],
                ],
            ],
        ];

        foreach ($menuTree as $item) {
            $parent = Menu::query()->create([
                'label' => $item['label'],
                'icon' => $item['icon'],
                'route' => $item['route'],
                'permission' => $item['permission'] ?? null,
                'order' => $item['order'] ?? 0,
                'is_active' => true,
            ]);

            foreach ($item['children'] ?? [] as $child) {
                Menu::query()->create([
                    'label' => $child['label'],
                    'icon' => $child['icon'] ?? null,
                    'route' => $child['route'] ?? null,
                    'permission' => $child['permission'] ?? null,
                    'parent_id' => $parent->id,
                    'order' => $child['order'] ?? 0,
                    'is_active' => true,
                ]);
            }
        }
    }
}
