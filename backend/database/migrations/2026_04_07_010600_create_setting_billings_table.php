<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('invoice_prefix', 20)->default('INV');
            $table->string('receipt_prefix', 20)->default('RCP');
            $table->string('estimate_prefix', 20)->default('EST');
            $table->string('refund_prefix', 20)->default('REF');
            $table->string('tax_name', 50)->default('GST');
            $table->decimal('tax_percentage', 5, 2)->default(18.00);
            $table->text('invoice_footer')->nullable();
            $table->boolean('auto_generate_invoice_number')->default(true);
            $table->boolean('allow_manual_discount')->default(false);
            $table->boolean('require_discount_approval')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_billings');
    }
};
