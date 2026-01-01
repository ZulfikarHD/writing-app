# 🖊️ Sprint 02 - Manuscript Editor

## Overview

Sprint Manuscript Editor untuk NovelWrite (Phase 1 & 2), yaitu: implementasi text editor berbasis TipTap dengan fitur auto-save, revision history, chapter/scene management, real-time word count tracking, Acts hierarchy, Scene Labels, Scene Metadata Panel, dan Plan view dengan Grid layout untuk story planning.

---

## Sprint Info

| Property | Value |
|----------|-------|
| **Sprint Name** | Manuscript Editor (Extended) |
| **Status** | ✅ Complete |
| **Start Date** | 2025-12-31 |
| **End Date** | 2026-01-01 |
| **Total Story Points** | 67 (Phase 1: 34 + Phase 2: 33) |

---

## User Stories Completed

### Phase 1: Core Editor (34 points)

| ID | Story | Points | Status |
|----|-------|--------|--------|
| ME-001 | User dapat membuka editor dari novel card | 2 | ✅ Done |
| ME-002 | User dapat menulis di TipTap rich text editor | 5 | ✅ Done |
| ME-003 | User dapat melihat chapter/scene tree di sidebar | 3 | ✅ Done |
| ME-004 | User dapat membuat chapter baru | 2 | ✅ Done |
| ME-005 | User dapat membuat scene baru dalam chapter | 2 | ✅ Done |
| ME-006 | User dapat switch antar scene | 2 | ✅ Done |
| ME-007 | User dapat auto-save saat mengetik | 5 | ✅ Done |
| ME-008 | User dapat manual save dengan Ctrl+S | 2 | ✅ Done |
| ME-009 | User dapat undo/redo di editor | 3 | ✅ Done |
| ME-010 | User dapat format text (bold, italic, underline, strike) | 3 | ✅ Done |
| ME-011 | User dapat melihat word count real-time | 2 | ✅ Done |
| ME-012 | User dapat melihat save status indicator | 1 | ✅ Done |
| ME-013 | User dapat archive scene | 2 | ✅ Done |

### Phase 2: Advanced Features (33 points)

| ID | Story | Points | Status |
|----|-------|--------|--------|
| ME-014 | User dapat melihat/edit scene metadata (POV, notes, subtitle) | 5 | ✅ Done |
| ME-015 | User dapat menggunakan action menu (duplicate, delete scene/chapter) | 5 | ✅ Done |
| ME-016 | User dapat membuat Acts untuk hierarchical structure | 5 | ✅ Done |
| ME-017 | User dapat melihat Grid view di Plan page | 8 | ✅ Done |
| ME-018 | User dapat create/manage Scene Labels dengan custom colors | 5 | ✅ Done |
| ME-019 | User dapat search/filter scenes by title, status, labels | 5 | ✅ Done |

---

## Technical Implementation

### Backend Files

| Category | Files |
|----------|-------|
| **Controllers** | `EditorController.php`, `ChapterController.php`, `SceneController.php`, `ActController.php`, `PlanController.php`, `SceneLabelController.php` |
| **Models** | `Chapter.php`, `Scene.php`, `SceneRevision.php`, `Act.php`, `SceneLabel.php` |
| **Form Requests** | `StoreChapterRequest.php`, `UpdateSceneContentRequest.php` |
| **Migrations** | `create_chapters_table.php`, `create_scenes_table.php`, `create_scene_revisions_table.php`, `create_acts_table.php`, `create_scene_labels_table.php`, `create_scene_label_table.php`, `add_act_id_to_chapters_table.php` |
| **Factories** | `ChapterFactory.php`, `SceneFactory.php`, `SceneRevisionFactory.php`, `ActFactory.php`, `SceneLabelFactory.php` |

### Frontend Files

