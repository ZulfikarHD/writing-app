# 🗺️ EPIC 3: Story Planning

**Epic ID:** EPIC-03  
**Priority:** 🔴 Critical  
**Total Story Points:** ~60  
**Est. Duration:** 2-3 Sprints  
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
| F-03.1.3 | Search & Filter | 🔴 Critical | 5 |
| F-03.1.4 | Actions Menu (Plan) | 🟡 High | 5 |

### FG-03.2: Plan Views

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.2.1 | Grid View | 🔴 Critical | 8 |
| F-03.2.2 | Matrix View | 🔴 Critical | 8 |
| F-03.2.3 | Outline View | 🟡 High | 5 |
| F-03.2.4 | Timeline View (Optional) | 🟢 Low | 5 |

### FG-03.3: Scene Cards

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.3.1 | Scene Card Component | 🔴 Critical | 5 |
| F-03.3.2 | Scene Card Appearance Customization | 🟡 High | 3 |
| F-03.3.3 | Drag & Drop Reordering | 🔴 Critical | 5 |
| F-03.3.4 | Scene Labels & Status | 🟡 High | 5 |

### FG-03.4: Structure Creation

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-03.4.1 | Add Acts/Chapters/Scenes | 🔴 Critical | 5 |
| F-03.4.2 | Create from Outline | 🟡 High | 8 |
| F-03.4.3 | Story Templates | 🟢 Medium | 5 |
| F-03.4.4 | Import Outline from Text | 🟢 Medium | 5 |

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
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** see all my scenes as cards in a grid layout,  
**So that** I can get an overview of my entire story.

#### Acceptance Criteria:
- [ ] Scene cards displayed in grid
- [ ] Cards grouped by Chapter (and optionally Act)
- [ ] Chapter headers with collapse/expand
- [ ] Drag & drop reorder within chapter
- [ ] Drag & drop move between chapters
- [ ] Responsive columns (4 desktop, 2 tablet, 1 mobile)
- [ ] Click card to navigate to editor
- [ ] Visual indicators for status/labels
- [ ] Word count per scene on card

