# Chat Enhancement Features - Development Strategy

## Feature Analysis Summary

| Feature | Owner (Creates) | Consumer (Views) | Complexity | Priority |

|---------|-----------------|------------------|------------|----------|

| F1: Codex Alias Auto-Detection | ChatInput + AI Response | ChatMessage, Context | High | P1 |

| F2: Delete Chat Confirmation | ChatThreadList | ChatThreadList | Low | P0 |

| F3: Rename from Thread List | ChatThreadList | ChatPanel | Low | P0 |

| F4: Real-time Chat (Reverb) | Backend Events | ChatPanel, ChatWindow | High | P1 |

| F5: Regenerate with Model | ChatWindow | ChatPanel | Medium | P1 |

---

## F1: Codex Alias Auto-Detection and Linking

**Difficulty: High** - Requires text parsing, API calls, and UI linking

### Data Flow

```
User types in ChatInput → Detect alias matches → Auto-add to context
AI responds → Parse response for aliases → Render as clickable links
```

### Backend Changes

**New endpoint:** `GET /api/novels/{novel}/codex/aliases-lookup`

```php
// app/Http/Controllers/CodexAliasController.php
public function lookup(Request $request, Novel $novel): JsonResponse
{
    // Returns all aliases with their codex entry info for client-side matching
    $entries = $novel->codexEntries()
        ->active()
        ->with('aliases')
        ->get(['id', 'name', 'type']);
    
    // Build lookup map: alias -> entry
    $lookupMap = [];
    foreach ($entries as $entry) {
        $lookupMap[strtolower($entry->name)] = [
            'id' => $entry->id,
            'name' => $entry->name,
            'type' => $entry->type,
        ];
        foreach ($entry->aliases as $alias) {
            $lookupMap[strtolower($alias->alias)] = [
                'id' => $entry->id,
                'name' => $entry->name,
                'type' => $entry->type,
                'alias' => $alias->alias,
            ];
        }
    }
    return response()->json(['lookup' => $lookupMap]);
}
```

### Frontend Changes

**New composable:** `resources/js/composables/useCodexAliasDetection.ts`

- Maintains a lookup map of all aliases for the current novel
- Provides `detectAliases(text)` function that returns matched entries
- Debounced detection on ChatInput text changes

**Update:** `ChatInput.vue`

- Watch message input for alias matches
- Emit detected codex entries to parent
- Show detected aliases as removable chips above input

**Update:** `ChatMessage.vue`

- Parse message content for alias matches
- Render matched aliases as `<a>` links with `@click` to navigate to codex entry
- Links styled as inline badges (similar to how NovelCrafter shows them)

---

## F2: Delete Chat Confirmation Dialog

**Difficulty: Low** - UI enhancement only

### Current Implementation

`ChatThreadList.vue` already has a 2-click confirm pattern (line 73-87), but it's easy to miss.

### Improvement

Replace with a proper confirmation modal for better UX.

**Update:** `ChatThreadList.vue`

- Add `ConfirmDeleteModal` component or use existing dialog
- Show modal with thread title/preview
- iOS-style spring animation on modal appearance
```vue
<!-- Add to ChatThreadList.vue -->
<ConfirmDialog
    :show="showDeleteConfirm"
    title="Delete Chat"
    :message="`Are you sure you want to delete '${threadToDelete?.title || 'this chat'}'?`"
    confirm-text="Delete"
    confirm-variant="danger"
    @confirm="confirmDelete"
    @cancel="showDeleteConfirm = false"
/>
```


---

## F3: Rename Chat from Thread List

**Difficulty: Low** - UI interaction addition

### Current State

- Title can only be edited in `ChatHeader.vue` when thread is active
- Thread list shows title but has no edit capability

### Implementation

**Update:** `ChatThreadList.vue`

- Add edit/rename button (pencil icon) that appears on hover next to delete
- On click, transform the title text into an inline input
- Submit on Enter or blur, cancel on Escape
- Emit `rename` event to parent
```vue
<!-- In thread item, add alongside delete button -->
<button
    type="button"
    class="rename-btn opacity-0 group-hover:opacity-100"
    title="Rename"
    @click.stop="startRename(thread)"
>
    <PencilIcon class="h-4 w-4" />
</button>
```


**Update:** `ChatPanel.vue`

- Handle `@rename` event from ChatThreadList
- Call existing `updateThread` with new title

---

## F4: Real-time Chat Updates (Laravel Reverb)

**Difficulty: High** - Full-stack WebSocket implementation

### Technology Choice: Laravel Reverb

