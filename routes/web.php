<?php

use App\Http\Controllers\CodexController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\SeriesCodexController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkspaceController;
use App\Models\Novel;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Novels
    Route::prefix('novels')->group(function () {
        Route::get('create', [NovelController::class, 'create'])->name('novels.create');
        Route::post('/', [NovelController::class, 'store'])->name('novels.store');
        Route::delete('{novel}', [NovelController::class, 'destroy'])->name('novels.destroy');

        // Editor (legacy - kept for backwards compatibility)
        Route::get('{novel}/write', [EditorController::class, 'show'])->name('editor.show');
        Route::get('{novel}/write/{scene}', [EditorController::class, 'show'])->name('editor.scene');

        // Unified Workspace (new Novelcrafter-style layout)
        Route::get('{novel}/workspace', [WorkspaceController::class, 'show'])->name('workspace.show');
        Route::get('{novel}/workspace/{scene}', [WorkspaceController::class, 'show'])->name('workspace.scene');

        // Plan redirect to workspace
        Route::get('{novel}/plan', fn (Novel $novel) => redirect()->route('workspace.show', ['novel' => $novel, 'mode' => 'plan']))->name('plan.redirect');

        // Codex pages
        Route::prefix('{novel}/codex')->group(function () {
            // Index redirects to workspace
            Route::get('/', fn (Novel $novel) => redirect()->route('workspace.show', ['novel' => $novel, 'mode' => 'codex']))->name('codex.redirect');
            Route::get('create', [CodexController::class, 'create'])->name('codex.create');
        });
    });

    // Codex standalone pages (entry-specific)
    Route::prefix('codex/{entry}')->group(function () {
        Route::get('/', [CodexController::class, 'show'])->name('codex.show');
        Route::get('edit', [CodexController::class, 'edit'])->name('codex.edit');
    });

    // Series
    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'index'])->name('series.index');
        Route::get('create', [SeriesController::class, 'create'])->name('series.create');
        Route::get('{series}', [SeriesController::class, 'show'])->name('series.show');
        Route::get('{series}/edit', [SeriesController::class, 'edit'])->name('series.edit');

        // Series Codex pages
        Route::get('{series}/codex', [SeriesCodexController::class, 'index'])->name('series.codex.index');
    });

    // Series Codex entry page
    Route::get('series-codex/{entry}', [SeriesCodexController::class, 'show'])->name('series.codex.show');

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Onboarding
    Route::prefix('onboarding')->group(function () {
        Route::post('welcome', [OnboardingController::class, 'completeWelcome'])->name('onboarding.welcome');
        Route::post('skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    });

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('ai', [SettingsController::class, 'aiConnections'])->name('settings.ai');
    });

    // Prompts Library
    Route::get('prompts', [PromptController::class, 'index'])->name('prompts.index');
});
