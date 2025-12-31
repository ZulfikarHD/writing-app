<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SceneController;
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

    // Profile routes
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Onboarding routes
    Route::post('onboarding/welcome', [OnboardingController::class, 'completeWelcome'])->name('onboarding.welcome');
    Route::post('onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
});
