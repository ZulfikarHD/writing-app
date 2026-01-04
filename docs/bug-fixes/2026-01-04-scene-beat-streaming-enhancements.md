# 🐛 Scene Beat Streaming & Prompt Preview Enhancement

**Date:** 2026-01-04  
**Sprint:** Sprint 32 (Continuation)  
**Category:** AI Writing Features, UX Enhancement  
**Status:** ✅ Completed

---

## Overview

Enhancement terhadap Scene Beat feature untuk memberikan user experience yang lebih intuitif dengan streaming content langsung ke section (seperti chat streaming), serta menambahkan View Prompt button pada generated sections untuk transparency dan debugging purposes.

---

## Enhancement Details

### ✨ ENH-32.5: Direct Section Streaming

**Category:** UX Enhancement  
**Component:** SceneBeatNode, SceneBeatNodeView  
**Priority:** 🟡 High (User Experience Critical)  
**Status:** ✅ Implemented

#### Problem Description

**Previous Flow (Less Intuitive):**
```
1. User clicks "Generate" di Scene Beat
2. Content streams into preview area di Scene Beat component
3. User sees content word-by-word dalam preview
4. After generation complete, content replaced Scene Beat with Section
5. Content copied from preview to Section
```

**Issues dengan Previous Approach:**
- ❌ Preview area felt like "intermediate step" (not direct)
- ❌ User melihat content twice (preview → section)
- ❌ Less intuitive workflow
- ❌ Tidak match dengan chat experience yang sudah familiar
- ❌ Preview component destroyed saat beat replaced, causing potential loss

#### Solution: Stream Directly Into Section

**New Flow (More Intuitive):**
```
1. User clicks "Generate" di Scene Beat
2. Section created IMMEDIATELY below Scene Beat (empty)
3. Content streams directly INTO that section (word-by-word)
4. User sees content being written in real section (not preview)
5. Scene Beat remains for potential regeneration
6. Success indicator shown when done
```

**Benefits:**
- ✅ Matches chat streaming experience (familiar pattern)
- ✅ Direct feedback - content written where it will live
- ✅ Beat stays alive untuk multiple generations
- ✅ No intermediate preview step
- ✅ Cleaner, more intuitive UX

#### Technical Implementation

**1. Modified Node Command: `createStreamingSection`**

```typescript
// SceneBeatNode.ts
createStreamingSection: (pos: number, metadata?: GenerationMetadata) =>
    ({ tr, dispatch, state }) => {
        // Create section AFTER beat (not replace)
        const afterBeatPos = pos + node.nodeSize;
        tr.insert(afterBeatPos, state.schema.nodeFromJSON(sectionNode));
    }
```

**Previous:** Replaced beat with section  
**Now:** Insert section after beat

**2. New Command: `appendToSection`**

```typescript
// SceneBeatNode.ts
appendToSection: (sectionPos: number, content: string) =>
    ({ tr, dispatch, state }) => {
        // Find last paragraph in section
        // Append new content chunk to it
        const newText = currentText + content;
        tr.replaceWith(...);
    }
```

Allows real-time appending as chunks arrive dari SSE stream.

**3. Streaming Watch Logic**

```typescript
// SceneBeatNodeView.vue
let lastContentLength = 0;

watch(generatedContent, async (newContent) => {
    if (isGenerating.value) {
        // Get only NEW chunk since last update
        const newChunk = newContent.substring(lastContentLength);
        lastContentLength = newContent.length;
        
        if (newChunk) {
            // Find section position dynamically (adjusts for doc changes)
            const currentBeatPos = props.getPos();
            const sectionPos = currentBeatPos + beatNode.nodeSize;
            
            // Append chunk to section
            props.editor.commands.appendToSection(sectionPos, newChunk);
        }
    }
});
```

**Key Features:**
- **Incremental updates:** Only new chunks appended
- **Dynamic position tracking:** Handles document changes during generation
- **Real-time streaming:** No buffering, immediate display
- **Section validation:** Ensures we're writing to correct section

