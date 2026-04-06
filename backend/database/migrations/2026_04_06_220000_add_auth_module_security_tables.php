<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('email');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('password');
            $table->boolean('is_2fa_enabled')->default(false)->after('two_factor_enabled');
            $table->string('otp_code', 16)->nullable()->after('is_2fa_enabled');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->timestamp('last_login_at')->nullable()->after('lockout_until');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });

        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_name', 191)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->string('token', 64)->unique();
            $table->timestamps();

            $table->index(['user_id', 'last_active_at']);
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip_address', 45)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamps();

            $table->unique(['email', 'ip_address']);
        });

        Schema::create('auth_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 64);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_logs');
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('auth_sessions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'status', 'last_login_at', 'last_login_ip', 'is_2fa_enabled', 'otp_code', 'otp_expires_at']);
        });
    }
};
