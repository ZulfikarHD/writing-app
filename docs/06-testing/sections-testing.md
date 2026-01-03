# Testing Guide: Scene Sections System

**Feature:** FG-06.1 - Sections System  
**Sprint:** 30  
**Status:** ✅ Complete  
**Test Coverage:** 23 automated tests (100% pass)

---

## Overview

Panduan testing untuk Scene Sections System, yaitu: organizational blocks within scenes yang memungkinkan writers untuk memisahkan content types, mengontrol AI visibility, dan manage multiple versions.

---

## Automated Tests

### Feature Tests Location

```
tests/Feature/SceneSectionTest.php
```

**Test Results:** 23 tests, 88 assertions, 100% pass rate

### Test Coverage Summary

| Category | Tests | Coverage |
|----------|-------|----------|
| CRUD Operations | 9 tests | ✅ Complete |
| Reorder & Toggle | 3 tests | ✅ Complete |
| Dissolve & Duplicate | 2 tests | ✅ Complete |
| Authorization | 4 tests | ✅ Complete |
| Business Logic | 5 tests | ✅ Complete |

---

## Manual QA Test Checklist

### 🔍 Quick Verification

- [ ] **CRUD Operations**: Create, read, update, delete sections work
- [ ] **Slash Commands**: `/section` command creates new section
- [ ] **Drag & Drop**: Sections can be reordered via drag handle
- [ ] **Collapse/Expand**: Toggle works and persists
- [ ] **AI Visibility**: Toggle affects context filtering
- [ ] **Type Changes**: Section type changes update color and defaults
- [ ] **Word Count**: Calculated correctly from content
- [ ] **Color Picker**: All predefined colors available
- [ ] **Dissolve**: Content preserved when unwrapping
- [ ] **Duplicate**: Creates copy with "(Copy)" suffix
- [ ] **Mobile Responsive**: All interactions work on touch devices
- [ ] **Loading States**: Shown during API calls
- [ ] **Error Handling**: User-friendly error messages displayed

---

## Detailed Test Cases

### TC-01: Create Section

**Priority:** Critical  
**Type:** Automated + Manual

**Automated Test:**
```php
test_user_can_create_section()
test_section_gets_default_color_based_on_type()
test_note_section_is_excluded_from_ai_by_default()
test_content_section_is_included_in_ai_by_default()
test_section_gets_auto_incremented_sort_order()
```

