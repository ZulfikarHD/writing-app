# 🗺️ User Journeys: Sprint 15 - Codex Editor Integration

**Version:** 1.0.0  
**Date:** 2026-01-01  
**Sprint:** 15  
**Status:** ✅ Complete

---

## Overview

User journey documentation untuk Sprint 15 Codex V2 Enhancements yang berfokus pada editor integration dan UX improvements, yaitu: seamless progression creation, hover previews, quick codex creation, dan bulk operations.

---

## Journey 1: Add Progression from Editor (Desktop)

**Actor:** Writer (Alice)  
**Goal:** Track character development tanpa meninggalkan editor  
**Frequency:** High (multiple times per writing session)

### Flow Diagram

```
📝 Editor Page
    │
    ├─▶ [1] Type /progression di editor
    │   └─ Slash command dropdown muncul
    │
    ├─▶ [2] Select "Add Progression"
    │   └─ Modal opens dengan:
    │       • Dropdown: Codex entries
    │       • Scene ID: Pre-filled (current scene)
    │       • Progression note: Textarea
    │       • Mode: Addition/Replacement
    │
    ├─▶ [3] Select "Gandalf" dari dropdown
    │   └─ Entry selected
    │
    ├─▶ [4] Type: "Lost his staff in the battle"
    │   └─ Note entered
    │
    ├─▶ [5] Select mode: "Addition"
    │   └─ Mode selected
    │
    ├─▶ [6] Click "Save Progression"
    │   └─ API: POST /api/codex/{entry}/progressions
    │       • Auto-save success
    │       • Toast notification
    │       • Modal closes
    │
    └─▶ [7] Continue writing
        └─ Flow uninterrupted

✅ Result: Progression added tanpa context switch
```

### Alternative Flow: Keyboard Shortcut

```
📝 Editor Page
    │
    ├─▶ [1] Press Cmd+Shift+P (Mac) / Ctrl+Shift+P (Windows)
    │   └─ Same modal opens
    │
    └─▶ [2-6] Same as main flow
```

### Edge Cases

| Scenario | Behavior | User Experience |
|----------|----------|----------------|
| No codex entries exist | Modal shows "No entries found" message | Clear guidance to create entries first |
| Network error | Error toast with retry option | Data NOT lost (cached) |
| Cancel modal | Modal closes, no save | No unintended changes |

### Success Metrics

- ✅ Modal opens < 300ms
- ✅ Dropdown searchable
- ✅ Scene ID auto-filled
- ✅ Save completes < 500ms
- ✅ No page reload

---

## Journey 2: Hover Preview in Editor (Desktop)

**Actor:** Writer (Bob)  
**Goal:** Quick reference character details tanpa membuka codex page  
**Frequency:** Very High (dozens per session)

### Flow Diagram

```
📝 Editor Page (Scene dengan mentions)
    │
    ├─▶ [1] Mouse hover over "Gandalf" (highlighted mention)
    │   └─ Wait 300ms (debounce)
    │
    ├─▶ [2] Tooltip appears
    │   └─ Content:
    │       • 📷 Thumbnail (if exists)
    │       • Name: "Gandalf"
    │       • Type: Badge "Character"
    │       • Description: First 200 chars...
    │       • Key details (if configured):
    │         - Story Role: Protagonist
    │         - Occupation: Wizard
    │
    ├─▶ [3] Read information
    │   └─ Tooltip remains visible
    │
    ├─▶ [4] Click tooltip (optional)
    │   └─ Navigate to: /codex/{entry}
    │       • Opens in new tab
    │
    └─▶ [5] Move mouse away
        └─ Tooltip fades out (300ms animation)

✅ Result: Quick reference tanpa context switch
```

### Mobile Flow (Tap)

```
📱 Editor Page (Mobile)
    │
    ├─▶ [1] Tap "Gandalf" mention
    │   └─ Tooltip appears (same content)
    │
    ├─▶ [2] Read information
    │   └─ Tooltip stays visible
    │
    └─▶ [3] Tap outside OR tap X button
        └─ Tooltip closes

✅ Result: Touch-friendly preview
```

