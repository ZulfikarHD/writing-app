# User Journey: Prompt Editor Flow

## Overview

Dokumen ini menjelaskan user journey untuk Prompt Editor system, yaitu: workflow untuk membuat, mengedit, dan menggunakan AI prompts dengan fitur advanced seperti variable autocomplete, multi-message support, dan prompt inputs.

---

## Journey 1: Creating a New Prompt with Variables

**Actor:** Writer  
**Goal:** Membuat custom prompt dengan variable system

```
📍 START: Prompt Library (/prompts)
    │
    ├─▶ Click "New" button in sidebar
    │   └─ Editor opens with empty form
    │
    ├─▶ General Tab:
    │   ├─ Enter prompt name
    │   ├─ Select type (Chat, Prose, Replacement, Summary)
    │   └─ Optionally configure model settings
    │
    ├─▶ Instructions Tab:
    │   ├─ Type system message
    │   ├─ Type "{" to trigger autocomplete
    │   │   └─ Select variable from dropdown
    │   ├─ Variable inserted: {scene.title}
    │   └─ Continue writing with more variables
    │
    ├─▶ Preview Tab:
    │   ├─ See resolved preview with sample data
    │   ├─ Check token count estimate
    │   └─ Copy preview to clipboard if needed
    │
    └─▶ Click "Create Prompt"
        └─ SUCCESS: Prompt saved, appears in library
```

---

## Journey 2: Editing a Prompt with Multi-Message

**Actor:** Writer  
**Goal:** Menambahkan multiple conversation turns ke prompt

```
📍 START: Prompt Library
    │
    ├─▶ Click on existing prompt
    │   └─ Editor opens with prompt data
    │
    ├─▶ Instructions Tab:
    │   ├─ Scroll to "Additional Messages" section
    │   ├─ Click "Add Message"
    │   │   └─ New message block appears (User role)
    │   ├─ Enter user message content
    │   ├─ Click role badge to toggle to "AI"
    │   │   └─ Message changes to AI/Assistant role
    │   ├─ Drag to reorder messages
    │   │   └─ Messages rearranged
    │   └─ Click duplicate icon to copy message
    │
    └─▶ Click "Save Changes"
        └─ SUCCESS: Prompt updated with multi-turn conversation
```

---

## Journey 3: Defining Prompt Inputs

**Actor:** Writer  
**Goal:** Membuat prompt dengan dynamic inputs

```
📍 START: Prompt Editor
    │
    ├─▶ Advanced Tab:
    │   ├─ Click "Add Input" button
    │   │   └─ New input section appears (expanded)
    │   │
    │   ├─ Configure Input:
    │   │   ├─ Variable Name: "word_count"
    │   │   ├─ Display Label: "Target Word Count"
    │   │   ├─ Input Type: Select dropdown
    │   │   ├─ Add options:
    │   │   │   ├─ Value: "500", Label: "Short"
    │   │   │   └─ Value: "1000", Label: "Medium"
    │   │   └─ Set default value: "500"
    │   │
    │   └─ See usage hint:
    │       └─ "Use in prompt as: {input("word_count")}"
    │
    ├─▶ Instructions Tab:
    │   └─ Insert: {input("word_count")} into message
    │
    └─▶ Save prompt
        └─ When prompt runs, user will be asked to select word count first
```

---

## Journey 4: Using Components in Prompts

**Actor:** Writer  
**Goal:** Menyisipkan reusable component ke prompt

```
📍 START: Prompt Editor
    │
    ├─▶ Advanced Tab:
    │   └─ "Available Components" section shows all components
    │       ├─ Click copy button on component
    │       │   └─ {include("name")} copied to clipboard
    │       ├─ Click "+" button to insert directly
    │       │   └─ Component inserted into current message
    │       └─ See system vs user-created badges
    │
    ├─▶ Instructions Tab - Autocomplete:
    │   └─ Type "{include(" to trigger autocomplete
    │       └─ Select component from dropdown
    │           └─ {include("writing_style")} inserted
    │
    └─▶ Preview Tab:
        ├─ Toggle "Show with sample data"
        └─ See component content resolved inline (cyan highlight)
```

---

## Journey 4b: Creating a New Component

**Actor:** Writer  
**Goal:** Membuat reusable component baru

```
📍 START: Workspace Sidebar
    │
    ├─▶ Click "Prompts" section in sidebar
    │   └─ Prompts Quick List appears
    │
    ├─▶ Click "Blocks" tab
    │   └─ Component list shown (or empty state)
    │
    ├─▶ Click "+ New Component"
    │   └─ ComponentEditor modal opens
    │
    ├─▶ Fill Component Details:
    │   ├─ Name (Identifier): "writing_style"
    │   │   └─ Must be valid identifier (letters, numbers, underscores)
    │   ├─ Display Label: "Writing Style Guide"
    │   └─ Instructions Tab:
    │       └─ Enter reusable instructions content
    │
    ├─▶ Click "Copy include() call"
    │   └─ {include("writing_style")} copied to clipboard
    │
    └─▶ Click "Create Component"
        └─ SUCCESS: Component appears in Blocks list
            └─ Can now be used in any prompt
```

---

