# User Journeys: Sections System

**Feature:** FG-06.1 - Sections System  
**Sprint:** 30  
**Last Updated:** 2026-01-04

---

## Journey 1: Create Note Section for Research

**Persona:** Fiction Writer  
**Goal:** Add research notes that won't be exported atau included in AI context

```
📍 START: Scene Editor
    │
    ├─▶ User types content: "The hero enters the ancient temple..."
    │
    ├─▶ User types: /
    │   └─ Slash command menu appears
    │
    ├─▶ User types: "note"
    │   └─ Menu filters to "Note Section"
    │
    ├─▶ User presses Enter
    │   └─ New Note section inserted below cursor
    │
    ├─▶ Section appears with:
    │   ├─ Yellow border (#eab308)
    │   ├─ Badge: "Note"
    │   ├─ AI icon: Eye-closed (excluded)
    │   └─ Empty content area
    │
    ├─▶ User types research notes:
    │   "Temple architecture based on Angkor Wat
    │    - 5 terraces representing Mount Meru
    │    - Sandstone construction
    │    - TODO: Research Khmer symbolism"
    │
    ├─▶ User continues writing main content below
    │
    └─▶ ✅ END: Note section saved, excluded from export & AI
```

**Key Interactions:**
- `/note` slash command
- Auto-color by type
- Auto-exclude from AI
- Inline editing

**Benefits:**
- Research stays with scene
- Won't clutter final manuscript
- AI won't use notes in suggestions

---

## Journey 2: Kitbash Alternative Dialogue Versions

**Persona:** Dialogue-Focused Writer  
**Goal:** Try multiple versions of dialogue side-by-side

```
📍 START: Scene Editor with dialogue
    │
    ├─▶ Current Content section:
    │   "I can't believe you did that," she said.
    │
    ├─▶ User types: /alternative
    │   └─ Alternative section created (violet border)
    │
    ├─▶ User types alternative version:
    │   "You betrayed everything we stood for," she whispered.
    │
    ├─▶ User creates another alternative:
    │   "How could you?" Her voice broke.
    │
    ├─▶ User reviews all three versions:
    │   ├─ Scrolls between sections
    │   ├─ Compares tone & impact
    │   └─ Decides version 3 is best
    │
    ├─▶ User clicks version 3 type badge
    │   └─ Changes type to "Content" (indigo)
    │
    ├─▶ User clicks version 1 menu → Delete
    │   └─ Original version removed
    │
    ├─▶ User clicks version 2 menu → Delete
    │   └─ First alternative removed
    │
    └─▶ ✅ END: Best version promoted, others removed
```

**Key Interactions:**
- Multiple alternative sections
- Type badge quick-change
- Delete unwanted versions
- Visual differentiation by color

**Benefits:**
- Experiment without losing work
- Compare versions visually
- Easy promotion/deletion

---

## Journey 3: Collapse Sections for Focus Mode

**Persona:** Novelist managing long scene  
**Goal:** Hide research/notes to focus on main prose

```
📍 START: Scene with 5 sections
    │
    ├─▶ Section 1: Content - "Opening prose" (1200 words)
    ├─▶ Section 2: Note - "Character motivation notes" (300 words)
    ├─▶ Section 3: Content - "Main dialogue" (800 words)
    ├─▶ Section 4: Beat - "Outline: Reveal secret here" (50 words)
    └─▶ Section 5: Content - "Closing action" (600 words)
    │
    ├─▶ User clicks collapse on Section 2 (Note)
    │   └─ Height animates down
    │   └─ Shows preview: "Character motivation notes..."
    │
    ├─▶ User clicks collapse on Section 4 (Beat)
    │   └─ Outline hidden, shows preview
    │
    ├─▶ Now editor shows:
    │   ├─ Section 1: Content (expanded)
    │   ├─ Section 2: Note (collapsed - one line)
    │   ├─ Section 3: Content (expanded)
    │   ├─ Section 4: Beat (collapsed - one line)
    │   └─ Section 5: Content (expanded)
    │
    ├─▶ User scrolls through scene:
    │   └─ Only sees content sections
    │   └─ Notes/beats minimized
    │
    ├─▶ User needs to check note
    │   └─ Clicks expand on Section 2
    │   └─ Height animates up
    │   └─ Full note visible
    │
    └─▶ ✅ END: Focused on content, notes accessible when needed
```

