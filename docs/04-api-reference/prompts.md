# API Documentation: Prompts

## Overview

API untuk mengelola AI prompts dalam writing app, mendukung CRUD operations, filtering, cloning, dan usage tracking untuk system dan user-defined prompts, yaitu: memfasilitasi prompt management, mendukung multiple prompt types, dan menyediakan access control yang aman.

**Base URL:** `/api/prompts`  
**Authentication:** Required (Bearer token via Sanctum)

---

## Authentication

Semua endpoint memerlukan header:

```http
Authorization: Bearer <access_token>
```

User hanya bisa mengakses:
- System prompts (is_system = true)
- Prompts milik sendiri (user_id = current user)

---

## Endpoints

### 1. List All Accessible Prompts

Mengambil daftar prompts yang accessible oleh user (system + owned prompts) dengan optional filtering.

**Endpoint:** `GET /api/prompts`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| type | string | No | - | Filter by prompt type (chat, prose, replacement, summary) |
| search | string | No | - | Search dalam name dan description |

**Response:** `200 OK`

```json
{
  "prompts": [
    {
      "id": 1,
      "user_id": null,
      "folder_id": null,
      "name": "Creative Writing Assistant",
      "description": "Helps with creative prose writing",
      "type": "prose",
      "type_label": "Scene Beat Completion",
      "system_message": "You are a creative writing assistant...",
      "user_message": "Help me write: {text}",
      "model_settings": {
        "temperature": 0.8,
        "max_tokens": 500
      },
      "is_system": true,
      "is_active": true,
      "sort_order": 0,
      "usage_count": 45,
      "created_at": "2026-01-03T10:00:00.000Z",
      "updated_at": "2026-01-03T10:00:00.000Z"
    }
  ]
}
```

**Example:**

```bash
# Get all prompts
curl -X GET https://api.yourapp.com/api/prompts \
  -H "Authorization: Bearer <token>"

# Filter by type
curl -X GET "https://api.yourapp.com/api/prompts?type=chat" \
  -H "Authorization: Bearer <token>"

# Search prompts
curl -X GET "https://api.yourapp.com/api/prompts?search=creative" \
  -H "Authorization: Bearer <token>"
```

---

### 2. Get Available Prompt Types

Mengambil daftar tipe prompts yang tersedia dengan labels untuk display.

**Endpoint:** `GET /api/prompts/types`

**Response:** `200 OK`

```json
{
  "types": {
    "chat": "Workshop Chat",
    "prose": "Scene Beat Completion",
    "replacement": "Text Replacement",
    "summary": "Scene Summarization"
  }
}
```

**Example:**

```bash
curl -X GET https://api.yourapp.com/api/prompts/types \
  -H "Authorization: Bearer <token>"
```

---

### 3. Get Prompts by Type

Mengambil prompts dengan tipe spesifik (untuk dropdown selectors).

**Endpoint:** `GET /api/prompts/type/{type}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| type | string | Prompt type: chat, prose, replacement, summary |

**Response:** `200 OK`

```json
{
  "prompts": [
    {
      "id": 2,
      "name": "Chat Companion",
      "type": "chat",
      "type_label": "Workshop Chat",
      "description": "Interactive chat assistant",
      "is_system": true,
      "usage_count": 120,
      ...
    }
  ]
}
```

**Error Response:** `400 Bad Request`

```json
{
  "error": "Invalid prompt type."
}
```

**Example:**

```bash
curl -X GET https://api.yourapp.com/api/prompts/type/chat \
  -H "Authorization: Bearer <token>"
```

---

### 4. Get Prompt Detail

Mengambil detail lengkap dari sebuah prompt.

**Endpoint:** `GET /api/prompts/{prompt}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| prompt | integer | Prompt ID |

**Response:** `200 OK`

```json
{
  "prompt": {
    "id": 5,
    "user_id": 123,
    "folder_id": null,
    "name": "My Custom Prompt",
    "description": "Personal writing assistant",
    "type": "prose",
    "type_label": "Scene Beat Completion",
    "system_message": "You are a helpful writing assistant...",
    "user_message": "Please help me with: {input}",
    "model_settings": {
      "temperature": 0.7,
      "max_tokens": 1000,
      "top_p": 1.0
    },
    "is_system": false,
    "is_active": true,
    "sort_order": 2,
    "usage_count": 8,
    "created_at": "2026-01-03T14:30:00.000Z",
    "updated_at": "2026-01-03T15:00:00.000Z"
  }
}
```

**Error Response:** `403 Forbidden`

```json
{
  "message": "You do not have access to this prompt."
}
```

**Error Response:** `404 Not Found`

```json
{
  "message": "No query results for model [Prompt] {id}"
}
```

**Example:**

