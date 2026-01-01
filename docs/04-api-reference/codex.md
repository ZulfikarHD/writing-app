# 📡 Codex API Reference

## Overview

API untuk mengelola Codex entries dalam novel, yaitu: sistem world-building yang memungkinkan penulis mengelola karakter, lokasi, item, dan elemen cerita lainnya dengan dukungan AI context integration.

---

## Authentication

Semua endpoint memerlukan autentikasi. User harus login dan memiliki akses ke novel yang bersangkutan.

---

## Entry Types

| Type | Description |
|------|-------------|
| `character` | Karakter dalam cerita |
| `location` | Lokasi atau tempat |
| `item` | Objek atau benda |
| `lore` | Sejarah atau background lore |
| `organization` | Grup, fraksi, atau organisasi |
| `subplot` | Arc cerita atau subplot |

## AI Context Modes

| Mode | Description |
|------|-------------|
| `always` | Selalu include dalam AI context |
| `detected` | Include ketika nama/alias terdeteksi |
| `manual` | Include hanya jika user pilih manual |
| `never` | Tidak pernah include dalam AI context |

---

## Endpoints

### Codex Entries

#### List Entries

**`GET /novels/{novel}/codex`**

Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| type | string | Filter by entry type |
| category | int | Filter by category ID |
| search | string | Search by name/description/alias |

Response: Inertia page render dengan entries, seriesEntries, categories, types, filters.

---

#### Create Entry Form

**`GET /novels/{novel}/codex/create`**

Response: Inertia page render dengan novel, types, contextModes.

---

#### Store Entry

**`POST /api/novels/{novel}/codex`**

Request Body:
```json
{
  "name": "John Doe",
  "type": "character",
  "description": "Main protagonist of the story...",
  "ai_context_mode": "detected"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | max:255 |
| type | string | Yes | in:character,location,item,lore,organization,subplot |
| description | string | No | max:65535 (sent to AI) |
| research_notes | string | No | max:65535 (NOT sent to AI - Sprint 13) |
| ai_context_mode | string | No | in:always,detected,manual,never (default: detected) |
| is_tracking_enabled | boolean | No | default: true (Sprint 13) |

Response: `201 Created`
```json
{
  "entry": {
    "id": 1,
    "type": "character",
    "name": "John Doe",
    "description": "Main protagonist...",
    "ai_context_mode": "detected"
  },
  "redirect": "/codex/1"
}
```

---

#### Show Entry

**`GET /codex/{entry}`**

Response: Inertia page render dengan entry lengkap termasuk:
- aliases
- details
- outgoing_relations
- incoming_relations
- progressions
- categories
- mentions

---

#### Edit Entry Form

**`GET /codex/{entry}/edit`**

Response: Inertia page render dengan novel, entry, types, contextModes.

---

#### Update Entry

**`PATCH /api/codex/{entry}`**

Request Body:
```json
{
  "name": "John Doe Jr.",
  "description": "Updated description...",
  "ai_context_mode": "always"
}
```

Response: `200 OK`
```json
{
  "entry": {
    "id": 1,
    "type": "character",
    "name": "John Doe Jr.",
    "description": "Updated description...",
    "ai_context_mode": "always",
    "is_archived": false
  }
}
```

---

#### Archive Entry

**`POST /api/codex/{entry}/archive`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Restore Entry

**`POST /api/codex/{entry}/restore`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Delete Entry

**`DELETE /api/codex/{entry}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Codex Aliases

#### List Aliases

**`GET /api/codex/{entry}/aliases`**

Response: `200 OK`
```json
{
  "aliases": [
    { "id": 1, "alias": "Johnny" },
    { "id": 2, "alias": "JD" }
  ]
}
```

---

#### Add Alias

**`POST /api/codex/{entry}/aliases`**

Request Body:
```json
{
  "alias": "Johnny Boy"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| alias | string | Yes | max:255, unique per entry |

Response: `201 Created`
```json
{
  "alias": {
    "id": 3,
    "alias": "Johnny Boy"
  }
}
```

---

#### Update Alias

**`PATCH /api/codex/aliases/{alias}`**

Request Body:
```json
{
  "alias": "Johnny B"
}
```

Response: `200 OK`
```json
{
  "alias": {
    "id": 3,
    "alias": "Johnny B"
  }
}
```

---

#### Delete Alias

**`DELETE /api/codex/aliases/{alias}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Codex Details

#### List Details

**`GET /api/codex/{entry}/details`**

