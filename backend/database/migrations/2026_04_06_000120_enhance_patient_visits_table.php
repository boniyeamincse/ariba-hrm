<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_visits', function (Blueprint $table): void {
            $table->text('summary')->nullable()->after('status');
            $table->json('meta')->nullable()->after('summary');

            $table->index(['tenant_id', 'patient_id', 'visit_at'], 'patient_visits_tenant_patient_visit_at_index');
            $table->index(['tenant_id', 'visit_type', 'visit_at'], 'patient_visits_tenant_type_visit_at_index');
            $table->index(['tenant_id', 'reference_no'], 'patient_visits_tenant_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('patient_visits', function (Blueprint $table): void {
            $table->dropIndex('patient_visits_tenant_patient_visit_at_index');
            $table->dropIndex('patient_visits_tenant_type_visit_at_index');
            $table->dropIndex('patient_visits_tenant_reference_index');

            $table->dropColumn(['summary', 'meta']);
        });
    }
};
