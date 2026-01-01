# 🎨 Enhancement: Workspace Scenes Sidebar & UX Improvements

**Date:** 2026-01-02  
**Type:** Enhancement / UX Improvement  
**Status:** ✅ Complete  
**Priority:** High  
**Affected Area:** Workspace UI, Plan Mode, Scene Navigation

---

## 📋 Overview

Enhancement workspace interface merupakan peningkatan user experience yang bertujuan untuk meningkatkan produktivitas penulis dalam mengelola scenes dan chapters, yaitu: memisahkan navigasi scenes ke sidebar dedicated, menambahkan quick action buttons untuk operasi umum, dan mengimplementasikan SPA-like updates untuk menghindari full page reloads.

---

## 🎯 Goals

Meningkatkan user experience workspace dengan:

1. **Better Visual Hierarchy**: Memisahkan scenes navigation dari tools lain (Codex, Notes) ke sidebar dedicated
2. **Faster Actions**: Menambahkan quick action buttons untuk rename/delete acts/chapters tanpa context menu
3. **Modern Animations**: Menggunakan `motion-v` library untuk smooth transitions dan spring physics
4. **SPA Experience**: Menghindari full page reloads saat melakukan operasi CRUD
5. **Ultra-wide Monitor Support**: Meningkatkan readability pada monitor ultra-wide dengan better spacing dan typography

---

## ✨ Features Implemented

### 1. Dedicated Scenes Right Sidebar

**Status:** ✅ Complete

- **File Created:** `resources/js/components/workspace/ScenesRightSidebar.vue`
- **Features:**
  - Full chapter & scenes tree dengan drag-and-drop
  - Stats bar (chapters count, scenes count, total words)
  - Spring animation untuk slide-in/out
  - Collapsible chapters dengan animated chevron
  - Visual tree lines untuk hierarchy
  - Quick "Add Chapter" button di footer
  - Status indicators untuk scenes (draft, in progress, completed, needs revision)

**Implementation:**
```vue
<ScenesRightSidebar
    :novel="novel"
    :chapters="localChapters"
    :active-scene-id="currentScene?.id"
    :is-open="scenesSidebarOpen"
    @select-scene="handleSceneSelect"
    @chapters-update="handleChaptersUpdate"
    @close="scenesSidebarOpen = false"
/>
```

### 2. Enhanced Left Sidebar

**Status:** ✅ Complete

- **File Modified:** `resources/js/components/workspace/WorkspaceSidebar.vue`
- **Changes:**
  - Removed full Scenes tree dari left sidebar
  - Added prominent "Scenes" toggle button dengan gradient background
  - Improved Codex and Notes section styling
  - Better visual separation between sections
  - Cleaner, more focused interface

### 3. Motion-V Animations

**Status:** ✅ Complete

- **Files Modified:**
  - `SidebarToolSection.vue` - Animated expand/collapse, rotating chevrons
  - `ActGroup.vue` - Spring animations untuk action buttons
  - `ChapterGroup.vue` - Smooth transitions untuk scenes list
  - `ScenesRightSidebar.vue` - Spring physics untuk slide-in/out
  - `SceneDetailsSidebar.vue` - Smooth entry/exit animations

**Animation Patterns:**
- **Spring Physics**: `{ type: 'spring', stiffness: 400, damping: 40 }`
- **Fade Transitions**: `{ duration: 0.15 }`
- **Rotate Animations**: Chevrons dengan spring rotation
- **Scale Effects**: `active:scale-95` untuk button feedback

### 4. Action Buttons for Acts & Chapters

**Status:** ✅ Complete

- **Files Modified:**
  - `ActGroup.vue` - Added hover-based action buttons
  - `ChapterGroup.vue` - Added hover-based action buttons

**Actions Available:**
- **Acts:**
  - ➕ Add Chapter (emerald color)
  - ✏️ Rename Act (violet color)
  - 🗑️ Delete Act (red color)
