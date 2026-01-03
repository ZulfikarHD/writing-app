# API Documentation: AI Writing Features

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Status:** ✅ Complete

## Overview

API untuk mengelola fitur AI Writing dalam editor manuscript yang bertujuan untuk mempercepat prose writing dengan AI assistance, yaitu: prose generation dari beats, text replacement (expand/rephrase/shorten), dan AI-powered editing.

Base URL: `https://app.domain.com/api`

## Authentication

Semua endpoint memerlukan session-based authentication melalui Laravel Sanctum:
```
Cookie: laravel_session=<session_token>
X-XSRF-TOKEN: <csrf_token>
```

---

## Endpoints

### 1. Generate Prose from Beat

Generate prose dari scene beat menggunakan AI dengan streaming output.

**Endpoint:** `POST /scenes/{scene}/generate-prose`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Request Body:**

```json
{
  "mode": "scene_beat",
  "beat": "Elena discovers the hidden letter in the drawer. Emotional reaction.",
  "instructions": "Focus on sensory details",
  "content_before": "...text before cursor...",
  "prompt_id": 1,
  "connection_id": 1,
  "model": "gpt-4o-mini",
  "temperature": 0.8,
  "max_tokens": 2000
}
```

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| mode | string | No | Enum: scene_beat, continue, custom | Generation mode (default: scene_beat) |
| beat | string | Sometimes | Max 5000 chars | Scene beat untuk mode scene_beat |
| instructions | string | No | Max 2000 chars | Additional instructions |
| content_before | string | No | Max 50000 chars | Content before cursor for context |
| prompt_id | integer | No | Exists in prompts | Custom prompt ID |
| connection_id | integer | No | Exists in ai_connections | AI connection ID |
| model | string | No | Max 100 chars | Model name override |
| temperature | float | No | 0-2 | Model temperature |
| max_tokens | integer | No | 100-8000 | Max tokens to generate |

**Response:** `200 OK` (Server-Sent Events stream)

```
data: {"type":"start","model":"gpt-4o-mini"}

data: {"type":"content","content":"Elena's"}

data: {"type":"content","content":" fingers"}

data: {"type":"content","content":" trembled"}

...

data: {"type":"done","full_content":"Elena's fingers trembled...","tokens_input":450,"tokens_output":230}
```

**Event Types:**

| Type | Fields | Description |
|------|--------|-------------|
| start | model | Generation started |
| content | content | Streamed text chunk |
| error | error | Error occurred |
| done | full_content, tokens_input, tokens_output | Generation completed |

**Error Response:** `400 Bad Request`

```json
{
  "message": "The mode field must be one of: scene_beat, continue, custom.",
  "errors": {
    "mode": ["The mode field must be one of: scene_beat, continue, custom."]
  }
}
```

**Error Response:** `403 Forbidden`

```json
{
  "message": "This action is unauthorized."
}
```

---

### 2. Get Prose Generation Options

Mendapatkan daftar prompts dan connections untuk prose generation.

**Endpoint:** `GET /prose-generation/options`

**Query Parameters:** None

**Response:** `200 OK`

```json
{
  "modes": {
    "scene_beat": {
      "name": "Scene Beat",
      "description": "Write prose from a beat/outline"
    },
    "continue": {
      "name": "Continue Writing",
      "description": "Continue the story naturally"
    },
    "custom": {
      "name": "Custom",
      "description": "Use custom instructions"
    }
  },
  "prompts": [
    {
      "id": 1,
      "name": "Default Prose Generator",
      "description": "Generate prose from scene beats and outlines.",
      "is_system": true
    },
    {
      "id": 2,
      "name": "Dialogue-Heavy Prose",
      "description": "Generate prose with emphasis on dialogue and character interaction.",
      "is_system": true
    }
  ],
  "connections": [
    {
      "id": 1,
      "name": "OpenAI GPT-4",
      "provider": "openai",
      "is_default": true
    },
    {
      "id": 2,
      "name": "Anthropic Claude",
      "provider": "anthropic",
      "is_default": false
    }
  ]
}
```

---

### 3. Replace Selected Text

Transform selected text menggunakan AI (expand, rephrase, shorten) dengan streaming output.

**Endpoint:** `POST /text/replace`

**Request Body:**

