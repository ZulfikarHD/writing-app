# API Documentation: AI Connections

## Overview

API untuk mengelola koneksi AI providers yang memungkinkan user untuk menghubungkan berbagai layanan AI (OpenAI, Anthropic, OpenRouter, Ollama, Groq, LM Studio, dan OpenAI-compatible providers), yaitu: menyimpan dan mengelola API keys dengan aman, melakukan testing koneksi, dan mengambil daftar model yang tersedia dari setiap provider.

**Base URL:** `/api/ai-connections`

**Authentication:** Semua endpoint memerlukan user authentication melalui Laravel session/Sanctum.

---

## Supported Providers

| Provider | Requires API Key | Default Base URL | Description |
|----------|------------------|------------------|-------------|
| `openai` | ✅ Yes | `https://api.openai.com/v1` | OpenAI GPT models |
| `anthropic` | ✅ Yes | `https://api.anthropic.com/v1` | Claude models |
| `openrouter` | ✅ Yes | `https://openrouter.ai/api/v1` | Unified API for multiple providers |
| `ollama` | ❌ No | `http://localhost:11434` | Local LLM server |
| `groq` | ✅ Yes | `https://api.groq.com/openai/v1` | Fast inference cloud |
| `lmstudio` | ❌ No | `http://localhost:1234/v1` | Local LM Studio |
| `openai_compatible` | ⚠️ Optional | User-defined | Generic OpenAI-compatible API |

---

## Endpoints

### 1. Get Available Providers

Mengambil daftar semua AI providers yang didukung beserta konfigurasi default mereka.

**Endpoint:** `GET /api/ai-connections/providers`

**Authentication:** Required

**Response:** `200 OK`

```json
{
  "openai": {
    "name": "OpenAI",
    "requires_api_key": true,
    "default_base_url": "https://api.openai.com/v1"
  },
  "anthropic": {
    "name": "Anthropic (Claude)",
    "requires_api_key": true,
    "default_base_url": "https://api.anthropic.com/v1"
  },
  "openrouter": {
    "name": "OpenRouter",
    "requires_api_key": true,
    "default_base_url": "https://openrouter.ai/api/v1"
  },
  "ollama": {
    "name": "Ollama (Local)",
    "requires_api_key": false,
    "default_base_url": "http://localhost:11434"
  }
}
```

---

### 2. List User Connections

Mengambil daftar semua AI connections milik user yang sedang login, termasuk status terakhir dan informasi koneksi.

**Endpoint:** `GET /api/ai-connections`

**Authentication:** Required

**Response:** `200 OK`

