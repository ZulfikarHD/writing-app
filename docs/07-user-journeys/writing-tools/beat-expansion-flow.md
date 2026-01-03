# User Journey: Beat Expansion Flow

## Overview

User journey untuk planning scenes menggunakan beat sections, kemudian expand beats menjadi prose menggunakan AI.

---

## Journey 1: Plan Scene dengan Beats, Lalu Expand

**Actor**: Writer  
**Goal**: Outline scene dengan bullet points, expand ke prose dengan AI  
**Success Criteria**: Beat content menjadi prose, integrated ke scene

### Flow

```
📍 START: Writer starting new scene
    │
    ├─▶ ACTION: Create beat section
    │   └─ Type `/beat` atau use section menu
    │   └─ Beat section created (green color)
    │   └─ Title: "Scene Planning" (optional)
    │
    ├─▶ ACTION: Add beat points
    │   └─ Type bullet points:
    │       • Elena opens the mysterious letter
    │       • She discovers her father's secret
    │       • Emotional breakdown and realization
    │       • Decision to investigate further
    │   └─ Word count updates: ~25 words
    │
    ├─▶ UI UPDATE: "Expand" button appears
    │   └─ Button only visible when:
    │       • Section type = beat
    │       • Word count > 0
    │       • Section not collapsed
    │
    ├─▶ ACTION: Click "Expand" button
    │   └─ ProseGenerationPanel opens
    │   └─ Beat content prefilled dalam input
    │   └─ Mode: scene_beat
    │   └─ Context: Scene content sebelum beat
    │
    ├─▶ ACTION: Select prompt & generate
    │   └─ Writer memilih "Narrative Prose" prompt
    │   └─ Click "Generate"
    │   └─ Loading state shown (AI processing)
    │
    ├─▶ AI GENERATES: Prose from beats
    │   └─ Response: ~300 words prose
    │   └─ Incorporates all beat points
    │   └─ Maintains scene context & style
    │
    ├─▶ REVIEW: Writer reads generated prose
    │   └─ Options:
    │       • Add as Content section
    │       • Regenerate with different prompt
    │       • Edit before adding
    │       • Discard
    │
    ├─▶ ACTION: Click "Add as Content"
    │   └─ New content section created
    │   └─ Prose inserted into scene
    │   └─ Panel closes
    │
    ├─▶ OPTIONAL: Mark beat as completed
    │   └─ Click completion checkbox
    │   └─ Beat badge shows line-through
    │   └─ Visual indicator: planning phase done
    │
    └─▶ ✅ END: Scene has outline (beats) + prose (content)
```

### Screenshot Flow

**State 1**: Beat section dengan content
```
┌─────────────────────────────────────────────┐
│ ○ Beat: Scene Planning             45 words │
├─────────────────────────────────────────────┤
│ • Elena opens the mysterious letter         │
│ • She discovers her father's secret         │
│ • Emotional breakdown and realization       │
│ • Decision to investigate further           │
│                                              │
│               [⚡ Expand to Prose]           │  ← Expand button
└─────────────────────────────────────────────┘
```

**State 2**: Prose generation panel
```
┌─────────────────────────────────────────────┐
│ 🎨 Generate Prose                      [×]  │
├─────────────────────────────────────────────┤
│                                              │
│ Beat Content:                                │
│ ┌─────────────────────────────────────────┐│
││ • Elena opens the mysterious letter      ││ ← Prefilled
││ • She discovers her father's secret      ││
││ • Emotional breakdown...                 ││
│└─────────────────────────────────────────┘│
│                                              │
│ Prompt: [Narrative Prose ▼]                │
│                                              │
│ [Generate] [Discard]                        │
└─────────────────────────────────────────────┘
```

**State 3**: Generated prose
```
┌─────────────────────────────────────────────┐
│ 🎨 Generate Prose                      [×]  │
├─────────────────────────────────────────────┤
│ Generated Content:                           │
│ ┌─────────────────────────────────────────┐│
││ Elena's hands trembled as she broke the  ││
││ seal on the envelope. The letter inside  ││
││ was written in her father's distinctive  ││
││ handwriting—a script she thought she'd   ││
││ never see again. As she read, each word  ││
││ shattered another piece of the life...   ││
│└─────────────────────────────────────────┘│
│                                              │
│ [Add as Content] [Regenerate]               │
└─────────────────────────────────────────────┘
```

**State 4**: Beat marked completed
```
┌─────────────────────────────────────────────┐
│ ✓ ~~~Beat: Scene Planning~~~        45 words│ ← Line-through
├─────────────────────────────────────────────┤
│ • Elena opens the mysterious letter         │
│ • She discovers her father's secret         │
│ (collapsed)                                  │
└─────────────────────────────────────────────┘
```

---

## Journey 2: Iterative Beat Planning

**Actor**: Writer  
**Goal**: Refine beats multiple times sebelum expand  
**Success Criteria**: Final beat structure reflects best outline

### Flow

```
📍 START: Writer brainstorming scene structure
    │
    ├─▶ ITERATION 1: Initial beats
    │   └─ Write 3 broad beats
    │   └─ Review: Too vague
    │   └─ Don't expand yet
    │
    ├─▶ ITERATION 2: Add details
    │   └─ Break each beat into sub-points
    │   └─ Now have 7 beats total
    │   └─ Review: Better but missing emotional arc
    │
    ├─▶ ITERATION 3: Add emotional beats
    │   └─ Insert emotional reactions between actions
    │   └─ Final: 10 beats dengan complete arc
    │   └─ Review: Ready untuk expansion
    │
    ├─▶ ACTION: Click "Expand"
    │   └─ All 10 beats become context
    │   └─ AI generates comprehensive prose
    │
    └─▶ ✅ END: Rich prose reflecting refined outline
```

