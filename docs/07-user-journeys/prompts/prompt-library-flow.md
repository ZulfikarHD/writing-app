# User Journey: Prompt Library Management

**Feature:** Prompt Library Core (FG-05.1)  
**User Type:** Writer / Author  
**Last Updated:** 2026-01-03

---

## Overview

User journey untuk mengelola AI prompts dari browsing, creating, editing, hingga organizing prompts dalam Prompt Library, yaitu: memahami full workflow user dalam menggunakan prompt system dan identifying potential UX pain points.

---

## Journey 1: Discovering & Using System Prompts

**Goal:** User menemukan dan menggunakan built-in system prompts

### Flow Diagram

```
📍 START: User Dashboard / Workspace
    │
    ├─▶ Navigate to: Prompts Library
    │   └─ Method 1: Sidebar menu "Prompts"
    │   └─ Method 2: Workspace sidebar → "Manage Prompts"
    │   └─ Method 3: Settings → AI Settings → Prompts
    │
    ├─▶ View: Prompt Library Page
    │   ├─ Statistics cards (System: 4, User: 0, Total: 4)
    │   ├─ Filter chips: [All] [Chat] [Prose] [Replacement] [Summary]
    │   └─ Grid of system prompt cards
    │
    ├─▶ Browse: System Prompts
    │   ├─ See 4 system prompts dengan badge "System"
    │   ├─ Each card shows:
    │   │   ├─ Name
    │   │   ├─ Type badge
    │   │   ├─ Description preview
    │   │   └─ Usage count
    │   └─ Notice: Edit/Delete buttons disabled
    │
    ├─▶ Select: "Creative Writing Assistant" (Prose type)
    │   └─ Click card to view details (future: modal)
    │
    ├─▶ Decision Point: Use as-is or customize?
    │   │
    │   ├─▶ Option A: Use directly
    │   │   └─ Go to Chat/Workspace → Select from dropdown
    │   │
    │   └─▶ Option B: Clone for customization
    │       └─ Continue to Journey 2
    │
✅ END: System prompt discovered and understood
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Navigates to Prompts page | Page loads with system prompts | Shows 4 system prompts |
| 2 | Views prompt cards | Cards display with badges | "System" badge visible |
| 3 | Hovers over system prompt | Actions revealed but Edit/Delete disabled | Only Clone button enabled |
| 4 | Clicks prompt card | (Future) Modal shows details | View prompt content |
| 5 | Uses prompt in chat | Prompt applied to AI | AI responds accordingly |

### Pain Points & Solutions

| Pain Point | Severity | Solution Implemented |
|------------|----------|----------------------|
| User doesn't understand "System" badge | 🟢 Low | Tooltip: "Built-in prompts, read-only" |
| Can't find how to use prompt | 🟡 Medium | "Manage Prompts" link in workspace |
| Unclear which type to use | 🟡 Medium | Type badges with clear labels |

---

## Journey 2: Cloning & Customizing System Prompt

**Goal:** User clones system prompt untuk customization

### Flow Diagram

```
📍 START: Prompt Library (dari Journey 1)
    │
    ├─▶ Select: System prompt to clone
    │   └─ Example: "Scene Summarizer"
    │
    ├─▶ Click: "Clone" button
    │   └─ Action button appears on hover/tap
    │
    ├─▶ Dialog: "Clone Prompt"
    │   ├─ Pre-filled name: "Scene Summarizer (Copy)"
    │   ├─ Input field: Editable name
    │   └─ Buttons: [Cancel] [Clone]
    │
    ├─▶ Enter: Custom name
    │   └─ Example: "My Chapter Summarizer"
    │
    ├─▶ Click: "Clone" button
    │   └─ Loading state: "Cloning..."
    │
    ├─▶ Success: Toast notification
    │   └─ "Prompt cloned successfully"
    │
    ├─▶ View: Cloned prompt in list
    │   ├─ New card appears (without "System" badge)
    │   ├─ Name: "My Chapter Summarizer"
    │   ├─ Usage count: 0
    │   └─ All action buttons enabled (Edit/Clone/Delete)
    │
    ├─▶ Click: "Edit" on cloned prompt
    │   └─ Continue to Journey 3
    │
✅ END: Custom version of system prompt created
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Hovers system prompt card | Clone button appears | Button visible & clickable |
| 2 | Clicks "Clone" | Dialog opens with name field | Pre-filled with "(Copy)" suffix |
| 3 | Edits name | Input updates in real-time | No validation errors |
| 4 | Clicks "Clone" | API POST request | Status 201, success toast |
| 5 | Views list | New prompt appears | Card visible, editable |

