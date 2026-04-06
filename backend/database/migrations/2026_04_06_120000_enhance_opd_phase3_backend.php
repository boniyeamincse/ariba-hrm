<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('bp_systolic')->nullable();
            $table->unsignedSmallInteger('bp_diastolic')->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('spo2')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedTinyInteger('pain_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('referral_type', ['internal', 'external']);
            $table->string('target_department', 120)->nullable();
            $table->string('target_specialist', 120)->nullable();
            $table->string('external_facility', 180)->nullable();
            $table->text('reason')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->string('status', 30)->default('initiated');
            $table->string('letter_pdf_path')->nullable();
            $table->timestamp('letter_generated_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamps();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->text('cancel_reason')->nullable()->after('cancelled_at');
            $table->foreignId('rescheduled_from_id')->nullable()->after('appointment_slot_id')->constrained('appointments')->nullOnDelete();
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->text('subjective')->nullable()->after('opd_queue_id');
            $table->text('objective')->nullable()->after('subjective');
            $table->string('icd10_code', 30)->nullable()->after('diagnosis_code');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('printable_content');
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->foreignId('drug_id')->nullable()->after('prescription_id')->constrained()->nullOnDelete();
        });

        Schema::table('investigation_orders', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('consultation_id')->constrained()->nullOnDelete();
            $table->string('routed_module', 40)->nullable()->after('status');
            $table->string('routed_reference', 80)->nullable()->after('routed_module');
        });
    }

    public function down(): void
    {
        Schema::table('investigation_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropColumn(['routed_module', 'routed_reference']);
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('drug_id');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_generated_at']);
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['subjective', 'objective', 'icd10_code']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rescheduled_from_id');
            $table->dropColumn(['cancelled_at', 'cancel_reason']);
        });

        Schema::dropIfExists('referrals');
        Schema::dropIfExists('vitals');
    }
};
