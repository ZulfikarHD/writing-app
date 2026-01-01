# 🧪 Story Planning Testing Guide

## Overview

Panduan testing untuk Story Planning System yang mencakup Plan views (Grid, Matrix, Outline), scene cards, drag & drop, create from outline, dan semua planning features.

---

## Pre-Testing Requirements

- [ ] User terautentikasi
- [ ] Novel dengan minimal 1 chapter dan 2 scenes
- [ ] Codex entries (characters, locations) untuk Matrix testing
- [ ] Scene labels sudah dibuat

---

## Quick Verification Checklist

### Core Functionality
- [ ] Grid View renders dengan scene cards
- [ ] Matrix View shows scenes vs columns
- [ ] Outline View shows hierarchical list
- [ ] View switching works (Grid ↔ Matrix ↔ Outline)
- [ ] Search/filter scenes bekerja
- [ ] Drag & drop scenes bekerja

### CRUD Operations
- [ ] Create Act bekerja
- [ ] Create Chapter bekerja
- [ ] Create Scene bekerja
- [ ] Update/Rename bekerja
- [ ] Delete bekerja (dengan validation)

### Mobile Responsiveness
- [ ] Plan panel responsive di mobile
- [ ] Scene cards stack properly
- [ ] Touch drag & drop bekerja

> 📋 Full test plan di bawah ini.

---

## Test Cases

### TC-PLAN-001: Grid View Display

**Preconditions:** Novel dengan acts, chapters, dan scenes

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate ke Workspace → Plan mode | Grid view default displayed |
| 2 | Verify scene cards visible | Cards show title, summary preview, word count |
| 3 | Verify grouping | Scenes grouped by chapter, chapters by act |
| 4 | Click scene card | Navigate ke editor dengan scene tersebut |
| 5 | Hover scene card | Drag handle appears |

**Pass:** ☐

---

### TC-PLAN-002: View Switching

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "Matrix" di View Switcher | Matrix view displayed |
| 2 | Click "Outline" | Outline view displayed |
| 3 | Click "Grid" | Grid view displayed |
| 4 | Refresh page | Last selected view persisted |

**Pass:** ☐

---

### TC-PLAN-003: Matrix View - Entries Mode

**Preconditions:** Codex entries exist (characters, locations)

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Switch ke Matrix view | Matrix shows scenes as rows |
| 2 | Select "Entries" in Show dropdown | Codex entries as columns |
| 3 | Filter by "Characters" | Only character entries shown |
| 4 | Click cell intersection | Entry assigned/unassigned to scene |
| 5 | Hover cell | Details tooltip shown |

**Pass:** ☐

---

### TC-PLAN-004: Matrix View - POV Mode

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select "POV" in Show dropdown | POV characters as columns |
| 2 | Click cell untuk scene tanpa POV | POV dialog opens |
| 3 | Select character dan POV type | Cell shows checkmark |
| 4 | Verify scene card | POV indicator updated |

**Pass:** ☐

---

### TC-PLAN-005: Matrix View - Labels Mode

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select "Labels" in Show dropdown | Labels as columns |
| 2 | Click cell | Label toggled on/off |
| 3 | Verify scene card | Label badge updated |

**Pass:** ☐

---

### TC-PLAN-006: Outline View

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Switch ke Outline view | Hierarchical list displayed |
| 2 | Collapse Act header | Chapters hidden |
| 3 | Expand Act header | Chapters visible |
| 4 | Click scene summary | Inline edit mode |
| 5 | Edit dan blur | Auto-save, toast confirmation |

**Pass:** ☐

---

### TC-PLAN-007: Search & Filter

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Type di search input | Scenes filtered by title/summary |
| 2 | Select status filter | Only matching status shown |
| 3 | Select label filter | Only scenes with label shown |
| 4 | Clear all filters | All scenes visible |
| 5 | Verify result count | Count matches visible scenes |

**Pass:** ☐

---

### TC-PLAN-008: Drag & Drop - Scenes

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Drag scene dalam chapter | Visual drop indicator |
| 2 | Drop scene | Position updated, API called |
| 3 | Drag scene ke different chapter | Scene moves to new chapter |
| 4 | Verify positions | All positions correct |

