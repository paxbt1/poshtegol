<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('news_articles', 'local_image_path')) {
                $table->text('local_image_path')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('news_articles') || ! Schema::hasColumn('news_articles', 'local_image_path')) {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropColumn('local_image_path');
        });
    }
};
