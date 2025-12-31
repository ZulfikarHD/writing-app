# 🧪 Testing Guide: Manuscript Editor

## Overview

Dokumen ini berisi panduan testing untuk fitur Manuscript Editor, yaitu: editor berbasis TipTap dengan auto-save, chapter/scene management, dan revision history.

---

## Test Summary

| Category | Total Tests | Coverage |
|----------|-------------|----------|
| Chapter CRUD | 8 | ✅ |
| Scene CRUD | 10 | ✅ |
| Auto-Save | 5 | ✅ |
| Revision History | 6 | ✅ |
| Authorization | 6 | ✅ |
| Frontend UI | 8 | ✅ |

---

## Backend Tests (PHPUnit)

### Chapter Tests

| Test ID | Scenario | Type | Expected Result |
|---------|----------|------|-----------------|
| CH-001 | List chapters for novel | Feature | Returns chapters with scenes |
| CH-002 | Create chapter with title | Feature | Chapter created with default scene |
| CH-003 | Create chapter without position | Feature | Auto-assigns max position + 1 |
| CH-004 | Update chapter title | Feature | Title updated successfully |
| CH-005 | Delete chapter | Feature | Chapter & scenes deleted (cascade) |
| CH-006 | Reorder chapters | Feature | Positions updated |
| CH-007 | Unauthorized access to other user's chapter | Feature | Returns 403 |
| CH-008 | Chapter word count calculation | Unit | Sum of all scene word counts |

### Scene Tests

| Test ID | Scenario | Type | Expected Result |
|---------|----------|------|-----------------|
| SC-001 | Create scene in chapter | Feature | Scene created with default content |
| SC-002 | Get scene content | Feature | Returns full scene with content |
| SC-003 | Update scene content (auto-save) | Feature | Content saved, word count updated |
| SC-004 | Update scene metadata | Feature | Metadata updated |
| SC-005 | Delete scene | Feature | Scene deleted permanently |
| SC-006 | Archive scene | Feature | archived_at set to current time |
| SC-007 | Restore archived scene | Feature | archived_at set to null |
| SC-008 | Reorder scenes | Feature | Positions updated |
| SC-009 | Scene word count calculation | Unit | Correct word count from TipTap JSON |
| SC-010 | Unauthorized access to other user's scene | Feature | Returns 403 |

### Revision Tests

| Test ID | Scenario | Type | Expected Result |
|---------|----------|------|-----------------|
| RV-001 | List revisions for scene | Feature | Returns revisions (max 50, desc by created_at) |
| RV-002 | Create manual revision | Feature | Revision created with current content |
| RV-003 | Restore from revision | Feature | Content restored, backup created |
| RV-004 | Auto-create revision on content change | Unit | (If implemented) Revision created |
| RV-005 | Revision content integrity | Unit | Content JSON matches original |
| RV-006 | Unauthorized revision access | Feature | Returns 403 |

---

## Frontend Tests (Manual QA)

### Editor Page Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| FE-001 | Open editor from dashboard | 1. Login 2. Click novel "Write" button | Editor opens with sidebar and content |
| FE-002 | Type in editor | 1. Focus editor 2. Type text | Text appears, word count updates |
| FE-003 | Auto-save on typing | 1. Type text 2. Wait 500ms | Save status shows "Saved", API called |
| FE-004 | Manual save Ctrl+S | 1. Type text 2. Press Ctrl+S | Immediate save, status updated |
| FE-005 | Undo/Redo | 1. Type text 2. Ctrl+Z 3. Ctrl+Y | Text undone/redone |
| FE-006 | Format text | 1. Select text 2. Click Bold/Italic/etc | Text formatted |
| FE-007 | Switch scene | 1. Click different scene in sidebar | Content changes, new scene loaded |
| FE-008 | Create chapter | 1. Click "Add Chapter" 2. Enter title | New chapter appears with default scene |

### Sidebar Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| SB-001 | Expand/collapse chapter | Click chapter title | Scenes shown/hidden |
| SB-002 | Active scene highlight | Click scene | Selected scene highlighted |
| SB-003 | Word count display | Write content | Total word count updates in footer |
| SB-004 | Status indicator | Check scene items | Colored dot shows status |
| SB-005 | Create scene in chapter | Click + button on chapter | New scene created, selected |

### Responsive Tests

| Test ID | Scenario | Expected Result |
|---------|----------|-----------------|
| RS-001 | Desktop (≥1024px) | Sidebar visible, editor full width |
| RS-002 | Tablet (768-1024px) | Sidebar toggleable, editor adapts |
| RS-003 | Mobile (<768px) | Sidebar overlay, full-screen editor |

---

## Quick Verification Checklist

### Happy Path

- [ ] Login → Dashboard → Click "Write" → Editor opens
- [ ] Type text → Word count increases → Save status shows "Saved"
- [ ] Create chapter → Chapter appears → Default scene created
- [ ] Create scene → Scene appears → Can select and write
- [ ] Switch scenes → Content switches correctly
- [ ] Press Ctrl+S → Immediate save
- [ ] Press Ctrl+Z → Undo works
- [ ] Format text (bold, italic) → Text formatted

### Edge Cases

- [ ] Empty novel → First chapter/scene auto-created
- [ ] Offline typing → Error status shown (when network returns)
- [ ] Rapid typing → Debounce prevents spam saves
- [ ] Very long content → Scrolling works, save succeeds
- [ ] Delete only chapter → Novel still accessible (empty state)

### Authorization

- [ ] Access other user's novel editor → 403 Forbidden
- [ ] Access other user's chapter API → 403 Forbidden
- [ ] Access other user's scene API → 403 Forbidden
- [ ] Not logged in → Redirect to login

---

## Test Commands

```bash
# Run all manuscript editor tests
php artisan test --filter=Editor
php artisan test --filter=Chapter
php artisan test --filter=Scene

# Run specific test file
php artisan test tests/Feature/EditorTest.php
php artisan test tests/Feature/ChapterControllerTest.php
php artisan test tests/Feature/SceneControllerTest.php

# Run full test suite
php artisan test
```

---

## Related Documentation

- **Sprint Documentation:** [Sprint 02 - Manuscript Editor](../10-sprints/sprint-02-manuscript-editor.md)
- **API Reference:** [Manuscript Editor API](../04-api-reference/manuscript-editor.md)

---

**Last Updated:** 2025-12-31
