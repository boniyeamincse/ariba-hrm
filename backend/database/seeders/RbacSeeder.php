<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Define core system roles
        $roles = [
            [
                'name' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Complete access to all system functions',
                'is_system' => true,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'tenant-admin',
                'display_name' => 'Tenant Administrator',
                'description' => 'Full access to tenant management and configuration',
                'is_system' => true,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'hospital-admin',
                'display_name' => 'Hospital Administrator',
                'description' => 'Administrative access to hospital operations',
                'is_system' => false,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'doctor',
                'display_name' => 'Doctor',
                'description' => 'Clinical access for patient consultations and records',
                'is_system' => false,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'nurse',
                'display_name' => 'Nurse',
                'description' => 'Nursing and patient care operations',
                'is_system' => false,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'receptionist',
                'display_name' => 'Receptionist',
                'description' => 'Front-desk and appointment management',
                'is_system' => false,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'pharmacist',
                'display_name' => 'Pharmacist',
                'description' => 'Pharmacy operations and medication management',
                'is_system' => false,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'lab-technician',
                'display_name' => 'Lab Technician',
                'description' => 'Laboratory testing and results management',
                'is_system' => false,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                array_merge($roleData, ['guard_name' => 'web'])
            );
        }

        // Define permission groups
        $groups = [
            ['key' => 'rbac', 'name' => 'RBAC Management', 'sort_order' => 1],
            ['key' => 'auth', 'name' => 'Authentication', 'sort_order' => 2],
            ['key' => 'patient', 'name' => 'Patient Management', 'sort_order' => 3],
            ['key' => 'appointment', 'name' => 'Appointments', 'sort_order' => 4],
            ['key' => 'consultation', 'name' => 'Consultations', 'sort_order' => 5],
            ['key' => 'prescription', 'name' => 'Prescriptions', 'sort_order' => 6],
            ['key' => 'pharmacy', 'name' => 'Pharmacy', 'sort_order' => 7],
            ['key' => 'lab', 'name' => 'Laboratory', 'sort_order' => 8],
            ['key' => 'billing', 'name' => 'Billing', 'sort_order' => 9],
            ['key' => 'settings', 'name' => 'Settings', 'sort_order' => 10],
            ['key' => 'dashboard', 'name' => 'Dashboard', 'sort_order' => 11],
            ['key' => 'users', 'name' => 'User Management', 'sort_order' => 12],
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(
                ['key' => $group['key']],
                $group
            );
        }

        // Define core system permissions (RBAC module)
        $rbacPermissions = [
            ['name' => 'rbac:view_roles', 'display_name' => 'View Roles', 'module_key' => 'rbac', 'description' => 'View role list and details', 'is_system' => true],
            ['name' => 'rbac:create_role', 'display_name' => 'Create Role', 'module_key' => 'rbac', 'description' => 'Create new roles', 'is_system' => true],
            ['name' => 'rbac:update_role', 'display_name' => 'Update Role', 'module_key' => 'rbac', 'description' => 'Edit existing roles', 'is_system' => true],
            ['name' => 'rbac:delete_role', 'display_name' => 'Delete Role', 'module_key' => 'rbac', 'description' => 'Delete roles', 'is_system' => true],
            ['name' => 'rbac:view_permissions', 'display_name' => 'View Permissions', 'module_key' => 'rbac', 'description' => 'View permission list', 'is_system' => true],
            ['name' => 'rbac:create_permission', 'display_name' => 'Create Permission', 'module_key' => 'rbac', 'description' => 'Create new permissions', 'is_system' => true],
            ['name' => 'rbac:update_permission', 'display_name' => 'Update Permission', 'module_key' => 'rbac', 'description' => 'Edit permissions', 'is_system' => true],
            ['name' => 'rbac:delete_permission', 'display_name' => 'Delete Permission', 'module_key' => 'rbac', 'description' => 'Delete permissions', 'is_system' => true],
            ['name' => 'rbac:sync_permissions', 'display_name' => 'Sync Permissions', 'module_key' => 'rbac', 'description' => 'Sync role permissions', 'is_system' => true],
            ['name' => 'rbac:assign_role', 'display_name' => 'Assign Roles', 'module_key' => 'rbac', 'description' => 'Assign roles to users', 'is_system' => true],
            ['name' => 'rbac:manage_groups', 'display_name' => 'Manage Groups', 'module_key' => 'rbac', 'description' => 'Manage permission groups', 'is_system' => true],
            ['name' => 'rbac:view_matrix', 'display_name' => 'View Matrix', 'module_key' => 'rbac', 'description' => 'View RBAC matrix', 'is_system' => true],
        ];

        // Auth module permissions
        $authPermissions = [
            ['name' => 'auth:login', 'display_name' => 'Login', 'module_key' => 'auth', 'description' => 'User login access', 'is_system' => true],
            ['name' => 'auth:session.manage', 'display_name' => 'Manage Sessions', 'module_key' => 'auth', 'description' => 'Manage user sessions', 'is_system' => true],
            ['name' => 'auth:2fa.manage', 'display_name' => 'Manage 2FA', 'module_key' => 'auth', 'description' => 'Manage two-factor authentication', 'is_system' => true],
        ];

        // Patient module permissions
        $patientPermissions = [
            ['name' => 'patient.view', 'display_name' => 'View Patients', 'module_key' => 'patient', 'description' => 'View patient records', 'is_system' => false],
            ['name' => 'patient.create', 'display_name' => 'Create Patient', 'module_key' => 'patient', 'description' => 'Register new patients', 'is_system' => false],
            ['name' => 'patient.update', 'display_name' => 'Update Patient', 'module_key' => 'patient', 'description' => 'Edit patient records', 'is_system' => false],
        ];

        // Appointment permissions
        $appointmentPermissions = [
            ['name' => 'appointment.view', 'display_name' => 'View Appointments', 'module_key' => 'appointment', 'description' => 'View appointment schedules', 'is_system' => false],
            ['name' => 'appointment.manage', 'display_name' => 'Manage Appointments', 'module_key' => 'appointment', 'description' => 'Create and manage appointments', 'is_system' => false],
        ];

        // Consultation permissions
        $consultationPermissions = [
            ['name' => 'consultation.view', 'display_name' => 'View Consultations', 'module_key' => 'consultation', 'description' => 'View consultation records', 'is_system' => false],
            ['name' => 'consultation.create', 'display_name' => 'Create Consultation', 'module_key' => 'consultation', 'description' => 'Create consultations', 'is_system' => false],
        ];

        // Prescription permissions
        $prescriptionPermissions = [
            ['name' => 'prescription.view', 'display_name' => 'View Prescriptions', 'module_key' => 'prescription', 'description' => 'View prescriptions', 'is_system' => false],
            ['name' => 'prescription.create', 'display_name' => 'Create Prescription', 'module_key' => 'prescription', 'description' => 'Create prescriptions', 'is_system' => false],
        ];

        // Pharmacy permissions
        $pharmacyPermissions = [
            ['name' => 'pharmacy.view', 'display_name' => 'View Pharmacy', 'module_key' => 'pharmacy', 'description' => 'View pharmacy stock', 'is_system' => false],
            ['name' => 'pharmacy.manage', 'display_name' => 'Manage Pharmacy', 'module_key' => 'pharmacy', 'description' => 'Manage pharmacy operations', 'is_system' => false],
        ];

        // Lab permissions
        $labPermissions = [
            ['name' => 'lab.view', 'display_name' => 'View Lab', 'module_key' => 'lab', 'description' => 'View lab tests and results', 'is_system' => false],
            ['name' => 'lab.manage', 'display_name' => 'Manage Lab', 'module_key' => 'lab', 'description' => 'Manage lab operations', 'is_system' => false],
        ];

        // Billing permissions
        $billingPermissions = [
            ['name' => 'billing.view', 'display_name' => 'View Billing', 'module_key' => 'billing', 'description' => 'View billing records', 'is_system' => false],
            ['name' => 'billing.manage', 'display_name' => 'Manage Billing', 'module_key' => 'billing', 'description' => 'Manage billing operations', 'is_system' => false],
        ];

        // Settings permissions
        $settingsPermissions = [
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'module_key' => 'settings', 'description' => 'View system settings', 'is_system' => true],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'module_key' => 'settings', 'description' => 'Manage system settings', 'is_system' => true],
        ];

        // Dashboard permissions
        $dashboardPermissions = [
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module_key' => 'dashboard', 'description' => 'Access dashboard', 'is_system' => true],
        ];

        // Users permissions
        $usersPermissions = [
            ['name' => 'users.view', 'display_name' => 'View Users', 'module_key' => 'users', 'description' => 'View user list', 'is_system' => true],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'module_key' => 'users', 'description' => 'Manage user accounts', 'is_system' => true],
        ];

        // Merge all permissions
        $allPermissions = array_merge(
            $rbacPermissions,
            $authPermissions,
            $patientPermissions,
            $appointmentPermissions,
            $consultationPermissions,
            $prescriptionPermissions,
            $pharmacyPermissions,
            $labPermissions,
            $billingPermissions,
            $settingsPermissions,
            $dashboardPermissions,
            $usersPermissions
        );

        // Create permissions
        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                array_merge($perm, ['guard_name' => 'web'])
            );
        }

        // Assign permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
        }

        // Assign permissions to tenant-admin role
        $tenantAdminRole = Role::where('name', 'tenant-admin')->first();
        if ($tenantAdminRole) {
            $tenantAdminPerms = Permission::whereIn('name', [
                'rbac:view_roles',
                'rbac:create_role',
                'rbac:update_role',
                'rbac:delete_role',
                'rbac:view_permissions',
                'rbac:assign_role',
                'rbac:view_matrix',
                'auth:session.manage',
                'patient.view',
                'patient.create',
                'patient.update',
                'appointment.view',
                'appointment.manage',
                'consultation.view',
                'consultation.create',
                'prescription.view',
                'prescription.create',
                'pharmacy.manage',
                'lab.manage',
                'billing.manage',
                'settings.view',
                'settings.manage',
                'dashboard.view',
                'users.view',
                'users.manage',
            ])->get();
            $tenantAdminRole->syncPermissions($tenantAdminPerms);
        }

        // Assign permissions to doctor role
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorPerms = Permission::whereIn('name', [
                'patient.view',
                'patient.update',
                'appointment.view',
                'consultation.view',
                'consultation.create',
                'prescription.create',
                'dashboard.view',
                'auth:session.manage',
                'auth:2fa.manage',
            ])->get();
            $doctorRole->syncPermissions($doctorPerms);
        }

        // Assign permissions to nurse role
        $nurseRole = Role::where('name', 'nurse')->first();
        if ($nurseRole) {
            $nursePerms = Permission::whereIn('name', [
                'patient.view',
                'patient.update',
                'appointment.view',
                'consultation.view',
                'dashboard.view',
                'auth:session.manage',
            ])->get();
            $nurseRole->syncPermissions($nursePerms);
        }

        // Assign permissions to receptionist role
        $receptionistRole = Role::where('name', 'receptionist')->first();
        if ($receptionistRole) {
            $receptionistPerms = Permission::whereIn('name', [
                'patient.view',
                'patient.create',
                'appointment.view',
                'appointment.manage',
                'billing.view',
                'dashboard.view',
                'auth:session.manage',
            ])->get();
            $receptionistRole->syncPermissions($receptionistPerms);
        }

        // Assign permissions to pharmacist role
        $pharmacistRole = Role::where('name', 'pharmacist')->first();
        if ($pharmacistRole) {
            $pharmacistPerms = Permission::whereIn('name', [
                'patient.view',
                'prescription.view',
                'pharmacy.view',
                'pharmacy.manage',
                'dashboard.view',
                'auth:session.manage',
            ])->get();
            $pharmacistRole->syncPermissions($pharmacistPerms);
        }

        // Assign permissions to lab-technician role
        $labRole = Role::where('name', 'lab-technician')->first();
        if ($labRole) {
            $labPerms = Permission::whereIn('name', [
                'patient.view',
                'lab.view',
                'lab.manage',
                'consultation.view',
                'dashboard.view',
                'auth:session.manage',
            ])->get();
            $labRole->syncPermissions($labPerms);
        }
    }
}
