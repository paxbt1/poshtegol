<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedSmallInteger('minute')->nullable();
            $table->string('type');
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_entry_id')->unique()->constrained('prediction_entries')->cascadeOnDelete();
            $table->unsignedInteger('full_time_points')->default(0);
            $table->unsignedInteger('exact_score_points')->default(0);
            $table->unsignedInteger('total_goals_points')->default(0);
            $table->unsignedInteger('qualified_team_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('user_period_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('settlement_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_entries')->default(0);
            $table->unsignedBigInteger('total_entry_amount')->default(0);
            $table->unsignedBigInteger('total_paid_amount')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->unsignedBigInteger('reward_amount')->default(0);
            $table->unsignedBigInteger('referral_bonus_amount')->default(0);
            $table->unsignedBigInteger('final_settlement_amount')->default(0);
            $table->string('settlement_status')->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->string('settlement_ref')->nullable();
            $table->timestamps();
            $table->unique(['period_id', 'user_id']);
        });

        Schema::create('period_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->unique()->constrained('settlement_periods')->cascadeOnDelete();
            $table->unsignedBigInteger('total_entry_amount')->default(0);
            $table->unsignedBigInteger('total_paid_amount')->default(0);
            $table->unsignedBigInteger('total_gateway_fee_amount')->default(0);
            $table->unsignedBigInteger('total_reward_amount')->default(0);
            $table->unsignedBigInteger('total_referral_bonus')->default(0);
            $table->bigInteger('net_admin_amount')->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('settlement_periods')->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('type');
            $table->string('direction');
            $table->unsignedBigInteger('amount');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledgers');
        Schema::dropIfExists('period_settlements');
        Schema::dropIfExists('user_period_results');
        Schema::dropIfExists('prediction_results');
        Schema::dropIfExists('match_events');
    }
};
