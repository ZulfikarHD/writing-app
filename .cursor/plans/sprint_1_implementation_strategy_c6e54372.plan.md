---
name: Sprint 1 Implementation Strategy
overview: Complete Sprint 1 (Foundation and Core Editor) by implementing the remaining features across US-001 (Rich Text Editor), US-002 (Scene/Chapter Structure), and US-009 (Theme/Display Options), building on the already-completed foundation work.
todos:
  - id: tiptap-extensions
    content: Add TipTap extensions (TextAlign, lists) to TipTapEditor.vue
    status: completed
  - id: toolbar-expansion
    content: Expand EditorToolbar with heading dropdown, list buttons, alignment buttons
    status: completed
  - id: settings-panel
    content: Create EditorSettingsPanel.vue with font/size/width/theme options
    status: completed
  - id: editor-settings-composable
    content: Create useEditorSettings.ts composable for state management
    status: completed
  - id: drag-drop
    content: Add vuedraggable to EditorSidebar for chapter/scene reordering
    status: completed
  - id: css-variables
    content: Add CSS variables for font family, size, line-height, editor width
    status: completed
  - id: feature-tests
    content: Write feature tests for new editor functionality
    status: completed
  - id: mobile-responsive
    content: Ensure mobile responsiveness with iOS design principles
    status: completed
---

# Sprint 1: Foundation and Core Editor - Implementation Strategy

## Current State Analysis

Based on my analysis of the codebase and scrum documentation:

**Already Completed (Foundation):**

- User authentication (register, login, logout)
- Dashboard with novel list (grid view, stats, empty state)
- Novel creation (basic form)
- Profile management
- Onboarding state tracking
- Basic TipTap editor with bold/italic/underline/strike
- Scene/Chapter structure with sidebar navigation
- Auto-save functionality (500ms debounce)
- Dark/Light theme toggle (localStorage only)
- Chapter/Scene CRUD APIs
- Scene archiving and revisions

---

## PHASE 1: Feature Understanding

### Sprint 1 User Stories

| ID | Story | Points | Owner | Consumer | Current Status |

|----|-------|--------|-------|----------|----------------|

| US-072 | User Account and Profile | 5 | Auth Pages | All Pages | COMPLETE |

| US-065 | Dashboard and Project List | 5 | Dashboard | User | COMPLETE |

| US-066 | Novel Creation and Setup | 5 | Create Page | Editor | COMPLETE (templates in Sprint 7) |

| US-001 | Rich Text Editor Dasar | 8 | Editor Page | Writer | PARTIAL - needs formatting |

| US-002 | Scene and Chapter Structure | 8 | EditorSidebar | Writer | PARTIAL - needs drag/drop |

| US-009 | Theme and Display Options | 5 | Settings Panel | All Pages | PARTIAL - needs UI |

---

## PHASE 2: Gap Analysis

### US-001: Rich Text Editor (8 pts) - GAP

**Implemented:**

- Basic formatting (bold, italic, underline, strike)
- Auto-save with debounce
- Word count display
- Undo/Redo

**Missing (P0 - Critical):**

- Heading levels (H1, H2, H3) in toolbar
- Bullet and numbered lists
- Text alignment (left, center, right, justify)

**Missing (P1 - Important):**

- Font selection dropdown (Serif, Sans-serif, Monospace)
- Font size slider/dropdown (12-24px)
- Paragraph spacing controls
- Line width/margin controls

**Files to Modify:**

- [`resources/js/components/editor/EditorToolbar.vue`](resources/js/components/editor/EditorToolbar.vue) - Add formatting buttons
- [`resources/js/components/editor/TipTapEditor.vue`](resources/js/components/editor/TipTapEditor.vue) - Add TipTap extensions

### US-002: Scene and Chapter Structure (8 pts) - GAP

**Implemented:**

- Sidebar with chapter/scene tree
- Expand/collapse chapters
- Scene selection and navigation
- Add chapter/scene buttons
- Scene status colors

**Missing (P0 - Critical):**

- Drag and drop reorder for scenes within chapter
- Drag and drop reorder for chapters

**Missing (P1 - Important):**

- Slash command "/scene" to add new scene from editor
- Scene context menu (right-click actions)