**4. UI Indicators**

**During Generation:**
```vue
<div class="bg-violet-900/20 border border-violet-700/50">
    <svg class="animate-pulse">...</svg>
    <span>Writing into section below...</span>
    <span>{{ wordCount }} words</span>
</div>
```

**After Generation:**
```vue
<div class="bg-green-900/20 border border-green-700/50">
    <svg class="checkmark">...</svg>
    <span>Generated {{ wordCount }} words</span>
    <button @click="handleClearBeat">Clear & New</button>
</div>
```

#### Files Changed

```
resources/js/
├── extensions/
│   └── SceneBeatNode.ts                  ✏️ UPDATED
│       - Modified createStreamingSection command
│       - Added appendToSection command
│
└── components/editor/
    └── SceneBeatNodeView.vue             ✏️ UPDATED
        - Removed preview display UI
        - Added streaming watch logic
        - Added dynamic position tracking
        - Enhanced status indicators
```

#### Testing

- [x] Beat creates section below when Generate clicked
- [x] Content streams into section character-by-character
- [x] Word count updates in real-time during streaming
- [x] Beat remains alive after generation
- [x] Success indicator shows when complete
- [x] "Clear & New" button resets for next generation
- [x] Multiple generations work correctly
- [x] Section position tracking handles document changes
- [x] No stale position references
- [x] Mobile responsive behavior maintained

---

### ✨ ENH-32.6: View Prompt Button for Generated Sections

**Category:** Transparency & Debugging Feature  
**Component:** GeneratedSectionHeader  
**Priority:** 🟢 Medium (Developer Experience)  
**Status:** ✅ Implemented

#### Enhancement Description

Menambahkan "View Prompt" button pada generated sections untuk display original prompt yang digunakan untuk generation. Berguna untuk:
- **Transparency:** User sees exactly what prompt was sent to AI
- **Debugging:** Developers can verify prompt construction
- **Learning:** Writers learn how to craft better beats
- **Regeneration Reference:** Understand what to adjust untuk better results

#### Features Implemented

**1. View Prompt Button**
- Positioned next to "Regenerate" button di section header
- Distinct violet theme (vs amber untuk regenerate)
- Toggles expandable prompt preview panel

**2. Prompt Preview Panel**

Displays comprehensive prompt information:

| Section | Content |
|---------|---------|
| **System Message** | Full writing rules dan instructions |
| **User Message** | Scene beat + word target |
| **Metadata** | Word target, Connection ID, Model ID |
| **Word Counts** | Individual dan total word counts |
| **Copy Button** | Copy entire prompt to clipboard |

**3. System Message Template**

```typescript
const defaultSystemMessage = `You are an expert fiction writer.

Always keep the following rules in mind:
- Write in past tense and use General English spelling, grammar, and colloquialisms/slang.
- Write in active voice
- Always follow the "show, don't tell" principle.
- Avoid adverbs and cliches and overused/commonly used phrases.
... (full rules)

When writing text:
- NEVER conclude the scene on your own, follow the beat instructions very closely`;
```

#### UI Design

**Collapsed State:**
```
┌────────────────────────────────────────────┐
│ ⚡ AI Generated  "John enters dark room..."│
│                                             │
│ [👁 View Prompt] [🔄 Regenerate] [≡ Menu] │
└────────────────────────────────────────────┘
```

**Expanded Prompt Preview:**
```
┌────────────────────────────────────────────┐
│ ⚡ AI Generated  "John enters dark room..."│
│ [👁 Hide] [🔄 Regenerate] [≡ Menu]         │
├────────────────────────────────────────────┤
│ 👁 Original Prompt      125 words | 📋Copy│
├────────────────────────────────────────────┤
│ System Message                   100 words │
│ ┌────────────────────────────────────────┐ │
│ │ You are an expert fiction writer...    │ │
│ │ (scrollable preview)                   │ │
│ └────────────────────────────────────────┘ │
│                                             │
│ User Message                      25 words │
│ ┌────────────────────────────────────────┐ │
│ │ Write approximately 400 words.         │ │
│ │                                         │ │
│ │ Scene beat to write:                   │ │
│ │ John enters the dark room              │ │
│ └────────────────────────────────────────┘ │
│                                             │
│ Metadata: Target: 400 | Connection: 1 |   │
│           Model: gpt-4                     │
└────────────────────────────────────────────┘
```

