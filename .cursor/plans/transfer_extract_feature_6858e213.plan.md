---
name: Transfer Extract Feature
overview: "Implement FG-04.3: Transfer &amp; Extract features to allow users to transfer AI chat responses to manuscript scenes, extract content to Codex entries, and extract structured data from conversations."
todos:
  - id: message-actions
    content: Add action buttons (Insert, Extract, Copy) to ChatMessage.vue with hover visibility
    status: completed
  - id: transfer-menu
    content: Create TransferMenu.vue component with scene selection dropdown
    status: completed
  - id: transfer-api
    content: Add transfer endpoint to ChatController.php with scene insertion logic
    status: completed
  - id: content-converter
    content: Create MarkdownToTipTap converter utility for content formatting
    status: completed
  - id: extract-codex-modal
    content: Create ExtractToCodexModal.vue with type selector and pre-fill logic
    status: completed
  - id: extract-api
    content: Add extract endpoint to ChatController.php for codex creation
    status: completed
  - id: extract-modal
    content: Create ExtractModal.vue for structured data extraction (P2)
    status: completed
  - id: editor-integration
    content: Integrate scene editor to receive transferred content at cursor
    status: in_progress
---

# FG-04.3: Transfer &amp; Extract Development Strategy

## Phase 1: Feature Understanding

### What This Feature Does

The Transfer &amp; Extract feature group enables writers to seamlessly move information from AI chat conversations into their project components:

- **F-04.3.1 (Transfer to Manuscript)**: Insert AI-generated content directly into scenes
- **F-04.3.2 (Extract to Codex)**: Create Codex entries from chat responses  
- **F-04.3.3 (Structured Extract)**: Bulk extract multiple items (scenes, codex entries, outline beats)

### Data Flow Summary

| Feature | Owner (Creator) | Consumer (Viewer) | Data Flow |

|---------|----------------|-------------------|-----------|

| Transfer to Scene | Chat Panel + Message Actions | Scene Editor | Chat Message -> Scene Content |

| Extract to Codex | Chat Panel + Extract Modal | Codex View | Chat Message -> Codex Entry |

| Structured Extract | Chat Panel + Extract Modal | Multiple targets | Chat Message -> AI Parse -> Multiple Items |

---

## Phase 2: Cross-Frontend Impact Analysis

### Owner Side (Chat Panel - Data Creation)

**Currently Exists:**

- [x] `ChatMessage.vue` - Message display with copy button
- [x] `ChatWindow.vue` - Message list container
- [x] `ChatPanel.vue` - Full chat panel orchestration
- [x] `ChatController.php` - API endpoints for chat

**Missing Implementation:**

- [ ] Action buttons on messages (Insert, Extract, Save as Snippet)
- [ ] Transfer destination selector (which scene/position)
- [ ] Extract to Codex modal with type selection
- [ ] Structured Extract modal with schema definition
- [ ] Copy conversation formats (JSON, YAML, Markdown)

### Consumer Side (Data Display)

**Scene Editor:**

- [ ] Accept inserted content at cursor position
- [ ] Accept content as new scene
- [ ] Handle content formatting (markdown to TipTap)

**Codex:**

- [x] Entry creation flow exists (`CodexController@quickCreate`)
- [ ] Pre-filled modal from chat content
- [ ] Multiple entry creation from structured extract

---

## Phase 3: Missing Implementation Detection

### F-04.3.1: Transfer to Manuscript

| Component | Status | Description |

|-----------|--------|-------------|

| Insert button on messages | Missing | Button to trigger transfer menu |

| TransferMenu.vue | Missing | Dropdown with transfer options |

| Insert at cursor API | Missing | Endpoint to insert content at cursor |

| Insert as new scene API | Partial | Scene creation exists, needs content param |

| Content format conversion | Missing | Markdown to TipTap JSON converter |

| Edit before insert modal | Missing | Preview/edit dialog |

### F-04.3.2: Extract to Codex

