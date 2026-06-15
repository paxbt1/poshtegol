<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('news_articles', 'original_description')) {
                $table->text('original_description')->nullable()->after('original_title');
            }
            if (! Schema::hasColumn('news_articles', 'original_content')) {
                $table->longText('original_content')->nullable()->after('original_description');
            }
            if (! Schema::hasColumn('news_articles', 'translated_summary')) {
                $table->text('translated_summary')->nullable()->after('translated_title');
            }
            if (! Schema::hasColumn('news_articles', 'translated_body')) {
                $table->longText('translated_body')->nullable()->after('translated_summary');
            }
            if (! Schema::hasColumn('news_articles', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('translated_body');
            }
        });

        if (Schema::hasColumn('news_articles', 'slug')) {
            DB::table('news_articles')
                ->whereNull('slug')
                ->orderBy('id')
                ->get(['id', 'original_title'])
                ->each(function ($article) {
                    $base = Str::slug(Str::limit((string) $article->original_title, 80, ''), '-');
                    $slug = ($base !== '' ? $base : 'news').'-'.$article->id;
                    DB::table('news_articles')->where('id', $article->id)->update(['slug' => $slug]);
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('news_articles')) {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table) {
            foreach (['original_description', 'original_content', 'translated_summary', 'translated_body', 'slug'] as $column) {
                if (Schema::hasColumn('news_articles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