### Performance Considerations

| Metric | Target | Implementation |
|--------|--------|----------------|
| Tooltip appear delay | < 300ms | Debounced hover |
| API fetch time | < 200ms | Session cached |
| Tooltip render | < 100ms | Lazy component load |

---

## Journey 3: Quick Create from Selection (Desktop)

**Actor:** Writer (Carol)  
**Goal:** Build codex database sambil writing tanpa workflow interruption  
**Frequency:** Medium (new entries per chapter)

### Flow Diagram

```
📝 Editor Page
    │
    ├─▶ [1] Write: "The Council of Five gathered..."
    │   └─ First mention of "Council of Five"
    │
    ├─▶ [2] Select text: "Council of Five"
    │   └─ Text highlighted
    │
    ├─▶ [3] Press Cmd+Shift+C (or right-click menu)
    │   └─ Quick Create modal opens
    │       • Name: Pre-filled "Council of Five"
    │       • Type: Dropdown (default: organization)
    │       • Description: Textarea (optional)
    │
    ├─▶ [4] Confirm type: "organization"
    │   └─ Type selected
    │
    ├─▶ [5] Enter brief description (optional):
    │   └─ "The ruling council of five kingdoms"
    │
    ├─▶ [6] Click "Create Entry"
    │   └─ API: POST /api/novels/{novel}/codex/quick-create
    │       • Entry created
    │       • Success toast
    │       • Modal closes
    │       • **Text immediately highlighted as mention**
    │       • Editor mentions auto-refreshed
    │
    └─▶ [7] Continue writing
        └─ New entry available for AI context

✅ Result: Seamless codex building while writing
```

### Mobile Flow (Selection Menu)

```
📱 Editor Page (Mobile)
    │
    ├─▶ [1] Long-press to select text
    │   └─ Text highlighted
    │
    ├─▶ [2] SelectionActionMenu appears (floating button)
    │   └─ Button: "📝 Create Codex Entry"
    │
    ├─▶ [3] Tap button
    │   └─ Quick Create modal opens (responsive)
    │
    └─▶ [4-7] Same as desktop flow

✅ Result: Mobile-optimized creation
```

### Event Flow (Technical)

```
Editor → TipTap Extension (QuickCreateCodex)
           │
           └─▶ Emit: 'codex:open-quick-create-modal'
                     { selectedText: "..." }
                     │
                     └─▶ Vue Component: QuickCreateModal
                           │
                           └─▶ API Call: POST /codex/quick-create
                                 │
                                 └─▶ Dispatch: 'codex-entry-created'
                                       │
                                       └─▶ Editor listens & refreshes mentions
```

---

## Journey 4: Bulk Create Entries (Setup Phase)

**Actor:** Writer (David)  
**Goal:** Populate codex database di awal novel planning  
**Frequency:** Low (once per novel, occasional updates)

### Flow Diagram

```
📊 Codex Index Page
    │
    ├─▶ [1] Click "Bulk Create" button
    │   └─ Modal opens dengan:
    │       • Textarea: Multi-line input
    │       • Format hint: "Name | Type | Description"
    │       • Example rows shown
    │
    ├─▶ [2] Type/Paste entries:
    │   └─ Input:
    │       """
    │       Alice | character | Young witch protagonist
    │       Bob | character | Alice's mentor wizard
    │       Merlin Academy | location | Magic school
    │       Staff of Light | item | Alice's weapon
    │       """
    │
    ├─▶ [3] Click "Preview"
    │   └─ API: POST /bulk-create (preview=true)
    │       • Returns:
    │         - ✅ Valid: 4 entries
    │         - ⚠️ Warnings: 0
    │         - ❌ Errors: 0
    │       • Table shows parsed entries
    │
    ├─▶ [4] Review preview table
    │   └─ Columns:
    │       • Line #
    │       • Name
    │       • Type
    │       • Description
    │       • Status (✅ Valid / ⚠️ Warning / ❌ Error)
    │
    ├─▶ [5] Click "Create All"
    │   └─ API: POST /bulk-create (preview=false)
    │       • Progress indicator (if many)
    │       • Success toast: "4 entries created"
    │       • Modal shows summary:
    │         - Created: 4
    │         - Skipped: 0
    │         - Links to new entries
    │
    └─▶ [6] Click "Done"
        └─ Redirect to: Codex Index
            • New entries visible in list

✅ Result: Rapid database population
```

