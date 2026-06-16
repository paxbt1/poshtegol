<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_settings')) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => 'offline_payment_card_number'],
                ['value' => '6221061063729273', 'is_private' => false, 'created_at' => now(), 'updated_at' => now()],
            );

            DB::table('app_settings')->updateOrInsert(
                ['key' => 'payment_driver'],
                ['value' => 'offline_card', 'is_private' => false, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        if (Schema::hasTable('ad_slots')) {
            DB::table('ad_slots')->updateOrInsert(
                ['key' => 'header_under_nav'],
                [
                    'title' => 'زیر منوی اصلی',
                    'placement' => 'header_under_nav',
                    'device' => 'all',
                    'width' => 1200,
                    'height' => 120,
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            DB::table('ad_slots')->updateOrInsert(
                ['key' => 'home_top_billboard'],
                [
                    'title' => 'بنر بزرگ بالای صفحه اصلی',
                    'placement' => 'home_top',
                    'device' => 'all',
                    'width' => 1200,
                    'height' => 180,
                    'is_active' => true,
                    'sort_order' => 20,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        //
    }
};