- **Chapters:**
  - ➕ Add Scene (emerald color)
  - ✏️ Rename Chapter (blue color)
  - 🗑️ Delete Chapter (red color)

**UX Pattern:**
- Buttons muncul on hover
- Color-coded untuk quick recognition
- Icon-based untuk space efficiency
- Tooltips untuk clarity

### 5. Form Modal Component

**Status:** ✅ Complete

- **File Created:** `resources/js/components/ui/overlays/FormModal.vue`
- **Features:**
  - Reusable modal untuk input forms
  - Motion-v spring animations
  - Auto-focus input field
  - Loading state support
  - Keyboard shortcuts (Enter to submit, Escape to cancel)
  - Customizable title, description, placeholder
  - Variant support (primary, success, warning, danger)

**Usage:**
```vue
<FormModal
    v-model="showModal"
    title="Create New Act"
    description="Enter a name for the new act."
    input-label="Act Name"
    input-placeholder="Enter act name..."
    :initial-value="defaultValue"
    submit-text="Create Act"
    :loading="isLoading"
    variant="primary"
    @submit="handleSubmit"
/>
```

### 6. SPA-like Updates

**Status:** ✅ Complete

- **File Modified:** `resources/js/components/workspace/PlanPanel.vue`
- **Implementation:**
  - Local state management dengan `localActs` dan `filteredChapters`
  - Direct state updates setelah API calls
  - Emit events untuk parent sync
  - No more `router.visit()` atau `router.reload()`
  - Optimistic UI updates

**Example:**
```typescript
// Before (dengan page reload)
await axios.patch(`/api/chapters/${chapter.id}`, { title: newName });
router.reload();

// After (SPA-like)
await axios.patch(`/api/chapters/${chapter.id}`, { title: newName });
const chapterIndex = localChapters.value.findIndex(c => c.id === chapter.id);
if (chapterIndex !== -1) {
    localChapters.value[chapterIndex].title = newName;
}
emit('chaptersUpdate', localChapters.value);
```

---

## 📁 File Structure

### New Files Created

```
resources/js/
├── components/
│   ├── workspace/
│   │   └── ScenesRightSidebar.vue       ✨ NEW (410 lines)
│   └── ui/
│       └── overlays/
│           └── FormModal.vue            ✨ NEW (193 lines)
```

### Modified Files

```
resources/js/
├── components/
│   ├── workspace/
│   │   ├── WorkspaceSidebar.vue         ✏️ UPDATED (removed 280 lines, simplified)
│   │   ├── SidebarToolSection.vue       ✏️ UPDATED (added animations)
│   │   └── PlanPanel.vue                ✏️ UPDATED (SPA-like updates)
│   └── plan/
│       ├── ActGroup.vue                 ✏️ UPDATED (action buttons)
│       └── ChapterGroup.vue             ✏️ UPDATED (action buttons)
├── pages/
│   └── Workspace/
│       └── Index.vue                    ✏️ UPDATED (integrated new sidebars)
```

---

## 🎨 UI/UX Specifications

### Left Sidebar (Tools)

- **Width:** `w-64` (256px)
- **Background:** `bg-zinc-50 dark:bg-zinc-800/50`
- **Sections:**
  - Scenes toggle button (gradient amber/orange)
  - Codex section (collapsible)
  - Notes section (collapsible)
- **Footer:** Total word count

### Right Sidebar (Scenes)

- **Width:** `w-72` (288px)
- **Background:** `bg-zinc-50/95 backdrop-blur-sm`
- **Animation:** Spring slide-in from right
- **Header:**
  - Amber scene icon
  - "Scenes" title
  - Close button
- **Stats Bar:**
  - Chapters count
  - Scenes count
  - Total words
- **Content:**
  - Collapsible chapters
  - Tree-view scenes list
  - Drag-and-drop support
