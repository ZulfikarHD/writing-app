# API Documentation: Chat (Workshop)

## Overview

API untuk mengelola chat threads dan messages dalam Workshop mode, yang memungkinkan penulis untuk berinteraksi dengan AI assistant untuk brainstorming, Q&A, dan bantuan penulisan dengan real-time streaming responses.

**Base URL:** `/api`  
**Authentication:** Required (Bearer Token via Sanctum)

---

## Authentication

Semua endpoint memerlukan authentication header:
```
Authorization: Bearer <access_token>
```

User harus memiliki akses ke novel yang terkait dengan chat thread.

---

## Endpoints

### List Chat Threads

Mengambil daftar chat threads untuk novel tertentu dengan pagination dan filtering.

**Endpoint:** `GET /api/novels/{novel}/chat/threads`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| include_archived | boolean | No | false | Include archived threads |
| page | integer | No | 1 | Page number |

**Response:** `200 OK`

```json
{
  "threads": [
    {
      "id": 1,
      "novel_id": 5,
      "user_id": 10,
      "title": "Character Development Ideas",
      "model": "gpt-4o",
      "connection_id": 2,
      "context_settings": null,
      "is_pinned": false,
      "linked_scene_id": null,
      "archived_at": null,
      "created_at": "2026-01-02T03:15:30Z",
      "updated_at": "2026-01-02T04:20:15Z",
      "messages": [
        {
          "id": 150,
          "role": "assistant",
          "content": "Here are some ideas for your protagonist...",
          "created_at": "2026-01-02T04:20:15Z"
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "total": 52
  }
}
```

**Authorization:**
- User must own the novel

---

### Create Chat Thread

Membuat chat thread baru untuk novel.

**Endpoint:** `POST /api/novels/{novel}/chat/threads`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Request Body:**

```json
{
  "title": "Plot Twist Ideas",
  "model": "gpt-4o-mini",
  "connection_id": 2,
  "linked_scene_id": 45
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| model | string | No | Max 255 chars |
| connection_id | integer | No | Must exist in ai_connections |
| linked_scene_id | integer | No | Must exist in scenes |

**Response:** `201 Created`

```json
{
  "thread": {
    "id": 53,
    "novel_id": 5,
    "user_id": 10,
    "title": "Plot Twist Ideas",
    "model": "gpt-4o-mini",
    "connection_id": 2,
    "is_pinned": false,
    "linked_scene_id": 45,
    "archived_at": null,
    "created_at": "2026-01-02T10:30:00Z",
    "updated_at": "2026-01-02T10:30:00Z",
    "messages": []
  }
}
```

**Error Response:** `403 Forbidden`

```json
{
  "message": "This action is unauthorized."
}
```

**Authorization:**
- User must own the novel

---

### Get Chat Thread

Mengambil detail chat thread beserta messages, linked scene, dan active context items.

**Endpoint:** `GET /api/chat/threads/{thread}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "thread": {
    "id": 1,
    "novel_id": 5,
    "user_id": 10,
    "title": "Character Development Ideas",
    "model": "gpt-4o",
    "connection_id": 2,
    "is_pinned": false,
    "linked_scene_id": 45,
    "archived_at": null,
    "created_at": "2026-01-02T03:15:30Z",
    "updated_at": "2026-01-02T04:20:15Z",
    "messages": [
      {
        "id": 150,
        "thread_id": 1,
        "role": "user",
        "content": "Help me develop my protagonist's backstory",
        "model_used": null,
        "tokens_input": null,
        "tokens_output": null,
        "context_snapshot": null,
        "created_at": "2026-01-02T04:15:00Z"
      },
      {
        "id": 151,
        "thread_id": 1,
        "role": "assistant",
        "content": "Here are some ideas for your protagonist...",
        "model_used": "gpt-4o",
        "tokens_input": 1250,
        "tokens_output": 580,
        "context_snapshot": {
          "novel": { "title": "My Novel", "genre": "Fantasy" }
        },
        "created_at": "2026-01-02T04:20:15Z"
      }
    ],
    "linked_scene": {
      "id": 45,
      "title": "Chapter 3 - The Revelation",
      "summary": "..."
    },
    "active_context_items": []
  }
}
```

**Error Response:** `404 Not Found`

```json
{
  "message": "No query results for model [App\\Models\\ChatThread] 999"
}
```

**Authorization:**
- User must own the novel
- User must own the thread

---

### Update Chat Thread

Memperbarui thread properties seperti title, model, pin status, atau archived status.

**Endpoint:** `PATCH /api/chat/threads/{thread}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Request Body:**

