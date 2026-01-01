# P1 Codex System - Cross-Frontend Implementation Strategy

> **Reference**: [NovelCrafter Codex Documentation](https://www.novelcrafter.com/help/docs/codex/the-codex)
> **Parent Plan**: `codex_system_strategy_2ffb0cb1.plan.md`
> **Date**: January 1, 2026

---

## Executive Summary

This document provides a comprehensive implementation strategy for P1 Codex features. P1 features are **critical for feature completeness** - without them, the Codex system is functional but incomplete for production use.

### P1 Feature Status Overview

| Feature | Status | Owner | Consumer | Complexity |
|---------|--------|-------|----------|------------|
| Progressions System | 🟡 In Progress | Codex Detail Page | Editor (AI Context) | Medium |
| Mention Tracking | 🔴 Pending | System (auto) | Codex Detail, Plan Matrix | High |
| Editor Mention Highlighting | 🔴 Pending | TipTap Extension | Editor Interface | High |
| Editor Codex Sidebar Panel | 🔴 Pending | Editor Sidebar | Writers in Editor | Medium |
| Categories System | 🔴 Pending | Codex Settings | Codex List Filters | Low |
| Search and Filter | 🔴 Pending | Codex Index | Writers finding entries | Medium |

---

## Phase 1: Feature Understanding (NovelCrafter Reference)

### What NovelCrafter Teaches Us

Based on the NovelCrafter documentation analysis:

#### 1. Progressions ("Additions" in NovelCrafter)
- **Purpose**: Track major character/world changes that occur at specific story points
- **Key Insight**: Only visible to AI for scenes ON/AFTER the progression was created
- **Two Modes**: Addition (appends info) vs Replace (overwrites detail value)
- **Integration**: Created from Write interface via slash command `/codex progressions`
- **Use Cases**: Character gets married, location destroyed, character gains powers

#### 2. Mention Tracking
- **Auto-detection**: System scans manuscript for entry names + aliases
- **Display**: Visual heatmap in entry header showing mention distribution
- **Breakdown**: Tracks mentions across manuscript, scene summaries, codex entries, snippets, chat
- **Toggle**: Can disable tracking per entry to reduce clutter

#### 3. Relations ("Nested Entries")
- **Purpose**: Link codex entries so they auto-include when parent is mentioned
- **NOT for**: Family relationships (parent/child) - that's contextual, not structural
- **Use Case**: "The Council of Five" pulls in all 5 member entries when mentioned
- **Warning**: Be selective - can cascade too many entries into context

#### 4. Categories/Tags
- **Human-only**: NOT seen by AI, purely for organization
- **Pre-set tags**: Per type (protagonist/antagonist for characters, city/town for locations)
- **Custom tags**: User-defined for story-specific groupings
- **Filtering**: Quick filter entries by category in list view

#### 5. AI Context Modes
- **Always include**: Global entry, always in AI context
- **When detected** (default): Added when name/alias found in text
- **Don't include when detected**: Excluded even if detected (can still be manually added)
- **Never include**: Never shown to AI (private notes)

---

## Phase 2: Cross-Frontend Impact Mapping

| Feature | Owner (Who Creates) | Consumer (Who Views) | Data Flow | Integration Points |
|---------|---------------------|---------------------|-----------|-------------------|
| **Progressions** | Codex Show Page + Editor (slash cmd) | Codex Timeline, AI Context | Create → Store → Filter by Scene → Include in AI | Editor, AI Service |
| **Mention Tracking** | System (background job on save) | Codex Header Heatmap, Plan Matrix | Scan Text → Store Counts → Display | Scene save hook, Editor |
| **Mention Highlighting** | TipTap Extension (auto) | Editor Interface | Detect alias → Highlight → Hover Preview | TipTap, Editor |
| **Codex Sidebar Panel** | Editor Sidebar Tab | Writers in Editor | Click entry → View/Quick-edit → Close | Editor Sidebar |
| **Categories** | Codex Settings Page | Codex List Filters, Entry Sidebar | Create cat → Assign → Filter | Codex Index, Show |
| **Search & Filter** | Codex Index UI | Writers finding entries | Type search → Filter results → Navigate | Codex Index |

---

## Phase 3: Missing Implementation Detection

### ✅ Already Implemented (P0 Complete)

**Owner Side:**
- [x] Create/Edit codex entries (Create.vue, Edit.vue)
- [x] Add/remove aliases (AliasManager.vue)
- [x] Add/edit details (DetailManager.vue)
- [x] Add relations (RelationManager.vue)
- [x] Add progressions (ProgressionManager.vue)
- [x] AI context mode selector (AIContextControl.vue)
- [x] Codex navigation from novel

**Consumer Side:**
- [x] Codex Index page with grid view
- [x] Codex Show page with all sections
- [x] Mentions display in sidebar (Show.vue)

### 🟡 Partially Implemented (P1 In Progress)

**Progressions System:**
- [x] Basic CRUD for progressions
- [x] Scene linking dropdown
- [x] Detail association
- [ ] **MISSING: Replace vs Addition mode toggle**
- [ ] **MISSING: Drag to reorder progressions**
- [ ] **MISSING: Inline creation from Editor (slash command)**
- [ ] **MISSING: Timeline view visualization**

### 🔴 Not Yet Implemented (P1 Pending)

**Mention Tracking:**
- [ ] MentionTracker service (backend)
- [ ] Background job on scene save
- [ ] Mention heatmap visualization in entry header
- [ ] Mention breakdown by source (manuscript, summaries, etc.)
- [ ] Tracking toggle per entry

**Editor Mention Highlighting:**
- [ ] TipTap extension for alias detection
- [ ] Underline styling (color per type)
- [ ] Hover preview card
- [ ] Click to open entry in sidebar

**Editor Codex Sidebar Panel:**
- [ ] CodexSidebarPanel.vue component
- [ ] Tab in EditorSidebar
- [ ] Entry quick-view
- [ ] Quick-create from selected text

**Categories System:**
- [ ] Category management UI
- [ ] Assign categories to entries
- [ ] Filter by category in Index
- [ ] Pre-set categories per type

**Search and Filter:**
- [ ] Search input in Codex Index
- [ ] Filter by type
- [ ] Filter by category
- [ ] Sort options (name, date, mentions)

---

## Phase 4: Gap Analysis - Critical Findings

### ⚠️ Critical Gaps (P1 Blockers)

| Gap | Impact | Priority | Recommendation |
|-----|--------|----------|----------------|
| **No mention detection** | AI context can't auto-include detected entries | P1-Critical | Build MentionTracker service first |
| **No Editor integration** | Writers must leave editor to view/create codex | P1-Critical | Add CodexSidebarPanel to EditorSidebar |
| **No search in Codex Index** | Hard to find entries in large codex (50+ entries) | P1-High | Add search/filter UI |

### 🟡 Important Gaps (Feature Incomplete)

| Gap | Impact | Priority | Recommendation |
|-----|--------|----------|----------------|
| No progression mode toggle | Can't differentiate additions from replacements | P1-Medium | Add mode field to progression |
| No mention highlighting in editor | Can't see which entries are referenced while writing | P1-Medium | Build TipTap extension |
| No category filtering | Manual organization only | P1-Low | Add category system after core features |

### 📱 UX Gaps

| Gap | Impact | Recommendation |
|-----|--------|----------------|
| No mobile-optimized Codex view | Grid cramped on mobile | Add list view toggle |
| No breadcrumb in Show page | Hard to navigate back | Add breadcrumb component |
| No empty state for new novels | Confusing for new users | Add onboarding prompts |

---

## Phase 5: Implementation Sequencing

### Dependency Graph

```
Progressions Enhancement
       │
       └──► Add mode toggle, inline editor creation
             │
Mention Tracking Service ◄────┘
       │
       ├──► Editor Mention Highlighting (depends on tracker)
       │           │
       │           └──► Hover preview, click to open
       │
       └──► Codex Sidebar Panel (parallel with highlighting)
                   │
                   └──► Quick-view, quick-create

Categories System (independent)
       │
       └──► Search & Filter (uses categories)
```

### Sprint Breakdown (P1)

#### Sprint 5A: Progressions + Mention Foundation (Week 1-2)

| Task | Priority | Est. Hours | Dependencies |
|------|----------|------------|--------------|
| Add progression mode (addition/replace) | P1 | 4h | None |
| MentionTracker service (backend) | P1 | 8h | None |
| Background job on scene save | P1 | 4h | MentionTracker |
| Mention heatmap UI | P1 | 6h | MentionTracker |
| Update Codex Show with heatmap | P1 | 4h | Heatmap UI |

#### Sprint 5B: Editor Integration (Week 3-4)

| Task | Priority | Est. Hours | Dependencies |
|------|----------|------------|--------------|
| CodexSidebarPanel.vue | P1 | 12h | None |
| Add Codex tab to EditorSidebar | P1 | 4h | CodexSidebarPanel |
| TipTap mention extension | P1 | 16h | MentionTracker |
| Hover preview card | P1 | 6h | TipTap extension |
| Click to open in sidebar | P1 | 4h | Sidebar panel |

#### Sprint 5C: Search & Categories (Week 5)

| Task | Priority | Est. Hours | Dependencies |
|------|----------|------------|--------------|
| Search input in Codex Index | P1 | 4h | None |
| Type filter dropdown | P1 | 3h | None |
| Sort options | P1 | 3h | None |
| Category model + migrations | P1 | 4h | None |
| Category management UI | P1 | 6h | Category model |
| Category filter in Index | P1 | 4h | Category UI |

### Priority Matrix

```
                    IMPACT
              High          Low
         ┌──────────┬──────────┐
    High │ Mention  │ Category │
EFFORT   │ Tracking │ System   │
         │ Editor   │          │
         │ Highlight│          │
         ├──────────┼──────────┤
    Low  │ Search   │ Progress │
         │ Filter   │ Mode     │
         │ Sidebar  │ Toggle   │
         └──────────┴──────────┘
```

---

## Phase 6: Detailed Recommendations

### New Components Needed

| Component | Location | Purpose | Priority |
|-----------|----------|---------|----------|
| `MentionHeatmap.vue` | `@/components/codex/` | Visual mention distribution | P1 |
| `CodexSidebarPanel.vue` | `@/components/editor/` | Codex quick-view in editor | P1 |
| `MentionHighlight.ts` | `@/extensions/tiptap/` | TipTap extension for highlighting | P1 |
| `MentionPreview.vue` | `@/components/codex/` | Hover card for mentions | P1 |
| `CategoryManager.vue` | `@/components/codex/` | Category CRUD | P1 |
| `CodexSearch.vue` | `@/components/codex/` | Search + filter controls | P1 |

### Updates to Existing Components

| Component | Update | Priority |
|-----------|--------|----------|
| `Show.vue` | Add MentionHeatmap to header | P1 |
| `ProgressionManager.vue` | Add mode toggle (addition/replace) | P1 |
| `Index.vue` | Add CodexSearch component | P1 |
| `EditorSidebar.vue` | Add Codex tab | P1 |

### Backend Services Needed

| Service | Purpose | Priority |
|---------|---------|----------|
| `MentionTracker.php` | Scan text for aliases, store counts | P1 |
| `ScanMentionsJob.php` | Background job triggered on scene save | P1 |
| `CategoryService.php` | Category CRUD with pre-sets | P1 |

### API Endpoints Needed

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/scenes/{id}/scan-mentions` | POST | Trigger mention scan |
| `/api/codex/{id}/mentions` | GET | Get mention breakdown |
| `/api/novels/{id}/categories` | CRUD | Category management |
| `/api/codex/search` | GET | Search entries |

---

## Phase 7: Example User Journeys

### Journey 1: Track Character Progression (P1)

**Owner Journey (Codex Page):**
1. User navigates to: `/codex/{entry}` (Character detail)
2. User scrolls to: Progressions section
3. User clicks: "+ Add Progression"
4. User fills:
   - Note: "Elena discovers her magical abilities"
   - Story Timestamp: "Chapter 5, Day 45"
   - Scene: Select "The Revelation" from dropdown
   - Mode: **Addition** (appends to description)
5. User clicks: "Save"
6. User sees: New progression in timeline with scene link

**Owner Journey (Editor - Future):**
1. User writes: Scene where Elena discovers magic
2. User types: `/codex` (slash command)
3. User selects: "Add Progression"
4. User fills: Quick form with pre-selected current scene
5. User sees: Progression created, linked to current scene

**Consumer Journey (AI):**
1. User writes: Scene in Chapter 7 (after Chapter 5)
2. System builds AI context
3. System includes: Elena's base description + Chapter 5 progression
4. AI generates: Text aware Elena has magical abilities

### Journey 2: Find Entry via Mention Highlight (P1)

**Writer Journey:**
1. User navigates to: `/novels/{id}/write/{scene}` (Editor)
2. User types: "Elena walked into the tavern"
3. System detects: "Elena" (alias), "tavern" (location entry)
4. User sees: "Elena" underlined in **purple**, "tavern" in **blue**
5. User hovers: "Elena" → Mini card shows entry preview
6. User clicks: "Elena" → Sidebar panel opens with full entry
7. User can: View details, add alias, create progression
8. User closes: Sidebar, continues writing

### Journey 3: Search Codex for Entry (P1)

**Writer Journey:**
1. User navigates to: `/novels/{id}/codex` (Index)
2. User sees: 50+ entries in grid
3. User types: "dragon" in search box
4. User filters: Type = "Character" from dropdown
5. User sees: 3 matching entries (Dragonlord Kai, Dragon Familiar, etc.)
6. User clicks: "Dragonlord Kai" entry card
7. User views: Full entry detail

---

## Implementation Tasks Checklist

### Backend Tasks

- [ ] Create `app/Services/Codex/MentionTracker.php`
- [ ] Create `app/Jobs/ScanMentionsJob.php`
- [ ] Add `mode` column to `codex_progressions` table (addition/replace)
- [ ] Create mention scan endpoint `POST /api/scenes/{id}/scan-mentions`
- [ ] Hook ScanMentionsJob into scene content update
- [ ] Create category endpoints

### Frontend Tasks

- [ ] Create `MentionHeatmap.vue` component
- [ ] Add heatmap to `Show.vue` header
- [ ] Create `CodexSidebarPanel.vue` component
- [ ] Add Codex tab to `EditorSidebar.vue`
- [ ] Create TipTap `MentionHighlight.ts` extension
- [ ] Create `MentionPreview.vue` hover card
- [ ] Add mode toggle to `ProgressionManager.vue`
- [ ] Create `CodexSearch.vue` component
- [ ] Add search/filter to `Index.vue`
- [ ] Create `CategoryManager.vue` component

### Testing Tasks

- [ ] Test mention tracking performance (100+ entries)
- [ ] Test editor highlighting with many aliases
- [ ] Test search with various filters
- [ ] Test progression AI context inclusion
- [ ] Mobile responsiveness for all new components

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Mention scanning is slow | Poor UX on save | Use background job, debounce, cache results |
| Too many highlights clutter editor | Hard to read | Allow toggle per entry, limit highlight density |
| Large codex search is slow | Can't find entries | Add pagination, virtual scrolling, index aliases |
| Cascading relations flood AI context | Token limits exceeded | Limit nesting depth, warn users |

---

## Success Metrics

- [ ] Mention detection completes within 2 seconds of scene save
- [ ] Editor highlights appear within 500ms of typing
- [ ] Search returns results within 200ms
- [ ] Codex sidebar loads entry within 300ms
- [ ] Mobile users can use all P1 features
- [ ] AI context correctly includes/excludes based on progression scene position

---

## Next Steps

1. **Immediate**: Complete progressions mode toggle (simplest P1 task)
2. **Week 1-2**: Build MentionTracker service and backend infrastructure
3. **Week 3-4**: Build Editor integration (sidebar + highlighting)
4. **Week 5**: Add search/filter and categories

**Recommended starting point**: Sprint 5A - Progressions Enhancement + Mention Foundation