```json
{
  "selected_text": "She walked into the room.",
  "type": "expand",
  "scene_id": 1,
  "prompt_id": null,
  "connection_id": 1,
  "model": "gpt-4o-mini",
  "expand_amount": "double",
  "expand_method": "sensory_details",
  "rephrase_options": [],
  "shorten_amount": null,
  "instructions": null,
  "temperature": 0.7,
  "max_tokens": 2000
}
```

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| selected_text | string | Yes | Min 1, Max 50000 chars | Selected text to transform |
| type | string | Yes | Enum: expand, rephrase, shorten, custom | Transformation type |
| scene_id | integer | No | Exists in scenes | Scene ID for context |
| prompt_id | integer | No | Exists in prompts | Custom prompt ID |
| connection_id | integer | No | Exists in ai_connections | AI connection ID |
| model | string | No | Max 100 chars | Model name override |
| expand_amount | string | No | Enum: slightly, double, triple | Expansion amount |
| expand_method | string | No | Enum: sensory_details, inner_thoughts, description, dialogue | Expansion method |
| rephrase_options | array | No | Valid option strings | Rephrase transformations |
| target_pov | string | No | Max 50 chars | Target POV for change_pov |
| target_tense | string | No | Max 50 chars | Target tense for change_tense |
| shorten_amount | string | No | Enum: half, quarter, single_paragraph | Shortening amount |
| instructions | string | No | Max 2000 chars | Custom instructions |
| temperature | float | No | 0-2 | Model temperature |
| max_tokens | integer | No | 100-8000 | Max tokens to generate |

**Rephrase Options:**

- `add_inner_thoughts` - Add inner thoughts
- `convert_to_dialogue` - Convert to dialogue
- `passive_to_active` - Convert passive to active voice
- `different_words` - Use different words
- `show_dont_tell` - Show, don't tell
- `change_pov` - Change POV (requires target_pov)
- `change_tense` - Change tense (requires target_tense)

**Response:** `200 OK` (Server-Sent Events stream)

```
data: {"type":"start","model":"gpt-4o-mini","original":"She walked into the room."}

data: {"type":"content","content":"She"}

data: {"type":"content","content":" hesitated"}

data: {"type":"content","content":" at"}

...

data: {"type":"done","full_content":"She hesitated at the threshold...","original":"She walked into the room.","tokens_input":150,"tokens_output":98}
```

**Error Response:** `400 Bad Request`

```json
{
  "message": "The selected text field is required.",
  "errors": {
    "selected_text": ["The selected text field is required."]
  }
}
```

**Error Response:** `422 Unprocessable Entity`

```json
{
  "message": "Please select at least 4 words to use text replacement."
}
```

---

### 4. Get Text Replacement Options

Mendapatkan daftar options untuk text replacement.

**Endpoint:** `GET /text-replacement/options`

**Query Parameters:** None

**Response:** `200 OK`

```json
{
  "types": {
    "expand": "Expand",
    "rephrase": "Rephrase",
    "shorten": "Shorten",
    "custom": "Custom"
  },
  "expand_amounts": {
    "slightly": "Slightly (~25-50%)",
    "double": "Double",
    "triple": "Triple"
  },
  "shorten_amounts": {
    "half": "Half",
    "quarter": "Quarter",
    "single_paragraph": "Single Paragraph"
  },
  "rephrase_options": {
    "add_inner_thoughts": "Add inner thoughts",
    "convert_to_dialogue": "Convert to dialogue",
    "passive_to_active": "Convert passive to active voice",
    "different_words": "Use different words",
    "show_dont_tell": "Show, don't tell",
    "change_pov": "Change POV",
    "change_tense": "Change tense"
  },
  "prompts": [
    {
      "id": 10,
      "name": "Expand",
      "description": "Expand the selected text with more detail and description.",
      "is_system": true
    },
    {
      "id": 11,
      "name": "Rephrase",
      "description": "Rewrite the text with different word choices while keeping the meaning.",
      "is_system": true
    }
  ],
  "connections": [
    {
      "id": 1,
      "name": "OpenAI GPT-4",
      "provider": "openai",
      "is_default": true
    }
  ]
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | Session tidak valid atau expired |
| FORBIDDEN | 403 | Tidak punya akses ke scene/resource |
| NOT_FOUND | 404 | Scene/resource tidak ditemukan |
| UNPROCESSABLE_ENTITY | 422 | Business rule validation failed |
| INTERNAL_ERROR | 500 | Server error |

## Business Rules

| Rule ID | Rule Description |
|---------|------------------|
| BR-01 | Text replacement requires minimum 4 words selection |
| BR-02 | User must have access to the scene's novel |
| BR-03 | AI connection must be active and belong to user |
| BR-04 | Prompt must be accessible by user (owned or system) |
| BR-05 | SSE stream automatically aborts if client disconnects |
| BR-06 | Scene beat mode requires 'beat' field |
| BR-07 | Context building includes scene content, beats, and codex |

## Rate Limiting

| Endpoint | Limit | Window |
|----------|-------|--------|
| All AI endpoints | 60 requests | Per minute per user |

## Related Documentation

- **Testing Guide:** [AI Writing Features Testing](../06-testing/ai-writing-features-testing.md)
- **User Journeys:** [AI Writing Features User Journeys](../07-user-journeys/ai-writing-features/)
- **Sprint Documentation:** [Sprint 31: AI Writing Features](../10-sprints/sprint-31-ai-writing-features.md)

---

*Last Updated: 2026-01-04*
