# 🧪 Sprint 17 Testing Guide: Unified Workspace & Codex UX

**Sprint:** 17  
**Feature:** Unified Workspace & Codex UX Enhancements  
**Date:** 2026-01-02  
**Status:** ✅ Complete

---

## 📋 Overview

Testing guide untuk Sprint 17 yang mencakup Unified Workspace layout, CodexEntryModal redesign, CodexCreateModal, dan sidebar hierarchy improvements, yaitu: memastikan seamless mode switching, proper UI/UX behavior, responsive design, dan integration yang smooth antara components.

---

## 🎯 Testing Scope

### Features to Test

1. **Unified Workspace Layout**
   - Mode switching (Write/Plan/Codex)
   - State persistence
   - URL synchronization
   - Sidebar integration

2. **CodexEntryModal Redesign**
   - Tab navigation dan content
   - Actions (Edit, Duplicate, Archive, Delete)
   - Responsive behavior
   - Dark mode

3. **CodexCreateModal**
   - Form validation
   - Type selection
   - Entry creation
   - Event emission

4. **Sidebar Hierarchy**
   - Visual indentation
   - Collapse/expand behavior
   - Drag & drop
   - Tree structure clarity

5. **Modal Component**
   - Slots (header, body, footer)
   - Sizing options
   - Close behaviors
   - Scrollable content

---

## ✅ Manual Testing Checklist

### 1. Unified Workspace - Basic Access

#### TC-WS-001: Navigate to Workspace
**Steps:**
1. Login to application
2. Select a novel
3. Navigate to `/novels/{novel}/workspace`

**Expected:**
- [ ] Workspace loads successfully
- [ ] Default scene created if none exists
- [ ] Sidebar displays chapters and scenes
- [ ] Write mode active by default
- [ ] Editor panel visible

---

#### TC-WS-002: Workspace dengan Specific Scene
**Steps:**
1. Navigate to `/novels/{novel}/workspace/{scene}`

**Expected:**
- [ ] Specified scene loaded di editor
- [ ] Scene highlighted di sidebar
- [ ] Scene content displayed jika ada
- [ ] URL matches scene ID

---

### 2. Unified Workspace - Mode Switching

#### TC-WS-003: Switch ke Plan Mode
**Steps:**
1. Di workspace, klik "Plan" di mode navigation
2. Observe panel change
3. Refresh page

**Expected:**
- [ ] URL updates to include `?mode=plan`
- [ ] Plan panel loads (lazy)
- [ ] Mode persists setelah refresh
- [ ] Sidebar tetap visible
- [ ] Loading smooth tanpa flicker

---

#### TC-WS-004: Switch ke Codex Mode
**Steps:**
1. Di workspace, klik "Codex" di mode navigation
2. Observe panel change
3. Switch back to Write

**Expected:**
- [ ] URL updates to `?mode=codex`
- [ ] Codex panel loads
- [ ] Codex entries list displayed
- [ ] Mode persists
- [ ] Switch back smooth

---

#### TC-WS-005: Mode State Persistence
**Steps:**
1. Switch to Plan mode
2. Close browser tab
3. Reopen workspace URL (tanpa ?mode query)

**Expected:**
- [ ] Plan mode tetap active (from localStorage)
- [ ] URL updated to `?mode=plan`
- [ ] State restored correctly

---

### 3. CodexEntryModal - Tab Navigation

#### TC-CEM-001: Open Modal dari Codex List
**Steps:**
1. Go to codex list page atau codex panel
2. Click on codex entry card
3. Observe modal opens

**Expected:**
- [ ] Modal opens dengan smooth animation
- [ ] Entry details displayed di header
- [ ] Default tab: "Details"
- [ ] All tabs visible
- [ ] Count badges displayed (Relations, Timeline, Mentions, Research)

---

#### TC-CEM-002: Details Tab Content
**Steps:**
1. Open codex entry modal
2. Verify Details tab active by default

**Expected:**
- [ ] **Description:** Rich text description displayed di top
- [ ] **Aliases:** List of aliases dalam card dengan icons
- [ ] **Attributes:** Custom attributes grid (two-column on desktop)
- [ ] Cards have proper borders dan styling
- [ ] Edit description button works
- [ ] Add alias/attribute works

