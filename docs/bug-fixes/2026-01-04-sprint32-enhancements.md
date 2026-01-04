# 🐛 Bug Fixes & Enhancements - Sprint 32

**Date:** 2026-01-04  
**Sprint:** Sprint 32  
**Category:** Writing Panel, UI/UX, State Management  
**Status:** ✅ Resolved

---

## Overview

Sprint 32 mencakup beberapa critical bug fixes dan UX enhancements untuk writing panel, focusing pada dark mode compatibility, content persistence, dan user experience improvements.

---

## Bug Fixes

### 🐛 BUG-32.1: Dark Mode Text Color Issue

**Severity:** 🔴 High  
**Component:** TipTapEditor  
**Reported:** Internal testing  
**Status:** ✅ Fixed

#### Problem Description
Saat dark mode aktif, text di writing panel tetap berwarna hitam (default text color), making it invisible against dark background. Users tidak bisa read atau edit content di dark mode.

#### Root Cause
TipTapEditor component menggunakan `prose prose-zinc dark:prose-invert` classes, tapi missing explicit text color classes untuk dark mode. Browser default text color (black) not overridden untuk dark mode state.

#### Impact
- **User Impact:** High - Users tidak bisa use editor di dark mode
- **Frequency:** 100% - Affects all users dengan dark mode preference
- **Workaround:** Switch to light mode temporarily

#### Solution
Added explicit text color classes ke TipTapEditor attributes:
- Light mode: `text-zinc-900` (dark text)
- Dark mode: `dark:text-zinc-100` (light text)

#### Files Changed
```
resources/js/components/editor/TipTapEditor.vue
```

#### Code Changes
```typescript
// Before
editorProps: {
    attributes: {
        class: 'prose prose-zinc dark:prose-invert max-w-none focus:outline-none min-h-[calc(100vh-16rem)] px-4 py-8 md:px-8 lg:px-16',
    },
},

// After
editorProps: {
    attributes: {
        class: 'prose prose-zinc dark:prose-invert max-w-none focus:outline-none min-h-[calc(100vh-16rem)] px-4 py-8 md:px-8 lg:px-16 text-zinc-900 dark:text-zinc-100',
    },
},
```

#### Testing
- [x] Text visible di light mode (dark text on light background)
- [x] Text visible di dark mode (light text on dark background)
- [x] Contrast ratios meet WCAG AA standards
- [x] Smooth transition antara light/dark mode
- [x] No layout shift saat toggle theme

#### Verification
```bash
# Visual inspection
1. Open workspace → Write panel
2. Type text di editor
3. Toggle dark mode
4. Verify text fully visible dengan good contrast
```

---

### 🐛 BUG-32.2: Write Panel Content Reversion on Panel Switch

**Severity:** 🔴 Critical  
**Component:** Workspace Index, WritePanel  
**Reported:** User reported via transcript  
**Status:** ✅ Fixed

#### Problem Description
Saat user edit content di write panel kemudian switch ke panel lain (chat, plan, codex) dan kembali ke write panel, edited content revert ke previous state. Content successfully saved ke backend (verified via database), tapi UI displays stale data from initial page load.

#### User Journey (Broken)
```
1. User edit text di write panel: "New content here"
2. Auto-save triggers (shows save indicator)
3. User switches to "Chat" panel
4. User switches back to "Write" panel
5. ❌ Text shows old content, not "New content here"
6. User refreshes page
7. ✅ Text shows correct "New content here"
```

#### Root Cause Analysis
1. **WritePanel Component Lifecycle:**
   - WritePanel unmounted saat user switches panel mode
   - When user returns to write mode, WritePanel remounted
   - Component initializes content from `props.scene.content`

2. **Stale Props:**
   - `props.scene` comes from Inertia page props
   - Page props set on initial page load
   - No refetch triggered saat mode changes
   - Therefore, `props.scene.content` contains OLD content from initial load

3. **Auto-Save Success But UI Mismatch:**
   - Auto-save correctly saves to backend
   - Backend has latest content
   - But frontend props never refreshed
   - UI displays props data (stale) instead of backend data (fresh)

#### Impact
- **User Impact:** Critical - Data loss perceived (though data actually saved)
- **Frequency:** 100% - Every panel switch causes issue
- **User Trust:** Severe - Users lose confidence in auto-save
- **Workaround:** Hard refresh (Ctrl+Shift+R) after every edit

#### Solution Strategy
Refetch activeScene data from server via Inertia saat user switches back to write mode, ensuring WritePanel always mounts dengan fresh data.

#### Implementation
```typescript
// Workspace/Index.vue - handleModeChange function

// Before (Broken)
const handleModeChange = (newMode: WorkspaceMode) => {
    setMode(newMode);
    updateUrl();
};

// After (Fixed)
const handleModeChange = (newMode: WorkspaceMode) => {
    setMode(newMode);
    
    // Refetch scene data saat kembali ke write mode
    if (newMode === 'write' && currentScene.value) {
        router.visit(route('workspace', { 
            scene: currentScene.value.id 
        }), {
            only: ['activeScene'],      // Only refetch activeScene prop
            preserveScroll: true,        // Maintain scroll position
            preserveState: true,         // Keep other state intact
            replace: true,               // Don't add browser history entry
        });
    }
    
    updateUrl();
};
```

