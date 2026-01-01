---
name: Sprint 2 Development Strategy
overview: "Sprint 2 focuses on completing Editor Features (Scene Metadata Panel, Action Menu) and starting Story Planning Interface (Hierarchical Structure, Grid View, Search/Filter, Scene Labels/Status). Total: 33 story points across 7 user stories."
todos:
  - id: db-migrations
    content: Create database migrations for acts, scene_labels, and scene_label pivot tables
    status: pending
  - id: backend-models
    content: Create Act and SceneLabel models with relationships
    status: pending
  - id: scene-metadata-panel
    content: Create SceneMetadataPanel.vue component with POV, notes, subtitle fields
    status: pending
  - id: plan-page
    content: Create Plan page with PlanController and /novels/{id}/plan route
    status: pending
  - id: grid-view
    content: Create Grid view with SceneCard and ChapterGroup components
    status: pending
  - id: action-menu
    content: Create ContextMenu.vue and implement scene/chapter actions
    status: pending
  - id: labels-system
    content: Create SceneLabelController and labels management UI
    status: pending
  - id: search-filter
    content: Create SearchFilter component and search API endpoint
    status: pending
  - id: acts-hierarchy
    content: Implement Acts management and update sidebar/plan for hierarchy
    status: pending
  - id: feature-tests
    content: Create feature tests for Acts, Labels, Plan, and Metadata
    status: pending
---

# Sprint 2: Editor Features and Planning Start

## Sprint Overview

**Duration:** Minggu 3-4 (2 weeks)

**Total Points:** 33

**Dependencies:** Sprint 1 (Foundation and Core Editor) - COMPLETED

---

## Phase 1: Feature Understanding

### What Data is Being Created/Managed

| Feature | Data Type | Owner | Consumer |

|---------|-----------|-------|----------|

| Scene Metadata Panel | POV, notes, subtitle, time/location | Writer (Editor) | Writer (Editor, Plan) |

| Action Menu | Scene/Chapter operations | Writer (Editor) | Scene/Chapter entities |

| Hierarchical Structure | Acts layer | Writer (Plan) | Editor Sidebar, Plan Views |

| Grid View | Scene cards display | System (derived) | Writer (Plan) |

| Scene Labels/Status | Custom labels, status | Writer (Editor/Plan) | Plan View, Sidebar |

| Scene Notes | Internal notes | Writer | Writer only |

| Search/Filter | Query results | System | Writer (Plan) |

---

## Phase 2: Cross-Frontend Impact Mapping

### Data Flow Diagram

```
Editor Page ─────────────────────────────────────────────┐
├── Sidebar (existing)                                   │
│   ├── Chapters/Scenes list (Sprint 1) ✅               │
│   ├── + Acts hierarchy (Sprint 2 NEW)                  │
│   └── Scene status indicators (Sprint 2 ENHANCE)      │
├── Scene Metadata Panel (Sprint 2 NEW)                  │
│   ├── POV character selector                          │
│   ├── Word count display                               │
│   ├── Subtitle/Notes fields                            │
│   └── Time/Location fields                             │
└── Action Menu (Sprint 2 NEW)                           │
    ├── Scene actions: duplicate, delete, archive        │
    └── Chapter actions: copy, export, delete            │
                                                         │
Plan Page (Sprint 2 NEW) ────────────────────────────────│
├── Grid View                                            │
│   ├── Scene cards (title, summary, status, POV)       │
│   ├── Drag-drop reorder                                │
│   └── Chapter grouping                                 │
├── Search & Filter bar                                  │
│   ├── Search by title/content                          │
│   └── Filter by label/status/POV                       │
└── Acts/Chapters/Scenes hierarchy                       │
```

---

## Phase 3: Missing Implementation Detection

### Owner Side (Data Creation)

**Scene Metadata Panel (US-003):**

- [x] POV character field (exists in DB: `pov_character_id`)
- [x] Word count (exists: `word_count` in scenes)
- [ ] Subtitle field (exists in DB: `subtitle`)
- [ ] Notes field (exists in DB: `notes`)
- [ ] Time/Location note (needs new field or use `metadata` JSON)
- [ ] UI panel component - **MISSING**

**Scene Labels & Status (US-014):**

- [x] Status field exists (`status` column)
- [ ] Custom labels table - **MISSING**
- [ ] Color coding per label - **MISSING**
- [ ] Labels CRUD UI - **MISSING**

