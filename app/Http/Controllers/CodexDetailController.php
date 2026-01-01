<?php

namespace App\Http\Controllers;

use App\Models\CodexDetail;
use App\Models\CodexDetailDefinition;
use App\Models\CodexEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CodexDetailController
 *
 * Handles codex detail CRUD operations.
 * Sprint 14 adds support for detail types, definitions, and AI visibility.
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details
 */
class CodexDetailController extends Controller
{
    /**
     * List all details for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->load('details.definition');

        return response()->json([
            'details' => $entry->details->map(fn (CodexDetail $detail) => $this->formatDetail($detail)),
        ]);
    }

    /**
     * Add a new detail to an entry.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'key_name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            // Sprint 14 fields
            'definition_id' => ['nullable', 'integer', 'exists:codex_detail_definitions,id'],
            'ai_visibility' => ['nullable', 'string', 'in:'.implode(',', CodexDetail::getAiVisibilityModes())],
            'type' => ['nullable', 'string', 'in:'.implode(',', CodexDetail::getTypes())],
        ]);

        // Set default sort order if not provided
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $entry->details()->max('sort_order') + 1;
        }

        // Validate definition belongs to the same novel
        if (isset($validated['definition_id'])) {
            $definition = CodexDetailDefinition::find($validated['definition_id']);
            if ($definition && $definition->novel_id !== null && $definition->novel_id !== $entry->novel_id) {
                return response()->json([
                    'message' => 'Definition does not belong to this novel.',
                ], 422);
            }
        }

        // Set defaults for Sprint 14 fields
        $validated['ai_visibility'] = $validated['ai_visibility'] ?? CodexDetail::AI_VISIBILITY_ALWAYS;
        $validated['type'] = $validated['type'] ?? CodexDetail::TYPE_TEXT;

        $detail = $entry->details()->create($validated);
        $detail->load('definition');

        return response()->json([
            'detail' => $this->formatDetail($detail),
        ], 201);
    }

    /**
     * Update a detail.
     */
    public function update(Request $request, CodexDetail $detail): JsonResponse
    {
        if ($detail->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'key_name' => ['sometimes', 'string', 'max:255'],
            'value' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            // Sprint 14 fields
            'definition_id' => ['nullable', 'integer', 'exists:codex_detail_definitions,id'],
            'ai_visibility' => ['sometimes', 'string', 'in:'.implode(',', CodexDetail::getAiVisibilityModes())],
            'type' => ['sometimes', 'string', 'in:'.implode(',', CodexDetail::getTypes())],
        ]);

        // Validate definition belongs to the same novel
        if (isset($validated['definition_id'])) {
            $definition = CodexDetailDefinition::find($validated['definition_id']);
            if ($definition && $definition->novel_id !== null && $definition->novel_id !== $detail->entry->novel_id) {
                return response()->json([
                    'message' => 'Definition does not belong to this novel.',
                ], 422);
            }
        }

        $detail->update($validated);
        $detail->load('definition');

        return response()->json([
            'detail' => $this->formatDetail($detail),
        ]);
    }

    /**
     * Remove a detail.
     */
    public function destroy(Request $request, CodexDetail $detail): JsonResponse
    {
        if ($detail->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $detail->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder details for an entry.
     */
    public function reorder(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'details' => ['required', 'array'],
            'details.*.id' => ['required', 'integer', 'exists:codex_details,id'],
            'details.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['details'] as $detailData) {
            CodexDetail::where('id', $detailData['id'])
                ->where('codex_entry_id', $entry->id)
                ->update(['sort_order' => $detailData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Create a detail from a system preset.
     */
    public function storeFromPreset(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'preset_index' => ['required', 'integer', 'min:0'],
            'value' => ['nullable', 'string'],
        ]);

        $presets = CodexDetailDefinition::SYSTEM_PRESETS;
        if (! isset($presets[$validated['preset_index']])) {
            return response()->json([
                'message' => 'Preset not found.',
            ], 404);
        }

        $preset = $presets[$validated['preset_index']];

        // Check if preset is valid for this entry type
        if ($preset['entry_types'] !== null && ! in_array($entry->type, $preset['entry_types'], true)) {
            return response()->json([
                'message' => 'This preset is not available for '.$entry->type.' entries.',
            ], 422);
        }

        // Determine default value
        $value = $validated['value'] ?? '';
        if (empty($value) && $preset['type'] === CodexDetailDefinition::TYPE_DROPDOWN && ! empty($preset['options'])) {
            $value = $preset['options'][0]; // Default to first option
        }

        $detail = $entry->details()->create([
            'key_name' => $preset['name'],
            'value' => $value,
            'sort_order' => $entry->details()->max('sort_order') + 1,
            'ai_visibility' => $preset['ai_visibility'],
            'type' => $preset['type'],
        ]);

        return response()->json([
            'detail' => $this->formatDetail($detail),
            'preset' => [
                'name' => $preset['name'],
                'type' => $preset['type'],
                'options' => $preset['options'],
            ],
        ], 201);
    }

    /**
     * Format a detail for JSON response.
     */
    private function formatDetail(CodexDetail $detail): array
    {
        return [
            'id' => $detail->id,
            'key_name' => $detail->key_name,
            'value' => $detail->value,
            'sort_order' => $detail->sort_order,
            // Sprint 14 fields
            'definition_id' => $detail->definition_id,
            'ai_visibility' => $detail->ai_visibility,
            'type' => $detail->getEffectiveType(),
            'definition' => $detail->definition ? [
                'id' => $detail->definition->id,
                'name' => $detail->definition->name,
                'type' => $detail->definition->type,
                'options' => $detail->definition->options,
                'show_in_sidebar' => $detail->definition->show_in_sidebar,
                'ai_visibility' => $detail->definition->ai_visibility,
            ] : null,
        ];
    }
}