| Component | Status | Description |

|-----------|--------|-------------|

| Extract button on messages | Missing | Button to trigger extract modal |

| ExtractToCodexModal.vue | Missing | Modal with type selector and preview |

| Pre-fill name/description | Missing | Parse message for suggested values |

| Edit before saving | Partial | Can reuse QuickCreateModal pattern |

| Multiple entries from one message | Missing | UI for splitting content |

| Auto-detect potential entries | Missing | AI-assisted entity detection |

### F-04.3.3: Structured Extract

| Component | Status | Description |

|-----------|--------|-------------|

| ExtractModal.vue | Missing | Main extraction interface |

| Define extraction schema | Missing | Schema builder UI |

| AI parsing endpoint | Missing | Backend to parse content |

| Preview extracted data | Missing | Review UI before creation |

| Create Codex entries | Partial | Bulk creation exists |

| Create scenes | Missing | Bulk scene creation from beats |

| Create outline items | Missing | Integration with Plan feature |

---

## Phase 4: Gap Analysis

### Critical Gaps

1. **Message Actions Missing**: `ChatMessage.vue` only has copy button - needs Insert/Extract/Save actions
2. **No Transfer API**: No endpoint exists for `/api/chat/messages/{message}/transfer`
3. **No Extract API**: No endpoint exists for `/api/chat/messages/{message}/extract`
4. **Content Conversion**: No utility to convert markdown to TipTap JSON format
5. **Snippet Feature**: Referenced in NovelCrafter but `snippets` table doesn't exist (out of scope for this feature group)

### Integration Points Needed

- Chat -> Scene Editor: Event bus or Inertia shared state for cursor position
- Chat -> Codex: Modal integration with existing quick create flow
- Extract -> Multiple targets: Batch creation service

---

## Phase 5: Implementation Sequencing

### Dependencies

```
┌─────────────────────────────────────────────────────────────────┐
│                        Foundation Layer                          │
│  - Message action buttons (ChatMessage.vue)                      │
│  - Markdown to TipTap converter utility                          │
│  - Transfer/Extract API endpoints                                │
└──────────────────────────┬──────────────────────────────────────┘
                           │
         ┌─────────────────┼─────────────────┐
         ▼                 ▼                 ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│ F-04.3.1        │ │ F-04.3.2        │ │ F-04.3.3        │
│ Transfer Menu   │ │ Extract Codex   │ │ Structured      │
│ Insert to Scene │ │ Modal           │ │ Extract Modal   │
└─────────────────┘ └─────────────────┘ └─────────────────┘
```

### Priority Matrix

| Task | Priority | Reason |

|------|----------|--------|

| Message action buttons in ChatMessage.vue | P0 | Foundation for all features |

| TransferMenu.vue component | P0 | Required for F-04.3.1 |

| Content converter (MD -> TipTap) | P0 | Required for scene insertion |

| Transfer API endpoint | P0 | Backend for F-04.3.1 |

| ExtractToCodexModal.vue | P1 | F-04.3.2 core |

| Extract API endpoint | P1 | Backend for F-04.3.2 |

| ExtractModal.vue (structured) | P2 | F-04.3.3 enhancement |

| AI-assisted extraction | P2 | Nice to have |

---

## Phase 6: Detailed Implementation Plan

### Backend Changes

**New API Endpoints:**

```
POST /api/chat/messages/{message}/transfer
 - target_type: 'scene' | 'new_scene'
 - scene_id: (required if target_type=scene)
 - chapter_id: (required if target_type=new_scene)
 - position: 'cursor' | 'end' | 'replace'
 - content: (optional, for edited content)

POST /api/chat/messages/{message}/extract
 - extract_type: 'codex' | 'structured'
 - entries: [{type, name, description}] (for codex)
 - schema: {} (for structured)
```

**New Service:**