**Manual Steps:**
1. Open scene in editor
2. Type `/section`
3. Press Enter
4. Verify new section appears with:
   - Default type badge "Content"
   - Indigo border color (#6366f1)
   - Empty content area
   - Collapsed toggle (expanded state)
   - AI visibility icon (eye open)
   - Drag handle
   - Menu button

**Expected Result:** ✅ Section created with correct defaults

**Edge Cases:**
- [ ] Create section when scene has no content
- [ ] Create section when scene already has 10+ sections
- [ ] Create section with very long title (255 chars)

---

### TC-02: Update Section Type

**Priority:** Critical  
**Type:** Manual

**Steps:**
1. Open scene with existing section
2. Click section type badge (e.g., "Content")
3. Select different type (e.g., "Note")
4. Verify:
   - Badge updates to "Note"
   - Border color changes to yellow (#eab308)
   - `exclude_from_ai` toggles to true (eye closed)

**Expected Result:** ✅ Type change updates all related properties

**Edge Cases:**
- [ ] Change type while section is collapsed
- [ ] Change type with unsaved content edits
- [ ] Rapid type switching

---

### TC-03: Drag & Drop Reorder

**Priority:** High  
**Type:** Automated + Manual

**Automated Test:**
```php
test_user_can_reorder_sections()
```

**Manual Steps:**
1. Open scene with 3+ sections
2. Grab drag handle on Section 1
3. Drag to position after Section 3
4. Release
5. Verify:
   - Section moves to new position
   - `sort_order` updates in database
   - UI reflects new order immediately
   - No visual glitches during drag

**Expected Result:** ✅ Sections reorder smoothly with correct persistence

**Edge Cases:**
- [ ] Drag first section to last position
- [ ] Drag last section to first position
- [ ] Cancel drag (ESC key)
- [ ] Drag on touch device

---

### TC-04: Toggle Collapse

**Priority:** Medium  
**Type:** Automated + Manual

**Automated Test:**
```php
test_user_can_toggle_section_collapse()
```

**Manual Steps:**
1. Open scene with section (expanded)
2. Click collapse button (chevron down)
3. Verify:
   - Content area hides
   - Chevron rotates -90deg
   - Preview text shows (first 100 chars)
   - Height animates smoothly
4. Click again to expand
5. Verify:
   - Content area shows
   - Chevron rotates back
   - Preview text hides

**Expected Result:** ✅ Collapse/expand works with smooth animation

**Edge Cases:**
- [ ] Collapse empty section
- [ ] Collapse very long section (1000+ words)
- [ ] Rapid collapse/expand toggling
- [ ] Collapse while editing content

---

### TC-05: AI Visibility Toggle

**Priority:** Critical  
**Type:** Automated + Manual

**Automated Test:**
```php
test_user_can_toggle_section_ai_visibility()
test_ai_visible_sections_filters_excluded_sections()
```

**Manual Steps:**
1. Open scene with section
2. Click AI visibility icon (eye open)
3. Verify:
   - Icon changes to eye-closed
   - `exclude_from_ai = true` in database
4. Open Chat/Workshop panel
5. Add scene to context
6. Verify:
   - Hidden section NOT included in context preview
   - Visible sections ARE included
7. Toggle back to visible
8. Verify context preview updates

**Expected Result:** ✅ AI visibility toggle affects context filtering

**Edge Cases:**
- [ ] Toggle AI visibility on "note" type (already excluded)
- [ ] All sections hidden - verify scene still usable
- [ ] Toggle while section is being edited

---

### TC-06: Section Menu Actions

**Priority:** Medium  
**Type:** Manual

**Steps:**
1. Open section menu (3-dot button)
2. Verify menu shows:
   - Word count display
   - Type selector (4 types)
   - Color picker (12 colors)
   - Copy Text action
   - Dissolve Section action
   - Delete Section (red, danger zone)

**Color Picker:**
3. Click "Change Color"
4. Select different color
5. Verify border color updates immediately

**Copy Text:**
6. Click "Copy Text"
7. Paste in external editor
8. Verify plain text extracted correctly

**Dissolve:**
9. Click "Dissolve Section"
10. Verify:
    - Section container removed
    - Content preserved in scene
    - No data loss

**Delete:**
11. Click "Delete Section"
12. Verify:
    - Section and content deleted
    - Confirmation prompt (if implemented)

**Expected Result:** ✅ All menu actions work correctly

---

### TC-07: Slash Commands

**Priority:** High  
**Type:** Manual

**Steps:**
1. Type `/` in editor
2. Verify command menu appears with:
   - Section
   - Note Section
   - Alternative Section
   - Beat Section
   - (other commands)
3. Type `/sec` to filter
4. Press Enter on "Section"
5. Verify new Content section created
6. Type `/note`
7. Press Enter
8. Verify new Note section created (yellow, excluded from AI)

**Expected Result:** ✅ Slash commands create sections with correct types

**Edge Cases:**
- [ ] ESC cancels command menu
- [ ] Arrow keys navigate menu
- [ ] Click outside closes menu
- [ ] Works at start/middle/end of document

---

### TC-08: Word Count Calculation

**Priority:** Low  
**Type:** Automated + Manual

**Automated Test:**
```php
test_section_word_count_is_calculated()
```

**Manual Steps:**
1. Create section
2. Type "This is a test sentence"
3. Verify word count shows "5 words"
4. Add paragraph: "Another sentence here"
5. Verify word count updates to "8 words"
6. Delete all content
7. Verify word count shows "0 words"

**Expected Result:** ✅ Word count accurate and updates in real-time

---

### TC-09: Authorization

**Priority:** Critical  
**Type:** Automated

**Automated Tests:**
```php
test_user_cannot_access_sections_from_other_users_scene()
test_user_cannot_create_section_in_other_users_scene()
test_user_cannot_update_other_users_section()
test_user_cannot_delete_other_users_section()
```

**Expected Result:** ✅ All authorization checks pass (403 Forbidden)

---

### TC-10: Export Behavior

**Priority:** High  
**Type:** Manual

**Steps:**
1. Create scene with:
   - 1 Content section (some text)
   - 1 Note section (some text)
   - 1 Alternative section (some text)
2. Export scene
3. Verify exported file only includes Content section
4. Verify Note and Alternative excluded

**Expected Result:** ✅ Only "content" type sections exported

---

## System QA Test Plan

### Desktop Testing

- [ ] **Chrome** - Windows 10+
- [ ] **Firefox** - Windows 10+
- [ ] **Safari** - macOS
- [ ] **Edge** - Windows 10+

### Mobile Testing

- [ ] **iOS Safari** - iPhone 12+
- [ ] **Chrome Mobile** - Android
- [ ] **Portrait orientation** - All interactions work
- [ ] **Landscape orientation** - Layout adapts

### Accessibility

- [ ] **Keyboard Navigation** - Tab through all controls
- [ ] **Screen Reader** - Sections announced correctly
- [ ] **Color Contrast** - Type badges readable
- [ ] **Focus Indicators** - Visible on all interactive elements

---

## Performance Testing

### Load Testing

- [ ] Scene with 1 section - Normal performance
- [ ] Scene with 10 sections - No lag
- [ ] Scene with 50 sections - Acceptable performance
- [ ] Scene with 100 sections - Still usable

### Content Testing

- [ ] Empty section (0 words) - Handles gracefully
- [ ] Small section (100 words) - Fast
- [ ] Medium section (1000 words) - Fast
- [ ] Large section (10,000 words) - Acceptable

---

## Regression Testing Checklist

**Run after any changes to:**
- TipTap editor core
- Scene save/load logic
- AI context building
- Export functionality

**Tests to run:**
- [ ] All automated tests pass
- [ ] Sections persist after page reload
- [ ] AI context filtering works
- [ ] Export excludes non-content sections
- [ ] Drag & drop still works
- [ ] Slash commands functional
- [ ] No console errors

---

## Known Issues & Limitations

| Issue | Severity | Status | Workaround |
|-------|----------|--------|------------|
| None | - | - | - |

---

## Test Data Setup

### Minimal Test Scene

```javascript
// Scene with 3 sections for testing
const testScene = {
  id: 1,
  title: "Test Scene",
  sections: [
    {
      id: 1,
      type: "content",
      title: "Opening",
      content: { /* TipTap JSON */ },
      sort_order: 0
    },
    {
      id: 2,
      type: "note",
      title: "Research Notes",
      content: { /* TipTap JSON */ },
      sort_order: 1
    },
    {
      id: 3,
      type: "alternative",
      title: "Alt Version",
      content: { /* TipTap JSON */ },
      sort_order: 2
    }
  ]
};
```

### Factory Usage

```php
// Create test sections
$section = SceneSection::factory()
    ->content()
    ->create(['scene_id' => $scene->id]);

$noteSection = SceneSection::factory()
    ->note()
    ->collapsed()
    ->create(['scene_id' => $scene->id]);
```

---

## Related Documentation

- **API Reference:** [Scene Sections API](../04-api-reference/scene-sections.md)
- **User Journeys:** [Sections User Journeys](../07-user-journeys/sections/)
- **Sprint Documentation:** [Sprint 30: Sections System](../10-sprints/sprint-30-sections-system.md)

---

*Last Updated: 2026-01-04*
