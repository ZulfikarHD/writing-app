<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\AIConnectionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\SceneLabelController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Novel routes
    Route::get('novels/create', [NovelController::class, 'create'])->name('novels.create');
    Route::post('novels', [NovelController::class, 'store'])->name('novels.store');
    Route::delete('novels/{novel}', [NovelController::class, 'destroy'])->name('novels.destroy');

    // Editor routes
    Route::get('novels/{novel}/write', [EditorController::class, 'show'])->name('editor.show');
    Route::get('novels/{novel}/write/{scene}', [EditorController::class, 'show'])->name('editor.scene');

    // Plan routes
    Route::get('novels/{novel}/plan', [PlanController::class, 'show'])->name('plan.show');
    Route::get('api/novels/{novel}/scenes/search', [PlanController::class, 'search'])->name('scenes.search');

    // Chapter API routes
    Route::get('api/novels/{novel}/chapters', [ChapterController::class, 'index'])->name('chapters.index');
    Route::post('api/novels/{novel}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
    Route::patch('api/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
    Route::delete('api/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
    Route::post('api/novels/{novel}/chapters/reorder', [ChapterController::class, 'reorder'])->name('chapters.reorder');

    // Scene API routes
    Route::post('api/chapters/{chapter}/scenes', [SceneController::class, 'store'])->name('scenes.store');
    Route::get('api/scenes/{scene}', [SceneController::class, 'show'])->name('scenes.show');
    Route::patch('api/scenes/{scene}', [SceneController::class, 'update'])->name('scenes.update');
    Route::patch('api/scenes/{scene}/content', [SceneController::class, 'updateContent'])->name('scenes.content');
    Route::delete('api/scenes/{scene}', [SceneController::class, 'destroy'])->name('scenes.destroy');
    Route::post('api/scenes/{scene}/archive', [SceneController::class, 'archive'])->name('scenes.archive');
    Route::post('api/scenes/{scene}/restore', [SceneController::class, 'restore'])->name('scenes.restore');
    Route::post('api/chapters/{chapter}/scenes/reorder', [SceneController::class, 'reorder'])->name('scenes.reorder');

    // Scene revisions
    Route::get('api/scenes/{scene}/revisions', [SceneController::class, 'revisions'])->name('scenes.revisions');
    Route::post('api/scenes/{scene}/revisions', [SceneController::class, 'createRevision'])->name('scenes.revisions.create');
    Route::post('api/scenes/{scene}/revisions/{revisionId}/restore', [SceneController::class, 'restoreRevision'])->name('scenes.revisions.restore');

    // Scene duplicate
    Route::post('api/scenes/{scene}/duplicate', [SceneController::class, 'duplicate'])->name('scenes.duplicate');

    // Acts API routes
    Route::get('api/novels/{novel}/acts', [ActController::class, 'index'])->name('acts.index');
    Route::post('api/novels/{novel}/acts', [ActController::class, 'store'])->name('acts.store');
    Route::patch('api/acts/{act}', [ActController::class, 'update'])->name('acts.update');
    Route::delete('api/acts/{act}', [ActController::class, 'destroy'])->name('acts.destroy');
    Route::post('api/novels/{novel}/acts/reorder', [ActController::class, 'reorder'])->name('acts.reorder');

    // Scene Labels API routes
    Route::get('api/novels/{novel}/labels', [SceneLabelController::class, 'index'])->name('labels.index');
    Route::post('api/novels/{novel}/labels', [SceneLabelController::class, 'store'])->name('labels.store');
    Route::patch('api/labels/{label}', [SceneLabelController::class, 'update'])->name('labels.update');
    Route::delete('api/labels/{label}', [SceneLabelController::class, 'destroy'])->name('labels.destroy');
    Route::post('api/scenes/{scene}/labels', [SceneLabelController::class, 'assignToScene'])->name('scenes.labels');

    // Profile routes
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Onboarding routes
    Route::post('onboarding/welcome', [OnboardingController::class, 'completeWelcome'])->name('onboarding.welcome');
    Route::post('onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

    // Settings routes
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/ai', [SettingsController::class, 'aiConnections'])->name('settings.ai');

    // AI Connection API routes
    Route::get('api/ai-connections/providers', [AIConnectionController::class, 'providers'])->name('ai-connections.providers');
    Route::get('api/ai-connections', [AIConnectionController::class, 'index'])->name('ai-connections.index');
    Route::post('api/ai-connections', [AIConnectionController::class, 'store'])->name('ai-connections.store');
    Route::get('api/ai-connections/{aiConnection}', [AIConnectionController::class, 'show'])->name('ai-connections.show');
    Route::patch('api/ai-connections/{aiConnection}', [AIConnectionController::class, 'update'])->name('ai-connections.update');
    Route::delete('api/ai-connections/{aiConnection}', [AIConnectionController::class, 'destroy'])->name('ai-connections.destroy');
    Route::post('api/ai-connections/{aiConnection}/test', [AIConnectionController::class, 'test'])->name('ai-connections.test');
    Route::get('api/ai-connections/{aiConnection}/models', [AIConnectionController::class, 'models'])->name('ai-connections.models');
});
