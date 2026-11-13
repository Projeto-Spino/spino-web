<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompetitionRankingService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionReportController extends Controller
{
    public function index(CompetitionRankingService $rankingService): Response
    {
        $ranking = $rankingService->rankedTeams();

        return Inertia::render('Admin/Reports/Results', [
            'rows' => $ranking->map(fn (array $result): array => $this->previewRow($result))->values(),
            'summary' => [
                'eligibleTeams' => $ranking->count(),
                'completedTests' => $ranking->where('ruptureSource', '!=', 'pending')->count(),
                'completedJudges' => $rankingService->completedJudgeCount(),
            ],
        ]);
    }

    public function export(CompetitionRankingService $rankingService): StreamedResponse
    {
        $ranking = $rankingService->rankedTeams();
        $filename = 'resultados-competicao-'.now()->format('Y-m-d_H-i').'.csv';

        return response()->streamDownload(function () use ($ranking): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Colocação',
                'Equipe',
                'Código da equipe',
                'Categoria',
                'Integrantes',
                'Líder',
                'Cursos',
                'Peso próprio (g)',
                'Carga declarada (kg)',
                'Carga de ruptura (kg)',
                'Origem da carga',
                'Eficiência (EE)',
                'Precisão (PP)',
                'Estética média',
                'Pontuação total',
                'Status do ensaio',
                'Data do ensaio',
                'Data da auditoria',
            ], ';', '"', '\\', "\r\n");

            foreach ($ranking as $result) {
                fputcsv($output, $this->csvRow($result), ';', '"', '\\', "\r\n");
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function previewRow(array $result): array
    {
        $team = $result['team'];

        return [
            'position' => $result['position'],
            'name' => $team->name,
            'code' => $team->code,
            'memberCount' => $team->members->count(),
            'ruptureLoadKg' => $result['ruptureLoadGrams'] !== null
                ? round($result['ruptureLoadGrams'] / 1000, 2)
                : null,
            'ruptureSource' => $result['ruptureSource'],
            'efficiency' => $result['efficiency'],
            'precision' => $result['precision'],
            'aesthetic' => $result['aesthetic'],
            'total' => $result['total'],
            'testStatus' => $team->test_status,
        ];
    }

    private function csvRow(array $result): array
    {
        $team = $result['team'];
        $leader = $team->members->firstWhere('is_leader', true);

        return [
            $result['position'],
            $this->safeText($team->name),
            $this->safeText($team->code),
            $this->safeText($team->category),
            $this->safeText($team->members->pluck('name')->join(' | ')),
            $this->safeText($leader?->name),
            $this->safeText($team->members->pluck('course')->unique()->join(' | ')),
            $team->measured_weight_grams ?? '',
            $this->formatDecimal($team->declared_load_grams !== null ? $team->declared_load_grams / 1000 : null),
            $this->formatDecimal($result['ruptureLoadGrams'] !== null ? $result['ruptureLoadGrams'] / 1000 : null),
            $this->ruptureSourceLabel($result['ruptureSource']),
            $this->formatDecimal($result['efficiency']),
            $this->formatDecimal($result['precision']),
            $this->formatDecimal($result['aesthetic']),
            $this->formatDecimal($result['total']),
            $this->testStatusLabel($team->test_status),
            $team->tested_at?->format('d/m/Y H:i') ?? '',
            $team->audited_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    private function formatDecimal(float|int|null $value): string
    {
        return $value === null ? '' : number_format((float) $value, 2, ',', '');
    }

    private function safeText(?string $value): string
    {
        $value ??= '';

        return preg_match('/^\s*[=+\-@]/u', $value) ? "'{$value}" : $value;
    }

    private function ruptureSourceLabel(string $source): string
    {
        return match ($source) {
            'sensor' => 'Sensor',
            'manual' => 'Lançamento manual',
            default => 'Sem resultado',
        };
    }

    private function testStatusLabel(string $status): string
    {
        return match ($status) {
            'running' => 'Em andamento',
            'completed' => 'Concluído',
            default => 'Pendente',
        };
    }
}
