# ✨ EPIC 5: Prompts System

**Epic ID:** EPIC-05  
**Priority:** 🟡 High  
**Total Story Points:** ~80  
**Est. Duration:** 3-4 Sprints  
**Dependencies:** EPIC-01 (AI Connections), EPIC-04 (Workshop Chat)

---

## 📋 Epic Description

Build a comprehensive prompt management system that enables writers to create, customize, and use AI prompts for various writing tasks. This includes prompt library, personas, presets, components, inputs, and advanced prompt configuration.

**Reference:** [Novelcrafter Prompts Documentation](https://www.novelcrafter.com/help/docs/prompts/prompt-library)

---

## 🎯 Epic Goals

1. Prompt library with built-in and custom prompts
2. Types of prompts for different use cases
3. Prompt editor with variables and components
4. Prompt personas for AI personality
5. Prompt presets for quick configuration
6. Prompt inputs for dynamic user input
7. Model parameter tuning per prompt
8. Prompt sharing and organization

---

## 📑 Feature Groups

### FG-05.1: Prompt Library Core

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-05.1.1 | Prompt Library Interface | 🔴 Critical | 5 |
| F-05.1.2 | Built-in/Default Prompts | 🔴 Critical | 5 |
| F-05.1.3 | Create a Prompt | 🔴 Critical | 8 |
| F-05.1.4 | Types of Prompts | 🔴 Critical | 5 |
| F-05.1.5 | Prompt Categories | 🟡 High | 3 |

### FG-05.2: Prompt Editor

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-05.2.1 | Prompt Editor Interface | 🔴 Critical | 8 |
| F-05.2.2 | Prompt Variables | 🔴 Critical | 5 |
| F-05.2.3 | Prompt Detail Tabs | 🟡 High | 3 |
| F-05.2.4 | Prompt Descriptions | 🟡 High | 2 |
| F-05.2.5 | Previewing Prompts | 🟡 High | 5 |

### FG-05.3: Personas & Presets

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-05.3.1 | Prompt Personas | 🔴 Critical | 8 |
| F-05.3.2 | Prompt Presets | 🔴 Critical | 5 |
| F-05.3.3 | Editing a Prompt Preset | 🟡 High | 3 |
| F-05.3.4 | Prompt Presets in Action | 🟡 High | 3 |
| F-05.3.5 | Persona or Preset? | 🟢 Medium | 2 |

### FG-05.4: Advanced Features

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-05.4.1 | Prompt Inputs | 🟡 High | 5 |
| F-05.4.2 | How to make a Prompt Input | 🟡 High | 3 |
| F-05.4.3 | Prompt Input Defaults | 🟢 Medium | 2 |
| F-05.4.4 | Prompt Components | 🟡 High | 5 |
| F-05.4.5 | Creating a Prompt Component | 🟡 High | 3 |
| F-05.4.6 | Adding a Prompt Component | 🟢 Medium | 2 |

### FG-05.5: Model & Sharing

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-05.5.1 | Tuning Model Settings | 🟡 High | 5 |
| F-05.5.2 | Clone a Prompt | 🟡 High | 2 |
| F-05.5.3 | Sharing Prompts | 🟢 Medium | 5 |
| F-05.5.4 | Organize Prompts into Submenus | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-05.1: Prompt Library Interface
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** browse and manage my prompts in a library,  
**So that** I can easily find and use prompts for writing.

#### Acceptance Criteria:
- [ ] Prompt library accessible from main navigation
- [ ] List/grid view of prompts
- [ ] Filter by type, category
- [ ] Search prompts
- [ ] Prompt cards with name, type, description preview
- [ ] Quick actions (run, edit, clone, delete)
- [ ] Sort by name, date, usage

**Reference:** [Prompt Library](https://www.novelcrafter.com/help/docs/prompts/prompt-library)

---

### US-05.2: Built-in/Default Prompts
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** have built-in prompts available,  
**So that** I can start using AI assistance immediately.

#### Acceptance Criteria:
- [ ] Default prompts for: Expand, Rephrase, Summarize, Continue, etc.
- [ ] Default prompts marked distinctly
- [ ] Cannot delete default prompts
- [ ] Can clone and customize default prompts
- [ ] Default prompts updated with app updates

**Reference:** [Default Prompts](https://www.novelcrafter.com/help/docs/prompts/default-prompts)

---

### US-05.3: Create a Prompt
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** create custom prompts,  
**So that** I can tailor AI assistance to my needs.

#### Acceptance Criteria:
- [ ] Create new prompt button
- [ ] Name and description fields
- [ ] Type selection
- [ ] System message field
- [ ] User message field
- [ ] Variable insertion
- [ ] Save and test prompt
- [ ] Validation of prompt structure

**Reference:** [Create a Prompt](https://www.novelcrafter.com/help/docs/prompts/prompt-creation)

---

### US-05.4: Types of Prompts
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** create prompts of different types,  
**So that** I can use them in appropriate contexts.

#### Acceptance Criteria:
- [ ] Chat prompts (for Workshop)
- [ ] Prose prompts (for editor - generate text)
- [ ] Text replacement prompts (transform selected text)
- [ ] Summarization prompts
- [ ] Type determines where prompt appears
- [ ] Type-specific settings

**Reference:** [Types of Prompts](https://www.novelcrafter.com/help/docs/prompts/prompt-types)

---

### US-05.5: Prompt Editor Interface
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** a full-featured prompt editor,  
**So that** I can create complex prompts.

#### Acceptance Criteria:
- [ ] Multi-tab interface
- [ ] System message editor (rich text)
- [ ] User message editor with variables
- [ ] Variable picker/inserter
- [ ] Component inserter
- [ ] Preview tab
- [ ] Settings tab (model, temperature)
- [ ] Save draft
- [ ] Syntax highlighting for variables

---

### US-05.6: Prompt Variables
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** use variables in prompts,  
**So that** prompts include dynamic context.

#### Acceptance Criteria:
- [ ] Variable syntax: `{{variable_name}}`
- [ ] Built-in variables: `{{scene}}`, `{{selection}}`, `{{codex}}`, `{{summary}}`
- [ ] Variable autocomplete
- [ ] Variable preview shows resolved value
- [ ] Custom input variables (see Prompt Inputs)
- [ ] Variable documentation/tooltips

---

### US-05.7: Prompt Detail Tabs
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** organize prompt settings in tabs,  
**So that** I can easily navigate prompt configuration.

#### Acceptance Criteria:
- [ ] Content tab (messages)
- [ ] Settings tab (model, parameters)
- [ ] Preview tab
- [ ] Test tab
- [ ] Tab state persisted

**Reference:** [Prompt Detail Tabs](https://www.novelcrafter.com/help/docs/prompts/prompt-detail-tabs)

---

### US-05.8: Previewing Prompts
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** preview prompts before running,  
**So that** I can verify the final prompt text.

#### Acceptance Criteria:
- [ ] Preview button in prompt editor
- [ ] Shows resolved variables
- [ ] Token count displayed
- [ ] Copy preview to clipboard
- [ ] Test with actual AI call

**Reference:** [Previewing Prompts](https://www.novelcrafter.com/help/docs/prompts/prompt-preview)

---

### US-05.9: Prompt Personas
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** create AI personas,  
**So that** AI responses have consistent personality and style.

#### Acceptance Criteria:
- [ ] Create persona with name and description
- [ ] Persona system message
- [ ] Persona applied across prompts
- [ ] Select persona per prompt or globally
- [ ] Multiple personas per user
- [ ] Persona shared across projects (optional)
- [ ] Built-in persona templates

**Reference:** [Prompt Personas](https://www.novelcrafter.com/help/docs/prompt-personas/prompt-personas)

---

### US-05.10: Prompt Presets
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** create prompt presets,  
**So that** I can quickly configure common prompt settings.

#### Acceptance Criteria:
- [ ] Create preset with name
- [ ] Preset includes: model, temperature, max tokens, etc.
- [ ] Apply preset to any prompt
- [ ] Override preset settings per prompt
- [ ] Default preset setting
- [ ] Preset list and management

**Reference:** [Prompt Presets](https://www.novelcrafter.com/help/docs/prompt-presets/prompt-presets)

---

### US-05.11: Prompt Inputs
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** create prompts that ask for input,  
**So that** I can provide specific guidance before running.

#### Acceptance Criteria:
- [ ] Define input fields in prompt
- [ ] Input types: text, textarea, select, number
- [ ] Input appears before prompt runs
- [ ] Input value inserted as variable
- [ ] Input defaults
- [ ] Required vs optional inputs

**Reference:** [Prompt Inputs](https://www.novelcrafter.com/help/docs/prompt-inputs/prompt-inputs)

---

### US-05.12: Prompt Components
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** create reusable prompt components,  
**So that** I can include common instructions across prompts.

#### Acceptance Criteria:
- [ ] Create named component
- [ ] Component content (text/instructions)
- [ ] Insert component in prompt
- [ ] Component syntax: `[[component_name]]`
- [ ] Component resolves at runtime
- [ ] Edit component updates all usages

**Reference:** [Prompt Components](https://www.novelcrafter.com/help/docs/prompt-components/prompt-components)

---

### US-05.13: Tuning Model Settings
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** tune model settings per prompt,  
**So that** I can optimize AI output quality.

#### Acceptance Criteria:
- [ ] Temperature slider (0-2)
- [ ] Max tokens setting
- [ ] Top P setting
- [ ] Frequency penalty
- [ ] Presence penalty
- [ ] Stop sequences
- [ ] Settings explained with tooltips

**Reference:** [Tuning Model Settings](https://www.novelcrafter.com/help/docs/prompts/tuning-model-settings)

---

### US-05.14: Clone a Prompt
**Priority:** 🟡 High | **Points:** 2

**As a** writer,  
**I want to** clone existing prompts,  
**So that** I can create variations easily.

#### Acceptance Criteria:
- [ ] Clone button on any prompt
- [ ] Cloned prompt is editable copy
- [ ] Name automatically suffixed "(Copy)"
- [ ] Can clone default prompts

**Reference:** [Clone a Prompt](https://www.novelcrafter.com/help/docs/prompts/clone-prompt)

---

### US-05.15: Sharing Prompts
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer,  
**I want to** share prompts with others,  
**So that** I can collaborate on prompt development.

#### Acceptance Criteria:
- [ ] Export prompt as JSON/file
- [ ] Import prompt from file
- [ ] Share via link (optional)
- [ ] Community prompt library (future)
- [ ] Import retains structure

**Reference:** [Sharing Prompts](https://www.novelcrafter.com/help/docs/prompts/sharing-prompts)

---

### US-05.16: Organize Prompts into Submenus
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** organize prompts into folders/submenus,  
**So that** I can manage many prompts efficiently.

#### Acceptance Criteria:
- [ ] Create prompt folders
- [ ] Drag prompts to folders
- [ ] Nested folders
- [ ] Folder in prompt picker appears as submenu
- [ ] Folder colors/icons

**Reference:** [Organize Your Prompts into Submenus](https://www.novelcrafter.com/help/docs/prompts/organize-your-prompts-into-submenus)

---

## 🗄️ Database Schema

### Table: `prompts`

```sql
CREATE TABLE prompts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL, -- NULL for system prompts
    folder_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type ENUM('chat', 'prose', 'replacement', 'summary', 'custom') NOT NULL,
    system_message LONGTEXT NULL,
    user_message LONGTEXT NULL,
    persona_id BIGINT UNSIGNED NULL,
    preset_id BIGINT UNSIGNED NULL,
    model_settings JSON NULL, -- Override preset settings
    is_system BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES prompt_folders(id) ON DELETE SET NULL,
    INDEX idx_user_type (user_id, type)
);
```

### Table: `prompt_personas`

```sql
CREATE TABLE prompt_personas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    system_message LONGTEXT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table: `prompt_presets`

```sql
CREATE TABLE prompt_presets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    model VARCHAR(255) NULL,
    temperature DECIMAL(3,2) DEFAULT 0.7,
    max_tokens INT UNSIGNED NULL,
    top_p DECIMAL(3,2) NULL,
    frequency_penalty DECIMAL(3,2) NULL,
    presence_penalty DECIMAL(3,2) NULL,
    stop_sequences JSON NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table: `prompt_inputs`

```sql
CREATE TABLE prompt_inputs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    prompt_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    label VARCHAR(255) NOT NULL,
    type ENUM('text', 'textarea', 'select', 'number', 'checkbox') NOT NULL,
    options JSON NULL, -- For select type
    default_value TEXT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE
);
```

### Table: `prompt_components`

```sql
CREATE TABLE prompt_components (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL, -- Used in [[name]] syntax
    label VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_user_name (user_id, name)
);
```

### Table: `prompt_folders`

```sql
CREATE TABLE prompt_folders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES prompt_folders(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Prompts/
│       ├── PromptService.php
│       ├── PromptResolver.php
│       ├── VariableResolver.php
│       └── ComponentResolver.php
├── Models/
│   ├── Prompt.php
│   ├── PromptPersona.php
│   ├── PromptPreset.php
│   ├── PromptInput.php
│   ├── PromptComponent.php
│   └── PromptFolder.php
├── Http/
│   └── Controllers/
│       ├── PromptController.php
│       ├── PromptPersonaController.php
│       ├── PromptPresetController.php
│       └── PromptComponentController.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Prompts/
│       ├── Index.vue
│       ├── Create.vue
│       └── Edit.vue
├── Components/
│   └── Prompts/
│       ├── PromptLibrary.vue
│       ├── PromptCard.vue
│       ├── PromptEditor.vue
│       ├── PromptPreview.vue
│       ├── VariablePicker.vue
│       ├── ComponentPicker.vue
│       ├── PersonaSelector.vue
│       ├── PresetSelector.vue
│       ├── ModelSettings.vue
│       ├── PromptInputForm.vue
│       └── FolderTree.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/prompts` | List user prompts |
| POST | `/api/prompts` | Create prompt |
| GET | `/api/prompts/{prompt}` | Get prompt |
| PATCH | `/api/prompts/{prompt}` | Update prompt |
| DELETE | `/api/prompts/{prompt}` | Delete prompt |
| POST | `/api/prompts/{prompt}/clone` | Clone prompt |
| POST | `/api/prompts/{prompt}/run` | Execute prompt |
| GET | `/api/prompts/{prompt}/preview` | Preview resolved prompt |
| GET | `/api/prompt-personas` | List personas |
| POST | `/api/prompt-personas` | Create persona |
| GET | `/api/prompt-presets` | List presets |
| POST | `/api/prompt-presets` | Create preset |
| GET | `/api/prompt-components` | List components |
| POST | `/api/prompt-components` | Create component |
| GET | `/api/prompt-folders` | List folders |
| POST | `/api/prompt-folders` | Create folder |

---

## ✅ Definition of Done

- [ ] Prompt library with CRUD operations
- [ ] Built-in prompts available
- [ ] Prompt editor with variables and components
- [ ] Personas system working
- [ ] Presets system working
- [ ] Prompt inputs functional
- [ ] Model settings tunable
- [ ] Preview shows resolved prompt
- [ ] All prompt types working in appropriate contexts
- [ ] Mobile-responsive
- [ ] Unit tests (80%+ coverage)
- [ ] Feature tests for all endpoints

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Variable resolution complexity | Medium | Thorough testing, clear syntax |
| Prompt versioning conflicts | Medium | Version tracking, conflict resolution UI |
| Component circular references | Low | Validation during save |
| Large prompt libraries | Medium | Pagination, search optimization |

---

## 📎 References

- [Prompt Library](https://www.novelcrafter.com/help/docs/prompts/prompt-library)
- [Types of Prompts](https://www.novelcrafter.com/help/docs/prompts/prompt-types)
- [Create a Prompt](https://www.novelcrafter.com/help/docs/prompts/prompt-creation)
- [Prompt Personas](https://www.novelcrafter.com/help/docs/prompt-personas/prompt-personas)
- [Prompt Presets](https://www.novelcrafter.com/help/docs/prompt-presets/prompt-presets)
- [Prompt Components](https://www.novelcrafter.com/help/docs/prompt-components/prompt-components)
- [Prompt Inputs](https://www.novelcrafter.com/help/docs/prompt-inputs/prompt-inputs)
