<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [
            'super-admin' => [
                'super-admin.manage-tenants',
                'auth.manage-users',
                'auth.manage-roles',
                'auth.view-sessions',
                'auth.revoke-sessions',
                'audit.view',
                'patient.create',
                'patient.view',
                'patient.update',
                'consultation.create',
                'prescription.create',
                'investigation.create',
                'billing.view',
                'billing.manage',
            ],
            'hospital-admin' => [
                'auth.manage-users',
                'auth.manage-roles',
                'auth.view-sessions',
                'auth.revoke-sessions',
                'audit.view',
                'patient.create',
                'patient.view',
                'patient.update',
                'consultation.create',
                'prescription.create',
                'investigation.create',
                'billing.view',
                'billing.manage',
            ],
            'doctor' => [
                'patient.view',
                'consultation.create',
                'prescription.create',
                'investigation.create',
            ],
            'nurse' => [
                'patient.view',
                'consultation.create',
            ],
            'receptionist' => [
                'patient.create',
                'patient.view',
                'billing.view',
            ],
            'pharmacist' => [
                'patient.view',
                'prescription.create',
                'billing.view',
            ],
            'lab-tech' => [
                'patient.view',
                'investigation.create',
            ],
            'accountant' => [
                'billing.view',
                'billing.manage',
            ],
            'patient' => [
                'patient.view',
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