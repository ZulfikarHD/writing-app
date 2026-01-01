<?php

namespace App\Http\Controllers;

use App\Models\CodexEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CodexImageController extends Controller
{
    /**
     * Upload a thumbnail image for a codex entry.
     */
    public function upload(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        // Delete old thumbnail if exists
        if ($entry->thumbnail_path) {
            Storage::disk('public')->delete($entry->thumbnail_path);
        }

        // Store new thumbnail
        $path = $request->file('thumbnail')->store(
            "codex/{$entry->novel_id}/thumbnails",
            'public'
        );

        // Update entry
        $entry->update(['thumbnail_path' => $path]);

        return response()->json([
            'success' => true,
            'thumbnail_path' => $path,
            'thumbnail_url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Remove the thumbnail from a codex entry.
     */
    public function destroy(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($entry->thumbnail_path) {
            Storage::disk('public')->delete($entry->thumbnail_path);
            $entry->update(['thumbnail_path' => null]);
        }

        return response()->json(['success' => true]);
    }
}
