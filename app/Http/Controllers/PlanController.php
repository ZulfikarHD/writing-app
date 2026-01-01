<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function show(Request $request, Novel $novel): Response
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load novel with acts, chapters, scenes, and labels
        $novel->load([
            'acts' => fn ($q) => $q->orderBy('position'),
            'chapters' => fn ($q) => $q->orderBy('position')->with([
                'scenes' => fn ($q) => $q->active()->orderBy('position')->with('labels'),
                'act',
            ]),
            'labels' => fn ($q) => $q->orderBy('position'),
        ]);

        // Format chapters with their scenes for the plan view
        $chapters = $novel->chapters->map(fn ($chapter) => [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'position' => $chapter->position,
            'act_id' => $chapter->act_id,
            'word_count' => $chapter->word_count,
            'scenes' => $chapter->scenes->map(fn ($scene) => [
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
            ]),
        ]);

        // Format acts
        $acts = $novel->acts->map(fn ($act) => [
            'id' => $act->id,
            'title' => $act->title,
            'position' => $act->position,
        ]);

        // Format labels
        $labels = $novel->labels->map(fn ($label) => [
            'id' => $label->id,
            'name' => $label->name,
            'color' => $label->color,
            'position' => $label->position,
        ]);

        return Inertia::render('Plan/Index', [
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
                'word_count' => $novel->word_count,
            ],
            'chapters' => $chapters,
            'acts' => $acts,
            'labels' => $labels,
        ]);
    }

    /**
     * Search scenes within a novel.
     */
    public function search(Request $request, Novel $novel)
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,in_progress,completed,needs_revision'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:scene_labels,id'],
        ]);

        $query = $novel->scenes()
            ->active()
            ->with(['chapter', 'labels']);

        // Search by title or summary
        if (! empty($validated['q'])) {
            $searchTerm = '%'.$validated['q'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('scenes.title', 'like', $searchTerm)
                    ->orWhere('scenes.summary', 'like', $searchTerm);
            });
        }

        // Filter by status
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filter by labels
        if (! empty($validated['label_ids'])) {
            $query->whereHas('labels', fn ($q) => $q->whereIn('scene_labels.id', $validated['label_ids']));
        }

        $scenes = $query->orderBy('position')->get();

        return response()->json([
            'scenes' => $scenes->map(fn ($scene) => [
                'id' => $scene->id,
                'chapter_id' => $scene->chapter_id,
                'chapter_title' => $scene->chapter->title,
                'title' => $scene->title,
                'summary' => $scene->summary,
                'position' => $scene->position,
                'status' => $scene->status,
                'word_count' => $scene->word_count,
                'labels' => $scene->labels->map(fn ($label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ]),
            ]),
        ]);
    }
}
