<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    /**
     * Display the unified workspace for a novel.
     */
    public function show(Request $request, Novel $novel, ?Scene $scene = null): Response
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load novel with all necessary relationships for the workspace
        $novel->load([
            'acts' => fn ($q) => $q->orderBy('position'),
            'chapters' => fn ($q) => $q->orderBy('position'),
            'chapters.scenes' => fn ($q) => $q->active()->orderBy('position'),
            'chapters.scenes.labels',
            'chapters.scenes.codexMentions',
            'labels' => fn ($q) => $q->orderBy('position'),
        ]);

        // Get or create default scene if none specified
        $activeScene = $scene;
        if (! $activeScene) {
            $activeScene = $this->getOrCreateDefaultScene($novel);
        }

        // Load labels for active scene
        if ($activeScene) {
            $activeScene->load('labels');
        }

        // Mark onboarding as editor toured if first time
        $onboardingState = $request->user()->onboardingState;
        if ($onboardingState && ! $onboardingState->editor_toured) {
            $onboardingState->update(['editor_toured' => true]);
        }

        // Determine initial mode from query parameter
        $mode = $request->query('mode', 'write');
        if (! in_array($mode, ['write', 'plan', 'codex'])) {
            $mode = 'write';
        }

        return Inertia::render('Workspace/Index', [
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
                'series_id' => $novel->series_id,
                'pov' => $novel->pov,
                'tense' => $novel->tense,
                'word_count' => $novel->word_count,
            ],
            'chapters' => $this->formatChapters($novel->chapters),
            'acts' => $novel->acts->map(fn ($act) => [
                'id' => $act->id,
                'title' => $act->title,
                'position' => $act->position,
            ]),
            'activeScene' => $activeScene ? $this->formatActiveScene($activeScene) : null,
            'labels' => $novel->labels->map(fn ($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
                'position' => $label->position,
            ]),
            'initialMode' => $mode,
        ]);
    }

    /**
     * Format chapters with their scenes for both editor sidebar and plan view.
     */
    private function formatChapters($chapters): array
    {
        return $chapters->map(fn (Chapter $chapter) => [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'position' => $chapter->position,
            'act_id' => $chapter->act_id,
            'word_count' => $chapter->word_count,
            'scenes' => $chapter->scenes->map(fn (Scene $scene) => [
                'id' => $scene->id,
                'chapter_id' => $scene->chapter_id,
                'title' => $scene->title,
                'summary' => $scene->summary,
                'position' => $scene->position,
                'status' => $scene->status,
                'word_count' => $scene->word_count,
                'pov_character_id' => $scene->pov_character_id,
                'subtitle' => $scene->subtitle,
                'labels' => $scene->labels->map(fn ($label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ]),
                'codex_mentions_count' => $scene->codexMentions->sum('mention_count'),
                'codex_entries_count' => $scene->codexMentions->count(),
            ]),
        ])->toArray();
    }

    /**
     * Format the active scene for the editor.
     */
    private function formatActiveScene(Scene $scene): array
    {
        return [
            'id' => $scene->id,
            'chapter_id' => $scene->chapter_id,
            'title' => $scene->title,
            'content' => $scene->content,
            'summary' => $scene->summary,
            'status' => $scene->status,
            'word_count' => $scene->word_count,
            'subtitle' => $scene->subtitle,
            'notes' => $scene->notes,
            'pov_character_id' => $scene->pov_character_id,
            'exclude_from_ai' => $scene->exclude_from_ai,
            'labels' => $scene->labels->map(fn ($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ]),
        ];
    }

    /**
     * Get or create a default scene for a novel.
     */
    private function getOrCreateDefaultScene(Novel $novel): ?Scene
    {
        // Try to get first scene from first chapter
        $firstChapter = $novel->chapters->first();

        if ($firstChapter && $firstChapter->scenes->isNotEmpty()) {
            return $firstChapter->scenes->first();
        }

        // Create default chapter and scene if none exist
        if (! $firstChapter) {
            $firstChapter = $novel->chapters()->create([
                'title' => 'Chapter 1',
                'position' => 0,
            ]);
        }

        // Create default scene
        return $firstChapter->scenes()->create([
            'title' => 'Scene 1',
            'position' => 0,
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                    ],
                ],
            ],
        ]);
    }
}