```
app/Services/Chat/TransferService.php
 - transferToScene(ChatMessage, Scene, position)
 - transferToNewScene(ChatMessage, Chapter)
 - convertMarkdownToTipTap(content)

app/Services/Chat/ExtractService.php
 - extractToCodex(ChatMessage, entries[])
 - parseStructuredContent(ChatMessage, schema)
```

### Frontend Changes

**New Components:**

```
resources/js/components/chat/
├── MessageActions.vue       # Action buttons for messages
├── TransferMenu.vue         # Transfer destination selector
├── ExtractToCodexModal.vue  # Single codex entry creation
└── ExtractModal.vue         # Structured extraction interface
```

**Modified Components:**

```
ChatMessage.vue
 - Add action bar (Insert, Extract, Copy, Save)
 - Add hover state for action visibility
 - Emit events for action triggers

ChatWindow.vue / ChatPanel.vue
 - Handle transfer/extract events
 - Open appropriate modals
 - Coordinate with scene editor
```

### Key Files to Modify

1. [`app/Http/Controllers/ChatController.php`](app/Http/Controllers/ChatController.php) - Add transfer/extract methods
2. [`resources/js/components/chat/ChatMessage.vue`](resources/js/components/chat/ChatMessage.vue) - Add action buttons
3. [`resources/js/components/workspace/ChatPanel.vue`](resources/js/components/workspace/ChatPanel.vue) - Handle action events

---

## Phase 7: User Journeys

### Journey 1: Transfer AI Response to Scene

**Owner Journey (Chat Panel):**

1. User reads AI response in chat
2. User clicks "Insert" button on message (appears on hover)
3. Transfer menu opens with options:

                                                                                                - "Insert at cursor" (if editor is open)
                                                                                                - "Insert at end of scene"
                                                                                                - "Create new scene"

4. User selects "Insert at cursor"
5. Content is converted and inserted at editor cursor
6. User sees success toast notification

**Consumer Journey (Scene Editor):**

1. Content appears at cursor position
2. Content is properly formatted (markdown converted)
3. Scene word count updates
4. Auto-save triggers

### Journey 2: Extract Character to Codex

**Owner Journey (Chat Panel):**

1. User asks AI "Describe the main character Elena"
2. AI responds with detailed character description
3. User clicks "Extract" button on message
4. Extract to Codex modal opens
5. Modal pre-fills:

                                                                                                - Name: "Elena" (detected from content)
                                                                                                - Type: "Character" (suggested)
                                                                                                - Description: (first paragraph of response)

6. User reviews and clicks "Create Entry"
7. Codex entry is created

### Journey 3: Bulk Extract Scene Beats

**Owner Journey (Chat Panel):**

1. User asks AI "Generate 5 scene beats for Chapter 3"
2. AI responds with numbered list of beats
3. User clicks "Extract" button
4. User selects "Extract as Scene Beats"
5. Modal shows parsed beats as editable list
6. User reviews, adjusts, clicks "Create All"
7. 5 new scenes are created in Chapter 3

---

## Technical Notes

### Content Conversion Strategy

The TipTap editor uses ProseMirror JSON format. Key conversions needed:

- `# Heading` -> `{ type: 'heading', attrs: { level: 1 }, content: [...] }`
- `**bold**` -> `{ type: 'text', marks: [{ type: 'bold' }], text: '...' }`
- Paragraphs -> `{ type: 'paragraph', content: [...] }`

Consider using `@tiptap/pm` or a markdown parser like `marked` + custom renderer.

### Scene Editor Integration

Two approaches for cursor insertion:

1. **Shared State**: Store cursor position in Pinia/composable, chat reads it
2. **Event Bus**: Chat emits event, editor listens and inserts

Recommended: Shared state via composable for cleaner architecture.

### Existing Patterns to Follow

- Modal pattern: [`QuickCreateModal.vue`](resources/js/components/codex/modals/QuickCreateModal.vue)
- API pattern: Existing `ChatController` structure
- Form pattern: `useForm` from Inertia for submissions
