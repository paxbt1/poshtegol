<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invite_links', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('earns_commission')->default(false);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'registered_via_invite_code')) {
                $table->string('registered_via_invite_code')->nullable()->after('invited_by_user_id');
            }

            if (! Schema::hasColumn('users', 'registered_via_invite_type')) {
                $table->string('registered_via_invite_type')->nullable()->after('registered_via_invite_code');
            }

            if (! Schema::hasColumn('users', 'direct_referrer_user_id')) {
                $table->foreignId('direct_referrer_user_id')->nullable()->after('registered_via_invite_type')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('matches', function (Blueprint $table) {
            if (! Schema::hasColumn('matches', 'qualified_team_id')) {
                $table->foreignId('qualified_team_id')->nullable()->after('away_score')->constrained('teams')->nullOnDelete();
            }
        });

        if (Schema::hasTable('app_settings')) {
            DB::table('app_settings')->where('key', 'private_gate_password')->delete();
        }
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (Schema::hasColumn('matches', 'qualified_team_id')) {
                $table->dropConstrainedForeignId('qualified_team_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'direct_referrer_user_id')) {
                $table->dropConstrainedForeignId('direct_referrer_user_id');
            }

            foreach (['registered_via_invite_type', 'registered_via_invite_code'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('invite_links');
    }
};
