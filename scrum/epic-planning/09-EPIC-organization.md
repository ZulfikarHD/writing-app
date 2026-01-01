# 🗂️ EPIC 9: Organization & Safety

**Epic ID:** EPIC-09  
**Priority:** 🟢 Medium  
**Total Story Points:** ~45  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** EPIC-03 (Story Planning)

---

## 📋 Epic Description

Build organization and data safety features including archiving, revision history, novel covers, deleting/restoring content, and other organizational tools that help writers manage their work safely.

**Reference:** [Novelcrafter Organization Documentation](https://www.novelcrafter.com/help/docs/organization/archiving)

---

## 🎯 Epic Goals

1. Archive and restore scenes/chapters
2. Revision history with comparison
3. Novel cover management
4. Safe deletion workflows
5. Recovery and backup features

---

## 📑 Feature Groups

### FG-09.1: Archiving

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-09.1.1 | Archiving Scenes/Chapters | 🔴 Critical | 5 |
| F-09.1.2 | Archiving and Restoring | 🔴 Critical | 5 |
| F-09.1.3 | Archive Browser | 🟡 High | 5 |

### FG-09.2: Revision History

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-09.2.1 | Revision History | 🔴 Critical | 8 |
| F-09.2.2 | Version Comparison (Diff) | 🟡 High | 5 |
| F-09.2.3 | Restore Previous Version | 🟡 High | 3 |

### FG-09.3: Organization Tools

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-09.3.1 | Adding a Cover to Your Novel | 🟡 High | 3 |
| F-09.3.2 | Deleting Acts, Chapters, and Scenes | 🟡 High | 3 |
| F-09.3.3 | Auto-Recovery & Data Safety | 🔴 Critical | 5 |
| F-09.3.4 | Backup & Restore | 🟡 High | 5 |

---

## 📝 Detailed User Stories

### US-09.1: Archiving Scenes/Chapters
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** archive scenes instead of deleting them,  
**So that** I can recover content later if needed.

#### Acceptance Criteria:
- [ ] Archive action on scenes
- [ ] Archive action on chapters
- [ ] Archived items hidden from main view
- [ ] Archive status indicator
- [ ] Batch archive selection
- [ ] Archive with children (chapter archives all scenes)

**Reference:** [Archiving](https://www.novelcrafter.com/help/docs/organization/archiving)

---

### US-09.2: Archiving and Restoring
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** restore archived content,  
**So that** I can bring back scenes I previously removed.

#### Acceptance Criteria:
- [ ] View archived items list
- [ ] Restore to original position
- [ ] Restore to different chapter
- [ ] Restore archived chapter with scenes
- [ ] Permanently delete archived item
- [ ] Bulk restore selection

**Reference:** [Archiving and Restoring Scenes](https://www.novelcrafter.com/help/docs/organization/archiving-restoring-scenes)

---

### US-09.3: Archive Browser
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** browse my archived content,  
**So that** I can find and restore specific items.

#### Acceptance Criteria:
- [ ] Archive panel/page
- [ ] Filter by type (scene, chapter)
- [ ] Sort by archive date
- [ ] Search archived content
- [ ] Preview archived content
- [ ] Archive statistics

---

### US-09.4: Revision History
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** see revision history for scenes,  
**So that** I can track changes and recover previous versions.

#### Acceptance Criteria:
- [ ] Automatic revision on save (with debounce)
- [ ] Manual "save version" action
- [ ] Version list with timestamps
- [ ] Version descriptions (optional)
- [ ] Storage limit (e.g., last 50 versions)
- [ ] Version size indication

**Reference:** [Revision History](https://www.novelcrafter.com/help/docs/organization/revision-history)

---

### US-09.5: Version Comparison (Diff)
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** compare different versions,  
**So that** I can see what changed between revisions.

#### Acceptance Criteria:
- [ ] Side-by-side comparison
- [ ] Inline diff view
- [ ] Highlight additions/deletions
- [ ] Compare any two versions
- [ ] Word/character count diff
- [ ] Navigate between changes

---

### US-09.6: Restore Previous Version
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** restore a previous version,  
**So that** I can undo unwanted changes.

#### Acceptance Criteria:
- [ ] Restore button on version
- [ ] Confirmation dialog
- [ ] Current version saved before restore
- [ ] Partial restore (copy to clipboard)
- [ ] Restore creates new revision

---

### US-09.7: Adding a Cover to Your Novel
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** add a cover image to my novel,  
**So that** I can visually identify my projects.

#### Acceptance Criteria:
- [ ] Upload cover image
- [ ] Crop/resize tool
- [ ] Cover displayed in dashboard
- [ ] Cover displayed in editor header
- [ ] Default covers (solid colors)
- [ ] Remove cover option

**Reference:** [Adding a Cover to Your Novel](https://www.novelcrafter.com/help/docs/organization/adding-a-cover-to-your-novel)

---

### US-09.8: Deleting Acts, Chapters, and Scenes
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** safely delete content,  
**So that** I can remove items I no longer need.

#### Acceptance Criteria:
- [ ] Delete with confirmation
- [ ] Soft delete (to archive first)
- [ ] Permanent delete option
- [ ] Warning for content with children
- [ ] Undo recent deletes
- [ ] Cascade delete handling

**Reference:** [Deleting Acts, Chapters, and Scenes](https://www.novelcrafter.com/help/docs/organization/deleting-acts-chapters-scenes)

---

### US-09.9: Auto-Recovery & Data Safety
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** have my work auto-saved and recoverable,  
**So that** I don't lose work due to crashes or network issues.

#### Acceptance Criteria:
- [ ] Auto-save to local storage
- [ ] Auto-save to server (debounced)
- [ ] Recovery notification on return
- [ ] Conflict resolution (local vs server)
- [ ] Offline mode support
- [ ] Save status indicator

---

### US-09.10: Backup & Restore
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** backup and restore my entire project,  
**So that** I have a complete safety net.

#### Acceptance Criteria:
- [ ] Export full project backup (ZIP/JSON)
- [ ] Include all content, Codex, settings
- [ ] Restore from backup file
- [ ] Scheduled auto-backup (optional)
- [ ] Backup to cloud storage (optional)

---

## 🗄️ Database Schema

### Table: `scene_revisions` (Enhancement)

```sql
CREATE TABLE scene_revisions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    scene_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NOT NULL,
    word_count INT UNSIGNED DEFAULT 0,
    description VARCHAR(255) NULL,
    is_manual BOOLEAN DEFAULT FALSE, -- User-triggered vs auto
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    INDEX idx_scene_revisions (scene_id, created_at)
);
```

### Updates to existing tables:

```sql
-- Add archived_at to scenes
ALTER TABLE scenes ADD COLUMN archived_at TIMESTAMP NULL;

-- Add archived_at to chapters  
ALTER TABLE chapters ADD COLUMN archived_at TIMESTAMP NULL;

-- Add cover_path to novels
ALTER TABLE novels ADD COLUMN cover_path VARCHAR(500) NULL;
```

### Table: `local_recovery_cache`

```sql
CREATE TABLE local_recovery_cache (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    scene_id BIGINT UNSIGNED NULL,
    content LONGTEXT NOT NULL,
    synced BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Organization/
│       ├── ArchiveService.php
│       ├── RevisionService.php
│       ├── BackupService.php
│       └── RecoveryService.php
├── Http/
│   └── Controllers/
│       ├── ArchiveController.php
│       ├── RevisionController.php
│       └── BackupController.php
├── Jobs/
│   ├── CreateBackup.php
│   └── CleanupOldRevisions.php
```

### Frontend Structure

```
resources/js/
├── Components/
│   └── Organization/
│       ├── ArchiveBrowser.vue
│       ├── RevisionHistory.vue
│       ├── VersionDiff.vue
│       ├── CoverUploader.vue
│       ├── DeleteConfirmation.vue
│       └── RecoveryNotification.vue
├── Composables/
│   └── useAutoSave.ts
│   └── useLocalRecovery.ts
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/scenes/{scene}/archive` | Archive scene |
| POST | `/api/scenes/{scene}/restore` | Restore scene |
| GET | `/api/novels/{novel}/archived` | List archived items |
| DELETE | `/api/scenes/{scene}/permanent` | Permanent delete |
| GET | `/api/scenes/{scene}/revisions` | List revisions |
| GET | `/api/scenes/{scene}/revisions/{rev}` | Get specific revision |
| POST | `/api/scenes/{scene}/revisions/{rev}/restore` | Restore revision |
| GET | `/api/scenes/{scene}/revisions/compare` | Compare revisions |
| POST | `/api/novels/{novel}/cover` | Upload cover |
| DELETE | `/api/novels/{novel}/cover` | Remove cover |
| POST | `/api/novels/{novel}/backup` | Create backup |
| POST | `/api/novels/{novel}/restore` | Restore from backup |

---

## ✅ Definition of Done

- [ ] Archive and restore working for scenes/chapters
- [ ] Archive browser functional
- [ ] Revision history tracking all changes
- [ ] Version comparison with diff view
- [ ] Restore previous versions working
- [ ] Novel covers uploadable
- [ ] Safe delete workflows implemented
- [ ] Auto-recovery working
- [ ] Backup/restore functional
- [ ] Feature tests for all operations

---

## 📎 References

- [Archiving](https://www.novelcrafter.com/help/docs/organization/archiving)
- [Archiving and Restoring Scenes](https://www.novelcrafter.com/help/docs/organization/archiving-restoring-scenes)
- [Revision History](https://www.novelcrafter.com/help/docs/organization/revision-history)
- [Adding a Cover to Your Novel](https://www.novelcrafter.com/help/docs/organization/adding-a-cover-to-your-novel)
- [Deleting Acts, Chapters, and Scenes](https://www.novelcrafter.com/help/docs/organization/deleting-acts-chapters-scenes)
