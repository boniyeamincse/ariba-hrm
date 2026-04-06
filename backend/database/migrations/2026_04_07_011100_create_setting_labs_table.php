<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('sample_prefix', 20)->default('SAM');
            $table->string('report_prefix', 20)->default('REP');
            $table->boolean('barcode_enabled')->default(true);
            $table->boolean('qr_report_enabled')->default(true);
            $table->boolean('critical_alert_enabled')->default(true);
            $table->boolean('result_approval_required')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_labs');
    }
};
