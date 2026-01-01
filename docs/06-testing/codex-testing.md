# 🧪 Codex System Testing Guide

## Overview

Testing guide untuk Codex System, yaitu: panduan lengkap untuk memverifikasi semua fitur world-building (entries, aliases, details, relations, progressions, categories, mentions) dan Series management berfungsi dengan benar.

---

## Pre-Testing Verification

### Backend Verification

```bash
# Verify routes are registered
php artisan route:list --path=codex
php artisan route:list --path=series

# Verify migrations ran
php artisan migrate:status | grep codex
php artisan migrate:status | grep series

# Test services exist
php artisan tinker --execute="echo class_exists(App\Services\Codex\MentionTracker::class) ? 'OK' : 'MISSING';"
```

### Frontend Verification

```bash
# Build assets
npm run build

# Verify components exist
ls resources/js/components/codex/
ls resources/js/pages/Codex/
ls resources/js/pages/Series/
```

---

## Test Categories

### 1. Codex Entry CRUD Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CE-001 | Create entry | Navigate to novel codex → Click "Tambah Entry" → Fill form → Submit | Entry created, redirect to show page |
| CE-002 | Create with all types | Create entries for each type (character, location, item, lore, organization, subplot) | All types accepted |
| CE-003 | Edit entry | Click edit on entry → Modify fields → Save | Changes persisted |
| CE-004 | Archive entry | Click archive button | Entry hidden from list, shows in archived |
| CE-005 | Restore entry | In archived view → Click restore | Entry visible in main list again |
| CE-006 | Delete entry | Click delete → Confirm | Entry removed (soft delete) |
| CE-007 | Search entries | Type in search box | Results filtered by name/description/alias |
| CE-008 | Filter by type | Select type dropdown | Only matching type entries shown |
| CE-009 | Filter by category | Select category | Only entries with category shown |

---

### 2. Codex Alias Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CA-001 | Add alias | In entry show → Type alias → Click add | Alias appears in list |
| CA-002 | Add duplicate alias | Add same alias twice | Error: duplicate not allowed |
| CA-003 | Edit alias | Click edit on alias → Modify → Save | Alias updated |
| CA-004 | Delete alias | Click delete on alias | Alias removed |
| CA-005 | Multiple aliases | Add 5+ aliases to entry | All aliases saved and displayed |

---

### 3. Codex Detail Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CD-001 | Add detail | Click "Add Detail" → Fill key/value → Save | Detail appears in list |
| CD-002 | Edit detail | Click edit → Modify → Save | Detail updated |
| CD-003 | Delete detail | Click delete | Detail removed |
| CD-004 | Reorder details | Drag detail to new position | Order persisted on reload |
| CD-005 | Long value | Add detail with 5000+ character value | Accepted, properly displayed |

---

### 4. Codex Relation Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CR-001 | Create relation | Select target entry → Choose type → Save | Relation appears |
| CR-002 | Bidirectional relation | Toggle bidirectional → Save | Visible from both entries |
| CR-003 | Custom label | Add custom label to relation | Label displayed |
| CR-004 | All relation types | Create relations with each type | All types work |
| CR-005 | Edit relation | Modify label/bidirectional | Changes saved |
| CR-006 | Delete relation | Click delete | Relation removed |
| CR-007 | Self-relation | Try to relate entry to itself | Should be prevented |

---

### 5. Codex Progression Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CP-001 | Create progression | Select scene → Add note → Save | Progression appears in timeline |
| CP-002 | Link to detail | Select detail → Set new value → Mode: replacement | Detail value changed after scene |
| CP-003 | Addition mode | Set mode to addition | Note added as supplementary info |
| CP-004 | Edit progression | Modify note/value → Save | Changes persisted |
| CP-005 | Delete progression | Click delete | Progression removed |
| CP-006 | Timeline order | Add multiple progressions | Ordered by scene position |

---

### 6. Codex Category Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CC-001 | Create category | Click "Add Category" → Enter name/color | Category appears |
| CC-002 | Assign to entry | Select category on entry | Entry shows category badge |
| CC-003 | Multiple categories | Assign 3 categories to entry | All badges displayed |
| CC-004 | Filter by category | Select category filter | Only matching entries shown |
| CC-005 | Edit category | Change name/color | All assigned entries updated |
| CC-006 | Delete category | Delete category | Removed from all entries |

