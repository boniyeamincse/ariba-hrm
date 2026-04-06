<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_pharmacies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('dispense_prefix', 20)->default('DISP');
            $table->boolean('enable_batch_tracking')->default(true);
            $table->boolean('enable_expiry_alert')->default(true);
            $table->string('low_stock_threshold_mode', 20)->default('percentage');
            $table->boolean('allow_partial_dispense')->default(true);
            $table->boolean('enforce_fefo')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_pharmacies');
    }
};
