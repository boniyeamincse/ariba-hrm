<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->longText('prescription_template')->nullable();
            $table->longText('invoice_template')->nullable();
            $table->longText('lab_report_template')->nullable();
            $table->longText('discharge_summary_template')->nullable();
            $table->longText('sick_leave_template')->nullable();
            $table->longText('consent_form_template')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_templates');
    }
};
