# 🐛 Bug Fix: Scene Navigation Not Working in Workspace

**Date:** 2026-01-02  
**Priority:** Critical  
**Status:** ✅ Fixed  
**Reporter:** User  
**Affected Versions:** All versions before this fix

---

## 🎯 Summary

Ketika user memilih scene dari right sidebar di workspace (e.g., Scene 1 pada Chapter 3), editor tidak navigate ke scene tersebut dan tetap menampilkan scene yang sama (Scene 1 Chapter 1), yaitu: navigation tidak berfungsi, content tidak update, dan user tidak bisa edit scene yang berbeda.

---

## 🐛 Problem Description

### User-Reported Issue

> "Please help me find a cause and fix it, why when I choose a scene from right bar on workspace chapter, eg when I chose scene 1 on chapter 3 it doesnt move me to that scene? I still in scene 1 chapter 1"

### Observed Behavior

1. User clicks Scene 1 di Chapter 3 dari right sidebar
2. URL berubah ke `/novels/{novel}/workspace/{scene}`
3. Browser logs menunjukkan Inertia visit succeeded
4. **NAMUN** editor content tetap menampilkan Scene 1 Chapter 1
5. Edits yang dilakukan ter-save ke scene yang SALAH

### Expected Behavior

1. User clicks Scene 1 di Chapter 3
2. URL changes to correct scene ID
3. Editor content updates untuk show scene yang dipilih
4. Word count updates
5. Auto-save saves to correct scene ID

---

## 🔍 Root Cause Analysis

### Issue 1: WritePanel Not Watching for Scene Changes

**Problem:** `WritePanel.vue` component tidak memiliki watcher untuk `scene` prop. Ketika user memilih scene baru, prop berubah tapi internal state (`content`, `currentScene`, `wordCount`) tidak update.

**Evidence:**
```vue
<!-- WritePanel.vue - BEFORE FIX -->
<script setup>
const content = ref(props.scene?.content || null);
const currentScene = ref<Scene | null>(props.scene);
// ❌ NO WATCHER for props.scene changes!
</script>
```

**Impact:** Editor menampilkan stale content dari scene sebelumnya.

### Issue 2: Auto-Save Using Static Scene ID

**Problem:** `useAutoSave` composable initialized dengan static scene ID. Ketika scene changes, auto-save tetap saves content ke scene ID yang lama.

**Evidence:**
```typescript
// useAutoSave.ts - BEFORE FIX
export function useAutoSave({
    sceneId,  // ❌ Static number, tidak reactive!
    debounceMs = 500,
    onSaved
}) {
    // Save always uses initial sceneId
    axios.put(`/api/scenes/${sceneId}`, { content });
}
```

**Impact:** User edits scene yang baru tapi content ter-save ke scene yang lama, causing data corruption.

### Issue 3: Missing Backend Validation

**Problem:** `WorkspaceController` tidak verify bahwa scene belongs to the novel sebelum displaying it.

**Evidence:**
```php
// WorkspaceController.php - BEFORE FIX
public function show(Request $request, Novel $novel, ?Scene $scene = null): Response
{
    // ❌ No validation that scene belongs to novel!
    $activeScene = $scene;
}
```

**Impact:** Potential security issue - user bisa access scenes dari novels yang bukan miliknya.

---

## ✅ Solution Implemented

### Fix 1: Add Scene Change Watcher to WritePanel

**File:** `resources/js/components/workspace/WritePanel.vue`

**Added:**
```typescript
// Watch for scene changes and update content
watch(
    () => props.scene?.id,
    (newSceneId, oldSceneId) => {
        // Only update if scene ID actually changed
        if (newSceneId !== oldSceneId && props.scene) {
            currentScene.value = props.scene;
            content.value = props.scene.content || null;
            wordCount.value = props.scene.word_count || 0;
            
            // Update editor content
            if (editorRef.value?.editor) {
                if (props.scene.content) {
                    editorRef.value.editor.commands.setContent(props.scene.content);
                } else {
                    // Set empty content if scene has no content
                    editorRef.value.editor.commands.setContent({
                        type: 'doc',
                        content: [{ type: 'paragraph' }],
                    });
                }
            }
        }
    }
);
```

**Explanation:** Watcher monitors `props.scene?.id` dan updates internal state + TipTap editor content ketika scene changes.

### Fix 2: Make Auto-Save Scene ID Reactive

**File:** `resources/js/composables/useAutoSave.ts`

**Before:**
```typescript
export function useAutoSave({
    sceneId,  // number
    ...
}) {
    // Static scene ID
}
```

**After:**
```typescript
import type { Ref } from 'vue';

export function useAutoSave({
    sceneId,  // number | Ref<number> - Accept both!
    ...
}) {
    // Compute scene ID - handle both static and reactive
    const computedSceneId = computed(() => {
        return typeof sceneId === 'number' ? sceneId : (sceneId as Ref<number>).value;
    });
    
    // Use computedSceneId in save operation
    await axios.put(`/api/scenes/${computedSceneId.value}`, {
        content: currentContent,
    });
}
```

**Usage in WritePanel:**
```typescript
const currentSceneId = computed(() => currentScene.value?.id || 0);
const { saveStatus, triggerSave, forceSave } = useAutoSave({
    sceneId: currentSceneId,  // Reactive computed!
    debounceMs: 500,
    onSaved: (newWordCount) => {
        wordCount.value = newWordCount;
        emit('wordCountUpdate', newWordCount);
    },
});
```

**Explanation:** Auto-save sekarang accepts reactive ref dan automatically uses current scene ID ketika saving.

### Fix 3: Add Backend Scene Validation

**File:** `app/Http/Controllers/WorkspaceController.php`