**Hierarchical Structure (US-010):**

- [ ] Acts table/model - **MISSING**
- [ ] Acts relationship to chapters - **MISSING**
- [ ] Acts management UI - **MISSING**

### Consumer Side (Data Display)

**Grid View (US-011):**

- [ ] Plan page route - **MISSING**
- [ ] Scene cards component - **MISSING**
- [ ] Chapter grouping display - **MISSING**
- [ ] Drag-drop for plan view - **MISSING**

**Search & Filter (US-017):**

- [ ] Search component - **MISSING**
- [ ] Filter dropdown - **MISSING**
- [ ] Search API endpoint - **MISSING**

---

## Phase 4: Gap Analysis

### Critical Gaps Identified

| Gap | Type | Priority | Resolution |

|-----|------|----------|------------|

| No Plan page exists | Page | P0 | Create `/novels/{id}/plan` route |

| No Scene Metadata Panel | Component | P0 | Create slide-over panel |

| No Acts model/migration | Backend | P0 | Create `acts` table |

| No Scene Labels system | Backend | P1 | Create `scene_labels` table |

| No Action Menu | Component | P1 | Create context menu component |

| No Search API | Backend | P1 | Add search endpoint |

---

## Phase 5: Implementation Sequencing

### P0 (Critical - Must Complete)

1. **Database: Acts table and Scene Labels** (Day 1-2)

   - Create `acts` migration
   - Create `scene_labels` migration  
   - Update Chapter model (belongs to Act)
   - Create Act model

2. **Scene Metadata Panel** (Day 2-3)

   - Create `SceneMetadataPanel.vue` component
   - Add API endpoint for updating metadata
   - Integrate into Editor page

3. **Plan Page with Grid View** (Day 3-5)

   - Create Plan controller
   - Create `/novels/{id}/plan` route
   - Create `Plan/Index.vue` page
   - Create `SceneCard.vue` component

### P1 (Important - Should Complete)

4. **Action Menu** (Day 5-6)

   - Create reusable `ContextMenu.vue` component
   - Implement scene actions (duplicate, delete, archive)
   - Implement chapter actions

5. **Scene Labels System** (Day 6-7)

   - Create labels CRUD API
   - Create label selector component
   - Add to metadata panel and plan view

6. **Search & Filter** (Day 7-8)

   - Add search API endpoint
   - Create `SearchFilter.vue` component
   - Integrate into Plan page

### P2 (Enhancement - Nice to Have)

7. **Hierarchical Structure with Acts** (Day 8-10)

   - Acts management UI
   - Update sidebar to show Acts
   - Update Plan view for Acts grouping

---

## Phase 6: Detailed File Structure

### New Files to Create

**Backend:**

```
database/migrations/
├── YYYY_MM_DD_create_acts_table.php              (NEW)
├── YYYY_MM_DD_create_scene_labels_table.php      (NEW)
└── YYYY_MM_DD_add_act_id_to_chapters_table.php   (NEW)

app/Models/
├── Act.php                                        (NEW)
└── SceneLabel.php                                 (NEW)

app/Http/Controllers/
├── ActController.php                              (NEW)
├── PlanController.php                             (NEW)
└── SceneLabelController.php                       (NEW)
```

**Frontend:**

```
resources/js/
├── pages/
│   └── Plan/
│       └── Index.vue                              (NEW)
├── components/
│   ├── editor/
│   │   └── SceneMetadataPanel.vue                 (NEW)
│   ├── plan/
│   │   ├── SceneCard.vue                          (NEW)
│   │   ├── ChapterGroup.vue                       (NEW)
│   │   └── SearchFilter.vue                       (NEW)
│   └── ui/
│       └── ContextMenu.vue                        (NEW)
└── composables/
    └── usePlan.ts                                 (NEW)
```

### Files to Modify

```
routes/web.php                    - Add plan routes
app/Models/Chapter.php           - Add act_id relationship
app/Models/Scene.php             - Add labels relationship
resources/js/pages/Editor/Index.vue  - Add metadata panel
resources/js/components/editor/EditorSidebar.vue - Add acts, context menu
```

---

## Phase 7: Example User Journeys

### Journey 1: Scene Metadata Panel (Owner)

