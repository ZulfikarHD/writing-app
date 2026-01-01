# 📡 Story Planning API Reference

## Overview

API untuk mengelola Story Planning features termasuk Plan views, Acts, Chapters, Scenes, Labels, dan structure creation.

Base URL: `/api`

---

## Authentication

Semua endpoint memerlukan authentication:
```
Authorization: Bearer <access_token>
```

Atau session-based authentication via Inertia.

---

## Plan Endpoints

### Get Matrix Data

Mengambil data untuk Matrix view dengan berbagai mode display.

**Endpoint:** `GET /novels/{novel}/plan/matrix`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| show | string | No | entries | Mode: `entries`, `pov`, `labels`, `custom` |
| entry_type | string | No | all | Filter: `all`, `character`, `location`, `item`, `lore`, `organization`, `subplot` |

**Response:** `200 OK`

```json
{
  "scenes": [
    {
      "id": 1,
      "title": "Opening Scene",
      "chapter_id": 1,
      "chapter_title": "Chapter 1",
      "act_id": 1,
      "act_title": "Act 1",
      "position": 0,
      "pov_character_id": null,
      "pov_type": null,
      "labels": []
    }
  ],
  "columns": [
    {
      "id": 1,
      "name": "John Doe",
      "type": "character",
      "color": "#3b82f6"
    }
  ],
  "matrix": {
    "1": {
      "1": true
    }
  }
}
```

---

### Get Plan Settings

Mengambil user preferences untuk Plan view.

**Endpoint:** `GET /novels/{novel}/plan/settings`

**Response:** `200 OK`

```json
{
  "current_view": "grid",
  "matrix_mode": "entries",
  "grid_axis": "vertical",
  "card_width": "medium",
  "card_height": "medium",
  "show_auto_references": true,
  "custom_matrix_entries": []
}
```

---

### Update Plan Settings

Update user preferences untuk Plan view.

**Endpoint:** `PATCH /novels/{novel}/plan/settings`

**Request Body:**

```json
{
  "current_view": "matrix",
  "matrix_mode": "pov",
  "grid_axis": "horizontal",
  "card_width": "large",
  "card_height": "full",
  "show_auto_references": false,
  "custom_matrix_entries": [1, 2, 3]
}
```

**Response:** `200 OK`

---

### Create from Outline

Membuat structure (acts/chapters/scenes) dari text outline.

**Endpoint:** `POST /novels/{novel}/plan/from-outline`

**Request Body:**

