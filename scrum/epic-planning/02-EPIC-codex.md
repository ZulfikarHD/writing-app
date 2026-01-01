# 📚 EPIC 2: Codex System

**Epic ID:** EPIC-02  
**Priority:** 🔴 Critical  
**Total Story Points:** ~95  
**Est. Duration:** 3-4 Sprints  
**Dependencies:** EPIC-01 (AI Connections)

---

## 📋 Epic Description

Build a comprehensive world-building database (Codex) that serves as the central hub for all story elements including characters, locations, items, lore, organizations, and subplots. The Codex provides AI context awareness and enables intelligent reference throughout the writing process.

**Reference:** [Novelcrafter Codex Documentation](https://www.novelcrafter.com/help/docs/codex/the-codex)

---

## 🎯 Epic Goals

1. Store and organize all story elements in a structured database
2. Multiple entry types with type-specific attributes
3. Aliases system for AI recognition and mentions
4. Relations mapping between entries
5. Progressions system for tracking changes over story timeline
6. Categories and organization tools
7. AI context controls for intelligent inclusion
8. Quick create and bulk entry management

---

## 📑 Feature Groups

### FG-02.1: Core Codex (Foundation)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.1.1 | Codex Entry Types | 🔴 Critical | 5 |
| F-02.1.2 | Create & Edit Codex Entry | 🔴 Critical | 8 |
| F-02.1.3 | Codex Entry Detail View | 🔴 Critical | 5 |
| F-02.1.4 | Codex List View & Navigation | 🔴 Critical | 5 |
| F-02.1.5 | Entry Thumbnails & Images | 🟡 High | 5 |

### FG-02.2: Aliases & Mentions

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.2.1 | Aliases System | 🔴 Critical | 5 |
| F-02.2.2 | Mentions Tracking | 🟡 High | 8 |
| F-02.2.3 | Mention Highlighting in Editor | 🟡 High | 5 |
| F-02.2.4 | Auto-detection of Mentions | 🟢 Medium | 5 |

### FG-02.3: Codex Details & Relations

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.3.1 | Codex Details (Attributes) | 🟡 High | 5 |
| F-02.3.2 | Adding Codex Details | 🟡 High | 3 |
| F-02.3.3 | Codex Details Quick Create | 🟡 High | 3 |
| F-02.3.4 | Codex Relations | 🟡 High | 8 |
| F-02.3.5 | Relation Types & Labels | 🟢 Medium | 3 |

### FG-02.4: Progressions & Changes

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.4.1 | Progressions/Additions | 🟡 High | 8 |
| F-02.4.2 | Progressions on Codex Details | 🟡 High | 5 |
| F-02.4.3 | Timeline-based Progression View | 🟢 Medium | 5 |

### FG-02.5: Organization

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.5.1 | Codex Categories | 🟡 High | 5 |
| F-02.5.2 | Tags & Labels | 🟡 High | 3 |
| F-02.5.3 | Search & Filter | 🔴 Critical | 5 |
| F-02.5.4 | Series Codex (Shared) | 🟢 Low | 8 |

### FG-02.6: AI Integration

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-02.6.1 | AI Context Controls | 🔴 Critical | 5 |
| F-02.6.2 | Description Guidelines | 🟢 Medium | 3 |
| F-02.6.3 | Auto-generate Codex from Chat | 🟢 Medium | 5 |

---

## 📝 Detailed User Stories

### US-02.1: Codex Entry Types
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer building my story world,  
**I want to** create entries of different types,  
**So that** I can organize characters, locations, items, and other story elements separately.

#### Acceptance Criteria:
- [ ] Support entry types: Character, Location, Item, Lore, Organization, Subplot
- [ ] Type-specific icons and color coding
- [ ] Filter Codex by type
- [ ] Type displayed on entry cards
- [ ] Type selection on entry creation
- [ ] Custom types (optional enhancement)

#### Technical Notes:
- Store as ENUM in database
- Consider future extensibility

**Reference:** [Codex Types - Novelcrafter](https://www.novelcrafter.com/help/docs/codex/codex-types)

---

### US-02.2: Create & Edit Codex Entry
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** create and edit Codex entries,  
**So that** I can document my story elements.

#### Acceptance Criteria:
- [ ] Create entry with name and type (required)
- [ ] Rich text description field
- [ ] Entry form validation
- [ ] Edit existing entries
- [ ] Unsaved changes warning
- [ ] Auto-save draft
- [ ] Delete entry with confirmation
- [ ] Soft delete (archivable)

#### Technical Notes:
- Use TipTap for description editor
- Consider reusing editor components

---

### US-02.3: Codex Entry Detail View
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** view entry details in a structured layout,  
**So that** I can quickly access all information about a story element.

#### Acceptance Criteria:
- [ ] Header: Name, type, thumbnail
- [ ] Description section
- [ ] Aliases section
- [ ] Details/attributes section (tabular)
- [ ] Relations section
- [ ] Progressions section
- [ ] Mentions section (where used)
- [ ] Quick edit inline
- [ ] Responsive layout

**Reference:** [Anatomy of a Codex Entry](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)

---

### US-02.4: Codex List View & Navigation
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** browse all Codex entries,  
**So that** I can find and manage my story elements.

#### Acceptance Criteria:
- [ ] Grid view with entry cards
- [ ] List view alternative
- [ ] Sort by name, type, date modified
- [ ] Filter by type
- [ ] Quick search
- [ ] Click to open detail view
- [ ] Sidebar integration in editor

---

### US-02.5: Entry Thumbnails & Images
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** add images to Codex entries,  
**So that** I can visually identify characters and locations.

#### Acceptance Criteria:
- [ ] Upload thumbnail image
- [ ] Image cropping tool
- [ ] Thumbnail displayed on cards and detail view
- [ ] Image gallery for entry (multiple images)
- [ ] Drag & drop upload
- [ ] Image size limits

---

### US-02.6: Aliases System
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** add aliases to Codex entries,  
**So that** AI can recognize characters by nicknames, titles, or alternate names.

#### Acceptance Criteria:
- [ ] Add multiple aliases per entry
- [ ] Aliases searchable
- [ ] Aliases used for mention detection
- [ ] Display aliases in entry detail
- [ ] Case-insensitive matching
- [ ] Remove individual aliases

**Reference:** [Aliases & Mentions](https://www.novelcrafter.com/help/docs/codex/aliases)

---

### US-02.7: Mentions Tracking
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** see where Codex entries appear in my manuscript,  
**So that** I can track character appearances and location usage.

#### Acceptance Criteria:
- [ ] Track mentions across all scenes
- [ ] Count of mentions per entry
- [ ] List scenes where entry appears
- [ ] Click to navigate to scene
- [ ] Mention count visible on cards
- [ ] Real-time updates on text changes

---

### US-02.8: Mention Highlighting in Editor
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** see Codex mentions highlighted in the editor,  
**So that** I can visually identify story elements in my text.

#### Acceptance Criteria:
- [ ] Highlight recognized names/aliases
- [ ] Different colors by type
- [ ] Hover to see entry preview
- [ ] Click to open entry detail
- [ ] Toggle highlighting on/off
- [ ] Performance optimized for long text

---

### US-02.9: Codex Details (Attributes)
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** add structured details to entries,  
**So that** I can track specific attributes like age, height, eye color, etc.

#### Acceptance Criteria:
- [ ] Key-value detail pairs
- [ ] Add/edit/remove details
- [ ] Details displayed in tabular format
- [ ] Type-specific suggested details (e.g., Age for Character)
- [ ] Custom detail keys
- [ ] Reorder details

**Reference:** [Codex Details](https://www.novelcrafter.com/help/docs/codex/codex-details)

---

### US-02.10: Codex Details Quick Create
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** quickly add details without leaving the entry view,  
**So that** I can efficiently document attributes.

#### Acceptance Criteria:
- [ ] Inline add detail form
- [ ] Auto-suggest common detail keys
- [ ] Quick duplicate last detail
- [ ] Keyboard shortcuts (Tab to add)

**Reference:** [Codex Details Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create)

---

### US-02.11: Codex Relations
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** define relationships between Codex entries,  
**So that** I can map character relationships, location hierarchies, etc.

#### Acceptance Criteria:
- [ ] Create relation between two entries
- [ ] Relation label (e.g., "Father of", "Located in")
- [ ] Bidirectional relations (optional)
- [ ] Visual relation graph
- [ ] Filter by relation type
- [ ] Remove relations
- [ ] Relations shown on both entries

**Reference:** [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations)

---

### US-02.12: Progressions/Additions
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** track how Codex entries change over the story timeline,  
**So that** I can document character development and world changes.

#### Acceptance Criteria:
- [ ] Create progression linked to scene/chapter
- [ ] Progression note text
- [ ] Progression timestamp (story time)
- [ ] View progressions chronologically
- [ ] AI context: include progressions up to current point
- [ ] Edit/delete progressions

**Reference:** [Progressions/Additions](https://www.novelcrafter.com/help/docs/codex/progressions-additions)

---

### US-02.13: Progressions on Codex Details
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** track changes to specific details over time,  
**So that** AI knows a character's eye color changed mid-story.

#### Acceptance Criteria:
- [ ] Add progression to specific detail
- [ ] Detail value changes at progression point
- [ ] Timeline view of detail changes
- [ ] AI uses correct value based on current scene

**Reference:** [Progressions on Codex Details](https://www.novelcrafter.com/help/docs/codex/progressions-codex-details)

---

### US-02.14: Codex Categories
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** organize entries into custom categories,  
**So that** I can group related elements beyond types.

#### Acceptance Criteria:
- [ ] Create custom categories
- [ ] Assign entry to category
- [ ] Filter by category
- [ ] Category colors
- [ ] Nested categories (optional)
- [ ] Bulk category assignment

**Reference:** [Codex Categories](https://www.novelcrafter.com/help/docs/codex/codex-categories)

---

### US-02.15: AI Context Controls
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** control which Codex entries are sent to AI,  
**So that** I can manage AI context and token usage.

#### Acceptance Criteria:
- [ ] Context modes: Always, Detected, Manual, Never
- [ ] Per-entry context setting
- [ ] Visual indicator of context mode
- [ ] Preview what will be sent to AI
- [ ] Bulk update context mode

**Reference:** [Description Guidelines](https://www.novelcrafter.com/help/docs/codex/guidelines-for-descriptions)

---

### US-02.16: Series Codex (Shared)
**Priority:** 🟢 Low | **Points:** 8

**As a** writer with a book series,  
**I want to** share Codex entries across multiple novels,  
**So that** I maintain consistency across the series.

#### Acceptance Criteria:
- [ ] Create Series as container
- [ ] Link novels to Series
- [ ] Shared Codex entries across Series
- [ ] Novel-specific overrides
- [ ] Import entries from another novel

**Reference:** [Series Codex](https://www.novelcrafter.com/help/docs/codex/series-codex)

---

## 🗄️ Database Schema

### Table: `codex_entries`

```sql
CREATE TABLE codex_entries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    type ENUM('character', 'location', 'item', 'lore', 'organization', 'subplot') NOT NULL,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    thumbnail_path VARCHAR(500) NULL,
    ai_context_mode ENUM('always', 'detected', 'manual', 'never') DEFAULT 'detected',
    sort_order INT UNSIGNED DEFAULT 0,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    INDEX idx_novel_type (novel_id, type),
    INDEX idx_novel_archived (novel_id, is_archived),
    FULLTEXT idx_search (name, description)
);
```

### Table: `codex_aliases`

```sql
CREATE TABLE codex_aliases (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    alias VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    INDEX idx_entry_alias (codex_entry_id),
    INDEX idx_alias_search (alias)
);
```

### Table: `codex_details`

```sql
CREATE TABLE codex_details (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    INDEX idx_entry_details (codex_entry_id)
);
```

### Table: `codex_relations`

```sql
CREATE TABLE codex_relations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    source_entry_id BIGINT UNSIGNED NOT NULL,
    target_entry_id BIGINT UNSIGNED NOT NULL,
    relation_type VARCHAR(100) NOT NULL, -- "father_of", "located_in", "owns", etc.
    label VARCHAR(255) NULL, -- Display label
    is_bidirectional BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (source_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (target_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    INDEX idx_source (source_entry_id),
    INDEX idx_target (target_entry_id)
);
```

### Table: `codex_progressions`

```sql
CREATE TABLE codex_progressions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    codex_detail_id BIGINT UNSIGNED NULL, -- If progression is for specific detail
    scene_id BIGINT UNSIGNED NULL, -- Reference point in story
    story_timestamp VARCHAR(100) NULL, -- "Chapter 5", "Year 3", etc.
    note TEXT NOT NULL,
    new_value TEXT NULL, -- For detail progressions
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (codex_detail_id) REFERENCES codex_details(id) ON DELETE SET NULL,
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE SET NULL,
    INDEX idx_entry_progressions (codex_entry_id)
);
```

### Table: `codex_categories`

```sql
CREATE TABLE codex_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) NULL, -- Hex color
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES codex_categories(id) ON DELETE SET NULL
);
```

### Table: `codex_entry_categories` (Pivot)

```sql
CREATE TABLE codex_entry_categories (
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (codex_entry_id, category_id),
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES codex_categories(id) ON DELETE CASCADE
);
```

### Table: `codex_mentions`

```sql
CREATE TABLE codex_mentions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    scene_id BIGINT UNSIGNED NOT NULL,
    mention_count INT UNSIGNED DEFAULT 1,
    positions JSON NULL, -- Array of character positions
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    UNIQUE KEY idx_entry_scene (codex_entry_id, scene_id)
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Codex/
│       ├── CodexService.php
│       ├── MentionTracker.php
│       ├── RelationManager.php
│       └── ProgressionManager.php
├── Models/
│   ├── CodexEntry.php
│   ├── CodexAlias.php
│   ├── CodexDetail.php
│   ├── CodexRelation.php
│   ├── CodexProgression.php
│   ├── CodexCategory.php
│   └── CodexMention.php
├── Http/
│   ├── Controllers/
│   │   ├── CodexController.php
│   │   ├── CodexRelationController.php
│   │   └── CodexProgressionController.php
│   └── Requests/
│       ├── StoreCodexEntryRequest.php
│       └── UpdateCodexEntryRequest.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Codex/
│       ├── Index.vue
│       ├── Show.vue
│       └── Create.vue
├── Components/
│   └── Codex/
│       ├── CodexEntryCard.vue
│       ├── CodexEntryForm.vue
│       ├── CodexDetailList.vue
│       ├── CodexDetailForm.vue
│       ├── CodexRelationGraph.vue
│       ├── CodexRelationForm.vue
│       ├── CodexProgressionList.vue
│       ├── CodexProgressionForm.vue
│       ├── CodexCategoryTree.vue
│       ├── AliasManager.vue
│       ├── AIContextControl.vue
│       └── MentionsList.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/codex` | List entries |
| POST | `/api/novels/{novel}/codex` | Create entry |
| GET | `/api/codex/{entry}` | Get entry details |
| PATCH | `/api/codex/{entry}` | Update entry |
| DELETE | `/api/codex/{entry}` | Delete entry |
| POST | `/api/codex/{entry}/aliases` | Add alias |
| DELETE | `/api/codex/{entry}/aliases/{alias}` | Remove alias |
| GET | `/api/codex/{entry}/details` | List details |
| POST | `/api/codex/{entry}/details` | Add detail |
| GET | `/api/codex/{entry}/relations` | List relations |
| POST | `/api/codex/{entry}/relations` | Add relation |
| GET | `/api/codex/{entry}/progressions` | List progressions |
| POST | `/api/codex/{entry}/progressions` | Add progression |
| GET | `/api/codex/{entry}/mentions` | List mentions |
| GET | `/api/novels/{novel}/categories` | List categories |
| POST | `/api/novels/{novel}/categories` | Create category |

---

## ✅ Definition of Done

- [ ] All entry types implemented
- [ ] CRUD operations complete and tested
- [ ] Aliases system working with mention detection
- [ ] Relations graph functional
- [ ] Progressions tracking story timeline
- [ ] Categories and organization tools complete
- [ ] AI context controls respected in AI calls
- [ ] Search and filter performant
- [ ] Mobile-responsive UI
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests for all endpoints
- [ ] Documentation updated

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Mention detection performance | High | Background processing, caching |
| Large Codex scalability | Medium | Virtual scrolling, pagination |
| Complex relations visualization | Medium | Use D3.js or similar library |
| Progression complexity | Medium | Clear UI with timeline view |

---

## 📎 References

- [The Codex](https://www.novelcrafter.com/help/docs/codex/the-codex)
- [Anatomy of a Codex Entry](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)
- [Codex Types](https://www.novelcrafter.com/help/docs/codex/codex-types)
- [Aliases & Mentions](https://www.novelcrafter.com/help/docs/codex/aliases)
- [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations)
- [Progressions/Additions](https://www.novelcrafter.com/help/docs/codex/progressions-additions)