### Benefits of Iterative Planning

1. **No Commitment**: Beats can be rewritten freely
2. **Visual Structure**: See scene shape before writing
3. **Context Building**: More beats = better AI prose
4. **Flexibility**: Can skip expansion, write manually

---

## Journey 3: Mixed Approach (Beats + Manual Writing)

**Actor**: Writer  
**Goal**: Use beats untuk difficult sections, write manually untuk others  
**Success Criteria**: Scene has both AI-generated and manually-written content

### Flow

```
📍 START: Scene dengan challenging middle section
    │
    ├─▶ SECTION 1: Opening (Manual)
    │   └─ Writer comfortable dengan opening
    │   └─ Write directly dalam content section
    │   └─ No beats needed
    │
    ├─▶ SECTION 2: Confrontation (Beats + AI)
    │   └─ Complex dialogue dan action
    │   └─ Create beat section untuk planning
    │   └─ Expand beats to prose
    │   └─ Edit AI prose untuk refinement
    │
    ├─▶ SECTION 3: Resolution (Manual)
    │   └─ Clear vision untuk ending
    │   └─ Write directly
    │   └─ No AI assistance needed
    │
    └─▶ ✅ END: Hybrid scene leveraging both approaches
```

---

## Journey 4: Beat Completion Tracking

**Actor**: Writer  
**Goal**: Track progress melalui multi-scene planning session  
**Success Criteria**: Visual indication of completed vs pending beats

### Flow

```
📍 START: Writer planning 5 scenes
    │
    ├─▶ Scene 1: Create 4 beat sections
    │   └─ Expand 2 beats → mark completed ✓
    │   └─ 2 beats pending ○
    │   └─ Visual: 50% progress
    │
    ├─▶ Scene 2: Create 3 beat sections
    │   └─ All expanded and completed ✓✓✓
    │   └─ Visual: 100% progress
    │
    ├─▶ Scene 3: Create 6 beat sections
    │   └─ None expanded yet ○○○○○○
    │   └─ Visual: 0% progress
    │
    ├─▶ Writer returns next day
    │   └─ Scan all scenes
    │   └─ Green checkmarks = done
    │   └─ Empty circles = todo
    │   └─ Prioritize pending beats
    │
    └─▶ ✅ END: Clear overview of planning status
```

### Completion Visualization

```
Scene Outline:
  ✓ Beat 1: Character enters (DONE)
  ✓ Beat 2: Initial dialogue (DONE)
  ○ Beat 3: Conflict escalates (TODO)
  ○ Beat 4: Revelation (TODO)
  ○ Beat 5: Decision point (TODO)

Progress: 2/5 (40%)
```

---

## Alternative Paths

### Alt 1: Generate Multiple Times

```
📍 Beat → Expand → Review prose
    │
    ├─▶ Not satisfied dengan tone
    │   └─ Click "Regenerate"
    │   └─ Try different prompt
    │   └─ Compare versions
    │
    └─▶ Choose best version atau blend both
```

### Alt 2: Expand Partial Beats

```
📍 Beat section dengan 10 beats
    │
    ├─▶ Writer select only beats 3-5
    │   └─ Copy to new beat section
    │   └─ Expand only those beats
    │   └─ More focused prose generation
    │
    └─▶ Result: Granular control over expansion
```

### Alt 3: Keep Beats Visible

```
📍 After expansion
    │
    ├─▶ Don't mark beat completed
    │   └─ Collapse beat section
    │   └─ Remains visible above prose
    │   └─ Reference during editing
    │
    └─▶ Benefit: Compare prose against outline
```

---

## Edge Cases

### Edge Case 1: Empty Beat Section

**Scenario**: Writer clicks "Expand" dengan no content

**Behavior**:
- Button hidden when word_count = 0
- No accidental empty generations
- Must add beats before expanding

### Edge Case 2: Very Long Beats

**Scenario**: Beat section dengan 500+ words

**Behavior**:
- Expansion works normally
- AI treats as detailed outline
- May generate longer prose (800-1000 words)
- Check token limits

### Edge Case 3: Beat dalam Collapsed State

**Scenario**: Beat section collapsed

**Behavior**:
- "Expand" button tidak visible
- Must expand section first
- Prevents accidental clicks

---

## Technical Details

### Beat Content as AI Context

**What Gets Sent**:
```javascript
{
  mode: 'scene_beat',
  beat: '• Elena opens letter\n• Discovers secret\n• Emotional breakdown',
  instructions: '',  // Optional user instructions
  content_before: '...'  // Previous scene content for context
}
```

**AI Prompt Structure**:
```
System: You are a creative writing assistant.
User: Generate prose from these beats: [beats]
      Maintain consistency with: [content_before]
      Style: [from selected prompt]
```

### Completion State Storage

**Database**:
- Field: `scene_sections.is_completed` BOOLEAN
- Default: `false`
- Updates immediately on checkbox toggle
- Persists across sessions

**Frontend State**:
- Tracked dalam TipTap node attributes
- Updates trigger re-render of section header
- No network request until auto-save

---

*Last Updated: 2026-01-04*