Response: `200 OK`
```json
{
  "details": [
    { "id": 1, "key_name": "Age", "value": "25", "sort_order": 1 },
    { "id": 2, "key_name": "Occupation", "value": "Detective", "sort_order": 2 }
  ]
}
```

---

#### Add Detail

**`POST /api/codex/{entry}/details`**

Request Body:
```json
{
  "key_name": "Height",
  "value": "180cm"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| key_name | string | Yes | max:255 |
| value | string | Yes | max:10000 |

Response: `201 Created`
```json
{
  "detail": {
    "id": 3,
    "key_name": "Height",
    "value": "180cm",
    "sort_order": 3
  }
}
```

---

#### Update Detail

**`PATCH /api/codex/details/{detail}`**

Request Body:
```json
{
  "key_name": "Height",
  "value": "185cm"
}
```

Response: `200 OK`
```json
{
  "detail": {
    "id": 3,
    "key_name": "Height",
    "value": "185cm",
    "sort_order": 3
  }
}
```

---

#### Reorder Details

**`POST /api/codex/{entry}/details/reorder`**

Request Body:
```json
{
  "order": [3, 1, 2]
}
```

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Delete Detail

**`DELETE /api/codex/details/{detail}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Codex Relations

#### List Relations

**`GET /api/codex/{entry}/relations`**

Response: `200 OK`
```json
{
  "outgoing": [
    {
      "id": 1,
      "relation_type": "knows",
      "label": "Best friend",
      "is_bidirectional": true,
      "target": { "id": 2, "name": "Jane Doe", "type": "character" }
    }
  ],
  "incoming": [
    {
      "id": 2,
      "relation_type": "works_for",
      "label": "Boss",
      "is_bidirectional": false,
      "source": { "id": 3, "name": "Acme Corp", "type": "organization" }
    }
  ]
}
```

---

#### Get Relation Types

**`GET /api/codex/relation-types`**

Response: `200 OK`
```json
{
  "types": [
    "knows", "allied_with", "enemy_of", "member_of", "located_in",
    "owns", "related_to", "works_for", "reports_to", "married_to",
    "parent_of", "child_of"
  ]
}
```

---

#### Create Relation

**`POST /api/codex/{entry}/relations`**

Request Body:
```json
{
  "target_entry_id": 2,
  "relation_type": "knows",
  "label": "Childhood friend",
  "is_bidirectional": true
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| target_entry_id | int | Yes | exists:codex_entries,id |
| relation_type | string | Yes | in:knows,allied_with,... |
| label | string | No | max:255 |
| is_bidirectional | bool | No | default: false |

Response: `201 Created`

---

#### Update Relation

**`PATCH /api/codex/relations/{relation}`**

Request Body:
```json
{
  "label": "Best friend since childhood",
  "is_bidirectional": true
}
```

Response: `200 OK`

---

#### Delete Relation

**`DELETE /api/codex/relations/{relation}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Codex Progressions

#### List Progressions

**`GET /api/codex/{entry}/progressions`**

Response: `200 OK`
```json
{
  "progressions": [
    {
      "id": 1,
      "note": "Character gets promoted",
      "new_value": "Senior Detective",
      "mode": "addition",
      "scene": { "id": 5, "title": "Chapter 3 - Promotion" },
      "detail": { "id": 2, "key_name": "Occupation" }
    }
  ]
}
```

---

#### Create Progression

**`POST /api/codex/{entry}/progressions`**

Request Body:
```json
{
  "scene_id": 5,
  "detail_id": 2,
  "note": "Gets promoted after solving the case",
  "new_value": "Senior Detective",
  "mode": "replacement"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| scene_id | int | No | exists:scenes,id |
| detail_id | int | No | exists:codex_details,id |
| note | string | No | max:1000 |
| new_value | string | No | max:10000 |
| mode | string | No | in:addition,replacement (default: addition) |

Response: `201 Created`

---

#### Update Progression

**`PATCH /api/codex/progressions/{progression}`**

Response: `200 OK`

---

#### Delete Progression

**`DELETE /api/codex/progressions/{progression}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Codex Categories

#### List Categories

**`GET /api/novels/{novel}/codex/categories`**

Response: `200 OK`
```json
{
  "categories": [
    { "id": 1, "name": "Main Characters", "color": "#3B82F6", "sort_order": 1 },
    { "id": 2, "name": "Side Characters", "color": "#10B981", "sort_order": 2 }
  ]
}
```

---

#### Create Category

**`POST /api/novels/{novel}/codex/categories`**

