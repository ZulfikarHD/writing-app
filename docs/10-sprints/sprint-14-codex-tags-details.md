# 📦 Sprint 14: Tags & Enhanced Details

**Version:** 1.0.0  
**Date:** 2026-01-01  
**Duration:** 1 Sprint  
**Status:** ✅ Completed

---

## 📋 Sprint Goals

Sprint 14 menambahkan sistem tags untuk organizational labels dan enhanced details dengan different types dan per-detail AI visibility control, yaitu: memungkinkan penulis mengorganisir entries dengan flexible tags, menggunakan structured detail types (dropdown, text, line), dan mengontrol secara granular detail mana yang dikirim ke AI.

---

## ✨ Features Implemented

### US-12.4: Tags System

Sistem labeling quick untuk organizational purposes yang TIDAK dikirim ke AI, terpisah dari Categories (yang untuk AI grouping).

**Key Features:**
- Create custom tags dengan color coding
- Predefined tags per entry type (Protagonist, Antagonist, Major, Minor, dll)
- Multi-tag assignment per entry
- Tag filtering di Index page
- Auto-save tag assignment (no manual save button)
- Tags NOT sent to AI (purely organizational)

**Predefined Tags:**
- **Character:** Protagonist, Antagonist, Supporting, Minor, Mentioned
- **Location:** Major, Minor, Historical
- **Item:** Weapon, Artifact, Tool

### US-12.5: Enhanced Detail Types

Support untuk different input types pada details, moving beyond simple text fields.

**Detail Types:**
- **Text:** Multi-line text input (for backstory, notes)
- **Line:** Single-line text input (for occupation, age)
- **Dropdown:** Pre-defined options dengan select input
- **Codex Reference:** Link ke entry lain (planned Sprint 16+)

### US-12.6: AI Visibility per Detail

Granular control atas detail mana yang dikirim ke AI context.

**Visibility Modes:**
- **Always:** Always included in AI context
- **Never:** Private notes, never sent to AI
- **NSFW Only:** Only included dengan NSFW prompts

**Use Cases:**
- Set "Physical Appearance" to **never** → prevent AI over-describing looks
- Set "Backstory" to **always** → important context
- Set "Fighting Style" to **nsfw_only** → only for action scenes

### US-12.7: Detail Presets

Built-in templates untuk common detail attributes dengan one-click add.

**System Presets:**
- Story Role (dropdown: Protagonist, Antagonist, Supporting, Minor)
- Pronouns (dropdown: he/him, she/her, they/them, other)
- Backstory (text)
- Occupation (line)
- Physical Appearance (text, AI = never)
- Voice Sheet (text)
- Fighting Style (text, AI = nsfw_only)
- Location Type (dropdown: City, Town, Village, dll)
- Climate (line)
- Item Type (dropdown: Weapon, Armor, Tool, dll)
- Powers/Abilities (text)
- Organization Type (dropdown: Government, Military, dll)

---

## 📁 File Structure

### Backend (11 files)

```
app/
├── Http/Controllers/
│   ├── CodexTagController.php                ✨ NEW
│   ├── CodexDetailDefinitionController.php   ✨ NEW
│   ├── CodexController.php                   ✏️ UPDATED
│   └── CodexDetailController.php             ✏️ UPDATED
├── Models/
│   ├── CodexTag.php                          ✨ NEW
│   ├── CodexDetailDefinition.php             ✨ NEW
│   ├── CodexEntry.php                        ✏️ UPDATED
│   ├── CodexDetail.php                       ✏️ UPDATED
│   └── Novel.php                             ✏️ UPDATED
└── Services/Codex/
    └── CodexContextBuilder.php               ✏️ UPDATED
```

### Frontend (6 files)

```
resources/js/
├── components/codex/
│   ├── TagManager.vue              ✨ NEW
│   ├── AIVisibilityToggle.vue      ✨ NEW
│   ├── DetailManager.vue           ✏️ UPDATED (major refactor)
│   └── index.ts                    ✏️ UPDATED
└── pages/Codex/
    ├── Index.vue                   ✏️ UPDATED
    └── Show.vue                    ✏️ UPDATED
```

