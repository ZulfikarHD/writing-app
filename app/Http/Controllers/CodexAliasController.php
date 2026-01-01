<?php

namespace App\Http\Controllers;

use App\Models\CodexAlias;
use App\Models\CodexEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodexAliasController extends Controller
{
    /**
     * List all aliases for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'aliases' => $entry->aliases->map(fn (CodexAlias $alias) => [
                'id' => $alias->id,
                'alias' => $alias->alias,
            ]),
        ]);
    }

    /**
     * Add a new alias to an entry.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'alias' => ['required', 'string', 'max:255'],
        ]);

        // Check for duplicate
        $exists = $entry->aliases()->where('alias', $validated['alias'])->exists();
        if ($exists) {
            return response()->json([
                'message' => 'This alias already exists',
            ], 422);
        }

        $alias = $entry->aliases()->create($validated);

        return response()->json([
            'alias' => [
                'id' => $alias->id,
                'alias' => $alias->alias,
            ],
        ], 201);
    }

    /**
     * Update an alias.
     */
    public function update(Request $request, CodexAlias $alias): JsonResponse
    {
        if ($alias->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'alias' => ['required', 'string', 'max:255'],
        ]);

        // Check for duplicate (excluding current)
        $exists = $alias->entry->aliases()
            ->where('alias', $validated['alias'])
            ->where('id', '!=', $alias->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This alias already exists',
            ], 422);
        }

        $alias->update($validated);

        return response()->json([
            'alias' => [
                'id' => $alias->id,
                'alias' => $alias->alias,
            ],
        ]);
    }

    /**
     * Remove an alias.
     */
    public function destroy(Request $request, CodexAlias $alias): JsonResponse
    {
        if ($alias->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $alias->delete();

        return response()->json(['success' => true]);
    }
}
