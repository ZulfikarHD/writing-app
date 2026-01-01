# 📡 Series API Reference

## Overview

API untuk mengelola book series, yaitu: fitur yang memungkinkan penulis mengelompokkan novel-novel yang berhubungan dalam satu seri dengan shared codex entries.

---

## Authentication

Semua endpoint memerlukan autentikasi. User harus login dan memiliki akses ke series yang bersangkutan.

---

## Endpoints

### Series Management

#### List All Series

**`GET /series`**

Response: Inertia page render dengan list series user.

**`GET /api/series`** (API endpoint)

Response: `200 OK`
```json
{
  "series": [
    {
      "id": 1,
      "title": "The Chronicles",
      "novels_count": 3
    }
  ]
}
```

---

#### Create Series Form

**`GET /series/create`**

Response: Inertia page render dengan availableNovels (novels tidak dalam series).

---

#### Store Series

**`POST /api/series`**

Request Body:
```json
{
  "title": "The Chronicles",
  "description": "An epic fantasy series...",
  "genre": "Fantasy",
  "novel_ids": [1, 2, 3]
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | Yes | max:255 |
| description | string | No | max:10000 |
| genre | string | No | max:100 |
| novel_ids | array | No | array of existing novel IDs |

Response: `201 Created`
```json
{
  "series": {
    "id": 1,
    "title": "The Chronicles"
  },
  "redirect": "/series/1"
}
```

---

#### Show Series

**`GET /series/{series}`**

Response: Inertia page render dengan:
- series (id, title, description, cover_path, genre, timestamps)
- novels (list of novels in series dengan order)
- codex_entries (series-level codex entries)
- availableNovels (novels yang bisa ditambahkan)

---

#### Edit Series Form

**`GET /series/{series}/edit`**

Response: Inertia page render dengan series data.

---

#### Update Series

**`PATCH /api/series/{series}`**

Request Body:
```json
{
  "title": "The Chronicles of Narnia",
  "description": "Updated description...",
  "genre": "Fantasy Adventure"
}
```

Response: `200 OK`
```json
{
  "series": {
    "id": 1,
    "title": "The Chronicles of Narnia",
    "description": "Updated description..."
  }
}
```

---

#### Delete Series

**`DELETE /api/series/{series}`**

Note: Novels tidak dihapus, hanya dikeluarkan dari series.

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Novel-Series Association

#### Add Novel to Series

**`POST /api/series/{series}/novels`**

Request Body:
```json
{
  "novel_id": 4
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| novel_id | int | Yes | exists:novels,id |

Response: `200 OK`
```json
{
  "success": true,
  "novel": {
    "id": 4,
    "title": "Book Four",
    "series_order": 4
  }
}
```

---

#### Remove Novel from Series

**`DELETE /api/series/{series}/novels/{novel}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Reorder Novels in Series

**`POST /api/series/{series}/novels/reorder`**

Request Body:
```json
{
  "novels": [
    { "id": 2, "order": 1 },
    { "id": 1, "order": 2 },
    { "id": 3, "order": 3 }
  ]
}
```

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Series Codex

#### List Series Codex Entries

**`GET /series/{series}/codex`**

Response: Inertia page render dengan:
- series info
- entries (series-level codex entries)
- types

---

#### Show Series Codex Entry

**`GET /series-codex/{entry}`**

Response: Inertia page render dengan entry details termasuk:
- aliases
- details

---

#### Create Series Codex Entry

**`POST /api/series/{series}/codex`**

Request Body:
```json
{
  "name": "The Dark Lord",
  "type": "character",
  "description": "Main antagonist appearing across all books...",
  "ai_context_mode": "always"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | max:255 |
| type | string | Yes | in:character,location,item,lore,organization,subplot |
| description | string | No | max:65535 |
| ai_context_mode | string | No | in:always,detected,manual,never |

Response: `201 Created`
```json
{
  "entry": {
    "id": 1,
    "type": "character",
    "name": "The Dark Lord",
    "description": "Main antagonist..."
  }
}
```

---

#### Update Series Codex Entry

**`PATCH /api/series-codex/{entry}`**

Response: `200 OK`

---

#### Delete Series Codex Entry

**`DELETE /api/series-codex/{entry}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Series Codex Aliases

#### Add Alias to Series Entry

**`POST /api/series-codex/{entry}/aliases`**

Request Body:
```json
{
  "alias": "Lord Voldemort"
}
```

Response: `201 Created`

---

#### Delete Series Entry Alias

**`DELETE /api/series-codex/aliases/{alias}`**

Response: `200 OK`

---

### Series Codex Details

#### Add Detail to Series Entry

**`POST /api/series-codex/{entry}/details`**

Request Body:
```json
{
  "key_name": "First Appearance",
  "value": "Book 1, Chapter 3"
}
```

Response: `201 Created`

---

#### Update Series Entry Detail

**`PATCH /api/series-codex/details/{detail}`**

Response: `200 OK`

---

#### Delete Series Entry Detail

**`DELETE /api/series-codex/details/{detail}`**

Response: `200 OK`

---

## Data Models

### Series

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner user |
| title | string | Series title |
| description | text | Series description |
| cover_path | string | Path to cover image |
| genre | string | Genre classification |
| sort_order | int | Display order |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |

### Novel (Updated)

| Field | Type | Description |
|-------|------|-------------|
| series_id | bigint | Foreign key to series (nullable) |
| series_order | int | Order dalam series (nullable) |

### SeriesCodexEntry

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| series_id | bigint | Foreign key to series |
| type | enum | Entry type (same as CodexEntry) |
| name | string | Entry name |
| description | text | Entry description |
| thumbnail_path | string | Path to thumbnail |
| ai_context_mode | string | AI context mode |
| sort_order | int | Display order |
| is_archived | bool | Archive flag |

---

## Series-Novel Codex Inheritance

Ketika novel adalah bagian dari series:

1. **Novel Codex Index** akan menampilkan:
   - Novel-specific entries
   - Series entries (dengan indicator `is_series_entry: true`)

2. **Filtering** tetap berfungsi untuk kedua jenis entries

3. **Series entries** bisa di-override per novel dengan `NovelSeriesCodexOverride` (planned feature)

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | User tidak terautentikasi |
| FORBIDDEN | 403 | User tidak memiliki akses ke series |
| NOT_FOUND | 404 | Series atau novel tidak ditemukan |
| CONFLICT | 400 | Novel sudah dalam series lain |

---

## Related Documentation

- **Sprint Documentation:** [Sprint 04 - Codex System](../10-sprints/sprint-04-codex-system.md)
- **Codex API:** [Codex API Reference](./codex.md)
- **Testing Guide:** [Codex Testing](../06-testing/codex-testing.md)

---

*Last Updated: 2026-01-01*
