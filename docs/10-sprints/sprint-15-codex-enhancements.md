# 📦 Sprint 15: Codex Enhancements - Batch Operations & QoL

**Version:** 1.0.0  
**Date:** 2026-01-01  
**Duration:** 1 Sprint  
**Status:** ✅ Completed

---

## 📋 Sprint Goals

Sprint 15 menambahkan fitur batch operations dan quality-of-life improvements pada Codex system, yaitu: mempercepat setup awal melalui bulk create, memudahkan penggandaan entries dengan duplicate function, dan memperbaiki relation direction tanpa re-create melalui swap feature.

---

## ✨ Features Implemented

### F-12.7.2: Duplicate Codex Entry

Deep clone functionality yang memungkinkan user menduplikasi codex entry beserta semua aliases, details, dan progressions.

**What Gets Cloned:**
- ✅ Entry data (name, type, description, research_notes)
- ✅ All aliases
- ✅ All details (with AI visibility & type)
- ✅ Progressions (WITHOUT scene links)

**What Doesn't Get Cloned:**
- ❌ Thumbnail (user uploads new)
- ❌ Relations (avoid complex bidirectional issues)
- ❌ Mentions (scene-specific)
- ❌ Categories & Tags (user re-assigns)

### US-12.12: Bulk Create Entries

Rapid codex setup melalui formatted text input: "Name | Type | Description"

**Key Features:**
- Multi-line text input (one entry per line)
- Fuzzy type matching dengan 40+ aliases
- Comment lines support (# prefix)
- Preview before create
- Skip duplicates option
- Line-by-line error reporting

**Supported Formats:**
```
Character Name | character | Description here
Location | location | Place description
Weapon | item | Weapon description

# This is a comment - ignored
Special Sword | item | Legendary weapon
```

**Type Aliases:**
- `char`, `person`, `npc` → `character`
- `loc`, `place` → `location`
- `org`, `faction` → `organization`
- `plot`, `arc` → `subplot`

### US-12.14: Swap Relation Direction

One-click swap untuk fix relation direction mistakes.

**Example:**
```
Before: Alice → Bob (mentor_of)
After swap: Bob → Alice (mentor_of)
```

Preserves: type, label, bidirectional flag, timestamps

---

## 📁 File Structure

### Backend (4 files)

```
app/
├── Http/Controllers/
│   ├── CodexController.php           ✏️ UPDATED - duplicate(), bulkCreate()
│   └── CodexRelationController.php   ✏️ UPDATED - swap()
└── Services/Codex/
    └── BulkEntryCreator.php          ✨ NEW - Parse & create logic
```

### Frontend (6 files)

```
resources/js/
├── components/codex/
│   ├── BulkCreateModal.vue           ✨ NEW
│   ├── CodexHoverTooltip.vue         ✨ NEW
│   ├── ProgressionEditorModal.vue    ✨ NEW
│   └── index.ts                      ✏️ UPDATED
└── pages/Codex/
    ├── Index.vue                     ✏️ UPDATED
    └── Show.vue                      ✏️ UPDATED
```

### Tests (2 files)

```
tests/
├── Feature/CodexTest.php             ✏️ UPDATED - Added 18 tests
└── Unit/BulkEntryCreatorTest.php     ✨ NEW
```

**Total:** 12 files (4 new, 8 updated)

---

## 🔌 API Endpoints (3 New)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/codex/{entry}/duplicate` | Deep clone entry | Required |
| POST | `/api/novels/{novel}/codex/bulk-create` | Bulk create dari text | Required |
| POST | `/api/codex/relations/{relation}/swap` | Swap source ↔ target | Required |

---

## 🧪 Testing Results

```bash
✓ can duplicate entry
✓ duplicate clones aliases & details
✓ duplicate increments name correctly

✓ can bulk create entries
✓ bulk create preview mode
✓ bulk create skips duplicates
✓ bulk create ignores comments

✓ can swap relation direction
✓ swap preserves metadata

Total: 18 new tests (62 assertions)
All passing ✅
```

---

## 🎯 User Stories

| ID | Story | Status |
|----|-------|--------|
| F-12.7.2 | Duplicate Entry | ✅ Complete |
| US-12.12 | Bulk Create Entries | ✅ Complete |
| US-12.14 | Swap Relation Direction | ✅ Complete |

---

## 📖 NovelCrafter Parity

| Feature | Status |
|---------|--------|
| Duplicate Entry | ✅ At parity |
| Bulk Import | ✅ At parity (text-based) |
| Relation Swap | ⚡ Enhancement (not in NC) |

---

## 🔗 Related Documentation

- **API Reference:** [Codex API - Sprint 15](../04-api-reference/codex.md#sprint-15-editor-integration--ux-enhancements)
- **Testing Guide:** [Sprint 15 Testing](../06-testing/sprint-15-testing.md)
- **Previous Sprint:** [Sprint 14 - Tags & Details](./sprint-14-codex-tags-details.md)

---

*Last Updated: 2026-01-01*
