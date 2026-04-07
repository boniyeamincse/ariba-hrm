<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('department_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');

            $table->unique(['facility_id', 'department_id']);
            $table->index(['tenant_id', 'department_id']);
            $table->index(['tenant_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_department');
    }
};
