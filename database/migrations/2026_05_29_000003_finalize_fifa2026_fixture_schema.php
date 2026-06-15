<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'fifa_code')) {
                $table->string('fifa_code', 8)->nullable()->unique()->after('id');
            }
        });

        Schema::table('matches', function (Blueprint $table) {
            if (Schema::hasColumn('matches', 'home_team_id')) {
                $table->foreignId('home_team_id')->nullable()->change();
            }

            if (Schema::hasColumn('matches', 'away_team_id')) {
                $table->foreignId('away_team_id')->nullable()->change();
            }

            if (! Schema::hasColumn('matches', 'kickoff_at_et')) {
                $table->timestamp('kickoff_at_et')->nullable()->after('status');
            }

            if (! Schema::hasColumn('matches', 'timezone_source')) {
                $table->string('timezone_source')->nullable()->after('prediction_locks_at');
            }

            if (! Schema::hasColumn('matches', 'country')) {
                $table->string('country')->nullable()->after('city');
            }

            if (! Schema::hasColumn('matches', 'match_day_label_fa')) {
                $table->string('match_day_label_fa')->nullable()->after('country');
            }

            if (! Schema::hasColumn('matches', 'stage_label_fa')) {
                $table->string('stage_label_fa')->nullable()->after('match_day_label_fa');
            }

            if (! Schema::hasColumn('matches', 'bracket_slot_home')) {
                $table->string('bracket_slot_home')->nullable()->after('stage_label_fa');
            }

            if (! Schema::hasColumn('matches', 'bracket_slot_away')) {
                $table->string('bracket_slot_away')->nullable()->after('bracket_slot_home');
            }

            if (! Schema::hasColumn('matches', 'is_placeholder_match')) {
                $table->boolean('is_placeholder_match')->default(false)->after('bracket_slot_away');
            }
        });

        Schema::table('referral_commissions', function (Blueprint $table) {
            if (! Schema::hasColumn('referral_commissions', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            if (Schema::hasColumn('referral_commissions', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });

        Schema::table('matches', function (Blueprint $table) {
            foreach ([
                'kickoff_at_et',
                'timezone_source',
                'country',
                'match_day_label_fa',
                'stage_label_fa',
                'bracket_slot_home',
                'bracket_slot_away',
                'is_placeholder_match',
            ] as $column) {
                if (Schema::hasColumn('matches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'fifa_code')) {
                $table->dropColumn('fifa_code');
            }
        });
    }
};
