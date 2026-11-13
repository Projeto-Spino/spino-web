<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\CompetitionRankingService;
use Inertia\Inertia;
use Inertia\Response;

class AudienceDashboardController extends Controller
{
    public function index(CompetitionRankingService $rankingService): Response
    {
        $rankedTeams = $rankingService->rankedTeams();
        $ranking = $rankedTeams->map(function (array $result): array {
            /** @var Team $team */
            $team = $result['team'];
            return [
                'id' => $team->id,
                'name' => $team->name,
                'efficiency' => $result['efficiency'],
                'precision' => $result['precision'],
                'aesthetic' => $result['aesthetic'],
                'total' => $result['total'],
                'actualLoadKg' => $result['ruptureLoadGrams'] !== null ? round($result['ruptureLoadGrams'] / 1000, 2) : null,
                'declaredLoadKg' => $team->declared_load_grams ? round($team->declared_load_grams / 1000, 2) : null,
                'members' => $team->members->map(fn ($member) => ['name' => $member->name, 'course' => $member->course])->values(),
                'ruptureSamples' => $team->loadSamples->sortBy('elapsed_ms')->map(fn ($sample) => [
                    'seconds' => round($sample->elapsed_ms / 1000, 1),
                    'loadKg' => round($sample->load_grams / 1000, 2),
                ])->values(),
                'position' => $result['position'],
            ];
        })->values();

        $current = Team::where('test_status', 'running')->with('loadSamples')->first();

        return Inertia::render('Public/Dashboard', [
            'ranking' => $ranking,
            'currentTest' => $this->currentTest($current),
            'completedJudges' => $rankingService->completedJudgeCount(),
        ]);
    }

    private function currentTest(?Team $team): ?array
    {
        if (! $team) return null;

        return [
            'name' => $team->name,
            'declaredLoadKg' => $team->declared_load_grams ? round($team->declared_load_grams / 1000, 2) : null,
            'samples' => $team->loadSamples->sortBy('elapsed_ms')->map(fn ($sample) => [
                'seconds' => round($sample->elapsed_ms / 1000, 1),
                'loadKg' => round($sample->load_grams / 1000, 2),
            ])->values(),
        ];
    }
}