## Journey 5: Customizing System Prompt

**Actor:** Writer  
**Goal:** Clone dan customize system prompt

```
📍 START: Prompt Library
    │
    ├─▶ Click on System prompt (has "System" badge)
    │   └─ Editor shows read-only mode
    │       └─ Yellow notice: "System prompt cannot be edited"
    │
    ├─▶ Click "Duplicate to Edit" button
    │   └─ Confirmation dialog appears
    │
    ├─▶ Confirm clone
    │   └─ New prompt created: "{Name} (Custom)"
    │       └─ Editor now in editable mode
    │
    ├─▶ Customize:
    │   ├─ Modify system message
    │   ├─ Adjust model settings
    │   └─ Add custom inputs
    │
    └─▶ Save
        └─ Custom version saved in user's library
```

---

## Journey 6: Previewing with Sample Data

**Actor:** Writer  
**Goal:** Test prompt dengan sample data sebelum menggunakan

```
📍 START: Prompt Editor (any prompt)
    │
    ├─▶ Preview Tab:
    │   ├─ See raw prompt content
    │   │   └─ Variables shown as: {scene.title}
    │   │
    │   ├─ Toggle "Show with sample data" checkbox
    │   │   └─ Variables replaced with sample values:
    │   │       └─ "Chapter 1 - The Beginning"
    │   │
    │   ├─ View Legend:
    │   │   ├─ Purple highlight = Resolved variable
    │   │   ├─ Amber highlight = Unresolved variable
    │   │   └─ Cyan highlight = Component reference
    │   │
    │   ├─ Check token count:
    │   │   └─ "~245 tokens"
    │   │
    │   └─ Click "Copy" button
    │       └─ Full prompt copied to clipboard
    │
    └─▶ Return to edit if needed
```

---

## Journey 7: Quick Edit from Workspace

**Actor:** Writer  
**Goal:** Edit prompt tanpa leave workspace

```
📍 START: Novel Workspace (workspace sidebar open)
    │
    ├─▶ Click "Prompts" in sidebar
    │   └─ Prompts quick list appears
    │
    ├─▶ Click on prompt name
    │   └─ Prompt Modal opens (overlay on workspace)
    │
    ├─▶ Edit in modal:
    │   ├─ Same 5 tabs as full editor
    │   ├─ Full autocomplete functionality
    │   └─ Multi-message support
    │
    └─▶ Save or Close
        └─ Return to workspace without navigation
```

---

## State Diagram: Prompt Editing

```
┌───────────────────────────────────────────────────────────┐
│                    PROMPT STATES                          │
├───────────────────────────────────────────────────────────┤
│                                                           │
│   ┌─────────┐      Create       ┌─────────┐              │
│   │  Empty  │ ───────────────▶ │  Draft  │              │
│   └─────────┘                   └────┬────┘              │
│                                      │                    │
│                                      │ Save               │
│                                      ▼                    │
│   ┌─────────┐      Clone       ┌─────────┐              │
│   │ System  │ ───────────────▶ │  Saved  │◀────┐        │
│   │(locked) │                   └────┬────┘     │        │
│   └─────────┘                        │          │        │
│                                      │ Edit     │ Save   │
│                                      ▼          │        │
│                                 ┌─────────┐     │        │
│                                 │Modified │─────┘        │
│                                 │(unsaved)│              │
│                                 └─────────┘              │
│                                                           │
└───────────────────────────────────────────────────────────┘
```

---

## Access Points

| Entry Point | Location | Opens |
|-------------|----------|-------|
| Library Page | `/prompts` | Full-page PromptEditor |
| Workspace Sidebar | Novel Workspace | PromptModal (overlay) |
| Quick Create | Sidebar button | PromptModal with empty form |

---

---

## Journey 8: Running Prompt with Inputs

**Actor:** Writer  
**Goal:** Menjalankan prompt yang memerlukan input

```
📍 START: Workspace Chat or Editor
    │
    ├─▶ Trigger prompt yang memiliki inputs
    │   └─ InputForm modal appears
    │
    ├─▶ InputForm shows:
    │   ├─ Prompt name di header
    │   ├─ All defined input fields:
    │   │   ├─ Text inputs (single line)
    │   │   ├─ Textarea (multi-line)
    │   │   ├─ Select dropdowns
    │   │   ├─ Number inputs
    │   │   └─ Checkbox toggles
    │   ├─ Default values pre-filled
    │   └─ Required fields marked with *
    │
    ├─▶ Fill input values:
    │   ├─ Enter custom values
    │   └─ Validation prevents submit if required empty
    │
    └─▶ Click "Run Prompt"
        └─ SUCCESS: Prompt executed with values
            └─ {input("name")} replaced with actual values
```

---

## Related Documentation

- **API Reference:** [Prompts API](../../04-api-reference/prompts.md)
- **Testing Guide:** [Prompts Testing](../../06-testing/prompts-testing.md)
- **Advanced Features Testing:** [FG-05.4 Testing](../../06-testing/prompt-advanced-features-testing.md)
- **Sprint Doc:** [Sprint 27 - Advanced Features](../../10-sprints/sprint-27-prompt-advanced-features.md)

---

*Last Updated: 2026-01-04*
