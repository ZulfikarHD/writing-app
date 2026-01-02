---
name: Chat Interface Core Strategy
overview: "Implementation strategy for FG-04.1: Chat Interface Core - building the conversational AI interface for the writing app, including message streaming, thread management, and model selection."
todos:
  - id: db-migrations
    content: Create database migrations for chat_threads, chat_messages, chat_context_items tables
    status: completed
  - id: models
    content: Create ChatThread, ChatMessage, ChatContextItem models with factories and relationships
    status: completed
  - id: chat-service
    content: Create ChatService with streaming support using Laravel 12 eventStream
    status: completed
  - id: chat-controller
    content: Create ChatController with CRUD and streaming message endpoint
    status: completed
  - id: routes
    content: Add chat API routes to web.php
    status: completed
  - id: workspace-mode
    content: Update useWorkspaceState.ts and ModeNavigation.vue to include chat mode
    status: completed
  - id: chat-panel
    content: Create ChatPanel.vue as main chat interface container
    status: completed
  - id: chat-components
    content: Create ChatWindow, ChatMessage, ChatInput, ChatHeader, ChatThreadList components
    status: completed
  - id: streaming-frontend
    content: Install @laravel/stream-vue and implement useChat composable for real-time streaming
    status: completed
  - id: workspace-integration
    content: Integrate ChatPanel into Workspace/Index.vue alongside other modes
    status: completed
  - id: tests
    content: Write feature tests for chat API endpoints and unit tests for ChatService
    status: completed
---

# FG-04.1: Chat Interface Core - Development Strategy

## Phase 1: Feature Understanding

### What is being built?

A conversational AI chat interface (Workshop) that allows writers to brainstorm, ask questions, and get AI assistance. Based on Novelcrafter's chat interface:

- **Top Bar**: Thread title, actions menu (pin, split, export, archive, delete)
- **Message Area**: Chat history with user/AI distinction
- **Message Bar**: Context selector, message input, prompt selector, model selector, send button

### Data Flow

| Feature | Owner (Creates) | Consumer (Views) | Data Flow |

|---------|-----------------|------------------|-----------|

| Chat Thread | Writer in Workspace | Writer in Workspace/Pinned Panel | Create -> Store -> Display |

| Chat Message | Writer sends, AI responds | Writer in chat window | User Input -> API -> Stream -> Display |

| Model Selection | Writer in message bar | Per-thread setting | Select -> Store -> Apply to API |

| Thread List | System auto-creates | Writer in sidebar | Create -> List -> Navigate |

---

## Phase 2: Gap Analysis

### Currently Exists

- AI Connection system (`AIConnection` model, providers, `ModelSelector.vue`)
- `ChatMessageObserver.php` - ready for ChatMessage model
- `ChatMentionTracker.php` - ready for chat mention scanning
- Workspace layout with mode navigation (Write/Plan/Codex)
- Database does NOT have chat tables yet

### Missing (Must Build)

**Backend:**

- [ ] P0: `chat_threads` table migration
- [ ] P0: `chat_messages` table migration  
- [ ] P0: `chat_context_items` table migration
- [ ] P0: `ChatThread` model with relationships
- [ ] P0: `ChatMessage` model with relationships
- [ ] P0: `ChatController.php` with streaming support
- [ ] P0: `ChatService.php` for AI interaction
- [ ] P1: `ContextBuilder.php` for context assembly

**Frontend:**

- [ ] P0: Chat mode in Workspace (alongside Write/Plan/Codex)
- [ ] P0: `ChatPanel.vue` - main chat interface container
- [ ] P0: `ChatThreadList.vue` - sidebar thread list
- [ ] P0: `ChatWindow.vue` - message display area
- [ ] P0: `ChatMessage.vue` - individual message component
- [ ] P0: `ChatInput.vue` - message input with send
- [ ] P0: `ChatHeader.vue` - thread title, actions
- [ ] P1: Streaming message display with `@laravel/stream-vue`

---

## Phase 3: Technical Architecture

### Database Schema (SQLite Compatible)

