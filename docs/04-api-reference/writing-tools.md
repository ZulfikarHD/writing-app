# API Documentation: Writing Tools

## Overview

API untuk mengelola advanced writing tools dalam manuscript editor, yaitu: text highlighting, scene beats dengan AI expansion, subplot assignment melalui progressions, dan text formatting.

Base URL: `https://app.example.com/api`

## Authentication

Semua endpoint memerlukan session-based authentication melalui Laravel Sanctum.

```
Cookie: laravel_session=...
X-CSRF-TOKEN: ...
```

---

## Scene Subplots Endpoints

### List Scene Subplots

Mengambil daftar subplot yang di-assign ke scene melalui progressions.

**Endpoint:** `GET /scenes/{scene}/subplots`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Response:** `200 OK`

```json
{
  "subplots": [
    {
      "progression_id": 15,
      "id": 42,
      "name": "Romance Subplot",
      "note": "First meeting of the lovers"
    }
  ]
}
```

**Authorization:**
- User must own the novel containing the scene
- Returns `403 Forbidden` if user tidak memiliki akses

---

### Assign Subplot to Scene

Membuat CodexProgression untuk link subplot ke scene.

**Endpoint:** `POST /scenes/{scene}/subplots`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Request Body:**

```json
{
  "codex_entry_id": 42,
  "note": "First meeting of the lovers"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| codex_entry_id | integer | Yes | Must exist, must be TYPE_SUBPLOT, same novel |
| note | string | No | Max 1000 chars |

**Response:** `201 Created`

```json
{
  "progression": {
    "id": 15,
    "codex_entry_id": 42,
    "scene_id": 10,
    "note": "First meeting of the lovers"
  }
}
```

**Response:** `200 OK` (if progression already exists)

```json
{
  "progression": {
    "id": 15,
    "codex_entry_id": 42,
    "scene_id": 10,
    "note": "Updated note"
  }
}
```

**Error Responses:**

`403 Forbidden`
```json
{
  "message": "This action is unauthorized."
}
```

`404 Not Found` (if entry is not subplot or wrong novel)
```json
{
  "message": "No query results for model [App\\Models\\CodexEntry]."
}
```

---

### Remove Subplot from Scene

Menghapus CodexProgression yang menghubungkan subplot dengan scene.

**Endpoint:** `DELETE /scenes/{scene}/subplots/{codexEntry}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |
| codexEntry | integer | CodexEntry ID (must be subplot) |

**Response:** `200 OK`

```json
{
  "success": true,
  "deleted": 1
}
```

**Response:** `200 OK` (if progression doesn't exist)

```json
{
  "success": false,
  "deleted": 0
}
```

---

## Novel Subplots Endpoints

### List Novel Subplots

Mengambil daftar semua subplot entries untuk novel.

**Endpoint:** `GET /novels/{novel}/codex/subplots`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Response:** `200 OK`

```json
{
  "subplots": [
    {
      "id": 42,
      "name": "Romance Subplot",
      "description": "Love story between protagonist and...",
      "aliases": ["Romance Arc", "Love Story"]
    },
    {
      "id": 43,
      "name": "Mystery Subplot",
      "description": "Investigation of the murder...",
      "aliases": []
    }
  ]
}
```

**Filters:**
- Only returns entries with `type = 'subplot'`
- Only returns active (non-archived) entries
- Ordered by name alphabetically

---

## Section Endpoints

### Update Section

Update section properties termasuk `is_completed` untuk beat sections.

**Endpoint:** `PATCH /sections/{section}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | SceneSection ID |

**Request Body:**

```json
{
  "is_completed": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| type | string | No | Enum: content, note, alternative, beat |
| title | string | No | Max 255 chars |
| content | array | No | TipTap JSON structure |
| color | string | No | Hex color, 7 chars |
| is_collapsed | boolean | No | - |
| exclude_from_ai | boolean | No | - |
| is_completed | boolean | No | - |

**Response:** `200 OK`

```json
{
  "section": {
    "id": 25,
    "scene_id": 10,
    "type": "beat",
    "title": "Scene Planning",
    "content": {...},
    "color": "#22c55e",
    "is_collapsed": false,
    "exclude_from_ai": false,
    "is_completed": true,
    "sort_order": 0,
    "word_count": 45,
    "created_at": "2026-01-04T10:00:00Z",
    "updated_at": "2026-01-04T10:30:00Z"
  }
}
```

---

## Data Structures

### CodexProgression

```typescript
interface CodexProgression {
  id: number;
  codex_entry_id: number;
  scene_id: number;
  note: string;
  mode: 'addition' | 'change' | 'removal';
  sort_order: number;
  created_at: string;
  updated_at: string;
}
```

### CodexEntry (Subplot)

```typescript
interface CodexEntry {
  id: number;
  novel_id: number;
  type: 'subplot';
  name: string;
  description: string | null;
  aliases: string[];
  archived_at: string | null;
}
```

### SceneSection (Beat)

```typescript
interface SceneSection {
  id: number;
  scene_id: number;
  type: 'beat' | 'content' | 'note' | 'alternative';
  title: string | null;
  content: TipTapJSON;
  color: string;
  is_collapsed: boolean;
  exclude_from_ai: boolean;
  is_completed: boolean;  // For beat sections
  sort_order: number;
  word_count: number;
  created_at: string;
  updated_at: string;
}
```

### HighlightMark (TipTap JSON)

```typescript
// In TipTap content JSON
{
  "type": "text",
  "text": "highlighted text",
  "marks": [
    {
      "type": "highlight",
      "attrs": {
        "color": "#fef08a"  // Yellow
      }
    }
  ]
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | Token tidak valid atau expired |
| FORBIDDEN | 403 | Tidak punya akses ke resource |
| NOT_FOUND | 404 | Resource tidak ditemukan |
| CONFLICT | 409 | Resource sudah ada (duplicate) |
| INTERNAL_ERROR | 500 | Server error |

---

## Business Rules

### Subplot Assignment

1. **Same Novel Only**: Subplot harus dari novel yang sama dengan scene
2. **Type Validation**: CodexEntry harus bertipe `subplot`, bukan `character` atau `location`
3. **Single Progression**: Satu scene + subplot combination hanya boleh punya 1 progression
4. **Auto Note**: Jika note tidak provided, auto-generate: "Scene: {scene_title}"
5. **Mode Default**: Progression dibuat dengan mode `addition` by default

### Beat Section Completion

1. **Beat Type Only**: Field `is_completed` hanya relevant untuk `type = 'beat'`
2. **Independent Tracking**: Completion status independent dari AI visibility
3. **No Auto-complete**: Completion harus di-toggle manual oleh user
4. **Persist State**: Completion state persists across page reloads

### Text Highlighting

1. **Color Validation**: Hanya accept 6 preset colors dari `HIGHLIGHT_COLORS`
2. **Persistent Storage**: Highlights disimpan dalam scene content JSON
3. **Merge Behavior**: Multiple highlights pada text yang sama merge menjadi 1
4. **Remove All**: Unset highlight removes semua highlight colors dari selection

---

## Rate Limiting

- **General Endpoints**: 60 requests per minute per user
- **AI Endpoints** (prose generation): 10 requests per minute per user
- **Bulk Operations**: 30 requests per minute per user

---

*Last Updated: 2026-01-04*
