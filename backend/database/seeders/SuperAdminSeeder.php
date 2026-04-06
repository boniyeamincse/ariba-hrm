<?php

namespace Database\Seeders;

use App\Models\PasswordHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrNew(['email' => 'superadmin@medcore.com']);
        $user->name = 'Super Admin';
        $user->tenant_id = null;
        $user->password = 'password';
        $user->password_changed_at = now();
        $user->password_expires_at = now()->addDays(90);
        $user->save();

        $superAdminRole = Role::query()->where('name', 'super-admin')->first();

        if ($superAdminRole) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        PasswordHistory::query()->firstOrCreate(
            ['user_id' => $user->id, 'password_hash' => $user->password],
            ['created_at' => now()]
        );

        $this->command->info('Super admin seeded: superadmin@medcore.com / password');
    }
}