**Added:**
```php
public function show(Request $request, Novel $novel, ?Scene $scene = null): Response
{
    // Ensure user owns this novel
    if ($novel->user_id !== $request->user()->id) {
        abort(403);
    }

    // If scene is provided, verify it belongs to this novel
    if ($scene) {
        $scene->load('chapter');
        if ($scene->chapter->novel_id !== $novel->id) {
            abort(404, 'Scene not found in this novel');
        }
    }
    
    // ... rest of the method
}
```

**Explanation:** Validates scene ownership sebelum displaying. Prevents unauthorized access ke scenes dari different novels.

---

## 📁 Files Changed

| File | Type | Description |
|------|------|-------------|
| `resources/js/components/workspace/WritePanel.vue` | ✏️ UPDATED | Added scene change watcher, updated TipTap content on scene change |
| `resources/js/composables/useAutoSave.ts` | ✏️ UPDATED | Made scene ID accept reactive refs, compute scene ID dynamically |
| `app/Http/Controllers/WorkspaceController.php` | ✏️ UPDATED | Added scene validation to ensure scene belongs to novel |

---

## 🧪 Testing

### Test Cases

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| TC-01 | Click Scene 1 Chapter 1 | Editor shows Scene 1 Chapter 1 content | ✅ Pass |
| TC-02 | Click Scene 2 Chapter 1 | Editor shows Scene 2 Chapter 1 content | ✅ Pass |
| TC-03 | Click Scene 1 Chapter 3 | Editor shows Scene 1 Chapter 3 content | ✅ Pass |
| TC-04 | Edit scene, switch scene | Content saved to correct scene | ✅ Pass |
| TC-05 | Empty scene | Editor shows empty paragraph | ✅ Pass |
| TC-06 | Invalid scene ID | Returns 404 error | ✅ Pass |
| TC-07 | Scene from different novel | Returns 404 error | ✅ Pass |
| TC-08 | Word count updates | Word count reflects new scene | ✅ Pass |

### Manual Testing Checklist

- [x] Click different scenes in same chapter
- [x] Click scenes in different chapters
- [x] Verify editor content updates correctly
- [x] Verify word count updates
- [x] Edit scene, switch to another scene
- [x] Verify auto-save saves to correct scene
- [x] Test with empty scenes
- [x] Test browser back/forward navigation
- [x] Verify URL reflects current scene

---

## 🎯 Verification Commands

```bash
# Check routes exist
php artisan route:list --path=workspace

# Expected output:
# GET|HEAD       novels/{novel}/workspace workspace.show
# GET|HEAD       novels/{novel}/workspace/{scene} workspace.scene

# Test scene validation with tinker
php artisan tinker --execute="
\$novel = App\Models\Novel::first();
\$scene = App\Models\Scene::first();
\$controller = new App\Http\Controllers\WorkspaceController();
// Validation happens in controller show() method
"
```

---

## 📊 Impact Analysis

### Before Fix

- ❌ Scene navigation tidak berfungsi
- ❌ Editor menampilkan wrong scene content
- ❌ Auto-save saves to wrong scene (data corruption risk)
- ❌ No backend validation (security risk)
- ❌ User frustration karena cannot switch scenes

### After Fix

- ✅ Scene navigation works perfectly
- ✅ Editor updates content correctly
- ✅ Auto-save saves to correct scene
- ✅ Backend validates scene ownership
- ✅ Better user experience
- ✅ No data corruption
- ✅ Improved security

---

## 📊 Data Flow (After Fix)

```
┌─────────────────────────────────────────────────────────────┐
│  1. User clicks Scene 3 in Chapter 2                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  2. Inertia.visit('/novels/{novel}/workspace/{scene}')      │
│     - only: ['activeScene']                                 │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  3. WorkspaceController validates:                          │
│     - Novel belongs to user ✅                              │
│     - Scene belongs to novel ✅                             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  4. Server returns activeScene data                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  5. currentScene ref updates in Workspace/Index.vue         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  6. WritePanel watcher detects scene.id change              │
│     - Updates currentScene.value                            │
│     - Updates content.value                                 │
│     - Updates wordCount.value                               │
│     - Calls editor.commands.setContent()                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  7. Editor displays new scene content                       │
│     - Auto-save configured with new scene ID (reactive)     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 19 - Performance Mode](../10-sprints/sprint-19-performance-mode.md)
- **API Routes:** Run `php artisan route:list --path=workspace`
- **Controller:** `app/Http/Controllers/WorkspaceController.php`
- **Component:** `resources/js/components/workspace/WritePanel.vue`
- **Composable:** `resources/js/composables/useAutoSave.ts`

---

## 📝 Lessons Learned

### For Future Reference

1. **Always Watch Props Changes**: Ketika component receives data via props yang bisa change, WAJIB add watcher untuk update internal state
2. **Reactive IDs in Composables**: Untuk composables yang handle CRUD operations, accept both static dan reactive IDs
3. **Backend Validation is Critical**: Always validate resource ownership di backend, even if frontend does validation
4. **Test Scene Switching**: Scene navigation adalah core feature, test thoroughly dengan multiple scenarios

### Prevention Checklist

- [ ] Add watchers untuk props yang represent "active" items
- [ ] Make composables accept reactive refs untuk dynamic IDs
- [ ] Add backend validation untuk resource ownership
- [ ] Test navigation between items dalam list
- [ ] Verify auto-save targets correct resource after navigation

---

## 🎯 Success Criteria

✅ **All Achieved:**
- Scene navigation works correctly in all scenarios
- Editor content updates when scene changes
- Auto-save targets correct scene
- Backend validates scene ownership
- No data corruption risk
- Improved security
- Better user experience

---

*Last Updated: 2026-01-02*
