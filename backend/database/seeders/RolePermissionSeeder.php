<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modulePermissions = [
            'dashboard.view',
            'users.view',
            'users.manage',
            'patient.create',
            'patient.view',
            'patient.update',
            'appointment.view',
            'appointment.manage',
            'billing.view',
            'billing.manage',
            'inventory.view',
            'inventory.manage',
            'pharmacy.view',
            'pharmacy.manage',
            'lab.view',
            'lab.manage',
            'hr.view',
            'hr.manage',
            'reports.view',
            'reports.export',
            'audit.view',
            'consultation.create',
            'prescription.create',
            'investigation.create',
            'integration.api',
            'ai.assist',
            'auth.manage-users',
            'auth.manage-roles',
            'auth.view-sessions',
            'auth.revoke-sessions',
            'super-admin.manage-tenants',
        ];

        $rolePermissions = [
            'super-admin' => [
                ...$modulePermissions,
            ],
            'tenant-admin' => [
                'dashboard.view',
                'users.view',
                'users.manage',
                'patient.create',
                'patient.view',
                'patient.update',
                'appointment.view',
                'appointment.manage',
                'billing.view',
                'billing.manage',
                'inventory.view',
                'inventory.manage',
                'pharmacy.view',
                'pharmacy.manage',
                'lab.view',
                'lab.manage',
                'hr.view',
                'hr.manage',
                'reports.view',
                'reports.export',
                'auth.manage-users',
                'auth.manage-roles',
                'auth.view-sessions',
                'auth.revoke-sessions',
                'audit.view',
            ],
            'hospital-admin' => [
                'dashboard.view',
                'users.view',
                'users.manage',
                'patient.create',
                'patient.view',
                'patient.update',
                'appointment.view',
                'appointment.manage',
                'consultation.create',
                'prescription.create',
                'investigation.create',
                'billing.view',
                'billing.manage',
                'reports.view',
            ],
            'hospital-manager' => ['dashboard.view', 'patient.view', 'appointment.view', 'billing.view', 'reports.view'],
            'operations-manager' => ['dashboard.view', 'patient.view', 'appointment.view', 'inventory.view', 'reports.view'],
            'doctor' => [
                'dashboard.view',
                'patient.view',
                'appointment.view',
                'consultation.create',
                'prescription.create',
                'investigation.create',
                'reports.view',
            ],
            'nurse' => [
                'dashboard.view',
                'patient.view',
                'appointment.view',
                'consultation.create',
            ],
            'receptionist' => [
                'dashboard.view',
                'patient.create',
                'patient.view',
                'appointment.view',
                'appointment.manage',
                'billing.view',
            ],
            'pharmacist' => [
                'dashboard.view',
                'patient.view',
                'pharmacy.view',
                'pharmacy.manage',
                'prescription.create',
                'billing.view',
            ],
            'lab-technician' => [
                'dashboard.view',
                'patient.view',
                'lab.view',
                'lab.manage',
                'investigation.create',
            ],
            'lab-tech' => [
                'dashboard.view',
                'patient.view',
                'lab.view',
                'lab.manage',
                'investigation.create',
            ],
            'accountant' => [
                'dashboard.view',
                'billing.view',
                'billing.manage',
                'reports.view',
                'reports.export',
            ],
            'ward-manager' => [
                'dashboard.view',
                'patient.view',
                'appointment.view',
                'reports.view',
            ],
            'ambulance-driver' => [
                'dashboard.view',
                'patient.view',
            ],
            'it-admin' => [
                'dashboard.view',
                'users.view',
                'users.manage',
                'audit.view',
                'reports.view',
                'integration.api',
            ],
            'inventory-manager' => [
                'dashboard.view',
                'inventory.view',
                'inventory.manage',
                'reports.view',
            ],
            'hr-manager' => [
                'dashboard.view',
                'users.view',
                'users.manage',
                'hr.view',
                'hr.manage',
                'reports.view',
            ],
            'patient' => [
                'dashboard.view',
                'patient.view',
                'appointment.view',
                'billing.view',
            ],
            'insurance-agent' => [
                'dashboard.view',
                'patient.view',
                'billing.view',
                'reports.view',
            ],
            'auditor' => [
                'dashboard.view',
                'audit.view',
                'reports.view',
                'reports.export',
            ],
            'data-analyst' => [
                'dashboard.view',
                'reports.view',
                'reports.export',
            ],
            'api-client' => [
                'integration.api',
                'patient.view',
                'appointment.view',
            ],
            'ai-assistant' => [
                'dashboard.view',
                'patient.view',
                'appointment.view',
                'ai.assist',
            ],
        ];

        $permissions = collect($rolePermissions)
            ->flatten()
            ->unique()
            ->values();

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName]);
        }

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::query()->firstOrCreate(['name' => $roleName]);
            $permissionIds = Permission::query()
                ->whereIn('name', $permissionNames)
                ->pluck('id');

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}