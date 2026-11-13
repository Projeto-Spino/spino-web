<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\JudgePanelController;
use App\Http\Controllers\AudienceDashboardController;
use App\Http\Controllers\Admin\RegistrationManagementController;
use App\Http\Controllers\Admin\BridgeAuditController;
use App\Http\Controllers\Admin\JudgeManagementController;
use App\Http\Controllers\Admin\OrganizerManagementController;
use App\Http\Controllers\Admin\TeamManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ManualRuptureResultController;
use App\Http\Controllers\Admin\TestRunController;
use App\Http\Controllers\Admin\CompetitionReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return to_route('registration.create');
});

Route::get('/inscricao', [RegistrationController::class, 'create'])->name('registration.create');
Route::post('/inscricao/equipes', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('registration.store');
Route::post('/inscricao/participacoes', [RegistrationController::class, 'join'])->middleware('throttle:5,1')->name('registration.join');
Route::post('/inscricao/confirmar-email', [RegistrationController::class, 'verifyEmail'])->middleware('throttle:10,1')->name('registration.verify-email');
Route::post('/inscricao/reenviar-confirmacao', [RegistrationController::class, 'resendEmailVerification'])->middleware('throttle:3,1')->name('registration.resend-email');
Route::get('/regulamento', fn () => Inertia::render('Event/Regulation'))->name('event.regulation');
Route::get('/cronograma', fn () => Inertia::render('Event/Schedule', [
    'registrationStartsAt' => config('competition.registration_starts_at'),
    'registrationEndsAt' => config('competition.registration_ends_at'),
]))->name('event.schedule');
Route::get('/contato', fn () => Inertia::render('Event/Contact', [
    'contactEmail' => config('competition.contact_email'),
]))->name('event.contact');
Route::get('/juiz/{credential}', [JudgePanelController::class, 'show'])->name('judge.panel');
Route::post('/juiz/{credential}/voto', [JudgePanelController::class, 'store'])->middleware('throttle:10,1')->name('judge.vote.store');
Route::get('/placar', [AudienceDashboardController::class, 'index'])->name('audience.dashboard');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::middleware('can:manage-registrations')->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/inscricoes', [RegistrationManagementController::class, 'index'])->name('admin.registrations.index');
        Route::patch('/admin/inscricoes/{team}/status', [RegistrationManagementController::class, 'updateStatus'])->name('admin.registrations.status');
        Route::get('/admin/organizacao', [BridgeAuditController::class, 'index'])->name('admin.bridge-audits.index');
        Route::get('/admin/organizacao/{team}', [BridgeAuditController::class, 'show'])->name('admin.bridge-audits.show');
        Route::post('/admin/organizacao/{team}', [BridgeAuditController::class, 'store'])->name('admin.bridge-audits.store');
        Route::get('/admin/ensaios', [TestRunController::class, 'index'])->name('admin.test-runs.index');
        Route::post('/admin/ensaios', [TestRunController::class, 'store'])->name('admin.test-runs.store');
        Route::post('/admin/ensaios/{testRun}/iniciar', [TestRunController::class, 'start'])->name('admin.test-runs.start');
        Route::post('/admin/ensaios/{testRun}/finalizar', [TestRunController::class, 'finish'])->name('admin.test-runs.finish');
        Route::post('/admin/ensaios/{testRun}/cancelar', [TestRunController::class, 'cancel'])->name('admin.test-runs.cancel');
        Route::get('/admin/resultados/ruptura', [ManualRuptureResultController::class, 'index'])->name('admin.manual-rupture-results.index');
        Route::post('/admin/resultados/ruptura/{team}', [ManualRuptureResultController::class, 'store'])->name('admin.manual-rupture-results.store');
        Route::get('/admin/juizes', [JudgeManagementController::class, 'index'])->name('admin.judges.index');
        Route::post('/admin/juizes', [JudgeManagementController::class, 'store'])->name('admin.judges.store');
        Route::patch('/admin/juizes/janela-votacao', [JudgeManagementController::class, 'toggleVoting'])->name('admin.judges.toggle-voting');
        Route::get('/admin/relatorios/resultados', [CompetitionReportController::class, 'index'])->name('admin.reports.results.index');
        Route::get('/admin/relatorios/resultados.csv', [CompetitionReportController::class, 'export'])->name('admin.reports.results.export');

        Route::middleware('can:manage-users')->group(function () {
            Route::get('/admin/organizadores', [OrganizerManagementController::class, 'index'])->name('admin.organizers.index');
            Route::post('/admin/organizadores', [OrganizerManagementController::class, 'store'])->name('admin.organizers.store');
            Route::delete('/admin/organizadores/{user}', [OrganizerManagementController::class, 'destroy'])->name('admin.organizers.destroy');
            Route::get('/admin/equipes', [TeamManagementController::class, 'index'])->name('admin.teams.index');
            Route::post('/admin/equipes', [TeamManagementController::class, 'store'])->name('admin.teams.store');
            Route::delete('/admin/equipes/{team}', [TeamManagementController::class, 'destroy'])->name('admin.teams.destroy');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