---

#### TC-CEM-003: Organize Tab Content
**Steps:**
1. Open modal
2. Click "Organize" tab

**Expected:**
- [ ] Tab switches smoothly
- [ ] **"Not sent to AI"** warning label displayed prominently
- [ ] **Tags:** TagManager component displayed
- [ ] **Categories:** CategoryManager component displayed  
- [ ] **Info section:** Stats grid (Aliases count, Attributes count, Relations count, Mentions count)
- [ ] **Metadata:** Created/Updated dates di footer
- [ ] Grid responsive (4-col → 2-col → 1-col pada mobile)

---

#### TC-CEM-004: Relations Tab
**Steps:**
1. Open modal
2. Click "Relations" tab

**Expected:**
- [ ] RelationGraph displayed di top
- [ ] RelationManager displayed di bottom
- [ ] Count badge shows relation count
- [ ] Graph interactive (click node untuk navigate)
- [ ] Add relation works

---

#### TC-CEM-005: Timeline Tab
**Steps:**
1. Open modal
2. Click "Timeline" tab

**Expected:**
- [ ] Progressions list displayed chronologically
- [ ] Each progression shows scene link, mode, content
- [ ] Count badge matches progression count
- [ ] Add progression button works
- [ ] Empty state displayed jika no progressions

---

#### TC-CEM-006: Mentions Tab
**Steps:**
1. Open modal
2. Click "Mentions" tab

**Expected:**
- [ ] MentionHeatmap displayed di top
- [ ] Scene mentions list di bottom
- [ ] Count badge matches mention count
- [ ] Click scene → navigates to scene
- [ ] Refresh mentions button works

---

#### TC-CEM-007: Research Tab
**Steps:**
1. Open modal
2. Click "Research" tab

**Expected:**
- [ ] ResearchTab component displayed
- [ ] Research notes editor visible
- [ ] External links list visible
- [ ] Add link button works
- [ ] Notes save functionality works

---

### 4. CodexEntryModal - Actions

#### TC-CEM-008: Edit Full Page
**Steps:**
1. Open modal
2. Click "Edit Full Page" button di footer

**Expected:**
- [ ] Modal closes
- [ ] Navigates to `/codex/{entry}/edit`
- [ ] Edit page loads successfully

---

#### TC-CEM-009: Duplicate Entry
**Steps:**
1. Open modal
2. Click "Duplicate" button

**Expected:**
- [ ] Confirmation dialog appears
- [ ] Confirm → entry duplicated
- [ ] Toast notification success
- [ ] Modal updates to show duplicated entry OR closes
- [ ] Duplicated entry visible di list

---

#### TC-CEM-010: Archive Entry
**Steps:**
1. Open modal
2. Click "Archive" button

**Expected:**
- [ ] Confirmation dialog appears
- [ ] Confirm → entry archived
- [ ] Toast notification success
- [ ] Modal closes
- [ ] Entry removed from active list

---

#### TC-CEM-011: Delete Entry
**Steps:**
1. Open modal
2. Click "Delete" button

**Expected:**
- [ ] Confirmation dialog appears dengan warning
- [ ] Confirm → entry deleted permanently
- [ ] Toast notification success
- [ ] Modal closes
- [ ] Entry removed from list

---

### 5. CodexEntryModal - Mobile & Responsive

#### TC-CEM-012: Mobile Tab Scrolling
**Viewport:** iPhone SE (375px)

**Steps:**
1. Open modal pada mobile viewport
2. Observe tabs

**Expected:**
- [ ] Tabs scrollable horizontally
- [ ] Active tab visible
- [ ] Smooth scroll behavior
- [ ] Touch gestures work

---

#### TC-CEM-013: Mobile Layout
**Viewport:** iPhone SE (375px)

**Steps:**
1. Open modal
2. Navigate through all tabs

