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

### 12. Plan Page Integration Tests

| Test ID | Scenario | Steps | Expected Result |
|---------|----------|-------|-----------------|
| PP-001 | Mention counts | View plan page | Each scene card shows codex mention count |
| PP-002 | Click count | Click on mention count | Navigate to scene with entries listed |

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

---

## Automated Test Commands

```bash
# Run all codex tests
php artisan test --filter=Codex

# Run specific test file
php artisan test tests/Feature/CodexEntryTest.php

# Run with coverage
php artisan test --filter=Codex --coverage
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

---

*Last Updated: 2026-01-01*
