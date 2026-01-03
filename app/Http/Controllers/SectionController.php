<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\SceneSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * List all sections for a scene.
     */
    public function index(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $sections = $scene->sections()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SceneSection $section) => $this->formatSection($section));

        return response()->json([
            'sections' => $sections,
        ]);
    }

    /**
     * Create a new section in a scene.
     */
    public function store(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:content,note,alternative,beat'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Set defaults based on type
        $type = $validated['type'] ?? SceneSection::TYPE_CONTENT;

        // Get max sort_order if not provided
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = ($scene->sections()->max('sort_order') ?? -1) + 1;
        }

        // Set default color based on type if not provided
        if (! isset($validated['color'])) {
            $validated['color'] = SceneSection::TYPE_COLORS[$type];
        }

        // Set default exclude_from_ai based on type
        $excludeFromAi = SceneSection::getDefaultExcludeFromAi($type);

        $section = $scene->sections()->create([
            'type' => $type,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'] ?? [
                'type' => 'doc',
                'content' => [['type' => 'paragraph']],
            ],
            'color' => $validated['color'],
            'sort_order' => $validated['sort_order'],
            'exclude_from_ai' => $excludeFromAi,
        ]);

        return response()->json([
            'section' => $this->formatSection($section),
        ], 201);
    }

    /**
     * Get a single section.
     */
    public function show(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'section' => $this->formatSection($section),
        ]);
    }

    /**
     * Update a section.
     */
    public function update(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:content,note,alternative,beat'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'nullable', 'array'],
            'color' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_collapsed' => ['sometimes', 'boolean'],
            'exclude_from_ai' => ['sometimes', 'boolean'],
        ]);

        $section->update($validated);

        return response()->json([
            'section' => $this->formatSection($section->fresh()),
        ]);
    }

    /**
     * Delete a section.
     */
    public function destroy(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $section->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder sections within a scene.
     */
    public function reorder(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.id' => ['required', 'integer', 'exists:scene_sections,id'],
            'sections.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['sections'] as $sectionData) {
            SceneSection::where('id', $sectionData['id'])
                ->where('scene_id', $scene->id)
                ->update(['sort_order' => $sectionData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle collapse state of a section.
     */
    public function toggleCollapse(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $section->update([
            'is_collapsed' => ! $section->is_collapsed,
        ]);

        return response()->json([
            'section' => $this->formatSection($section->fresh()),
        ]);
    }

    /**
     * Toggle AI visibility of a section.
     */
    public function toggleAiVisibility(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $section->update([
            'exclude_from_ai' => ! $section->exclude_from_ai,
        ]);

        return response()->json([
            'section' => $this->formatSection($section->fresh()),
        ]);
    }

    /**
     * Dissolve a section - merge its content back to the scene.
     */
    public function dissolve(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $scene = $section->scene;
        $sectionContent = $section->content;

        // Delete the section
        $section->delete();

        // The content can be returned to the frontend to handle insertion
        return response()->json([
            'success' => true,
            'dissolved_content' => $sectionContent,
        ]);
    }

    /**
     * Duplicate a section.
     */
    public function duplicate(Request $request, SceneSection $section): JsonResponse
    {
        // Ensure user owns this section's scene's novel
        if ($section->scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Get the next sort_order
        $nextSortOrder = $section->scene->sections()->max('sort_order') + 1;

        // Create the duplicate
        $duplicate = $section->replicate();
        $duplicate->title = ($section->title ?? 'Untitled').' (Copy)';
        $duplicate->sort_order = $nextSortOrder;
        $duplicate->save();

        return response()->json([
            'section' => $this->formatSection($duplicate),
        ], 201);
    }

    /**
     * Format section for API response.
     */
    private function formatSection(SceneSection $section): array
    {
        return [
            'id' => $section->id,
            'scene_id' => $section->scene_id,
            'type' => $section->type,
            'title' => $section->title,
            'content' => $section->content,
            'color' => $section->color,
            'is_collapsed' => $section->is_collapsed,
            'exclude_from_ai' => $section->exclude_from_ai,
            'sort_order' => $section->sort_order,
            'word_count' => $section->calculateWordCount(),
            'created_at' => $section->created_at?->toIso8601String(),
            'updated_at' => $section->updated_at?->toIso8601String(),
        ];
    }
}
