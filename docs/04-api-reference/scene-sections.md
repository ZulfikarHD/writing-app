# API Documentation: Scene Sections

## Overview

API untuk mengelola sections dalam scene, yaitu: organizational blocks yang membantu writers memisahkan prose dari notes, mengontrol AI context, dan kitbash multiple versions side-by-side.

**Base URL:** `https://app.example.com/api`

## Authentication

Semua endpoint memerlukan header:
```
Authorization: Bearer <access_token>
```

---

## Endpoints

### 1. List Scene Sections

Mengambil daftar sections dari sebuah scene, terurut berdasarkan `sort_order`.

**Endpoint:** `GET /scenes/{scene}/sections`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Response:** `200 OK`

```json
{
  "sections": [
    {
      "id": 1,
      "scene_id": 42,
      "type": "content",
      "title": "Opening paragraph",
      "content": { "type": "doc", "content": [...] },
      "color": "#6366f1",
      "is_collapsed": false,
      "exclude_from_ai": false,
      "sort_order": 0,
      "word_count": 250,
      "created_at": "2026-01-04T10:00:00.000000Z",
      "updated_at": "2026-01-04T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**
- `403 Forbidden` - User tidak memiliki akses ke scene

---

### 2. Create Section

Membuat section baru dalam scene.

**Endpoint:** `POST /scenes/{scene}/sections`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Request Body:**

```json
{
  "type": "content",
  "title": "Opening paragraph",
  "content": {
    "type": "doc",
    "content": [
      { "type": "paragraph" }
    ]
  },
  "color": "#6366f1",
  "sort_order": 0
}
```

| Field | Type | Required | Validation | Default |
|-------|------|----------|------------|---------|
| type | string | No | Enum: content, note, alternative, beat | content |
| title | string | No | Max 255 chars | null |
| content | object | No | Valid TipTap JSON | Empty doc |
| color | string | No | Hex color format (#RRGGBB) | Type default color |
| sort_order | integer | No | >= 0 | Auto-incremented |

**Type Default Colors:**
- `content`: `#6366f1` (Indigo)
- `note`: `#eab308` (Yellow)
- `alternative`: `#8b5cf6` (Violet)
- `beat`: `#22c55e` (Green)

**Type Default AI Visibility:**
- `content`: `exclude_from_ai = false`
- `note`: `exclude_from_ai = true`
- `alternative`: `exclude_from_ai = true`
- `beat`: `exclude_from_ai = false`

**Response:** `201 Created`

```json
{
  "section": {
    "id": 123,
    "scene_id": 42,
    "type": "content",
    "title": "Opening paragraph",
    "content": {...},
    "color": "#6366f1",
    "is_collapsed": false,
    "exclude_from_ai": false,
    "sort_order": 2,
    "word_count": 0,
    "created_at": "2026-01-04T10:00:00.000000Z",
    "updated_at": "2026-01-04T10:00:00.000000Z"
  }
}
```

---

### 3. Get Section

Mengambil detail section berdasarkan ID.

**Endpoint:** `GET /sections/{section}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `200 OK`

```json
{
  "section": {
    "id": 123,
    "scene_id": 42,
    "type": "content",
    "title": "Opening paragraph",
    "content": {...},
    "color": "#6366f1",
    "is_collapsed": false,
    "exclude_from_ai": false,
    "sort_order": 0,
    "word_count": 250,
    "created_at": "2026-01-04T10:00:00.000000Z",
    "updated_at": "2026-01-04T10:00:00.000000Z"
  }
}
```

**Error Responses:**
- `403 Forbidden` - User tidak memiliki akses ke section
- `404 Not Found` - Section tidak ditemukan

---

### 4. Update Section

Update section attributes (title, type, content, etc.).

**Endpoint:** `PATCH /sections/{section}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Request Body:**

```json
{
  "title": "Updated title",
  "type": "note",
  "content": {...},
  "color": "#eab308",
  "is_collapsed": true,
  "exclude_from_ai": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| type | string | No | Enum: content, note, alternative, beat |
| title | string | No | Max 255 chars |
| content | object | No | Valid TipTap JSON |
| color | string | No | Hex color format (#RRGGBB) |
| is_collapsed | boolean | No | true/false |
| exclude_from_ai | boolean | No | true/false |

**Response:** `200 OK`

```json
{
  "section": {
    "id": 123,
    "title": "Updated title",
    ...
  }
}
```

---

### 5. Delete Section

Menghapus section secara permanen.

**Endpoint:** `DELETE /sections/{section}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `200 OK`

```json
{
  "success": true
}
```

**Error Responses:**
- `403 Forbidden` - User tidak memiliki akses ke section

---

### 6. Reorder Sections

Mengubah urutan sections dalam scene via drag-and-drop.

**Endpoint:** `POST /scenes/{scene}/sections/reorder`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| scene | integer | Scene ID |

**Request Body:**

```json
{
  "sections": [
    { "id": 123, "sort_order": 0 },
    { "id": 456, "sort_order": 1 },
    { "id": 789, "sort_order": 2 }
  ]
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| sections | array | Yes | Required |
| sections[].id | integer | Yes | Exists in scene |
| sections[].sort_order | integer | Yes | >= 0 |

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### 7. Toggle Collapse

Toggle `is_collapsed` state untuk menyembunyikan/menampilkan content.

**Endpoint:** `POST /sections/{section}/toggle-collapse`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `200 OK`

```json
{
  "section": {
    "id": 123,
    "is_collapsed": true,
    ...
  }
}
```

---

### 8. Toggle AI Visibility

Toggle `exclude_from_ai` untuk show/hide section dari AI context.

**Endpoint:** `POST /sections/{section}/toggle-ai-visibility`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `200 OK`

```json
{
  "section": {
    "id": 123,
    "exclude_from_ai": true,
    ...
  }
}
```

---

### 9. Dissolve Section

Menghapus section container tapi keep contentnya (unwrap).

**Endpoint:** `POST /sections/{section}/dissolve`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `200 OK`

```json
{
  "success": true,
  "dissolved_content": {
    "type": "doc",
    "content": [...]
  }
}
```

**Note:** Frontend harus handle insertion dari `dissolved_content` ke scene content.

---

### 10. Duplicate Section

Membuat copy dari section dengan title suffix "(Copy)".

**Endpoint:** `POST /sections/{section}/duplicate`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| section | integer | Section ID |

**Response:** `201 Created`

```json
{
  "section": {
    "id": 456,
    "title": "Original Title (Copy)",
    "sort_order": 3,
    ...
  }
}
```

---

## Section Types

| Type | Description | Default AI Visibility | Export Behavior |
|------|-------------|----------------------|-----------------|
| **content** | Main prose, final manuscript | Included | Exported |
| **note** | Personal reminders, TODO items | Excluded | Not exported |
| **alternative** | Alternate versions, kitbashing | Excluded | Not exported |
| **beat** | Scene planning, expandable outlines | Included | Not exported |

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | Token tidak valid atau expired |
| FORBIDDEN | 403 | Tidak punya akses ke resource |
| RESOURCE_NOT_FOUND | 404 | Section/Scene tidak ditemukan |
| INTERNAL_ERROR | 500 | Server error |

---

## Related Documentation

- **Testing Guide:** [Sections Testing](../06-testing/sections-testing.md)
- **User Journeys:** [Sections User Journeys](../07-user-journeys/sections/)
- **Sprint Documentation:** [Sprint 30: Sections System](../10-sprints/sprint-30-sections-system.md)

---

*Last Updated: 2026-01-04*
