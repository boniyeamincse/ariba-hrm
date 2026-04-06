<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PasswordHistory;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MenuSeeder::class,
        ]);

        $primaryHospitalTenant = Tenant::query()->firstOrCreate(
            ['subdomain' => 'hospital'],
            [
                'name' => 'Hospital Tenant',
                'database_name' => 'tenant_hospital',
                'status' => 'active',
            ]
        );

        $partnerTenant = Tenant::query()->firstOrCreate(
            ['subdomain' => 'partner'],
            [
                'name' => 'Insurance Partner Tenant',
                'database_name' => 'tenant_partner',
                'status' => 'active',
            ]
        );

        $roleIds = Role::query()
            ->pluck('id', 'name');

        $accounts = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@medcore.com',
                'tenant_id' => null,
                'roles' => ['super-admin'],
            ],
            [
                'name' => 'Hospital Admin',
                'email' => 'admin@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['tenant-admin', 'hospital-admin'],
            ],
            [
                'name' => 'Hospital Manager',
                'email' => 'manager@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['hospital-manager'],
            ],
            [
                'name' => 'Operations Manager',
                'email' => 'ops@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['operations-manager'],
            ],
            [
                'name' => 'Doctor User',
                'email' => 'doctor@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['doctor'],
            ],
            [
                'name' => 'Nurse User',
                'email' => 'nurse@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['nurse'],
            ],
            [
                'name' => 'Reception User',
                'email' => 'reception@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['receptionist'],
            ],
            [
                'name' => 'Pharmacist User',
                'email' => 'pharmacist@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['pharmacist'],
            ],
            [
                'name' => 'Lab Technician',
                'email' => 'lab@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['lab-technician'],
            ],
            [
                'name' => 'Finance Manager',
                'email' => 'finance@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['accountant'],
            ],
            [
                'name' => 'Ward Manager',
                'email' => 'wardmanager@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['ward-manager'],
            ],
            [
                'name' => 'Transport Staff',
                'email' => 'transport@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['ambulance-driver'],
            ],
            [
                'name' => 'IT Admin',
                'email' => 'itadmin@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['it-admin'],
            ],
            [
                'name' => 'Inventory Manager',
                'email' => 'inventory@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['inventory-manager'],
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['hr-manager'],
            ],
            [
                'name' => 'Patient Portal User',
                'email' => 'patient@example.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['patient'],
            ],
            [
                'name' => 'Insurance Partner User',
                'email' => 'insurance@partner.com',
                'tenant_id' => $partnerTenant->id,
                'roles' => ['insurance-agent'],
            ],
            [
                'name' => 'Compliance Auditor',
                'email' => 'auditor@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['auditor'],
            ],
            [
                'name' => 'Data Analyst',
                'email' => 'analyst@hospital.com',
                'tenant_id' => $primaryHospitalTenant->id,
                'roles' => ['data-analyst'],
            ],
            [
                'name' => 'API Integration Client',
                'email' => 'api@integration.local',
                'tenant_id' => null,
                'roles' => ['api-client'],
            ],
            [
                'name' => 'AI Assistant Agent',
                'email' => 'ai_agent@medcore.internal',
                'tenant_id' => null,
                'roles' => ['ai-assistant'],
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);
            $user->name = $account['name'];
            $user->tenant_id = $account['tenant_id'];
            $user->password = 'password';
            $user->password_changed_at = now();
            $user->password_expires_at = now()->addDays(90);
            $user->save();

            $assignedRoleIds = collect($account['roles'])
                ->map(fn (string $roleName) => $roleIds->get($roleName))
                ->filter()
                ->values();

            if ($assignedRoleIds->isNotEmpty()) {
                $user->roles()->sync($assignedRoleIds->all());
            }

            PasswordHistory::query()->firstOrCreate([
                'user_id' => $user->id,
                'password_hash' => $user->password,
            ], [
                'created_at' => now(),
            ]);
        }

        Patient::query()->updateOrCreate([
            'tenant_id' => $primaryHospitalTenant->id,
            'uhid' => 'PT-1001',
        ], [
            'first_name' => 'Portal',
            'last_name' => 'Patient',
            'email' => 'patient@example.com',
            'phone' => '+15550001001',
            'gender' => 'other',
            'date_of_birth' => '1995-01-01',
        ]);
    }
}