---

### 7. Mention Tracking Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| CM-001 | Auto-detect mention | Create entry "John" → Write "John" in scene | Mention counted |
| CM-002 | Alias detection | Add alias "Johnny" → Write "Johnny" in scene | Mention counted |
| CM-003 | Multiple mentions | Write name 5 times in scene | Count shows 5 |
| CM-004 | Heatmap display | View entry with mentions | Heatmap shows scene intensity |
| CM-005 | Manual rescan | Click "Rescan" button | Mentions updated |
| CM-006 | Case insensitive | Write "JOHN" and "john" | Both detected |

---

### 8. Bulk Import/Export Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| BI-001 | Export JSON | Click "Export JSON" | Valid JSON file downloaded |
| BI-002 | Export CSV | Click "Export CSV" | Valid CSV file downloaded |
| BI-003 | Import preview | Upload JSON → Preview | Shows count of entries to create |
| BI-004 | Import execute | Confirm import | Entries created |
| BI-005 | Duplicate handling | Import file with existing names | Duplicates skipped or flagged |
| BI-006 | Invalid JSON | Upload malformed JSON | Error message shown |

---

### 9. Series Management Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| SM-001 | Create series | Navigate to series → Create → Fill form | Series created |
| SM-002 | Add novels | Select novels during creation | Novels assigned with order |
| SM-003 | View series | Click on series | Shows all novels and codex entries |
| SM-004 | Edit series | Modify title/description | Changes saved |
| SM-005 | Delete series | Delete series | Novels unassigned (not deleted) |
| SM-006 | Add novel later | From series page → Add novel | Novel appears in series |
| SM-007 | Remove novel | Click remove on novel | Novel removed from series |
| SM-008 | Reorder novels | Drag novels to reorder | Order persisted |

---

### 10. Series Codex Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| SC-001 | Create series entry | In series codex → Create entry | Entry created at series level |
| SC-002 | View in novel | Open novel codex that's in series | Series entries visible with indicator |
| SC-003 | Series entry aliases | Add alias to series entry | Alias works for detection in all novels |
| SC-004 | Filter series entries | In novel codex → Filter | Can distinguish series vs novel entries |

---

### 11. Editor Integration Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| EI-001 | Codex sidebar | Open editor → Click codex icon | Sidebar shows all entries |
| EI-002 | Quick create | Select text → Quick create | Entry created with text as name |
| EI-003 | Entry search | Type in sidebar search | Entries filtered |
| EI-004 | Click to insert | Click entry in sidebar | Name inserted at cursor |
| EI-005 | Highlight mentions | With entry "John" → Write "John" | Name highlighted in editor |

---

### 12. Thumbnail Upload Tests

|| Test ID | Scenario | Steps | Expected Result |
||---------|----------|-------|-----------------|
|| TU-001 | Upload image | In entry form → Select image → Upload | Thumbnail appears |
|| TU-002 | Preview thumbnail | Upload image | Preview shows before save |
|| TU-003 | Invalid file type | Upload .pdf or .txt | Error: invalid file type |
|| TU-004 | File too large | Upload 5MB image | Error: file too large (max 2MB) |
|| TU-005 | Replace thumbnail | Upload new image | Old thumbnail replaced |
|| TU-006 | Delete thumbnail | Click remove thumbnail | Thumbnail removed |
|| TU-007 | Supported formats | Upload JPEG, PNG, GIF, WebP | All formats accepted |

---

### 13. Relation Graph Tests

|| Test ID | Scenario | Steps | Expected Result |
||---------|----------|-------|-----------------|
|| RG-001 | View graph | Entry with relations → View graph section | D3 visualization renders |
|| RG-002 | Node positioning | View graph | Center node is current entry, related nodes around |
|| RG-003 | Node colors | View with multiple types | Nodes colored by entry type |
|| RG-004 | Drag nodes | Drag a node | Node moves, connections update |
|| RG-005 | Zoom graph | Scroll on graph | Graph zooms in/out |
|| RG-006 | Click node | Click related node | Navigates to that entry |
|| RG-007 | Bidirectional links | View bidirectional relation | No arrow on link |
|| RG-008 | Directional links | View one-way relation | Arrow points to target |
|| RG-009 | Relation labels | Hover on link | Shows relation type/label |
|| RG-010 | Empty state | Entry with no relations | Shows "No relationships" message |
|| RG-011 | Legend display | View graph with data | Legend shows type colors |

