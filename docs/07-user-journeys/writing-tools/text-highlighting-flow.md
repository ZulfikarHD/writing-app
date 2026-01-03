# User Journey: Text Highlighting Flow

## Overview

User journey untuk highlighting text dengan berbagai warna untuk marking dan annotation purposes.

---

## Journey 1: Highlight Text untuk Revision

**Actor**: Writer  
**Goal**: Mark awkward paragraphs yang perlu di-revisi  
**Success Criteria**: Text highlighted dengan pink color, highlight persists

### Flow

```
📍 START: Editor halaman dengan scene content
    │
    ├─▶ Writer membaca draft scene
    │   └─ Menemukan paragraph yang awkward
    │
    ├─▶ ACTION: Select paragraph text
    │   └─ Text ter-select dengan blue highlight (selection)
    │
    ├─▶ ACTION: Click highlight button di toolbar
    │   └─ Color picker dropdown opens
    │   └─ Menampilkan 6 preset colors
    │
    ├─▶ ACTION: Click pink color
    │   └─ Dropdown closes
    │   └─ Text background berubah ke pink (#fbcfe8)
    │   └─ Personal convention: pink = "needs work"
    │
    ├─▶ SYSTEM: Auto-save scene content
    │   └─ Highlight data disimpan dalam TipTap JSON
    │   └─ Mark: { type: 'highlight', attrs: { color: '#fbcfe8' } }
    │
    └─▶ ✅ END: Text highlighted, ready untuk revision nanti
```

### Screenshots

**State 1**: Text selected
```
┌──────────────────────────────────────┐
│ Editor                         [🎨] │
├──────────────────────────────────────┤
│                                      │
│ The character walked [through the]  │  ← Text selected (blue)
│ [dark forest, feeling increasingly] │
│ [uneasy about the journey.]         │
│                                      │
└──────────────────────────────────────┘
```

**State 2**: Color picker open
```
┌──────────────────────────────────────┐
│ Editor                    [🎨▼]     │
├─────────────────────┬────────────────┤
│                     │ ┌──────────┐  │
│ The character       │ │🟨🟢🔵    │  │ ← Color grid
│ walked...           │ │🩷🟠🟣    │  │
│                     │ └──────────┘  │
│                     │ [× Remove]    │  ← Remove button
└─────────────────────┴────────────────┘
```

**State 3**: Text highlighted
```
┌──────────────────────────────────────┐
│ Editor                         [🎨] │
├──────────────────────────────────────┤
│                                      │
│ The character walked through the     │
│ [dark forest, feeling increasingly]  │  ← Pink background
│ [uneasy about the journey.]          │
│                                      │
└──────────────────────────────────────┘
```

### Alternative Paths

**Alt 1**: Keyboard shortcut
- Writer selects text
- Press `Ctrl+Shift+H`
- Text highlighted dengan default yellow color
- Faster untuk quick highlighting

**Alt 2**: Change highlight color
- Writer selects already-highlighted text
- Click highlight button
- Select different color
- Previous highlight replaced with new color

**Alt 3**: Remove highlight
- Writer selects highlighted text
- Click highlight button
- Click "Remove" button
- Highlight removed, text returns to normal

---

## Journey 2: Use Different Colors untuk Kategorisasi

**Actor**: Writer  
**Goal**: Organize marked text dengan color-coded system  
**Success Criteria**: Multiple highlights dengan berbagai warna

### Color Convention System

| Color | Hex | Writer's Convention |
|-------|-----|---------------------|
| 🟨 Yellow | #fef08a | General notes/reminders |
| 🟢 Green | #bbf7d0 | Good prose, keep as-is |
| 🔵 Blue | #bfdbfe | Research needed |
| 🩷 Pink | #fbcfe8 | Needs revision |
| 🟠 Orange | #fed7aa | Dialogue improvements |
| 🟣 Purple | #e9d5ff | Foreshadowing elements |

### Flow