1. User opens Editor page for a novel
2. User clicks on a scene in sidebar
3. User clicks "Info" button or presses `i` shortcut
4. Side panel slides in showing:

   - Scene title (editable)
   - Word count (readonly)
   - POV character dropdown
   - Subtitle input
   - Notes textarea
   - Status dropdown

5. User makes changes
6. Changes auto-save (debounced 500ms)
7. User closes panel or clicks outside

### Journey 2: Grid View (Consumer)

1. User clicks "Plan" tab in navigation
2. Plan page loads with Grid view (default)
3. User sees scene cards grouped by Chapter
4. Each card shows: title, summary preview, word count, status badge, POV
5. User can drag cards to reorder
6. User clicks a card to jump to Editor
7. User can filter by status using dropdown
8. User can search by title/content using search bar

### Journey 3: Action Menu (Owner)

1. User right-clicks on a scene in sidebar or plan view
2. Context menu appears with options:

   - Edit metadata
   - Duplicate scene
   - Archive scene
   - Delete scene
   - Exclude from AI context

3. User selects "Duplicate scene"
4. New scene created with copied content
5. User is navigated to the new scene

---

## Database Schema Updates

### Acts Table

```sql
CREATE TABLE acts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT NOT NULL REFERENCES novels(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Scene Labels Table

```sql
CREATE TABLE scene_labels (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT NOT NULL REFERENCES novels(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#6B7280',
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Scene Labels Pivot

```sql
CREATE TABLE scene_label (
    scene_id BIGINT NOT NULL REFERENCES scenes(id) ON DELETE CASCADE,
    label_id BIGINT NOT NULL REFERENCES scene_labels(id) ON DELETE CASCADE,
    PRIMARY KEY (scene_id, label_id)
);
```

---

## API Endpoints to Add

| Method | URI | Name | Description |

|--------|-----|------|-------------|

| GET | `/novels/{novel}/plan` | plan.show | Plan page |

| GET | `/api/novels/{novel}/scenes/search` | scenes.search | Search scenes |

| POST | `/api/scenes/{scene}/duplicate` | scenes.duplicate | Duplicate scene |

| GET | `/api/novels/{novel}/acts` | acts.index | List acts |

| POST | `/api/novels/{novel}/acts` | acts.store | Create act |

| PATCH | `/api/acts/{act}` | acts.update | Update act |

| DELETE | `/api/acts/{act}` | acts.destroy | Delete act |

| GET | `/api/novels/{novel}/labels` | labels.index | List labels |

| POST | `/api/novels/{novel}/labels` | labels.store | Create label |

| PATCH | `/api/labels/{label}` | labels.update | Update label |

| DELETE | `/api/labels/{label}` | labels.destroy | Delete label |

| POST | `/api/scenes/{scene}/labels` | scenes.labels | Assign labels |

---

## Testing Strategy

### Feature Tests to Create

- `tests/Feature/ActTest.php` - Acts CRUD
- `tests/Feature/SceneLabelTest.php` - Labels CRUD
- `tests/Feature/PlanTest.php` - Plan page access, search
- `tests/Feature/SceneMetadataTest.php` - Metadata updates

### Key Test Cases

1. User can create/update/delete acts
2. Acts are ordered correctly
3. Chapters can belong to acts (optional)
4. User can create custom labels
5. Labels have colors and positions
6. Scene metadata can be updated
7. Plan page shows all scenes grouped
8. Search returns matching scenes
9. Filter by label/status works

---

## Risk Mitigation

| Risk | Mitigation |

|------|------------|

| Acts complexity | Make acts OPTIONAL - chapters can exist without acts |

| Performance with many scenes | Virtual scrolling for plan view, paginate search |

| Label colors accessibility | Provide default accessible colors, allow custom |

| Context menu positioning | Use Floating UI library for smart positioning |

---

## Verification Commands

```bash
# Create migrations
php artisan make:migration create_acts_table
php artisan make:migration create_scene_labels_table

# Create models
php artisan make:model Act
php artisan make:model SceneLabel

# Create controllers
php artisan make:controller ActController --api
php artisan make:controller PlanController
php artisan make:controller SceneLabelController --api

# Run migrations
php artisan migrate

# Run tests
php artisan test --filter=Act
php artisan test --filter=SceneLabel
php artisan test --filter=Plan

# Lint
vendor/bin/pint --dirty
yarn run lint
```
