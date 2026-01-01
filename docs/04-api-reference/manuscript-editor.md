# 📝 API Documentation: Manuscript Editor

## Overview

API untuk mengelola manuscript editor, yaitu: rich text editing, scene/chapter management, content auto-save, dan drag-drop reordering untuk struktur novel yang optimal.

**Base URL:** `https://app.example.com`

**Authentication:** Semua endpoint memerlukan Laravel session authentication (cookies).

---

## Editor Routes

### Open Editor

Membuka editor untuk novel dengan auto-create chapter/scene jika belum ada.

**Endpoint:** `GET /novels/{novel}/write`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |

**Response:** Inertia page `Editor/Index`

**Response Data:**

```json
{
  "novel": {
    "id": 1,
    "title": "My Novel",
    "pov": "third",
    "tense": "past"
  },
  "chapters": [
    {
      "id": 1,
      "title": "Chapter 1",
      "position": 0,
      "scenes": [
        {
          "id": 1,
          "title": "Scene 1",
          "position": 0,
          "status": "draft",
          "word_count": 0
        }
      ]
    }
  ],
  "activeScene": {
    "id": 1,
    "chapter_id": 1,
    "title": "Scene 1",
    "content": {
      "type": "doc",
      "content": [{"type": "paragraph"}]
    },
    "summary": null,
    "status": "draft",
    "word_count": 0,
    "subtitle": null,
    "notes": null,
    "pov_character_id": null
  }
}
```

---

### Open Specific Scene

Membuka editor dengan scene tertentu yang sudah active.

