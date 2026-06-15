<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->unique()->after('last_name');
            }

            if (! Schema::hasColumn('users', 'card_number')) {
                $table->text('card_number')->nullable()->after('mobile');
            }

            if (! Schema::hasColumn('users', 'card_last4')) {
                $table->string('card_last4', 4)->nullable()->after('card_number');
            }

            if (! Schema::hasColumn('users', 'invite_code')) {
                $table->string('invite_code')->nullable()->unique()->after('card_last4');
            }

            if (! Schema::hasColumn('users', 'invited_by_user_id')) {
                $table->unsignedBigInteger('invited_by_user_id')->nullable()->after('invite_code');
            }

            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('invited_by_user_id');
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_admin');
            }

            if (! Schema::hasColumn('users', 'mobile_verified_at')) {
                $table->timestamp('mobile_verified_at')->nullable()->after('is_active');
            }
        });

        if (Schema::hasColumn('users', 'email')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('drop index if exists "users_email_unique"');
            } else {
                Schema::table('users', fn (Blueprint $table) => $table->dropUnique('users_email_unique'));
            }
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['name', 'email', 'email_verified_at', 'password', 'remember_token'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['first_name', 'last_name', 'mobile', 'card_number', 'card_last4', 'invite_code', 'is_admin', 'is_active', 'mobile_verified_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('users', 'invited_by_user_id')) {
                $table->dropColumn('invited_by_user_id');
            }
        });
    }
};
