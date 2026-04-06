<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_email_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('mail_driver', 50)->default('smtp');
            $table->string('smtp_host', 255)->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_user', 255)->nullable();
            $table->longText('smtp_password')->nullable();
            $table->string('smtp_encryption', 20)->default('tls');
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_email_configs');
    }
};
