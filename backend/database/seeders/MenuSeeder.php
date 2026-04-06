<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Top level items
        $dashboard = Menu::create([
            'label' => 'Overview',
            'icon' => 'LayoutDashboard',
            'route' => '/dashboard',
            'order' => 1,
        ]);

        $clinicalGroups = Menu::create([
            'label' => 'Clinical Hub',
            'icon' => 'Stethoscope',
            'order' => 2,
        ]);

        Menu::create([
            'label' => 'Outpatients (OPD)',
            'icon' => 'Users',
            'route' => '/dashboard/clinical/opd',
            'parent_id' => $clinicalGroups->id,
            'order' => 1,
        ]);

        Menu::create([
            'label' => 'Inpatients (IPD)',
            'icon' => 'Bed',
            'route' => '/dashboard/clinical/ipd',
            'parent_id' => $clinicalGroups->id,
            'order' => 2,
        ]);

        $tasks = Menu::create([
            'label' => 'Task Terminal',
            'icon' => 'CheckSquare',
            'route' => '/dashboard/tasks',
            'order' => 3,
        ]);

        $admin = Menu::create([
            'label' => 'System Control',
            'icon' => 'ShieldCheck',
            'permission' => 'super-admin.manage-tenants',
            'order' => 10,
        ]);

        Menu::create([
            'label' => 'Tenant Management',
            'icon' => 'Globe',
            'route' => '/dashboard/admin/tenants',
            'parent_id' => $admin->id,
            'order' => 1,
        ]);

        Menu::create([
            'label' => 'Audit Trails',
            'icon' => 'History',
            'route' => '/dashboard/admin/audit',
            'parent_id' => $admin->id,
            'permission' => 'audit.view',
            'order' => 2,
        ]);
        
        $settings = Menu::create([
            'label' => 'Settings',
            'icon' => 'Settings',
            'route' => '/dashboard/settings',
            'order' => 20,
        ]);
    }
}
