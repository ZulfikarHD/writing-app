# 🖊️ Sprint 02 - Manuscript Editor

## Overview

Sprint Manuscript Editor untuk NovelWrite, yaitu: implementasi text editor berbasis TipTap dengan fitur auto-save, revision history, chapter/scene management, dan real-time word count tracking.

---

## Sprint Info

| Property | Value |
|----------|-------|
| **Sprint Name** | Manuscript Editor |
| **Status** | ✅ Complete |
| **Start Date** | 2025-12-31 |
| **End Date** | 2025-12-31 |
| **Total Story Points** | 34 |

---

## User Stories Completed

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

---

## Technical Implementation

### Backend Files

| Category | Files |
|----------|-------|
| **Controllers** | `EditorController.php`, `ChapterController.php`, `SceneController.php` |
| **Models** | `Chapter.php`, `Scene.php`, `SceneRevision.php` |
| **Form Requests** | `StoreChapterRequest.php`, `UpdateSceneContentRequest.php` |
| **Migrations** | `create_chapters_table.php`, `create_scenes_table.php`, `create_scene_revisions_table.php` |
| **Factories** | `ChapterFactory.php`, `SceneFactory.php`, `SceneRevisionFactory.php` |

### Frontend Files

| Category | Files |
|----------|-------|
| **Pages** | `Editor/Index.vue` |
| **Editor Components** | `TipTapEditor.vue`, `EditorSidebar.vue`, `EditorToolbar.vue` |
| **Composables** | `useAutoSave.ts`, `useTheme.ts` |

---

## Routes Summary

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/novels/{novel}/write` | editor.show | Open editor for novel |
| GET | `/novels/{novel}/write/{scene}` | editor.scene | Open editor with specific scene |
| GET | `/api/novels/{novel}/chapters` | chapters.index | List chapters with scenes |
| POST | `/api/novels/{novel}/chapters` | chapters.store | Create chapter |
| PATCH | `/api/chapters/{chapter}` | chapters.update | Update chapter |
| DELETE | `/api/chapters/{chapter}` | chapters.destroy | Delete chapter |
| POST | `/api/novels/{novel}/chapters/reorder` | chapters.reorder | Reorder chapters |
| POST | `/api/chapters/{chapter}/scenes` | scenes.store | Create scene |
| GET | `/api/scenes/{scene}` | scenes.show | Get scene content |
| PATCH | `/api/scenes/{scene}` | scenes.update | Update scene metadata |
| PATCH | `/api/scenes/{scene}/content` | scenes.content | Auto-save content |
| DELETE | `/api/scenes/{scene}` | scenes.destroy | Delete scene |
| POST | `/api/scenes/{scene}/archive` | scenes.archive | Archive scene |
| POST | `/api/scenes/{scene}/restore` | scenes.restore | Restore archived scene |
| POST | `/api/chapters/{chapter}/scenes/reorder` | scenes.reorder | Reorder scenes |
| GET | `/api/scenes/{scene}/revisions` | scenes.revisions | Get revision history |
| POST | `/api/scenes/{scene}/revisions` | scenes.revisions.create | Create manual revision |
| POST | `/api/scenes/{scene}/revisions/{revisionId}/restore` | scenes.revisions.restore | Restore from revision |

---

## Database Schema

### chapters

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| novel_id | bigint | FK → novels.id, CASCADE DELETE |
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

---

## Key Design Decisions

### 1. TipTap Editor dengan StarterKit
- Extensible rich text editor
- Built-in history untuk undo/redo
- JSON content format untuk database storage
- Customizable styling dengan prose classes

### 2. Auto-Save dengan Debounce
- 500ms debounce untuk mencegah spam requests
- Save status indicator (saved, saving, unsaved, error)
- Manual save dengan Ctrl+S override debounce
- Word count update setiap save

### 3. Scene-based Content Structure
- Setiap scene memiliki content JSON sendiri
- Chapter sebagai organisasi/grouping
- Revision history per scene untuk recovery
- Archive soft-delete untuk scene yang tidak diperlukan

### 4. Optimistic UI Updates
- Scene switch tanpa reload halaman
- Sidebar state tetap saat navigasi
- Word count update real-time dari client + server sync

---

## Related Documentation

- **API Reference:** [Manuscript Editor API](../04-api-reference/manuscript-editor.md)
- **Testing Guide:** [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md)

---

**Last Updated:** 2025-12-31