**Pass:** ☐

---

### TC-PLAN-009: Drag & Drop - Chapters

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Drag chapter header | Visual feedback |
| 2 | Drop chapter | Chapter position updated |
| 3 | Verify chapter order | Order persisted |

**Pass:** ☐

---

### TC-PLAN-010: Add Act

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "Add Act" button | Prompt/modal for title |
| 2 | Enter title | Act created |
| 3 | Verify act list | New act appears |
| 4 | Verify position | Correct position (end) |

**Pass:** ☐

---

### TC-PLAN-011: Add Chapter

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "Add Chapter" | Prompt for title |
| 2 | Enter title | Chapter created |
| 3 | Verify act assignment | Assigned to last act (if exists) |
| 4 | Verify chapter list | New chapter appears |

**Pass:** ☐

---

### TC-PLAN-012: Add Scene

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "+" di chapter | New scene created |
| 2 | Verify scene | Appears in chapter |
| 3 | Verify position | At end of chapter |

**Pass:** ☐

---

### TC-PLAN-013: Delete Act (No Chapters)

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Create empty act | Act exists |
| 2 | Right-click → Delete | Confirmation dialog |
| 3 | Confirm | Act deleted |

**Pass:** ☐

---

### TC-PLAN-014: Delete Act (With Chapters) - Blocked

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Try delete act dengan chapters | Error message |
| 2 | Verify act | Still exists |

**Pass:** ☐

---

### TC-PLAN-015: Rename Act/Chapter

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Right-click Act → Rename | Inline edit |
| 2 | Enter new title | Title updated |
| 3 | Right-click Chapter → Rename | Inline edit |
| 4 | Enter new title | Title updated |

**Pass:** ☐

---

### TC-PLAN-016: Create from Outline - Custom

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "From Outline" | Modal opens |
| 2 | Enter text outline | Text accepted |
| 3 | Click "Preview" | Parsed structure shown |
| 4 | Click "Create" | Structure created |
| 5 | Verify acts/chapters | Match outline |

**Pass:** ☐

---

### TC-PLAN-017: Create from Outline - Template

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click "From Outline" | Modal opens |
| 2 | Select "Three Act Structure" | Template loaded |
| 3 | Preview structure | 3 acts shown |
| 4 | Create | Acts created with descriptions |

**Pass:** ☐

---

### TC-PLAN-018: Scene Card Settings

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click Settings (gear icon) | Settings panel opens |
| 2 | Change card size | Cards resize |
| 3 | Toggle show summary | Summary hidden/shown |
| 4 | Change grid axis | Layout swaps |
| 5 | Refresh page | Settings persisted |

**Pass:** ☐

---

### TC-PLAN-019: Scene Context Menu

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Right-click scene card | Context menu appears |
| 2 | Click "Edit Summary" | Inline edit mode |
| 3 | Click "Set POV" | POV selector opens |
| 4 | Click "Add Label" | Label selector opens |
| 5 | Click "Duplicate" | Scene duplicated |
| 6 | Click "Archive" | Scene archived |
| 7 | Click "Delete" | Confirmation → deleted |

**Pass:** ☐

---

### TC-PLAN-020: Archive & Restore Scene

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Archive scene | Scene disappears from list |
| 2 | Navigate ke archived | Scene visible |
| 3 | Restore scene | Scene back in chapter |
| 4 | Verify position | Correct position |

**Pass:** ☐

---

### TC-PLAN-021: Bulk POV Assignment (Matrix)

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Switch ke Matrix POV mode | POV columns visible |
| 2 | Select multiple scenes (checkbox) | Scenes selected |
| 3 | Click "Set POV" bulk action | POV dialog |
| 4 | Select character | All selected scenes updated |

**Pass:** ☐

---

### TC-PLAN-022: Scene Labels CRUD

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Create new label | Label appears in list |
| 2 | Assign label ke scene | Badge visible on card |
| 3 | Filter by label | Only labeled scenes shown |
| 4 | Remove label dari scene | Badge removed |
| 5 | Delete label | Removed from all scenes |