**Key Interactions:**
- Collapse/expand toggle
- Smooth height animation
- Preview text in collapsed state
- Persistent collapse state

**Benefits:**
- Less scrolling
- Better focus
- Non-destructive hiding

---

## Journey 4: Reorder Sections via Drag & Drop

**Persona:** Writer reorganizing scene structure  
**Goal:** Move alternative version to compare with different paragraph

```
📍 START: Scene Editor with sections
    │
    ├─▶ Current order:
    │   1. Content - "Paragraph A" (opening)
    │   2. Content - "Paragraph B" (middle)
    │   3. Alternative - "Alt version of B"
    │   4. Content - "Paragraph C" (closing)
    │
    ├─▶ User wants to compare Alt with Paragraph A
    │
    ├─▶ User hovers over Section 3 header
    │   └─ Drag handle appears (≡ icon)
    │
    ├─▶ User clicks and holds drag handle
    │   └─ Section 3 becomes semi-transparent
    │   └─ Cursor changes to "grabbing"
    │
    ├─▶ User drags upward
    │   └─ Other sections shift down as cursor passes
    │   └─ Drop zone indicators appear
    │
    ├─▶ User drops between Section 1 and 2
    │   └─ Section 3 smoothly animates to new position
    │
    ├─▶ New order:
    │   1. Content - "Paragraph A"
    │   2. Alternative - "Alt version of B" (moved)
    │   3. Content - "Paragraph B"
    │   4. Content - "Paragraph C"
    │
    ├─▶ User reviews comparison
    │   └─ Decides which version flows better
    │
    └─▶ ✅ END: Sections reordered, sort_order persisted
```

**Key Interactions:**
- Drag handle (visible on hover)
- Live drag preview
- Drop zone indicators
- Smooth animation

**Benefits:**
- Flexible scene organization
- Compare sections side-by-side
- Non-destructive reorganization

---

## Journey 5: Control AI Context with Toggle

**Persona:** Writer using AI assistance  
**Goal:** Exclude sensitive/unfinished content from AI suggestions

```
📍 START: Scene with mix of final/draft content
    │
    ├─▶ Section 1: Content - "Published quality prose"
    ├─▶ Section 2: Content - "Rough draft, needs work"
    ├─▶ Section 3: Note - "NSFW scene planning"
    │
    ├─▶ User opens Workshop (Chat) panel
    │
    ├─▶ User adds scene to context
    │   └─ By default:
    │       ├─ Section 1: Included (eye open)
    │       ├─ Section 2: Included (eye open)
    │       └─ Section 3: Excluded (note type, eye closed)
    │
    ├─▶ User wants to exclude Section 2 from AI
    │
    ├─▶ User clicks AI visibility icon on Section 2
    │   └─ Eye changes to eye-closed
    │   └─ exclude_from_ai = true
    │
    ├─▶ User returns to Workshop panel
    │   └─ Context preview updates:
    │       ├─ Shows Section 1 content only
    │       └─ Sections 2 & 3 not included
    │
    ├─▶ User asks AI: "Continue this scene"
    │   └─ AI only sees Section 1 context
    │   └─ Suggestion based on final content only
    │
    ├─▶ User finishes revising Section 2
    │
    ├─▶ User clicks AI visibility icon again
    │   └─ Eye changes to eye-open
    │   └─ Section 2 now included in context
    │
    └─▶ ✅ END: Granular control over AI context per section
```

**Key Interactions:**
- AI visibility toggle (eye icon)
- Real-time context preview update
- Type-based defaults (notes excluded)
- Independent per section

**Benefits:**
- Protect sensitive content
- Control AI suggestions quality
- Exclude rough drafts
- NSFW content stays private

---

## Journey 6: Dissolve Section (Unwrap Content)

**Persona:** Writer cleaning up organization  
**Goal:** Merge section content back to main flow

```
📍 START: Scene with temporary section
    │
    ├─▶ Section 1: Content - "Opening"
    ├─▶ Section 2: Alternative - "Experimental paragraph"
    │   └─ User tried alternative, decided it's good
    ├─▶ Section 3: Content - "Closing"
    │
    ├─▶ User wants to remove Alternative container
    │   └─ Keep content, just not as separate section
    │
    ├─▶ User clicks menu on Section 2
    │   └─ Clicks "Dissolve Section"
    │
    ├─▶ System:
    │   ├─ Extracts Section 2 content
    │   ├─ Deletes section container
    │   └─ Inserts content into scene flow
    │
    ├─▶ Result:
    │   ├─ Section 1: Content - "Opening"
    │   ├─ [Dissolved content merged here]
    │   └─ Section 2: Content - "Closing" (was Section 3)
    │
    └─▶ ✅ END: Section unwrapped, content preserved
```

