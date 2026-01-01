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
| tag | int | Filter by tag ID (Sprint 14) |
| search | string | Search by name/description/alias |

Response: Inertia page render dengan entries, seriesEntries, categories, tags (Sprint 14), types, filters.

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
  "value": "180cm",
  "type": "line",
  "ai_visibility": "always",
  "definition_id": null
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| key_name | string | Yes | max:255 |
| value | string | Yes | max:10000 |
| type | string | No | text, line, dropdown, codex_reference (default: text) |
| ai_visibility | string | No | always, never, nsfw_only (default: always) |
| definition_id | int | No | Reference ke CodexDetailDefinition |

Response: `201 Created`
```json
{
  "detail": {
    "id": 3,
    "key_name": "Height",
    "value": "180cm",
    "sort_order": 3,
    "type": "line",
    "ai_visibility": "always",
    "definition_id": null,
    "definition": null
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
    {
      "id": 1,
      "name": "Main Characters",
      "color": "#3B82F6",
      "sort_order": 1,
      "entry_count": 3,
      "total_entry_count": 5,
      "linked_tag_id": 2,
      "linked_tag": {
        "id": 2,
        "name": "Protagonist",
        "color": "#8B5CF6"
      },
      "linked_detail_definition_id": null,
      "linked_detail_definition": null,
      "linked_detail_value": null,
      "has_auto_linking": true,
      "auto_linked_count": 2
    }
  ]
}
```

**Sprint 16 Fields:**
- `linked_tag_id`: Tag yang di-link → entries dengan tag ini auto-appear
- `linked_tag`: Full tag object jika ada link
- `linked_detail_definition_id`: Detail definition yang di-link
- `linked_detail_definition`: Full definition object jika ada link
- `linked_detail_value`: Value yang harus match (untuk dropdown details)
- `has_auto_linking`: Boolean apakah category punya auto-linking enabled
- `auto_linked_count`: Jumlah entries yang auto-linked
- `total_entry_count`: entry_count (manual) + auto_linked_count

---

#### Create Category

**`POST /api/novels/{novel}/codex/categories`**

Request Body:
```json
{
  "name": "Villains",
  "color": "#EF4444",
  "linked_tag_id": 5,
  "linked_detail_definition_id": null,
  "linked_detail_value": null
}
```

| Field | Type | Required | Description (Sprint 16) |
|-------|------|----------|-------------------------|
| name | string | Yes | Category name |
| color | string | No | Hex color code |
| parent_id | int | No | Parent category ID |
| sort_order | int | No | Sort order (auto-assigned if omitted) |
| **linked_tag_id** | int | No | Tag ID - entries dengan tag ini auto-link (Sprint 16) |
| **linked_detail_definition_id** | int | No | Detail definition ID - untuk dropdown linking (Sprint 16) |
| **linked_detail_value** | string | No | Detail value to match (Sprint 16) |

**Sprint 16 Notes:**
- Jika `linked_tag_id` provided, semua entries dengan tag tersebut otomatis muncul di category
- Jika `linked_detail_definition_id` + `linked_detail_value` provided, entries dengan detail value matching otomatis muncul
- Detail definition harus type `dropdown` untuk linking to work
- Auto-linked entries dan manually assigned entries keduanya muncul di category

Response: `201 Created`
```json
{
  "category": {
    "id": 3,
    "name": "Villains",
    "color": "#EF4444",
    "linked_tag_id": 5,
    "has_auto_linking": true,
    "auto_linked_count": 2,
    "total_entry_count": 2
  }
}
```

---

#### Update Category

**`PATCH /api/codex/categories/{category}`**

Request Body:
```json
{
  "name": "Main Villains",
  "linked_tag_id": null,
  "linked_detail_definition_id": 3,
  "linked_detail_value": "Antagonist"
}
```

**Sprint 16 Notes:**
- Setting `linked_tag_id: null` removes tag linking
- Setting `linked_detail_definition_id: null` removes detail linking
- Dapat switch dari tag linking ke detail linking atau vice versa

Response: `200 OK`
```json
{
  "category": {
    "id": 3,
    "name": "Main Villains",
    "linked_tag_id": null,
    "linked_detail_definition_id": 3,
    "linked_detail_value": "Antagonist",
    "has_auto_linking": true
  }
}
```

---

#### Preview Auto-Linked Entries (Sprint 16)

**`GET /api/codex/categories/{category}/preview-entries`**

**Purpose:** Preview entries yang akan auto-link sebelum saving category.

