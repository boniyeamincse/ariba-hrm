<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_localizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('default_language', 10)->default('en');
            $table->json('supported_languages')->default(json_encode(['en']));
            $table->string('timezone', 100)->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->string('number_format', 20)->default('1,000.00');
            $table->string('date_format', 20)->default('YYYY-MM-DD');
            $table->string('time_format', 20)->default('HH:mm:ss');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_localizations');
    }
};
