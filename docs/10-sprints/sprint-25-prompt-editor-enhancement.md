# 📦 Sprint 25: Prompt Editor Enhancement

**Version:** 1.0.0  
**Date:** 2026-01-03  
**Duration:** 1 Sprint  
**Status:** ✅ Completed

## 📋 Sprint Goals

Sprint ini merupakan implementasi lanjutan dari Prompt System (FG-05.2), yaitu: menambahkan multi-tab editor interface, function-based variable system dengan autocomplete, multi-message support, dan prompt inputs/components management.

---

## ✨ Features Implemented

### 1. Multi-Tab Editor Interface
- Refactored editor dari 3 tabs menjadi 5 tabs: **General**, **Instructions**, **Advanced**, **Description**, **Preview**
- Consistent experience antara full-page editor (`PromptEditor.vue`) dan modal editor (`PromptModal.vue`)
- Smooth tab transitions dengan motion-v animations

### 2. Function-Based Variable System
- Variable syntax: `{variable_name}` dan `{function(params)}`
- Autocomplete dropdown triggered by typing `{`
- Fuzzy search dengan category grouping
- Comprehensive variable registry (40+ variables):
  - Acts/Chapters/Scenes context
  - Codex entries (characters, locations, lore)
  - Text manipulation (wordCount, firstWords, lastWords)
  - Logic functions (ifs, isEmpty)
  - Composition (include, input)

### 3. Multi-Message Support
- Multiple User/AI message turns dalam satu prompt
- Drag-and-drop reordering
- Role toggle (User ↔ AI)
- Message duplication

### 4. Prompt Inputs System
- Dynamic input fields yang user isi sebelum menjalankan prompt
- Input types: text, textarea, select, number, checkbox
- Support untuk options pada select type
- Required/optional fields

### 5. Prompt Components System
- Reusable text snippets dengan syntax `[[component_name]]`
- CRUD operations untuk components
- Clone functionality
- System vs user-owned components

### 6. Enhanced Preview
- Real-time preview dengan variable resolution
- Sample data untuk testing
- Token count estimation
- Copy to clipboard functionality

---

## 📁 File Structure

### Database Migrations

```
database/migrations/
├── 2026_01_03_110000_create_prompt_inputs_table.php     ✨ NEW
├── 2026_01_03_110001_create_prompt_components_table.php ✨ NEW
└── 2026_01_03_110002_add_messages_to_prompts_table.php  ✨ NEW
```

### Backend Models

```
app/Models/
├── Prompt.php              ✏️ UPDATED (added messages, inputs relation)
├── PromptInput.php         ✨ NEW
└── PromptComponent.php     ✨ NEW
```

### Backend Controllers

```
app/Http/Controllers/
├── PromptController.php         ✏️ UPDATED (formatPrompt with inputs)
├── PromptInputController.php    ✨ NEW
└── PromptComponentController.php ✨ NEW
```

### Backend Services

```
app/Services/Prompts/
├── PromptService.php        (existing)
├── VariableResolver.php     ✨ NEW
└── ComponentResolver.php    ✨ NEW
```

### Frontend - Editor Components

```
resources/js/components/prompts/editor/
├── TabGeneral.vue           ✨ NEW - Name, type, model settings
├── TabInstructions.vue      ✨ NEW - Messages with autocomplete
├── TabAdvanced.vue          ✨ NEW - Inputs & components
├── TabDescription.vue       ✨ NEW - Description editor
├── PromptPreviewPanel.vue   ✨ NEW - Live preview
├── MessageList.vue          ✨ NEW - Multi-message management
├── MessageItem.vue          ✨ NEW - Single message editor
└── VariableAutocomplete.vue ✨ NEW - Variable autocomplete dropdown
```

### Frontend - Main Components

```
resources/js/components/prompts/
├── PromptEditor.vue         ✏️ UPDATED (5 tabs, uses editor components)
└── PromptModal.vue          ✏️ UPDATED (5 tabs, uses editor components)
```

### Frontend - Composables

```
resources/js/composables/
└── usePrompts.ts            ✏️ UPDATED (added PromptMessage, PromptInput, PromptComponent types)
```

---

## 🔌 API Endpoints Summary

### Prompt Inputs (NEW)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/prompts/{prompt}/inputs` | List inputs |
| POST | `/api/prompts/{prompt}/inputs` | Create input |
| PUT | `/api/prompts/{prompt}/inputs/bulk` | Bulk update/reorder |
| PATCH | `/api/prompts/{prompt}/inputs/{input}` | Update input |
| DELETE | `/api/prompts/{prompt}/inputs/{input}` | Delete input |

### Prompt Components (NEW)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/prompt-components` | List components |
| POST | `/api/prompt-components` | Create component |
| GET | `/api/prompt-components/{id}` | Get detail |
| PATCH | `/api/prompt-components/{id}` | Update |
| DELETE | `/api/prompt-components/{id}` | Delete |
| POST | `/api/prompt-components/{id}/clone` | Clone |

---

## 🗄️ Database Schema

### prompt_inputs

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| prompt_id | BIGINT FK | Reference to prompts |
| name | VARCHAR(100) | Variable name |
| label | VARCHAR(255) | Display label |
| type | ENUM | text, textarea, select, number, checkbox |
| options | JSON | Options for select type |
| default_value | TEXT | Default value |
| placeholder | TEXT | Placeholder text |
| description | TEXT | Help text |
| is_required | BOOLEAN | Required flag |
| sort_order | INT | Display order |

### prompt_components

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT FK | Owner |
| name | VARCHAR(100) | Component name (unique per user) |
| label | VARCHAR(255) | Display name |
| content | LONGTEXT | Component content |
| description | TEXT | Description |
| is_system | BOOLEAN | System component flag |

### prompts (UPDATED)

| Column | Type | Description |
|--------|------|-------------|
| messages | JSON | Multi-message array |

---

## 🔗 Related Documentation

- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **Testing Guide:** [Prompts Testing](../06-testing/prompts-testing.md)
- **User Journeys:** [Prompt Editor Flow](../07-user-journeys/prompts/prompt-editor-flow.md)
- **Previous Sprint:** [Sprint 24: Prompts Library Core](./sprint-24-prompts-library-core.md)

---

## 📊 Variable Registry Categories

| Category | Examples |
|----------|----------|
| Acts | `{act}`, `{act.fullText}`, `{act.name}`, `{act.summary}` |
| Chapters | `{chapter}`, `{chapter.fullText}`, `{chapter.name}` |
| Scenes | `{scene.title}`, `{scene.fullText}`, `{scene.summary}` |
| Codex | `{codex.characters}`, `{codex.locations}`, `{codex.context}` |
| Context | `{textBefore}`, `{textAfter}`, `{storySoFar}`, `{message}` |
| Composition | `{include(component)}`, `{input(name)}` |
| Logic | `{ifs(condition, then, else)}`, `{isEmpty(value)}` |
| Text | `{wordCount(text)}`, `{firstWords(text, n)}` |
| Other | `{personas}`, `{date.today}` |

---

## ✅ Verification

```
[✓] Migrations ran successfully
[✓] Routes verified with php artisan route:list
[✓] Frontend build successful (yarn run build)
[✓] Lint check passed (yarn run lint)
```

---

*Last Updated: 2026-01-03*
