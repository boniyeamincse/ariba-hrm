<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_generals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('hospital_name');
            $table->string('hospital_code', 50);
            $table->string('registration_no', 100)->nullable();
            $table->string('license_no', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('timezone', 100)->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->string('language', 10)->default('en');
            $table->string('date_format', 20)->default('YYYY-MM-DD');
            $table->string('time_format', 20)->default('HH:mm:ss');
            $table->text('logo_url')->nullable();
            $table->text('favicon_url')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_generals');
    }
};