**Pass:** ☐

---

### TC-PLAN-023: Duplicate Scene

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Right-click → Duplicate | Scene duplicated |
| 2 | Verify title | "(Copy)" appended |
| 3 | Verify content | Same as original |
| 4 | Verify position | After original |
| 5 | Verify labels | Not copied (by design) |

**Pass:** ☐

---

### TC-PLAN-024: Word Count Display

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Verify scene card | Word count visible |
| 2 | Verify chapter header | Total word count |
| 3 | Verify act header | Total word count |
| 4 | Edit scene content | Counts update |

**Pass:** ☐

---

### TC-PLAN-025: Mobile Responsiveness

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Resize browser ke 375px | Layout adapts |
| 2 | Scene cards | Stack vertically |
| 3 | View Switcher | Remains accessible |
| 4 | Context menu | Opens properly |
| 5 | Drag & drop | Touch-friendly |

**Pass:** ☐

---

## Edge Cases

| Scenario | Expected Behavior |
|----------|-------------------|
| Empty novel (no chapters) | "Add Chapter" button prominent |
| Search with no results | "No scenes found" message |
| Drag scene to same position | No API call |
| Delete last scene in chapter | Chapter remains (empty) |
| Very long scene title | Truncated with ellipsis |
| 100+ scenes | Virtual scrolling / pagination |
| Offline mode | Local changes queued |

---

## Performance Benchmarks

| Metric | Target | Test Command |
|--------|--------|--------------|
| Grid view load (50 scenes) | < 500ms | DevTools Performance |
| Matrix view load | < 800ms | DevTools Performance |
| Drag & drop response | < 100ms | DevTools Performance |
| Search debounce | 300ms | Input → Filter timing |

---

## API Test Cases (Backend)

### Test: Matrix Data Builder

```php
public function test_matrix_returns_correct_structure(): void
{
    $novel = Novel::factory()->create();
    $chapter = Chapter::factory()->for($novel)->create();
    $scene = Scene::factory()->for($chapter)->create();
    $entry = CodexEntry::factory()->for($novel)->create(['type' => 'character']);
    
    $response = $this->actingAs($novel->user)
        ->getJson("/api/novels/{$novel->id}/plan/matrix?show=entries");
    
    $response->assertOk()
        ->assertJsonStructure([
            'scenes',
            'columns',
            'matrix'
        ]);
}
```

### Test: Create from Outline

```php
public function test_create_from_outline_creates_structure(): void
{
    $novel = Novel::factory()->create();
    
    $response = $this->actingAs($novel->user)
        ->postJson("/api/novels/{$novel->id}/plan/from-outline", [
            'outline' => "Act 1\n  Chapter 1\n    Scene 1"
        ]);
    
    $response->assertCreated();
    
    $this->assertDatabaseHas('acts', ['novel_id' => $novel->id, 'title' => 'Act 1']);
    $this->assertDatabaseHas('chapters', ['title' => 'Chapter 1']);
    $this->assertDatabaseHas('scenes', ['title' => 'Scene 1']);
}
```

### Test: Scene Reorder

```php
public function test_scene_reorder_updates_positions(): void
{
    $chapter = Chapter::factory()->create();
    $scene1 = Scene::factory()->for($chapter)->create(['position' => 0]);
    $scene2 = Scene::factory()->for($chapter)->create(['position' => 1]);
    
    $response = $this->actingAs($chapter->novel->user)
        ->postJson("/api/chapters/{$chapter->id}/scenes/reorder", [
            'order' => [$scene2->id, $scene1->id]
        ]);
    
    $response->assertOk();
    
    $this->assertEquals(0, $scene2->fresh()->position);
    $this->assertEquals(1, $scene1->fresh()->position);
}
```

---

## Regression Tests

Setelah setiap perubahan, pastikan:

- [ ] Existing scenes tidak kehilangan data
- [ ] Positions tetap consistent
- [ ] Labels tidak hilang
- [ ] POV assignments preserved
- [ ] Codex mentions tidak terganggu

---

*Last Updated: 2026-01-02*