```
📍 START: Writer reviewing full scene
    │
    ├─▶ First pass: Highlight research items (Blue)
    │   └─ Highlight: "Byzantine architecture" → Blue
    │   └─ Highlight: "medieval sword types" → Blue
    │
    ├─▶ Second pass: Mark good prose (Green)
    │   └─ Highlight: opening paragraph → Green
    │   └─ Highlight: climax dialogue → Green
    │
    ├─▶ Third pass: Mark problems (Pink)
    │   └─ Highlight: awkward transitions → Pink
    │   └─ Highlight: info-dump paragraph → Pink
    │
    └─▶ ✅ END: Scene color-coded, visual overview complete
```

### Benefits

1. **Visual Scanning**: Dapat immediately see problem areas
2. **Prioritization**: Tackle pink (needs work) first
3. **Progress Tracking**: Green areas = done, safe
4. **Context Switching**: Blue areas = research phase terpisah

---

## Journey 3: Persist Highlights Across Sessions

**Actor**: Writer  
**Goal**: Return to editing setelah beberapa hari, highlights masih ada  
**Success Criteria**: All highlights preserved after page reload

### Flow

```
📍 START: Writer finishing editing session
    │
    ├─▶ Writer has highlighted several passages
    │   └─ 5 pink highlights (needs revision)
    │   └─ 3 blue highlights (research)
    │
    ├─▶ ACTION: Close browser / navigate away
    │   └─ Scene auto-saved dengan highlight data
    │   └─ TipTap JSON contains all mark objects
    │
    ├─▶ [TIME PASSES: 2 days later]
    │
    ├─▶ ACTION: Writer opens scene again
    │   └─ Scene content loaded dari database
    │   └─ TipTap parses highlight marks
    │
    ├─▶ RESULT: All highlights render correctly
    │   └─ Same colors as before
    │   └─ Same text ranges
    │   └─ No data loss
    │
    └─▶ ✅ END: Writer continues revision work seamlessly
```

### Technical Details

**Storage Format:**
```json
{
  "type": "text",
  "text": "This text is highlighted",
  "marks": [
    {
      "type": "highlight",
      "attrs": {
        "color": "#fbcfe8"
      }
    }
  ]
}
```

**Rendering:**
- TipTap HighlightMark extension parses marks
- Applies `background-color` style
- Renders as `<mark data-highlight data-color="#fbcfe8">...</mark>`

---

## Edge Cases

### Edge Case 1: Overlapping Highlights

**Scenario**: Writer highlights text, then re-selects partial overlap

**Behavior**:
- Later highlight replaces earlier highlight
- No merged colors
- Single color per text span

**Example**:
```
Original: "The [quick] brown fox"  (yellow)
Select:   "The quick [brown] fox"
Highlight: blue
Result:   "The quick [brown] fox"  (blue replaces yellow)
```

### Edge Case 2: Highlight dalam Section

**Scenario**: Writer highlights text yang ada dalam beat section

**Behavior**:
- Highlight works normally
- Section collapse/expand preserves highlights
- Beat expansion includes highlighted text as context

### Edge Case 3: Multi-paragraph Selection

**Scenario**: Writer selects across multiple paragraphs

**Behavior**:
- All selected paragraphs highlighted
- Highlight mark applied per text node
- Visual continuity maintained

---

## Mobile Experience

### Touch Interaction

**Select Text**:
- Long-press to start selection
- Drag handles to adjust range
- Selection menu appears

**Apply Highlight**:
- Tap highlight button di selection menu
- Color picker opens as bottom sheet
- Larger touch targets (min 44x44px)

**Visual Differences**:
- Color picker grid: 2 columns instead of 3
- Larger color swatches
- "Remove" button full-width

---

## Accessibility Considerations

### Screen Readers

- Highlight button: "Highlight text, opens color picker"
- Color options: "Yellow highlight", "Green highlight", etc.
- Remove button: "Remove highlight from selected text"

### Keyboard Navigation

- Tab to highlight button
- Enter/Space to open dropdown
- Arrow keys to navigate colors
- Enter to select
- Esc to close

### Color Blindness

- Colors chosen dengan consideration untuk common types
- Visual distinction maintained even in grayscale
- Optional: Add pattern overlay (future enhancement)

---

*Last Updated: 2026-01-04*
