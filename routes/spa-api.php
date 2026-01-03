<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\AIConnectionController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChatContextController;
use App\Http\Controllers\ChatController;
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
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PromptComponentController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptInputController;
use App\Http\Controllers\PromptPersonaController;
use App\Http\Controllers\PromptPresetController;
use App\Http\Controllers\ProseGenerationController;
use App\Http\Controllers\PromptSharingController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\SceneLabelController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TextReplacementController;
use App\Http\Controllers\SeriesCodexController;
use App\Http\Controllers\SeriesController;
use Illuminate\Support\Facades\Route;

/**
 * SPA API Routes
 *
 * These routes are used by the Vue.js SPA and need:
 * - 'web' middleware (for session & CSRF protection)
 * - 'auth' middleware (session-based authentication)
 * - They are prefixed with /api but use web middleware
 */
Route::middleware('auth')->prefix('api')->group(function () {
    // ==================== Series API ====================
    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'apiIndex'])->name('series.api.index');
        Route::post('/', [SeriesController::class, 'store'])->name('series.store');
        Route::patch('{series}', [SeriesController::class, 'update'])->name('series.update');
        Route::delete('{series}', [SeriesController::class, 'destroy'])->name('series.destroy');
        Route::post('{series}/novels', [SeriesController::class, 'addNovel'])->name('series.add-novel');
        Route::delete('{series}/novels/{novel}', [SeriesController::class, 'removeNovel'])->name('series.remove-novel');
        Route::post('{series}/novels/reorder', [SeriesController::class, 'reorderNovels'])->name('series.reorder-novels');
    });

    // ==================== Series Codex API ====================
    Route::prefix('series/{series}/codex')->group(function () {
        Route::get('/', [SeriesCodexController::class, 'apiIndex'])->name('series.codex.api.index');
        Route::post('/', [SeriesCodexController::class, 'store'])->name('series.codex.store');
    });

    Route::prefix('series-codex/{entry}')->group(function () {
        Route::patch('/', [SeriesCodexController::class, 'update'])->name('series.codex.update');
        Route::delete('/', [SeriesCodexController::class, 'destroy'])->name('series.codex.destroy');
        Route::post('aliases', [SeriesCodexController::class, 'addAlias'])->name('series.codex.aliases.store');
        Route::delete('aliases/{aliasId}', [SeriesCodexController::class, 'removeAlias'])->name('series.codex.aliases.destroy');
        Route::post('details', [SeriesCodexController::class, 'addDetail'])->name('series.codex.details.store');
        Route::patch('details/{detailId}', [SeriesCodexController::class, 'updateDetail'])->name('series.codex.details.update');
        Route::delete('details/{detailId}', [SeriesCodexController::class, 'removeDetail'])->name('series.codex.details.destroy');
    });

    // ==================== Plan API ====================
    Route::prefix('novels/{novel}')->group(function () {
        Route::get('scenes/search', [PlanController::class, 'search'])->name('scenes.search');
        Route::get('plan/matrix', [PlanController::class, 'matrix'])->name('plan.matrix');
        Route::get('plan/settings', [PlanController::class, 'getSettings'])->name('plan.settings');
        Route::patch('plan/settings', [PlanController::class, 'updateSettings'])->name('plan.settings.update');
        Route::post('plan/from-outline', [PlanController::class, 'createFromOutline'])->name('plan.from-outline');
        Route::post('plan/bulk-pov', [PlanController::class, 'bulkSetPov'])->name('plan.bulk-pov');
        Route::delete('empty-scenes', [PlanController::class, 'deleteEmptyScenes'])->name('novels.delete-empty-scenes');
    });

    Route::prefix('scenes/{scene}')->group(function () {
        Route::patch('pov', [PlanController::class, 'setScenePov'])->name('scenes.pov');
        Route::post('labels/sync', [PlanController::class, 'setSceneLabel'])->name('scenes.labels.sync');
    });

    Route::get('templates', [PlanController::class, 'getTemplates'])->name('templates.index');
    Route::post('plan/parse-outline', [PlanController::class, 'parseOutline'])->name('plan.parse-outline');

    Route::prefix('acts')->group(function () {
        Route::patch('{act}/numeration', [PlanController::class, 'toggleActNumeration'])->name('acts.numeration');
        Route::post('{act}/copy-prose', [PlanController::class, 'copyActProse'])->name('acts.copy-prose');
        Route::post('{act}/copy-outlines', [PlanController::class, 'copyActOutlines'])->name('acts.copy-outlines');
    });

    Route::patch('chapters/{chapter}/numeration', [PlanController::class, 'toggleChapterNumeration'])->name('chapters.numeration');

    // ==================== Codex API ====================
    Route::prefix('novels/{novel}/codex')->group(function () {
        Route::get('/', [CodexController::class, 'apiIndex'])->name('codex.api.index');
        Route::get('editor', [CodexController::class, 'apiEntriesForEditor'])->name('codex.api.editor');
        Route::get('alias-lookup', [CodexController::class, 'aliasLookup'])->name('codex.api.alias-lookup');
        Route::get('subplots', [CodexController::class, 'subplots'])->name('codex.api.subplots');
        Route::get('export/json', [CodexController::class, 'exportJson'])->name('codex.export.json');
        Route::get('export/csv', [CodexController::class, 'exportCsv'])->name('codex.export.csv');
        Route::post('import/preview', [CodexController::class, 'previewImport'])->name('codex.import.preview');
        Route::post('import', [CodexController::class, 'importJson'])->name('codex.import');
        Route::post('scan-mentions', [CodexController::class, 'scanMentions'])->name('codex.api.scan-mentions');
        Route::post('quick-create', [CodexController::class, 'quickCreate'])->name('codex.quick-create');
        Route::post('/', [CodexController::class, 'store'])->name('codex.store');
        Route::post('bulk-create', [CodexController::class, 'bulkCreate'])->name('codex.bulk-create');
    });

    Route::prefix('codex/{entry}')->group(function () {
        Route::get('/', [CodexController::class, 'apiShow'])->name('codex.api.show');
        Route::patch('/', [CodexController::class, 'update'])->name('codex.update');
        Route::post('archive', [CodexController::class, 'archive'])->name('codex.archive');
        Route::post('restore', [CodexController::class, 'restore'])->name('codex.restore');
        Route::post('rescan-mentions', [CodexController::class, 'rescanMentions'])->name('codex.rescan-mentions');
        Route::post('duplicate', [CodexController::class, 'duplicate'])->name('codex.duplicate');
        Route::delete('/', [CodexController::class, 'destroy'])->name('codex.destroy');

        // Codex Image
        Route::post('thumbnail', [CodexImageController::class, 'upload'])->name('codex.thumbnail.upload');
        Route::delete('thumbnail', [CodexImageController::class, 'destroy'])->name('codex.thumbnail.destroy');

        // Codex Aliases
        Route::get('aliases', [CodexAliasController::class, 'index'])->name('codex.aliases.index');
        Route::post('aliases', [CodexAliasController::class, 'store'])->name('codex.aliases.store');

        // Codex Details
        Route::get('details', [CodexDetailController::class, 'index'])->name('codex.details.index');
        Route::post('details', [CodexDetailController::class, 'store'])->name('codex.details.store');
        Route::post('details/reorder', [CodexDetailController::class, 'reorder'])->name('codex.details.reorder');
        Route::post('details/from-preset', [CodexDetailController::class, 'storeFromPreset'])->name('codex.details.from-preset');

        // Codex Relations
        Route::get('relations', [CodexRelationController::class, 'index'])->name('codex.relations.index');
        Route::post('relations', [CodexRelationController::class, 'store'])->name('codex.relations.store');

        // Codex Progressions
        Route::get('progressions', [CodexProgressionController::class, 'index'])->name('codex.progressions.index');
        Route::post('progressions', [CodexProgressionController::class, 'store'])->name('codex.progressions.store');

        // Codex External Links
        Route::get('external-links', [CodexExternalLinkController::class, 'index'])->name('codex.external-links.index');
        Route::post('external-links', [CodexExternalLinkController::class, 'store'])->name('codex.external-links.store');
        Route::post('external-links/reorder', [CodexExternalLinkController::class, 'reorder'])->name('codex.external-links.reorder');

        // Codex Tags
        Route::post('tags', [CodexTagController::class, 'assignToEntry'])->name('codex.tags.assign');
        Route::delete('tags/{tag}', [CodexTagController::class, 'removeFromEntry'])->name('codex.tags.remove');

        // Codex Categories
        Route::post('categories', [CodexCategoryController::class, 'assignToEntry'])->name('codex.categories.assign');
    });

    // Codex Alias routes (by alias ID)
    Route::prefix('codex/aliases/{alias}')->group(function () {
        Route::patch('/', [CodexAliasController::class, 'update'])->name('codex.aliases.update');
        Route::delete('/', [CodexAliasController::class, 'destroy'])->name('codex.aliases.destroy');
    });

    // Codex Detail routes (by detail ID)
    Route::prefix('codex/details/{detail}')->group(function () {
        Route::patch('/', [CodexDetailController::class, 'update'])->name('codex.details.update');
        Route::delete('/', [CodexDetailController::class, 'destroy'])->name('codex.details.destroy');
    });

    // Codex Relation routes (by relation ID)
    Route::prefix('codex/relations/{relation}')->group(function () {
        Route::patch('/', [CodexRelationController::class, 'update'])->name('codex.relations.update');
        Route::post('swap', [CodexRelationController::class, 'swap'])->name('codex.relations.swap');
        Route::delete('/', [CodexRelationController::class, 'destroy'])->name('codex.relations.destroy');
    });

    Route::get('codex/relation-types', [CodexRelationController::class, 'types'])->name('codex.relations.types');

    // Codex Progression routes (by progression ID)
    Route::prefix('codex/progressions/{progression}')->group(function () {
        Route::patch('/', [CodexProgressionController::class, 'update'])->name('codex.progressions.update');
        Route::delete('/', [CodexProgressionController::class, 'destroy'])->name('codex.progressions.destroy');
    });

    // Codex Category routes
    Route::prefix('novels/{novel}/codex/categories')->group(function () {
        Route::get('/', [CodexCategoryController::class, 'index'])->name('codex.categories.index');
        Route::post('/', [CodexCategoryController::class, 'store'])->name('codex.categories.store');
    });

    Route::prefix('codex/categories/{category}')->group(function () {
        Route::patch('/', [CodexCategoryController::class, 'update'])->name('codex.categories.update');
        Route::delete('/', [CodexCategoryController::class, 'destroy'])->name('codex.categories.destroy');
        Route::get('preview-entries', [CodexCategoryController::class, 'previewEntries'])->name('codex.categories.preview');
    });

    // Codex External Link routes (by link ID)
    Route::prefix('codex/external-links/{link}')->group(function () {
        Route::patch('/', [CodexExternalLinkController::class, 'update'])->name('codex.external-links.update');
        Route::delete('/', [CodexExternalLinkController::class, 'destroy'])->name('codex.external-links.destroy');
    });

    // Codex Tag routes
    Route::prefix('novels/{novel}/codex/tags')->group(function () {
        Route::get('/', [CodexTagController::class, 'index'])->name('codex.tags.index');
        Route::post('/', [CodexTagController::class, 'store'])->name('codex.tags.store');
        Route::post('initialize', [CodexTagController::class, 'initializePredefined'])->name('codex.tags.initialize');
    });

    Route::prefix('codex/tags/{tag}')->group(function () {
        Route::patch('/', [CodexTagController::class, 'update'])->name('codex.tags.update');
        Route::delete('/', [CodexTagController::class, 'destroy'])->name('codex.tags.destroy');
    });

    // Codex Detail Definition routes
    Route::prefix('novels/{novel}/codex/detail-definitions')->group(function () {
        Route::get('/', [CodexDetailDefinitionController::class, 'index'])->name('codex.definitions.index');
        Route::post('/', [CodexDetailDefinitionController::class, 'store'])->name('codex.definitions.store');
    });

    Route::prefix('codex/detail-definitions/{definition}')->group(function () {
        Route::patch('/', [CodexDetailDefinitionController::class, 'update'])->name('codex.definitions.update');
        Route::delete('/', [CodexDetailDefinitionController::class, 'destroy'])->name('codex.definitions.destroy');
    });

    Route::get('codex/detail-presets/{index}', [CodexDetailDefinitionController::class, 'getPreset'])->name('codex.presets.show');

    // ==================== Chapter API ====================
    Route::prefix('novels/{novel}/chapters')->group(function () {
        Route::get('/', [ChapterController::class, 'index'])->name('chapters.index');
        Route::post('/', [ChapterController::class, 'store'])->name('chapters.store');
        Route::post('reorder', [ChapterController::class, 'reorder'])->name('chapters.reorder');
    });

    Route::prefix('chapters/{chapter}')->group(function () {
        Route::patch('/', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/', [ChapterController::class, 'destroy'])->name('chapters.destroy');

        // Scenes under chapters
        Route::post('scenes', [SceneController::class, 'store'])->name('scenes.store');
        Route::post('scenes/reorder', [SceneController::class, 'reorder'])->name('scenes.reorder');
    });

    // ==================== Scene API ====================
    Route::prefix('scenes/{scene}')->group(function () {
        Route::get('/', [SceneController::class, 'show'])->name('scenes.show');
        Route::patch('/', [SceneController::class, 'update'])->name('scenes.update');
        Route::patch('content', [SceneController::class, 'updateContent'])->name('scenes.content');
        Route::delete('/', [SceneController::class, 'destroy'])->name('scenes.destroy');
        Route::post('archive', [SceneController::class, 'archive'])->name('scenes.archive');
        Route::post('restore', [SceneController::class, 'restore'])->name('scenes.restore');
        Route::post('duplicate', [SceneController::class, 'duplicate'])->name('scenes.duplicate');

        // Scene revisions
        Route::get('revisions', [SceneController::class, 'revisions'])->name('scenes.revisions');
        Route::post('revisions', [SceneController::class, 'createRevision'])->name('scenes.revisions.create');
        Route::post('revisions/{revisionId}/restore', [SceneController::class, 'restoreRevision'])->name('scenes.revisions.restore');

        // Scene labels
        Route::post('labels', [SceneLabelController::class, 'assignToScene'])->name('scenes.labels');

        // Scene sections
        Route::get('sections', [SectionController::class, 'index'])->name('scenes.sections.index');
        Route::post('sections', [SectionController::class, 'store'])->name('scenes.sections.store');
        Route::post('sections/reorder', [SectionController::class, 'reorder'])->name('scenes.sections.reorder');

        // Scene subplots
        Route::get('subplots', [SceneController::class, 'subplots'])->name('scenes.subplots.index');
        Route::post('subplots', [SceneController::class, 'assignSubplot'])->name('scenes.subplots.store');
        Route::delete('subplots/{codexEntry}', [SceneController::class, 'removeSubplot'])->name('scenes.subplots.destroy');
    });

    // ==================== Sections API ====================
    Route::prefix('sections/{section}')->group(function () {
        Route::get('/', [SectionController::class, 'show'])->name('sections.show');
        Route::patch('/', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/', [SectionController::class, 'destroy'])->name('sections.destroy');
        Route::post('toggle-collapse', [SectionController::class, 'toggleCollapse'])->name('sections.toggle-collapse');
        Route::post('toggle-ai-visibility', [SectionController::class, 'toggleAiVisibility'])->name('sections.toggle-ai');
        Route::post('dissolve', [SectionController::class, 'dissolve'])->name('sections.dissolve');
        Route::post('duplicate', [SectionController::class, 'duplicate'])->name('sections.duplicate');
    });

    // ==================== AI Writing Features API ====================
    // Prose Generation
    Route::prefix('scenes/{scene}')->group(function () {
        Route::post('generate-prose', [ProseGenerationController::class, 'generate'])->name('scenes.generate-prose');
    });

    Route::get('prose-generation/options', [ProseGenerationController::class, 'options'])->name('prose-generation.options');

    // Text Replacement
    Route::post('text/replace', [TextReplacementController::class, 'replace'])->name('text.replace');
    Route::get('text-replacement/options', [TextReplacementController::class, 'options'])->name('text-replacement.options');

    // ==================== Acts API ====================
    Route::prefix('novels/{novel}/acts')->group(function () {
        Route::get('/', [ActController::class, 'index'])->name('acts.index');
        Route::post('/', [ActController::class, 'store'])->name('acts.store');
        Route::post('reorder', [ActController::class, 'reorder'])->name('acts.reorder');
    });

    Route::prefix('acts/{act}')->group(function () {
        Route::patch('/', [ActController::class, 'update'])->name('acts.update');
        Route::delete('/', [ActController::class, 'destroy'])->name('acts.destroy');
    });

    // ==================== Scene Labels API ====================
    Route::prefix('novels/{novel}/labels')->group(function () {
        Route::get('/', [SceneLabelController::class, 'index'])->name('labels.index');
        Route::post('/', [SceneLabelController::class, 'store'])->name('labels.store');
    });

    Route::prefix('labels/{label}')->group(function () {
        Route::patch('/', [SceneLabelController::class, 'update'])->name('labels.update');
        Route::delete('/', [SceneLabelController::class, 'destroy'])->name('labels.destroy');
    });

    // ==================== AI Connection API ====================
    Route::prefix('ai-connections')->group(function () {
        Route::get('providers', [AIConnectionController::class, 'providers'])->name('ai-connections.providers');
        Route::get('/', [AIConnectionController::class, 'index'])->name('ai-connections.index');
        Route::post('/', [AIConnectionController::class, 'store'])->name('ai-connections.store');
    });

    Route::prefix('ai-connections/{aiConnection}')->group(function () {
        Route::get('/', [AIConnectionController::class, 'show'])->name('ai-connections.show');
        Route::patch('/', [AIConnectionController::class, 'update'])->name('ai-connections.update');
        Route::delete('/', [AIConnectionController::class, 'destroy'])->name('ai-connections.destroy');
        Route::post('test', [AIConnectionController::class, 'test'])->name('ai-connections.test');
        Route::get('models', [AIConnectionController::class, 'models'])->name('ai-connections.models');
    });

    // ==================== Chat API (Workshop) ====================
    Route::prefix('novels/{novel}/chat/threads')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chat.threads.index');
        Route::post('/', [ChatController::class, 'store'])->name('chat.threads.store');
    });

    Route::prefix('chat/threads/{thread}')->group(function () {
        Route::get('/', [ChatController::class, 'show'])->name('chat.threads.show');
        Route::patch('/', [ChatController::class, 'update'])->name('chat.threads.update');
        Route::delete('/', [ChatController::class, 'destroy'])->name('chat.threads.destroy');
        Route::post('archive', [ChatController::class, 'archive'])->name('chat.threads.archive');
        Route::post('restore', [ChatController::class, 'restore'])->name('chat.threads.restore');

        // Chat messages
        Route::get('messages', [ChatController::class, 'messages'])->name('chat.messages.index');
        Route::post('messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
        Route::post('regenerate', [ChatController::class, 'regenerate'])->name('chat.messages.regenerate');
    });

    Route::delete('chat/messages/{message}', [ChatController::class, 'deleteMessage'])->name('chat.messages.destroy');

    // Chat message actions (Transfer & Extract)
    Route::post('chat/messages/{message}/transfer', [ChatController::class, 'transfer'])->name('chat.messages.transfer');
    Route::post('chat/messages/{message}/extract', [ChatController::class, 'extract'])->name('chat.messages.extract');

    // ==================== Chat Context API ====================
    Route::prefix('chat/threads/{thread}/context')->group(function () {
        Route::get('/', [ChatContextController::class, 'index'])->name('chat.context.index');
        Route::post('/', [ChatContextController::class, 'store'])->name('chat.context.store');
        Route::get('preview', [ChatContextController::class, 'preview'])->name('chat.context.preview');
        Route::post('bulk', [ChatContextController::class, 'bulkAdd'])->name('chat.context.bulk');
        Route::delete('clear', [ChatContextController::class, 'clear'])->name('chat.context.clear');
    });

    Route::prefix('chat/context/{item}')->group(function () {
        Route::patch('/', [ChatContextController::class, 'update'])->name('chat.context.update');
        Route::delete('/', [ChatContextController::class, 'destroy'])->name('chat.context.destroy');
    });

    // Context sources for a novel
    Route::get('novels/{novel}/context-sources', [ChatContextController::class, 'sources'])->name('chat.context.sources');

    // ==================== Prompts API ====================
    Route::prefix('prompts')->group(function () {
        Route::get('/', [PromptController::class, 'list'])->name('prompts.list');
        Route::get('types', [PromptController::class, 'types'])->name('prompts.types');
        Route::get('type/{type}', [PromptController::class, 'byType'])->name('prompts.by-type');
        Route::post('/', [PromptController::class, 'store'])->name('prompts.store');
        Route::post('reorder', [PromptController::class, 'reorder'])->name('prompts.reorder');
    });

    Route::prefix('prompts/{prompt}')->group(function () {
        Route::get('/', [PromptController::class, 'show'])->name('prompts.show');
        Route::patch('/', [PromptController::class, 'update'])->name('prompts.update');
        Route::delete('/', [PromptController::class, 'destroy'])->name('prompts.destroy');
        Route::post('clone', [PromptController::class, 'clone'])->name('prompts.clone');
        Route::post('usage', [PromptController::class, 'recordUsage'])->name('prompts.usage');
        Route::get('export', [PromptSharingController::class, 'export'])->name('prompts.export');

        // Prompt Inputs
        Route::get('inputs', [PromptInputController::class, 'index'])->name('prompts.inputs.index');
        Route::post('inputs', [PromptInputController::class, 'store'])->name('prompts.inputs.store');
        Route::put('inputs/bulk', [PromptInputController::class, 'bulkUpdate'])->name('prompts.inputs.bulk');
        Route::patch('inputs/{input}', [PromptInputController::class, 'update'])->name('prompts.inputs.update');
        Route::delete('inputs/{input}', [PromptInputController::class, 'destroy'])->name('prompts.inputs.destroy');
    });

    // Prompt Sharing/Import
    Route::post('prompts/import/preview', [PromptSharingController::class, 'preview'])->name('prompts.import.preview');
    Route::post('prompts/import', [PromptSharingController::class, 'import'])->name('prompts.import');

    // ==================== Prompt Components API ====================
    Route::prefix('prompt-components')->group(function () {
        Route::get('/', [PromptComponentController::class, 'index'])->name('prompt-components.index');
        Route::post('/', [PromptComponentController::class, 'store'])->name('prompt-components.store');
    });

    Route::prefix('prompt-components/{component}')->group(function () {
        Route::get('/', [PromptComponentController::class, 'show'])->name('prompt-components.show');
        Route::patch('/', [PromptComponentController::class, 'update'])->name('prompt-components.update');
        Route::delete('/', [PromptComponentController::class, 'destroy'])->name('prompt-components.destroy');
        Route::post('clone', [PromptComponentController::class, 'clone'])->name('prompt-components.clone');
        Route::get('usages', [PromptComponentController::class, 'usages'])->name('prompt-components.usages');
    });

    // ==================== Prompt Personas API ====================
    Route::prefix('prompt-personas')->group(function () {
        Route::get('/', [PromptPersonaController::class, 'index'])->name('prompt-personas.index');
        Route::get('context', [PromptPersonaController::class, 'forContext'])->name('prompt-personas.context');
        Route::get('interaction-types', [PromptPersonaController::class, 'interactionTypes'])->name('prompt-personas.interaction-types');
        Route::post('/', [PromptPersonaController::class, 'store'])->name('prompt-personas.store');
    });

    Route::prefix('prompt-personas/{promptPersona}')->group(function () {
        Route::get('/', [PromptPersonaController::class, 'show'])->name('prompt-personas.show');
        Route::patch('/', [PromptPersonaController::class, 'update'])->name('prompt-personas.update');
        Route::delete('/', [PromptPersonaController::class, 'destroy'])->name('prompt-personas.destroy');
        Route::post('archive', [PromptPersonaController::class, 'archive'])->name('prompt-personas.archive');
        Route::post('restore', [PromptPersonaController::class, 'restore'])->name('prompt-personas.restore');
    });

    // ==================== Prompt Presets API ====================
    Route::prefix('prompt-presets')->group(function () {
        Route::get('/', [PromptPresetController::class, 'index'])->name('prompt-presets.index');
    });

    Route::prefix('prompt-presets/{promptPreset}')->group(function () {
        Route::get('/', [PromptPresetController::class, 'show'])->name('prompt-presets.show');
        Route::patch('/', [PromptPresetController::class, 'update'])->name('prompt-presets.update');
        Route::delete('/', [PromptPresetController::class, 'destroy'])->name('prompt-presets.destroy');
        Route::post('set-default', [PromptPresetController::class, 'setDefault'])->name('prompt-presets.set-default');
    });

    // Presets for a specific prompt
    Route::prefix('prompts/{prompt}/presets')->group(function () {
        Route::get('/', [PromptPresetController::class, 'forPrompt'])->name('prompts.presets.index');
        Route::post('/', [PromptPresetController::class, 'store'])->name('prompts.presets.store');
    });
});