**Expected:**
- [ ] **Details:** Aliases + Attributes stack vertically (1 column)
- [ ] **Organize:** Stats grid 2-column pada mobile
- [ ] **Relations:** Graph responsive
- [ ] **All tabs:** Proper spacing dan padding
- [ ] Footer buttons stack atau wrap jika needed
- [ ] No horizontal overflow

---

#### TC-CEM-014: Tablet Layout
**Viewport:** iPad (768px)

**Steps:**
1. Open modal
2. Check layout

**Expected:**
- [ ] **Details:** Aliases + Attributes 2-column grid
- [ ] **Organize:** Stats grid 2-column
- [ ] Modal size appropriate (not too wide)
- [ ] Touch targets sufficiently large

---

#### TC-CEM-015: Dark Mode
**Steps:**
1. Enable dark mode (system atau app setting)
2. Open modal
3. Navigate through all tabs

**Expected:**
- [ ] All borders visible (zinc-700)
- [ ] Background colors appropriate (zinc-800/50)
- [ ] Text contrast sufficient
- [ ] Icons visible
- [ ] Section cards styled correctly

---

### 6. CodexCreateModal - Form & Validation

#### TC-CCM-001: Open Modal dari Workspace
**Steps:**
1. Di workspace (any mode)
2. Click "Create Codex" button atau trigger
3. Observe modal opens

**Expected:**
- [ ] Modal opens dengan smooth animation
- [ ] Title: "Create Codex Entry"
- [ ] Type selection cards displayed
- [ ] Form fields empty
- [ ] AI Context Mode default: "Detected"

---

#### TC-CCM-002: Type Selection
**Steps:**
1. Open create modal
2. Click different type cards (Character, Location, Item, Lore, Organization, Subplot)

**Expected:**
- [ ] Each card has icon dan description
- [ ] Selected card highlighted (border atau background)
- [ ] Only one type selected at a time
- [ ] Type value updates dalam form state

---

#### TC-CCM-003: Form Validation - Empty Name
**Steps:**
1. Open modal
2. Select type
3. Leave name empty
4. Click "Create"

**Expected:**
- [ ] Validation error shown: "Name is required"
- [ ] Name input highlighted (red border)
- [ ] Submit prevented
- [ ] Error message clear

---

#### TC-CCM-004: Form Validation - No Type Selected
**Steps:**
1. Open modal
2. Enter name
3. Don't select type
4. Click "Create"

**Expected:**
- [ ] Validation error: "Please select a type"
- [ ] Type section highlighted
- [ ] Submit prevented

---

#### TC-CCM-005: Successful Entry Creation
**Steps:**
1. Open modal
2. Select type: "Character"
3. Enter name: "Test Character"
4. Enter description: "Test description"
5. Select AI mode: "Always"
6. Click "Create"

**Expected:**
- [ ] Loading state shown (button disabled, spinner)
- [ ] API call: `POST /api/novels/{novel}/codex`
- [ ] Success toast notification
- [ ] Modal closes
- [ ] `created` event emitted dengan entry data
- [ ] Entry visible di list (jika on codex page)

---

#### TC-CCM-006: AI Context Mode Selection
**Steps:**
1. Open modal
2. Test each AI context mode option

**Expected:**
- [ ] Always: Described as "Always include in AI context"
- [ ] Detected: Described as "Include when mentioned" (default)
- [ ] Manual: Described as "Only when manually selected"
- [ ] Never: Described as "Never include"
- [ ] Selection visual feedback

---

#### TC-CCM-007: Cancel Creation
**Steps:**
1. Open modal
2. Fill some fields
3. Click "Cancel" atau click overlay atau press ESC

**Expected:**
- [ ] Modal closes
- [ ] No entry created
- [ ] Form data cleared
- [ ] No API call made

---

### 7. Sidebar Hierarchy - Visual Structure

#### TC-SH-001: Chapter Indentation
**Steps:**
1. Di workspace/editor dengan sidebar visible
2. Observe chapter items

**Expected:**
- [ ] Chapters at root level (ml-0)
- [ ] Folder icon (📁 collapsed, 📂 expanded)
- [ ] Bold font weight
- [ ] Hover background subtle
- [ ] No left border/line

---

