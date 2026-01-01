<?php

namespace App\Http\Controllers;

use App\Models\CodexDetailDefinition;
use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CodexDetailDefinitionController - Sprint 14 (US-12.5, US-12.7)
 *
 * Handles detail definition management for the Codex system.
 * Definitions are templates that specify the type, options, and AI visibility
 * for codex details.
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create
 */
class CodexDetailDefinitionController extends Controller
{
    /**
     * List all detail definitions for a novel (including system presets).
     */
    public function index(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Get custom definitions for this novel
        $query = CodexDetailDefinition::where('novel_id', $novel->id);

        // Filter by entry type if specified
        if ($request->filled('entry_type')) {
            $query->forEntryType($request->entry_type);
        }

        $customDefinitions = $query->orderBy('sort_order')->orderBy('name')->get();

        // Get system presets (filter by entry type if specified)
        $presets = collect(CodexDetailDefinition::SYSTEM_PRESETS);
        if ($request->filled('entry_type')) {
            $presets = $presets->filter(function ($preset) use ($request) {
                return $preset['entry_types'] === null ||
                    in_array($request->entry_type, $preset['entry_types'], true);
            });
        }

        return response()->json([
            'definitions' => $customDefinitions->map(fn (CodexDetailDefinition $def) => [
                'id' => $def->id,
                'name' => $def->name,
                'type' => $def->type,
                'options' => $def->options,
                'entry_types' => $def->entry_types,
                'show_in_sidebar' => $def->show_in_sidebar,
                'ai_visibility' => $def->ai_visibility,
                'is_preset' => false,
            ]),
            'presets' => $presets->values()->map(fn ($preset, $index) => [
                'id' => 'preset_'.$index, // Virtual ID for presets
                'name' => $preset['name'],
                'type' => $preset['type'],
                'options' => $preset['options'],
                'entry_types' => $preset['entry_types'],
                'show_in_sidebar' => $preset['show_in_sidebar'],
                'ai_visibility' => $preset['ai_visibility'],
                'is_preset' => true,
            ]),
        ]);
    }

    /**
     * Create a new detail definition for a novel.
     */
    public function store(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:'.implode(',', CodexDetailDefinition::getTypes()),
            'options' => 'nullable|array',
            'options.*' => 'string|max:100',
            'entry_types' => 'nullable|array',
            'entry_types.*' => 'string|in:'.implode(',', CodexEntry::getTypes()),
            'show_in_sidebar' => 'boolean',
            'ai_visibility' => 'string|in:'.implode(',', CodexDetailDefinition::getAiVisibilityModes()),
        ]);

        // Validate dropdown has options
        if ($validated['type'] === CodexDetailDefinition::TYPE_DROPDOWN &&
            (empty($validated['options']) || count($validated['options']) < 2)) {
            return response()->json([
                'message' => 'Dropdown type requires at least 2 options.',
            ], 422);
        }

        // Set sort order
        $maxSortOrder = CodexDetailDefinition::where('novel_id', $novel->id)->max('sort_order') ?? 0;

        $definition = CodexDetailDefinition::create([
            'novel_id' => $novel->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'options' => $validated['options'] ?? null,
            'entry_types' => $validated['entry_types'] ?? null,
            'show_in_sidebar' => $validated['show_in_sidebar'] ?? false,
            'ai_visibility' => $validated['ai_visibility'] ?? CodexDetailDefinition::AI_VISIBILITY_ALWAYS,
            'sort_order' => $maxSortOrder + 1,
            'is_preset' => false,
        ]);

        return response()->json([
            'definition' => [
                'id' => $definition->id,
                'name' => $definition->name,
                'type' => $definition->type,
                'options' => $definition->options,
                'entry_types' => $definition->entry_types,
                'show_in_sidebar' => $definition->show_in_sidebar,
                'ai_visibility' => $definition->ai_visibility,
                'is_preset' => false,
            ],
        ], 201);
    }

    /**
     * Update an existing detail definition.
     */
    public function update(Request $request, CodexDetailDefinition $definition): JsonResponse
    {
        if ($definition->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Cannot edit system presets
        if ($definition->is_preset) {
            return response()->json([
                'message' => 'System presets cannot be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|string|in:'.implode(',', CodexDetailDefinition::getTypes()),
            'options' => 'nullable|array',
            'options.*' => 'string|max:100',
            'entry_types' => 'nullable|array',
            'entry_types.*' => 'string|in:'.implode(',', CodexEntry::getTypes()),
            'show_in_sidebar' => 'boolean',
            'ai_visibility' => 'string|in:'.implode(',', CodexDetailDefinition::getAiVisibilityModes()),
        ]);

        // Validate dropdown has options if type is dropdown
        $type = $validated['type'] ?? $definition->type;
        if ($type === CodexDetailDefinition::TYPE_DROPDOWN) {
            $options = $validated['options'] ?? $definition->options;
            if (empty($options) || count($options) < 2) {
                return response()->json([
                    'message' => 'Dropdown type requires at least 2 options.',
                ], 422);
            }
        }

        $definition->update($validated);

        return response()->json([
            'definition' => [
                'id' => $definition->id,
                'name' => $definition->name,
                'type' => $definition->type,
                'options' => $definition->options,
                'entry_types' => $definition->entry_types,
                'show_in_sidebar' => $definition->show_in_sidebar,
                'ai_visibility' => $definition->ai_visibility,
                'is_preset' => false,
            ],
        ]);
    }

    /**
     * Delete a detail definition.
     */
    public function destroy(Request $request, CodexDetailDefinition $definition): JsonResponse
    {
        if ($definition->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Cannot delete system presets
        if ($definition->is_preset) {
            return response()->json([
                'message' => 'System presets cannot be deleted.',
            ], 422);
        }

        // Check if any details use this definition
        $usageCount = $definition->details()->count();
        if ($usageCount > 0) {
            return response()->json([
                'message' => "This definition is used by {$usageCount} detail(s). Remove those details first or they will be converted to basic text details.",
                'usage_count' => $usageCount,
            ], 422);
        }

        $definition->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get a single system preset by index.
     * Used when creating a detail from a preset.
     */
    public function getPreset(Request $request, int $index): JsonResponse
    {
        $presets = CodexDetailDefinition::SYSTEM_PRESETS;

        if (! isset($presets[$index])) {
            return response()->json([
                'message' => 'Preset not found.',
            ], 404);
        }

        $preset = $presets[$index];

        return response()->json([
            'preset' => [
                'id' => 'preset_'.$index,
                'name' => $preset['name'],
                'type' => $preset['type'],
                'options' => $preset['options'],
                'entry_types' => $preset['entry_types'],
                'show_in_sidebar' => $preset['show_in_sidebar'],
                'ai_visibility' => $preset['ai_visibility'],
                'is_preset' => true,
            ],
        ]);
    }
}
