<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operation_theaters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type', 60)->nullable(); // e.g., General, Cardiac
            $table->string('status', 30)->default('available'); // available, occupied, maintenance
            $table->timestamps();
        });

        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('category', 60)->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('surgery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_visit_id')->nullable()->constrained('patient_visits')->nullOnDelete();
            $table->foreignId('operation_theater_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surgery_id')->constrained()->cascadeOnDelete();
            
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            
            $table->string('status', 30)->default('scheduled'); // scheduled, in_progress, recovery, completed, cancelled
            
            $table->foreignId('primary_surgeon_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anesthesiologist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('anesthesia_type')->nullable(); // general, local, regional, etc.
            
            $table->text('pre_op_notes')->nullable();
            $table->text('surgery_notes')->nullable(); // post operative details/notes about the procedure
            
            $table->timestamps();
        });

        Schema::create('surgery_recovery_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('surgery_schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('recovery_room_no', 30)->nullable();
            $table->text('vitals_summary')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->timestamp('cleared_for_ward_at')->nullable();
            $table->foreignId('nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgery_recovery_records');
        Schema::dropIfExists('surgery_schedules');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('operation_theaters');
    }
};