**Key Interactions:**
- Dissolve action in menu
- Content extraction
- Automatic insertion
- Section container removal

**Benefits:**
- Clean up organization
- No data loss
- Merge experiments into main flow

---

## Journey 7: Duplicate Section for Variations

**Persona:** Writer exploring pacing options  
**Goal:** Create variation without retyping content

```
📍 START: Scene with established content
    │
    ├─▶ Section: Content - "Fast-paced action sequence" (500 words)
    │
    ├─▶ User wants to try slower, detailed version
    │
    ├─▶ User clicks section menu
    │   └─ Clicks "Duplicate"
    │
    ├─▶ System creates:
    │   └─ New section: "Fast-paced action sequence (Copy)"
    │   └─ Identical content
    │   └─ Placed below original
    │   └─ sort_order auto-incremented
    │
    ├─▶ User changes duplicate type to "Alternative"
    │   └─ Border changes to violet
    │   └─ Badge shows "Alternative"
    │
    ├─▶ User edits duplicate:
    │   ├─ Expands action beats
    │   ├─ Adds sensory details
    │   └─ Slows pacing
    │
    ├─▶ User compares both versions:
    │   └─ Scrolls between original & variation
    │   └─ Decides which pacing works better
    │
    └─▶ ✅ END: Variation created from duplicate, both preserved
```

**Key Interactions:**
- Duplicate action
- Automatic "(Copy)" suffix
- Type change after duplication
- Independent editing

**Benefits:**
- Quick variation creation
- No retyping needed
- Safe experimentation

---

## Edge Case Journeys

### Journey 8: Recover from Accidental Delete

```
📍 Scenario: User deletes section by mistake
    │
    ├─▶ User clicks "Delete Section"
    │   └─ Section immediately removed
    │
    ├─▶ User realizes mistake
    │
    ├─▶ User presses Ctrl+Z (Undo)
    │   └─ TipTap undo restores section
    │   └─ [Backend: Section not yet deleted from DB]
    │
    └─▶ ✅ Section recovered
```

**Note:** Currently no "trash" or "undo delete" at API level. Frontend undo must handle this.

---

### Journey 9: Handle Network Failure During Reorder

```
📍 Scenario: Drag & drop reorder, network fails
    │
    ├─▶ User drags Section 3 to position 1
    │
    ├─▶ Frontend updates UI immediately (optimistic)
    │
    ├─▶ API call fails (network error)
    │
    ├─▶ Frontend:
    │   ├─ Reverts to original order
    │   ├─ Shows error toast:
    │       "Failed to reorder sections. Please try again."
    │   └─ Sections animate back to original positions
    │
    ├─▶ User retries drag & drop
    │
    └─▶ ✅ Reorder succeeds, persisted
```

**Key Handling:**
- Optimistic UI update
- Graceful error recovery
- User-friendly error message
- Retry available

---

## Common Patterns

### Pattern: Type + Color Association

All journeys rely on color coding for quick recognition:

| Type | Color | Mental Model |
|------|-------|--------------|
| Content | Indigo | "Final manuscript" |
| Note | Yellow | "Sticky note" |
| Alternative | Violet | "Different version" |
| Beat | Green | "Outline/planning" |

### Pattern: Menu Hierarchy

Consistent menu structure across all journeys:
1. Word Count (info only)
2. Type Selector (frequent)
3. Color Picker (customization)
4. Copy Text (utility)
5. Dissolve (advanced)
6. Delete (danger zone)

### Pattern: Toggle States

All toggles provide immediate visual feedback:
- Collapse: Chevron rotation + height animation
- AI Visibility: Eye icon change (open ↔ closed)

---

## Related Documentation

- **API Reference:** [Scene Sections API](../../04-api-reference/scene-sections.md)
- **Testing Guide:** [Sections Testing](../../06-testing/sections-testing.md)
- **Sprint Documentation:** [Sprint 30: Sections System](../../10-sprints/sprint-30-sections-system.md)

---

*Last Updated: 2026-01-04*
