# 🗺️ EPIC 3: Story Planning

**Epic ID:** EPIC-03  
**Priority:** 🔴 Critical  
**Total Story Points:** ~110  
**Est. Duration:** 4-5 Sprints  
**Dependencies:** EPIC-02 (Codex System)

---

## 📋 Epic Description

Build a comprehensive story planning interface that enables writers to visualize, organize, and plan their novels using multiple views (Grid, Matrix, Outline). This includes the Plan interface, scene cards, create from outline, and all planning visualization tools.

**Reference:** [Novelcrafter Plan Documentation](https://www.novelcrafter.com/help/docs/plan/the-plan-interface)

---

## 🎯 Epic Goals

1. Multiple planning views for different workflows (Grid, Matrix, Outline)
2. Visual scene cards with customizable appearance
3. Drag & drop scene reordering
4. Matrix view for tracking story elements across scenes
5. Create structure from outline/templates
6. Scene labels and status tracking
7. Actions menu for bulk operations

---

## 📑 Feature Groups

### FG-03.1: Plan Interface Core

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.1.1 | Plan Interface Layout | 🔴 Critical | 5 |
| F-03.1.2 | View Switcher (Grid/Matrix/Outline) | 🔴 Critical | 3 |
| F-03.1.3 | Search & Filter (with Codex) | 🔴 Critical | 5 |
| F-03.1.4 | Actions Menu (Scene/Chapter/Act/Novel) | 🟡 High | 8 |
| F-03.1.5 | Import from Plan Interface | 🟡 High | 5 |

### FG-03.2: Plan Views

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.2.1 | Grid View (with Codex filtering) | 🔴 Critical | 13 |
| F-03.2.2 | Matrix View (POV/Label/Codex/Custom modes) | 🔴 Critical | 13 |
| F-03.2.3 | Outline View (with inline editing) | 🟡 High | 8 |
| F-03.2.4 | Timeline View (Optional) | 🟢 Low | 5 |

### FG-03.3: Scene Cards

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.3.1 | Scene Card Component (with Context Menu) | 🔴 Critical | 8 |
| F-03.3.2 | Scene Card Appearance Customization | 🟡 High | 5 |
| F-03.3.3 | Drag & Drop Reordering | 🔴 Critical | 5 |
| F-03.3.4 | Scene Labels & Status | 🟡 High | 5 |

### FG-03.4: Structure Creation

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.4.1 | Add Acts/Chapters/Scenes | 🔴 Critical | 5 |
| F-03.4.2 | Create from Outline (with Preview) | 🟡 High | 8 |
| F-03.4.3 | Story Templates (8 built-in) | 🟢 Medium | 5 |
| F-03.4.4 | Import Outline from Text | 🟢 Medium | 5 |

### FG-03.5: Scene Management

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.5.1 | Scene POV Management | 🟡 High | 5 |
| F-03.5.2 | Scene Subtitles | 🟢 Medium | 2 |
| F-03.5.3 | Scene History & Revisions | 🟡 High | 5 |
| F-03.5.4 | Archived Scenes Management | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-03.1: Plan Interface Layout
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** access a dedicated Plan interface,  
**So that** I can visualize and plan my story structure.

#### Acceptance Criteria:
- [ ] Plan accessible from main navigation
- [ ] Header with view switcher
- [ ] Search/filter bar
- [ ] Appearance settings button
- [ ] Add acts button
- [ ] Create from outline button
- [ ] Import button
- [ ] Actions menu
- [ ] Responsive design (works on mobile)

**Reference:** [The Plan Interface](https://www.novelcrafter.com/help/docs/plan/the-plan-interface)

---

### US-03.2: View Switcher
**Priority:** 🔴 Critical | **Points:** 3

**As a** writer,  
**I want to** switch between Grid, Matrix, and Outline views,  
**So that** I can use the visualization that fits my current task.

#### Acceptance Criteria:
- [ ] Toggle buttons for Grid, Matrix, Outline
- [ ] View state persisted per novel
- [ ] Smooth transition between views
- [ ] Keyboard shortcut for view switching
- [ ] Visual indicator of active view

**Reference:** [Plan Views](https://www.novelcrafter.com/help/docs/plan/plan-views)

---

### US-03.3: Grid View
**Priority:** 🔴 Critical | **Points:** 13

**As a** writer,  
**I want to** see all my scenes as cards in a grid layout with codex filtering,  
**So that** I can get an overview of my entire story and track where elements appear.

#### Acceptance Criteria:

**Core Layout:**
- [ ] Scene cards displayed in grid
- [ ] Cards grouped by Chapter within Acts
- [ ] Act headers with collapse/expand
- [ ] Chapter headers with collapse/expand
- [ ] Responsive columns (4 desktop, 2 tablet, 1 mobile)
- [ ] Click card to navigate to editor
- [ ] Word count per scene on card
- [ ] Word count per chapter/act in headers

**Codex Filtering:**
- [ ] Filter bar at top with codex entry pills
- [ ] Click to add codex entry filters (Characters, Locations, etc.)
- [ ] Multiple entries can be selected simultaneously
- [ ] "X entries left to add" indicator
- [ ] Clear filter button per entry
- [ ] Show only scenes containing selected entries
- [ ] Scenes not matching filters are dimmed/hidden

**Layout Options:**
- [ ] Swap Layout button (switch between horizontal/vertical axis)
- [ ] Vertical: chapters as columns, scenes flow down
- [ ] Horizontal: chapters as rows, scenes flow across

**Drag & Drop:**
- [ ] Drag & drop reorder within chapter
- [ ] Drag & drop move between chapters
- [ ] Visual drop indicator showing insertion point
- [ ] Drag chapter to reorder chapters
- [ ] Drag act to reorder acts

**Visual Features:**
- [ ] Visual indicators for status/labels (colored badges)
- [ ] Auto-detected codex references shown as pills on cards
- [ ] New Scene button in each chapter
- [ ] New Chapter button in each act

**Reference:** [Plan Views - Grid](https://www.novelcrafter.com/help/docs/plan/plan-views#grid)

---

### US-03.4: Matrix View
**Priority:** 🔴 Critical | **Points:** 13

**As a** writer,  
**I want to** see a matrix of scenes vs story elements with multiple display modes,  
**So that** I can track characters, locations, POV, labels, and subplots across my story.

#### Acceptance Criteria:

**Core Layout:**
- [ ] Scenes as rows grouped by Act/Chapter
- [ ] Horizontal scroll for many columns
- [ ] Sticky header row with column titles
- [ ] Sticky first column (scene names/info)
- [ ] Column grouping by type
- [ ] Click scene to navigate to editor
- [ ] Swap layout button (horizontal/vertical orientation)

**Display Mode Switcher ("Show" dropdown):**
- [ ] **Codex Mode** (default): Shows codex entries as columns
  - [ ] Filter by codex type (Characters, Locations, Items, etc.)
  - [ ] Auto-detect mentions in scene summary/content
  - [ ] Cell shows presence indicator (dot, checkmark, or count)
  - [ ] Hover cell for details of mentions
  - [ ] Manually assign codex entries to scenes
- [ ] **POV Mode**: Shows POV characters as columns
  - [ ] Each character column shows their POV type (1st Person, 3rd Person, etc.)
  - [ ] Click to set POV for scene in one click
  - [ ] "Set POV" button in empty cells
  - [ ] Mass POV changes across multiple scenes
- [ ] **Label/Status Mode**: Shows status labels as columns
  - [ ] Columns: Idea, Draft, Edited, Finalized (or custom labels)
  - [ ] Click to assign label to scene
  - [ ] Color-coded columns matching label colors
  - [ ] Visual indicator showing current label
- [ ] **Subplot Mode**: Shows subplots as columns
  - [ ] Track which scenes contain subplot elements
  - [ ] See subplot progression across story
- [ ] **Custom Mode**: Manually select specific entries
  - [ ] Pick specific codex entries to track
  - [ ] Compare hero vs villain appearances
  - [ ] Track specific relationships across scenes

**Reference:** [Planning with the Matrix](https://www.novelcrafter.com/help/docs/plan/planning-with-the-matrix)

---

### US-03.5: Outline View
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** see a linear outline of all scenes with inline editing,  
**So that** I can quickly review the story flow and edit summaries directly.

#### Acceptance Criteria:

**Core Layout:**
- [ ] Hierarchical list: Acts → Chapters → Scenes
- [ ] Collapsible sections (expand/collapse acts and chapters)
- [ ] Scene title and summary preview
- [ ] Word count per scene and chapter
- [ ] Total word count per act
- [ ] Click scene to navigate to editor

**Inline Editing:**
- [ ] Click to edit scene summary directly inline
- [ ] Click to edit chapter summary inline
- [ ] Auto-save on blur or Ctrl+Enter
- [ ] Cancel editing with Escape key
- [ ] Visual indicator when editing

**Structure Management:**
- [ ] "Add Scene" button under each chapter
- [ ] "New Chapter" button for adding chapters
- [ ] Drag & drop reordering with visual indicators
- [ ] Drag handle visible on hover

**Additional Features:**
- [ ] Print-friendly mode
- [ ] Copy outline to clipboard
- [ ] Labels/status badges visible on scenes

**Reference:** [Plan Views - Outline](https://www.novelcrafter.com/help/docs/plan/plan-views#outline)

---

### US-03.6: Search & Filter (Plan)
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** search and filter scenes in Plan view,  
**So that** I can find specific scenes quickly.

#### Acceptance Criteria:
- [ ] Search by scene title
- [ ] Search by summary content
- [ ] Search by Codex mentions (names/aliases)
- [ ] Filter by label/status
- [ ] Filter by Chapter/Act
- [ ] Filter by POV character
- [ ] Clear filters button
- [ ] Result count displayed
- [ ] Keyboard shortcut to focus search

---

### US-03.7: Scene Card Component
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** see informative scene cards with rich context menus,  
**So that** I can understand scene content at a glance and perform quick actions.

#### Acceptance Criteria:

**Card Display:**
- [ ] Scene number and title
- [ ] Summary preview (truncated based on card height setting)
- [ ] Word count
- [ ] POV character indicator (if set)
- [ ] Status/label badge (color-coded)
- [ ] Auto-detected codex references as pills
- [ ] Edit button (pencil icon) for quick summary edit
- [ ] Actions menu button (three dots)

**Interaction:**
- [ ] Hover for more info tooltip
- [ ] Click card to navigate to editor
- [ ] Selection state (multi-select with Ctrl/Cmd+click)
- [ ] Visual feedback on drag (ghost card)
- [ ] Drag handle visible on hover

**Context Menu Actions (right-click or actions button):**

*Basic Actions:*
- [ ] Set custom POV → Opens POV selector
- [ ] Add Subtitle → Inline text input
- [ ] Duplicate Scene → Creates copy in same chapter

*AI Section:*
- [ ] Exclude from AI Context → Toggle to include/exclude scene
- [ ] Summarize Scene → Submenu with prompt options
- [ ] Detect Characters → Submenu to detect codex entries
- [ ] Chat with Scene → Submenu with chat options

*History Section:*
- [ ] Scene Summary → View/restore prior summary versions
- [ ] Scene Contents → View/restore prior content versions

*Export Section:*
- [ ] Copy Scene Prose → Copy prose to clipboard
- [ ] Export Scene → Export as file (docx, md, txt)
- [ ] Archive Scene → Move to archived scenes

**Quick Actions (visible on card):**
- [ ] "+ Codex" button to assign codex entry
- [ ] "+ Label" button to assign label

---

### US-03.8: Scene Card Appearance Customization
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** customize scene card appearance through a View Settings panel,  
**So that** I can see the information most relevant to me.

#### Acceptance Criteria:

**View Settings Panel (accessible via "View" button):**
- [ ] Show auto-detected references toggle
  - [ ] When enabled, show codex mentions found in scene summary/content
  - [ ] Auto-detection works for names and aliases
- [ ] Grid axis selection
  - [ ] Vertical (chapters as columns, default)
  - [ ] Horizontal (chapters as rows)
- [ ] Card width options
  - [ ] Small (more cards per row)
  - [ ] Medium (default)
  - [ ] Large (fewer cards, more content)
- [ ] Card height options
  - [ ] Full (show all content)
  - [ ] Small (compact, title + minimal info)
  - [ ] Medium (default)
  - [ ] Large (more summary preview)

**Additional Settings:**
- [ ] Toggle visible info: title, summary, word count, POV, label
- [ ] Color coding by label
- [ ] Settings persisted per novel (saved to user preferences)
- [ ] Settings apply to both Grid and Matrix views

**Reference:** [Changing Scene Card Appearance](https://www.novelcrafter.com/help/docs/plan/changing-plan-appearance)

---

### US-03.9: Drag & Drop Reordering
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** drag and drop scenes to reorder them,  
**So that** I can easily reorganize my story structure.

#### Acceptance Criteria:
- [ ] Drag scene within chapter
- [ ] Drag scene to different chapter
- [ ] Visual drop indicator
- [ ] Undo reorder action
- [ ] Multi-select and bulk move
- [ ] Keyboard accessibility
- [ ] Mobile touch support

---

### US-03.10: Scene Labels & Status
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** assign labels/status to scenes,  
**So that** I can track writing progress.

#### Acceptance Criteria:
- [ ] Predefined labels: Draft, Revision, Final, Needs Work
- [ ] Custom labels creatable
- [ ] Color coding per label
- [ ] Assign label from Plan view (quick action)
- [ ] Assign label from Editor
- [ ] Filter by label
- [ ] Label statistics (e.g., "5 Draft, 10 Final")
- [ ] Multi-select label assignment

**Reference:** [Labeling Scenes](https://www.novelcrafter.com/help/docs/organization/labeling-scenes)

---

### US-03.11: Add Acts/Chapters/Scenes
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** add new acts, chapters, and scenes from Plan view,  
**So that** I can build my story structure.

#### Acceptance Criteria:
- [ ] "Add Act" button creates new act
- [ ] "Add Chapter" within act
- [ ] "Add Scene" within chapter
- [ ] Inline naming on creation
- [ ] Position selection (add after specific item)
- [ ] Duplicate scene/chapter option
- [ ] Keyboard shortcuts

---

### US-03.12: Create from Outline
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** create story structure from an outline,  
**So that** I can quickly set up my novel from existing plans.

#### Acceptance Criteria:
- [ ] Paste text outline in dialog
- [ ] Auto-detect structure from formatting (indentation, numbering)
- [ ] Preview parsed structure before confirming
- [ ] Map headings to Acts/Chapters/Scenes
- [ ] Support common templates (3-Act, 5-Act, Hero's Journey)
- [ ] Create as summaries only or full scenes option
- [ ] Edit structure in preview before creating

**Reference:** [Create from Outline](https://www.novelcrafter.com/help/docs/plan/create-from-outline)

---

### US-03.13: Actions Menu (Plan)
**Priority:** 🟡 High | **Points:** 8

**As a** writer,  
**I want to** perform various actions on scenes, chapters, acts, and the whole novel,  
**So that** I can efficiently manage my story structure.

#### Acceptance Criteria:

**Act Actions Menu:**
- [ ] Disable numeration (exclude from automatic numbering)
- [ ] Copy all beats (copy beat box text within act)
- [ ] Copy all prose (copy prose and sections within act)
- [ ] Copy outline/summaries (copy scene summaries)
- [ ] Export act (export prose/sections as file)
- [ ] Delete act (if no populated scenes)
- [ ] Split act (divide into multiple acts)

**Chapter Actions Menu:**
- [ ] Disable numeration (exclude from automatic numbering)
- [ ] Copy all beats
- [ ] Copy all prose
- [ ] Copy outline/summaries
- [ ] Export chapter
- [ ] Delete chapter (if no populated scenes)
- [ ] Merge chapters (combine with adjacent chapter)
- [ ] Rename chapter inline

**Scene Bulk Actions:**
- [ ] Select multiple scenes (Ctrl/Cmd + click)
- [ ] Bulk delete selected
- [ ] Bulk label assignment
- [ ] Bulk move to chapter
- [ ] Export selected scenes
- [ ] Archive selected scenes

**Whole Novel Actions Menu:**
- [ ] Copy all prose to clipboard
- [ ] Copy all outlines to clipboard
- [ ] Delete empty scenes (remove unpopulated scenes)
- [ ] Export entire novel

**Reference:** [Actions Menu (Plan)](https://www.novelcrafter.com/help/docs/plan/plan-actions-menu)

---

### US-03.14: Story Templates
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer,  
**I want to** start from story structure templates,  
**So that** I can use proven narrative frameworks.

#### Acceptance Criteria:
- [ ] Template library accessible from Create from Outline dialog
- [ ] Built-in templates:
  - [ ] 3 Act Structure
  - [ ] Save the Cat
  - [ ] Hero's Journey
  - [ ] Freytag's Pyramid
  - [ ] Dan Harmon's Story Circle
  - [ ] Fichtean Curve
  - [ ] Derek Murphy's 24 Chapters
  - [ ] Story Clock
- [ ] Template preview before applying
- [ ] Apply template creates acts/chapters with descriptions
- [ ] Customize template text before applying
- [ ] User can save custom templates for reuse

---

### US-03.15: Import from Plan Interface
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** import existing content directly from the Plan interface,  
**So that** I can bring in pre-written novels or scene summaries.

#### Acceptance Criteria:
- [ ] Import button in Plan header
- [ ] Import pre-written novel (Word, Markdown)
- [ ] Import scene summaries (plain text)
- [ ] Import from partial novel/chapter
- [ ] Structure detection during import
- [ ] Preview before confirming import
- [ ] Option to append or replace content

**Reference:** [The Plan Interface](https://www.novelcrafter.com/help/docs/plan/the-plan-interface)

---

### US-03.16: Scene POV Management
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** set and manage POV (Point of View) for each scene,  
**So that** I can track perspective changes throughout my story.

#### Acceptance Criteria:
- [ ] Set POV from scene card context menu
- [ ] Set POV from scene editor sidebar
- [ ] Quick POV assignment in Matrix POV mode
- [ ] POV indicator on scene cards
- [ ] POV types: 1st Person, 2nd Person, 3rd Person Limited, 3rd Person Omniscient
- [ ] Associate POV with character from codex
- [ ] Filter scenes by POV character
- [ ] Mass POV changes via Matrix view

---

### US-03.17: Scene Subtitles
**Priority:** 🟢 Medium | **Points:** 2

**As a** writer,  
**I want to** add subtitles to scenes,  
**So that** I can indicate POV changes, time skips, or other relevant info.

#### Acceptance Criteria:
- [ ] Add subtitle from scene context menu
- [ ] Subtitle appears below scene title
- [ ] Edit subtitle inline
- [ ] Optional: auto-generate from POV character
- [ ] Visible in Grid and Outline views

---

### US-03.18: Scene History & Revisions
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** view and restore previous versions of scenes,  
**So that** I never lose valuable content or can undo changes.

#### Acceptance Criteria:
- [ ] Scene Summary history
  - [ ] View prior versions with timestamps
  - [ ] Restore previous summary version
  - [ ] Compare versions side-by-side
- [ ] Scene Contents history
  - [ ] View prior prose versions
  - [ ] Restore previous content version
  - [ ] Track major changes
- [ ] Access history from scene context menu
- [ ] History panel with timeline view

---

### US-03.19: Archived Scenes Management
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** archive and restore scenes,  
**So that** I can remove scenes without permanently deleting them.

#### Acceptance Criteria:
- [ ] Archive scene from context menu
- [ ] Archived scenes folder icon at bottom of each chapter
- [ ] Click folder to view archived scenes
- [ ] Restore archived scene to any chapter
- [ ] Permanently delete archived scenes
- [ ] Search within archived scenes

**Reference:** [Archiving and Restoring Scenes](https://www.novelcrafter.com/help/docs/organization/archiving-restoring-scenes)

---

## 🗄️ Database Schema

### Table: `scene_labels`

```sql
CREATE TABLE scene_labels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL, -- Hex color
    preset_type VARCHAR(50) NULL, -- 'status' or 'temporal' for preset labels
    is_default BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    INDEX idx_novel_labels (novel_id)
);
```

### Table: `story_templates`

```sql
CREATE TABLE story_templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL, -- NULL for system templates
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    structure JSON NOT NULL, -- Nested acts/chapters/scenes
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table: `archived_scenes`

```sql
CREATE TABLE archived_scenes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    original_chapter_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT NULL,
    content LONGTEXT NULL,
    word_count INT UNSIGNED DEFAULT 0,
    metadata JSON NULL, -- POV, label, codex refs, etc.
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    INDEX idx_novel_archived (novel_id)
);
```

### Table: `scene_codex_mentions` (for tracking manual assignments)

```sql
CREATE TABLE scene_codex_mentions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    scene_id BIGINT UNSIGNED NOT NULL,
    codex_entry_id BIGINT UNSIGNED NOT NULL,
    is_manual BOOLEAN DEFAULT FALSE, -- TRUE if manually assigned, FALSE if auto-detected
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE,
    UNIQUE KEY unique_scene_codex (scene_id, codex_entry_id)
);
```

### Table: `novel_plan_settings` (for persisting view preferences)

```sql
CREATE TABLE novel_plan_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    current_view VARCHAR(20) DEFAULT 'grid', -- grid, matrix, outline
    matrix_mode VARCHAR(20) DEFAULT 'codex', -- codex, pov, label, custom, subplot
    grid_axis VARCHAR(20) DEFAULT 'vertical', -- vertical, horizontal
    card_width VARCHAR(20) DEFAULT 'medium', -- small, medium, large
    card_height VARCHAR(20) DEFAULT 'medium', -- full, small, medium, large
    show_auto_references BOOLEAN DEFAULT TRUE,
    custom_matrix_entries JSON NULL, -- Array of codex entry IDs for custom mode
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_novel_user_settings (novel_id, user_id)
);
```

### Updates to `scenes` table:

```sql
ALTER TABLE scenes ADD COLUMN label_id BIGINT UNSIGNED NULL;
ALTER TABLE scenes ADD COLUMN pov_character_id BIGINT UNSIGNED NULL;
ALTER TABLE scenes ADD COLUMN pov_type VARCHAR(30) NULL; -- '1st_person', '2nd_person', '3rd_limited', '3rd_omniscient'
ALTER TABLE scenes ADD COLUMN subtitle VARCHAR(255) NULL;
ALTER TABLE scenes ADD COLUMN exclude_from_ai BOOLEAN DEFAULT FALSE;

ALTER TABLE scenes ADD FOREIGN KEY (label_id) REFERENCES scene_labels(id) ON DELETE SET NULL;
ALTER TABLE scenes ADD FOREIGN KEY (pov_character_id) REFERENCES codex_entries(id) ON DELETE SET NULL;
```

### Updates to `acts` and `chapters` tables:

```sql
ALTER TABLE acts ADD COLUMN disable_numeration BOOLEAN DEFAULT FALSE;
ALTER TABLE chapters ADD COLUMN disable_numeration BOOLEAN DEFAULT FALSE;
ALTER TABLE chapters ADD COLUMN summary TEXT NULL;
```

---

## 🏗️ Technical Architecture

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Workspace/
│       └── Index.vue (Plan embedded as panel)
├── Components/
│   └── Plan/
│       ├── PlanHeader.vue          # Header with view switcher, search, actions
│       ├── ViewSwitcher.vue        # Grid/Matrix/Outline toggle
│       ├── ViewSettings.vue        # Appearance settings dropdown
│       ├── GridView.vue            # Grid layout with codex filtering
│       ├── MatrixView.vue          # Matrix with mode switcher
│       ├── MatrixModeSwitcher.vue  # POV/Label/Codex/Custom mode selector
│       ├── OutlineView.vue         # Outline with inline editing
│       ├── TimelineView.vue        # (Optional) Timeline view
│       ├── SceneCard.vue           # Scene card with context menu
│       ├── SceneCardContextMenu.vue # Right-click/actions menu
│       ├── SceneCardSettings.vue   # Card appearance options
│       ├── ChapterGroup.vue        # Chapter container with actions
│       ├── ActGroup.vue            # Act container with actions
│       ├── ActionsMenu.vue         # Act/Chapter/Novel actions
│       ├── LabelBadge.vue          # Label display component
│       ├── LabelManager.vue        # Create/edit labels
│       ├── LabelSelector.vue       # Dropdown to assign labels
│       ├── POVSelector.vue         # Dropdown to set POV
│       ├── CodexFilterBar.vue      # Filter bar for codex entries
│       ├── CreateFromOutline.vue   # Outline creation modal
│       ├── OutlinePreview.vue      # Preview parsed outline
│       ├── TemplateSelector.vue    # Story template picker
│       ├── ImportModal.vue         # Import from Plan modal
│       ├── PlanSearch.vue          # Search input with filters
│       ├── PlanFilters.vue         # Filter dropdowns
│       ├── SceneHistory.vue        # History viewer for scenes
│       ├── ArchivedScenes.vue      # Archived scenes list
│       └── DragDropProvider.vue    # Drag & drop context provider
├── Composables/
│   └── Plan/
│       ├── usePlanView.ts          # View state management
│       ├── useDragDrop.ts          # Drag & drop logic
│       ├── useMatrixMode.ts        # Matrix mode switching
│       └── useSceneActions.ts      # Scene action handlers
```

### Backend Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── PlanController.php      # Main plan endpoints
│       ├── SceneLabelController.php # Label CRUD
│       └── SceneHistoryController.php # History endpoints
├── Models/
│   ├── SceneLabel.php
│   └── ArchivedScene.php
├── Services/
│   └── Plan/
│       ├── OutlineParser.php       # Parse text outlines
│       ├── MatrixDataBuilder.php   # Build matrix data
│       ├── TemplateService.php     # Story template handling
│       └── SceneArchiver.php       # Archive/restore logic
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/plan` | Get plan data (scenes with metadata) |
| GET | `/api/novels/{novel}/plan/matrix` | Get matrix data (with mode param) |
| GET | `/api/novels/{novel}/plan/matrix/pov` | Get matrix POV data |
| GET | `/api/novels/{novel}/plan/matrix/labels` | Get matrix labels data |
| GET | `/api/novels/{novel}/plan/matrix/codex` | Get matrix codex data |
| POST | `/api/novels/{novel}/plan/reorder` | Reorder scenes/chapters/acts |
| GET | `/api/novels/{novel}/labels` | List labels |
| POST | `/api/novels/{novel}/labels` | Create label |
| PATCH | `/api/novels/{novel}/labels/{label}` | Update label |
| DELETE | `/api/novels/{novel}/labels/{label}` | Delete label |
| PATCH | `/api/scenes/{scene}/label` | Assign label to scene |
| PATCH | `/api/scenes/{scene}/pov` | Set scene POV |
| PATCH | `/api/scenes/{scene}/subtitle` | Set scene subtitle |
| POST | `/api/scenes/{scene}/duplicate` | Duplicate scene |
| POST | `/api/scenes/{scene}/archive` | Archive scene |
| POST | `/api/scenes/{scene}/restore` | Restore archived scene |
| GET | `/api/scenes/{scene}/history/summary` | Get summary revision history |
| GET | `/api/scenes/{scene}/history/content` | Get content revision history |
| POST | `/api/scenes/{scene}/history/restore` | Restore from history |
| POST | `/api/novels/{novel}/plan/bulk-action` | Bulk operations |
| POST | `/api/novels/{novel}/plan/from-outline` | Create from outline |
| POST | `/api/novels/{novel}/plan/import` | Import content from Plan |
| GET | `/api/templates` | List story templates |
| POST | `/api/templates` | Create custom template |
| GET | `/api/novels/{novel}/archived-scenes` | List archived scenes |
| DELETE | `/api/novels/{novel}/empty-scenes` | Delete empty scenes |
| POST | `/api/acts/{act}/copy-prose` | Copy all prose in act |
| POST | `/api/acts/{act}/copy-outlines` | Copy all outlines in act |
| POST | `/api/chapters/{chapter}/merge` | Merge with adjacent chapter |
| PATCH | `/api/acts/{act}/numeration` | Toggle act numeration |
| PATCH | `/api/chapters/{chapter}/numeration` | Toggle chapter numeration |

---

## ✅ Definition of Done

**Core Views:**
- [ ] Grid view with codex filtering and swap layout
- [ ] Matrix view with all modes (POV, Label, Codex, Custom, Subplot)
- [ ] Outline view with inline editing

**Scene Cards:**
- [ ] Scene card context menu with all actions
- [ ] View settings panel working
- [ ] Auto-detected codex references displayed
- [ ] Labels/status visible and assignable

**Drag & Drop:**
- [ ] Drag & drop reordering scenes within chapter
- [ ] Drag & drop moving scenes between chapters
- [ ] Drag & drop reordering chapters and acts
- [ ] Visual feedback during drag operations

**Matrix Features:**
- [ ] Matrix POV mode with quick assignment
- [ ] Matrix Label mode with click-to-assign
- [ ] Matrix Codex mode tracking mentions
- [ ] Matrix Custom mode with entry selection
- [ ] Matrix Subplot tracking

**Structure Creation:**
- [ ] Create from outline with preview
- [ ] All 8 story templates available
- [ ] Import from Plan interface working

**Scene Management:**
- [ ] POV management from card and Matrix
- [ ] Scene subtitles addable
- [ ] Scene history viewable and restorable
- [ ] Archive/restore scenes working

**Actions:**
- [ ] Act/Chapter/Scene context menus complete
- [ ] Novel-level actions working
- [ ] Bulk operations functional

**Quality:**
- [ ] Search and filter performant
- [ ] Mobile-responsive
- [ ] Keyboard navigation supported
- [ ] Settings persisted per novel
- [ ] Unit tests for components
- [ ] Feature tests for endpoints

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Matrix view performance with many scenes | High | Virtual scrolling, lazy loading, pagination |
| Multiple matrix modes complexity | High | Well-designed mode switcher, clear UX patterns |
| Drag & drop complexity | Medium | Use proven library (vue-draggable-plus) |
| Outline parsing accuracy | Medium | Multiple parsing strategies, preview before create |
| Mobile UX for planning | Medium | Simplified mobile views, touch-optimized, swipe gestures |
| Context menu on mobile | Medium | Long-press to open, bottom sheet on mobile |
| History storage growth | Medium | Configurable retention, periodic cleanup |
| Auto-detect codex performance | Medium | Debounced detection, caching results |
| Large novel performance | High | Virtualization, lazy loading acts/chapters |

---

## 📎 References

- [The Plan Interface](https://www.novelcrafter.com/help/docs/plan/the-plan-interface)
- [Plan Views](https://www.novelcrafter.com/help/docs/plan/plan-views)
- [Planning with the Matrix](https://www.novelcrafter.com/help/docs/plan/planning-with-the-matrix)
- [Create from Outline](https://www.novelcrafter.com/help/docs/plan/create-from-outline)
- [Changing Scene Card Appearance](https://www.novelcrafter.com/help/docs/plan/changing-plan-appearance)
- [Actions Menu (Plan)](https://www.novelcrafter.com/help/docs/plan/plan-actions-menu)
- [Labeling Scenes](https://www.novelcrafter.com/help/docs/organization/labeling-scenes)