```bash
curl -X GET https://api.yourapp.com/api/prompts/5 \
  -H "Authorization: Bearer <token>"
```

---

### 5. Create New Prompt

Membuat prompt baru milik user.

**Endpoint:** `POST /api/prompts`

**Request Body:**

```json
{
  "name": "My Writing Assistant",
  "description": "Custom assistant for creative writing",
  "type": "prose",
  "system_message": "You are a creative writing assistant focused on fiction.",
  "user_message": "Help me write: {input}",
  "model_settings": {
    "temperature": 0.8,
    "max_tokens": 500
  },
  "is_active": true,
  "folder_id": null
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| name | string | Yes | Max 255 chars |
| description | string | No | - |
| type | string | Yes | Enum: chat, prose, replacement, summary |
| system_message | string | No | - |
| user_message | string | No | - |
| model_settings | object | No | Valid JSON |
| is_active | boolean | No | Default: true |
| folder_id | integer | No | Must exist in prompt_folders |

**Response:** `201 Created`

```json
{
  "prompt": {
    "id": 42,
    "user_id": 123,
    "name": "My Writing Assistant",
    "description": "Custom assistant for creative writing",
    "type": "prose",
    "type_label": "Scene Beat Completion",
    "system_message": "You are a creative writing assistant focused on fiction.",
    "user_message": "Help me write: {input}",
    "model_settings": {
      "temperature": 0.8,
      "max_tokens": 500
    },
    "is_system": false,
    "is_active": true,
    "sort_order": 5,
    "usage_count": 0,
    "created_at": "2026-01-03T16:00:00.000Z",
    "updated_at": "2026-01-03T16:00:00.000Z"
  },
  "message": "Prompt created successfully."
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "type": ["The selected type is invalid."]
  }
}
```

**Example:**

```bash
curl -X POST https://api.yourapp.com/api/prompts \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Writing Assistant",
    "type": "prose",
    "system_message": "You are a helpful assistant."
  }'
```

---

### 6. Update Prompt

Update prompt yang sudah ada. Hanya bisa update prompts milik sendiri (non-system).

**Endpoint:** `PATCH /api/prompts/{prompt}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| prompt | integer | Prompt ID |

**Request Body:** (Semua field optional)

```json
{
  "name": "Updated Prompt Name",
  "description": "Updated description",
  "system_message": "New system message",
  "user_message": "New user message",
  "model_settings": {
    "temperature": 0.9
  },
  "is_active": false
}
```

**Response:** `200 OK`

```json
{
  "prompt": {
    "id": 42,
    "name": "Updated Prompt Name",
    ...
  },
  "message": "Prompt updated successfully."
}
```

**Error Response:** `403 Forbidden`

```json
{
  "error": "You cannot edit this prompt."
}
```

**Example:**

```bash
curl -X PATCH https://api.yourapp.com/api/prompts/42 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "is_active": false
  }'
```

---

### 7. Delete Prompt

Hapus prompt. Hanya bisa hapus prompts milik sendiri (non-system).

**Endpoint:** `DELETE /api/prompts/{prompt}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| prompt | integer | Prompt ID |

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Prompt deleted successfully."
}
```

**Error Response:** `403 Forbidden`

```json
{
  "error": "You cannot delete this prompt."
}
```

**Example:**

```bash
curl -X DELETE https://api.yourapp.com/api/prompts/42 \
  -H "Authorization: Bearer <token>"
```

---

### 8. Clone Prompt

Duplicate prompt (system atau user) untuk customization. Clone akan menjadi user-owned prompt.

**Endpoint:** `POST /api/prompts/{prompt}/clone`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| prompt | integer | Prompt ID to clone |

**Request Body:**

```json
{
  "name": "My Custom Version"
}
```

**Validation Rules:**

| Field | Type | Required | Default |
|-------|------|----------|---------|
| name | string | No | "{original_name} (Copy)" |

**Response:** `201 Created`

```json
{
  "prompt": {
    "id": 43,
    "user_id": 123,
    "name": "My Custom Version",
    "description": "Helps with creative prose writing",
    "type": "prose",
    "system_message": "You are a creative writing assistant...",
    "user_message": "Help me write: {text}",
    "is_system": false,
    "is_active": true,
    "sort_order": 6,
    "usage_count": 0,
    "created_at": "2026-01-03T17:00:00.000Z",
    "updated_at": "2026-01-03T17:00:00.000Z"
  },
  "message": "Prompt cloned successfully."
}
```

**Error Response:** `403 Forbidden`

```json
{
  "message": "You do not have access to this prompt."
}
```

**Example:**

