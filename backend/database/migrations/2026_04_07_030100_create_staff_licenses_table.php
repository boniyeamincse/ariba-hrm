<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('license_type', 100);
            $table->string('license_number', 100);
            $table->string('issuing_authority', 150)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            $table->index(['tenant_id', 'staff_id']);
            $table->index(['tenant_id', 'expires_at']);
            $table->unique(['tenant_id', 'license_type', 'license_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_licenses');
    }
};