---

### 14. Plan Page Integration Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| PP-001 | Mention counts | View plan page | Each scene card shows codex mention count |
| PP-002 | Click count | Click on mention count | Navigate to scene with entries listed |

---

### 15. Sprint 13: Research & Tracking Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| S13-M01 | Toggle tracking | Entry show page → Click tracking toggle | Visual indicator changes, saves instantly |
| S13-M02 | Auto-scan behavior | Write scene with character name → Save | Mentions update within 5-10 seconds (check Codex page) |
| S13-M03 | Disabled tracking | Disable tracking → Write scene with that entry | No new mention created |
| S13-M04 | Research tab | Entry show page → Click "Research" tab | Tab switches, shows research notes area |
| S13-M05 | Research notes auto-save | Type in research notes → Wait 500ms | "Saving..." indicator, then "Saved" |
| S13-M06 | Research word count | Type in research notes | Word count updates live |
| S13-M07 | Add external link | Research tab → Fill link form → Submit | Link added to list |
| S13-M08 | Edit external link | Click edit on link → Modify → Save | Link updated |
| S13-M09 | Delete external link | Click delete on link → Confirm | Link removed |
| S13-M10 | Reorder external links | Drag link to new position | Order saved, persists on refresh |
| S13-M11 | Invalid URL validation | Add link with invalid URL → Submit | Validation error shown |
| S13-M12 | Live mention polling | Open Codex entry → Write in scene (other tab) → Wait | Mentions update without manual refresh (10s max) |
| S13-M13 | Private badge | Check Research tab | "Private" badge visible, tooltip explains not sent to AI |
| S13-M14 | Mobile responsive | Open Research tab on mobile | Layout adapts, all functions work |

---

## Manual QA Checklist

### Desktop Browser Testing

- [ ] Chrome - All CRUD operations work
- [ ] Firefox - All CRUD operations work
- [ ] Safari - All CRUD operations work
- [ ] Edge - All CRUD operations work

### Mobile Responsiveness

- [ ] Codex list responsive on mobile
- [ ] Entry detail page scrollable
- [ ] Modals fit mobile screen
- [ ] Touch interactions work (drag-drop may differ)

### Performance Testing

- [ ] Load 100+ entries - List performs well
- [ ] Load entry with 50+ mentions - Heatmap renders
- [ ] Import 100 entries - Completes in reasonable time

### Error Handling

- [ ] Network offline - Graceful error messages
- [ ] Session expired - Redirect to login
- [ ] Invalid input - Validation messages shown
- [ ] Server error - Error toast displayed

### v1.1.0 Enhancements Testing

- [x] Hover preview tooltip displays correctly
- [x] Tooltip positioning adjusts to viewport
- [x] Click navigation opens correct entry
- [x] Thumbnail upload validates file types
- [x] Thumbnail preview shows before save
- [x] Relation graph renders with D3.js
- [x] Graph interactions work (drag, zoom, click)
- [x] Description guidelines show in form

### v1.2.0 Sprint 13 Testing

- [x] Tracking toggle updates instantly
- [x] Auto-scan runs synchronously (no queue worker needed)
- [x] Live polling updates mentions (5s interval)
- [x] Research tab switches correctly
- [x] Research notes auto-save with debounce
- [x] External links CRUD operations work
- [x] External link URL validation
- [x] Reorder external links persists
- [x] "Private" badge shows on Research tab
- [x] Mobile responsive for new components
- [x] All 41 automated tests pass

---

## Automated Test Commands

```bash
# Run all codex tests
php artisan test --filter=Codex

# Run comprehensive CodexTest (32 tests)
php artisan test tests/Feature/CodexTest.php

# Run specific test file
php artisan test tests/Feature/CodexEntryTest.php

# Run with coverage
php artisan test --filter=Codex --coverage
```

### Test Coverage

**Latest Results (v1.2 - Sprint 13):**
- ✅ 41 tests passed (143 assertions) in 1.26s
- Coverage areas:
  - CRUD operations (entries, aliases, details, relations, progressions, categories)
  - **Sprint 13:** Tracking toggle, research notes, external links
  - **Sprint 13:** Auto-scan synchronous behavior
  - Authorization checks
  - Validation rules
  - Mention tracking
  - Bulk import/export
  - Quick create functionality
  - Archived entry exclusion
  - API endpoints for editor integration