#### Key Implementation Details

1. **Selective Refetch:**
   - `only: ['activeScene']` - Only refetch scene data, not entire page props
   - Minimizes network traffic dan re-render scope

2. **State Preservation:**
   - `preserveScroll: true` - User scroll position maintained
   - `preserveState: true` - Other component states (sidebar, etc.) not reset

3. **History Management:**
   - `replace: true` - No new browser history entry
   - User can use back button without unexpected behavior

4. **Conditional Execution:**
   - Only triggers untuk 'write' mode
   - Only if scene exists (`currentScene.value`)
   - No unnecessary requests untuk other mode switches

#### Files Changed
```
resources/js/pages/Workspace/Index.vue
```

#### Testing
- [x] Edit content di write panel
- [x] Auto-save completes (verify indicator)
- [x] Switch to Chat panel
- [x] Switch back to Write panel
- [x] ✅ Content shows latest edit (not reverted)
- [x] Switch to Plan panel
- [x] Switch back to Write panel  
- [x] ✅ Content still correct
- [x] Edit more content
- [x] Switch panels multiple times
- [x] ✅ Content always shows latest
- [x] Verify no browser history spam (back button works correctly)
- [x] Verify scroll position maintained after refetch

#### Verification Commands
```bash
# Database verification
php artisan tinker --execute="
\$scene = App\Models\Scene::find(1);
echo 'Content: ' . substr(\$scene->content, 0, 100);
"

# Compare dengan UI content di browser
# Should match after panel switch
```

---

### 🐛 BUG-32.3: ModelSelector No Auto-Fetch Models

**Severity:** 🟡 Medium  
**Component:** ModelSelector  
**Reported:** Internal testing  
**Status:** ✅ Fixed

#### Problem Description
Saat user punya default AI connection configured, available models tidak automatically fetched saat ModelSelector component mounted. User must manually interact dengan dropdown untuk trigger fetch, resulting di empty dropdown on first click.

#### User Experience (Broken)
```
1. User inserts Scene Beat
2. Beat block shows dengan model selector
3. User clicks model dropdown immediately
4. ❌ Dropdown empty (no models listed)
5. User waits few seconds
6. User clicks dropdown again
7. ✅ Models now available
```

#### Expected Behavior
```
1. User inserts Scene Beat
2. ModelSelector auto-fetches models di background
3. User clicks model dropdown
4. ✅ Models immediately available (no delay)
```

#### Root Cause
ModelSelector component set default connection di `onMounted`, tapi tidak explicitly call `fetchModels()` after setting default. Component menunggu user interaction untuk trigger fetch.

#### Solution
Explicitly call `fetchModels()` after setting default connection di `onMounted` hook.

#### Files Changed
```
resources/js/components/ai/ModelSelector.vue
```

#### Code Changes
```typescript
// Added explicit fetchModels() call
onMounted(async () => {
    if (defaultConnection.value) {
        selectedConnectionId.value = defaultConnection.value.id;
        await fetchModels(); // ✨ NEW - Explicitly fetch models
    }
});
```

#### Testing
- [x] Configure default AI connection di settings
- [x] Navigate to Scene Beat atau prose generation
- [x] Open model selector dropdown immediately
- [x] ✅ Models visible without delay
- [x] No empty dropdown issue
- [x] Loading indicator shows during fetch (if applicable)

---

### 🐛 BUG-32.4: Prose Generation Wayfinder Dependency Error

**Severity:** 🔴 High  
**Component:** useProseGeneration composable  
**Reported:** Browser console error  
**Status:** ✅ Fixed

#### Problem Description
Prose generation failing dengan error: `TypeError: Cannot read properties of undefined (reading 'definition')`. Error caused by dependency pada Wayfinder-generated action functions yang broken atau incompletely generated.

#### Browser Console Error
```
Failed to fetch prose generation options: TypeError Cannot read properties of undefined (reading 'definition')
    at useProseGeneration.ts:45
    at ProseGenerationController.options (generated/actions.ts:123)
```

#### Root Cause
`useProseGeneration.ts` menggunakan Wayfinder-generated action functions:
```typescript
// Broken code
import { ProseGenerationController } from '@/actions/...';

// Usage
const options = await ProseGenerationController.options();
```

Wayfinder code generation incomplete atau out of sync dengan actual backend routes, causing runtime errors.

#### Solution Strategy
Refactor `useProseGeneration.ts` untuk use direct URLs instead of Wayfinder actions, matching pattern dari working `useChat.ts` composable.

#### Files Changed
```
resources/js/composables/useProseGeneration.ts
```