| Category | Files |
|----------|-------|
| **Pages** | `Editor/Index.vue`, `Plan/Index.vue` |
| **Editor Components** | `TipTapEditor.vue`, `EditorSidebar.vue`, `EditorToolbar.vue`, `EditorSettingsPanel.vue`, `SceneMetadataPanel.vue` |
| **Plan Components** | `plan/SceneCard.vue`, `plan/ChapterGroup.vue`, `plan/SearchFilter.vue` |
| **UI Components** | `ui/ContextMenu.vue` |
| **Composables** | `useAutoSave.ts`, `useEditorSettings.ts`, `useTheme.ts` |

---

## Routes Summary

### Editor & Chapters

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/novels/{novel}/write` | editor.show | Open editor for novel |
| GET | `/novels/{novel}/write/{scene}` | editor.scene | Open editor with specific scene |
| GET | `/api/novels/{novel}/chapters` | chapters.index | List chapters with scenes |
| POST | `/api/novels/{novel}/chapters` | chapters.store | Create chapter |
| PATCH | `/api/chapters/{chapter}` | chapters.update | Update chapter |
| DELETE | `/api/chapters/{chapter}` | chapters.destroy | Delete chapter |
| POST | `/api/novels/{novel}/chapters/reorder` | chapters.reorder | Reorder chapters |

### Scenes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/api/chapters/{chapter}/scenes` | scenes.store | Create scene |
| GET | `/api/scenes/{scene}` | scenes.show | Get scene content |
| PATCH | `/api/scenes/{scene}` | scenes.update | Update scene metadata |
| PATCH | `/api/scenes/{scene}/content` | scenes.content | Auto-save content |
| DELETE | `/api/scenes/{scene}` | scenes.destroy | Delete scene |
| POST | `/api/scenes/{scene}/archive` | scenes.archive | Archive scene |
| POST | `/api/scenes/{scene}/restore` | scenes.restore | Restore archived scene |
| POST | `/api/scenes/{scene}/duplicate` | scenes.duplicate | Duplicate scene with labels |
| POST | `/api/chapters/{chapter}/scenes/reorder` | scenes.reorder | Reorder scenes |

