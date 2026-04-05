<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('max_patients')->default(1);
            $table->unsignedInteger('booked_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('appointment_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_at');
            $table->string('status', 30)->default('scheduled');
            $table->string('visit_mode', 30)->default('in_person');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('telemedicine_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40)->default('jitsi');
            $table->string('meeting_id');
            $table->string('meeting_url');
            $table->string('status', 30)->default('created');
            $table->timestamps();
        });

        Schema::create('insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->text('contact_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_provider_id')->constrained()->cascadeOnDelete();
            $table->string('policy_no');
            $table->decimal('coverage_limit', 12, 2)->default(0);
            $table->decimal('used_amount', 12, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->unique(['insurance_provider_id', 'policy_no']);
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('insurance_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_no')->unique();
            $table->decimal('claim_amount', 12, 2)->default(0);
            $table->decimal('approved_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('submitted');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 60);
            $table->string('name');
            $table->string('category', 80)->nullable();
            $table->unsignedInteger('stock_on_hand')->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'sku']);
        });

        Schema::create('procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('po_no')->unique();
            $table->string('supplier_name');
            $table->string('status', 30)->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('department', 80)->nullable();
            $table->string('designation', 80)->nullable();
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->date('joined_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period', 20);
            $table->string('status', 30)->default('draft');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'period']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('allowance', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('blood_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('blood_group', 5);
            $table->unsignedInteger('units_available')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'blood_group']);
        });

        Schema::create('blood_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('donor_name');
            $table->string('blood_group', 5);
            $table->unsignedInteger('units');
            $table->date('donated_on');
            $table->timestamps();
        });

        Schema::create('blood_transfusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group', 5);
            $table->unsignedInteger('units');
            $table->timestamp('transfused_at');
            $table->timestamps();
        });

        Schema::create('mortuary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deceased_name');
            $table->string('cause_of_death')->nullable();
            $table->timestamp('time_of_death');
            $table->string('status', 30)->default('received');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mortuary_records');
        Schema::dropIfExists('blood_transfusions');
        Schema::dropIfExists('blood_donations');
        Schema::dropIfExists('blood_products');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('procurement_order_items');
        Schema::dropIfExists('procurement_orders');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('insurance_providers');
        Schema::dropIfExists('telemedicine_sessions');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_slots');
    }
};
