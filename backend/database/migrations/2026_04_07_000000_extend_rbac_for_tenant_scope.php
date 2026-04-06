<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend roles table with tenant scope and metadata
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            }
            if (!Schema::hasColumn('roles', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('roles', 'description')) {
                $table->text('description')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('roles', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('description');
            }
            if (!Schema::hasColumn('roles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_system');
            }
            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_default');
            }
            if (!Schema::hasColumn('roles', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('roles', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }

            // Add indexes
            $table->index(['tenant_id', 'is_active'], 'roles_tenant_id_is_active_index');
        });

        // Extend permissions table with tenant scope and module key
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            }
            if (!Schema::hasColumn('permissions', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'module_key')) {
                $table->string('module_key')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('permissions', 'description')) {
                $table->text('description')->nullable()->after('module_key');
            }
            if (!Schema::hasColumn('permissions', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('description');
            }

            // Add indexes
            $table->index(['module_key', 'tenant_id'], 'permissions_module_key_tenant_id_index');
        });

        // Create permission groups table
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        // Create RBAC audit logs table
        Schema::create('rbac_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('target_type', 100);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rbac_audit_logs');
        Schema::dropIfExists('permission_groups');

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasColumn('permissions', 'display_name')) {
                $table->dropColumn('display_name');
            }
            if (Schema::hasColumn('permissions', 'module_key')) {
                $table->dropColumn('module_key');
            }
            if (Schema::hasColumn('permissions', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('permissions', 'is_system')) {
                $table->dropColumn('is_system');
            }
            if (Schema::hasIndex('permissions', 'permissions_module_key_tenant_id_index')) {
                $table->dropIndex('permissions_module_key_tenant_id_index');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('roles', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('roles', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('roles', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('roles', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('roles', 'is_system')) {
                $table->dropColumn('is_system');
            }
            if (Schema::hasColumn('roles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('roles', 'display_name')) {
                $table->dropColumn('display_name');
            }
            if (Schema::hasColumn('roles', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasIndex('roles', 'roles_tenant_id_is_active_index')) {
                $table->dropIndex('roles_tenant_id_is_active_index');
            }
        });
    }
};
