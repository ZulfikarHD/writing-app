# 📖 Novels API Reference

## Overview

Novels API untuk NovelWrite, yaitu: endpoints untuk membuat, membaca, dan menghapus novel.

---

## Endpoints

### Create Novel Form

Menampilkan form untuk membuat novel baru.

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/novels/create` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Response

**Success (200)**
- Renders `pages/Novels/Create.vue` with Inertia
- Returns `pen_names` array for dropdown selection

---

### Store Novel

Menyimpan novel baru ke database.

| Property | Value |
|----------|-------|
| **Method** | `POST` |
| **URL** | `/novels` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `title` | string | ✅ | min:1, max:255 |
| `description` | string | ❌ | max:1000 |
| `genre` | string | ❌ | max:100 |
| `target_word_count` | integer | ❌ | min:0 |
| `pen_name_id` | integer | ❌ | exists:pen_names,id |

#### Example Request

```json
{
    "title": "The Mystery of Bromo",
    "description": "A thrilling adventure in East Java",
    "genre": "Mystery",
    "target_word_count": 50000,
    "pen_name_id": null
}
```

#### Response

**Success (302 Redirect)**
- Redirects to `/dashboard`
- Flash message: "Novel created successfully!"

**Error (422 Validation Error)**

```json
{
    "message": "The title field is required.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

---

### Delete Novel

Menghapus novel dari database.

| Property | Value |
|----------|-------|
| **Method** | `DELETE` |
| **URL** | `/novels/{novel}` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `novel` | integer | ID of the novel to delete |

#### Response

**Success (302 Redirect)**
- Redirects to `/dashboard`
- Flash message: "Novel deleted successfully!"

**Error (403 Forbidden)**
- When user tries to delete another user's novel

**Error (404 Not Found)**
- When novel doesn't exist

---

## Novel Model

### Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Owner of the novel |
| `pen_name_id` | bigint | Optional pen name |
| `title` | string | Novel title |
| `description` | text | Optional description |
| `genre` | string | Optional genre |
| `word_count` | integer | Current word count (default: 0) |
| `chapter_count` | integer | Current chapter count (default: 0) |
| `target_word_count` | integer | Target word count |
| `status` | enum | draft, in_progress, completed, archived |
| `last_edited_at` | timestamp | Last edit timestamp |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

### Status Values

| Status | Description |
|--------|-------------|
| `draft` | Novel baru, belum mulai ditulis |
| `in_progress` | Novel sedang aktif ditulis |
| `completed` | Novel sudah selesai |
| `archived` | Novel diarsipkan |

---

## Frontend Components

| Component | Path | Description |
|-----------|------|-------------|
| Create Page | `pages/Novels/Create.vue` | Multi-step creation wizard |
| NovelCard | `components/dashboard/NovelCard.vue` | Card display for novel |
| EmptyState | `components/dashboard/EmptyState.vue` | Empty state when no novels |

---

## Related Documentation

- **Testing Guide:** [Foundation Testing](../06-testing/foundation-testing.md)
- **Sprint Documentation:** [Sprint 01 - Foundation](../10-sprints/sprint-01-foundation.md)

---

**Last Updated:** 2025-12-31
