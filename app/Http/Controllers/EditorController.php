<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditorController extends Controller
{
    public function show(Request $request, Novel $novel, ?Scene $scene = null): Response
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load chapters with their scenes and labels
        $novel->load([
            'chapters.scenes' => function ($query) {
                $query->active()->orderBy('position');
            },
            'labels' => function ($query) {
                $query->orderBy('position');
            },
        ]);

        // If no scene is specified, get the first one or create a default chapter/scene
        $activeScene = $scene;
        if (! $activeScene) {
            $activeScene = $this->getOrCreateDefaultScene($novel);
        }

        // Mark onboarding as editor toured if first time
        $onboardingState = $request->user()->onboardingState;
        if ($onboardingState && ! $onboardingState->editor_toured) {
            $onboardingState->update(['editor_toured' => true]);
        }

        return Inertia::render('Editor/Index', [
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
                'pov' => $novel->pov,
                'tense' => $novel->tense,
            ],
            'chapters' => $novel->chapters->map(fn (Chapter $chapter) => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
                'scenes' => $chapter->scenes->map(fn (Scene $s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'position' => $s->position,
                    'status' => $s->status,
                    'word_count' => $s->word_count,
                ]),
            ]),
            'activeScene' => $activeScene ? [
                'id' => $activeScene->id,
                'chapter_id' => $activeScene->chapter_id,
                'title' => $activeScene->title,
                'content' => $activeScene->content,
                'summary' => $activeScene->summary,
                'status' => $activeScene->status,
                'word_count' => $activeScene->word_count,
                'subtitle' => $activeScene->subtitle,
                'notes' => $activeScene->notes,
                'pov_character_id' => $activeScene->pov_character_id,
                'exclude_from_ai' => $activeScene->exclude_from_ai,
                'labels' => $activeScene->labels->map(fn ($label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ]),
            ] : null,
            'labels' => $novel->labels->map(fn ($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ]),
        ]);
    }

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