```json
{
  "outline": "Act 1: Setup\n  Chapter 1: Introduction\n    Scene 1: Opening\n    Scene 2: Hook\n  Chapter 2: Rising Action\n    Scene 3: Conflict",
  "template": null,
  "create_as": "scenes"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| outline | string | Yes | Text outline dengan indentation |
| template | string | No | Template ID (null untuk custom) |
| create_as | string | No | `scenes` atau `summaries` |

**Response:** `201 Created`

```json
{
  "message": "Structure created successfully",
  "acts_created": 1,
  "chapters_created": 2,
  "scenes_created": 3
}
```

---

### Parse Outline (Preview)

Preview parsed structure sebelum create.

**Endpoint:** `POST /plan/parse-outline`

**Request Body:**

```json
{
  "outline": "Act 1: Setup\n  Chapter 1: Introduction"
}
```

**Response:** `200 OK`

```json
{
  "structure": [
    {
      "type": "act",
      "title": "Setup",
      "children": [
        {
          "type": "chapter",
          "title": "Introduction",
          "children": []
        }
      ]
    }
  ]
}
```

---

### Bulk Set POV

Set POV untuk multiple scenes sekaligus.

**Endpoint:** `POST /novels/{novel}/plan/bulk-pov`

**Request Body:**

```json
{
  "scene_ids": [1, 2, 3],
  "pov_character_id": 5,
  "pov_type": "3rd_limited"
}
```

**Response:** `200 OK`

```json
{
  "message": "POV updated for 3 scenes",
  "updated_count": 3
}
```

---

## Act Endpoints

### List Acts

**Endpoint:** `GET /novels/{novel}/acts`

**Response:** `200 OK`

```json
{
  "acts": [
    {
      "id": 1,
      "title": "Act 1",
      "position": 0,
      "disable_numeration": false,
      "chapters_count": 5,
      "word_count": 15000
    }
  ]
}
```

---

### Create Act

**Endpoint:** `POST /novels/{novel}/acts`

**Request Body:**

```json
{
  "title": "Act 2",
  "position": 1
}
```

**Response:** `201 Created`

---

### Update Act

**Endpoint:** `PATCH /acts/{act}`

**Request Body:**

```json
{
  "title": "Act 2: Rising Action"
}
```

**Response:** `200 OK`

---

### Delete Act

**Endpoint:** `DELETE /acts/{act}`

**Response:** `200 OK` atau `422` jika act memiliki chapters

---

### Reorder Acts

**Endpoint:** `POST /novels/{novel}/acts/reorder`

**Request Body:**

```json
{
  "order": [3, 1, 2]
}
```

**Response:** `200 OK`

---

### Toggle Numeration

**Endpoint:** `PATCH /acts/{act}/numeration`

**Request Body:**

```json
{
  "disable_numeration": true
}
```

**Response:** `200 OK`

---

### Copy Act Prose

**Endpoint:** `POST /acts/{act}/copy-prose`

**Response:** `200 OK`

```json
{
  "prose": "Full prose content from all scenes in act..."
}
```

---

### Copy Act Outlines

**Endpoint:** `POST /acts/{act}/copy-outlines`

**Response:** `200 OK`

```json
{
  "outline": "Scene 1: Opening\nSummary: ...\n\nScene 2: Hook\nSummary: ..."
}
```

---

## Chapter Endpoints

### List Chapters

**Endpoint:** `GET /novels/{novel}/chapters`

**Response:** `200 OK`

```json
{
  "chapters": [
    {
      "id": 1,
      "title": "Chapter 1",
      "position": 0,
      "act_id": 1,
      "word_count": 5000,
      "scenes_count": 3
    }
  ]
}
```

---

### Create Chapter

**Endpoint:** `POST /novels/{novel}/chapters`

**Request Body:**

```json
{
  "title": "Chapter 2",
  "position": 1,
  "act_id": 1
}
```

**Response:** `201 Created`

---

### Update Chapter

**Endpoint:** `PATCH /chapters/{chapter}`

**Request Body:**

```json
{
  "title": "Chapter 2: The Discovery"
}
```

**Response:** `200 OK`

---

### Delete Chapter

**Endpoint:** `DELETE /chapters/{chapter}`

**Response:** `200 OK` atau `422` jika chapter memiliki scenes

---

### Reorder Chapters

**Endpoint:** `POST /novels/{novel}/chapters/reorder`

**Request Body:**

```json
{
  "order": [2, 1, 3]
}
```

**Response:** `200 OK`

---

## Scene Endpoints

### Set Scene POV

**Endpoint:** `PATCH /scenes/{scene}/pov`

**Request Body:**

```json
{
  "pov_character_id": 5,
  "pov_type": "3rd_limited"
}
```

| POV Type Options |
|------------------|
| `1st_person` |
| `2nd_person` |
| `3rd_limited` |
| `3rd_omniscient` |

**Response:** `200 OK`

---

### Sync Scene Labels

**Endpoint:** `POST /scenes/{scene}/labels/sync`

**Request Body:**

```json
{
  "label_ids": [1, 2, 3]
}
```

**Response:** `200 OK`

---

### Archive Scene

**Endpoint:** `POST /scenes/{scene}/archive`

**Response:** `200 OK`

```json
{
  "message": "Scene archived",
  "archived_at": "2026-01-02T10:00:00Z"
}
```

---

### Restore Scene

**Endpoint:** `POST /scenes/{scene}/restore`

**Response:** `200 OK`

---

### Duplicate Scene

**Endpoint:** `POST /scenes/{scene}/duplicate`

**Response:** `201 Created`

```json
{
  "id": 15,
  "title": "Opening Scene (Copy)",
  "message": "Scene duplicated"
}
```

---

### Search Scenes

**Endpoint:** `GET /novels/{novel}/scenes/search`

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| q | string | Search term |
| status | string | Filter by status |
| label_ids | array | Filter by label IDs |

**Response:** `200 OK`

```json
{
  "scenes": [
    {
      "id": 1,
      "chapter_id": 1,
      "chapter_title": "Chapter 1",
      "title": "Opening",
      "summary": "The story begins...",
      "position": 0,
      "status": "draft",
      "word_count": 1500,
      "labels": [
        {
          "id": 1,
          "name": "Draft",
          "color": "#fbbf24"
        }
      ]
    }
  ]
}
```

---

### Delete Empty Scenes

**Endpoint:** `DELETE /novels/{novel}/empty-scenes`

**Response:** `200 OK`

```json
{
  "message": "Deleted 5 empty scenes",
  "deleted_count": 5
}
```

---

## Scene Labels Endpoints

### List Labels

**Endpoint:** `GET /novels/{novel}/labels`

**Response:** `200 OK`

```json
{
  "labels": [
    {
      "id": 1,
      "name": "Draft",
      "color": "#fbbf24",
      "preset_type": "status",
      "position": 0
    }
  ]
}
```

---

### Create Label

**Endpoint:** `POST /novels/{novel}/labels`

**Request Body:**

```json
{
  "name": "Needs Revision",
  "color": "#ef4444",
  "position": 5
}
```

**Response:** `201 Created`

---

### Update Label

**Endpoint:** `PATCH /labels/{label}`

**Request Body:**

```json
{
  "name": "Needs Work",
  "color": "#f97316"
}
```

**Response:** `200 OK`

---

### Delete Label

**Endpoint:** `DELETE /labels/{label}`

**Response:** `200 OK`

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | Token tidak valid atau expired |
| FORBIDDEN | 403 | Tidak punya akses ke resource |
| NOT_FOUND | 404 | Resource tidak ditemukan |
| CONFLICT | 409 | Resource memiliki dependencies |
| INTERNAL_ERROR | 500 | Server error |

---

## Story Templates

Built-in templates available untuk Create from Outline:

| Template | Acts | Description |
|----------|------|-------------|
| Three Act Structure | 3 | Setup, Confrontation, Resolution |
| Save the Cat | 3 | 15-beat structure by Blake Snyder |
| Hero's Journey | 3 | 12-step monomyth by Joseph Campbell |
| Dan Harmon's Story Circle | 4 | 8-step circular narrative |
| Freytag's Pyramid | 5 | Exposition → Rising → Climax → Falling → Resolution |
| Seven Point Story Structure | 3 | Hook → Plot Points → Resolution |
| Fichtean Curve | 3 | Crisis-driven structure |
| Derek Murphy's 24 Chapters | 3 | Novel-specific 24-chapter framework |

---

*Last Updated: 2026-01-02*
