<?php

namespace App\Services;

use App\Models\JudgeVote;
use App\Models\Team;
use Illuminate\Support\Collection;

class CompetitionRankingService
{
    public function completedJudgeCount(): int
    {
        return JudgeVote::query()->count();
    }

    /**
     * @return Collection<int, array{
     *     team: Team,
     *     position: int,
     *     efficiency: float,
     *     precision: float,
     *     aesthetic: float,
     *     total: float,
     *     ruptureLoadGrams: int|null,
     *     ruptureSource: string
     * }>
     */
    public function rankedTeams(): Collection
    {
        $votes = JudgeVote::query()->get();
        $judgeCount = $votes->count();
        $aestheticTotals = [];

        foreach ($votes as $vote) {
            $aestheticTotals[$vote->first_team_id] = ($aestheticTotals[$vote->first_team_id] ?? 0) + 15;
            $aestheticTotals[$vote->second_team_id] = ($aestheticTotals[$vote->second_team_id] ?? 0) + 10;
            $aestheticTotals[$vote->third_team_id] = ($aestheticTotals[$vote->third_team_id] ?? 0) + 5;
        }

        return Team::query()
            ->where('audit_status', 'approved')
            ->with(['members', 'loadSamples'])
            ->get()
            ->map(function (Team $team) use ($aestheticTotals, $judgeCount): array {
                $aesthetic = $judgeCount ? round(($aestheticTotals[$team->id] ?? 0) / $judgeCount, 2) : 0;
                $efficiency = (float) ($team->efficiency_score ?? 0);
                $precision = (float) ($team->precision_score ?? 0);
                $ruptureLoadGrams = $team->actual_load_grams ?? $team->manual_rupture_load_grams;

                return [
                    'team' => $team,
                    'efficiency' => $efficiency,
                    'precision' => $precision,
                    'aesthetic' => $aesthetic,
                    'total' => round($efficiency + $precision + $aesthetic, 2),
                    'ruptureLoadGrams' => $ruptureLoadGrams,
                    'ruptureSource' => $team->actual_load_grams !== null
                        ? 'sensor'
                        : ($team->manual_rupture_load_grams !== null ? 'manual' : 'pending'),
                ];
            })
            ->sort(function (array $a, array $b): int {
                $teamA = $a['team'];
                $teamB = $b['team'];
                $testedAtA = $teamA->tested_at?->timestamp ?? PHP_INT_MAX;
                $testedAtB = $teamB->tested_at?->timestamp ?? PHP_INT_MAX;

                return [
                    $b['total'],
                    $b['precision'],
                    round(($b['ruptureLoadGrams'] ?? 0) / 1000, 2),
                    $b['aesthetic'],
                    -$testedAtB,
                ] <=> [
                    $a['total'],
                    $a['precision'],
                    round(($a['ruptureLoadGrams'] ?? 0) / 1000, 2),
                    $a['aesthetic'],
                    -$testedAtA,
                ];
            })
            ->values()
            ->map(fn (array $result, int $index): array => [
                ...$result,
                'position' => $index + 1,
            ]);
    }
}