Response: `200 OK`
```json
{
  "entries": [
    {
      "id": 10,
      "name": "Elena Blackwood",
      "type": "character"
    },
    {
      "id": 15,
      "name": "Marcus Stone",
      "type": "character"
    }
  ],
  "count": 2,
  "has_auto_linking": true
}
```

**Use Case:**
- Call this endpoint di CategoryManager untuk show preview entries
- User dapat see which entries akan auto-link sebelum save
- Transparency untuk user tentang auto-linking behavior

---

#### Delete Category

**`DELETE /api/codex/categories/{category}`**

Response: `200 OK`
```json
{
  "success": true
}
```

**Sprint 16 Note:** Deleting category tidak delete entries. Children categories moved ke parent level.

---

#### Assign Category to Entry

**`POST /api/codex/{entry}/categories`**

Request Body:
```json
{
  "category_ids": [1, 2, 3]
}
```

Response: `200 OK`
```json
{
  "categories": [
    {
      "id": 1,
      "name": "Main Characters",
      "color": "#3B82F6"
    }
  ]
}
```

**Sprint 16 Note:** Manual assignment works alongside auto-linking. Entry dapat simultaneously be:
- Manually assigned ke category A
- Auto-linked ke category B (via tag)
- Total categories = manual + auto

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

### Sprint 14: Tags System & Enhanced Details

#### List Tags for Novel

**`GET /api/novels/{novel}/codex/tags`**

Mendapatkan semua tags untuk novel, dengan optional filter by entry type.

Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| entry_type | string | Filter tags untuk specific entry type (character, location, dll) |

Response: `200 OK`
```json
{
  "tags": [
    {
      "id": 1,
      "name": "Protagonist",
      "color": "#8B5CF6",
      "entry_type": "character",
      "is_predefined": true,
      "entries_count": 5
    }
  ]
}
```

---

#### Create Tag

**`POST /api/novels/{novel}/codex/tags`**

Membuat tag baru untuk novel. Tags digunakan untuk organizational purposes dan TIDAK dikirim ke AI.

