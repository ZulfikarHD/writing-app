# 📦 Sprint 30: Sections System (FG-06.1)

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Duration:** 1 Sprint  
**Status:** ✅ Complete

---

## 📋 Sprint Goals

Implementasi Sections System untuk Scene Editor, yaitu: organizational blocks yang memungkinkan writers untuk memisahkan content types (prose/notes/alternatives), mengontrol AI visibility per section, dan manage multiple versions side-by-side dengan drag & drop reordering.

---

## ✨ Features Implemented

### 1. Core Section CRUD

- ✅ Create sections via `/section` slash command
- ✅ Read sections list from scene
- ✅ Update section properties (title, type, content, color, collapse, AI visibility)
- ✅ Delete sections with content
- ✅ Auto-save section content dengan debounce

### 2. Section Types

Implemented 4 section types dengan behavior berbeda:

| Type | Color | Export | AI Context | Use Case |
|------|-------|--------|------------|----------|
| **Content** | Indigo (#6366f1) | ✅ Yes | ✅ Included | Final manuscript prose |
| **Note** | Yellow (#eab308) | ❌ No | ❌ Excluded | Research, TODO, reminders |
| **Alternative** | Violet (#8b5cf6) | ❌ No | ❌ Excluded | Alternate versions, kitbashing |
| **Beat** | Green (#22c55e) | ❌ No | ✅ Included | Outlines, scene planning |

### 3. Section Actions

- ✅ **Reorder** - Drag & drop dengan sort_order persistence
- ✅ **Collapse/Expand** - Toggle dengan smooth animation + preview
- ✅ **AI Visibility Toggle** - Show/hide from AI context
- ✅ **Dissolve** - Unwrap section, keep content
- ✅ **Duplicate** - Copy section dengan "(Copy)" suffix
- ✅ **Change Type** - Quick type switching via badge
- ✅ **Change Color** - 12 predefined colors in picker
- ✅ **Copy Text** - Extract plain text ke clipboard
- ✅ **Word Count** - Real-time calculation per section

### 4. Editor Integration

- ✅ Slash commands menu (`/section`, `/note`, `/alternative`, `/beat`)
- ✅ TipTap custom node (SectionNode)
- ✅ Vue node view dengan header + content area
- ✅ Inline editing dalam section
- ✅ Drag handle visible on hover

### 5. AI Context Filtering

- ✅ `exclude_from_ai` flag per section
- ✅ Chat context builder respects flag
- ✅ TokenCounterService filters hidden sections
- ✅ Export service filters by type (content only)

---

## 📁 File Structure

### Backend Files

```
backend/
├── database/
│   ├── migrations/
│   │   └── 2026_01_04_100000_create_scene_sections_table.php    ✨ NEW
│   └── factories/
│       └── SceneSectionFactory.php                               ✨ NEW
│
├── app/
│   ├── Models/
│   │   ├── SceneSection.php                                      ✨ NEW
│   │   ├── Scene.php                                             ✏️ UPDATED (relationships)
│   │   └── ChatContextItem.php                                   ✏️ UPDATED (AI filtering)
│   │
│   ├── Http/Controllers/
│   │   └── SectionController.php                                 ✨ NEW
│   │
│   └── Services/Chat/
│       └── TokenCounterService.php                               ✏️ UPDATED (section filtering)
│
├── routes/
│   └── spa-api.php                                               ✏️ UPDATED (10 new routes)
│
└── tests/Feature/
    └── SceneSectionTest.php                                      ✨ NEW (23 tests)
```

### Frontend Files

```
frontend/resources/js/
├── extensions/
│   ├── SectionNode.ts                                            ✨ NEW
│   ├── SlashCommands.ts                                          ✨ NEW
│   ├── CodexHighlight.ts                                         (existing)
│   └── ...
│
├── components/editor/
│   ├── SectionNodeView.vue                                       ✨ NEW
│   ├── SectionHeader.vue                                         ✨ NEW
│   ├── SectionMenu.vue                                           ✨ NEW
│   ├── SlashCommandsList.vue                                     ✨ NEW
│   ├── TipTapEditor.vue                                          ✏️ UPDATED (extensions)
│   └── ...
│
├── composables/
│   ├── useSections.ts                                            ✨ NEW
│   └── ...
│
└── actions/App/Http/Controllers/
    └── SectionController.ts                                      ✨ GENERATED (wayfinder)
```

---

## 🔌 API Endpoints Summary

**Total Routes:** 10

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/scenes/{scene}/sections` | List all sections |
| POST | `/api/scenes/{scene}/sections` | Create section |
| POST | `/api/scenes/{scene}/sections/reorder` | Reorder sections |
| GET | `/api/sections/{section}` | Get section detail |
| PATCH | `/api/sections/{section}` | Update section |
| DELETE | `/api/sections/{section}` | Delete section |
| POST | `/api/sections/{section}/toggle-collapse` | Toggle collapse |
| POST | `/api/sections/{section}/toggle-ai-visibility` | Toggle AI |
| POST | `/api/sections/{section}/dissolve` | Unwrap section |
| POST | `/api/sections/{section}/duplicate` | Duplicate section |

> 📡 **Full API documentation:** [Scene Sections API](../04-api-reference/scene-sections.md)

---

## 🗄️ Database Schema

### `scene_sections` Table

```sql
CREATE TABLE scene_sections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    scene_id BIGINT UNSIGNED NOT NULL,
    type ENUM('content', 'note', 'alternative', 'beat') DEFAULT 'content',
    title VARCHAR(255) NULL,
    content LONGTEXT NULL,  -- TipTap JSON
    color VARCHAR(7) DEFAULT '#6366f1',
    is_collapsed BOOLEAN DEFAULT FALSE,
    exclude_from_ai BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    INDEX idx_scene_sort (scene_id, sort_order),
    INDEX idx_scene_type (scene_id, type)
);
```

**Key Design Decisions:**
- `type` ENUM untuk validation + performance
- `content` LONGTEXT untuk TipTap JSON (same as scenes.content)
- `color` VARCHAR(7) untuk hex color (#RRGGBB)
- `sort_order` untuk drag & drop reordering
- `exclude_from_ai` independent dari type (customizable)
- Cascade delete ketika scene dihapus

---

## 🔧 Technical Implementation

### 1. TipTap Custom Node

**SectionNode** (`extensions/SectionNode.ts`):
- Custom TipTap Node dengan content: `block+`
- Attributes: id, type, title, color, isCollapsed, excludeFromAi
- Commands: insertSection, toggleSectionCollapse, updateSectionAttributes, dissolveSection
- Keyboard shortcuts: Backspace prevention at section start
- Draggable enabled

**Vue Node View** (`components/editor/SectionNodeView.vue`):
- Renders section header + content area
- Manages local state (editing title, menu open)
- Word count computed from textContent
- Event handling untuk all section actions

### 2. Slash Commands System

**SlashCommands Extension** (`extensions/SlashCommands.ts`):
- TipTap Suggestion extension
- Trigger: `/` character
- Renders VueRenderer dengan Tippy.js popup
- Default commands: section, note, alternative, beat, headings, lists
- Keyboard navigation: Arrow keys + Enter/Escape

**SlashCommandsList Component** (`components/editor/SlashCommandsList.vue`):
- Popup menu dengan icon + description per command
- Hover selection + keyboard navigation
- Filtered by query string
- Color-coded icons per section type

### 3. AI Context Filtering

**Scene Model** (`app/Models/Scene.php`):
```php
public function getAiVisibleContent(): string
{
    if ($this->sections()->exists()) {
        // Build from AI-visible sections only
        return sections->where('exclude_from_ai', false)->text;
    }
    // Fallback: legacy scene.content
    return $this->content;
}
```

**TokenCounterService** (`app/Services/Chat/TokenCounterService.php`):
```php
public function buildSceneContextText(Scene $scene): string
{
    foreach ($scene->aiVisibleSections as $section) {
        $content .= $this->extractTextFromContent($section->content);
    }
    return $content;
}
```

### 4. Drag & Drop Reordering

**Frontend:**
- SectionNode dengan `draggable: true`
- Drag handle visible on group-hover
- Optimistic UI update
- API call dengan error rollback

**Backend:**
```php
public function reorder(Request $request, Scene $scene): JsonResponse
{
    foreach ($validated['sections'] as $sectionData) {
        SceneSection::where('id', $sectionData['id'])
            ->where('scene_id', $scene->id)
            ->update(['sort_order' => $sectionData['sort_order']]);
    }
    return response()->json(['success' => true]);
}
```

---

## 🧪 Testing

### Automated Tests

**Location:** `tests/Feature/SceneSectionTest.php`

**Coverage:** 23 tests, 88 assertions

| Test Category | Tests | Status |
|---------------|-------|--------|
| CRUD Operations | 9 | ✅ Pass |
| Reorder & Toggle | 3 | ✅ Pass |
| Dissolve & Duplicate | 2 | ✅ Pass |
| Authorization | 4 | ✅ Pass |
| Model Relationships | 3 | ✅ Pass |
| Word Count | 2 | ✅ Pass |

**Sample Tests:**
```php
test_user_can_create_section()
test_section_gets_default_color_based_on_type()
test_note_section_is_excluded_from_ai_by_default()
test_user_can_reorder_sections()
test_ai_visible_sections_filters_excluded_sections()
test_user_cannot_update_other_users_section()  // Authorization
```

### Manual Testing

> 📋 **Full test plan:** [Sections Testing](../06-testing/sections-testing.md)

**Quick Verification:**
- [ ] Slash commands create sections
- [ ] Drag & drop reordering works
- [ ] Collapse/expand with animation
- [ ] AI visibility toggle affects context
- [ ] Type changes update color/defaults
- [ ] Word count accurate
- [ ] Export excludes non-content sections
- [ ] Mobile responsive

---

## 🎨 UI/UX Design

### Design Principles

Mengikuti [design-standard.mdc](../../.cursor/rules/design-standard.mdc):
- **Clean & Compact UI** - Minimal visual noise
- **iOS-like Motion** - Spring physics, press feedback
- **Staggered Animations** - 50ms delays untuk sequential actions
- **Color Coding** - Immediate type recognition
- **Hover States** - Progressive disclosure (drag handle, menu)

### Component Hierarchy

```
SectionNodeView (NodeViewWrapper)
├── SectionHeader
│   ├── Collapse Toggle (chevron)
│   ├── Type Badge (color-coded)
│   ├── Title Input (inline edit)
│   ├── Word Count (on hover)
│   ├── AI Visibility Toggle (eye icon)
│   ├── Drag Handle (on hover)
│   └── Menu Button (3-dot)
│
├── NodeViewContent (when expanded)
│   └── TipTap Editor Instance
│
├── Collapsed Preview (when collapsed)
│   └── First 100 chars + "..."
│
└── SectionMenu (Teleport to body)
    ├── Word Count Display
    ├── Type Selector (4 types)
    ├── Color Picker (12 colors)
    ├── Copy Text Action
    ├── Dissolve Action
    └── Delete Action (danger)
```

### Responsive Behavior

**Desktop (>= 1024px):**
- Drag handle on hover
- Menu button on hover
- Full header with all controls

**Tablet (768px - 1023px):**
- Drag handle always visible
- Menu button always visible
- Slightly smaller header

**Mobile (< 768px):**
- All controls visible
- Touch-friendly tap targets (min 44x44px)
- Long-press for drag (tidak native yet, use button)

---

## 🔒 Security Considerations

| Concern | Mitigation | Implementation |
|---------|------------|----------------|
| **Unauthorized Access** | Scene ownership check | All endpoints verify `scene.chapter.novel.user_id` |
| **XSS via Title** | HTML escaping | Vue auto-escapes, max 255 chars |
| **Content Injection** | TipTap validation | Content stored as JSON, validated by TipTap |
| **Mass Assignment** | $fillable guard | Only specified fields allowed |
| **SQL Injection** | Eloquent ORM | Parameterized queries |
| **CSRF** | Laravel Sanctum | Session-based authentication |

**Authorization Flow:**
```php
// Every controller method:
if ($scene->chapter->novel->user_id !== $request->user()->id) {
    abort(403);
}
```

---

## 📊 Performance Considerations

### Database

**Optimizations:**
- Index pada `(scene_id, sort_order)` untuk ordering query
- Index pada `(scene_id, type)` untuk filter by type
- Eager loading: `$scene->with('sections')` untuk N+1 prevention

**Load Testing Results:**
- 10 sections/scene: Normal performance (< 100ms)
- 50 sections/scene: Acceptable performance (< 200ms)
- 100 sections/scene: Still usable (< 500ms)

### Frontend

**Optimizations:**
- Debounced auto-save (1000ms)
- Optimistic UI updates
- Lazy-loaded menu components
- Virtualization not needed (sections have limited height)

**Bundle Size:**
- SectionNode: ~5KB
- SlashCommands: ~4KB
- Total impact: +9KB gzipped

---

## 🐛 Edge Cases Handled

| Scenario | Handling |
|----------|----------|
| **Create section when scene has no content** | Section created at sort_order 0 |
| **Delete last section** | Scene remains valid (backward compatible) |
| **Reorder with network failure** | Optimistic UI rollback + error toast |
| **Collapse while editing** | Content preserved, save triggered |
| **Change type to same type** | No-op, no API call |
| **Dissolve section with unsaved edits** | Content saved before dissolve |
| **Duplicate section 10 times** | All get unique IDs + auto sort_order |
| **Drag section outside editor** | Cancelled, returns to original |
| **Very long title (>255 chars)** | Validation error, truncated |
| **Empty section export** | Skipped, not included in output |
| **All sections collapsed** | Still saveable, word count accurate |

---

## 🔄 Migration Strategy

**Backward Compatibility:**
- Existing scenes without sections: Continue working normally
- `Scene.content` still used if no sections exist
- Word count calculation: Legacy path preserved
- AI context building: Fallback to scene.content

**Forward Path:**
- New scenes: Can immediately use sections
- Existing scenes: Can add sections anytime
- No data migration required
- Hybrid mode supported (scene.content + sections)

**Future Considerations:**
- Optional: Migrate all scenes to sections-based model
- Optional: Deprecate scene.content field
- Not breaking: Legacy support permanent

---

## 📝 Known Limitations

| Limitation | Workaround | Future Enhancement |
|------------|------------|-------------------|
| **No undo after delete** | Ctrl+Z (TipTap undo) | Add "Trash" system |
| **No section nesting** | By design | Not planned |
| **12 predefined colors only** | Color picker limited | Add custom color input |
| **No section templates** | Copy existing section | Add template library |
| **Export filter by type only** | Cannot custom-exclude content sections | Add per-section export flag |

---

## 🔗 Related Documentation

- **API Reference:** [Scene Sections API](../04-api-reference/scene-sections.md)
- **Testing Guide:** [Sections Testing](../06-testing/sections-testing.md)
- **User Journeys:** [Sections User Journeys](../07-user-journeys/sections/)
- **Epic Planning:** [EPIC-06: Manuscript Editor](../../scrum/epic-planning/06-EPIC-manuscript-editor.md)

---

## 📚 References

- **Novelcrafter Sections:** https://www.novelcrafter.com/help/docs/write/sections
- **TipTap Custom Nodes:** https://tiptap.dev/guide/custom-extensions
- **Vue Composables:** https://vuejs.org/guide/reusability/composables.html

---

## 👥 Team

**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Project:** Writing App  
**Sprint Duration:** 1 Sprint

---

*Last Updated: 2026-01-04*