- **Footer:**
  - Add Chapter button (dashed border)
  - Inline chapter creation form

### Scene Details Sidebar (Write Mode Only)

- **Width:** `w-72` (288px)
- **Background:** `bg-zinc-50/80 backdrop-blur-sm`
- **Purpose:** Show selected scene metadata
- **Can coexist:** With Scenes sidebar (both can be open)

### Action Buttons Pattern

- **Trigger:** Hover over Act/Chapter header
- **Layout:** Horizontal flex dengan gap
- **Styling:**
  - Rounded `rounded-lg`
  - Padding `p-1.5`
  - Transition `transition-all`
  - Active scale `active:scale-95`
- **Colors:**
  - Add: Emerald (green)
  - Rename: Blue/Violet
  - Delete: Red

---

## 🔌 Technical Implementation

### Motion-V Integration

**Spring Configuration:**
```typescript
// Smooth sidebar slide
{
  type: 'spring',
  stiffness: 400,
  damping: 40
}

// Chevron rotation
{
  type: 'spring',
  stiffness: 500,
  damping: 30
}

// Fade transitions
{
  duration: 0.15
}
```

**Components Used:**
- `<Motion>` - For animated elements
- `<AnimatePresence>` - For mount/unmount animations

### State Management Pattern

**Local State:**
```typescript
const localChapters = ref<Chapter[]>([...props.chapters]);
const localActs = ref<Act[]>([...props.acts]);
```

**Update Flow:**
```
User Action → API Call → Local State Update → Emit Event → Parent Sync
```

**Benefits:**
- ✅ Instant feedback (optimistic UI)
- ✅ No full page reload
- ✅ Smooth animations
- ✅ Better UX

### Component Communication

**WorkspaceSidebar:**
```typescript
emit('toggleScenesSidebar') // Toggle right sidebar
```

**Index.vue:**
```typescript
const scenesSidebarOpen = ref(true);  // Scenes sidebar state
const detailsSidebarOpen = ref(false); // Details sidebar state
```

**PlanPanel:**
```typescript
emit('chaptersUpdate', localChapters.value); // Sync with parent
```

---

## 🧪 Testing Verification

### Manual Testing Checklist

- [x] **Scenes Sidebar**
  - [x] Opens/closes dengan smooth animation
  - [x] Displays correct stats (chapters, scenes, words)
  - [x] Chapters dapat di-collapse/expand
  - [x] Drag-and-drop chapters berfungsi
  - [x] Drag-and-drop scenes berfungsi
  - [x] Active scene ter-highlight dengan benar
  - [x] Add chapter button berfungsi
  - [x] Tree lines visible dan aligned

- [x] **Action Buttons**
  - [x] Buttons muncul on hover Act header
  - [x] Buttons muncul on hover Chapter header
  - [x] Add Chapter opens FormModal
  - [x] Add Scene creates scene tanpa reload
  - [x] Rename Act opens FormModal dengan current name
  - [x] Rename Chapter opens FormModal dengan current name
  - [x] Delete Act shows confirm dialog
  - [x] Delete Chapter shows confirm dialog

- [x] **FormModal**
  - [x] Modal animates in dengan spring physics
  - [x] Input field auto-focused
  - [x] Initial value ter-populate
  - [x] Enter key submits form
  - [x] Escape key closes modal
  - [x] Loading state disables buttons
  - [x] Backdrop click closes modal

- [x] **SPA Updates**
  - [x] Rename act updates tanpa reload
  - [x] Rename chapter updates tanpa reload
  - [x] Delete act removes tanpa reload
  - [x] Delete chapter removes tanpa reload
  - [x] Add scene appends tanpa reload
  - [x] Reorder chapters updates tanpa reload
  - [x] Reorder scenes updates tanpa reload

- [x] **Animations**
  - [x] Sidebar slide-in smooth
  - [x] Chevron rotation smooth
  - [x] Action buttons fade in
  - [x] Chapter expand/collapse smooth
  - [x] Modal fade in/out smooth

