<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile')->index();
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });

        Schema::create('settlement_periods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('upcoming');
            $table->json('prize_distribution_json')->nullable();
            $table->boolean('referral_enabled')->default(false);
            $table->decimal('referral_rate', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name_fa');
            $table->string('name_en')->nullable();
            $table->string('flag_emoji')->nullable();
            $table->string('group_name')->nullable();
            $table->timestamps();
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('match_number')->nullable();
            $table->string('external_fixture_id')->nullable();
            $table->foreignId('period_id')->nullable()->constrained('settlement_periods')->nullOnDelete();
            $table->string('stage');
            $table->string('group_name')->nullable();
            $table->foreignId('home_team_id')->constrained('teams');
            $table->foreignId('away_team_id')->constrained('teams');
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('starts_at');
            $table->timestamp('prediction_locks_at');
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('settlement_periods')->nullOnDelete();
            $table->unsignedBigInteger('entry_amount')->default(0);
            $table->unsignedBigInteger('gateway_fee_amount')->default(0);
            $table->unsignedBigInteger('payable_amount')->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('prediction_status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_entries');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('settlement_periods');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('otp_codes');
    }
};