```sql
-- chat_threads
id, novel_id, user_id, title, model, context_settings (JSON),
is_pinned, linked_scene_id, archived_at, created_at, updated_at

-- chat_messages  
id, thread_id, role (user/assistant/system), content,
model_used, tokens_input, tokens_output, context_snapshot (JSON), created_at

-- chat_context_items
id, thread_id, context_type, reference_id, custom_content, is_active, created_at
```

### Backend Structure

```
app/
├── Models/
│   ├── ChatThread.php
│   ├── ChatMessage.php
│   └── ChatContextItem.php
├── Http/
│   ├── Controllers/ChatController.php
│   └── Requests/SendChatMessageRequest.php
└── Services/Chat/
    ├── ChatService.php
    └── ContextBuilder.php
```

### Frontend Structure

```
resources/js/
├── pages/Workspace/Index.vue  (update: add chat mode)
├── components/
│   ├── workspace/ChatPanel.vue  (new)
│   └── chat/
│       ├── ChatWindow.vue
│       ├── ChatThreadList.vue
│       ├── ChatMessage.vue
│       ├── ChatInput.vue
│       ├── ChatHeader.vue
│       └── StreamingMessage.vue
└── composables/useChat.ts
```

### API Endpoints

| Method | Endpoint | Description |

|--------|----------|-------------|

| GET | `/api/novels/{novel}/chat/threads` | List threads |

| POST | `/api/novels/{novel}/chat/threads` | Create thread |

| GET | `/api/chat/threads/{thread}` | Get thread + messages |

| PATCH | `/api/chat/threads/{thread}` | Update thread |

| DELETE | `/api/chat/threads/{thread}` | Delete thread |

| POST | `/api/chat/threads/{thread}/messages` | Send message (SSE stream) |

| POST | `/api/chat/threads/{thread}/stop` | Stop generation |

| DELETE | `/api/chat/messages/{message}` | Delete message |

| POST | `/api/chat/messages/{message}/regenerate` | Regenerate response |

---

## Phase 4: Implementation Sequence

### Sprint A: Foundation (P0 - Critical)

**Build FIRST (no dependencies):**

1. Database migrations (can run in parallel):

   - `create_chat_threads_table`
   - `create_chat_messages_table`
   - `create_chat_context_items_table`

2. Models (after migrations):

   - `ChatThread.php` with factory
   - `ChatMessage.php` with factory
   - `ChatContextItem.php`

**Build SECOND:**

3. `ChatService.php` - Core AI interaction with SSE streaming

   - Leverage existing `AIServiceFactory`
   - Use Laravel 12's `response()->eventStream()`

4. `ChatController.php` - API endpoints

   - Thread CRUD
   - Message sending with streaming

### Sprint B: Frontend Core (P0 - Critical)

**Build in PARALLEL:**

1. Update `useWorkspaceState.ts` - add 'chat' mode
2. Create `ChatPanel.vue` - main container
3. Create `ChatWindow.vue` - messages display
4. Create `ChatMessage.vue` - message bubble
5. Create `ChatInput.vue` - input + model selector
6. Create `ChatThreadList.vue` - thread sidebar

**Build LAST:**

7. Install `@laravel/stream-vue` for streaming
8. Create `useChat.ts` composable for stream handling
9. Integrate streaming into `ChatMessage.vue`

### Sprint C: Polish (P1 - Important)

- Stop generation button
- Regenerate response
- Thread rename/delete
- Message copy button
- Loading states with skeleton
- Empty states

---

## Phase 5: User Journeys

### Journey 1: First Chat Message

**Steps:**