```bash
# Clone with custom name
curl -X POST https://api.yourapp.com/api/prompts/1/clone \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Modified System Prompt"
  }'

# Clone with auto-generated name
curl -X POST https://api.yourapp.com/api/prompts/1/clone \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{}'
```

---

### 9. Record Prompt Usage

Increment usage count untuk tracking popularity.

**Endpoint:** `POST /api/prompts/{prompt}/usage`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| prompt | integer | Prompt ID |

**Request Body:** Empty

**Response:** `200 OK`

```json
{
  "success": true
}
```

**Error Response:** `403 Forbidden`

```json
{
  "message": "You do not have access to this prompt."
}
```

**Example:**

```bash
curl -X POST https://api.yourapp.com/api/prompts/5/usage \
  -H "Authorization: Bearer <token>"
```

---

### 10. Reorder Prompts

Update sort order untuk multiple prompts sekaligus (untuk drag-and-drop).

**Endpoint:** `POST /api/prompts/reorder`

**Request Body:**

```json
{
  "order": {
    "42": 0,
    "43": 1,
    "44": 2,
    "45": 3
  }
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| order | object | Yes | Keys: prompt IDs, Values: sort order (integer) |
| order.* | integer | Yes | Must be integer |

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Prompts reordered successfully."
}
```

**Note:** Hanya prompts milik user yang akan di-update. System prompts ignored.

**Example:**

```bash
curl -X POST https://api.yourapp.com/api/prompts/reorder \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "order": {
      "42": 0,
      "43": 1,
      "44": 2
    }
  }'
```

---

---

## Prompt Inputs API

### 11. List Prompt Inputs

Mengambil daftar inputs untuk sebuah prompt.

**Endpoint:** `GET /api/prompts/{prompt}/inputs`

**Response:** `200 OK`

```json
{
  "inputs": [
    {
      "id": 1,
      "prompt_id": 42,
      "name": "word_count",
      "label": "Word Count",
      "type": "select",
      "options": [
        { "value": "500", "label": "Short (500 words)" },
        { "value": "1000", "label": "Medium (1000 words)" }
      ],
      "default_value": "500",
      "placeholder": null,
      "description": "Target word count",
      "is_required": true,
      "sort_order": 0
    }
  ]
}
```

---

### 12. Create Prompt Input

**Endpoint:** `POST /api/prompts/{prompt}/inputs`

**Request Body:**