### Error Handling Flow

```
📊 Codex Index (Bulk Create Modal)
    │
    ├─▶ [1] Enter invalid input:
    │   └─ """
    │       Alice | character | Valid entry
    │       Bob charcter Missing pipe
    │       Carol | charcter | Typo in type
    │       # Comment line
    │       
    │       Dave | location | Valid entry
    │       """
    │
    ├─▶ [2] Click "Preview"
    │   └─ API returns:
    │       • Valid: 2 (Alice, Dave)
    │       • Errors: 2
    │         - Line 2: "Invalid format (missing |)"
    │         - Line 3: "Invalid type 'charcter'. Did you mean 'character'?"
    │       • Warnings: 0
    │
    ├─▶ [3] Preview table shows:
    │   └─ Line 2: ❌ Red highlight dengan error message
    │       Line 3: ❌ Red highlight dengan suggestion
    │       Line 4: 💬 Gray (comment, ignored)
    │       Line 5: ℹ️ Gray (empty, ignored)
    │
    ├─▶ [4] Fix errors di textarea:
    │   └─ Update lines 2-3
    │
    ├─▶ [5] Click "Preview" again
    │   └─ Validation passes
    │
    └─▶ [6] Click "Create All"
        └─ All 4 entries created

✅ Result: Clear error guidance
```

### Mobile Responsive Design

| Element | Desktop | Mobile |
|---------|---------|--------|
| Textarea | 600px wide, 10 rows | Full width, 6 rows |
| Preview table | Fixed layout | Horizontal scroll |
| Buttons | Inline (Preview / Create) | Stack vertical |
| Format hint | Full example | Compact version |

---

## Journey 5: Duplicate Entry (Quick Setup)

**Actor:** Writer (Eve)  
**Goal:** Reuse entry structure untuk similar characters  
**Frequency:** Low-Medium (when creating similar entries)

### Flow Diagram

```
📄 Codex Entry Detail Page (Alice)
    │
    ├─▶ [1] Click "⋯" menu → "Duplicate"
    │   └─ Confirmation (optional):
    │       "Duplicate 'Alice' with all details?"
    │
    ├─▶ [2] Click "Confirm"
    │   └─ API: POST /api/codex/{entry}/duplicate
    │       • Loading state (button disabled)
    │       • Deep clone:
    │         - Name: "Alice (Copy)"
    │         - Type: "character"
    │         - Description: Same
    │         - Thumbnail: Same
    │         - Aliases: Cloned
    │         - Details: Cloned
    │         - Progressions: Cloned (no scene links)
    │
    ├─▶ [3] Redirect to new entry
    │   └─ URL: /codex/{new_entry_id}
    │       • Success toast: "Entry duplicated"
    │
    ├─▶ [4] Edit duplicated entry
    │   └─ Modify:
    │       • Name: "Alice (Copy)" → "Eve"
    │       • Description: Update untuk Eve
    │       • Keep same details structure
    │
    └─▶ [5] Save changes
        └─ Entry now tailored untuk Eve

✅ Result: Faster setup untuk similar entries
```

### Use Case Examples

| Original Entry | Duplicate For | Benefit |
|----------------|---------------|---------|
| "Alice" (Protagonist) | "Eve" (Sister) | Same detail fields (appearance, backstory, etc) |
| "Sword of Fire" | "Sword of Ice" | Same item properties, different values |
| "Merlin Academy" | "Warlock College" | Same location details structure |

