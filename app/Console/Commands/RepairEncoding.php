<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairEncoding extends Command
{
    protected $signature = 'poshtegol:repair-encoding {--dry-run : Show how many values would change without updating them}';

    protected $description = 'Repair Persian text that was saved after being decoded as Windows-1252/Latin-1 mojibake.';

    /** @var array<string, array<int, string>> */
    private array $columnsByTable = [
        'app_settings' => ['value'],
        'news_articles' => [
            'source_name',
            'original_title',
            'original_description',
            'original_content',
            'translated_title',
            'translated_summary',
            'translated_body',
            'translation_status',
        ],
        'news_categories' => ['title', 'description'],
        'news_sources' => ['name'],
        'ads' => ['title', 'body_text', 'cta_text'],
        'ad_slots' => ['title', 'placement', 'device'],
        'teams' => ['name', 'name_fa', 'group'],
        'football_matches' => ['stage', 'status', 'venue', 'city'],
        'invite_links' => ['title'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totalChanged = 0;

        foreach ($this->columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $existingColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($table, $column)
            ));

            if (! $existingColumns) {
                continue;
            }

            $primaryKey = Schema::hasColumn($table, 'id') ? 'id' : null;
            if (! $primaryKey) {
                continue;
            }

            DB::table($table)
                ->select(array_merge([$primaryKey], $existingColumns))
                ->orderBy($primaryKey)
                ->chunkById(100, function ($rows) use ($table, $existingColumns, $dryRun, &$totalChanged): void {
                    foreach ($rows as $row) {
                        $updates = [];

                        foreach ($existingColumns as $column) {
                            $value = $row->{$column} ?? null;
                            if (! is_string($value) || $value === '') {
                                continue;
                            }

                            $fixed = $this->repairString($value);
                            if ($fixed !== $value) {
                                $updates[$column] = $fixed;
                            }
                        }

                        if (! $updates) {
                            continue;
                        }

                        $totalChanged += count($updates);

                        if (! $dryRun) {
                            DB::table($table)->where('id', $row->id)->update($updates);
                        }
                    }
                }, 'id');
        }

        $message = $dryRun
            ? "Dry run complete. {$totalChanged} text values would be repaired."
            : "Encoding repair complete. {$totalChanged} text values repaired.";

        $this->info($message);

        return self::SUCCESS;
    }

    private function repairString(string $value): string
    {
        $best = $value;
        $bestScore = $this->score($value);
        $current = $value;

        for ($i = 0; $i < 6; $i++) {
            if (! $this->looksLikeMojibake($current)) {
                break;
            }

            $converted = @mb_convert_encoding($current, 'UTF-8', 'Windows-1252');
            if (! is_string($converted) || $converted === $current) {
                break;
            }

            $score = $this->score($converted);
            if ($score > $bestScore) {
                $best = $converted;
                $bestScore = $score;
            }

            $current = $converted;
        }

        return $best;
    }

    private function looksLikeMojibake(string $value): bool
    {
        return (bool) preg_match('/Ã|Â|â€|â€¦|Ë|Å|Æ|Ø|Ù/u', $value);
    }

    private function score(string $value): int
    {
        preg_match_all('/[\x{0600}-\x{06FF}]/u', $value, $persian);
        preg_match_all('/Ã|Â|â€|â€¦|Ë|Å|Æ|Ø|Ù/u', $value, $bad);
        preg_match_all('/\?{2,}/u', $value, $questionMarks);

        return count($persian[0]) * 10
            - count($bad[0]) * 8
            - count($questionMarks[0]) * 30;
    }
}