Request Body:
```json
{
  "name": "Villains",
  "color": "#EF4444"
}
```

Response: `201 Created`

---

#### Update Category

**`PATCH /api/codex/categories/{category}`**

Response: `200 OK`

---

#### Delete Category

**`DELETE /api/codex/categories/{category}`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Assign Category to Entry

**`POST /api/codex/{entry}/categories/{category}`**

Response: `200 OK`

---

#### Remove Category from Entry

**`DELETE /api/codex/{entry}/categories/{category}`**

Response: `200 OK`

---

### Bulk Operations

#### Export JSON

**`GET /api/novels/{novel}/codex/export/json`**

Response: JSON file download dengan struktur:
```json
{
  "version": "1.0",
  "exported_at": "2026-01-01T12:00:00Z",
  "novel_id": 1,
  "entries": [
    {
      "type": "character",
      "name": "John Doe",
      "description": "...",
      "aliases": ["Johnny", "JD"],
      "details": [{ "key": "Age", "value": "25" }],
      "categories": ["Main Characters"]
    }
  ],
  "categories": [
    { "name": "Main Characters", "color": "#3B82F6" }
  ]
}
```

---

#### Export CSV

**`GET /api/novels/{novel}/codex/export/csv`**

Response: CSV file download dengan columns:
- type, name, description, ai_context_mode, aliases (semicolon-separated), categories

---

#### Preview Import

**`POST /api/novels/{novel}/codex/import/preview`**

Request: Multipart form dengan file JSON.

Response: `200 OK`
```json
{
  "to_create": 5,
  "to_skip": 2,
  "duplicates": ["John Doe", "Jane Doe"],
  "categories_to_create": ["New Category"]
}
```

---

#### Import JSON

**`POST /api/novels/{novel}/codex/import`**

Request: Multipart form dengan file JSON.

Response: `200 OK`
```json
{
  "success": true,
  "imported": 5,
  "skipped": 2,
  "errors": []
}
```

---

### Mention Tracking

#### Scan Novel Mentions

**`POST /api/novels/{novel}/codex/scan-mentions`**

Response: `200 OK`
```json
{
  "success": true,
  "message": "Mention scan completed"
}
```

---

#### Rescan Entry Mentions

**`POST /api/codex/{entry}/rescan-mentions`**

Response: `200 OK`
```json
{
  "success": true,
  "mentions": [
    {
      "id": 1,
      "mention_count": 15,
      "scene": {
        "id": 3,
        "title": "Chapter 1",
        "chapter": { "id": 1, "title": "Beginning" }
      }
    }
  ]
}
```

---

### Thumbnail Management

#### Upload Thumbnail

**`POST /api/codex/{entry}/thumbnail`**

Request: Multipart form data dengan file image.

```
thumbnail: (binary file)
```

|| Field | Type | Required | Validation |
||-------|------|----------|------------|
|| thumbnail | file | Yes | image, mimes:jpeg,jpg,png,gif,webp, max:2048KB |

Response: `200 OK`
```json
{
  "success": true,
  "thumbnail_path": "codex/1/thumbnails/abc123.jpg",
  "thumbnail_url": "https://example.com/storage/codex/1/thumbnails/abc123.jpg"
}
```

---

#### Delete Thumbnail

**`DELETE /api/codex/{entry}/thumbnail`**

Response: `200 OK`
```json
{
  "success": true
}
```

---

### Editor Integration

#### Get Entry Details for Hover Tooltip

**`GET /api/codex/{entry}`**

Response: `200 OK`
```json
{
  "entry": {
    "id": 1,
    "type": "character",
    "name": "John Doe",
    "description": "Main protagonist...",
    "thumbnail_url": "https://example.com/storage/codex/1/thumbnails/abc123.jpg",
    "aliases": ["Johnny", "JD"],
    "details": [
      { "id": 1, "key_name": "Age", "value": "25" },
      { "id": 2, "key_name": "Occupation", "value": "Detective" }
    ],
    "outgoing_relations": [...],
    "incoming_relations": [...]
  }
}
```

---

#### Get Entries for Editor

**`GET /api/novels/{novel}/codex/editor`**

Response: `200 OK`
```json
{
  "entries": [
    {
      "id": 1,
      "type": "character",
      "name": "John Doe",
      "description": "Main protagonist...",
      "ai_context_mode": "detected",
      "aliases": ["Johnny", "JD"],
      "details": [{ "key": "Age", "value": "25" }]
    }
  ]
}
```

---

#### Quick Create Entry

**`POST /api/novels/{novel}/codex/quick-create`**

