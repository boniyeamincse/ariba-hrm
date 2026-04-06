<?php

namespace Database\Seeders;

use App\Models\PasswordHistory;
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

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        $role = Role::where('name', 'super-admin')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        PasswordHistory::query()->create([
            'user_id' => $user->id,
            'password_hash' => $user->password,
            'created_at' => now(),
        ]);
    }
}
