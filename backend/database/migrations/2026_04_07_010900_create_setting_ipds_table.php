<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_ipds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('admission_prefix', 20)->default('ADM');
            $table->string('discharge_prefix', 20)->default('DIS');
            $table->string('bed_transfer_prefix', 20)->default('TRF');
            $table->boolean('enable_bed_reservation')->default(true);
            $table->boolean('allow_direct_admission')->default(false);
            $table->boolean('require_guarantor_info')->default(true);
            $table->boolean('enable_discharge_approval')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_ipds');
    }
};
