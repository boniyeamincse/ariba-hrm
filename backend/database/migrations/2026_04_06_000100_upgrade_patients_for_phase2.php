<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique('patients_uhid_unique');

            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('national_id_no', 40)->nullable()->after('email');
            $table->string('passport_no', 40)->nullable()->after('national_id_no');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->string('country', 100)->nullable()->after('postal_code');
            $table->string('marital_status', 30)->nullable()->after('country');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('religion', 50)->nullable()->after('occupation');
            $table->string('emergency_contact_name')->nullable()->after('religion');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_phone');
            $table->string('photo_path')->nullable()->after('emergency_contact_relation');
            $table->string('photo_thumb_path')->nullable()->after('photo_path');

            $table->unique(['tenant_id', 'uhid'], 'patients_tenant_uhid_unique');
            $table->index(['tenant_id', 'first_name', 'last_name'], 'patients_tenant_name_index');
            $table->index(['tenant_id', 'phone'], 'patients_tenant_phone_index');
            $table->index(['tenant_id', 'national_id_no'], 'patients_tenant_nid_index');
            $table->index(['tenant_id', 'date_of_birth', 'phone'], 'patients_dob_phone_index');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique('patients_tenant_uhid_unique');
            $table->dropIndex('patients_tenant_name_index');
            $table->dropIndex('patients_tenant_phone_index');
            $table->dropIndex('patients_tenant_nid_index');
            $table->dropIndex('patients_dob_phone_index');

            $table->dropColumn([
                'middle_name',
                'national_id_no',
                'passport_no',
                'city',
                'state',
                'postal_code',
                'country',
                'marital_status',
                'occupation',
                'religion',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
                'photo_path',
                'photo_thumb_path',
            ]);

            $table->unique('uhid');
        });
    }
};
