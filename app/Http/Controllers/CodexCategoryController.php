<?php

namespace App\Http\Controllers;

use App\Models\CodexCategory;
use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->with('children')
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
        ]);

        // Set default sort order
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $novel->codexCategories()
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('sort_order') + 1;
        }

        $category = $novel->codexCategories()->create($validated);

        return response()->json([
            'category' => $this->formatCategory($category),
        ], 201);
    }

    /**
     * Update a category.
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
        ]);

        // Prevent category from being its own parent
        if (isset($validated['parent_id']) && $validated['parent_id'] === $category->id) {
            return response()->json(['message' => 'Category cannot be its own parent'], 422);
        }

        $category->update($validated);

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
     * Format category for API response.
     *
     * @return array<string, mixed>
     */
    private function formatCategory(CodexCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'children' => $category->children->map(fn ($child) => $this->formatCategory($child)),
            'entry_count' => $category->entries()->count(),
        ];
    }
}