**Reference:** [Plan Views - Grid](https://www.novelcrafter.com/help/docs/plan/plan-views)

---

### US-03.4: Matrix View
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** see a matrix of scenes vs story elements,  
**So that** I can track which characters/locations appear in each scene.

#### Acceptance Criteria:
- [ ] Scenes as rows
- [ ] Columns: Characters, Locations, Items, Subplots (selectable)
- [ ] Cell shows presence indicator or count
- [ ] Hover cell for details
- [ ] Click cell to navigate to scene
- [ ] Filter columns by Codex type
- [ ] Sort rows by chapter order
- [ ] Horizontal scroll for many columns
- [ ] Sticky header row
- [ ] Sticky first column (scene names)
- [ ] Column grouping by type

**Reference:** [Planning with the Matrix](https://www.novelcrafter.com/help/docs/plan/planning-with-the-matrix)

---

### US-03.5: Outline View
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** see a linear outline of all scenes,  
**So that** I can quickly review the story flow.

#### Acceptance Criteria:
- [ ] Hierarchical list: Acts → Chapters → Scenes
- [ ] Collapsible sections
- [ ] Scene title and summary preview
- [ ] Word count per scene
- [ ] Total word count
- [ ] Click to navigate to editor
- [ ] Quick edit summary inline
- [ ] Drag & drop reordering
- [ ] Print-friendly mode

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
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** see informative scene cards,  
**So that** I can understand scene content at a glance.

#### Acceptance Criteria:
- [ ] Card displays: scene number, title
- [ ] Summary preview (truncated)
- [ ] Word count
- [ ] POV character indicator
- [ ] Status/label badge
- [ ] Hover for more info
- [ ] Context menu (right-click)
- [ ] Selection state (multi-select)
- [ ] Visual feedback on drag

---

### US-03.8: Scene Card Appearance Customization
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** customize scene card appearance,  
**So that** I can see the information most relevant to me.

#### Acceptance Criteria:
- [ ] Toggle visible info: title, summary, word count, POV, label
- [ ] Card size options: compact, normal, large
- [ ] Card height settings
- [ ] Card width settings
- [ ] Color coding options
- [ ] Show/hide thumbnails
- [ ] Settings persisted per novel

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
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** perform bulk actions on scenes,  
**So that** I can efficiently manage multiple scenes.

#### Acceptance Criteria:
- [ ] Select multiple scenes
- [ ] Bulk delete
- [ ] Bulk label assignment
- [ ] Bulk move to chapter
- [ ] Export selected scenes
- [ ] Archive selected scenes
- [ ] Merge scenes
- [ ] Split scene

**Reference:** [Actions Menu (Plan)](https://www.novelcrafter.com/help/docs/plan/plan-actions-menu)

---

### US-03.14: Story Templates
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer,  
**I want to** start from story structure templates,  
**So that** I can use proven narrative frameworks.

#### Acceptance Criteria:
- [ ] Template library: 3-Act, 5-Act, Hero's Journey, Save the Cat, etc.
- [ ] Template preview
- [ ] Apply template creates acts/chapters
- [ ] Customize template before applying
- [ ] User can save custom templates

---

## 🗄️ Database Schema

### Table: `scene_labels`

```sql
CREATE TABLE scene_labels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL, -- Hex color
    is_default BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
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

### Updates to `scenes` table:

```sql
ALTER TABLE scenes ADD COLUMN label_id BIGINT UNSIGNED NULL;
ALTER TABLE scenes ADD FOREIGN KEY (label_id) REFERENCES scene_labels(id) ON DELETE SET NULL;
```

---

## 🏗️ Technical Architecture

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Plan/
│       └── Index.vue
├── Components/
│   └── Plan/
│       ├── PlanHeader.vue
│       ├── ViewSwitcher.vue
│       ├── GridView.vue
│       ├── MatrixView.vue
│       ├── OutlineView.vue
│       ├── TimelineView.vue
│       ├── SceneCard.vue
│       ├── SceneCardSettings.vue
│       ├── ChapterGroup.vue
│       ├── ActGroup.vue
│       ├── LabelBadge.vue
│       ├── LabelManager.vue
│       ├── CreateFromOutline.vue
│       ├── TemplateSelector.vue
│       ├── PlanSearch.vue
│       ├── PlanFilters.vue
│       └── ActionsMenu.vue
```

### Backend Structure

```
app/
├── Http/
│   └── Controllers/
│       └── PlanController.php
├── Models/
│   └── SceneLabel.php
├── Services/
│   └── Plan/
│       ├── OutlineParser.php
│       └── MatrixDataBuilder.php
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/plan` | Get plan data (scenes with metadata) |
| GET | `/api/novels/{novel}/plan/matrix` | Get matrix data |
| POST | `/api/novels/{novel}/plan/reorder` | Reorder scenes |
| GET | `/api/novels/{novel}/labels` | List labels |
| POST | `/api/novels/{novel}/labels` | Create label |
| PATCH | `/api/scenes/{scene}/label` | Assign label to scene |
| POST | `/api/novels/{novel}/plan/bulk-action` | Bulk operations |
| POST | `/api/novels/{novel}/plan/from-outline` | Create from outline |
| GET | `/api/templates` | List story templates |

---

## ✅ Definition of Done

- [ ] All three views (Grid, Matrix, Outline) implemented
- [ ] Drag & drop reordering working smoothly
- [ ] Matrix view displays Codex entry mentions correctly
- [ ] Scene labels system complete
- [ ] Create from outline parsing correctly
- [ ] Search and filter performant
- [ ] Mobile-responsive
- [ ] Keyboard navigation supported
- [ ] Unit tests for components
- [ ] Feature tests for endpoints

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Matrix view performance with many scenes | High | Virtual scrolling, lazy loading |
| Drag & drop complexity | Medium | Use proven library (vue-draggable) |
| Outline parsing accuracy | Medium | Multiple parsing strategies, manual correction |
| Mobile UX for planning | Medium | Simplified mobile views, touch-optimized |

---

## 📎 References

- [The Plan Interface](https://www.novelcrafter.com/help/docs/plan/the-plan-interface)
- [Plan Views](https://www.novelcrafter.com/help/docs/plan/plan-views)
- [Planning with the Matrix](https://www.novelcrafter.com/help/docs/plan/planning-with-the-matrix)
- [Create from Outline](https://www.novelcrafter.com/help/docs/plan/create-from-outline)
- [Changing Scene Card Appearance](https://www.novelcrafter.com/help/docs/plan/changing-plan-appearance)
- [Actions Menu (Plan)](https://www.novelcrafter.com/help/docs/plan/plan-actions-menu)
- [Labeling Scenes](https://www.novelcrafter.com/help/docs/organization/labeling-scenes)