### Technical Flow

```
[Frontend] PromptCard.vue
    │
    ├─▶ Click clone button
    │   └─ Emits: @clone="handleClone(prompt)"
    │
[Frontend] Prompts/Index.vue
    │
    ├─▶ Receives clone event
    │   └─ Opens dialog with name input
    │
    ├─▶ User enters name
    │   └─ Validates: min 3 chars
    │
    ├─▶ User confirms
    │   └─ Calls: usePrompts.clonePrompt()
    │
[Composable] usePrompts.ts
    │
    ├─▶ POST /api/prompts/{id}/clone
    │   └─ Body: { name: "Custom Name" }
    │
[Backend] PromptController@clone
    │
    ├─▶ Validates access (system or own)
    │   └─ 403 if unauthorized
    │
    ├─▶ Delegates to PromptService
    │
[Service] PromptService@clonePrompt
    │
    ├─▶ Copies prompt data
    │   ├─ system_message
    │   ├─ user_message
    │   ├─ type
    │   └─ model_settings
    │
    ├─▶ Sets new properties
    │   ├─ user_id: current user
    │   ├─ is_system: false
    │   ├─ usage_count: 0
    │   └─ sort_order: auto
    │
    └─▶ Creates new Prompt record
        └─ Returns: cloned prompt
```

---

## Journey 3: Creating Custom Prompt from Scratch

**Goal:** User membuat prompt baru sesuai kebutuhan spesifik

### Flow Diagram

```
📍 START: Prompt Library
    │
    ├─▶ Click: "Create Prompt" button (top-right)
    │   └─ Button prominently placed
    │
    ├─▶ Opens: Prompt Editor Modal/Slide-over
    │   ├─ Title: "Create New Prompt"
    │   ├─ Form fields visible
    │   └─ Empty state (no data pre-filled)
    │
    ├─▶ Step 1: Basic Info
    │   ├─ Enter name: "Chapter Brainstorming"
    │   ├─ Enter description: "Help brainstorm chapter ideas"
    │   └─ Select type: "Workshop Chat"
    │
    ├─▶ Step 2: Configure Messages
    │   ├─ System Message:
    │   │   └─ "You are a creative brainstorming partner..."
    │   ├─ User Message (optional):
    │   │   └─ "Help me brainstorm: {topic}"
    │   └─ Textareas: Syntax highlighting, auto-resize
    │
    ├─▶ Step 3: Model Settings (Optional, Expandable)
    │   ├─ Expand "Advanced Settings"
    │   ├─ Temperature: 0.9 (slider)
    │   ├─ Max Tokens: 500 (input)
    │   └─ Other parameters...
    │
    ├─▶ Validation Check
    │   ├─ ✅ Name: Required, present
    │   ├─ ✅ Type: Required, selected
    │   └─ ✅ All rules passed
    │
    ├─▶ Click: "Save Prompt" button
    │   └─ Loading state: Button disabled, spinner
    │
    ├─▶ API Request: POST /api/prompts
    │   └─ Payload: { name, description, type, ... }
    │
    ├─▶ Success Response
    │   ├─ Status: 201 Created
    │   ├─ Toast: "Prompt created successfully"
    │   └─ Modal closes
    │
    ├─▶ View: New prompt in library
    │   ├─ Card appears at top/sorted position
    │   ├─ Badge shows type: "Workshop Chat"
    │   └─ Usage count: 0
    │
✅ END: Custom prompt created and ready to use
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Clicks "Create Prompt" | Editor modal opens | Empty form displayed |
| 2 | Fills required fields | Real-time validation | No errors shown |
| 3 | Enters system message | Textarea auto-expands | Text visible, no overflow |
| 4 | (Optional) Configures model settings | Sliders/inputs update | Values within valid ranges |
| 5 | Clicks "Save" | API POST, loading state | Button disabled during save |
| 6 | Receives success | Modal closes, toast appears | New prompt visible in list |

### Validation Rules

| Field | Rule | Error Message |
|-------|------|---------------|
| Name | Required, max 255 chars | "Name is required" |
| Type | Required, valid enum | "Please select a type" |
| Model Settings | Valid JSON | "Invalid model settings format" |
| Folder | Exists in user's folders | "Selected folder not found" |

---

## Journey 4: Editing Existing User Prompt

**Goal:** User modifies prompt yang sudah dibuat sebelumnya

### Flow Diagram

```
📍 START: Prompt Library
    │
    ├─▶ Locate: User's own prompt
    │   └─ Example: "My Chapter Summarizer"
    │
    ├─▶ Hover/Tap: Prompt card
    │   └─ Actions appear: [Edit] [Clone] [Delete]
    │
    ├─▶ Click: "Edit" button
    │   └─ Icon: Pencil/Edit
    │
    ├─▶ Opens: Prompt Editor with existing data
    │   ├─ Title: "Edit Prompt"
    │   ├─ Pre-filled fields:
    │   │   ├─ Name: "My Chapter Summarizer"
    │   │   ├─ Type: "Scene Summarization" (disabled)
    │   │   ├─ System message: (existing text)
    │   │   └─ Model settings: (existing values)
    │   └─ Note: Type field read-only after creation
    │
    ├─▶ Make Changes
    │   ├─ Update system message
    │   ├─ Adjust temperature: 0.7 → 0.8
    │   └─ Add description
    │
    ├─▶ Decision Point
    │   │
    │   ├─▶ Option A: Save changes
    │   │   ├─ Click "Save"
    │   │   ├─ API PATCH request
    │   │   ├─ Success toast
    │   │   └─ Modal closes
    │   │
    │   └─▶ Option B: Discard changes
    │       ├─ Click "Cancel" or X
    │       ├─ Confirmation: "Discard changes?"
    │       └─ Modal closes, no save
    │
    ├─▶ View: Updated prompt
    │   ├─ Changes reflected in card
    │   └─ Updated timestamp visible
    │