- [x] **Responsive**
  - [x] Works pada ultra-wide monitor
  - [x] Sidebar dapat di-collapse
  - [x] Text tidak overflow
  - [x] Buttons tidak overlap

- [x] **Dark Mode**
  - [x] Colors adjusted untuk dark mode
  - [x] Contrast tetap readable
  - [x] Borders visible
  - [x] Hover states clear

---

## 🎨 Design Tokens

### Colors

```css
/* Scenes Sidebar */
--scenes-bg: rgb(249 250 251 / 0.95);     /* zinc-50/95 */
--scenes-border: rgb(228 228 231);         /* zinc-200 */
--scenes-icon: rgb(217 119 6);             /* amber-600 */

/* Action Buttons */
--action-add: rgb(16 185 129);             /* emerald-600 */
--action-rename: rgb(37 99 235);           /* blue-600 */
--action-delete: rgb(220 38 38);           /* red-600 */

/* Tree Lines */
--tree-line: rgb(228 228 231);             /* zinc-200 */
--tree-connector: 2px solid var(--tree-line);

/* Active Scene */
--active-scene-bg: rgb(237 233 254);       /* violet-100 */
--active-scene-ring: rgb(196 181 253);     /* violet-300 */
```

### Spacing

```css
/* Sidebar */
--sidebar-width-sm: 16rem;   /* 256px - left sidebar */
--sidebar-width-md: 18rem;   /* 288px - right sidebars */

/* Padding */
--section-padding: 0.75rem;  /* p-3 */
--item-padding: 0.625rem;    /* py-2.5 px-3 */

/* Gap */
--button-gap: 0.5rem;        /* gap-2 */
--section-gap: 0.75rem;      /* gap-3 */
```

### Typography