```json
{
  "name": "word_count",
  "label": "Word Count",
  "type": "select",
  "options": [
    { "value": "500", "label": "Short" },
    { "value": "1000", "label": "Medium" }
  ],
  "default_value": "500",
  "is_required": true
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| name | string | Yes | Max 100, regex: `/^[a-z_][a-z0-9_]*$/i` |
| label | string | Yes | Max 255 |
| type | string | Yes | Enum: text, textarea, select, number, checkbox |
| options | array | No | Required if type=select |
| default_value | string | No | - |
| placeholder | string | No | - |
| description | string | No | - |
| is_required | boolean | No | Default: false |
| sort_order | integer | No | Default: 0 |

**Response:** `201 Created`

---

### 13. Update Prompt Input

**Endpoint:** `PATCH /api/prompts/{prompt}/inputs/{input}`

**Request Body:** Same as create, all fields optional.

**Response:** `200 OK`

---

### 14. Delete Prompt Input

**Endpoint:** `DELETE /api/prompts/{prompt}/inputs/{input}`

**Response:** `200 OK`

---

### 15. Bulk Update Inputs (Reorder)

**Endpoint:** `PUT /api/prompts/{prompt}/inputs/bulk`

**Request Body:**

```json
{
  "inputs": [
    { "id": 1, "sort_order": 0 },
    { "id": 2, "sort_order": 1 }
  ]
}
```

**Response:** `200 OK`

---

## Prompt Components API

Base URL: `/api/prompt-components`

### 16. List Components

Mengambil daftar komponen yang accessible (system + user-owned).

**Endpoint:** `GET /api/prompt-components`

**Response:** `200 OK`

```json
{
  "components": [
    {
      "id": 1,
      "user_id": 123,
      "name": "genre_fantasy",
      "label": "Fantasy Genre Rules",
      "content": "When writing fantasy, consider world-building...",
      "description": "Guidelines for fantasy writing",
      "is_system": false,
      "created_at": "2026-01-03T10:00:00.000Z",
      "updated_at": "2026-01-03T10:00:00.000Z"
    }
  ]
}
```

---

### 17. Create Component

**Endpoint:** `POST /api/prompt-components`

**Request Body:**

```json
{
  "name": "genre_fantasy",
  "label": "Fantasy Genre Rules",
  "content": "When writing fantasy...",
  "description": "Guidelines for fantasy"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| name | string | Yes | Max 100, regex: `/^[a-z_][a-z0-9_]*$/i`, unique per user |
| label | string | Yes | Max 255 |
| content | string | Yes | - |
| description | string | No | - |

**Response:** `201 Created`

---

### 18. Get Component Detail

**Endpoint:** `GET /api/prompt-components/{component}`

**Response:** `200 OK`

---

### 19. Update Component

**Endpoint:** `PATCH /api/prompt-components/{component}`

**Response:** `200 OK`

---

### 20. Delete Component

**Endpoint:** `DELETE /api/prompt-components/{component}`

**Response:** `200 OK`

---

### 21. Clone Component

**Endpoint:** `POST /api/prompt-components/{component}/clone`

**Response:** `201 Created`

---

## Data Structures

### Prompt Object

```typescript
interface Prompt {
  id: number;
  user_id: number | null;
  folder_id: number | null;
  name: string;
  description: string | null;
  type: 'chat' | 'prose' | 'replacement' | 'summary';
  type_label: string;
  system_message: string | null;
  user_message: string | null;
  messages: PromptMessage[] | null;  // NEW: Multi-message support
  model_settings: ModelSettings | null;
  is_system: boolean;
  is_active: boolean;
  sort_order: number;
  usage_count: number;
  inputs: PromptInput[];  // NEW: Dynamic inputs
  created_at: string;
  updated_at: string;
}

// NEW: Multi-message structure
interface PromptMessage {
  id: string;
  role: 'user' | 'assistant';
  content: string;
}

// NEW: Prompt Input definition
interface PromptInput {
  id: number;
  prompt_id: number;
  name: string;
  label: string;
  type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
  options?: { value: string; label: string }[];
  default_value?: string | null;
  placeholder?: string | null;
  description?: string | null;
  is_required: boolean;
  sort_order: number;
}

// NEW: Prompt Component
interface PromptComponent {
  id: number;
  user_id: number;
  name: string;
  label: string;
  content: string;
  description?: string | null;
  is_system: boolean;
  created_at: string;
  updated_at: string;
}
```

### Model Settings Schema

Model settings adalah JSON object yang dapat berisi parameter AI model:

```typescript
interface ModelSettings {
  temperature?: number;        // 0.0 - 2.0, default: 0.7
  max_tokens?: number;         // 1 - 4096, default: varies by model
  top_p?: number;              // 0.0 - 1.0, default: 1.0
  frequency_penalty?: number;  // -2.0 - 2.0, default: 0
  presence_penalty?: number;   // -2.0 - 2.0, default: 0
  stop?: string[];            // Stop sequences
  [key: string]: any;         // Extensible untuk future parameters
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| UNAUTHENTICATED | 401 | Token tidak valid atau expired |
| FORBIDDEN | 403 | User tidak punya akses ke resource |
| NOT_FOUND | 404 | Prompt tidak ditemukan |
| VALIDATION_ERROR | 422 | Request body tidak valid |
| INVALID_TYPE | 400 | Prompt type tidak valid |

---

## Rate Limiting

API menggunakan Laravel Sanctum rate limiting:

- **Default:** 60 requests per minute per user
- **Headers:**
  - `X-RateLimit-Limit`: Total request limit
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Unix timestamp untuk reset

---

## Usage Examples

### Complete CRUD Flow

```javascript
// 1. Get available types
const typesResponse = await fetch('/api/prompts/types', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { types } = await typesResponse.json();

// 2. Create a new prompt
const createResponse = await fetch('/api/prompts', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'My Writing Helper',
    type: 'prose',
    system_message: 'You are a creative assistant.',
    model_settings: { temperature: 0.8 }
  })
});
const { prompt: newPrompt } = await createResponse.json();

// 3. List all prompts
const listResponse = await fetch('/api/prompts?type=prose', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { prompts } = await listResponse.json();

// 4. Update the prompt
const updateResponse = await fetch(`/api/prompts/${newPrompt.id}`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    description: 'Updated description'
  })
});

// 5. Record usage
await fetch(`/api/prompts/${newPrompt.id}/usage`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
});

// 6. Clone a system prompt
const cloneResponse = await fetch(`/api/prompts/1/clone`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'My Modified Version'
  })
});

// 7. Delete the prompt
await fetch(`/api/prompts/${newPrompt.id}`, {
  method: 'DELETE',
  headers: { 'Authorization': `Bearer ${token}` }
});
```

---

## Related Documentation

- **Sprint Documentation:** [Sprint 24: Prompts Library Core](../10-sprints/sprint-24-prompts-library-core.md)
- **Testing Guide:** [Prompts Testing](../06-testing/prompts-testing.md)
- **User Journeys:** [Prompts User Journeys](../07-user-journeys/prompts/)

---

*Last Updated: 2026-01-03*