Request Body:
```json
{
  "name": "New Character",
  "type": "character",
  "description": "Quick description",
  "add_alias": "NC"
}
```

Response: `201 Created`
```json
{
  "entry": {
    "id": 10,
    "type": "character",
    "name": "New Character",
    "description": "Quick description",
    "ai_context_mode": "detected",
    "aliases": ["NC"]
  }
}
```

---

### Research Notes & External Links (Sprint 13)

#### Get External Links

**`GET /api/codex/{entry}/external-links`**

Mengambil semua external links untuk research purposes.

Response: `200 OK`
```json
{
  "links": [
    {
      "id": 1,
      "title": "Character Inspiration",
      "url": "https://example.com/reference",
      "notes": "Visual reference for character design",
      "sort_order": 0
    }
  ]
}
```

---

#### Add External Link

**`POST /api/codex/{entry}/external-links`**

Request Body:
```json
{
  "title": "Character Inspiration",
  "url": "https://example.com/reference",
  "notes": "Visual reference for character design"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| title | string | Yes | max:255 |
| url | string (URL) | Yes | valid URL, max:2048 |
| notes | string | No | max:65535 |

Response: `201 Created`
```json
{
  "link": {
    "id": 1,
    "title": "Character Inspiration",
    "url": "https://example.com/reference",
    "notes": "Visual reference for character design",
    "sort_order": 0
  }
}
```

---

#### Update External Link

**`PATCH /api/codex/external-links/{link}`**

Request Body: Same as Add External Link

Response: `200 OK` dengan updated link object

---

#### Delete External Link

**`DELETE /api/codex/external-links/{link}`**

Response: `204 No Content`

---

#### Reorder External Links

**`POST /api/codex/{entry}/external-links/reorder`**

Request Body:
```json
{
  "link_ids": [3, 1, 2]
}
```

Response: `200 OK`

---

### Tracking Toggle (Sprint 13)

Entry field `is_tracking_enabled` mengontrol apakah entry di-scan untuk mentions.

**Update via PATCH endpoint:**

```json
{
  "is_tracking_enabled": false
}
```

Ketika `false`, entry tidak akan:
- Di-scan untuk mentions baru saat scene di-save
- Muncul dalam editor highlighting
- Tapi masih bisa digunakan untuk AI context manual

---

### Auto-Scan Behavior (Sprint 13)

**Automatic Mention Scanning:**
- Berjalan **synchronously** saat scene content di-save
- **Tidak perlu queue worker** - scan instant
- Respects `is_tracking_enabled` field

**Polling untuk Real-Time Updates:**
- Codex Show page polls API setiap 5 detik
- Deteksi perubahan mentions otomatis
- UI update tanpa perlu refresh manual

**Manual Rescan:**

**`POST /api/codex/{entry}/rescan-mentions`**

Force rescan mentions untuk entry ini di semua scenes dalam novel.

Response: `200 OK`
```json
{
  "success": true,
  "message": "Mention scanning queued"
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Request body tidak valid |
| UNAUTHORIZED | 401 | User tidak terautentikasi |
| FORBIDDEN | 403 | User tidak memiliki akses ke resource |
| NOT_FOUND | 404 | Entry tidak ditemukan |
| CONFLICT | 409 | Duplikat entry (untuk import) |

---

## Related Documentation

- **Sprint Documentation:** [Sprint 04 - Codex System](../10-sprints/sprint-04-codex-system.md)
- **Testing Guide:** [Codex Testing](../06-testing/codex-testing.md)

---

## Version History

### v1.2.0 (2026-01-01) - Sprint 13
- ✨ Added Research Notes field (`research_notes`) - private notes NOT sent to AI
- ✨ Added External Links management for research purposes
- ✨ Added Tracking Toggle (`is_tracking_enabled`) - per-entry mention control
- 🚀 Auto-scan mentions synchronously (no queue worker needed)
- 📡 Live polling for real-time mention updates (5s interval)
- 🔧 Enhanced `apiShow` endpoint to include mentions data

### v1.1.0 (2026-01-01)
- Added thumbnail upload/delete endpoints
- Added API endpoint for hover tooltip integration (`GET /api/codex/{entry}`)
- Enhanced editor integration with mention hover preview
- Added D3.js relation graph visualization support

### v1.0.0 (2026-01-01)
- Initial Codex API implementation
- Full CRUD for entries, aliases, details, relations, progressions, categories
- Bulk import/export functionality
- Mention tracking system

---

*Last Updated: 2026-01-01*