#### Code Changes
```typescript
// Before (Broken - Wayfinder dependency)
import { ProseGenerationController } from '@/actions/...';

const fetchOptions = async () => {
    const response = await ProseGenerationController.options();
    // ...
};

// After (Fixed - Direct URLs)
const fetchOptions = async () => {
    const response = await fetch('/api/prose-generation/options', {
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
    });
    // ...
};

const generate = async (sceneId: number, options: ProseGenerationOptions) => {
    const eventSource = new EventSource(
        `/api/scenes/${sceneId}/generate-prose?${queryString}`,
        {
            withCredentials: true,
        }
    );
    // ...
};
```

#### Benefits
- ✅ No Wayfinder dependency
- ✅ Direct URL usage - more reliable
- ✅ Matches proven pattern dari `useChat.ts`
- ✅ Easier debugging (URLs visible)
- ✅ No code generation issues

#### Testing
- [x] Fetch prose generation options
- [x] ✅ No console errors
- [x] Available connections dan prompts loaded
- [x] Generate prose dengan scene beat
- [x] ✅ SSE streaming working
- [x] No "undefined reading 'definition'" error

---

## Enhancements

### ✨ ENH-32.1: Sidebar Text Size Improvement

**Category:** UI/UX Enhancement  
**Component:** SidebarToolSection, WorkspaceSidebar  
**Priority:** 🟢 Low (Quality of Life)  
**Status:** ✅ Implemented

#### Enhancement Description
Increased sidebar text size untuk better readability, especially di mobile devices. Previous text size (`text-sm` / 14px) too small untuk comfortable reading.

#### Changes
- Section titles: `text-sm` (14px) → `text-base` (16px)
- Improved font weight dan spacing
- Better visual hierarchy

#### Files Changed
```
resources/js/components/workspace/SidebarToolSection.vue
resources/js/components/workspace/WorkspaceSidebar.vue
```

#### Code Changes
```vue
<!-- Before -->
<span class="flex-1 text-sm font-semibold tracking-tight text-zinc-800 dark:text-zinc-200">
    {{ title }}
</span>

<!-- After -->
<span class="flex-1 text-base font-semibold tracking-tight text-zinc-800 dark:text-zinc-200">
    {{ title }}
</span>
```

#### Benefits
- ✅ Better readability di desktop
- ✅ Significantly better di mobile (16px touch-friendly)
- ✅ Reduced eye strain
- ✅ Better hierarchy untuk section titles

#### Testing
- [x] Sidebar titles visibly larger
- [x] No layout break atau overflow
- [x] Mobile viewport (375px) readability improved
- [x] Maintains visual balance dengan content

---

## Summary

### Bugs Resolved
| Bug ID | Title | Severity | Status |
|--------|-------|----------|--------|
| BUG-32.1 | Dark Mode Text Color | High | ✅ Fixed |
| BUG-32.2 | Content Reversion on Panel Switch | Critical | ✅ Fixed |
| BUG-32.3 | ModelSelector No Auto-Fetch | Medium | ✅ Fixed |
| BUG-32.4 | Prose Generation Wayfinder Error | High | ✅ Fixed |

### Enhancements Delivered
| ENH ID | Title | Priority | Status |
|--------|-------|----------|--------|
| ENH-32.1 | Sidebar Text Size Improvement | Low | ✅ Implemented |

### Impact Assessment

**User Experience:**
- 🟢 **Significantly Improved** - Critical bugs resolved, smoother workflow
- 🟢 **Accessibility** - Dark mode now fully usable
- 🟢 **Reliability** - Content persistence working correctly
- 🟢 **Readability** - Better text sizes across UI

**Developer Experience:**
- 🟢 **Reduced Debugging** - Direct URLs easier to debug than Wayfinder
- 🟢 **Code Quality** - More explicit, less "magic"
- 🟢 **Maintenance** - Fewer dependencies to manage

**Technical Debt:**
- ⚪ **Wayfinder Usage** - Reduced dependency (improvement)
- 🟢 **State Management** - Better pattern established untuk content persistence
- 🟢 **Dark Mode** - Consistent pattern untuk future components

---

## Verification Steps

### Manual QA Checklist
- [x] Dark mode text visible di all editor views
- [x] Content persists after panel switches (write → chat → write)
- [x] Auto-save working reliably
- [x] Model selector shows models immediately
- [x] Prose generation working without console errors
- [x] Sidebar text readable di mobile
- [x] No regressions di existing features

### Automated Tests
```bash
# Unit tests for composables
yarn test:unit composables/useProseGeneration

# Integration tests for Workspace
yarn test:integration pages/Workspace

# E2E test for content persistence
yarn test:e2e write-panel-persistence
```

---

## Related Documentation
- [Sprint 32 Documentation](../10-sprints/sprint-32-scene-beat-enhancements.md)
- [Sprint 32 Testing Guide](../06-testing/sprint-32-testing.md)
- [Scene Beat User Journey](../07-user-journeys/ai-writing-features/scene-beat-flow.md)

---

*Last Updated: 2026-01-04*  
*Bugs Fixed: 4*  
*Enhancements: 1*  
*Status: ✅ All Resolved*
