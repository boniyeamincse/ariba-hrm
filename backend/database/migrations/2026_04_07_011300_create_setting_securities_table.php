<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_securities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->unsignedInteger('password_min_length')->default(8);
            $table->boolean('password_require_uppercase')->default(true);
            $table->boolean('password_require_lowercase')->default(true);
            $table->boolean('password_require_number')->default(true);
            $table->boolean('password_require_special_char')->default(false);
            $table->unsignedInteger('password_expiry_days')->default(90);
            $table->unsignedInteger('login_attempt_limit')->default(5);
            $table->unsignedInteger('lockout_duration_minutes')->default(30);
            $table->boolean('mfa_enabled')->default(false);
            $table->unsignedInteger('session_timeout_minutes')->default(30);
            $table->json('ip_whitelist')->nullable();
            $table->boolean('trusted_devices_enabled')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_securities');
    }
};
