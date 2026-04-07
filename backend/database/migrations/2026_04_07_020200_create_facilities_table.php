<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('facility_type_id')->nullable();
            $table->string('code', 50);
            $table->string('name');
            $table->string('slug');
            $table->string('building_name', 150)->nullable();
            $table->string('floor_no', 30)->nullable();
            $table->string('wing', 100)->nullable();
            $table->string('room_prefix', 20)->nullable();
            $table->string('service_point_type', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('facility_type_id')->references('id')->on('facility_types')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['tenant_id', 'slug']);
            $table->unique(['branch_id', 'code']);
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['tenant_id', 'facility_type_id']);
            $table->index(['tenant_id', 'building_name']);
            $table->index(['tenant_id', 'floor_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