**Files to Modify:**

- [`resources/js/components/editor/EditorSidebar.vue`](resources/js/components/editor/EditorSidebar.vue) - Add VueDraggable
- [`resources/js/components/editor/TipTapEditor.vue`](resources/js/components/editor/TipTapEditor.vue) - Add slash commands extension

### US-009: Theme and Display Options (5 pts) - GAP

**Implemented:**

- Dark/Light mode toggle (useTheme composable)
- CSS variables for theming
- localStorage persistence

**Missing (P0 - Critical):**

- Settings panel UI in editor
- Font family selection (Serif, Sans-serif, Monospace, OpenDyslexic)
- Editor width options (narrow/medium/wide)

**Missing (P1 - Important):**

- Line height adjustment
- Sync theme preferences to server (user settings)

**Files to Create/Modify:**

- [`resources/js/components/editor/EditorSettingsPanel.vue`](resources/js/components/editor/EditorSettingsPanel.vue) - NEW
- [`resources/js/composables/useEditorSettings.ts`](resources/js/composables/useEditorSettings.ts) - NEW
- [`app/Http/Controllers/ProfileController.php`](app/Http/Controllers/ProfileController.php) - Add settings endpoint

---

## PHASE 3: Implementation Sequence

### Priority Matrix

**P0 (Must Complete - Sprint 1 Core):**

1. Extended toolbar formatting (headings, lists, alignment)
2. Drag and drop reordering
3. Settings panel with font/width options

**P1 (Should Complete - Better UX):**

1. Slash commands for scene creation
2. Font size adjustment
3. Line height controls
4. Server-side settings sync

**P2 (Can Defer - Enhancement):**

1. Paragraph spacing controls
2. Custom line width

### Implementation Order

```
1. TipTap Extensions (Day 1-2)
   ├── Add TextAlign extension
   ├── Add heading toggle support
   └── Add list extensions
   
2. EditorToolbar Expansion (Day 2-3)
   ├── Heading dropdown (H1/H2/H3)
   ├── List buttons (bullet/numbered)
   ├── Alignment buttons
   └── Settings gear button
   
3. Editor Settings Panel (Day 3-4)
   ├── Font family dropdown
   ├── Font size slider
   ├── Editor width toggle
   ├── Line height slider
   └── Theme toggle integration
   
4. Drag and Drop (Day 4-5)
   ├── Install vuedraggable
   ├── Scene reorder within chapter
   ├── Chapter reorder
   └── API calls for position updates
   
5. Testing and Polish (Day 6)
   ├── Feature tests for new functionality
   ├── Mobile responsiveness check
   └── Dark mode verification
```

---

## PHASE 4: Detailed Implementation

### 1. TipTap Extensions

Add to [`TipTapEditor.vue`](resources/js/components/editor/TipTapEditor.vue):

```typescript
// New imports needed
import TextAlign from '@tiptap/extension-text-align';
import BulletList from '@tiptap/extension-bullet-list';
import OrderedList from '@tiptap/extension-ordered-list';
import ListItem from '@tiptap/extension-list-item';

// Add to extensions array
TextAlign.configure({
    types: ['heading', 'paragraph'],
}),
```

### 2. EditorToolbar Enhancement

Add to [`EditorToolbar.vue`](resources/js/components/editor/EditorToolbar.vue):

- Heading dropdown (Paragraph, H1, H2, H3)
- List buttons (bullet, numbered)
- Alignment group (left, center, right, justify)
- Settings gear icon (opens EditorSettingsPanel)

### 3. EditorSettingsPanel (New Component)

Create [`resources/js/components/editor/EditorSettingsPanel.vue`](resources/js/components/editor/EditorSettingsPanel.vue):

- Slide-over panel from right
- Font Family: Georgia (Serif), Inter (Sans), JetBrains Mono, OpenDyslexic
- Font Size: 14px, 16px, 18px, 20px, 22px, 24px
- Line Height: 1.5, 1.6, 1.8, 2.0
- Editor Width: Narrow (640px), Medium (768px), Wide (896px)
- Theme: Light, Dark, System

### 4. useEditorSettings Composable

Create [`resources/js/composables/useEditorSettings.ts`](resources/js/composables/useEditorSettings.ts):