✅ END: Prompt successfully updated
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Hovers user prompt | Edit button appears | Button visible |
| 2 | Clicks "Edit" | Editor opens with data | All fields pre-populated |
| 3 | Modifies fields | Changes tracked | Unsaved indicator shown |
| 4 | Clicks "Save" | API PATCH, loading | Button disabled during save |
| 5 | Receives success | Modal closes, toast | Card updates immediately |

### Edge Cases

| Scenario | Expected Behavior |
|----------|-------------------|
| Edit system prompt | Button disabled, tooltip: "System prompts cannot be edited" |
| Edit other user's prompt | 403 error, redirect to library |
| Network error during save | Error toast, changes not lost, retry option |
| Concurrent edit (multi-tab) | Last write wins, consider refresh prompt |

---

## Journey 5: Organizing Prompts (Future: Folders)

**Goal:** User organizes prompts into folders untuk better management

### Flow Diagram

```
📍 START: Prompt Library (Future state)
    │
    ├─▶ View: Sidebar with folders
    │   ├─ 📁 Character Development (3)
    │   ├─ 📁 Scene Writing (5)
    │   ├─ 📁 Editing & Revisions (2)
    │   └─ 📂 Uncategorized (8)
    │
    ├─▶ Action: Create new folder
    │   ├─ Click "New Folder" button
    │   ├─ Dialog: Name & color picker
    │   ├─ Enter: "Plot Development"
    │   ├─ Select color: Purple
    │   └─ Folder created
    │
    ├─▶ Action: Move prompt to folder
    │   ├─ Drag prompt card
    │   ├─ Drop on folder
    │   └─ Prompt moves, count updates
    │
    ├─▶ Action: Nested folders (optional)
    │   ├─ Create subfolder inside existing
    │   └─ Hierarchy: Character > Antagonist Prompts
    │
✅ END: Prompts organized by folder
```

> ⚠️ **Note:** Folder UI not implemented in Sprint 24. Database schema ready for future implementation.

---

## Journey 6: Deleting Unused Prompts

**Goal:** User removes prompts that are no longer needed

### Flow Diagram

