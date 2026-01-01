# 📦 Sprint 04: Codex System

**Version:** 1.0.0  
**Date:** 2026-01-01  
**Duration:** 1 Sprint  
**Status:** ✅ Completed

## 📋 Sprint Goals

Implementasi sistem Codex lengkap untuk writing app, yaitu: fitur manajemen world-building yang memungkinkan penulis mengelola karakter, lokasi, item, lore, organisasi, dan subplot dalam novel mereka dengan dukungan AI context integration.

---

## ✨ Features Implemented

### P1 - Core Codex Features

#### Codex Entries
- Manajemen entry dengan 6 tipe: Character, Location, Item, Lore, Organization, Subplot
- CRUD operations lengkap (Create, Read, Update, Delete)
- Archive/restore functionality
- Sort order untuk pengaturan urutan tampilan
- AI Context Mode (Always, Detected, Manual, Never)
- Search dan filter berdasarkan type, category, dan keyword

#### Codex Aliases
- Alternative names untuk setiap entry (nicknames, alternate spellings)
- Digunakan untuk mention detection dalam editor
- CRUD operations dengan instant updates

#### Codex Details
- Key-value pairs untuk structured data (height, age, occupation, etc.)
- Sortable dengan drag-drop
- CRUD operations dengan inline editing

#### Codex Relations
- Menghubungkan entries satu sama lain
- Bidirectional relations support
- Relation types: knows, allied_with, enemy_of, member_of, located_in, owns, related_to, works_for, reports_to, married_to, parent_of, child_of
- Custom labels untuk setiap relasi
- Graph visualization support

#### Codex Progressions
- Track perubahan entry sepanjang cerita
- Mode: Addition (menambah info) atau Replacement (mengganti info)
- Terhubung ke specific scene (progression aktif dari scene tersebut)
- Bisa terhubung ke specific detail (untuk replace detail value)
- Timeline visualization

#### Codex Categories
- Custom categories per novel untuk organisasi
- Color coding untuk visual distinction
- Filter entries berdasarkan category
- Many-to-many relationship dengan entries

#### Codex Mentions
- Auto-tracking mentions dalam scene content
- Mention count per scene
- Heatmap visualization di entry page
- Manual rescan functionality
- Background job untuk scan otomatis

### P2 - Codex Enhancements

#### Bulk Import/Export
- Export ke JSON format dengan semua relasi dan data
- Export ke CSV untuk spreadsheet compatibility
- Import dari JSON dengan preview before commit
- Skip duplicates atau overwrite options

#### Quick Create Modal
- Create entry langsung dari editor
- Minimal fields untuk fast entry creation
- Auto-add selected text as alias

#### AI Context Control
- Per-entry control untuk AI inclusion
- 4 modes: Always include, Include when detected, Manual include, Never include
- Visual indicator di entry cards

#### Mention Heatmap
- Visual representation mentions across scenes
- Color intensity berdasarkan mention count
- Click to navigate ke scene

### P3 - Series Codex (NEW)

#### Series Management
- Create dan manage book series
- Assign novels to series dengan ordering
- Series metadata (title, description, genre, cover)
- Reorder novels dalam series

#### Series Codex Entries
- Codex entries di level series (shared across all novels)
- Series entries otomatis visible di novel codex
- Aliases dan details per series entry
- Override capability per novel (novel-specific customization)

#### Series-Novel Inheritance
- Novel dalam series melihat series entries
- Filter untuk membedakan series vs novel entries
- Seamless integration dengan existing codex UI

---

## 📁 File Structure

### Backend Files

```
app/
├── Http/Controllers/
│   ├── CodexController.php              ✨ NEW
│   ├── CodexAliasController.php         ✨ NEW
│   ├── CodexDetailController.php        ✨ NEW
│   ├── CodexRelationController.php      ✨ NEW
│   ├── CodexProgressionController.php   ✨ NEW
│   ├── CodexCategoryController.php      ✨ NEW
│   ├── SeriesController.php             ✨ NEW
│   ├── SeriesCodexController.php        ✨ NEW
│   └── PlanController.php               ✏️ UPDATED
├── Http/Requests/
│   ├── StoreCodexEntryRequest.php       ✨ NEW
│   └── UpdateCodexEntryRequest.php      ✨ NEW
├── Jobs/
│   └── ScanSceneMentionsJob.php         ✨ NEW
├── Models/
│   ├── CodexEntry.php                   ✨ NEW
│   ├── CodexAlias.php                   ✨ NEW
│   ├── CodexDetail.php                  ✨ NEW
│   ├── CodexRelation.php                ✨ NEW
│   ├── CodexProgression.php             ✨ NEW
│   ├── CodexCategory.php                ✨ NEW
│   ├── CodexMention.php                 ✨ NEW
│   ├── Series.php                       ✨ NEW
│   ├── SeriesCodexEntry.php             ✨ NEW
│   ├── SeriesCodexAlias.php             ✨ NEW
│   ├── SeriesCodexDetail.php            ✨ NEW
│   ├── NovelSeriesCodexOverride.php     ✨ NEW
│   ├── Novel.php                        ✏️ UPDATED
│   └── Scene.php                        ✏️ UPDATED
├── Observers/
│   └── SceneObserver.php                ✨ NEW
├── Providers/
│   └── AppServiceProvider.php           ✏️ UPDATED
└── Services/Codex/
    ├── BulkExportService.php            ✨ NEW
    ├── BulkImportService.php            ✨ NEW
    └── MentionTracker.php               ✨ NEW
```

### Database Migrations

