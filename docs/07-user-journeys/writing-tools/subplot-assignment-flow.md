# User Journey: Subplot Assignment Flow

## Overview

User journey untuk assigning subplots ke scenes menggunakan Codex Progressions, memungkinkan tracking subplot evolution across the story.

---

## Journey 1: Assign Subplot ke Scene

**Actor**: Writer  
**Goal**: Link romance subplot ke scene dimana karakter pertama kali bertemu  
**Success Criteria**: Subplot assigned, visible dalam scene metadata

### Flow

```
📍 START: Writer editing scene tentang first meeting
    │
    ├─▶ CONTEXT: Scene "Chapter 2: The Encounter"
    │   └─ Contains dialogue between protagonist & love interest
    │   └─ Important untuk romance subplot tracking
    │
    ├─▶ ACTION: Open Scene Info panel
    │   └─ Click Info button (🛈) dalam toolbar
    │   └─ OR press Ctrl+I keyboard shortcut
    │   └─ Side panel slides in dari kanan
    │
    ├─▶ SCROLL: Navigate to Subplots section
    │   └─ Section terletak setelah Labels
    │   └─ Shows: "Subplots" label
    │   └─ Shows: Current assigned subplots (empty)
    │   └─ Shows: "Add subplot" button
    │
    ├─▶ ACTION: Click "Add subplot" button
    │   └─ Dropdown opens below button
    │   └─ Loading state jika API call
    │   └─ Displays list of available subplots
    │
    ├─▶ DROPDOWN DISPLAYS:
    │   └─ Available subplots:
    │       • Romance Subplot (cyan dot)
    │       • Mystery Investigation (cyan dot)
    │       • Character Growth Arc (cyan dot)
    │   └─ Alphabetically sorted
    │   └─ Only shows subplots belum assigned
    │
    ├─▶ ACTION: Click "Romance Subplot"
    │   └─ Dropdown closes
    │   └─ API call: POST /api/scenes/{id}/subplots
    │   └─ Creates CodexProgression:
    │       • codex_entry_id: 42 (Romance Subplot)
    │       • scene_id: 10 (The Encounter)
    │       • mode: addition
    │       • note: "Scene: The Encounter"
    │
    ├─▶ UI UPDATE: Subplot badge appears
    │   └─ Badge: "Romance Subplot" (cyan bg)
    │   └─ Shows X button untuk remove
    │   └─ "Add subplot" button still visible
    │
    ├─▶ OPTIONAL: Assign lebih banyak subplots
    │   └─ Can assign multiple subplots ke 1 scene
    │   └─ Example: Both "Romance" dan "Character Growth"
    │
    └─▶ ✅ END: Subplot linked ke scene, trackable
```

### Screenshot Flow

**State 1**: Scene Info panel opened
```
┌─────────────────────────────────────┐
│ Scene Info                     [×] │
├─────────────────────────────────────┤
│                                     │
│ 📊 2,450 words                      │
│                                     │
│ Title: [The Encounter          ]   │
│ Subtitle: [First meeting...    ]   │
│                                     │
│ Status: [In Progress ▼]            │
│                                     │
│ Labels:                             │
│ [Action] [Emotional]               │
│                                     │
│ Subplots:                           │  ← Subplots section
│ (none assigned)                     │
│                                     │
│ [+ Add subplot]                    │  ← Add button
│                                     │
└─────────────────────────────────────┘
```

**State 2**: Dropdown opened
```
┌─────────────────────────────────────┐
│ Subplots:                           │
│ (none assigned)                     │
│                                     │
│ [+ Add subplot▼]                   │
│  ┌───────────────────────────────┐│
│  │● Romance Subplot              ││ ← Clickable
│  │● Mystery Investigation        ││
│  │● Character Growth Arc         ││
│  └───────────────────────────────┘│
│                                     │
└─────────────────────────────────────┘
```

**State 3**: Subplot assigned
```
┌─────────────────────────────────────┐
│ Subplots:                           │
│ [Romance Subplot ×]                │ ← Badge dengan X
│                                     │
│ [+ Add subplot]                    │
│                                     │
└─────────────────────────────────────┘
```

**State 4**: Multiple subplots
```
┌─────────────────────────────────────┐
│ Subplots:                           │
│ [Romance Subplot ×]                │
│ [Character Growth Arc ×]           │
│ [Mystery Investigation ×]          │
│                                     │
│ (no more subplots available)        │
└─────────────────────────────────────┘
```

---

## Journey 2: Remove Subplot Assignment

