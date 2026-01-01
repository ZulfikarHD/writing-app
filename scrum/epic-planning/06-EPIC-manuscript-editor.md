# ✍️ EPIC 6: Manuscript Editor (Advanced)

**Epic ID:** EPIC-06  
**Priority:** 🟡 High  
**Total Story Points:** ~55  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** EPIC-01 (AI Connections), EPIC-02 (Codex)

---

## 📋 Epic Description

Enhance the manuscript editor with advanced features including sections, generating prose, text replacement prompts, format menu, subplots tracking, and integrated AI writing tools. This builds upon the foundation editor from Sprint 1-2.

**Reference:** [Novelcrafter Write Documentation](https://www.novelcrafter.com/help/docs/write/the-write-interface)

---

## 🎯 Epic Goals

1. Sections system for organizing scene content
2. AI-powered prose generation
3. Text replacement prompts for transformations
4. Format menu with AI options
5. Subplots tracking integration
6. Scene beats workflow

---

## 📑 Feature Groups

### FG-06.1: Sections System

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-06.1.1 | Sections (Core) | 🔴 Critical | 8 |
| F-06.1.2 | Section Types (Content/Note/Alternative) | 🟡 High | 5 |
| F-06.1.3 | Collapsible Sections | 🟡 High | 3 |
| F-06.1.4 | Section Actions (Move/Delete) | 🟡 High | 3 |

### FG-06.2: AI Writing Features

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-06.2.1 | Generating Prose | 🔴 Critical | 8 |
| F-06.2.2 | Text Replacement Prompts | 🔴 Critical | 5 |
| F-06.2.3 | Slash Commands | 🟡 High | 5 |
| F-06.2.4 | Format Menu AI Options | 🟡 High | 3 |

### FG-06.3: Writing Tools

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-06.3.1 | Scene Beats Workflow | 🟡 High | 5 |
| F-06.3.2 | Subplots in Editor | 🟡 High | 3 |
| F-06.3.3 | Formatting Text | 🟢 Medium | 3 |
| F-06.3.4 | Highlighter/Marker Tool | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-06.1: Sections System
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** organize scene content into sections,  
**So that** I can separate prose from notes and alternatives.

#### Acceptance Criteria:
- [ ] Create sections within a scene
- [ ] Section types: Content (prose), Note (private), Alternative (variations)
- [ ] Sections are ordered
- [ ] Drag sections to reorder
- [ ] Section headers with type indicator
- [ ] Only Content sections exported
- [ ] Notes excluded from AI context (optional)

**Reference:** [Sections](https://www.novelcrafter.com/help/docs/write/sections)

---

### US-06.2: Section Types
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** create different section types,  
**So that** I can separate prose, notes, and alternatives.

#### Acceptance Criteria:
- [ ] **Content**: Main prose, included in export and AI context
- [ ] **Note**: Private notes, excluded from export
- [ ] **Alternative**: Alternate versions, can swap with content
- [ ] Visual distinction per type
- [ ] Convert section between types
- [ ] Section word count per type

---

### US-06.3: Collapsible Sections
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** collapse sections,  
**So that** I can focus on specific content.

#### Acceptance Criteria:
- [ ] Collapse/expand individual sections
- [ ] Collapse all sections button
- [ ] Collapsed state shows section summary
- [ ] Keyboard shortcut for collapse
- [ ] Collapse state persisted per scene

---

### US-06.4: Generating Prose
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** generate prose using AI,  
**So that** I can get help writing or continuing my story.

#### Acceptance Criteria:
- [ ] "Generate" button/command in editor
- [ ] Generation at cursor position
- [ ] Continue writing prompt
- [ ] Generate from beat/summary prompt
- [ ] Streaming output into editor
- [ ] Accept/reject generated content
- [ ] Undo generated content
- [ ] Model selection for generation

**Reference:** [Generating Prose](https://www.novelcrafter.com/help/docs/write/generating-prose)

---

### US-06.5: Text Replacement Prompts
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** transform selected text using AI,  
**So that** I can rephrase, expand, or modify my writing.

#### Acceptance Criteria:
- [ ] Select text to transform
- [ ] Prompt picker for transformation type
- [ ] Built-in: Expand, Rephrase, Shorten, Fix Grammar, etc.
- [ ] Custom text replacement prompts
- [ ] Preview transformation
- [ ] Accept/reject replacement
- [ ] Undo replacement

**Reference:** [Text Replacement Prompts](https://www.novelcrafter.com/help/docs/write/text-replacement-prompts)

---

### US-06.6: Slash Commands
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** use slash commands for quick actions,  
**So that** I can insert AI-generated content quickly.

#### Acceptance Criteria:
- [ ] Type "/" to open command menu
- [ ] Commands: /continue, /expand, /scene-beat, /dialogue, etc.
- [ ] Filter commands by typing
- [ ] Keyboard navigation
- [ ] Custom slash commands from prompts
- [ ] Command execution inline

---

### US-06.7: Format Menu AI Options
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** access AI options from the format menu,  
**So that** I can use AI while selecting text.

#### Acceptance Criteria:
- [ ] Format menu appears on text selection
- [ ] AI section in format menu
- [ ] Quick transformation options
- [ ] Custom prompt option
- [ ] Model indicator

**Reference:** [The Format Menu](https://www.novelcrafter.com/help/docs/write/format-menu)

---

### US-06.8: Scene Beats Workflow
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** write scene beats and expand them,  
**So that** I can plan scenes before writing prose.

#### Acceptance Criteria:
- [ ] Scene beats section type
- [ ] Simple beats (bullet points)
- [ ] Detailed beats (paragraphs)
- [ ] "Expand beat to prose" action
- [ ] AI considers beats when generating
- [ ] Track beat completion

---

### US-06.9: Subplots in Editor
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** see and manage subplots in the editor,  
**So that** I can track which subplots are in each scene.

#### Acceptance Criteria:
- [ ] Subplot indicator in scene metadata
- [ ] Assign scenes to subplots
- [ ] Filter editor sidebar by subplot
- [ ] Subplot color coding
- [ ] Link to subplot Codex entry

**Reference:** [Subplots](https://www.novelcrafter.com/help/docs/write/subplots)

---

### US-06.10: Formatting Text
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** format my text easily,  
**So that** my manuscript looks professional.

#### Acceptance Criteria:
- [ ] Bold, italic, underline, strikethrough
- [ ] Headings (Chapter title, Scene title)
- [ ] Block quotes
- [ ] Alignment options
- [ ] Keyboard shortcuts
- [ ] Format persistence

**Reference:** [Formatting text](https://www.novelcrafter.com/help/docs/write/formatting-text)

---

### US-06.11: Highlighter/Marker Tool
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** highlight text in different colors,  
**So that** I can mark sections for revision.

#### Acceptance Criteria:
- [ ] Multiple highlight colors
- [ ] Highlight persists
- [ ] Remove highlight option
- [ ] Highlight meaning (configurable)
- [ ] Filter/find highlighted text

---

## 🗄️ Database Schema

### Table: `scene_sections`

```sql
CREATE TABLE scene_sections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    scene_id BIGINT UNSIGNED NOT NULL,
    type ENUM('content', 'note', 'alternative', 'beat') NOT NULL DEFAULT 'content',
    title VARCHAR(255) NULL,
    content LONGTEXT NULL,
    is_collapsed BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    INDEX idx_scene_sections (scene_id, sort_order)
);
```

### Table: `scene_subplots` (Pivot)

```sql
CREATE TABLE scene_subplots (
    scene_id BIGINT UNSIGNED NOT NULL,
    codex_entry_id BIGINT UNSIGNED NOT NULL, -- Subplot type Codex entry
    
    PRIMARY KEY (scene_id, codex_entry_id),
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    FOREIGN KEY (codex_entry_id) REFERENCES codex_entries(id) ON DELETE CASCADE
);
```

### Table: `text_highlights`

```sql
CREATE TABLE text_highlights (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    scene_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NULL,
    start_position INT UNSIGNED NOT NULL,
    end_position INT UNSIGNED NOT NULL,
    color VARCHAR(7) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (scene_id) REFERENCES scenes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES scene_sections(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Editor/
│       ├── ProseGenerator.php
│       ├── TextReplacer.php
│       └── SectionManager.php
├── Models/
│   ├── SceneSection.php
│   └── TextHighlight.php
├── Http/
│   └── Controllers/
│       ├── SectionController.php
│       └── ProseGenerationController.php
```

### Frontend Structure

```
resources/js/
├── Components/
│   └── Editor/
│       ├── Section.vue
│       ├── SectionHeader.vue
│       ├── SectionMenu.vue
│       ├── SlashCommands.vue
│       ├── FormatMenu.vue
│       ├── ProseGenerator.vue
│       ├── TextReplacer.vue
│       ├── SceneBeatEditor.vue
│       ├── SubplotSelector.vue
│       └── HighlightTool.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/scenes/{scene}/sections` | List sections |
| POST | `/api/scenes/{scene}/sections` | Create section |
| PATCH | `/api/sections/{section}` | Update section |
| DELETE | `/api/sections/{section}` | Delete section |
| POST | `/api/sections/reorder` | Reorder sections |
| POST | `/api/scenes/{scene}/generate-prose` | Generate prose |
| POST | `/api/text/replace` | Text replacement |
| POST | `/api/scenes/{scene}/subplots` | Assign subplot |

---

## ✅ Definition of Done

- [ ] Sections system with types working
- [ ] Collapsible sections functional
- [ ] Prose generation with streaming
- [ ] Text replacement prompts working
- [ ] Slash commands functional
- [ ] Format menu with AI options
- [ ] Scene beats workflow complete
- [ ] Subplots integration working
- [ ] Mobile-responsive
- [ ] Unit tests for services
- [ ] Feature tests for endpoints

---

## 📎 References

- [Sections](https://www.novelcrafter.com/help/docs/write/sections)
- [Generating Prose](https://www.novelcrafter.com/help/docs/write/generating-prose)
- [Text Replacement Prompts](https://www.novelcrafter.com/help/docs/write/text-replacement-prompts)
- [The Format Menu](https://www.novelcrafter.com/help/docs/write/format-menu)
- [Subplots](https://www.novelcrafter.com/help/docs/write/subplots)
- [Formatting text](https://www.novelcrafter.com/help/docs/write/formatting-text)