```typescript
interface EditorSettings {
    fontFamily: 'serif' | 'sans' | 'mono' | 'dyslexic';
    fontSize: number;
    lineHeight: number;
    editorWidth: 'narrow' | 'medium' | 'wide';
}
```

### 5. Drag and Drop Implementation

Update [`EditorSidebar.vue`](resources/js/components/editor/EditorSidebar.vue):

```bash
yarn add vuedraggable@next
```

- Wrap chapter list in `<draggable>`
- Wrap scene list in `<draggable>`
- Call reorder APIs on drag end

---

## PHASE 5: User Journeys

### Journey 1: Writer Formats Text

1. User opens editor at `/novels/{id}/write`
2. User selects text in TipTapEditor
3. User clicks **Bold** button in toolbar - text becomes bold
4. User clicks **H1** dropdown - text becomes heading
5. User clicks **Bullet List** - paragraph converts to list
6. Changes auto-save within 500ms

### Journey 2: Writer Reorders Scenes

1. User sees chapter/scene tree in EditorSidebar
2. User drags "Scene 3" above "Scene 2"
3. Draggable animates the move with spring physics
4. On drop: API call `POST /api/chapters/{id}/scenes/reorder`
5. Sidebar updates to show new order
6. Toast notification: "Scene order updated"

### Journey 3: Writer Customizes Editor

1. User clicks **gear icon** in toolbar
2. EditorSettingsPanel slides in from right
3. User selects "Sans-serif" font
4. Editor immediately reflects font change
5. User adjusts editor width to "Wide"
6. Content area expands with smooth transition
7. Settings persist in localStorage
8. Panel closes on outside click or X button

---

## PHASE 6: Database/API Changes

### No New Tables Required

Existing `users.settings` JSON column can store editor preferences:

```json
{
  "editor": {
    "fontFamily": "serif",
    "fontSize": 18,
    "lineHeight": 1.8,
    "editorWidth": "medium"
  },
  "theme": "system"
}
```

### API Endpoints (Existing)

- `PATCH /api/chapters/{chapter}` - update chapter position
- `POST /api/novels/{novel}/chapters/reorder` - reorder chapters
- `POST /api/chapters/{chapter}/scenes/reorder` - reorder scenes
- `PATCH /profile` - can extend to save settings

---

## PHASE 7: Files Summary

### New Files to Create

| File | Purpose | Priority |

|------|---------|----------|

| `resources/js/components/editor/EditorSettingsPanel.vue` | Settings slide-over panel | P0 |

| `resources/js/composables/useEditorSettings.ts` | Editor settings state management | P0 |

### Files to Modify

| File | Changes | Priority |

|------|---------|----------|

| `resources/js/components/editor/TipTapEditor.vue` | Add TextAlign, list extensions | P0 |

| `resources/js/components/editor/EditorToolbar.vue` | Add heading/list/align buttons | P0 |

| `resources/js/components/editor/EditorSidebar.vue` | Add vuedraggable for reordering | P0 |

| `resources/js/pages/Editor/Index.vue` | Integrate settings panel | P0 |

| `resources/css/app.css` | Add editor font/width CSS variables | P0 |

| `tests/Feature/EditorTest.php` | Add tests for new features | P1 |

### Dependencies to Add

```bash
yarn add vuedraggable@next @tiptap/extension-text-align
```

---

## PHASE 8: Mobile Considerations

Following the design guide's iOS principles:

- **Spring Physics**: Use motion-v for drag animations
- **Press Feedback**: Scale-down on toolbar button press (0.97 scale)
- **Staggered Animations**: Scene list entrance animations
- **Gesture Recognition**: Swipe to reveal scene actions on mobile
- **Settings Panel**: Full-screen modal on mobile, slide-over on desktop

---

## Definition of Done Checklist

- [ ] All P0 features implemented
- [ ] Dark mode works for all new components
- [ ] Mobile responsive (tested on 375px width)
- [ ] Feature tests passing
- [ ] `yarn run lint` passes
- [ ] `vendor/bin/pint --dirty` passes
- [ ] `yarn build` succeeds
- [ ] Manual testing on desktop and mobile complete
