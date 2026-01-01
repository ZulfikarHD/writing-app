<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\AIConnectionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CodexAliasController;
use App\Http\Controllers\CodexCategoryController;
use App\Http\Controllers\CodexController;
use App\Http\Controllers\CodexDetailController;
use App\Http\Controllers\CodexDetailDefinitionController;
use App\Http\Controllers\CodexExternalLinkController;
use App\Http\Controllers\CodexImageController;
use App\Http\Controllers\CodexProgressionController;
use App\Http\Controllers\CodexRelationController;
use App\Http\Controllers\CodexTagController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\SceneLabelController;
use App\Http\Controllers\SeriesCodexController;
use App\Http\Controllers\SeriesController;
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

    // Series routes (Pages)
    Route::get('series', [SeriesController::class, 'index'])->name('series.index');
    Route::get('series/create', [SeriesController::class, 'create'])->name('series.create');
    Route::get('series/{series}', [SeriesController::class, 'show'])->name('series.show');
    Route::get('series/{series}/edit', [SeriesController::class, 'edit'])->name('series.edit');

    // Series API routes
    Route::get('api/series', [SeriesController::class, 'apiIndex'])->name('series.api.index');
    Route::post('api/series', [SeriesController::class, 'store'])->name('series.store');
    Route::patch('api/series/{series}', [SeriesController::class, 'update'])->name('series.update');
    Route::delete('api/series/{series}', [SeriesController::class, 'destroy'])->name('series.destroy');
    Route::post('api/series/{series}/novels', [SeriesController::class, 'addNovel'])->name('series.add-novel');
    Route::delete('api/series/{series}/novels/{novel}', [SeriesController::class, 'removeNovel'])->name('series.remove-novel');
    Route::post('api/series/{series}/novels/reorder', [SeriesController::class, 'reorderNovels'])->name('series.reorder-novels');

    // Series Codex routes (Pages)
    Route::get('series/{series}/codex', [SeriesCodexController::class, 'index'])->name('series.codex.index');
    Route::get('series-codex/{entry}', [SeriesCodexController::class, 'show'])->name('series.codex.show');

    // Series Codex API routes
    Route::get('api/series/{series}/codex', [SeriesCodexController::class, 'apiIndex'])->name('series.codex.api.index');
    Route::post('api/series/{series}/codex', [SeriesCodexController::class, 'store'])->name('series.codex.store');
    Route::patch('api/series-codex/{entry}', [SeriesCodexController::class, 'update'])->name('series.codex.update');
    Route::delete('api/series-codex/{entry}', [SeriesCodexController::class, 'destroy'])->name('series.codex.destroy');
    Route::post('api/series-codex/{entry}/aliases', [SeriesCodexController::class, 'addAlias'])->name('series.codex.aliases.store');
    Route::delete('api/series-codex/{entry}/aliases/{aliasId}', [SeriesCodexController::class, 'removeAlias'])->name('series.codex.aliases.destroy');
    Route::post('api/series-codex/{entry}/details', [SeriesCodexController::class, 'addDetail'])->name('series.codex.details.store');
    Route::patch('api/series-codex/{entry}/details/{detailId}', [SeriesCodexController::class, 'updateDetail'])->name('series.codex.details.update');
    Route::delete('api/series-codex/{entry}/details/{detailId}', [SeriesCodexController::class, 'removeDetail'])->name('series.codex.details.destroy');

    // Editor routes
    Route::get('novels/{novel}/write', [EditorController::class, 'show'])->name('editor.show');
    Route::get('novels/{novel}/write/{scene}', [EditorController::class, 'show'])->name('editor.scene');

    // Plan routes
    Route::get('novels/{novel}/plan', [PlanController::class, 'show'])->name('plan.show');
    Route::get('api/novels/{novel}/scenes/search', [PlanController::class, 'search'])->name('scenes.search');

    // Codex routes (Pages)
    Route::get('novels/{novel}/codex', [CodexController::class, 'index'])->name('codex.index');
    Route::get('novels/{novel}/codex/create', [CodexController::class, 'create'])->name('codex.create');
    Route::get('codex/{entry}', [CodexController::class, 'show'])->name('codex.show');
    Route::get('codex/{entry}/edit', [CodexController::class, 'edit'])->name('codex.edit');

    // Codex API routes
    Route::get('api/novels/{novel}/codex', [CodexController::class, 'apiIndex'])->name('codex.api.index');
    Route::get('api/novels/{novel}/codex/editor', [CodexController::class, 'apiEntriesForEditor'])->name('codex.api.editor');
    Route::get('api/novels/{novel}/codex/export/json', [CodexController::class, 'exportJson'])->name('codex.export.json');
    Route::get('api/novels/{novel}/codex/export/csv', [CodexController::class, 'exportCsv'])->name('codex.export.csv');
    Route::post('api/novels/{novel}/codex/import/preview', [CodexController::class, 'previewImport'])->name('codex.import.preview');
    Route::post('api/novels/{novel}/codex/import', [CodexController::class, 'importJson'])->name('codex.import');
    Route::post('api/novels/{novel}/codex/scan-mentions', [CodexController::class, 'scanMentions'])->name('codex.api.scan-mentions');
    Route::post('api/novels/{novel}/codex/quick-create', [CodexController::class, 'quickCreate'])->name('codex.quick-create');
    Route::post('api/novels/{novel}/codex', [CodexController::class, 'store'])->name('codex.store');
    Route::get('api/codex/{entry}', [CodexController::class, 'apiShow'])->name('codex.api.show');
    Route::patch('api/codex/{entry}', [CodexController::class, 'update'])->name('codex.update');
    Route::post('api/codex/{entry}/archive', [CodexController::class, 'archive'])->name('codex.archive');
    Route::post('api/codex/{entry}/restore', [CodexController::class, 'restore'])->name('codex.restore');
    Route::post('api/codex/{entry}/rescan-mentions', [CodexController::class, 'rescanMentions'])->name('codex.rescan-mentions');
    Route::post('api/codex/{entry}/duplicate', [CodexController::class, 'duplicate'])->name('codex.duplicate');
    Route::delete('api/codex/{entry}', [CodexController::class, 'destroy'])->name('codex.destroy');
    Route::post('api/novels/{novel}/codex/bulk-create', [CodexController::class, 'bulkCreate'])->name('codex.bulk-create');

    // Codex Image API routes
    Route::post('api/codex/{entry}/thumbnail', [CodexImageController::class, 'upload'])->name('codex.thumbnail.upload');
    Route::delete('api/codex/{entry}/thumbnail', [CodexImageController::class, 'destroy'])->name('codex.thumbnail.destroy');

    // Codex Alias API routes
    Route::get('api/codex/{entry}/aliases', [CodexAliasController::class, 'index'])->name('codex.aliases.index');
    Route::post('api/codex/{entry}/aliases', [CodexAliasController::class, 'store'])->name('codex.aliases.store');
    Route::patch('api/codex/aliases/{alias}', [CodexAliasController::class, 'update'])->name('codex.aliases.update');
    Route::delete('api/codex/aliases/{alias}', [CodexAliasController::class, 'destroy'])->name('codex.aliases.destroy');

    // Codex Detail API routes
    Route::get('api/codex/{entry}/details', [CodexDetailController::class, 'index'])->name('codex.details.index');
    Route::post('api/codex/{entry}/details', [CodexDetailController::class, 'store'])->name('codex.details.store');
    Route::patch('api/codex/details/{detail}', [CodexDetailController::class, 'update'])->name('codex.details.update');
    Route::delete('api/codex/details/{detail}', [CodexDetailController::class, 'destroy'])->name('codex.details.destroy');
    Route::post('api/codex/{entry}/details/reorder', [CodexDetailController::class, 'reorder'])->name('codex.details.reorder');

    // Codex Relation API routes
    Route::get('api/codex/{entry}/relations', [CodexRelationController::class, 'index'])->name('codex.relations.index');
    Route::post('api/codex/{entry}/relations', [CodexRelationController::class, 'store'])->name('codex.relations.store');
    Route::patch('api/codex/relations/{relation}', [CodexRelationController::class, 'update'])->name('codex.relations.update');
    Route::post('api/codex/relations/{relation}/swap', [CodexRelationController::class, 'swap'])->name('codex.relations.swap');
    Route::delete('api/codex/relations/{relation}', [CodexRelationController::class, 'destroy'])->name('codex.relations.destroy');
    Route::get('api/codex/relation-types', [CodexRelationController::class, 'types'])->name('codex.relations.types');

    // Codex Progression API routes
    Route::get('api/codex/{entry}/progressions', [CodexProgressionController::class, 'index'])->name('codex.progressions.index');
    Route::post('api/codex/{entry}/progressions', [CodexProgressionController::class, 'store'])->name('codex.progressions.store');
    Route::patch('api/codex/progressions/{progression}', [CodexProgressionController::class, 'update'])->name('codex.progressions.update');
    Route::delete('api/codex/progressions/{progression}', [CodexProgressionController::class, 'destroy'])->name('codex.progressions.destroy');

    // Codex Category API routes
    Route::get('api/novels/{novel}/codex/categories', [CodexCategoryController::class, 'index'])->name('codex.categories.index');
    Route::post('api/novels/{novel}/codex/categories', [CodexCategoryController::class, 'store'])->name('codex.categories.store');
    Route::patch('api/codex/categories/{category}', [CodexCategoryController::class, 'update'])->name('codex.categories.update');
    Route::delete('api/codex/categories/{category}', [CodexCategoryController::class, 'destroy'])->name('codex.categories.destroy');
    Route::post('api/codex/{entry}/categories', [CodexCategoryController::class, 'assignToEntry'])->name('codex.categories.assign');

    // Codex External Link API routes (Sprint 13: F-12.2.2)
    Route::get('api/codex/{entry}/external-links', [CodexExternalLinkController::class, 'index'])->name('codex.external-links.index');
    Route::post('api/codex/{entry}/external-links', [CodexExternalLinkController::class, 'store'])->name('codex.external-links.store');
    Route::patch('api/codex/external-links/{link}', [CodexExternalLinkController::class, 'update'])->name('codex.external-links.update');
    Route::delete('api/codex/external-links/{link}', [CodexExternalLinkController::class, 'destroy'])->name('codex.external-links.destroy');
    Route::post('api/codex/{entry}/external-links/reorder', [CodexExternalLinkController::class, 'reorder'])->name('codex.external-links.reorder');

    // Codex Tag API routes (Sprint 14: US-12.4)
    Route::get('api/novels/{novel}/codex/tags', [CodexTagController::class, 'index'])->name('codex.tags.index');
    Route::post('api/novels/{novel}/codex/tags', [CodexTagController::class, 'store'])->name('codex.tags.store');
    Route::post('api/novels/{novel}/codex/tags/initialize', [CodexTagController::class, 'initializePredefined'])->name('codex.tags.initialize');
    Route::patch('api/codex/tags/{tag}', [CodexTagController::class, 'update'])->name('codex.tags.update');
    Route::delete('api/codex/tags/{tag}', [CodexTagController::class, 'destroy'])->name('codex.tags.destroy');
    Route::post('api/codex/{entry}/tags', [CodexTagController::class, 'assignToEntry'])->name('codex.tags.assign');
    Route::delete('api/codex/{entry}/tags/{tag}', [CodexTagController::class, 'removeFromEntry'])->name('codex.tags.remove');

    // Codex Detail Definition API routes (Sprint 14: US-12.5, US-12.7)
    Route::get('api/novels/{novel}/codex/detail-definitions', [CodexDetailDefinitionController::class, 'index'])->name('codex.definitions.index');
    Route::post('api/novels/{novel}/codex/detail-definitions', [CodexDetailDefinitionController::class, 'store'])->name('codex.definitions.store');
    Route::patch('api/codex/detail-definitions/{definition}', [CodexDetailDefinitionController::class, 'update'])->name('codex.definitions.update');
    Route::delete('api/codex/detail-definitions/{definition}', [CodexDetailDefinitionController::class, 'destroy'])->name('codex.definitions.destroy');
    Route::get('api/codex/detail-presets/{index}', [CodexDetailDefinitionController::class, 'getPreset'])->name('codex.presets.show');

    // Codex Detail from Preset (Sprint 14: US-12.7)
    Route::post('api/codex/{entry}/details/from-preset', [CodexDetailController::class, 'storeFromPreset'])->name('codex.details.from-preset');

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