**Actor**: Writer  
**Goal**: Unassign subplot yang ternyata tidak relevant ke scene  
**Success Criteria**: Subplot removed, available lagi untuk assignment

### Flow

```
📍 START: Scene dengan assigned subplot
    │
    ├─▶ CONTEXT: Scene initially tagged dengan "Mystery"
    │   └─ After review, mystery tidak dibahas di scene ini
    │   └─ Decision: Remove assignment
    │
    ├─▶ ACTION: Open Scene Info panel
    │   └─ See assigned subplots
    │   └─ Badge: [Mystery Investigation ×]
    │
    ├─▶ ACTION: Click X button pada badge
    │   └─ Confirmation tidak diperlukan (can re-add easily)
    │   └─ API call: DELETE /api/scenes/{id}/subplots/{entry}
    │   └─ Deletes CodexProgression record
    │
    ├─▶ UI UPDATE: Badge removed
    │   └─ "Mystery Investigation" hilang dari list
    │   └─ Subplot muncul kembali di dropdown
    │
    └─▶ ✅ END: Clean subplot assignment
```

---

## Journey 3: Tracking Subplot Across Multiple Scenes

**Actor**: Writer  
**Goal**: Assign romance subplot ke all relevant scenes untuk tracking  
**Success Criteria**: Clear progression dari meet → develop → climax

### Flow

```
📍 START: Writer reviewing romance subplot structure
    │
    ├─▶ SCENE 1: "First Meeting" (Chapter 2)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Initial attraction, chemistry"
    │
    ├─▶ SCENE 2: "Coffee Shop Encounter" (Chapter 5)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Growing interest, shared values"
    │
    ├─▶ SCENE 3: "The Confession" (Chapter 12)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Emotional vulnerability"
    │
    ├─▶ SCENE 4: "First Kiss" (Chapter 15)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Relationship milestone"
    │
    ├─▶ SCENE 5: "Conflict" (Chapter 18)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Trust issues emerge"
    │
    ├─▶ SCENE 6: "Resolution" (Chapter 22)
    │   └─ Assign: Romance Subplot
    │   └─ Note: "Reconciliation, commitment"
    │
    ├─▶ FUTURE: View Matrix dalam Plan interface
    │   └─ See all 6 scenes horizontally
    │   └─ Romance subplot progression visualized
    │   └─ Identify gaps atau pacing issues
    │
    └─▶ ✅ END: Complete subplot arc documented
```

### Progression Notes Examples

| Scene | Note | Subplot State |
|-------|------|---------------|
| First Meeting | "Initial attraction" | Beginning |
| Coffee Shop | "Growing interest" | Development |
| The Confession | "Emotional vulnerability" | Deepening |
| First Kiss | "Relationship milestone" | Peak |
| Conflict | "Trust issues" | Crisis |
| Resolution | "Reconciliation" | Resolution |

---

## Journey 4: Subplot Setup (Prerequisite)

**Actor**: Writer  
**Goal**: Create subplot entry dalam Codex sebelum assignment  
**Success Criteria**: Subplot exists dan available untuk scenes

### Flow

```
📍 START: Writer planning novel structure
    │
    ├─▶ ACTION: Navigate to Codex page
    │   └─ URL: /novels/{id}/codex
    │
    ├─▶ ACTION: Create new entry
    │   └─ Click "Create Entry"
    │   └─ Type: Select "Subplot"
    │   └─ Name: "Romance Subplot"
    │   └─ Description: "Love story between protagonist and Aria"
    │   └─ Save entry
    │
    ├─▶ REPEAT: Create more subplots
    │   └─ "Mystery Investigation"
    │   └─ "Character Growth Arc"
    │   └─ "Political Intrigue"
    │
    ├─▶ RESULT: Subplots available
    │   └─ All subplot entries now appear dalam:
    │       • Scene Info subplot dropdown
    │       • Novel codex list (filtered by type)
    │       • Plan Matrix view (future)
    │
    └─▶ ✅ END: Ready untuk assignment ke scenes
```

---

## Alternative Paths

### Alt 1: No Subplots Exist Yet

```
📍 Scene Info panel → Subplots section
    │
    ├─▶ Display message:
    │   "No subplots in codex. Create a subplot entry first."
    │
    ├─▶ ACTION: Click link atau navigate to Codex
    │   └─ Create subplot entries
    │   └─ Return to scene
    │   └─ Subplots now available
    │
    └─▶ Assign subplots
```

### Alt 2: All Subplots Already Assigned

