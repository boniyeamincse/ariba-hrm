<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->unsignedInteger('default_slot_duration')->default(30);
            $table->unsignedInteger('max_patients_per_slot')->default(3);
            $table->boolean('allow_overbooking')->default(false);
            $table->unsignedInteger('overbooking_limit')->default(0);
            $table->unsignedInteger('booking_lead_days')->default(30);
            $table->unsignedInteger('cancellation_window_hours')->default(24);
            $table->boolean('auto_confirm_appointments')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_appointments');
    }
};