- Free and open source
- Works offline/locally
- Built into Laravel ecosystem
- No external service dependencies

### Backend Setup

**Install Reverb:**

```bash
php artisan install:broadcasting
```

**New Event:** `app/Events/ChatMessageCreated.php`

```php
class ChatMessageCreated implements ShouldBroadcast
{
    public function __construct(
        public ChatMessage $message,
        public ChatThread $thread
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.thread.' . $this->thread->id),
        ];
    }
}
```

**New Event:** `app/Events/ChatThreadUpdated.php`

- For title changes, new messages, etc.

**Update:** `ChatService.php`

- Dispatch events after message creation
- Dispatch events after thread updates

### Frontend Setup

**Install Laravel Echo + Pusher:**

```bash
yarn add laravel-echo pusher-js
```

**New file:** `resources/js/bootstrap/echo.ts`

```ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

**New composable:** `resources/js/composables/useChatRealtime.ts`

```ts
export function useChatRealtime(threadId: Ref<number | null>) {
    const onNewMessage = ref<(message: Message) => void>(() => {});
    
    watch(threadId, (newId, oldId) => {
        if (oldId) window.Echo.leave(`chat.thread.${oldId}`);
        if (newId) {
            window.Echo.private(`chat.thread.${newId}`)
                .listen('ChatMessageCreated', (e) => {
                    onNewMessage.value(e.message);
                });
        }
    });
    
    return { onNewMessage };
}
```

**Update:** `ChatPanel.vue`

- Subscribe to thread channel when active thread changes
- Update messages array when new messages arrive via WebSocket
- Remove manual polling/refresh

---

## F5: Regenerate with Different Model

**Difficulty: Medium** - UI + API enhancement

### Backend Changes

**Update endpoint:** `POST /api/chat/threads/{thread}/regenerate`

- Accept optional `model` and `connection_id` parameters
```php
// ChatController.php
public function regenerate(Request $request, ChatThread $thread): StreamedResponse
{
    $validated = $request->validate([
        'model' => ['nullable', 'string', 'max:255'],
        'connection_id' => ['nullable', 'integer', 'exists:ai_connections,id'],
    ]);
    
    return response()->eventStream(function () use ($thread, $validated) {
        foreach ($this->chatService->regenerateLastResponse(
            $thread,
            $validated['model'] ?? null,
            $validated['connection_id'] ?? null
        ) as $chunk) {
            yield new StreamedEvent(event: $chunk['type'], data: $chunk);
        }
    });
}
```


### Frontend Changes

**Update:** `ChatWindow.vue`

- Add dropdown/menu next to "Regenerate response" button
- Menu shows: "Regenerate" and "Regenerate with different model..."
- Second option opens model selector popover
```vue
<!-- Replace single regenerate button with button group -->
<div v-if="canRegenerate" class="flex justify-center gap-1 pt-2">
    <button @click="emit('regenerate')">
        <RefreshIcon /> Regenerate
    </button>
    <Popover>
        <PopoverTrigger>
            <button class="px-2">
                <ChevronDownIcon />
            </button>
        </PopoverTrigger>
        <PopoverContent>
            <ModelSelector v-model="regenerateModel" />
            <button @click="emit('regenerate-with-model', regenerateModel)">
                Regenerate with this model
            </button>
        </PopoverContent>
    </Popover>
</div>
```


**Update:** `ChatPanel.vue`

- Handle new `regenerate-with-model` event
- Pass model to regenerate API call

---

## Implementation Priority & Sequencing

### Phase 1: Quick Wins (1-2 days)

- **F2: Delete Confirmation** - Low effort, high UX impact
- **F3: Rename from List** - Low effort, convenience feature

### Phase 2: Core Enhancement (2-3 days)

- **F5: Regenerate with Model** - Medium effort, high value for AI usage

### Phase 3: Advanced Features (3-5 days)

- **F1: Codex Alias Detection** - High effort, unique differentiator
- **F4: Real-time Chat** - High effort, modern chat experience

---

## Key Files to Modify

| File | Features |

|------|----------|

| `ChatThreadList.vue` | F2, F3 |

| `ChatWindow.vue` | F1 (display), F5 |

| `ChatInput.vue` | F1 (detection) |

| `ChatMessage.vue` | F1 (links) |

| `ChatPanel.vue` | F3, F4, F5 |

| `ChatController.php` | F4, F5 |

| `ChatService.php` | F4 |

| `CodexAliasController.php` | F1 |

| New: `useChatRealtime.ts` | F4 |

| New: `useCodexAliasDetection.ts` | F1 |