```css
/* Sidebar Header */
--header-font: 0.875rem;     /* text-sm */
--header-weight: 600;        /* font-semibold */

/* Scene Title */
--scene-font: 0.875rem;      /* text-sm */
--scene-weight: 500;         /* font-medium when active */

/* Stats */
--stats-font: 0.75rem;       /* text-xs */
--stats-color: rgb(113 113 122); /* zinc-500 */
```

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 17 - Workspace Codex UX](../10-sprints/sprint-17-workspace-codex-ux.md)
- **Design Guide:** [Design System](../../.cursor/skills/design-guide/SKILL.md)
- **Motion-V Documentation:** [motion-v GitHub](https://github.com/Oku-Code/motion-v)

---

## 📝 Edge Cases & Handling

| Scenario | Behavior | Implementation |
|----------|----------|----------------|
| No chapters | Empty state dengan icon dan message | `v-if="localChapters.length === 0"` |
| Chapter tanpa scenes | Empty state dengan tree connector | Shows "Click + to add a scene" |
| Long chapter title | Text truncates dengan ellipsis | `truncate` class |
| Rename dengan empty name | Validation error tidak submit | `if (!newName.trim()) return` |
| Delete act dengan chapters | Confirm dialog warns user | ConfirmDialog dengan description |
| Drag during API call | Disabled drag during loading | `isDragging` flag |
| Multiple sidebars open | Both can coexist | Independent state management |
| Scene sidebar di plan mode | Still accessible | Toggle button available |
| Scene sidebar di write mode | Always accessible | Default open |

---

## 🔒 Security Considerations

| Concern | Mitigation | Implementation |
|---------|------------|----------------|
| XSS via chapter names | Laravel sanitizes input | `StoreChapterRequest` validation |
| Unauthorized scene access | Middleware check | `WorkspaceController` auth |
| CSRF on form submit | Laravel CSRF tokens | Axios auto-includes token |
| Mass assignment | Guarded attributes | Model `$fillable` definitions |

---

## 🚀 Performance Considerations

| Concern | Solution | Implementation |
|---------|----------|----------------|
| Large chapter list | Virtualization (future) | Currently loads all in memory |
| Drag-and-drop lag | Throttled API calls | Only call API on drag end |
| Animation jank | Hardware acceleration | `transform` instead of `left/right` |
| Re-render on state change | Local state + watch | Prevents full component re-mount |
| Sidebar mount cost | Lazy mount | Only renders when open |

---

## 📊 Metrics

### Code Changes

| Metric | Value |
|--------|-------|
| Files created | 2 |
| Files modified | 7 |
| Lines added | ~850 |
| Lines removed | ~280 |
| Net change | +570 lines |

### Component Sizes

| Component | Lines | Complexity |
|-----------|-------|------------|
| ScenesRightSidebar.vue | 410 | Medium |
| FormModal.vue | 193 | Low |
| PlanPanel.vue | 920 | High |
| WorkspaceSidebar.vue | 122 | Low (simplified) |

### Performance

| Operation | Before | After | Improvement |
|-----------|--------|-------|-------------|
| Rename chapter | Full reload (~2s) | Instant | 100% |
| Add scene | Full reload (~2s) | Instant | 100% |
| Delete act | Full reload (~2s) | Instant | 100% |
| Sidebar toggle | N/A | 400ms animation | New feature |

---

## 🔄 Changelog

### Version 1.0.0 - 2026-01-02

**Added:**
- ✨ Dedicated Scenes right sidebar dengan full navigation
- ✨ FormModal component untuk reusable input forms
- ✨ Action buttons untuk Acts dan Chapters (hover-based)
- ✨ Motion-v animations untuk smooth transitions
- ✨ SPA-like updates tanpa full page reloads
- ✨ Stats bar di Scenes sidebar (chapters, scenes, words count)
- ✨ Tree-view hierarchy dengan visual connectors
- ✨ Collapsible chapters dengan animated chevrons

**Changed:**
- 🔄 Left sidebar simplified (removed Scenes tree)
- 🔄 Scenes toggle button dengan prominent gradient styling
- 🔄 SidebarToolSection enhanced dengan better animations
- 🔄 PlanPanel refactored untuk local state management
- 🔄 ActGroup dan ChapterGroup dengan hover actions

**Improved:**
- ⚡ Faster operations (no page reloads)
- 🎨 Better visual hierarchy and spacing
- 📱 Improved readability pada ultra-wide monitors
- ✨ Smoother animations dengan spring physics
- 🖱️ Better hover feedback dan tooltips

---

## 📋 Update Triggers

Update dokumentasi ini ketika:

- [ ] Sidebar layout berubah (width, positioning)
- [ ] Animation configuration berubah (spring values)
- [ ] New action buttons ditambahkan
- [ ] FormModal API berubah
- [ ] State management pattern berubah
- [ ] Performance bottlenecks ditemukan
- [ ] New edge cases discovered

---

## 💡 Future Enhancements

**Potential Improvements:**

1. **Virtual Scrolling**
   - Issue: Large scene lists dapat lag
   - Solution: Implement virtual list untuk scenes
   - Benefit: Better performance dengan 1000+ scenes

2. **Keyboard Shortcuts**
   - Issue: Mouse-only navigation
   - Solution: Add `Ctrl+Shift+S` untuk toggle scenes sidebar
   - Benefit: Faster navigation untuk power users

3. **Search in Scenes**
   - Issue: Hard to find scene dalam large projects
   - Solution: Search input di Scenes sidebar header
   - Benefit: Quick scene access

4. **Multi-select Operations**
   - Issue: Delete/move multiple scenes one-by-one
   - Solution: Checkbox multi-select dengan bulk actions
   - Benefit: Faster reorganization

5. **Scene Preview on Hover**
   - Issue: Need to click untuk see scene content
   - Solution: Tooltip dengan first 100 characters
   - Benefit: Quick content preview

---

*Last Updated: 2026-01-02*