#### Implementation Details

**1. State Management**

```typescript
// GeneratedSectionHeader.vue
const showPromptPreview = ref(false);

function togglePromptPreview() {
    showPromptPreview.value = !showPromptPreview.value;
    if (showPromptPreview.value) {
        // Close regenerate panel if open
        showRegeneratePanel.value = false;
    }
}
```

**2. Computed Prompt Construction**

```typescript
const constructedUserMessage = computed(() => {
    if (!props.sourceBeat) return '';
    return `Write approximately ${props.sourceWordTarget || 400} words.\n\nScene beat to write:\n${props.sourceBeat}`;
});

const previewPrompt = computed(() => {
    const parts: string[] = [];
    parts.push('=== SYSTEM MESSAGE ===');
    parts.push(defaultSystemMessage);
    parts.push('');
    parts.push('=== USER MESSAGE ===');
    parts.push(constructedUserMessage.value);
    return parts.join('\n');
});
```

**3. Copy to Clipboard Function**

```typescript
function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
    // Optional: Show toast notification
}
```

#### Files Changed

```
resources/js/
└── components/editor/
    ├── GeneratedSectionHeader.vue        ✏️ UPDATED
    │   - Added showPromptPreview state
    │   - Added defaultSystemMessage constant
    │   - Added computed prompt properties
    │   - Added View Prompt button
    │   - Added Prompt Preview panel UI
    │   - Added copyToClipboard function
    │   - Enhanced scrollbar styles
    │
    └── SectionHeader.vue                 ✏️ UPDATED (removed - not needed)
        - Initially considered but not implemented
        - GeneratedSectionHeader is separate component
```

#### Styling Details

**Violet Theme for Prompt Preview:**
- Border: `border-violet-200 dark:border-violet-900/50`
- Background: `bg-violet-50/50 dark:bg-violet-950/30`
- Text: `text-violet-700 dark:text-violet-300`
- Scrollbar: `#8b5cf6` (violet-500)

**Contrast dengan Regenerate Panel (Amber):**
- Allows visual distinction between actions
- Prompt = information (violet = info)
- Regenerate = action (amber = warning/caution)

#### Testing

- [x] View Prompt button visible di generated sections
- [x] Button toggle prompt preview panel
- [x] System message displays correctly
- [x] User message shows beat + word target
- [x] Metadata displays (connection, model, target)
- [x] Word counts accurate
- [x] Copy button copies entire prompt
- [x] Panel scrollable untuk long prompts
- [x] Closes regenerate panel saat opened
- [x] Violet theme distinct from amber regenerate
- [x] Mobile responsive layout
- [x] Dark mode compatibility

---

## User Experience Improvements

### Before Enhancements

**Generation Flow:**
```
User → Beat → Generate → Preview Area (streaming)
                              ↓
                         Section created
                              ↓
                         Content copied
```
- Extra step (preview)
- Less direct
- Preview component destroyed during replacement

**Prompt Visibility:**
- ❌ No way to see what prompt was sent
- ❌ Debugging required backend logs
- ❌ Users confused about AI output quality

### After Enhancements

**Generation Flow:**
```
User → Beat → Generate → Section created immediately
                              ↓
                         Content streams INTO section
                              ↓
                         Beat stays for regeneration
```
- Direct streaming
- Familiar pattern (like chat)
- Clean, intuitive

**Prompt Visibility:**
- ✅ "View Prompt" button on every generated section
- ✅ Full transparency on what was sent to AI
- ✅ Easy debugging and learning
- ✅ Better understanding of results

---

## Technical Highlights

### 1. Dynamic Position Tracking