```
📍 START: Prompt Library
    │
    ├─▶ Locate: Prompt to delete
    │   └─ Example: Old experimental prompt
    │
    ├─▶ Hover: Prompt card
    │   └─ Delete button (trash icon) appears
    │
    ├─▶ Click: "Delete" button
    │   └─ Red/destructive styling
    │
    ├─▶ Confirmation Dialog
    │   ├─ Title: "Delete Prompt?"
    │   ├─ Message: "This action cannot be undone."
    │   ├─ Prompt name: "Old Experimental Prompt"
    │   └─ Buttons: [Cancel] [Delete]
    │
    ├─▶ Decision Point
    │   │
    │   ├─▶ Option A: Confirm delete
    │   │   ├─ Click "Delete"
    │   │   ├─ API DELETE request
    │   │   ├─ Success toast: "Prompt deleted"
    │   │   └─ Card fades out, removed from list
    │   │
    │   └─▶ Option B: Cancel
    │       └─ Dialog closes, no changes
    │
✅ END: Prompt removed from library
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Hovers prompt card | Delete button appears | Red trash icon visible |
| 2 | Clicks "Delete" | Confirmation dialog opens | Warning message clear |
| 3 | Confirms deletion | API DELETE, loading | Dialog disabled during delete |
| 4 | Receives success | Card animates out, toast | Prompt removed from DB & list |

### Safety Measures

| Measure | Implementation |
|---------|----------------|
| Confirmation required | Always show dialog before delete |
| System prompt protection | Delete button disabled + tooltip |
| Soft delete (future) | Consider adding deleted_at column |
| Undo option (future) | Trash folder for 30 days |

---

## Journey 7: Using Prompts in Workspace (Integration)

**Goal:** User applies prompt while writing in workspace

### Flow Diagram

```
📍 START: Writing Workspace
    │
    ├─▶ Scenario: User writing a scene, needs AI help
    │   └─ Current text: "The detective entered the dark alley..."
    │
    ├─▶ Open: Chat Panel
    │   └─ Click chat icon in workspace
    │
    ├─▶ Select: Prompt from dropdown
    │   ├─ Dropdown shows:
    │   │   ├─ Workshop Chat (3)
    │   │   ├─ Scene Beat Completion (2)
    │   │   ├─ Text Replacement (1)
    │   │   └─ Scene Summarization (1)
    │   └─ Click: "Creative Writing Assistant" (Prose)
    │
    ├─▶ Prompt Applied
    │   ├─ System message loaded to AI
    │   ├─ User message template populated
    │   └─ Variables replaced (if any)
    │
    ├─▶ User: Enters additional input
    │   └─ Example: "Continue this scene with suspense"
    │
    ├─▶ Click: "Send" or press Enter
    │   └─ API: POST /api/prompts/{id}/usage (tracks usage)
    │
    ├─▶ AI Response
    │   └─ Generated text appears in chat
    │
    ├─▶ User: Applies suggestion to manuscript
    │   └─ Copy/paste or one-click insert
    │
    ├─▶ Quick Access (Sidebar)
    │   ├─ Prompts section in workspace sidebar
    │   ├─ Shows recently used prompts
    │   └─ Click prompt → applies to chat instantly
    │
✅ END: Prompt used to assist writing
```

### User Actions

| Step | User Action | System Response | Success Criteria |
|------|-------------|-----------------|------------------|
| 1 | Opens chat in workspace | Prompt selector visible | Dropdown populated with prompts |
| 2 | Selects prompt | Prompt loaded | System message applied |
| 3 | Sends message | Usage recorded | AI responds with prompt context |
| 4 | Views sidebar prompts | Quick list shown | Recently used prompts highlighted |

---

## Common Pain Points & Solutions

| Pain Point | User Quote | Severity | Solution | Status |
|------------|------------|----------|----------|--------|
| Can't find prompt library | "Where do I manage prompts?" | 🟡 Medium | Add to main nav & workspace sidebar | ✅ Done |
| Unclear type differences | "What's the difference between types?" | 🟢 Low | Type descriptions in selector | 🔄 Future |
| Can't preview before using | "How do I know what this does?" | 🟡 Medium | Click card to view details | 🔄 Future |
| Lost track of custom prompts | "Which ones did I create?" | 🟢 Low | Visual separator (System vs User) | ✅ Done |
| Want to share prompts | "Can I share with team?" | 🟢 Low | Export/import feature | 🔄 Future |

---

## Metrics & Success Criteria

### User Engagement Metrics

| Metric | Target | How to Measure |
|--------|--------|----------------|
| Prompt creation rate | 2+ prompts per user | Track POST /api/prompts |
| System prompt clone rate | 50% of users | Track clone endpoint |
| Prompt usage frequency | 5+ uses per week | Track usage_count |
| Time to first prompt | < 2 minutes | Analytics: registration → first prompt |

### UX Quality Metrics

| Metric | Target | How to Measure |
|--------|--------|----------------|
| Task completion rate | > 95% | Track successful saves |
| Error rate | < 5% | Track validation errors |
| Time to create prompt | < 1 minute | Analytics: modal open → save |
| User satisfaction | > 4.5/5 | Post-feature survey |

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 24: Prompts Library Core](../../10-sprints/sprint-24-prompts-library-core.md)
- **API Reference:** [Prompts API](../../04-api-reference/prompts.md)
- **Testing Guide:** [Prompts Testing](../../06-testing/prompts-testing.md)

---

*Last Updated: 2026-01-03*
