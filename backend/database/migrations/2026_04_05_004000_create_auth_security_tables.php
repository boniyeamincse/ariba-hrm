<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('two_factor_expires_at');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->string('two_factor_challenge_token')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('two_factor_challenge_expires_at')->nullable()->after('two_factor_challenge_token');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('two_factor_challenge_expires_at');
            $table->timestamp('lockout_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('password_changed_at')->nullable()->after('lockout_until');
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');
        });

        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password_hash');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_confirmed_at',
                'two_factor_challenge_token',
                'two_factor_challenge_expires_at',
                'failed_login_attempts',
                'lockout_until',
                'password_changed_at',
                'password_expires_at',
            ]);
        });
    }
};