<?php

namespace App\Http\Controllers;

use App\Models\CodexCategory;
use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CodexCategoryController
 *
 * Sprint 16 (US-12.13): Added tag integration support.
 * Categories can now be linked to tags or detail values for auto-linking entries.
 */
class CodexCategoryController extends Controller
{
    /**
     * List all categories for a novel.
     */
    public function index(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $categories = $novel->codexCategories()
            ->whereNull('parent_id')
            ->with(['children', 'linkedTag', 'linkedDetailDefinition'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories->map(fn (CodexCategory $cat) => $this->formatCategory($cat)),
        ]);
    }

    /**
     * Create a new category.
     */
    public function store(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'parent_id' => ['nullable', 'integer', 'exists:codex_categories,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            // Sprint 16: Tag integration
            'linked_tag_id' => ['nullable', 'integer', 'exists:codex_tags,id'],
            'linked_detail_definition_id' => ['nullable', 'integer', 'exists:codex_detail_definitions,id'],
            'linked_detail_value' => ['nullable', 'string', 'max:255'],
        ]);

        // Set default sort order
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $novel->codexCategories()
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('sort_order') + 1;
        }

        $category = $novel->codexCategories()->create($validated);
        $category->load(['linkedTag', 'linkedDetailDefinition']);

        return response()->json([
            'category' => $this->formatCategory($category),
        ], 201);
    }

    /**
     * Update a category.
     * Sprint 16: Now accepts linked_tag_id, linked_detail_definition_id, linked_detail_value.
     */
    public function update(Request $request, CodexCategory $category): JsonResponse
    {
        if ($category->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'parent_id' => ['nullable', 'integer', 'exists:codex_categories,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            // Sprint 16: Tag integration
            'linked_tag_id' => ['nullable', 'integer', 'exists:codex_tags,id'],
            'linked_detail_definition_id' => ['nullable', 'integer', 'exists:codex_detail_definitions,id'],
            'linked_detail_value' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent category from being its own parent
        if (isset($validated['parent_id']) && $validated['parent_id'] === $category->id) {
            return response()->json(['message' => 'Category cannot be its own parent'], 422);
        }

        // Handle clearing linked tag/detail when null is explicitly sent
        if (array_key_exists('linked_tag_id', $validated) && $validated['linked_tag_id'] === null) {
            $category->linked_tag_id = null;
        }
        if (array_key_exists('linked_detail_definition_id', $validated) && $validated['linked_detail_definition_id'] === null) {
            $category->linked_detail_definition_id = null;
            $category->linked_detail_value = null;
        }

        $category->update($validated);
        $category->load(['linkedTag', 'linkedDetailDefinition']);

        return response()->json([
            'category' => $this->formatCategory($category),
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(Request $request, CodexCategory $category): JsonResponse
    {
        if ($category->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Move children to parent level
        $category->children()->update(['parent_id' => $category->parent_id]);

        $category->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Assign categories to an entry.
     */
    public function assignToEntry(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['integer', 'exists:codex_categories,id'],
        ]);

        // Verify all categories belong to the same novel
        $validCategoryIds = CodexCategory::whereIn('id', $validated['category_ids'])
            ->where('novel_id', $entry->novel_id)
            ->pluck('id')
            ->all();

        $entry->categories()->sync($validCategoryIds);

        return response()->json([
            'categories' => $entry->categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'color' => $cat->color,
            ]),
        ]);
    }

    /**
     * Sprint 16 (US-12.13): Preview entries that would auto-link to a category.
     *
     * GET /api/codex/categories/{category}/preview-entries
     */
    public function previewEntries(Request $request, CodexCategory $category): JsonResponse
    {
        if ($category->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $autoLinkedEntries = $category->getAutoLinkedEntries();

        return response()->json([
            'entries' => $autoLinkedEntries->map(fn (CodexEntry $entry) => [
                'id' => $entry->id,
                'name' => $entry->name,
                'type' => $entry->type,
            ]),
            'count' => $autoLinkedEntries->count(),
            'has_auto_linking' => $category->hasAutoLinking(),
        ]);
    }

    /**
     * Format category for API response.
     * Sprint 16: Added tag integration fields.
     *
     * @return array<string, mixed>
     */
    private function formatCategory(CodexCategory $category): array
    {
        $manualCount = $category->entries()->count();
        $autoCount = $category->getAutoLinkedEntries()->count();

        return [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'children' => $category->children->map(fn ($child) => $this->formatCategory($child)),
            'entry_count' => $manualCount,
            'total_entry_count' => $category->getTotalEntryCount(),
            // Sprint 16: Tag integration fields
            'linked_tag_id' => $category->linked_tag_id,
            'linked_tag' => $category->linkedTag ? [
                'id' => $category->linkedTag->id,
                'name' => $category->linkedTag->name,
                'color' => $category->linkedTag->color,
            ] : null,
            'linked_detail_definition_id' => $category->linked_detail_definition_id,
            'linked_detail_definition' => $category->linkedDetailDefinition ? [
                'id' => $category->linkedDetailDefinition->id,
                'name' => $category->linkedDetailDefinition->name,
                'type' => $category->linkedDetailDefinition->type,
                'options' => $category->linkedDetailDefinition->options,
            ] : null,
            'linked_detail_value' => $category->linked_detail_value,
            'has_auto_linking' => $category->hasAutoLinking(),
            'auto_linked_count' => $autoCount,
        ];
    }
}
