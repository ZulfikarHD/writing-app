# API Documentation: Personas & Presets

## Overview

API untuk mengelola Prompt Personas dan Prompt Presets dalam sistem prompts. Personas merupakan instruksi AI yang dapat dibagikan across multiple prompts dan projects, sementara Presets adalah konfigurasi model settings dan input values yang tersimpan untuk prompt tertentu.

**Base URL:** `https://app.example.com/api`  
**Authentication:** Semua endpoint memerlukan session authentication (web middleware)

---

## Prompt Personas

### List Personas

Mengambil daftar personas untuk user yang sedang login.

**Endpoint:** `GET /prompt-personas`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| include_archived | boolean | No | false | Sertakan personas yang diarchive |

**Response:** `200 OK`

```json
{
  "personas": [
    {
      "id": 1,
      "user_id": 1,
      "name": "My Writing Style",
      "description": "Personal writing guidelines",
      "system_message": "You are writing in a conversational, friendly tone...",
      "interaction_types": ["chat", "prose"],
      "project_ids": null,
      "is_default": true,
      "is_archived": false,
      "created_at": "2026-01-03T09:30:00.000000Z",
      "updated_at": "2026-01-03T09:30:00.000000Z"
    }
  ]
}
```

---

### Get Personas for Context

Mengambil personas yang applicable untuk interaction type dan project tertentu.

**Endpoint:** `GET /prompt-personas/context`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | No | Interaction type (chat, prose, replacement, summary) |
| project_id | integer | No | Project ID untuk filtering |

**Response:** `200 OK`

```json
{
  "personas": [
    {
      "id": 1,
      "name": "Default Style",
      "system_message": "...",
      "interaction_types": ["chat"],
      "project_ids": null
    }
  ]
}
```

---

### Get Interaction Types

Mengambil daftar interaction types yang tersedia.

**Endpoint:** `GET /prompt-personas/interaction-types`

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

---

### Create Persona

Membuat persona baru.

**Endpoint:** `POST /prompt-personas`

**Request Body:**

```json
{
  "name": "My Writing Style",
  "description": "Personal writing guidelines",
  "system_message": "You are writing in a conversational tone...",
  "interaction_types": ["chat", "prose"],
  "project_ids": null,
  "is_default": false
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | Max 255 chars |
| description | string | No | - |
| system_message | string | Yes | - |
| interaction_types | array | No | Must be valid types |
| project_ids | array | No | Array of integers |
| is_default | boolean | No | Default: false |

**Response:** `201 Created`

```json
{
  "persona": {
    "id": 2,
    "user_id": 1,
    "name": "My Writing Style",
    ...
  },
  "message": "Persona created successfully."
}
```

---

### Update Persona

Update persona yang ada.

**Endpoint:** `PATCH /prompt-personas/{id}`

**Request Body:** (Semua field optional)

```json
{
  "name": "Updated Name",
  "system_message": "Updated message...",
  "interaction_types": ["chat"],
  "is_archived": false
}
```

**Response:** `200 OK`

```json
{
  "persona": { ... },
  "message": "Persona updated successfully."
}
```

---

### Archive Persona

Archive persona (soft delete).

**Endpoint:** `POST /prompt-personas/{id}/archive`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Persona archived successfully."
}
```

---

### Restore Persona

Restore persona yang diarchive.

**Endpoint:** `POST /prompt-personas/{id}/restore`

**Response:** `200 OK`

```json
{
  "persona": { ... },
  "message": "Persona restored successfully."
}
```

---

### Delete Persona

Delete persona permanently.

**Endpoint:** `DELETE /prompt-personas/{id}`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Persona deleted successfully."
}
```

---

## Prompt Presets

### List All Presets

Mengambil semua presets untuk user.

**Endpoint:** `GET /prompt-presets`

**Response:** `200 OK`

```json
{
  "presets": [
    {
      "id": 1,
      "user_id": 1,
      "prompt_id": 5,
      "name": "High Creativity",
      "model": "gpt-4",
      "temperature": 0.9,
      "max_tokens": 2048,
      "top_p": 1.0,
      "frequency_penalty": 0.5,
      "presence_penalty": 0.5,
      "stop_sequences": null,
      "input_values": {"tone": "casual"},
      "is_default": true,
      "prompt": {
        "id": 5,
        "name": "Character Development",
        "type": "chat"
      },
      "created_at": "2026-01-03T09:30:00.000000Z",
      "updated_at": "2026-01-03T09:30:00.000000Z"
    }
  ]
}
```

---

### Get Presets for Prompt

Mengambil presets untuk prompt tertentu.

**Endpoint:** `GET /prompts/{promptId}/presets`

**Response:** `200 OK`

```json
{
  "presets": [
    {
      "id": 1,
      "name": "High Creativity",
      "temperature": 0.9,
      ...
    }
  ]
}
```

---

### Create Preset

Membuat preset baru untuk prompt.

**Endpoint:** `POST /prompts/{promptId}/presets`

**Request Body:**

```json
{
  "name": "High Creativity",
  "model": "gpt-4",
  "temperature": 0.9,
  "max_tokens": 2048,
  "top_p": 1.0,
  "frequency_penalty": 0.5,
  "presence_penalty": 0.5,
  "stop_sequences": ["END", "STOP"],
  "input_values": {
    "character_name": "John",
    "setting": "Medieval"
  },
  "is_default": false
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | Max 255 chars |
| model | string | No | Max 255 chars |
| temperature | number | No | 0.0 - 2.0 |
| max_tokens | integer | No | Min 1 |
| top_p | number | No | 0.0 - 1.0 |
| frequency_penalty | number | No | -2.0 - 2.0 |
| presence_penalty | number | No | -2.0 - 2.0 |
| stop_sequences | array | No | Array of strings |
| input_values | object | No | JSON object |
| is_default | boolean | No | Default: false |

**Response:** `201 Created`

```json
{
  "preset": { ... },
  "message": "Preset created successfully."
}
```

---

### Update Preset

Update preset yang ada.

**Endpoint:** `PATCH /prompt-presets/{id}`

**Request Body:** (Semua field optional)

```json
{
  "name": "Updated Name",
  "temperature": 0.8,
  "is_default": true
}
```

**Response:** `200 OK`

```json
{
  "preset": { ... },
  "message": "Preset updated successfully."
}
```

---

### Set as Default

Set preset sebagai default untuk prompt-nya.

**Endpoint:** `POST /prompt-presets/{id}/set-default`

**Response:** `200 OK`

```json
{
  "preset": { ... },
  "message": "Preset set as default successfully."
}
```

---

### Delete Preset

Delete preset permanently.

**Endpoint:** `DELETE /prompt-presets/{id}`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Preset deleted successfully."
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | Session tidak valid |
| FORBIDDEN | 403 | Tidak punya akses ke resource |
| RESOURCE_NOT_FOUND | 404 | Persona/Preset tidak ditemukan |
| INTERNAL_ERROR | 500 | Server error |

---

## Related Documentation

- **Testing Guide:** [Personas & Presets Testing](../06-testing/personas-presets-testing.md)
- **User Journeys:** [Personas & Presets User Journeys](../07-user-journeys/personas-presets/)
- **Sprint Documentation:** [Sprint 26: Personas & Presets](../10-sprints/sprint-26-personas-presets.md)

---

*Last Updated: 2026-01-03*