```json
[
  {
    "id": 1,
    "provider": "openai",
    "provider_name": "OpenAI",
    "name": "Production OpenAI",
    "api_key_masked": "sk-...xyz",
    "base_url": "https://api.openai.com/v1",
    "settings": null,
    "is_active": true,
    "is_default": true,
    "last_tested_at": "2026-01-01T10:30:00.000000Z",
    "last_test_status": "success",
    "created_at": "2026-01-01T08:00:00.000000Z",
    "updated_at": "2026-01-01T10:30:00.000000Z"
  },
  {
    "id": 2,
    "provider": "ollama",
    "provider_name": "Ollama (Local)",
    "name": "Local Development",
    "api_key_masked": null,
    "base_url": "http://localhost:11434",
    "settings": null,
    "is_active": true,
    "is_default": false,
    "last_tested_at": null,
    "last_test_status": null,
    "created_at": "2026-01-01T09:00:00.000000Z",
    "updated_at": "2026-01-01T09:00:00.000000Z"
  }
]
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Connection ID |
| `provider` | string | Provider identifier (enum) |
| `provider_name` | string | Human-readable provider name |
| `name` | string | User-defined connection name |
| `api_key_masked` | string\|null | Masked API key (e.g., "sk-...xyz") |
| `base_url` | string | API base URL |
| `settings` | object\|null | Provider-specific settings (JSON) |
| `is_active` | boolean | Connection active status |
| `is_default` | boolean | Default connection flag |
| `last_tested_at` | string\|null | ISO 8601 timestamp of last test |
| `last_test_status` | string\|null | `success`, `failed`, or `null` |
| `created_at` | string | ISO 8601 creation timestamp |
| `updated_at` | string | ISO 8601 update timestamp |

---

### 3. Get Connection by ID

Mengambil detail lengkap dari satu AI connection berdasarkan ID.

**Endpoint:** `GET /api/ai-connections/{id}`

**Authentication:** Required

**Authorization:** User hanya dapat mengakses connection miliknya sendiri.

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Connection ID |

**Response:** `200 OK`

```json
{
  "id": 1,
  "provider": "openai",
  "provider_name": "OpenAI",
  "name": "Production OpenAI",
  "api_key_masked": "sk-...xyz",
  "base_url": "https://api.openai.com/v1",
  "settings": null,
  "is_active": true,
  "is_default": true,
  "last_tested_at": "2026-01-01T10:30:00.000000Z",
  "last_test_status": "success",
  "created_at": "2026-01-01T08:00:00.000000Z",
  "updated_at": "2026-01-01T10:30:00.000000Z"
}
```

**Error Responses:**

**`403 Forbidden`** - User tidak memiliki akses ke connection ini

```json
{
  "message": "This action is unauthorized."
}
```

**`404 Not Found`** - Connection tidak ditemukan

```json
{
  "message": "No query results for model [App\\Models\\AIConnection] {id}"
}
```

---

### 4. Create Connection

Membuat AI connection baru untuk user yang sedang login.

**Endpoint:** `POST /api/ai-connections`

**Authentication:** Required

**Request Body:**

```json
{
  "provider": "openai",
  "name": "My OpenAI Connection",
  "api_key": "sk-1234567890abcdefghijklmnopqrstuvwxyz",
  "base_url": "https://api.openai.com/v1",
  "settings": null,
  "is_default": false
}
```

**Request Fields:**

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `provider` | string | ✅ Yes | Must be valid provider from enum | AI provider identifier |
| `name` | string | ✅ Yes | Min 2, Max 100 chars | User-defined connection name |
| `api_key` | string | ⚠️ Conditional | Required if provider needs API key | API key from provider |
| `base_url` | string | ❌ No | Valid URL format | Custom base URL (uses default if omitted) |
| `settings` | object | ❌ No | Valid JSON | Provider-specific settings |
| `is_default` | boolean | ❌ No | Boolean | Set as default connection (default: false) |

**Response:** `201 Created`

```json
{
  "id": 3,
  "provider": "openai",
  "provider_name": "OpenAI",
  "name": "My OpenAI Connection",
  "api_key_masked": "sk-...xyz",
  "base_url": "https://api.openai.com/v1",
  "settings": null,
  "is_active": true,
  "is_default": false,
  "last_tested_at": null,
  "last_test_status": null,
  "created_at": "2026-01-01T11:00:00.000000Z",
  "updated_at": "2026-01-01T11:00:00.000000Z"
}
```

**Error Responses:**

**`422 Unprocessable Entity`** - Validation error

```json
{
  "message": "The provider field is required. (and 1 more error)",
  "errors": {
    "provider": [
      "The provider field is required."
    ],
    "api_key": [
      "An API key is required for this provider."
    ]
  }
}
```

**Validation Rules:**

- Provider harus salah satu dari: `openai`, `anthropic`, `openrouter`, `ollama`, `groq`, `lmstudio`, `openai_compatible`
- Jika provider memerlukan API key, maka field `api_key` wajib diisi
- Name minimal 2 karakter, maksimal 100 karakter
- Base URL harus format URL yang valid jika diisi

---

### 5. Update Connection

Mengupdate AI connection yang ada. User hanya dapat mengupdate connection miliknya sendiri.

**Endpoint:** `PATCH /api/ai-connections/{id}`

**Authentication:** Required

**Authorization:** User hanya dapat mengupdate connection miliknya sendiri

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Connection ID |

**Request Body:**

```json
{
  "name": "Updated Connection Name",
  "api_key": "sk-new-api-key-here",
  "base_url": "https://api.openai.com/v1",
  "settings": {
    "custom_header": "value"
  },
  "is_active": true,
  "is_default": true
}
```

**Request Fields (All Optional):**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `name` | string | Min 2, Max 100 chars | Connection name |
| `api_key` | string | Required if provider needs key | New API key |
| `base_url` | string | Valid URL | Custom base URL |
| `settings` | object | Valid JSON | Provider settings |
| `is_active` | boolean | Boolean | Active status |
| `is_default` | boolean | Boolean | Default connection flag |

**Response:** `200 OK`

```json
{
  "id": 1,
  "provider": "openai",
  "provider_name": "OpenAI",
  "name": "Updated Connection Name",
  "api_key_masked": "sk-...key",
  "base_url": "https://api.openai.com/v1",
  "settings": {
    "custom_header": "value"
  },
  "is_active": true,
  "is_default": true,
  "last_tested_at": "2026-01-01T10:30:00.000000Z",
  "last_test_status": "success",
  "created_at": "2026-01-01T08:00:00.000000Z",
  "updated_at": "2026-01-01T11:30:00.000000Z"
}
```

**Error Responses:**

**`403 Forbidden`** - User tidak memiliki akses

```json
{
  "message": "This action is unauthorized."
}
```

**`422 Unprocessable Entity`** - Validation error

```json
{
  "message": "The name field must be at least 2 characters.",
  "errors": {
    "name": [
      "The name field must be at least 2 characters."
    ]
  }
}
```

---

### 6. Delete Connection

Menghapus AI connection. User hanya dapat menghapus connection miliknya sendiri.

**Endpoint:** `DELETE /api/ai-connections/{id}`

**Authentication:** Required

**Authorization:** User hanya dapat menghapus connection miliknya sendiri

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Connection ID |

**Response:** `204 No Content`

(No response body)

**Error Responses:**

**`403 Forbidden`** - User tidak memiliki akses

```json
{
  "message": "This action is unauthorized."
}
```

**`404 Not Found`** - Connection tidak ditemukan

```json
{
  "message": "No query results for model [App\\Models\\AIConnection] {id}"
}
```

---

### 7. Test Connection

Melakukan test koneksi ke AI provider untuk memverifikasi bahwa credentials dan base URL bekerja dengan benar.

**Endpoint:** `POST /api/ai-connections/{id}/test`

**Authentication:** Required

**Authorization:** User hanya dapat test connection miliknya sendiri

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Connection ID |

**Response:** `200 OK`

**Success Response:**

```json
{
  "success": true,
  "message": "Connection successful. Found 50 models.",
  "model_count": 50
}
```

**Failed Response:**

```json
{
  "success": false,
  "message": "Connection failed: Invalid API key. Please check your credentials.",
  "model_count": 0
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Connection test result |
| `message` | string | Human-readable status message |
| `model_count` | integer | Number of models found (0 if failed) |

**Side Effects:**
- Updates `last_tested_at` timestamp di database
- Updates `last_test_status` (`success` atau `failed`)

**Error Responses:**

**`403 Forbidden`** - User tidak memiliki akses

```json
{
  "message": "This action is unauthorized."
}
```

---

### 8. Fetch Available Models

Mengambil daftar model yang tersedia dari AI provider untuk connection tertentu.

**Endpoint:** `GET /api/ai-connections/{id}/models`

**Authentication:** Required

**Authorization:** User hanya dapat fetch models dari connection miliknya sendiri

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Connection ID |

**Response:** `200 OK`

**Success Response:**

```json
{
  "success": true,
  "models": [
    {
      "id": "gpt-4-turbo",
      "name": "GPT-4 Turbo",
      "context_length": 128000,
      "pricing": {
        "prompt": null,
        "completion": null
      }
    },
    {
      "id": "gpt-3.5-turbo",
      "name": "GPT-3.5 Turbo",
      "context_length": 16385,
      "pricing": {
        "prompt": null,
        "completion": null
      }
    }
  ]
}
```

**Failed Response:**

```json
{
  "success": false,
  "message": "Failed to fetch models: Connection timeout. Please check your network.",
  "models": []
}
```

**Model Object Structure:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Model identifier |
| `name` | string | Human-readable model name |
| `context_length` | integer | Maximum context window in tokens |
| `pricing` | object | Pricing info (may be null) |
| `pricing.prompt` | number\|null | Price per prompt token |
| `pricing.completion` | number\|null | Price per completion token |

**Provider-Specific Fields:**

For **OpenRouter**:
```json
{
  "id": "anthropic/claude-3-opus",
  "name": "Claude 3 Opus",
  "context_length": 200000,
  "pricing": {
    "prompt": 0.015,
    "completion": 0.075
  },
  "description": "Most powerful model, best for complex tasks",
  "top_provider": 200000,
  "per_request_limits": {
    "prompt_tokens": 100000,
    "completion_tokens": 4096
  }
}
```

**Error Responses:**

**`403 Forbidden`** - User tidak memiliki akses

```json
{
  "message": "This action is unauthorized."
}
```

---

## Common Error Codes

| HTTP Status | Error Code | Description | Common Causes |
|-------------|------------|-------------|---------------|
| `401 Unauthorized` | - | User tidak terautentikasi | Session expired, not logged in |
| `403 Forbidden` | - | User tidak punya akses | Trying to access other user's connection |
| `404 Not Found` | - | Resource tidak ditemukan | Invalid connection ID |
| `422 Unprocessable Entity` | `VALIDATION_ERROR` | Request body tidak valid | Missing required fields, invalid format |
| `500 Internal Server Error` | `INTERNAL_ERROR` | Server error | Database error, unexpected exception |

---

## Security Considerations

### API Key Encryption
- API keys disimpan terenkripsi di database menggunakan `Crypt::encrypt()`
- API keys tidak pernah dikembalikan dalam response (hanya versi masked)
- Masked format: `sk-...xyz` (4 karakter terakhir)

### Authorization
- Semua endpoint memerlukan authentication
- User hanya dapat CRUD connection miliknya sendiri
- Policy `AIConnectionPolicy` melakukan authorization check di setiap request

### Rate Limiting
- Test connection endpoint sebaiknya di-rate limit untuk mencegah abuse
- Fetch models endpoint juga sebaiknya di-limit

### Validation
- Provider harus dari enum yang didefinisikan
- API key required untuk provider yang memerlukan
- Base URL harus format URL yang valid

---

## Related Documentation

- **Testing Guide:** [AI Connections Testing](../06-testing/ai-connections-testing.md)
- **Sprint Documentation:** [Sprint 03 - AI Connections](../10-sprints/sprint-03-ai-connections.md)

---

*Last Updated: 2026-01-01*
