<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Novel;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * List chat threads for a novel.
     */
    public function index(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $threads = $novel->chatThreads()
            ->where('user_id', $request->user()->id)
            ->with(['messages' => function ($query) {
                $query->latest('created_at')->limit(1);
            }])
            ->when($request->boolean('include_archived', false), function ($query) {
                // Include all
            }, function ($query) {
                $query->whereNull('archived_at');
            })
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json([
            'threads' => $threads->items(),
            'pagination' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    /**
     * Create a new chat thread.
     */
    public function store(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'connection_id' => ['nullable', 'integer', 'exists:ai_connections,id'],
            'linked_scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
        ]);

        $thread = $this->chatService->createThread(
            user: $request->user(),
            novelId: $novel->id,
            title: $validated['title'] ?? null,
            connectionId: $validated['connection_id'] ?? null,
            model: $validated['model'] ?? null,
        );

        if (isset($validated['linked_scene_id'])) {
            $thread->update(['linked_scene_id' => $validated['linked_scene_id']]);
        }

        return response()->json([
            'thread' => $thread->load('messages'),
        ], 201);
    }

    /**
     * Get a chat thread with its messages.
     */
    public function show(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $thread->load(['messages', 'linkedScene', 'activeContextItems']);

        return response()->json([
            'thread' => $thread,
        ]);
    }

    /**
     * Update a chat thread.
     */
    public function update(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'connection_id' => ['nullable', 'integer', 'exists:ai_connections,id'],
            'is_pinned' => ['nullable', 'boolean'],
            'archived_at' => ['nullable', 'date'],
        ]);

        $thread->update($validated);

        return response()->json([
            'thread' => $thread,
        ]);
    }

    /**
     * Delete a chat thread.
     */
    public function destroy(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $thread->delete();

        return response()->json([
            'message' => 'Thread deleted successfully.',
        ]);
    }

    /**
     * Archive a chat thread.
     */
    public function archive(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $thread->update(['archived_at' => now()]);

        return response()->json([
            'thread' => $thread,
        ]);
    }

    /**
     * Restore an archived chat thread.
     */
    public function restore(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $thread->update(['archived_at' => null]);

        return response()->json([
            'thread' => $thread,
        ]);
    }

    /**
     * Send a message to a chat thread (streaming response).
     */
    public function sendMessage(SendChatMessageRequest $request, ChatThread $thread): StreamedResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $message = $request->validated()['message'];
        $model = $request->validated()['model'] ?? null;
        $connectionId = $request->validated()['connection_id'] ?? null;

        return response()->eventStream(function () use ($thread, $message, $model, $connectionId) {
            foreach ($this->chatService->streamResponse($thread, $message, $model, $connectionId) as $chunk) {
                yield new StreamedEvent(
                    event: $chunk['type'],
                    data: $chunk,
                );
            }
        });
    }

    /**
     * Regenerate the last response in a thread.
     */
    public function regenerate(Request $request, ChatThread $thread): StreamedResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        return response()->eventStream(function () use ($thread) {
            foreach ($this->chatService->regenerateLastResponse($thread) as $chunk) {
                yield new StreamedEvent(
                    event: $chunk['type'],
                    data: $chunk,
                );
            }
        });
    }

    /**
     * Delete a chat message.
     */
    public function deleteMessage(Request $request, ChatMessage $message): JsonResponse
    {
        $thread = $message->thread;

        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this message.');
        }

        $message->delete();

        return response()->json([
            'message' => 'Message deleted successfully.',
        ]);
    }

    /**
     * Get messages for a thread (for lazy loading).
     */
    public function messages(Request $request, ChatThread $thread): JsonResponse
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }

        $messages = $thread->messages()
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'total' => $messages->total(),
            ],
        ]);
    }
}
