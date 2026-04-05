<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'role_id']);
        });

        DB::table('roles')->insert([
            ['name' => 'super-admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'hospital-admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'doctor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'nurse', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('permissions')->insert([
            ['name' => 'super-admin.manage-tenants', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'auth.manage-users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'audit.view', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $superAdminRoleId = DB::table('roles')->where('name', 'super-admin')->value('id');
        $permissionIds = DB::table('permissions')->pluck('id')->all();

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $superAdminRoleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