**Endpoint:** `GET /novels/{novel}/write/{scene}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| novel | integer | Novel ID |
| scene | integer | Scene ID |

**Response:** Inertia page `Editor/Index` dengan `activeScene` sesuai ID

---

## Chapter API

### List Chapters

Mengambil daftar chapters dengan scenes untuk novel tertentu.

**Endpoint:** `GET /api/novels/{novel}/chapters`

**Authorization:** User must own the novel

**Response:** `200 OK`

```json
{
  "chapters": [
    {
      "id": 1,
      "title": "Chapter 1",
      "position": 0,
      "word_count": 1250,
      "scenes": [
        {
          "id": 1,
          "title": "Opening Scene",
          "position": 0,
          "status": "completed",
          "word_count": 650
        },
        {
          "id": 2,
          "title": "Conflict Intro",
          "position": 1,
          "status": "in_progress",
          "word_count": 600
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

**Authorization:** User must own the novel

**Request Body:**

```json
{
  "title": "Chapter 2",
  "position": 1
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | Yes | Max 255 chars |
| position | integer | No | Auto-incremented if not provided |

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

Update chapter metadata (title, position).

**Endpoint:** `PATCH /api/chapters/{chapter}`

**Authorization:** User must own the chapter's novel

**Request Body:**

```json
{
  "title": "Updated Chapter Title",
  "position": 2
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
    "title": "Updated Chapter Title",
    "position": 2
  }
}
```

---

### Delete Chapter

Menghapus chapter dan semua scenes di dalamnya.

**Endpoint:** `DELETE /api/chapters/{chapter}`

**Authorization:** User must own the chapter's novel

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Reorder Chapters

Update positions untuk multiple chapters sekaligus (drag-drop result).

**Endpoint:** `POST /api/novels/{novel}/chapters/reorder`

**Authorization:** User must own the novel

**Request Body:**

```json
{
  "chapters": [
    {"id": 3, "position": 0},
    {"id": 1, "position": 1},
    {"id": 2, "position": 2}
  ]
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| chapters | array | Yes | Array of objects |
| chapters[].id | integer | Yes | Must exist and belong to novel |
| chapters[].position | integer | Yes | Min 0 |

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

## Scene API

### Create Scene

Membuat scene baru di chapter tertentu.

**Endpoint:** `POST /api/chapters/{chapter}/scenes`

**Authorization:** User must own the chapter's novel

**Request Body:**

```json
{
  "title": "New Scene",
  "position": 2
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars, defaults to "New Scene" |
| position | integer | No | Auto-incremented if not provided |

**Response:** `201 Created`

```json
{
  "scene": {
    "id": 5,
    "chapter_id": 2,
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

Mengambil detail scene lengkap.

**Endpoint:** `GET /api/scenes/{scene}`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 5,
    "chapter_id": 2,
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
    "summary": "Opening scene introducing the protagonist.",
    "position": 0,
    "status": "completed",
    "word_count": 850,
    "subtitle": "The Beginning",
    "notes": "Need to add more description of the storm.",
    "pov_character_id": 3,
    "exclude_from_ai": false
  }
}
```

---

### Update Scene Content (Auto-save)

Update konten scene dengan auto-save. Endpoint ini dipanggil secara otomatis saat user mengetik (debounced 500ms).

**Endpoint:** `PATCH /api/scenes/{scene}/content`

**Authorization:** User must own the scene's novel

**Request Body:**

```json
{
  "content": {
    "type": "doc",
    "content": [
      {
        "type": "paragraph",
        "content": [{"type": "text", "text": "Updated content here..."}]
      }
    ]
  }
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| content | object | Yes | Valid TipTap JSON with type: "doc" |

**Response:** `200 OK`

```json
{
  "success": true,
  "word_count": 873,
  "saved_at": "2026-01-01T12:34:56.000Z"
}
```

**Side Effects:**
- Updates scene `word_count` based on content
- Updates novel's `last_edited_at` timestamp

---

### Update Scene Metadata

Update scene metadata (title, status, notes, dll) tanpa mengubah content.

**Endpoint:** `PATCH /api/scenes/{scene}`

**Authorization:** User must own the scene's novel

**Request Body:**

```json
{
  "title": "Updated Scene Title",
  "summary": "New summary",
  "status": "in_progress",
  "subtitle": "Part Two",
  "notes": "Remember to add dialogue",
  "pov_character_id": 5,
  "exclude_from_ai": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | No | Max 255 chars |
| summary | string | No | Text |
| position | integer | No | Min 0 |
| status | string | No | Enum: draft, in_progress, completed, needs_revision |
| subtitle | string | No | Max 255 chars |
| notes | string | No | Text |
| pov_character_id | integer | No | Nullable |
| exclude_from_ai | boolean | No | - |

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 5,
    "title": "Updated Scene Title",
    "position": 0,
    "status": "in_progress"
  }
}
```

---

### Delete Scene

Menghapus scene permanen.

**Endpoint:** `DELETE /api/scenes/{scene}`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Archive Scene

Soft delete scene (archived_at diisi).

**Endpoint:** `POST /api/scenes/{scene}/archive`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Restore Archived Scene

Restore scene yang sudah di-archive.

**Endpoint:** `POST /api/scenes/{scene}/restore`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

### Reorder Scenes

Update positions untuk multiple scenes dalam chapter (drag-drop result).

**Endpoint:** `POST /api/chapters/{chapter}/scenes/reorder`

**Authorization:** User must own the chapter's novel

**Request Body:**

```json
{
  "scenes": [
    {"id": 5, "position": 0},
    {"id": 3, "position": 1},
    {"id": 4, "position": 2}
  ]
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| scenes | array | Yes | Array of objects |
| scenes[].id | integer | Yes | Must exist and belong to chapter |
| scenes[].position | integer | Yes | Min 0 |

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

## Scene Revisions API

### List Revisions

Mengambil history revisions untuk scene (max 50 terbaru).

**Endpoint:** `GET /api/scenes/{scene}/revisions`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "revisions": [
    {
      "id": 15,
      "word_count": 850,
      "created_at": "2026-01-01T12:30:00.000Z"
    },
    {
      "id": 14,
      "word_count": 820,
      "created_at": "2026-01-01T11:45:00.000Z"
    }
  ]
}
```

---

### Create Manual Revision

Membuat snapshot manual dari current scene content.

**Endpoint:** `POST /api/scenes/{scene}/revisions`

**Authorization:** User must own the scene's novel

**Response:** `201 Created`

```json
{
  "revision": {
    "id": 16,
    "word_count": 850,
    "created_at": "2026-01-01T13:00:00.000Z"
  }
}
```

---

### Restore Revision

Restore scene content dari revision tertentu. Creates backup of current content sebelum restore.

**Endpoint:** `POST /api/scenes/{scene}/revisions/{revisionId}/restore`

**Authorization:** User must own the scene's novel

**Response:** `200 OK`

```json
{
  "scene": {
    "id": 5,
    "content": {
      "type": "doc",
      "content": [...]
    },
    "word_count": 820
  }
}
```

**Side Effects:**
- Creates automatic revision from current content before restoring
- Updates scene content and word_count

---

## Data Structures

### TipTap Content Format

Scene content disimpan dalam format JSON TipTap:

```typescript
interface TipTapDocument {
  type: 'doc';
  content: TipTapNode[];
}

interface TipTapNode {
  type: string; // 'paragraph', 'heading', 'bulletList', etc.
  attrs?: {
    level?: number; // for headings: 1, 2, 3
    textAlign?: 'left' | 'center' | 'right' | 'justify';
  };
  content?: TipTapNode[]; // nested nodes
  text?: string; // for text nodes
  marks?: Array<{
    type: 'bold' | 'italic' | 'underline' | 'strike';
  }>;
}
```

**Example - Simple Paragraph:**

```json
{
  "type": "doc",
  "content": [
    {
      "type": "paragraph",
      "content": [
        {
          "type": "text",
          "text": "This is a simple paragraph."
        }
      ]
    }
  ]
}
```

**Example - Formatted Text:**

```json
{
  "type": "doc",
  "content": [
    {
      "type": "paragraph",
      "content": [
        {
          "type": "text",
          "marks": [{"type": "bold"}],
          "text": "Bold text"
        },
        {
          "type": "text",
          "text": " and "
        },
        {
          "type": "text",
          "marks": [{"type": "italic"}],
          "text": "italic text"
        }
      ]
    }
  ]
}
```

**Example - Heading with Alignment:**

```json
{
  "type": "doc",
  "content": [
    {
      "type": "heading",
      "attrs": {
        "level": 1,
        "textAlign": "center"
      },
      "content": [
        {
          "type": "text",
          "text": "Chapter Title"
        }
      ]
    }
  ]
}
```

**Example - Bullet List:**

```json
{
  "type": "doc",
  "content": [
    {
      "type": "bulletList",
      "content": [
        {
          "type": "listItem",
          "content": [
            {
              "type": "paragraph",
              "content": [
                {
                  "type": "text",
                  "text": "First item"
                }
              ]
            }
          ]
        },
        {
          "type": "listItem",
          "content": [
            {
              "type": "paragraph",
              "content": [
                {
                  "type": "text",
                  "text": "Second item"
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| UNAUTHORIZED | 401 | User tidak authenticated |
| FORBIDDEN | 403 | User tidak memiliki akses ke resource |
| NOT_FOUND | 404 | Novel/Chapter/Scene tidak ditemukan |
| VALIDATION_ERROR | 422 | Request body tidak valid |
| INTERNAL_ERROR | 500 | Server error |

**Error Response Format:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "content": ["The content must be a valid TipTap document."]
  }
}
```

---

## Rate Limiting

Auto-save endpoint (`PATCH /api/scenes/{scene}/content`) memiliki rate limiting:

- **Limit:** 120 requests per minute per user
- **Reason:** Mencegah excessive API calls dari debounce yang terlalu aggressive

**Rate Limit Headers:**

```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1704110400
```

---

## Notes

### Auto-save Behavior

- Debounced 500ms dari keystroke terakhir
- Force save dengan `Ctrl+S` / `Cmd+S` (tidak di-debounce)
- Save status indicator: saved, saving, unsaved, error
- Retry logic: 3x dengan exponential backoff jika gagal

### Word Count Calculation

Word count dihitung di backend dengan `str_word_count()` setelah extract plain text dari TipTap JSON:

1. Extract semua text nodes dari content
2. Gabungkan dengan space
3. Count words menggunakan PHP `str_word_count()`
4. Return integer word count

### Content Validation

Content MUST be valid TipTap JSON:

- Root node type MUST be "doc"
- Content MUST be array of nodes
- Paragraph is minimum valid content: `{"type": "doc", "content": [{"type": "paragraph"}]}`

---

## Acts (Phase 2)

### List Acts

**GET** `/api/novels/{novel}/acts`

List semua acts untuk novel dengan ordering by position.

**Response:**
```json
{
  "acts": [
    {
      "id": 1,
      "title": "Act 1: Setup",
      "position": 0
    },
    {
      "id": 2,
      "title": "Act 2: Confrontation",
      "position": 1
    }
  ]
}
```

### Create Act

**POST** `/api/novels/{novel}/acts`

Create act baru dalam novel.

**Request:**
```json
{
  "title": "Act 1: Setup",
  "position": 0  // Optional, auto-increment if not provided
}
```

**Response:** `201 Created`
```json
{
  "act": {
    "id": 1,
    "title": "Act 1: Setup",
    "position": 0
  }
}
```

### Update Act

**PATCH** `/api/acts/{act}`

Update act title atau position.

**Request:**
```json
{
  "title": "Act 1: The Beginning",
  "position": 0
}
```

**Response:** `200 OK`

### Delete Act

**DELETE** `/api/acts/{act}`

Delete act. **Note:** Chapters yang belong to act ini akan memiliki `act_id` set to `null` (tidak ikut terhapus).

**Response:** `200 OK`
```json
{
  "success": true
}
```

### Reorder Acts

**POST** `/api/novels/{novel}/acts/reorder`

Batch update position untuk multiple acts.

**Request:**
```json
{
  "acts": [
    { "id": 2, "position": 0 },
    { "id": 1, "position": 1 },
    { "id": 3, "position": 2 }
  ]
}
```

**Response:** `200 OK`

---

## Scene Labels (Phase 2)

### List Labels

**GET** `/api/novels/{novel}/labels`

List semua scene labels untuk novel.

**Response:**
```json
{
  "labels": [
    {
      "id": 1,
      "name": "Action",
      "color": "#EF4444",
      "position": 0
    },
    {
      "id": 2,
      "name": "Romance",
      "color": "#EC4899",
      "position": 1
    }
  ]
}
```

### Create Label

**POST** `/api/novels/{novel}/labels`

Create scene label baru dengan custom color.

**Request:**
```json
{
  "name": "Important",
  "color": "#F59E0B",  // Optional, defaults to #6B7280
  "position": 0  // Optional, auto-increment if not provided
}
```

**Validation:**
- `name`: required, max 100 chars
- `color`: optional, must be valid hex (#RRGGBB format)

**Response:** `201 Created`
```json
{
  "label": {
    "id": 3,
    "name": "Important",
    "color": "#F59E0B",
    "position": 0
  }
}
```

### Update Label

**PATCH** `/api/labels/{label}`

Update label name, color, atau position.

**Request:**
```json
{
  "name": "Very Important",
  "color": "#FF0000"
}
```

**Response:** `200 OK`

### Delete Label

**DELETE** `/api/labels/{label}`

Delete label. Pivot relationships otomatis terhapus (CASCADE).

**Response:** `200 OK`
```json
{
  "success": true
}
```

### Assign Labels to Scene

**POST** `/api/scenes/{scene}/labels`

Assign labels ke scene. **Note:** Ini **replace** existing labels (sync, bukan append).

**Request:**
```json
{
  "label_ids": [1, 2, 3]  // Array of label IDs
}
```

**Business Rules:**
- Labels MUST belong to same novel as scene
- Labels dari novel lain akan di-filter out
- Empty array = remove all labels

**Response:** `200 OK`
```json
{
  "labels": [
    {
      "id": 1,
      "name": "Action",
      "color": "#EF4444"
    },
    {
      "id": 2,
      "name": "Romance",
      "color": "#EC4899"
    }
  ]
}
```

---

## Plan View (Phase 2)

### Show Plan Page

**GET** `/novels/{novel}/plan`

Load Plan page dengan complete data: chapters, scenes, acts, labels.

**Response:** Inertia page `Plan/Index` dengan props:
```javascript
{
  novel: {
    id: 1,
    title: "My Novel",
    word_count: 50000
  },
  chapters: [
    {
      id: 1,
      title: "Chapter 1",
      position: 0,
      act_id: 1,
      word_count: 5000,
      scenes: [
        {
          id: 1,
          chapter_id: 1,
          title: "Opening Scene",
          summary: "Hero meets mentor",
          position: 0,
          status: "completed",
          word_count: 1200,
          pov_character_id: null,
          subtitle: "The Meeting",
          labels: [
            { id: 1, name: "Action", color: "#EF4444" }
          ]
        }
      ]
    }
  ],
  acts: [
    { id: 1, title: "Act 1", position: 0 }
  ],
  labels: [
    { id: 1, name: "Action", color: "#EF4444", position: 0 }
  ]
}
```

**Business Rules:**
- Only loads **active** (non-archived) scenes
- Scenes eager loaded dengan labels
- Chapters ordered by position
- Acts ordered by position

### Search Scenes

**GET** `/api/novels/{novel}/scenes/search`

Search dan filter scenes by title, summary, status, atau labels.

**Query Parameters:**
- `q` (string, optional): Search term untuk title/summary
- `status` (string, optional): Filter by status (`draft`, `in_progress`, `completed`, `needs_revision`)
- `label_ids[]` (array, optional): Filter by label IDs

**Example:**
```
GET /api/novels/1/scenes/search?q=dragon&status=completed&label_ids[]=1&label_ids[]=3
```

**Response:** `200 OK`
```json
{
  "scenes": [
    {
      "id": 5,
      "chapter_id": 2,
      "chapter_title": "Chapter 2",
      "title": "The Dragon Fight",
      "summary": "Epic battle scene",
      "position": 0,
      "status": "completed",
      "word_count": 2500,
      "labels": [
        { "id": 1, "name": "Action", "color": "#EF4444" },
        { "id": 3, "name": "Important", "color": "#F59E0B" }
      ]
    }
  ]
}
```

**Business Rules:**
- Only searches **active** (non-archived) scenes
- Search is case-insensitive dengan LIKE `%term%`
- Labels filter uses `whereIn()` dengan labels relationship
- Results ordered by position

---

## Duplicate Scene

**POST** `/api/scenes/{scene}/duplicate`

Duplicate scene dengan semua metadata dan labels.

**Response:** `201 Created`
```json
{
  "scene": {
    "id": 10,
    "chapter_id": 2,
    "title": "Original Scene (Copy)",
    "position": 5,
    "status": "draft",
    "word_count": 1200
  }
}
```

**Business Rules:**
- Title ditambah suffix "(Copy)"
- Position = max position + 1 di chapter yang sama
- Labels ikut ter-copy (attach)
- `archived_at` di-reset ke `null`
- Content (TipTap JSON) di-copy

---

## Related Documentation

- **Sprint Documentation:** [Sprint 02 - Manuscript Editor (Extended)](../10-sprints/sprint-02-manuscript-editor.md)
- **Testing Guide:** [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md)

---

*Last Updated: 2026-01-01*
