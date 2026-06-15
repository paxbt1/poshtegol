<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (! Schema::hasColumn('matches', 'entry_amount')) {
                $table->unsignedBigInteger('entry_amount')->default(50000)->after('metadata');
            }
        });

        Schema::table('prediction_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('prediction_entries', 'full_time_result')) {
                $table->string('full_time_result')->nullable()->after('payable_amount');
            }
            if (! Schema::hasColumn('prediction_entries', 'exact_home_score')) {
                $table->unsignedTinyInteger('exact_home_score')->nullable()->after('full_time_result');
            }
            if (! Schema::hasColumn('prediction_entries', 'exact_away_score')) {
                $table->unsignedTinyInteger('exact_away_score')->nullable()->after('exact_home_score');
            }
            if (! Schema::hasColumn('prediction_entries', 'total_goals_option')) {
                $table->string('total_goals_option')->nullable()->after('exact_away_score');
            }
            if (! Schema::hasColumn('prediction_entries', 'qualified_team_id')) {
                $table->unsignedBigInteger('qualified_team_id')->nullable()->after('total_goals_option');
            }
            if (! Schema::hasColumn('prediction_entries', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('prediction_status');
            }
            if (! Schema::hasColumn('prediction_entries', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('locked_at');
            }
            if (! Schema::hasColumn('prediction_entries', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            }

            $table->index(['user_id', 'match_id', 'payment_status'], 'prediction_user_match_payment_idx');
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prediction_entry_id')->nullable()->constrained('prediction_entries')->nullOnDelete();
            $table->string('gateway')->default('zibal');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('amount_gateway');
            $table->unsignedBigInteger('entry_amount');
            $table->unsignedBigInteger('gateway_fee_amount');
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->json('callback_payload')->nullable();
            $table->json('request_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invite_code')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('converted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('source')->default('invite_link');
            $table->timestamp('active_until')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('settlement_periods')->cascadeOnDelete();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('base_reward_amount')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(3);
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referral_relations');
        Schema::dropIfExists('referral_visits');
        Schema::dropIfExists('payment_transactions');
    }
};
