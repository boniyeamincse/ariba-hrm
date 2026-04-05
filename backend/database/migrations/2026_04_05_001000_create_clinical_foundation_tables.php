<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('uhid')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('surgical_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('immunization_records')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('visit_type', ['opd', 'ipd', 'emergency']);
            $table->string('reference_no')->nullable();
            $table->timestamp('visit_at');
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('opd_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->nullOnDelete();
            $table->unsignedInteger('token_no');
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('status', 30)->default('waiting');
            $table->timestamp('queued_at');
            $table->timestamps();
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opd_queue_id')->nullable()->constrained('opd_queues')->nullOnDelete();
            $table->text('complaint')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->string('diagnosis_code')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescribed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instructions')->nullable();
            $table->longText('printable_content')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->string('medicine_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->string('route')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('investigation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->enum('order_type', ['lab', 'radiology']);
            $table->string('test_name');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('ordered');
            $table->timestamps();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('bed_number');
            $table->string('bed_type', 40)->default('general');
            $table->boolean('is_occupied')->default(false);
            $table->timestamps();
            $table->unique(['ward_id', 'bed_number']);
        });

        Schema::create('ipd_admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->nullOnDelete();
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('admitted_at');
            $table->timestamp('discharged_at')->nullable();
            $table->string('status', 30)->default('admitted');
            $table->timestamps();
        });

        Schema::create('ward_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes');
            $table->timestamp('rounded_at');
            $table->timestamps();
        });

        Schema::create('nursing_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes');
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('medicine_name');
            $table->string('dose')->nullable();
            $table->string('route')->nullable();
            $table->timestamp('administered_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('emergency_triages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->nullOnDelete();
            $table->enum('triage_level', ['red', 'orange', 'yellow', 'green', 'blue']);
            $table->text('complaint')->nullable();
            $table->json('vitals')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_triages');
        Schema::dropIfExists('medication_administrations');
        Schema::dropIfExists('nursing_notes');
        Schema::dropIfExists('ward_rounds');
        Schema::dropIfExists('ipd_admissions');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('investigation_orders');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('opd_queues');
        Schema::dropIfExists('patient_visits');
        Schema::dropIfExists('patient_histories');
        Schema::dropIfExists('patients');
    }
};
