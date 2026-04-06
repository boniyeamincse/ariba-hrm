<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_clinicals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('uhid_prefix', 20)->default('UHID');
            $table->string('opd_prefix', 20)->default('OPD');
            $table->string('ipd_prefix', 20)->default('IPD');
            $table->string('prescription_prefix', 20)->default('RX');
            $table->string('lab_order_prefix', 20)->default('LAB');
            $table->string('radiology_order_prefix', 20)->default('RAD');
            $table->boolean('enable_eprescription')->default(false);
            $table->boolean('enable_clinical_notes_template')->default(true);
            $table->boolean('enable_icd10')->default(true);
            $table->boolean('enable_followup_reminder')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_clinicals');
    }
};