```
database/migrations/
├── 2026_01_01_111644_create_codex_entries_table.php
├── 2026_01_01_111651_create_codex_aliases_table.php
├── 2026_01_01_111652_create_codex_details_table.php
├── 2026_01_01_111652_create_codex_relations_table.php
├── 2026_01_01_111653_create_codex_progressions_table.php
├── 2026_01_01_111654_create_codex_categories_table.php
├── 2026_01_01_111654_create_codex_entry_categories_table.php
├── 2026_01_01_111655_create_codex_mentions_table.php
├── 2026_01_01_120812_add_mode_to_codex_progressions_table.php
├── 2026_01_01_124834_create_series_table.php
└── 2026_01_01_124900_create_series_codex_entries_table.php
```

### Frontend Files

```
resources/js/
├── pages/
│   ├── Codex/
│   │   ├── Index.vue                    ✨ NEW
│   │   ├── Create.vue                   ✨ NEW
│   │   ├── Edit.vue                     ✨ NEW
│   │   └── Show.vue                     ✨ NEW
│   ├── Series/
│   │   ├── Index.vue                    ✨ NEW
│   │   ├── Create.vue                   ✨ NEW
│   │   ├── Edit.vue                     ✨ NEW
│   │   ├── Show.vue                     ✨ NEW
│   │   └── Codex/
│   │       ├── Index.vue                ✨ NEW
│   │       └── Show.vue                 ✨ NEW
│   ├── Plan/
│   │   └── Index.vue                    ✏️ UPDATED
│   └── Editor/
│       └── Index.vue                    ✏️ UPDATED
├── components/
│   ├── codex/
│   │   ├── AIContextControl.vue         ✨ NEW
│   │   ├── AliasManager.vue             ✨ NEW
│   │   ├── BulkExportButton.vue         ✨ NEW
│   │   ├── BulkImportModal.vue          ✨ NEW
│   │   ├── CategoryManager.vue          ✨ NEW
│   │   ├── CodexEntryCard.vue           ✨ NEW
│   │   ├── CodexEntryForm.vue           ✨ NEW
│   │   ├── CodexTypeBadge.vue           ✨ NEW
│   │   ├── CodexTypeIcon.vue            ✨ NEW
│   │   ├── DetailManager.vue            ✨ NEW
│   │   ├── MentionHeatmap.vue           ✨ NEW
│   │   ├── ProgressionManager.vue       ✨ NEW
│   │   ├── ProgressionTimeline.vue      ✨ NEW
│   │   ├── QuickCreateModal.vue         ✨ NEW
│   │   ├── RelationManager.vue          ✨ NEW
│   │   └── index.ts                     ✨ NEW
│   ├── editor/
│   │   ├── CodexSidebarPanel.vue        ✨ NEW
│   │   ├── EditorSidebar.vue            ✨ NEW
│   │   └── TipTapEditor.vue             ✏️ UPDATED
│   └── plan/
│       ├── ChapterGroup.vue             ✨ NEW
│       └── SceneCard.vue                ✏️ UPDATED
├── extensions/
│   └── CodexHighlight.ts                ✨ NEW
├── composables/
│   └── useCodexHighlight.ts             ✨ NEW
└── routes/
    ├── codex/
    │   ├── index.ts                     ✨ NEW
    │   ├── aliases/index.ts             ✨ NEW
    │   ├── details/index.ts             ✨ NEW
    │   ├── progressions/index.ts        ✨ NEW
    │   ├── relations/index.ts           ✨ NEW
    │   └── api/index.ts                 ✨ NEW
    └── plan/
        └── index.ts                     ✏️ UPDATED
```

---

## 🔌 API Endpoints Summary

| Group | Count | Prefix |
|-------|-------|--------|
| Codex CRUD | 12 | `novels/{novel}/codex` |
| Codex Aliases | 3 | `api/codex/{entry}/aliases` |
| Codex Details | 4 | `api/codex/{entry}/details` |
| Codex Relations | 4 | `api/codex/{entry}/relations` |
| Codex Progressions | 3 | `api/codex/{entry}/progressions` |
| Codex Categories | 4 | `api/codex/categories` |
| Bulk Operations | 4 | `novels/{novel}/codex` |
| Series | 9 | `series` |
| Series Codex | 3 | `series/{series}/codex` |

> 📡 Full API documentation: [Codex API](../04-api-reference/codex.md) | [Series API](../04-api-reference/series.md)

---

## 🗃️ Database Schema

### Codex Entries Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| novel_id | bigint | Foreign key to novels |
| type | enum | character, location, item, lore, organization, subplot |
| name | varchar(255) | Entry name |
| description | text | Entry description |
| thumbnail_path | varchar(255) | Path to thumbnail image |
| ai_context_mode | varchar(50) | always, detected, manual, never |
| sort_order | int | Display order |
| is_archived | boolean | Soft archive flag |
| timestamps, soft deletes | - | Laravel standard |

### Series Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| title | varchar(255) | Series title |
| description | text | Series description |
| cover_path | varchar(255) | Path to cover image |
| genre | varchar(100) | Series genre |
| sort_order | int | Display order |
| timestamps | - | Laravel standard |

---

## 🔗 Related Documentation

- **API Reference:** [Codex API](../04-api-reference/codex.md) | [Series API](../04-api-reference/series.md)
- **Testing Guide:** [Codex Testing](../06-testing/codex-testing.md)
- **Previous Sprint:** [Sprint 03 - AI UI System](./sprint-03-ai-ui-system.md)

---

*Last Updated: 2026-01-01*