#### TC-SH-002: Scene Indentation
**Steps:**
1. Expand a chapter
2. Observe scene items

**Expected:**
- [ ] Scenes indented (ml-6 + pl-3)
- [ ] Clear visual separation from chapters
- [ ] Left border line visible (tree structure)
- [ ] Lighter font weight vs chapters
- [ ] Scene icon visible

---

#### TC-SH-003: Multiple Chapters - Visual Clarity
**Steps:**
1. View sidebar dengan multiple chapters, each dengan scenes
2. Collapse dan expand different chapters

**Expected:**
```
📂 Chapter 1
   ├─ Scene 1
   └─ Scene 2
📁 Chapter 2
📂 Chapter 3
   ├─ Scene 3
   ├─ Scene 4
   └─ Scene 5
```
- [ ] Clear hierarchy visible
- [ ] No confusion antara chapter dan scene levels
- [ ] Tree lines help understand structure
- [ ] Collapse/expand smooth

---

#### TC-SH-004: Empty Chapter
**Steps:**
1. Create chapter tanpa scenes
2. Observe di sidebar

**Expected:**
- [ ] Chapter displayed dengan folder icon
- [ ] No expand/collapse icon (atau disabled)
- [ ] Empty state message atau indicator
- [ ] Still draggable

---

#### TC-SH-005: Drag & Drop dengan Hierarchy
**Steps:**
1. Drag a scene within same chapter
2. Drag a scene to different chapter
3. Drag a chapter to reorder

**Expected:**
- [ ] Visual feedback during drag (ghost, placeholder)
- [ ] Drop zones clear
- [ ] Indentation maintained setelah drop
- [ ] Position saved to backend
- [ ] Sidebar rerenders correctly

---

### 8. Modal Component - Base Functionality

#### TC-MOD-001: Modal Sizes
**Steps:**
1. Open modals dengan different size props (sm, md, lg, xl, full)

**Expected:**
- [ ] `sm`: max-w-sm (384px)
- [ ] `md`: max-w-md (448px)
- [ ] `lg`: max-w-lg (512px)
- [ ] `xl`: max-w-xl (576px)
- [ ] `full`: max-w-4xl (896px)
- [ ] Responsive pada mobile (full width)

---

#### TC-MOD-002: Close on Overlay Click
**Steps:**
1. Open modal dengan `closeOnOverlay: true`
2. Click backdrop (outside modal)

**Expected:**
- [ ] Modal closes
- [ ] `close` event emitted

**Steps:**
1. Open modal dengan `closeOnOverlay: false`
2. Click backdrop

**Expected:**
- [ ] Modal stays open

---

#### TC-MOD-003: Close on ESC Key
**Steps:**
1. Open modal dengan `closeOnEscape: true`
2. Press ESC key

**Expected:**
- [ ] Modal closes
- [ ] `close` event emitted

---

#### TC-MOD-004: Scrollable Content
**Steps:**
1. Open modal dengan long content dan `scrollable: true`

**Expected:**
- [ ] Body area scrollable
- [ ] Header fixed at top
- [ ] Footer fixed at bottom (jika ada)
- [ ] Scroll smooth

---

#### TC-MOD-005: Slots Usage
**Steps:**
1. Test modal dengan custom slots (header, default/body, footer)

**Expected:**
- [ ] **Header slot:** Custom content displayed di top
- [ ] **Default slot:** Main content di body
- [ ] **Footer slot:** Actions/buttons di bottom
- [ ] Proper borders between sections

---

## 🤖 Automated Testing

### Feature Tests (PHPUnit)

#### WorkspaceTest.php

**Test Cases:**

```php
test_user_can_access_workspace()
- User authenticated
- Novel belongs to user
- Workspace loads successfully

test_workspace_creates_default_scene_if_none_exists()
- Novel without scenes
- Workspace creates scene
- Scene active

test_user_cannot_access_workspace_of_another_users_novel()
- Novel belongs to other user
- Access denied (403)

test_workspace_loads_specified_scene()
- Scene belongs to novel's chapter
- Scene loaded as active
- Content displayed

test_workspace_loads_necessary_relationships()
- Novel loaded dengan acts, chapters, scenes, labels
- Codex data prepared untuk editor
- No N+1 queries
```

