<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('sms_notifications_enabled')->default(false);
            $table->boolean('push_notifications_enabled')->default(false);
            $table->boolean('whatsapp_notifications_enabled')->default(false);
            $table->boolean('appointment_reminder_enabled')->default(true);
            $table->boolean('billing_alert_enabled')->default(true);
            $table->boolean('lab_result_notification_enabled')->default(true);
            $table->boolean('discharge_notification_enabled')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_notifications');
    }
};