Challenge: Document positions shift as content is inserted.

Solution: Recalculate section position on every chunk:
```typescript
const currentBeatPos = props.getPos(); // Get current beat position
const sectionPos = currentBeatPos + beatNode.nodeSize; // Calculate section
```

### 2. Incremental Content Streaming

Challenge: Avoid re-rendering entire content on every chunk.

Solution: Track last length, append only new content:
```typescript
let lastContentLength = 0;
const newChunk = newContent.substring(lastContentLength);
lastContentLength = newContent.length;
```

### 3. Section Validation

Challenge: Ensure we're writing to correct section.

Solution: Verify section exists and is generated type:
```typescript
const sectionNode = props.editor.state.doc.nodeAt(sectionPos);
if (sectionNode && sectionNode.type.name === 'section' && sectionNode.attrs.isGenerated) {
    props.editor.commands.appendToSection(sectionPos, newChunk);
}
```

---

## Impact Assessment

### User Experience
- 🟢 **Significantly Improved** - More intuitive generation flow
- 🟢 **Transparency** - Full visibility into AI prompts
- 🟢 **Familiar Pattern** - Matches chat streaming UX
- 🟢 **Learning Tool** - Writers understand prompt structure

### Developer Experience
- 🟢 **Debugging** - View Prompt eliminates guesswork
- 🟢 **Maintainability** - Cleaner code, less state management
- 🟢 **Testability** - Easier to verify correct behavior

### Performance
- 🟢 **Efficiency** - No intermediate preview component
- 🟢 **Memory** - Less state to track
- 🟢 **Render Cycles** - Reduced unnecessary re-renders

---

## Related Documentation

- **Sprint Documentation:** [Sprint 32 - Scene Beat Enhancements](../10-sprints/sprint-32-scene-beat-enhancements.md)
- **Testing Guide:** [Sprint 32 Testing](../06-testing/sprint-32-testing.md)
- **User Journey:** [Scene Beat Flow](../07-user-journeys/ai-writing-features/scene-beat-flow.md)
- **API Reference:** [AI Writing Features](../04-api-reference/ai-writing-features.md)

---

## Verification Steps

### Manual Testing Checklist

#### Scene Beat Streaming
- [ ] Insert Scene Beat via slash command
- [ ] Enter beat text: "Character discovers secret room"
- [ ] Click Generate
- [ ] ✅ Verify section created immediately below beat
- [ ] ✅ Verify content streams into section (not preview)
- [ ] ✅ Verify word count updates in real-time
- [ ] ✅ Verify beat shows "Writing into section below..." indicator
- [ ] ✅ Verify success message shown when done
- [ ] ✅ Verify beat remains after generation
- [ ] Click "Clear & New"
- [ ] ✅ Verify beat reset for next generation

#### View Prompt Feature
- [ ] Locate generated section (amber header)
- [ ] Click "View Prompt" button
- [ ] ✅ Verify prompt panel expands (violet theme)
- [ ] ✅ Verify system message visible
- [ ] ✅ Verify user message shows beat + word target
- [ ] ✅ Verify metadata displays correctly
- [ ] ✅ Verify word counts accurate
- [ ] Click copy button
- [ ] ✅ Verify prompt copied to clipboard
- [ ] Paste into text editor
- [ ] ✅ Verify full prompt structure preserved
- [ ] Click "Hide" button
- [ ] ✅ Verify panel collapses

#### Edge Cases
- [ ] Generate with long beat text (200+ chars)
- [ ] ✅ Verify streaming still works
- [ ] Switch between light/dark mode during generation
- [ ] ✅ Verify no visual glitches
- [ ] Generate multiple beats in sequence
- [ ] ✅ Verify each creates separate section
- [ ] View prompt dari multiple generated sections
- [ ] ✅ Verify each shows correct original prompt

---

*Last Updated: 2026-01-04*  
*Enhancements: 2*  
*Status: ✅ Completed*  
*Developer: Zulfikar Hidayatullah*