**Run Tests:**
```bash
php artisan test --filter=WorkspaceTest
```

---

### Component Tests (Vitest + Vue Test Utils)

#### CodexEntryModal.spec.ts

**Test Cases:**

```typescript
test('renders modal dengan entry data')
test('switches tabs correctly')
test('Details tab displays description, aliases, attributes')
test('Organize tab shows tags, categories, stats')
test('emits close event saat close button clicked')
test('calls API saat duplicate clicked')
test('calls API saat archive clicked')
test('shows confirmation untuk delete action')
```

---

#### CodexCreateModal.spec.ts

**Test Cases:**

```typescript
test('renders form fields correctly')
test('validates required name field')
test('validates required type selection')
test('type selection cards work')
test('AI context mode selection works')
test('emits created event dengan entry data setelah success')
test('emits close event saat cancel')
test('shows loading state during submission')
```

---

#### useWorkspaceState.spec.ts

**Test Cases:**

```typescript
test('loads state from localStorage')
test('saves state to localStorage on change')
test('mode defaults to write')
test('setMode updates mode correctly')
test('toggleSidebar toggles collapsed state')
test('tool expansion state persists')
test('resetToDefaults clears state')
```

---

### E2E Tests (Playwright / Cypress)

#### workspace-flow.spec.ts

**Test Cases:**

```typescript
test('complete workspace flow: write → plan → codex → write')
test('create codex entry dari workspace')
test('open codex entry modal dari codex panel')
test('edit codex entry full page')
test('sidebar collapse/expand persists')
test('mode state persists across page refresh')
```

---

## 🐛 Bug Testing

### Known Issues to Verify Fixed

| Issue | Description | Expected Fix | Test Status |
|-------|-------------|--------------|-------------|
| Header Cramped | Modal header terlalu banyak buttons | Actions moved to footer | ✅ |
| Tab Organization | Mixed AI context vs personal | Separate Details/Organize tabs | ✅ |
| Sidebar Confusion | Scenes same level as chapters | Clear ml-6 indentation | ✅ |
| Mobile Scrolling | Modal content overflow | Scrollable tabs + responsive grid | ✅ |

---

## 📊 Performance Testing

### Load Time Metrics

| Component | Target | Measurement |
|-----------|--------|-------------|
| Workspace Initial Load | < 1.5s | Measure dengan DevTools |
| Mode Switch (lazy load) | < 500ms | First interaction |
| Modal Open | < 200ms | Animation smooth |
| Tab Switch | < 100ms | Instant feel |

**Test Steps:**
1. Open DevTools Network tab
2. Hard refresh workspace page
3. Measure load time
4. Switch modes → measure lazy load time
5. Open modal → measure animation time

---

### Bundle Size

| Asset | Before (Sprint 16) | After (Sprint 17) | Change |
|-------|--------------------|--------------------|--------|
| app.js (initial) | - | - | Measure dengan `yarn build --report` |
| CodexEntryModal chunk | - | - | Check chunk size |
| Workspace chunks | - | - | Lazy loaded |

---

## 🎯 Acceptance Criteria Summary

### Must Pass

- [ ] All manual test cases passed
- [ ] Automated tests green (WorkspaceTest, component tests)
- [ ] No console errors di browser
- [ ] No linter errors (`yarn lint`)
- [ ] No TypeScript errors (`yarn type-check`)
- [ ] Mobile responsive (tested on iPhone SE, iPad)
- [ ] Dark mode working
- [ ] Performance within targets

### Optional (Nice to Have)

- [ ] E2E tests implemented
- [ ] Performance metrics logged
- [ ] Accessibility score > 90 (Lighthouse)
- [ ] User acceptance testing completed

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 17 - Workspace & Codex UX](../10-sprints/sprint-17-workspace-codex-ux.md)
- **API Reference:** [Codex API](../04-api-reference/codex.md)
- **User Journey:** [Workspace Flow](../07-user-journeys/workspace/)

---

*Last Updated: 2026-01-02*