Request Body:
```json
{
  "name": "Main Cast",
  "color": "#3B82F6",
  "entry_type": "character"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | max:50, unique per novel |
| color | string | No | Hex color format (#RRGGBB) |
| entry_type | string | No | Limit tag untuk specific type |

Response: `201 Created`
```json
{
  "tag": {
    "id": 1,
    "name": "Main Cast",
    "color": "#3B82F6",
    "entry_type": "character",
    "is_predefined": false,
    "entries_count": 0
  }
}
```

---

#### Update Tag

**`PATCH /api/codex/tags/{tag}`**

Update tag existing. CANNOT update predefined tags.

Request Body:
```json
{
  "name": "Updated Name",
  "color": "#EF4444"
}
```

Response: `200 OK` dengan updated tag object.

---

#### Delete Tag

**`DELETE /api/codex/tags/{tag}`**

Delete tag. CANNOT delete predefined tags. Assignments ke entries akan otomatis removed (cascade).

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Assign Tag to Entry

**`POST /api/codex/{entry}/tags`**

Assign tag ke codex entry (auto-save, instant update).

Request Body:
```json
{
  "tag_id": 1
}
```

Validation:
- Tag must belong to same novel
- Tag entry_type filter must match entry type (if set)

Response: `200 OK`
```json
{
  "success": true,
  "tag": {
    "id": 1,
    "name": "Protagonist",
    "color": "#8B5CF6"
  }
}
```

---

#### Remove Tag from Entry

**`DELETE /api/codex/{entry}/tags/{tag}`**

Remove tag assignment dari entry (auto-save, instant update).

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Initialize Predefined Tags

**`POST /api/novels/{novel}/codex/tags/initialize`**

Initialize predefined tags untuk novel (automatically called on first tag access).

Predefined tags per type:
- **Character:** Protagonist, Antagonist, Supporting, Minor, Mentioned
- **Location:** Major, Minor, Historical
- **Item:** Weapon, Artifact, Tool

Response: `200 OK`
```json
{
  "message": "Predefined tags initialized successfully.",
  "initialized": true,
  "count": 11
}
```

---

#### List Detail Definitions

**`GET /api/novels/{novel}/codex/detail-definitions`**

Mendapatkan custom definitions dan system presets untuk novel.

Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| entry_type | string | Filter definitions untuk specific entry type |

Response: `200 OK`
```json
{
  "definitions": [
    {
      "id": 1,
      "name": "Custom Field",
      "type": "dropdown",
      "options": ["Option A", "Option B"],
      "entry_types": ["character"],
      "show_in_sidebar": false,
      "ai_visibility": "always",
      "is_preset": false
    }
  ],
  "presets": [
    {
      "id": "preset_0",
      "name": "Story Role",
      "type": "dropdown",
      "options": ["Protagonist", "Antagonist", "Supporting", "Minor"],
      "entry_types": ["character"],
      "show_in_sidebar": true,
      "ai_visibility": "always",
      "is_preset": true
    }
  ]
}
```

**System Presets Available:**
- **Story Role** (dropdown): Protagonist, Antagonist, Supporting, Minor
- **Pronouns** (dropdown): he/him, she/her, they/them, other
- **Backstory** (text): Full character backstory
- **Occupation** (line): Single-line occupation
- **Physical Appearance** (text): Description, AI visibility = never
- **Voice Sheet** (text): Character voice/dialogue notes
- **Fighting Style** (text): Combat description, AI visibility = nsfw_only
- **Location Type** (dropdown): City, Town, Village, Building, dll
- **Climate** (line): Location climate info
- **Item Type** (dropdown): Weapon, Armor, Tool, Artifact, dll
- **Powers/Abilities** (text): Special powers/skills
- **Organization Type** (dropdown): Government, Military, Religious, dll

---

#### Create Detail Definition

**`POST /api/novels/{novel}/codex/detail-definitions`**

Membuat custom detail definition untuk novel.

Request Body:
```json
{
  "name": "Custom Attribute",
  "type": "dropdown",
  "options": ["Option A", "Option B", "Option C"],
  "entry_types": ["character"],
  "show_in_sidebar": false,
  "ai_visibility": "always"
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | string | Yes | max:100 |
| type | string | Yes | text, line, dropdown, codex_reference |
| options | array | Required for dropdown | min:2 options |
| entry_types | array | No | Array of valid types, null = all types |
| show_in_sidebar | boolean | No | default: false |
| ai_visibility | string | No | always, never, nsfw_only (default: always) |

Response: `201 Created` dengan definition object.

---

#### Update Detail Definition

**`PATCH /api/codex/detail-definitions/{definition}`**

Update custom definition. CANNOT update system presets.

Request Body: Same as Create Detail Definition

Response: `200 OK` dengan updated definition.

---

#### Delete Detail Definition

**`DELETE /api/codex/detail-definitions/{definition}`**

Delete custom definition. CANNOT delete system presets.

Validation:
- Definition must not be used by any details
- Will return 422 dengan usage count if still in use

Response: `200 OK`
```json
{
  "success": true
}
```

---

#### Add Detail from Preset

**`POST /api/codex/{entry}/details/from-preset`**

Quick create detail menggunakan system preset template.

Request Body:
```json
{
  "preset_index": 0,
  "value": "Protagonist"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| preset_index | int | Yes | Index dari CodexDetailDefinition::SYSTEM_PRESETS |
| value | string | No | Initial value (default to first dropdown option jika dropdown) |

Validation:
- Preset must be valid for entry type
- Preset index must exist

Response: `201 Created`
```json
{
  "detail": {
    "id": 1,
    "key_name": "Story Role",
    "value": "Protagonist",
    "type": "dropdown",
    "ai_visibility": "always",
    "definition": null
  },
  "preset": {
    "name": "Story Role",
    "type": "dropdown",
    "options": ["Protagonist", "Antagonist", "Supporting", "Minor"]
  }
}
```

---

#### Detail Types

**Text Type:**
- Multi-line text input
- Unlimited length (within MySQL TEXT limits)
- Use for: backstory, notes, descriptions

**Line Type:**
- Single-line text input
- Best for: occupation, age, simple attributes

**Dropdown Type:**
- Pre-defined options
- User selects from list
- `show_in_sidebar` option displays value next to entry name

**Codex Reference Type:**
- Links to another codex entry
- Future implementation untuk Sprint 16+

---

#### AI Visibility Modes

Setiap detail dapat di-set visibility mode-nya:

| Mode | Behavior | Use Case |
|------|----------|----------|
| **always** | Always included in AI context | Important character traits, plot-relevant info |
| **never** | Never sent to AI | Private notes, meta information, physical descriptions |
| **nsfw_only** | Only included dengan NSFW prompts | Combat details, mature content |

**Update via Detail PATCH endpoint:**
```json
{
  "ai_visibility": "never"
}
```

---

### Sprint 15: Editor Integration & UX Enhancements

#### Duplicate Entry

**`POST /api/codex/{entry}/duplicate`**

Deep-clone entry dengan semua aliases, details, dan progressions (tanpa scene links).

Request Body: None

Response: `302 Redirect` ke entry baru

**Clone Behavior:**
- Name appended dengan "(Copy)" atau "(Copy 2)" jika duplikat
- Semua aliases di-clone
- Semua details di-clone (tanpa progression pada details)
- Progressions di-clone tanpa scene associations
- Thumbnail, research_notes, tags tetap sama

---

#### Bulk Create Entries

**`POST /api/novels/{novel}/codex/bulk-create`**

Create multiple entries dari formatted text input. Format: `Name | Type | Description`

Request Body:
```json
{
  "input": "Alice | character | A young witch\nBob | character | Alice's mentor\nCastle | location | A dark fortress",
  "preview": false,
  "skip_duplicates": true
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| input | string | Yes | Multi-line text, satu entry per baris |
| preview | boolean | No | Jika true, return preview tanpa create (default: false) |
| skip_duplicates | boolean | No | Skip entry jika nama sudah ada (default: true) |

**Input Format Rules:**
- Format: `Name | Type | Description`
- Type: character, location, item, lore, organization, subplot
- Description opsional
- Lines starting with `#` diabaikan (comment)
- Empty lines diabaikan
- Whitespace otomatis di-trim

Response (Preview Mode): `200 OK`
```json
{
  "preview": {
    "entries": [
      {
        "name": "Alice",
        "type": "character",
        "description": "A young witch"
      }
    ],
    "errors": [
      {
        "line": 5,
        "message": "Invalid type 'charcter'. Did you mean 'character'?"
      }
    ],
    "warnings": [
      {
        "line": 3,
        "message": "Entry 'Bob' already exists in this novel"
      }
    ]
  },
  "stats": {
    "valid": 2,
    "errors": 1,
    "warnings": 1
  }
}
```

Response (Create Mode): `201 Created`
```json
{
  "created": [
    {
      "id": 10,
      "name": "Alice",
      "type": "character"
    },
    {
      "id": 11,
      "name": "Castle",
      "type": "location"
    }
  ],
  "skipped": [
    {
      "name": "Bob",
      "reason": "Duplicate name"
    }
  ],
  "stats": {
    "created": 2,
    "skipped": 1
  }
}
```

**Type Suggestions:**
Service akan suggest type yang paling mirip untuk typo:
- "charcter" → "character"
- "loction" → "location"
- "characters" (plural) → "character"

---

#### Swap Relation Direction

**`POST /api/codex/relations/{relation}/swap`**

Swap source dan target entry dalam relation. Berguna untuk memperbaiki arah relation tanpa delete-recreate.

Request Body: None

Response: `200 OK`
```json
{
  "relation": {
    "id": 5,
    "source_entry_id": 2,
    "target_entry_id": 1,
    "type": "enemy"
  },
  "message": "Relation direction swapped successfully"
}
```

**Before:**
```
Entry A --[enemy]--> Entry B
```

**After:**
```
Entry B --[enemy]--> Entry A
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

- **Sprint Documentation:** 
  - [Sprint 04 - Codex System](../10-sprints/sprint-04-codex-system.md)
  - [Sprint 13 - Codex V2 Enhancements](../10-sprints/sprint-13-codex-v2-enhancements.md)
  - [Sprint 15 - Editor Integration & UX](../10-sprints/sprint-15-codex-v2-editor-ux.md)
- **Testing Guide:** 
  - [Codex Testing](../06-testing/codex-testing.md)
  - [Codex Sprint 15 Testing](../06-testing/codex-sprint15-testing.md)
- **User Journeys:** [Codex Sprint 15 Editor Integration](../07-user-journeys/codex/sprint-15-editor-integration.md)

---

## Version History

### v1.3.0 (2026-01-01) - Sprint 15
- ✨ Added Duplicate Entry endpoint (`POST /api/codex/{entry}/duplicate`)
- ✨ Added Bulk Create endpoint dengan preview mode dan validation
- ✨ Added Swap Relation Direction endpoint
- 🎨 New TipTap extensions: CodexProgression, QuickCreateCodex
- 🎯 New Vue components: ProgressionEditorModal, BulkCreateModal, CodexHoverTooltip, SelectionActionMenu
- 📱 Mobile support untuk tap tooltips dan selection menu
- ✅ Comprehensive test coverage (21 unit + 12 feature tests)

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