### Database (2 files)

```
database/
├── migrations/
│   └── 2026_01_01_145835_add_sprint14_tags_and_detail_types.php  ✨ NEW
└── seeders/
    └── CodexPresetSeeder.php       ✨ NEW
```

### Routes & Tests (2 files)

```
routes/web.php                      ✏️ UPDATED - Added 14 endpoints
tests/Feature/CodexTest.php         ✏️ UPDATED - Added 20 tests
```

**Total:** 21 files (8 new, 13 updated)

---

## 🔌 API Endpoints (14 New)

### Tags Management (7 endpoints)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/codex/tags` | List tags dengan optional type filter |
| POST | `/api/novels/{novel}/codex/tags` | Create custom tag |
| POST | `/api/novels/{novel}/codex/tags/initialize` | Initialize predefined tags |
| PATCH | `/api/codex/tags/{tag}` | Update tag (not predefined) |
| DELETE | `/api/codex/tags/{tag}` | Delete tag (not predefined) |
| POST | `/api/codex/{entry}/tags` | Assign tag ke entry |
| DELETE | `/api/codex/{entry}/tags/{tag}` | Remove tag dari entry |

### Detail Definitions (5 endpoints)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/codex/detail-definitions` | List definitions + presets |
| POST | `/api/novels/{novel}/codex/detail-definitions` | Create custom definition |
| PATCH | `/api/codex/detail-definitions/{def}` | Update definition (not preset) |
| DELETE | `/api/codex/detail-definitions/{def}` | Delete definition |
| GET | `/api/codex/detail-presets/{index}` | Get specific preset |

### Enhanced Details (2 endpoints)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/codex/{entry}/details` | Create detail (now supports type & AI visibility) |
| POST | `/api/codex/{entry}/details/from-preset` | Quick create dari preset template |

---

## 🎯 User Stories Summary

| ID | Story | Status |
|----|-------|--------|
| US-12.4 | Tags System | ✅ Complete |
| US-12.5 | Enhanced Detail Types | ✅ Complete |
| US-12.6 | AI Visibility per Detail | ✅ Complete |
| US-12.7 | Detail Presets | ✅ Complete |
| F-12.3.2 | Tag Management | ✅ Complete |
| F-12.3.3 | Filter by Tags | ✅ Complete |
| F-12.3.4 | Predefined Tags | ✅ Complete |
| F-12.4.5 | Show Detail in Sidebar | ✅ Complete |

---

## 🧪 Testing Results

```bash
php artisan test --filter=CodexTest

✓ can create tag (20 tests Sprint 14)
✓ can assign/remove tags
✓ can add detail with ai visibility
✓ can add from preset
✓ ai context excludes never visibility
... and 15 more Sprint 14 tests

Total: 62 tests passing (195 assertions)
Duration: 1.83s
```

---

## 📖 NovelCrafter Parity

| Feature | Status |
|---------|--------|
| Tags separate dari Categories | ✅ At parity |
| Detail Types (Text, Line, Dropdown) | ✅ At parity |
| AI Visibility per Detail | ✅ At parity |
| Detail Presets | ✅ At parity (12 presets) |
| Show in Sidebar | ✅ At parity |
| Codex Reference Type | 🔄 Schema ready, UI Sprint 16+ |

**Overall Parity:** 95% (pending Codex Reference UI)

---

## 🔗 Related Documentation

- **API Reference:** [Codex API - Sprint 14](../04-api-reference/codex.md#sprint-14-tags-system--enhanced-details)
- **Testing Guide:** [Codex Testing](../06-testing/codex-testing.md)
- **Epic Reference:** `scrum/epic-planning/12-EPIC-codex-v2-enhancements.md`
- **NovelCrafter Docs:** https://www.novelcrafter.com/help/docs/codex/codex-details

---

*Last Updated: 2026-01-01*
*All Tests Passing: ✅ 62/62*