```json
{
  "title": "Updated Title",
  "model": "claude-3-5-sonnet-20241022",
  "connection_id": 3,
  "is_pinned": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| model | string | No | Max 255 chars |
| connection_id | integer | No | Must exist in ai_connections |
| is_pinned | boolean | No | - |
| archived_at | datetime | No | Valid datetime or null |

**Response:** `200 OK`

```json
{
  "thread": {
    "id": 1,
    "title": "Updated Title",
    "model": "claude-3-5-sonnet-20241022",
    "connection_id": 3,
    "is_pinned": true,
    "updated_at": "2026-01-02T11:00:00Z"
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

---

### Delete Chat Thread

Menghapus chat thread beserta semua messages (cascade delete).

**Endpoint:** `DELETE /api/chat/threads/{thread}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "message": "Thread deleted successfully."
}
```

**Authorization:**
- User must own the novel
- User must own the thread

**Note:** Deletion is permanent. Semua messages dalam thread juga akan terhapus.

---

### Archive Chat Thread

Mengarsipkan chat thread (soft archive dengan timestamp).

**Endpoint:** `POST /api/chat/threads/{thread}/archive`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "thread": {
    "id": 1,
    "archived_at": "2026-01-02T11:30:00Z",
    "updated_at": "2026-01-02T11:30:00Z"
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

---

### Restore Archived Thread

Mengembalikan archived thread ke status active.

**Endpoint:** `POST /api/chat/threads/{thread}/restore`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "thread": {
    "id": 1,
    "archived_at": null,
    "updated_at": "2026-01-02T11:35:00Z"
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

---

### Get Thread Messages

Mengambil paginated messages untuk thread (untuk lazy loading).

**Endpoint:** `GET /api/chat/threads/{thread}/messages`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| page | integer | No | 1 | Page number |

**Response:** `200 OK`

```json
{
  "messages": [
    {
      "id": 150,
      "thread_id": 1,
      "role": "user",
      "content": "Help me with the plot",
      "created_at": "2026-01-02T04:15:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "total": 75
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

**Note:** Messages diurutkan by `created_at` ASC, dengan 50 messages per page.

---

### Send Message (Streaming)

Mengirim message ke chat thread dan menerima AI response sebagai Server-Sent Events (SSE) stream.

**Endpoint:** `POST /api/chat/threads/{thread}/messages`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Request Headers:**
```
Accept: text/event-stream
Content-Type: application/json
```

**Request Body:**

```json
{
  "message": "Help me develop the villain's motivation",
  "model": "gpt-4o",
  "connection_id": 2
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| message | string | Yes | Max 32,000 chars |
| model | string | No | Max 255 chars |
| connection_id | integer | No | Must exist in ai_connections |

**Response:** `200 OK` (Server-Sent Events)

**Event Types:**

#### 1. `user_message` Event
Dikembalikan setelah user message berhasil disimpan.

```
event: user_message
data: {"type":"user_message","message_id":152}
```

#### 2. `content` Event
Streaming token-by-token dari AI response.

```
event: content
data: {"type":"content","content":"Here"}

event: content
data: {"type":"content","content":" are"}

event: content
data: {"type":"content","content":" some"}
```

#### 3. `done` Event
Response streaming selesai, assistant message disimpan.

```
event: done
data: {"type":"done","message_id":153}
```

#### 4. `error` Event
Jika terjadi error selama streaming.

```
event: error
data: {"type":"error","error":"Failed to connect to AI provider"}
```

**Client Implementation Example:**

```javascript
const response = await fetch('/api/chat/threads/1/messages', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'text/event-stream',
    'Authorization': 'Bearer <token>'
  },
  body: JSON.stringify({ message: 'Help me...' })
});

const reader = response.body.getReader();
const decoder = new TextDecoder();

while (true) {
  const { done, value } = await reader.read();
  if (done) break;
  
  const chunk = decoder.decode(value);
  const lines = chunk.split('\n');
  
  for (const line of lines) {
    if (line.startsWith('data: ')) {
      const data = JSON.parse(line.slice(6));
      console.log(data);
    }
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

**Business Logic:**
- Jika thread tidak punya title, auto-generate dari first user message (limit 50 chars)
- Thread's `updated_at` di-update setelah message selesai
- Context injection: novel info (title, genre, POV, tense), linked scene, active context items
- Token counting (jika provider support)

---

### Regenerate Last Response (Streaming)

Menghapus last assistant message dan regenerate dengan streaming.

**Endpoint:** `POST /api/chat/threads/{thread}/regenerate`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Request Headers:**
```
Accept: text/event-stream
```

**Response:** `200 OK` (Server-Sent Events)

Event types sama seperti `Send Message` endpoint.

**Authorization:**
- User must own the novel
- User must own the thread

**Business Logic:**
- Mencari last assistant message dalam thread
- Menghapus assistant message tersebut
- Re-generate response dengan context yang sama
- Jika tidak ada assistant message → return error event

---

### Delete Message

Menghapus individual message dari thread.

**Endpoint:** `DELETE /api/chat/messages/{message}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| message | integer | Chat Message ID |

**Response:** `200 OK`

```json
{
  "message": "Message deleted successfully."
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

**Note:** Deletion is permanent. Tidak ada soft delete untuk messages.

---

## Context Management Endpoints

### List Context Items

Mengambil daftar context items yang attached ke thread beserta token information dan limit warnings.

**Endpoint:** `GET /api/chat/threads/{thread}/context`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "items": [
    {
      "id": 10,
      "thread_id": 1,
      "context_type": "scene",
      "reference_id": 45,
      "is_active": true,
      "tokens": 1250,
      "preview": "Scene content preview truncated to 200 chars...",
      "created_at": "2026-01-02T10:30:00Z",
      "reference": {
        "id": 45,
        "title": "Chapter 3 - The Discovery",
        "word_count": 2500
      }
    },
    {
      "id": 11,
      "thread_id": 1,
      "context_type": "codex",
      "reference_id": 12,
      "is_active": true,
      "tokens": 580,
      "preview": "Character: John Smith - A mysterious detective...",
      "created_at": "2026-01-02T10:35:00Z",
      "reference": {
        "id": 12,
        "name": "John Smith",
        "type": "character"
      }
    },
    {
      "id": 12,
      "thread_id": 1,
      "context_type": "custom",
      "reference_id": null,
      "is_active": false,
      "tokens": 125,
      "preview": "Remember to focus on emotional depth...",
      "created_at": "2026-01-02T11:00:00Z",
      "custom_content": "Remember to focus on emotional depth and character development."
    }
  ],
  "tokens": {
    "total": 2150,
    "base_tokens": 45,
    "items": [
      { "id": 10, "type": "scene", "name": "Chapter 3 - The Discovery", "tokens": 1250 },
      { "id": 11, "type": "codex", "name": "John Smith", "tokens": 580 },
      { "id": 12, "type": "custom", "name": "Custom Context", "tokens": 125 }
    ]
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 2.1,
    "tokens_used": 2150,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

---

### Add Context Item

Menambahkan context item baru ke thread (scene, codex entry, summary, outline, atau custom text).

**Endpoint:** `POST /api/chat/threads/{thread}/context`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Request Body:**

```json
{
  "context_type": "scene",
  "reference_id": 45,
  "is_active": true
}
```

**For custom context:**
```json
{
  "context_type": "custom",
  "custom_content": "Remember to focus on the protagonist's internal conflict.",
  "is_active": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| context_type | string | Yes | Enum: scene, codex, summary, outline, custom |
| reference_id | integer | Conditional | Required for scene/codex types |
| custom_content | string | Conditional | Required for custom type, max 100000 chars |
| is_active | boolean | No | Default: true |

**Response:** `201 Created`

```json
{
  "item": {
    "id": 13,
    "thread_id": 1,
    "context_type": "scene",
    "reference_id": 45,
    "is_active": true,
    "tokens": 1250,
    "preview": "Scene content preview...",
    "created_at": "2026-01-02T12:00:00Z",
    "reference": {
      "id": 45,
      "title": "Chapter 3 - The Discovery",
      "word_count": 2500
    }
  },
  "tokens": {
    "total": 3400,
    "base_tokens": 45,
    "items": [...]
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 3.5,
    "tokens_used": 3400,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Error Response:** `404 Not Found`

```json
{
  "message": "The specified scene was not found."
}
```

**Authorization:**
- User must own the novel
- User must own the thread
- Referenced scene/codex must belong to the novel

**Business Logic:**
- Jika context item yang sama sudah ada (same type + reference_id), akan di-reactivate instead of creating duplicate
- System validates bahwa scene/codex belongs to the novel
- Token count automatically calculated dan limit checked

---

### Update Context Item

Update context item untuk toggle active/inactive atau edit custom content.

**Endpoint:** `PATCH /api/chat/context/{item}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| item | integer | Chat Context Item ID |

**Request Body:**

```json
{
  "is_active": false
}
```

**Or for custom context:**
```json
{
  "custom_content": "Updated instruction for AI..."
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| is_active | boolean | No | Toggle context on/off |
| custom_content | string | No | Max 100000 chars (for custom type only) |

**Response:** `200 OK`

```json
{
  "item": {
    "id": 13,
    "thread_id": 1,
    "context_type": "scene",
    "reference_id": 45,
    "is_active": false,
    "tokens": 1250,
    "preview": "Scene content preview...",
    "created_at": "2026-01-02T12:00:00Z",
    "reference": {...}
  },
  "tokens": {
    "total": 2150,
    "items": [...]
  },
  "limit": {...}
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

**Business Logic:**
- Inactive context items tidak included dalam AI prompt
- Token count recalculated setelah update

---

### Delete Context Item

Menghapus context item dari thread (permanent deletion).

**Endpoint:** `DELETE /api/chat/context/{item}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| item | integer | Chat Context Item ID |

**Response:** `200 OK`

```json
{
  "message": "Context item removed.",
  "tokens": {
    "total": 900,
    "items": [...]
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 0.9,
    "tokens_used": 900,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

---

### Get Context Preview

Mengambil full context preview dengan actual text yang akan di-inject ke AI prompt.

**Endpoint:** `GET /api/chat/threads/{thread}/context/preview`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "preview": {
    "text": "=== Chapter 3 - The Discovery (scene) ===\n[Full scene content...]\n\n=== John Smith (codex) ===\n[Full codex entry...]\n\n",
    "tokens": 2150,
    "items": [
      {
        "name": "Chapter 3 - The Discovery",
        "type": "scene",
        "content": "[full content...]",
        "tokens": 1250
      },
      {
        "name": "John Smith",
        "type": "codex",
        "content": "[full codex entry...]",
        "tokens": 580
      }
    ]
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 2.1,
    "tokens_used": 2150,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

**Use Case:** 
- Debugging context injection
- Showing user exactly what AI will see
- Token usage estimation before sending message

---

### Bulk Add Context Items

Menambahkan multiple context items sekaligus (useful untuk "Add all scenes" atau batch operations).

**Endpoint:** `POST /api/chat/threads/{thread}/context/bulk`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Request Body:**

```json
{
  "items": [
    {
      "context_type": "scene",
      "reference_id": 45
    },
    {
      "context_type": "scene",
      "reference_id": 46
    },
    {
      "context_type": "codex",
      "reference_id": 12
    }
  ]
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| items | array | Yes | Min 1, Max 50 items |
| items.*.context_type | string | Yes | Enum: scene, codex, summary, outline, custom |
| items.*.reference_id | integer | Conditional | Required for scene/codex |
| items.*.custom_content | string | Conditional | Required for custom type |

**Response:** `201 Created`

```json
{
  "items": [
    {
      "id": 14,
      "thread_id": 1,
      "context_type": "scene",
      "reference_id": 45,
      "is_active": true,
      "tokens": 1250,
      "preview": "...",
      "created_at": "2026-01-02T13:00:00Z",
      "reference": {...}
    },
    {
      "id": 15,
      "thread_id": 1,
      "context_type": "scene",
      "reference_id": 46,
      "is_active": true,
      "tokens": 1100,
      "preview": "...",
      "created_at": "2026-01-02T13:00:00Z",
      "reference": {...}
    }
  ],
  "tokens": {
    "total": 5500,
    "items": [...]
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 5.7,
    "tokens_used": 5500,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Authorization:**
- User must own the novel
- User must own the thread

**Business Logic:**
- Invalid references di-skip (tidak abort entire request)
- Duplicate items di-skip (tidak create duplicate)
- Existing items di-reactivate jika inactive

---

### Clear All Context

Menghapus semua context items dari thread sekaligus.

**Endpoint:** `DELETE /api/chat/threads/{thread}/context/clear`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| thread | integer | Chat Thread ID |

**Response:** `200 OK`

```json
{
  "message": "All context items cleared.",
  "tokens": {
    "total": 0,
    "items": []
  },
  "limit": {
    "within_limit": true,
    "usage_percentage": 0.0,
    "tokens_used": 0,
    "limit": 96000,
    "model_limit": 128000
  }
}
```

**Authorization:**
- User must own the novel (via thread)
- User must own the thread

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 400 | Request body tidak valid |
| `UNAUTHORIZED` | 401 | Token tidak valid atau expired |
| `FORBIDDEN` | 403 | Tidak punya akses ke resource |
| `NOT_FOUND` | 404 | Thread/Message tidak ditemukan |
| `UNPROCESSABLE_ENTITY` | 422 | Validation failed |
| `INTERNAL_ERROR` | 500 | Server error |

---

## Rate Limiting

Standard Laravel rate limiting applied:
- **Web routes:** 60 requests/minute per user
- **API routes:** 60 requests/minute per user

Streaming endpoints tidak dihitung per-chunk, tapi per-request.

---

## Pagination

List endpoints menggunakan Laravel's standard pagination:
- Default page size: **20 items** (threads)
- Default page size: **50 items** (messages)
- Max page size: Not enforced (controlled by backend)

Response format:
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "total": 100
  }
}
```

---

## Data Types

### ChatThread
```typescript
interface ChatThread {
  id: number;
  novel_id: number;
  user_id: number;
  title: string | null;
  model: string | null;
  connection_id: number | null;
  context_settings: object | null;
  is_pinned: boolean;
  linked_scene_id: number | null;
  archived_at: string | null; // ISO 8601 datetime
  created_at: string; // ISO 8601 datetime
  updated_at: string; // ISO 8601 datetime
  messages?: ChatMessage[];
  linked_scene?: Scene;
  active_context_items?: ChatContextItem[];
}
```

### ChatMessage
```typescript
interface ChatMessage {
  id: number;
  thread_id: number;
  role: 'user' | 'assistant' | 'system';
  content: string;
  model_used: string | null;
  tokens_input: number | null;
  tokens_output: number | null;
  context_snapshot: object | null;
  created_at: string; // ISO 8601 datetime
}
```

### ChatContextItem
```typescript
interface ChatContextItem {
  id: number;
  thread_id: number;
  context_type: 'scene' | 'codex' | 'summary' | 'outline' | 'custom';
  reference_id: number | null;
  custom_content: string | null;
  is_active: boolean;
  created_at: string; // ISO 8601 datetime
}
```

---

## Notes

### Streaming Implementation
- Menggunakan **Server-Sent Events (SSE)** protocol
- Laravel 12's `response()->eventStream()` dengan `Generator` pattern
- Frontend consume menggunakan native `fetch()` dengan stream reader
- Event format: `event: <type>\ndata: <json>\n\n`

### Context Injection
System secara otomatis inject context ke AI prompt:
1. **Novel context:** title, genre, POV, tense
2. **Linked scene:** jika thread linked ke scene tertentu
3. **Active context items:** custom context yang di-set user
4. **Codex mentions:** auto-detected via `ChatMentionTracker` (ready, not implemented yet in this sprint)

### Token Tracking
- `tokens_input` dan `tokens_output` di-track jika provider support (OpenAI, Anthropic)
- Untuk provider tanpa token tracking → `null`
- Used untuk future cost estimation dan analytics

### Model Selection Priority
1. Model dari request body (jika ada)
2. Thread's default model (jika di-set)
3. User's default connection model
4. Provider default model (fallback)

---

## Real-time Broadcasting (WebSocket)

Sprint 23 memperkenalkan real-time chat updates menggunakan Laravel Reverb WebSocket server.

### Channel Configuration

| Channel | Type | Purpose |
|---------|------|---------|
| `chat.thread.{threadId}` | Private | New messages dan thread updates |
| `chat.novel.{novelId}` | Private | Thread list updates untuk novel |

### Events

#### ChatMessageCreated

Broadcast saat assistant message berhasil di-create.

**Event Name:** `message.created`

**Payload:**
```json
{
  "message": {
    "id": 153,
    "thread_id": 1,
    "role": "assistant",
    "content": "Here are some ideas for your protagonist...",
    "model_used": "gpt-4o",
    "tokens_input": 1250,
    "tokens_output": 580,
    "created_at": "2026-01-02T04:20:15Z"
  }
}
```

#### ChatThreadUpdated

Broadcast saat thread di-update (title change, archive, message added, etc.)

**Event Name:** `thread.updated`

**Payload:**
```json
{
  "thread": {
    "id": 1,
    "novel_id": 5,
    "title": "Updated Title",
    "model": "gpt-4o",
    "is_pinned": false,
    "archived_at": null,
    "updated_at": "2026-01-02T11:00:00Z"
  },
  "update_type": "message_added"
}
```

**Update Types:**
- `updated` - General update (title, model, etc.)
- `message_added` - New message in thread
- `message_regenerated` - Last message regenerated
- `deleted` - Thread deleted
- `archived` - Thread archived
- `restored` - Thread restored from archive

### Client Implementation

```typescript
import Echo from 'laravel-echo';

// Subscribe to thread channel
window.Echo.private(`chat.thread.${threadId}`)
  .listen('.message.created', (e) => {
    console.log('New message:', e.message);
    messages.value.push(e.message);
  })
  .listen('.thread.updated', (e) => {
    console.log('Thread updated:', e.thread, e.update_type);
  });

// Cleanup on unmount
window.Echo.leave(`chat.thread.${threadId}`);
```

### Authorization

```php
// routes/channels.php
Broadcast::channel('chat.thread.{threadId}', function ($user, int $threadId) {
    $thread = ChatThread::find($threadId);
    return $thread && $thread->user_id === $user->id;
});

Broadcast::channel('chat.novel.{novelId}', function ($user, int $novelId) {
    $novel = Novel::find($novelId);
    return $novel && $novel->user_id === $user->id;
});
```

---

## Related API: Codex Alias Lookup

Untuk mendukung fitur Codex Alias Auto-Detection di ChatInput, endpoint berikut tersedia di Codex API:

### Get Alias Lookup Map

**Endpoint:** `GET /api/novels/{novel}/codex/alias-lookup`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Response:** `200 OK`

```json
{
  "lookup": {
    "john smith": {
      "id": 12,
      "name": "John Smith",
      "type": "character",
      "description": "A mysterious detective with a dark past..."
    },
    "johnny": {
      "id": 12,
      "name": "John Smith",
      "type": "character",
      "alias": "Johnny",
      "description": "A mysterious detective with a dark past..."
    },
    "the old castle": {
      "id": 45,
      "name": "The Old Castle",
      "type": "location",
      "description": "An ancient fortress on the northern cliffs..."
    }
  },
  "entry_count": 25
}
```

**Authorization:**
- User must own the novel

**Notes:**
- Keys are lowercased untuk case-insensitive matching
- Includes both entry names dan aliases
- Description truncated to 100 characters
- Name entries take precedence over alias entries (same key)

> 📡 Full Codex API documentation: [Codex API](./codex.md)

---

*Last Updated: 2026-01-02*