1. User navigates to: `/novels/{id}/workspace?mode=chat`
2. User sees: Empty chat state with "Start a conversation" prompt
3. User clicks: New Thread button (or auto-creates on first message)
4. User types message in input field
5. User selects AI model from dropdown (defaults to user's default)
6. User clicks Send (or presses Enter)
7. System: Creates thread + message, streams AI response
8. User sees: Message appearing token by token
9. Result: Thread created with first exchange

### Journey 2: Continue Existing Thread

**Steps:**

1. User navigates to: `/novels/{id}/workspace?mode=chat`
2. User sees: Thread list in sidebar
3. User clicks: Thread title
4. User sees: Previous messages loaded
5. User types and sends new message
6. Result: Conversation continues

### Journey 3: Model Selection

**Steps:**

1. User is in chat input area
2. User clicks: Model selector dropdown
3. User sees: List of available models from their AI connections
4. User selects: Different model (e.g., GPT-4 instead of GPT-3.5)
5. System: Stores model preference for this thread
6. Result: Next message uses selected model

---

## Phase 6: Integration Points

### Navigation Update

Update `ModeNavigation.vue` to include Chat mode:

```vue
// Add alongside write, plan, codex
{ id: 'chat', label: 'Chat', icon: 'chat-bubble' }
```

### Workspace Integration

Update `Workspace/Index.vue`:

```vue
// Import ChatPanel
const ChatPanel = defineAsyncComponent(() => import('@/components/workspace/ChatPanel.vue'));

// Add to template
<ChatPanel v-else-if="isChatMode" :novel="novel" />
```

### AI Provider Extension

Extend `AIProviderInterface.php`:

```php
// Add streaming method
public function streamChat(AIConnection $connection, array $messages, string $model): Generator;
```

---

## Phase 7: Mobile Considerations

- Chat interface is naturally mobile-friendly (messaging UI pattern)
- Thread list: Collapsible drawer on mobile
- Full-width message input on mobile
- Touch-friendly send button
- Swipe gestures for thread actions (later)

---

## Phase 8: Key Files to Create/Modify

### New Files

| File | Purpose |

|------|---------|

| `database/migrations/xxxx_create_chat_threads_table.php` | Thread storage |

| `database/migrations/xxxx_create_chat_messages_table.php` | Message storage |

| `app/Models/ChatThread.php` | Thread model |

| `app/Models/ChatMessage.php` | Message model |

| `app/Http/Controllers/ChatController.php` | API controller |

| `app/Services/Chat/ChatService.php` | AI interaction |

| `resources/js/components/workspace/ChatPanel.vue` | Main chat UI |

| `resources/js/components/chat/*.vue` | Chat components |

| `resources/js/composables/useChat.ts` | Chat state management |

### Modified Files

| File | Change |

|------|--------|

| `routes/web.php` | Add chat API routes |

| `resources/js/pages/Workspace/Index.vue` | Add chat mode |

| `resources/js/components/workspace/ModeNavigation.vue` | Add chat tab |

| `resources/js/composables/useWorkspaceState.ts` | Add 'chat' to WorkspaceMode |

| `app/Providers/AppServiceProvider.php` | Register ChatMessage observer |

---

## Phase 9: Streaming Implementation

### Backend (Laravel 12 SSE)

```php
// ChatController.php
public function sendMessage(SendChatMessageRequest $request, ChatThread $thread)
{
    return response()->eventStream(function () use ($request, $thread) {
        $chatService = app(ChatService::class);
        
        foreach ($chatService->streamResponse($thread, $request->message) as $chunk) {
            yield new StreamedEvent(
                event: 'message',
                data: $chunk,
            );
        }
        
        yield new StreamedEvent(event: 'done', data: ['message_id' => $message->id]);
    });
}
```

### Frontend (Vue Stream)

```typescript
// useChat.ts
import { useStream } from '@laravel/stream-vue';

export function useChat(threadId: Ref<number>) {
    const { data, isFetching, isStreaming, send } = useStream(
        computed(() => `/api/chat/threads/${threadId.value}/messages`)
    );
    
    return { streamingContent: data, isFetching, isStreaming, sendMessage: send };
}
```

---

## Phase 10: Definition of Done Checklist

- [ ] Chat threads can be created, listed, updated, deleted
- [ ] Messages stream in real-time token by token
- [ ] Model selection works per-thread
- [ ] Stop generation button works
- [ ] Regenerate response works
- [ ] Chat mode accessible from workspace navigation
- [ ] Thread list shows in sidebar
- [ ] Mobile-responsive layout
- [ ] Loading/empty states implemented
- [ ] Feature tests for API endpoints
- [ ] Unit tests for ChatService