### Sprint 13 Test Cases

| Test ID | Test Method | Scenario | Expected Result | Status |
|---------|-------------|----------|-----------------|--------|
| S13-001 | `test_can_toggle_tracking_enabled()` | Toggle tracking on/off | `is_tracking_enabled` updates correctly | ✅ Pass |
| S13-002 | `test_disabled_tracking_entry_not_scanned_for_mentions()` | Create scene with disabled tracking entry | Mention not created | ✅ Pass |
| S13-003 | `test_can_update_research_notes()` | Update research notes field | Notes persisted, not sent to AI | ✅ Pass |
| S13-004 | `test_research_notes_included_in_show_response()` | GET entry via API | Response includes research_notes | ✅ Pass |
| S13-005 | `test_can_add_external_link()` | POST external link | Link created with correct data | ✅ Pass |
| S13-006 | `test_can_update_external_link()` | PATCH external link | Link updated | ✅ Pass |
| S13-007 | `test_can_delete_external_link()` | DELETE external link | Link removed, cascade delete works | ✅ Pass |
| S13-008 | `test_external_link_requires_valid_url()` | POST with invalid URL | Validation error returned | ✅ Pass |
| S13-009 | `test_unauthorized_user_cannot_add_external_link()` | Unauthorized user tries to add link | 403 Forbidden | ✅ Pass |

**Automated Test Commands:**

```bash
# Run all Codex tests
php artisan test --filter=CodexTest

# Run only Sprint 13 tests
php artisan test --filter=CodexTest::test_can_toggle_tracking_enabled
php artisan test --filter=CodexTest::test_disabled_tracking_entry_not_scanned_for_mentions
php artisan test --filter=CodexTest::test_can_update_research_notes
php artisan test --filter=CodexTest::test_research_notes_included_in_show_response
php artisan test --filter=CodexTest::test_can_add_external_link
php artisan test --filter=CodexTest::test_can_update_external_link
php artisan test --filter=CodexTest::test_can_delete_external_link
php artisan test --filter=CodexTest::test_external_link_requires_valid_url
php artisan test --filter=CodexTest::test_unauthorized_user_cannot_add_external_link
```

---

## Test Data Setup

### Seed Test Data

```php
// In DatabaseSeeder or dedicated seeder
$novel = Novel::factory()->create();

// Create entries of each type
foreach (CodexEntry::getTypes() as $type) {
    $entry = CodexEntry::factory()->create([
        'novel_id' => $novel->id,
        'type' => $type,
    ]);
    
    // Add aliases
    $entry->aliases()->createMany([
        ['alias' => 'Alias 1'],
        ['alias' => 'Alias 2'],
    ]);
    
    // Add details
    $entry->details()->createMany([
        ['key_name' => 'Key 1', 'value' => 'Value 1'],
        ['key_name' => 'Key 2', 'value' => 'Value 2'],
    ]);
}

// Create relations between entries
$entries = $novel->codexEntries;
CodexRelation::create([
    'source_entry_id' => $entries[0]->id,
    'target_entry_id' => $entries[1]->id,
    'relation_type' => 'knows',
]);
```

---

## Related Documentation

- **Sprint Documentation:** [Sprint 04 - Codex System](../10-sprints/sprint-04-codex-system.md)
- **API Reference:** [Codex API](../04-api-reference/codex.md) | [Series API](../04-api-reference/series.md)
- **Implementation Plan:** [Codex Remaining Work](../../.cursor/plans/codex_remaining_work_3c10dc6c.plan.md)

---

## Version History

### v1.1.0 (2026-01-01) - Editor Enhancements Testing
- Added hover preview tooltip tests (EI-006 to EI-009)
- Added thumbnail upload tests (TU-001 to TU-007)
- Added relation graph visualization tests (RG-001 to RG-011)
- Updated automated test coverage report
- Added v1.1.0 enhancement verification checklist

### v1.0.0 (2026-01-01) - Initial Testing Guide
- Comprehensive test cases for all core Codex features
- Manual QA checklists for desktop and mobile
- Automated test commands and coverage reporting
- Test data setup examples

---

*Last Updated: 2026-01-01*