```
📍 Scene Info panel → Click "Add subplot"
    │
    ├─▶ Dropdown shows:
    │   "(no more subplots available)"
    │
    ├─▶ Implication:
    │   └─ All novel subplots already assigned ke scene ini
    │   └─ Either remove some atau create new subplot entries
    │
    └─▶ Decision:
        • Keep all (scene central to multiple subplots)
        • Or remove irrelevant ones
```

### Alt 3: Reassign Same Subplot

```
📍 Scene already has Romance subplot assigned
    │
    ├─▶ Writer accidentally assigns lagi
    │   (UI prevents this: subplot tidak muncul di dropdown)
    │
    ├─▶ IF somehow API called with same entry:
    │   └─ Backend checks existing progression
    │   └─ Updates note if different
    │   └─ Returns 200 OK (not 201 Created)
    │   └─ No duplicate progressions created
    │
    └─▶ Result: Idempotent operation
```

---

## Edge Cases

### Edge Case 1: Delete Subplot Entry

**Scenario**: Writer deletes subplot dari Codex setelah assign ke scenes

**Behavior**:
- Progressions remain in database (foreign key cascade: NO)
- Scene metadata shows: "[Deleted Entry]" atau filter out
- Can remove assignment, tapi tidak bisa re-add (entry gone)

### Edge Case 2: Many Subplots (10+)

**Scenario**: Novel dengan 15 subplot entries

**Behavior**:
- Dropdown scrollable (max-height: 12rem)
- Smooth scrolling dengan mouse wheel
- Touch-scrollable pada mobile
- Search/filter tidak implemented yet (future enhancement)

### Edge Case 3: Subplot Assigned Across 50 Scenes

**Scenario**: Main plot subplot assigned ke majority of scenes

**Behavior**:
- No performance issues (progressions lightweight)
- Matrix view shows dense timeline
- Useful untuk identifying scenes WITHOUT subplot

---

## Mobile Experience

### Touch Targets

**Buttons**:
- "Add subplot": min 48px height
- Dropdown items: min 44px height
- X remove button: min 44px tap target

**Dropdown**:
- Full-width pada mobile (<640px)
- Bottom sheet alternative (future enhancement)
- Larger text untuk readability

### Visual Adjustments

**Badges**:
- Stack vertically if many subplots
- Larger font size
- Clear visual separation

---

## Technical Details

### API Request/Response

**List Subplots**:
```http
GET /api/novels/1/codex/subplots
Authorization: Bearer {token}

Response 200 OK:
{
  "subplots": [
    {
      "id": 42,
      "name": "Romance Subplot",
      "description": "Love story...",
      "aliases": ["Romance Arc"]
    }
  ]
}
```

**Assign Subplot**:
```http
POST /api/scenes/10/subplots
Authorization: Bearer {token}
Content-Type: application/json

{
  "codex_entry_id": 42,
  "note": "First meeting scene"
}

Response 201 Created:
{
  "progression": {
    "id": 15,
    "codex_entry_id": 42,
    "scene_id": 10,
    "note": "First meeting scene"
  }
}
```

**Remove Assignment**:
```http
DELETE /api/scenes/10/subplots/42
Authorization: Bearer {token}

Response 200 OK:
{
  "success": true,
  "deleted": 1
}
```

### Database Structure

**CodexProgression**:
```sql
CREATE TABLE codex_progressions (
  id BIGINT PRIMARY KEY,
  codex_entry_id BIGINT NOT NULL,
  scene_id BIGINT,
  note TEXT,
  mode VARCHAR(20) DEFAULT 'addition',
  sort_order INT DEFAULT 0,
  FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id),
  FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE
);
```

**Query untuk Scene Subplots**:
```sql
SELECT ce.id, ce.name, cp.id as progression_id, cp.note
FROM codex_entries ce
JOIN codex_progressions cp ON ce.id = cp.codex_entry_id
WHERE cp.scene_id = 10
  AND ce.type = 'subplot'
ORDER BY ce.name;
```

---

## Future Enhancements

- [ ] **Matrix View**: Visual timeline subplot progressions across chapters
- [ ] **Subplot Indicators**: Badge pada scene cards di plan view
- [ ] **Bulk Assignment**: Assign subplot ke multiple scenes at once
- [ ] **Progression Reordering**: Drag-and-drop subplot progressions
- [ ] **Search/Filter**: Search subplots dalam large dropdown lists
- [ ] **Subplot Colors**: Color-code subplots untuk better visual distinction
- [ ] **Statistics**: Show subplot coverage percentage per novel

---

*Last Updated: 2026-01-04*
