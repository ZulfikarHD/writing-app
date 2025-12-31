# 📡 API Reference: Manuscript Editor

## Overview

API untuk mengelola manuscript editor termasuk chapters, scenes, dan revision history.

Base URL: `http://localhost:8000/api`

## Authentication

Semua endpoint memerlukan autentikasi via session cookie. User harus sudah login.

---

## Chapters

### List Chapters

Mengambil daftar chapters dengan scenes untuk sebuah novel.

**Endpoint:** `GET /api/novels/{novel}/chapters`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Response:** `200 OK`

```json
{
  "chapters": [
    {
      "id": 1,
      "title": "Chapter 1",
      "position": 0,
      "word_count": 1500,
      "scenes": [
        {
          "id": 1,
          "title": "Opening Scene",
          "position": 0,
          "status": "draft",
          "word_count": 500
        }
      ]
    }
  ]
}
```

---

### Create Chapter

Membuat chapter baru dengan default scene.

**Endpoint:** `POST /api/novels/{novel}/chapters`

**Request Body:**

```json
{
  "title": "Chapter 2",
  "position": 1
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | Yes | Min 1, Max 255 chars |
| position | integer | No | Auto-assigned if not provided |

**Response:** `201 Created`

```json
{
  "chapter": {
    "id": 2,
    "title": "Chapter 2",
    "position": 1,
    "scenes": [
      {
        "id": 3,
        "title": "Scene 1",
        "position": 0,
        "status": "draft",
        "word_count": 0
      }
    ]
  }
}
```

---

### Update Chapter

Update chapter title atau position.

**Endpoint:** `PATCH /api/chapters/{chapter}`

**Request Body:**

```json
{
  "title": "Chapter 2: The Journey"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| position | integer | No | Min 0 |

**Response:** `200 OK`

```json
{
  "chapter": {
    "id": 2,
    "title": "Chapter 2: The Journey",
    "position": 1
  }
}
```

---

### Delete Chapter

Menghapus chapter beserta semua scenes di dalamnya.

**Endpoint:** `DELETE /api/chapters/{chapter}`

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Reorder Chapters

Mengatur ulang urutan chapters.

**Endpoint:** `POST /api/novels/{novel}/chapters/reorder`

**Request Body:**

```json
{
  "chapters": [
    { "id": 1, "position": 0 },
    { "id": 3, "position": 1 },
    { "id": 2, "position": 2 }
  ]
}
```

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

## Scenes

### Create Scene

Membuat scene baru dalam chapter.

**Endpoint:** `POST /api/chapters/{chapter}/scenes`

**Request Body:**

```json
{
  "title": "New Scene",
  "position": 2
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| position | integer | No | Auto-assigned if not provided |

**Response:** `201 Created`

```json
{
  "scene": {
    "id": 5,
    "chapter_id": 1,
    "title": "New Scene",
    "position": 2,
    "status": "draft",
    "word_count": 0,
    "content": {
      "type": "doc",
      "content": [{"type": "paragraph"}]
    }
  }
}
```

---

### Get Scene

Mengambil detail scene termasuk content.

**Endpoint:** `GET /api/scenes/{scene}`

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 1,
    "chapter_id": 1,
    "title": "Opening Scene",
    "content": {
      "type": "doc",
      "content": [
        {
          "type": "paragraph",
          "content": [{"type": "text", "text": "It was a dark and stormy night..."}]
        }
      ]
    },
    "summary": "The protagonist arrives in a mysterious town.",
    "position": 0,
    "status": "in_progress",
    "word_count": 1500,
    "subtitle": null,
    "notes": "Remember to add foreshadowing here.",
    "pov_character_id": null,
    "exclude_from_ai": false
  }
}
```

---

### Update Scene Content (Auto-Save)

Auto-save endpoint untuk content. Endpoint ini dibuat khusus untuk typing = saving.

**Endpoint:** `PATCH /api/scenes/{scene}/content`

**Request Body:**

```json
{
  "content": {
    "type": "doc",
    "content": [
      {
        "type": "paragraph",
        "content": [{"type": "text", "text": "Updated content..."}]
      }
    ]
  }
}
```

**Response:** `200 OK`

```json
{
  "success": true,
  "word_count": 2,
  "saved_at": "2025-12-31T10:30:00+07:00"
}
```

---

### Update Scene Metadata

Update scene metadata (title, status, notes, etc).

**Endpoint:** `PATCH /api/scenes/{scene}`

**Request Body:**

```json
{
  "title": "Revised Opening",
  "status": "completed",
  "notes": "Final version"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| summary | string | No | - |
| position | integer | No | Min 0 |
| status | string | No | Enum: draft, in_progress, completed, needs_revision |
| subtitle | string | No | Max 255 chars |
| notes | string | No | - |
| pov_character_id | integer | No | - |
| exclude_from_ai | boolean | No | - |

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 1,
    "title": "Revised Opening",
    "position": 0,
    "status": "completed"
  }
}
```

---

### Delete Scene

Menghapus scene permanen.

**Endpoint:** `DELETE /api/scenes/{scene}`

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Archive Scene

Soft-delete scene dengan archive.

**Endpoint:** `POST /api/scenes/{scene}/archive`

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Restore Scene

Restore scene dari archive.

**Endpoint:** `POST /api/scenes/{scene}/restore`

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Reorder Scenes

Mengatur ulang urutan scenes dalam chapter.

**Endpoint:** `POST /api/chapters/{chapter}/scenes/reorder`

**Request Body:**

```json
{
  "scenes": [
    { "id": 1, "position": 0 },
    { "id": 3, "position": 1 },
    { "id": 2, "position": 2 }
  ]
}
```

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

## Revisions

### List Revisions

Mengambil revision history untuk scene (max 50 entries).

**Endpoint:** `GET /api/scenes/{scene}/revisions`

**Response:** `200 OK`

```json
{
  "revisions": [
    {
      "id": 10,
      "word_count": 1500,
      "created_at": "2025-12-31T10:30:00+07:00"
    },
    {
      "id": 9,
      "word_count": 1450,
      "created_at": "2025-12-31T10:15:00+07:00"
    }
  ]
}
```

---

### Create Manual Revision

Membuat revision snapshot manual.

**Endpoint:** `POST /api/scenes/{scene}/revisions`

**Response:** `201 Created`

```json
{
  "revision": {
    "id": 11,
    "word_count": 1520,
    "created_at": "2025-12-31T10:45:00+07:00"
  }
}
```

---

### Restore from Revision

Restore content scene dari revision tertentu. Akan membuat backup sebelum restore.

**Endpoint:** `POST /api/scenes/{scene}/revisions/{revisionId}/restore`

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 1,
    "content": {
      "type": "doc",
      "content": [...]
    },
    "word_count": 1450
  }
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| 403 | Forbidden | User tidak memiliki akses ke novel/chapter/scene |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Unprocessable Entity | Validation error |

---

## Related Documentation

- **Sprint Documentation:** [Sprint 02 - Manuscript Editor](../10-sprints/sprint-02-manuscript-editor.md)
- **Testing Guide:** [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md)

---

**Last Updated:** 2025-12-31