### Scene Revisions

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/api/scenes/{scene}/revisions` | scenes.revisions | Get revision history |
| POST | `/api/scenes/{scene}/revisions` | scenes.revisions.create | Create manual revision |
| POST | `/api/scenes/{scene}/revisions/{revisionId}/restore` | scenes.revisions.restore | Restore from revision |

### Acts (Phase 2)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/api/novels/{novel}/acts` | acts.index | List acts for novel |
| POST | `/api/novels/{novel}/acts` | acts.store | Create act |
| PATCH | `/api/acts/{act}` | acts.update | Update act |
| DELETE | `/api/acts/{act}` | acts.destroy | Delete act (sets chapters' act_id to null) |
| POST | `/api/novels/{novel}/acts/reorder` | acts.reorder | Reorder acts |

### Scene Labels (Phase 2)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/api/novels/{novel}/labels` | labels.index | List labels for novel |
| POST | `/api/novels/{novel}/labels` | labels.store | Create label with custom color |
| PATCH | `/api/labels/{label}` | labels.update | Update label |
| DELETE | `/api/labels/{label}` | labels.destroy | Delete label |
| POST | `/api/scenes/{scene}/labels` | scenes.labels | Assign/replace labels to scene |

### Plan View (Phase 2)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/novels/{novel}/plan` | plan.show | Plan page with grid view |
| GET | `/api/novels/{novel}/scenes/search` | scenes.search | Search & filter scenes by title, status, labels |

---

## Database Schema

### chapters

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| novel_id | bigint | FK → novels.id, CASCADE DELETE |
| act_id | bigint | FK → acts.id, NULL ON DELETE (Phase 2) |
| title | varchar(255) | NOT NULL |
| position | integer | NOT NULL |
| settings | json | NULLABLE |
| timestamps | - | created_at, updated_at |

**Indexes:**
- `chapters_novel_id_position_index` on (novel_id, position)

### scenes

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| chapter_id | bigint | FK → chapters.id, CASCADE DELETE |
| title | varchar(255) | NULLABLE |
| content | json | NULLABLE (TipTap JSON) |
| summary | text | NULLABLE |
| position | integer | NOT NULL |
| pov_character_id | bigint | NULLABLE |
| status | varchar | draft, in_progress, completed, needs_revision |
| word_count | integer | DEFAULT 0 |
| subtitle | varchar(255) | NULLABLE |
| notes | text | NULLABLE |
| exclude_from_ai | boolean | DEFAULT false |
| metadata | json | NULLABLE |
| archived_at | timestamp | NULLABLE |
| timestamps | - | created_at, updated_at |

**Indexes:**
- `scenes_chapter_id_position_index` on (chapter_id, position)
- `scenes_status_index` on (status)

### scene_revisions

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| scene_id | bigint | FK → scenes.id, CASCADE DELETE |
| content | json | NOT NULL (TipTap JSON) |
| word_count | integer | NOT NULL |
| created_at | timestamp | NOT NULL |

**Indexes:**
- `scene_revisions_scene_id_created_at_index` on (scene_id, created_at)

### acts (Phase 2)

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| novel_id | bigint | FK → novels.id, CASCADE DELETE |
| title | varchar(255) | NOT NULL |
| position | integer | DEFAULT 0 |
| timestamps | - | created_at, updated_at |

**Indexes:**
- `acts_novel_id_position_index` on (novel_id, position)

### scene_labels (Phase 2)

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| novel_id | bigint | FK → novels.id, CASCADE DELETE |
| name | varchar(100) | NOT NULL |
| color | varchar(7) | DEFAULT '#6B7280' (hex color) |
| position | integer | DEFAULT 0 |
| timestamps | - | created_at, updated_at |

**Indexes:**
- `scene_labels_novel_id_position_index` on (novel_id, position)

### scene_label (Pivot - Phase 2)

| Column | Type | Constraints |
|--------|------|-------------|
| scene_id | bigint | FK → scenes.id, CASCADE DELETE |
| scene_label_id | bigint | FK → scene_labels.id, CASCADE DELETE |

**Primary Key:** Composite (scene_id, scene_label_id)

---

## Key Design Decisions

### Phase 1: Core Editor

#### 1. TipTap Editor dengan StarterKit
- Extensible rich text editor
- Built-in history untuk undo/redo
- JSON content format untuk database storage
- Customizable styling dengan prose classes

#### 2. Auto-Save dengan Debounce
- 500ms debounce untuk mencegah spam requests
- Save status indicator (saved, saving, unsaved, error)
- Manual save dengan Ctrl+S override debounce
- Word count update setiap save

#### 3. Scene-based Content Structure
- Setiap scene memiliki content JSON sendiri
- Chapter sebagai organisasi/grouping
- Revision history per scene untuk recovery
- Archive soft-delete untuk scene yang tidak diperlukan

#### 4. Optimistic UI Updates
- Scene switch tanpa reload halaman
- Sidebar state tetap saat navigasi
- Word count update real-time dari client + server sync

### Phase 2: Advanced Features

#### 5. Optional Acts Hierarchy
- Acts bersifat optional (chapters dapat exist tanpa act)
- Delete act → chapters' `act_id` set to `null` (tidak delete chapters)
- Position-based ordering untuk flexibility
- Digunakan untuk three-act structure atau struktur naratif kompleks

#### 6. Scene Labels dengan Custom Colors
- Per-novel custom labels dengan hex color picker
- Many-to-many relationship (scene dapat punya multiple labels)
- Digunakan untuk tagging (Action, Romance, Important, etc.)
- Sync replace saat assign labels (tidak append)

#### 7. Scene Metadata Panel
- Slide-over panel dengan spring animation
- Auto-save dengan 500ms debounce
- Fields: title, subtitle, summary, notes, status, POV, labels, exclude_from_AI
- Keyboard shortcut: Ctrl+I untuk open/close

#### 8. Plan View dengan Grid Layout
- Card-based layout untuk visual story planning
- Collapsible chapter groups dengan drag-drop reordering
- Search by title/summary dengan debounce
- Filter by status dan labels (multi-select)
- Right-click context menu untuk scene actions

#### 9. Context Menu Pattern
- Reusable context menu component
- Auto-positioning untuk tidak keluar viewport
- Used untuk scene dan chapter actions
- Actions: duplicate, archive, delete, open metadata

---

## Testing Summary

### Test Coverage

| Feature | Tests | Assertions | Status |
|---------|-------|------------|--------|
| Chapters | 11 tests | 34 assertions | ✅ Pass |
| Scenes | 15 tests | 51 assertions | ✅ Pass |
| Scene Revisions | 12 tests | 36 assertions | ✅ Pass |
| Editor Page | 9 tests | 25 assertions | ✅ Pass |
| **Acts (Phase 2)** | 12 tests | 34 assertions | ✅ Pass |
| **Scene Labels (Phase 2)** | 14 tests | 43 assertions | ✅ Pass |
| **Plan View (Phase 2)** | 11 tests | 66 assertions | ✅ Pass |
| **Total** | **84 tests** | **289 assertions** | ✅ All Pass |

### Key Test Scenarios Covered

**Phase 1:**
- CRUD operations untuk chapters dan scenes
- Auto-save dengan word count calculation
- Scene revision create & restore
- Scene archiving & restoration
- Drag-drop reordering
- Authorization checks (user cannot access other user's data)

**Phase 2:**
- Acts CRUD dengan optional chapter assignment
- Acts deletion sets chapters' act_id to null (tidak delete chapters)
- Scene Labels CRUD dengan color validation
- Labels assignment dengan sync (replace existing)
- Labels filtering (tidak bisa assign labels dari novel lain)
- Plan page access dengan complete data loading
- Scene search by title/summary
- Scene filter by status dan labels
- Scene duplication dengan labels copy
- Archived scenes tidak muncul di plan dan search

---

## Business Rules Enforced

| Rule | Implementation | Test Coverage |
|------|----------------|---------------|
| User can only access own novels | Authorization in all controllers | ✅ Tested |
| Acts are optional | Nullable FK, null on delete | ✅ Tested |
| Labels are per-novel | novel_id FK, validation in controller | ✅ Tested |
| Archived scenes hidden | `whereNull('archived_at')` scope | ✅ Tested |
| Scene labels sync replace | `sync()` method, not `attach()` | ✅ Tested |
| Word count auto-calculated | On content save via model method | ✅ Tested |
| Revisions cascade delete | FK constraint CASCADE DELETE | ✅ Tested |

---

## Performance Considerations

### Database Optimization
- **Indexes:** Composite indexes pada (novel_id, position), (chapter_id, position), (scene_id, created_at)
- **Eager Loading:** `with(['chapters.scenes', 'labels'])` untuk prevent N+1
- **Active Scope:** `whereNull('archived_at')` sebagai query scope
- **Position-based Ordering:** Integer position untuk fast sorting

### Frontend Optimization
- **Debounced Search:** 300ms debounce untuk prevent spam API calls
- **Auto-save Debounce:** 500ms untuk balance UX vs server load
- **Local State:** Drag-drop uses local state, sync on drop end
- **Conditional Rendering:** Components only render when needed (panels, dropdowns)

### Future Optimization Opportunities
- Virtual scrolling untuk plan view jika > 100 scenes
- Pagination untuk scene search results
- IndexedDB caching untuk offline editing
- Web Workers untuk word count calculation

---

## Migration & Rollback Notes

### Migration Order (Critical)
```bash
# Run dalam urutan ini:
php artisan migrate  # Batch 2 akan run 4 migrations:
# 1. create_acts_table
# 2. create_scene_labels_table  
# 3. create_scene_label_table (pivot)
# 4. add_act_id_to_chapters_table (FK to acts)
```

### Safe Rollback
```bash
# Rollback Phase 2 features:
php artisan migrate:rollback --step=4

# Data tetap aman:
# - Chapters tidak kehilangan data (act_id jadi null)
# - Scenes tidak kehilangan data
# - Labels dan pivot table terhapus (expected behavior)
```

---

## Related Documentation

- **API Reference:** [Manuscript Editor API](../04-api-reference/manuscript-editor.md)
- **Testing Guide:** [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md)

---

**Last Updated:** 2026-01-01
