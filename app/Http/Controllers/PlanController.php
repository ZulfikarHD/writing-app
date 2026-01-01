<?php

namespace App\Http\Controllers;

use App\Models\Act;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\NovelPlanSettings;
use App\Models\Scene;
use App\Models\StoryTemplate;
use App\Services\Plan\MatrixDataBuilder;
use App\Services\Plan\OutlineParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function __construct(
        private MatrixDataBuilder $matrixBuilder,
        private OutlineParser $outlineParser
    ) {}

    /**
     * Get matrix data for the Matrix view.
     */
    public function matrix(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'show' => ['nullable', 'string', 'in:entries,pov,labels,custom'],
            'entry_type' => ['nullable', 'string', 'in:all,character,location,item,lore,organization,subplot'],
        ]);

        $data = $this->matrixBuilder->build(
            $novel,
            $validated['show'] ?? 'entries',
            $validated['entry_type'] ?? null
        );

        return response()->json($data);
    }

    /**
     * Search scenes within a novel.
     */
    public function search(Request $request, Novel $novel): JsonResponse
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

    /**
     * Get plan settings for the current user and novel.
     */
    public function getSettings(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $settings = NovelPlanSettings::getOrCreate($novel->id, $request->user()->id);

        return response()->json(['settings' => $settings]);
    }

    /**
     * Update plan settings for the current user and novel.
     */
    public function updateSettings(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'current_view' => ['nullable', 'string', 'in:grid,matrix,outline'],
            'matrix_mode' => ['nullable', 'string', 'in:codex,pov,labels,custom,subplot'],
            'grid_axis' => ['nullable', 'string', 'in:vertical,horizontal'],
            'card_width' => ['nullable', 'string', 'in:small,medium,large'],
            'card_height' => ['nullable', 'string', 'in:full,small,medium,large'],
            'show_auto_references' => ['nullable', 'boolean'],
            'custom_matrix_entries' => ['nullable', 'array'],
        ]);

        $settings = NovelPlanSettings::getOrCreate($novel->id, $request->user()->id);
        $settings->update(array_filter($validated, fn ($v) => $v !== null));

        return response()->json(['settings' => $settings]);
    }

    /**
     * Set POV for a scene (used by Matrix POV mode).
     */
    public function setScenePov(Request $request, Scene $scene): JsonResponse
    {
        $chapter = $scene->chapter;
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'pov_character_id' => ['nullable', 'integer', 'exists:codex_entries,id'],
            'pov_type' => ['nullable', 'string', 'in:1st_person,2nd_person,3rd_limited,3rd_omniscient'],
        ]);

        $scene->update($validated);

        return response()->json(['scene' => $scene->fresh()]);
    }

    /**
     * Set label for a scene (used by Matrix Label mode).
     */
    public function setSceneLabel(Request $request, Scene $scene): JsonResponse
    {
        $chapter = $scene->chapter;
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label_ids' => ['required', 'array'],
            'label_ids.*' => ['integer', 'exists:scene_labels,id'],
        ]);

        $scene->labels()->sync($validated['label_ids']);

        return response()->json([
            'scene' => $scene->fresh(['labels']),
        ]);
    }

    /**
     * Get all story templates (system + user's custom).
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $templates = StoryTemplate::forUser($request->user()->id)
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        // If no system templates exist, create them
        if ($templates->where('is_system', true)->isEmpty()) {
            $this->seedSystemTemplates();
            $templates = StoryTemplate::forUser($request->user()->id)
                ->orderBy('is_system', 'desc')
                ->orderBy('name')
                ->get();
        }

        return response()->json(['templates' => $templates]);
    }

    /**
     * Parse outline text and return preview.
     */
    public function parseOutline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outline' => ['required', 'string', 'max:50000'],
        ]);

        $parsed = $this->outlineParser->parse($validated['outline']);

        if (! $this->outlineParser->validate($parsed)) {
            return response()->json([
                'error' => 'Could not parse outline. Please check your formatting.',
            ], 422);
        }

        return response()->json(['preview' => $parsed]);
    }

    /**
     * Create structure from outline (or template).
     */
    public function createFromOutline(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'structure' => ['required', 'array'],
            'structure.acts' => ['required', 'array', 'min:1'],
            'structure.acts.*.title' => ['required', 'string', 'max:255'],
            'structure.acts.*.chapters' => ['nullable', 'array'],
            'structure.acts.*.chapters.*.title' => ['required', 'string', 'max:255'],
            'structure.acts.*.chapters.*.summary' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // Get current max positions
            $maxActPosition = $novel->acts()->max('position') ?? -1;
            $maxChapterPosition = $novel->chapters()->max('position') ?? -1;

            foreach ($validated['structure']['acts'] as $actIndex => $actData) {
                $act = $novel->acts()->create([
                    'title' => $actData['title'],
                    'position' => $maxActPosition + $actIndex + 1,
                ]);

                foreach ($actData['chapters'] ?? [] as $chapterIndex => $chapterData) {
                    $chapter = $novel->chapters()->create([
                        'act_id' => $act->id,
                        'title' => $chapterData['title'],
                        'position' => $maxChapterPosition + 1,
                    ]);
                    $maxChapterPosition++;

                    // Create a scene with the summary if provided
                    if (! empty($chapterData['summary'])) {
                        $chapter->scenes()->create([
                            'title' => 'Scene 1',
                            'summary' => $chapterData['summary'],
                            'position' => 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Structure created successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create structure: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update scene POV from Matrix view.
     */
    public function bulkSetPov(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'scene_ids' => ['required', 'array'],
            'scene_ids.*' => ['integer'],
            'pov_character_id' => ['nullable', 'integer', 'exists:codex_entries,id'],
            'pov_type' => ['nullable', 'string', 'in:1st_person,2nd_person,3rd_limited,3rd_omniscient'],
        ]);

        // Verify scenes belong to this novel
        $sceneIds = $novel->scenes()->whereIn('scenes.id', $validated['scene_ids'])->pluck('scenes.id');

        Scene::whereIn('id', $sceneIds)->update([
            'pov_character_id' => $validated['pov_character_id'],
            'pov_type' => $validated['pov_type'] ?? null,
        ]);

        return response()->json(['success' => true, 'updated' => $sceneIds->count()]);
    }

    /**
     * Seed system templates if not exist.
     */
    private function seedSystemTemplates(): void
    {
        $templates = StoryTemplate::getDefaultTemplates();

        foreach ($templates as $template) {
            StoryTemplate::firstOrCreate(
                ['name' => $template['name'], 'is_system' => true],
                [
                    'description' => $template['description'],
                    'structure' => $template['structure'],
                    'is_system' => true,
                ]
            );
        }
    }

    /**
     * Toggle numeration for act.
     */
    public function toggleActNumeration(Request $request, Act $act): JsonResponse
    {
        if ($act->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $act->update(['disable_numeration' => ! $act->disable_numeration]);

        return response()->json(['act' => $act]);
    }

    /**
     * Toggle numeration for chapter.
     */
    public function toggleChapterNumeration(Request $request, Chapter $chapter): JsonResponse
    {
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $chapter->update(['disable_numeration' => ! $chapter->disable_numeration]);

        return response()->json(['chapter' => $chapter]);
    }

    /**
     * Copy all prose from an act.
     */
    public function copyActProse(Request $request, Act $act): JsonResponse
    {
        if ($act->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $prose = [];
        foreach ($act->chapters as $chapter) {
            foreach ($chapter->scenes as $scene) {
                if ($scene->content) {
                    $prose[] = $this->extractTextFromContent($scene->content);
                }
            }
        }

        return response()->json(['prose' => implode("\n\n", $prose)]);
    }

    /**
     * Copy all outlines/summaries from an act.
     */
    public function copyActOutlines(Request $request, Act $act): JsonResponse
    {
        if ($act->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $outlines = [];
        foreach ($act->chapters as $chapter) {
            $outlines[] = "## {$chapter->title}";
            foreach ($chapter->scenes as $scene) {
                $title = $scene->title ?? 'Untitled Scene';
                $summary = $scene->summary ?? '';
                $outlines[] = "- **{$title}**: {$summary}";
            }
        }

        return response()->json(['outlines' => implode("\n", $outlines)]);
    }

    /**
     * Delete empty scenes from a novel.
     */
    public function deleteEmptyScenes(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $deleted = $novel->scenes()
            ->whereNull('content')
            ->whereNull('summary')
            ->where(function ($q) {
                $q->whereNull('title')->orWhere('title', '');
            })
            ->delete();

        return response()->json(['deleted' => $deleted]);
    }

    /**
     * Extract plain text from TipTap JSON content.
     *
     * @param  array<string, mixed>|null  $content
     */
    private function extractTextFromContent(?array $content): string
    {
        if (empty($content)) {
            return '';
        }

        $text = '';

        if (isset($content['text'])) {
            $text .= $content['text'].' ';
        }

        if (isset($content['content']) && is_array($content['content'])) {
            foreach ($content['content'] as $node) {
                $text .= $this->extractTextFromContent($node);
            }
        }

        return trim($text);
    }
}
