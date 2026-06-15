<?php

use App\Services\CardNumberService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->after('mobile');
            }

            if (! Schema::hasColumn('users', 'card_hash')) {
                $table->string('card_hash', 128)->nullable()->unique()->after('card_number');
            }
        });

        $this->backfillCardHashes();

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'external_team_id')) {
                $table->unsignedBigInteger('external_team_id')->nullable()->unique()->after('fifa_code');
            }
            if (! Schema::hasColumn('teams', 'tla')) {
                $table->string('tla', 8)->nullable()->after('external_team_id');
            }
            if (! Schema::hasColumn('teams', 'crest_url')) {
                $table->string('crest_url')->nullable()->after('flag_emoji');
            }
            if (! Schema::hasColumn('teams', 'area_name')) {
                $table->string('area_name')->nullable()->after('group_name');
            }
            if (! Schema::hasColumn('teams', 'area_code')) {
                $table->string('area_code', 8)->nullable()->after('area_name');
            }
            if (! Schema::hasColumn('teams', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('area_code');
            }
            if (! Schema::hasColumn('teams', 'raw_payload')) {
                $table->json('raw_payload')->nullable()->after('last_synced_at');
            }
        });

        Schema::table('matches', function (Blueprint $table) {
            if (! Schema::hasColumn('matches', 'football_data_id')) {
                $table->unsignedBigInteger('football_data_id')->nullable()->unique()->after('external_fixture_id');
            }
            if (! Schema::hasColumn('matches', 'api_status')) {
                $table->string('api_status')->nullable()->after('status');
            }
            if (! Schema::hasColumn('matches', 'api_stage')) {
                $table->string('api_stage')->nullable()->after('api_status');
            }
            if (! Schema::hasColumn('matches', 'api_group')) {
                $table->string('api_group')->nullable()->after('api_stage');
            }
            if (! Schema::hasColumn('matches', 'matchday')) {
                $table->unsignedSmallInteger('matchday')->nullable()->after('api_group');
            }
            if (! Schema::hasColumn('matches', 'minute')) {
                $table->unsignedSmallInteger('minute')->nullable()->after('matchday');
            }
            if (! Schema::hasColumn('matches', 'injury_time')) {
                $table->unsignedSmallInteger('injury_time')->nullable()->after('minute');
            }
            if (! Schema::hasColumn('matches', 'half_time_home_score')) {
                $table->unsignedTinyInteger('half_time_home_score')->nullable()->after('away_score');
            }
            if (! Schema::hasColumn('matches', 'half_time_away_score')) {
                $table->unsignedTinyInteger('half_time_away_score')->nullable()->after('half_time_home_score');
            }
            if (! Schema::hasColumn('matches', 'source_last_updated_at')) {
                $table->timestamp('source_last_updated_at')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('matches', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('source_last_updated_at');
            }
            if (! Schema::hasColumn('matches', 'auto_score_calculated_at')) {
                $table->timestamp('auto_score_calculated_at')->nullable()->after('last_synced_at');
            }
            if (! Schema::hasColumn('matches', 'manual_result_locked')) {
                $table->boolean('manual_result_locked')->default(false)->after('auto_score_calculated_at');
            }
            if (! Schema::hasColumn('matches', 'raw_payload')) {
                $table->json('raw_payload')->nullable()->after('manual_result_locked');
            }
        });

        Schema::table('match_events', function (Blueprint $table) {
            if (! Schema::hasColumn('match_events', 'source')) {
                $table->string('source')->nullable()->after('payload');
            }
            if (! Schema::hasColumn('match_events', 'external_event_id')) {
                $table->string('external_event_id')->nullable()->index()->after('source');
            }
        });

        Schema::create('football_data_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('status');
            $table->string('requested_url')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('items_received')->default(0);
            $table->unsignedInteger('items_created')->default(0);
            $table->unsignedInteger('items_updated')->default(0);
            $table->text('message')->nullable();
            $table->json('error_payload')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('football_data_sync_logs');

        Schema::table('match_events', function (Blueprint $table) {
            foreach (['external_event_id', 'source'] as $column) {
                if (Schema::hasColumn('match_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('matches', function (Blueprint $table) {
            foreach ([
                'football_data_id', 'api_status', 'api_stage', 'api_group', 'matchday', 'minute',
                'injury_time', 'half_time_home_score', 'half_time_away_score', 'source_last_updated_at',
                'last_synced_at', 'auto_score_calculated_at', 'manual_result_locked', 'raw_payload',
            ] as $column) {
                if (Schema::hasColumn('matches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            foreach (['external_team_id', 'tla', 'crest_url', 'area_name', 'area_code', 'last_synced_at', 'raw_payload'] as $column) {
                if (Schema::hasColumn('teams', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'card_hash')) {
                $table->dropColumn('card_hash');
            }
            if (Schema::hasColumn('users', 'password')) {
                $table->dropColumn('password');
            }
        });
    }

    private function backfillCardHashes(): void
    {
        if (! class_exists(CardNumberService::class) || ! Schema::hasColumn('users', 'card_hash')) {
            return;
        }

        $service = app(CardNumberService::class);

        \App\Models\User::query()
            ->whereNull('card_hash')
            ->whereNotNull('card_number')
            ->each(function (\App\Models\User $user) use ($service) {
                try {
                    $card = $service->normalize((string) $user->card_number);
                    if ($card !== '') {
                        DB::table('users')->where('id', $user->id)->update(['card_hash' => $service->hash($card)]);
                    }
                } catch (Throwable) {
                    // Keep the user row intact if an old encrypted value cannot be decrypted.
                }
            });
    }
};
