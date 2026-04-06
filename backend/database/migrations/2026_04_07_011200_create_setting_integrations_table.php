<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->boolean('hl7_enabled')->default(false);
            $table->boolean('fhir_enabled')->default(false);
            $table->boolean('webhook_enabled')->default(false);
            $table->boolean('api_access_enabled')->default(true);
            $table->boolean('third_party_integration_enabled')->default(false);
            $table->boolean('pacs_enabled')->default(false);
            $table->boolean('payment_gateway_enabled')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_integrations');
    }
};
