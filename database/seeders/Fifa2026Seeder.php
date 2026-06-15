<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\SettlementPeriod;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RuntimeException;

class Fifa2026Seeder extends Seeder
{
    private const STAGE_LABELS = [
        'group' => 'مرحله گروهی',
        'round_32' => 'یک‌شانزدهم نهایی',
        'round_16' => 'یک‌هشتم نهایی',
        'quarter_final' => 'یک‌چهارم نهایی',
        'semi_final' => 'نیمه‌نهایی',
        'bronze_final' => 'رده‌بندی',
        'final' => 'فینال',
    ];

    private const ENTRY_AMOUNTS = [
        'group' => 50000,
        'round_32' => 75000,
        'round_16' => 85000,
        'quarter_final' => 100000,
        'semi_final' => 125000,
        'bronze_final' => 125000,
        'final' => 150000,
    ];

    private const VENUES = [
        'VANCOUVER' => ['BC Place Vancouver', 'ونکوور', 'کانادا', 'America/Vancouver'],
        'SEATTLE' => ['Seattle Stadium', 'سیاتل', 'آمریکا', 'America/Los_Angeles'],
        'SANFRANCISCO' => ['San Francisco Bay Area Stadium', 'سن‌فرانسیسکو', 'آمریکا', 'America/Los_Angeles'],
        'LOSANGELES' => ['Los Angeles Stadium', 'لس‌آنجلس', 'آمریکا', 'America/Los_Angeles'],
        'GUADALAJARA' => ['Guadalajara Stadium', 'گوادالاخارا', 'مکزیک', 'America/Mexico_City'],
        'MEXCITY' => ['Mexico City Stadium', 'مکزیکوسیتی', 'مکزیک', 'America/Mexico_City'],
        'MONTERREY' => ['Monterrey Stadium', 'مونتری', 'مکزیک', 'America/Mexico_City'],
        'HOUSTON' => ['Houston Stadium', 'هیوستون', 'آمریکا', 'America/Chicago'],
        'DALLAS' => ['Dallas Stadium', 'دالاس', 'آمریکا', 'America/Chicago'],
        'KANSASCITY' => ['Kansas City Stadium', 'کانزاس‌سیتی', 'آمریکا', 'America/Chicago'],
        'ATLANTA' => ['Atlanta Stadium', 'آتلانتا', 'آمریکا', 'America/New_York'],
        'MIAMI' => ['Miami Stadium', 'میامی', 'آمریکا', 'America/New_York'],
        'TORONTO' => ['Toronto Stadium', 'تورنتو', 'کانادا', 'America/Toronto'],
        'BOSTON' => ['Boston Stadium', 'بوستون', 'آمریکا', 'America/New_York'],
        'PHILADELPHIA' => ['Philadelphia Stadium', 'فیلادلفیا', 'آمریکا', 'America/New_York'],
        'NYNJ' => ['New York New Jersey Stadium', 'نیویورک/نیوجرسی', 'آمریکا', 'America/New_York'],
    ];

    public function run(): void
    {
        $teams = $this->json('fifa2026_teams.json');
        $matches = $this->json('fifa2026_matches.json');

        if (count($teams) !== 48) {
            throw new RuntimeException('FIFA 2026 team seed must contain exactly 48 teams.');
        }

        if (count($matches) !== 104) {
            throw new RuntimeException('FIFA 2026 match seed must contain exactly 104 matches.');
        }

        $groupNames = collect($teams)->pluck('group')->unique()->sort()->values()->all();
        if ($groupNames !== range('A', 'L')) {
            throw new RuntimeException('FIFA 2026 groups must be exactly A to L.');
        }

        $teamMap = [];
        foreach ($teams as $team) {
            $model = Team::updateOrCreate(
                ['fifa_code' => $team['code']],
                [
                    'name_fa' => $team['name_fa'],
                    'name_en' => $team['name_en'],
                    'flag_emoji' => $team['flag_emoji'],
                    'group_name' => $team['group'],
                ],
            );
            $teamMap[$team['code']] = $model;
        }

        $groupStart = Carbon::parse('2026-06-11 15:00', 'America/New_York')->timezone('Asia/Tehran');
        $groupEnd = Carbon::parse('2026-06-27 23:59', 'America/New_York')->timezone('Asia/Tehran');
        $knockoutStart = Carbon::parse('2026-06-28 00:00', 'America/New_York')->timezone('Asia/Tehran');
        $finalEnd = Carbon::parse('2026-07-19 23:59', 'America/New_York')->timezone('Asia/Tehran');

        $groupPeriod = SettlementPeriod::updateOrCreate(
            ['type' => 'group_stage'],
            [
                'title' => 'مرحله گروهی',
                'starts_at' => $groupStart,
                'ends_at' => $groupEnd,
                'status' => 'upcoming',
                'referral_enabled' => true,
                'referral_rate' => 3,
            ],
        );

        $knockoutPeriod = SettlementPeriod::updateOrCreate(
            ['type' => 'knockout_to_final'],
            [
                'title' => 'مرحله حذفی تا فینال',
                'starts_at' => $knockoutStart,
                'ends_at' => $finalEnd,
                'status' => 'upcoming',
                'referral_enabled' => false,
                'referral_rate' => 0,
            ],
        );

        foreach ($matches as $row) {
            [$number, $date, $time, $homeCode, $awayCode, $stage, $group, $venueKey] = $row;
            [$venue, $city, $country, $localTimezone] = self::VENUES[$venueKey];

            $kickoffLocal = Carbon::parse($date.' '.$time, $localTimezone);
            $kickoffEt = $kickoffLocal->copy()->timezone('America/New_York');
            $startsAt = $kickoffLocal->copy()->timezone('Asia/Tehran');
            $isPlaceholder = ! isset($teamMap[$homeCode]) || ! isset($teamMap[$awayCode]);

            FootballMatch::updateOrCreate(
                ['match_number' => $number],
                [
                    'external_fixture_id' => 'FWC26-'.$number,
                    'period_id' => $stage === 'group' ? $groupPeriod->id : $knockoutPeriod->id,
                    'stage' => $stage,
                    'group_name' => $group,
                    'home_team_id' => $teamMap[$homeCode]->id ?? null,
                    'away_team_id' => $teamMap[$awayCode]->id ?? null,
                    'status' => 'scheduled',
                    'kickoff_at_et' => $kickoffEt,
                    'starts_at' => $startsAt,
                    'prediction_locks_at' => $startsAt->copy()->subHour(),
                    'timezone_source' => $localTimezone,
                    'venue' => $venue,
                    'city' => $city,
                    'country' => $country,
                    'match_day_label_fa' => $this->matchDayLabel($startsAt),
                    'stage_label_fa' => self::STAGE_LABELS[$stage],
                    'bracket_slot_home' => $homeCode,
                    'bracket_slot_away' => $awayCode,
                    'is_placeholder_match' => $isPlaceholder,
                    'entry_amount' => self::ENTRY_AMOUNTS[$stage],
                ],
            );
        }
    }

    private function json(string $file): array
    {
        $path = database_path('data/'.$file);
        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid FIFA 2026 data file: {$file}");
        }

        return $data;
    }

    private function matchDayLabel(Carbon $startsAt): string
    {
        return $startsAt->locale('fa')->translatedFormat('l j F Y');
    }
}
