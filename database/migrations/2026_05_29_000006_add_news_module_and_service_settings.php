<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            Schema::create('news_articles', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->default('gnews')->index();
                $table->string('external_id')->nullable()->index();
                $table->string('source_name')->nullable();
                $table->string('source_url')->nullable();
                $table->text('original_url')->nullable();
                $table->text('image_url')->nullable();
                $table->text('original_title');
                $table->text('translated_title')->nullable();
                $table->string('language', 10)->default('en');
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamp('fetched_at')->nullable();
                $table->timestamp('translated_at')->nullable();
                $table->string('status')->default('draft')->index();
                $table->boolean('is_featured')->default(false)->index();
                $table->json('raw_payload')->nullable();
                $table->string('hash', 64)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_sync_logs')) {
            Schema::create('news_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->default('gnews')->index();
                $table->string('status')->index();
                $table->unsignedInteger('items_received')->default(0);
                $table->unsignedInteger('items_created')->default(0);
                $table->unsignedInteger('items_updated')->default(0);
                $table->unsignedInteger('items_translated')->default(0);
                $table->text('message')->nullable();
                $table->json('error_payload')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sync_logs');
        Schema::dropIfExists('news_articles');
    }
};
