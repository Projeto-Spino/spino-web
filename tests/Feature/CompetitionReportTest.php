<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_view_the_consolidated_results_report(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->createRankedTeam('Equipe Relatório', 'RELAT001', 24.5, 18.25, 5200);

        $this->actingAs($organizer)
            ->get(route('admin.reports.results.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Reports/Results')
                ->where('summary.eligibleTeams', 1)
                ->where('summary.completedTests', 1)
                ->where('rows.0.name', 'Equipe Relatório')
                ->where('rows.0.position', 1)
                ->where('rows.0.ruptureSource', 'sensor'));
    }

    public function test_organizer_can_export_excel_compatible_csv_using_the_public_ranking_order(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->createRankedTeam('Equipe Segunda', 'CSV00002', 10, 10, null, 4100);
        $this->createRankedTeam('Equipe Primeira', 'CSV00001', 30, 20, 6300, 6200);
        Team::create([
            'name' => 'Equipe fora da auditoria',
            'code' => 'CSVFORA1',
            'category' => 'general',
            'quota_type' => 'general',
            'status' => 'approved',
            'audit_status' => 'waiting',
        ]);

        $response = $this->actingAs($organizer)
            ->get(route('admin.reports.results.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $rows = $this->parseCsv(substr($content, 3));

        $this->assertSame('Colocação', $rows[0][0]);
        $this->assertSame('Equipe Primeira', $rows[1][1]);
        $this->assertSame('Sensor', $rows[1][10]);
        $this->assertSame('50,00', $rows[1][14]);
        $this->assertSame('Equipe Segunda', $rows[2][1]);
        $this->assertSame('Lançamento manual', $rows[2][10]);
        $this->assertStringNotContainsString('Equipe fora da auditoria', $content);
    }

    public function test_guest_cannot_export_competition_results(): void
    {
        $this->get(route('admin.reports.results.export'))
            ->assertRedirect(route('login'));
    }

    public function test_csv_neutralizes_spreadsheet_formulas_from_user_supplied_fields(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->createRankedTeam('=HYPERLINK("https://example.com")', 'SAFE0001', 1, 1, 1000);

        $content = $this->actingAs($organizer)
            ->get(route('admin.reports.results.export'))
            ->streamedContent();
        $rows = $this->parseCsv(substr($content, 3));

        $this->assertSame("'=HYPERLINK(\"https://example.com\")", $rows[1][1]);
    }

    private function createRankedTeam(
        string $name,
        string $code,
        float $efficiency,
        float $precision,
        ?int $sensorLoad,
        ?int $manualLoad = null,
    ): Team {
        $team = Team::create([
            'name' => $name,
            'code' => $code,
            'category' => 'general',
            'quota_type' => 'general',
            'status' => 'approved',
            'audit_status' => 'approved',
            'measured_weight_grams' => 350,
            'declared_load_grams' => 5000,
            'actual_load_grams' => $sensorLoad,
            'manual_rupture_load_grams' => $manualLoad,
            'efficiency_score' => $efficiency,
            'precision_score' => $precision,
            'test_status' => 'completed',
            'audited_at' => now()->subHour(),
            'tested_at' => now(),
        ]);

        Member::create([
            'team_id' => $team->id,
            'enrollment' => null,
            'name' => "Líder {$name}",
            'course' => 'Engenharia Civil',
            'email' => strtolower($code).'@aluno.ifsp.edu.br',
            'is_leader' => true,
        ]);

        return $team;
    }

    private function parseCsv(string $content): array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $rows = [];

        while (($row = fgetcsv($stream, 0, ';', '"', '\\')) !== false) {
            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