---

## Journey 6: Swap Relation Direction (Fix Mistake)

**Actor:** Writer (Frank)  
**Goal:** Correct relation direction tanpa delete-recreate  
**Frequency:** Low (occasional fixes)

### Flow Diagram

```
📄 Codex Entry Detail Page (Alice)
    │
    ├─▶ [1] View Relations section
    │   └─ Current relation:
    │       Alice --[mentor]--> Bob
    │       (Alice is mentored by Bob)
    │
    ├─▶ [2] Realize: Relation is backwards!
    │   └─ Should be: Bob --[mentor]--> Alice
    │
    ├─▶ [3] Click "↔ Swap" button next to relation
    │   └─ Confirmation: "Swap direction of this relation?"
    │
    ├─▶ [4] Click "Confirm"
    │   └─ API: POST /api/codex/relations/{id}/swap
    │       • source_entry_id ↔ target_entry_id
    │       • Success toast
    │
    ├─▶ [5] Relations list updates
    │   └─ Now shows:
    │       Bob --[mentor]--> Alice
    │       (Bob mentors Alice)
    │
    └─▶ [6] Verify on Bob's page
        └─ Navigate to Bob's entry
            • Relation appears correctly

✅ Result: Quick fix tanpa data loss
```

### Before vs After

**Before Swap:**
```
Entry A (Alice)
  Relations:
    → mentor: Bob

Entry B (Bob)
  Relations:
    (none)
```

**After Swap:**
```
Entry A (Alice)
  Relations:
    (none)

Entry B (Bob)
  Relations:
    → mentor: Alice
```

---

## Cross-Journey Integration

### Combined Flow: Writing → Research → Writing

```
📝 Editor
    │
    ├─▶ Write scene dengan "Gandalf"
    │   └─ Mention auto-detected
    │
    ├─▶ Hover over "Gandalf"
    │   └─ Tooltip: Quick preview
    │       └─ Need more info?
    │           └─ Click → Open Codex
    │
    ├─▶ On Codex page: Add progression
    │   └─ Add note: "Lost staff here"
    │
    ├─▶ Return to Editor
    │   └─ Type /progression
    │       └─ Already saved from Codex
    │
    └─▶ Continue writing with updated context
```

---

## Mobile-Specific Considerations

### Gesture Mappings

| Desktop Action | Mobile Equivalent |
|----------------|-------------------|
| Hover mention → Tooltip | Tap mention → Tooltip |
| Right-click → Context menu | Long-press → SelectionActionMenu |
| Kbd shortcut (Cmd+Shift+C) | Selection menu button |
| Kbd shortcut (Cmd+Shift+P) | Editor toolbar button |

### Responsive Adaptations

| Component | Adaptation |
|-----------|------------|
| ProgressionEditorModal | Full-screen on mobile |
| BulkCreateModal | Textarea smaller, table scrollable |
| CodexHoverTooltip | Larger tap target, dismissible |
| SelectionActionMenu | Floating button, accessible |

---

## Success Metrics

| Journey | Key Metric | Target | Actual |
|---------|------------|--------|--------|
| Add Progression | Time to complete | < 10s | ~8s |
| Hover Preview | Load time | < 300ms | ~200ms |
| Quick Create | Steps to create | ≤ 3 clicks | 3 clicks |
| Bulk Create | Entries per minute | > 10 | ~15 |
| Duplicate Entry | Time to duplicate | < 5s | ~3s |

---

## Related Documentation

- **API Reference:** [Codex API](../../04-api-reference/codex.md)
- **Testing Guide:** [Sprint 15 Testing](../../06-testing/codex-sprint15-testing.md)
- **Sprint Documentation:** [Sprint 15 - Editor Integration & UX](../../10-sprints/sprint-15-codex-v2-editor-ux.md)

---

*Last Updated: 2026-01-01*
