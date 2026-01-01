# 🚀 Sprint 5-7: Core Features

**Phase:** 2 - Core Features  
**Duration:** 6 weeks (3 sprints)  
**Total Story Points:** ~180  
**Focus:** Story Planning, Workshop Chat Complete, Prompts System, Advanced Editor, Snippets

---

## 📋 Phase Overview

This phase builds out the core writing and planning features, including the Plan interface with multiple views, complete prompt system, and advanced editor features. After this phase, the app is feature-complete for beta testing.

---

## 🗓️ Sprint 5: Story Planning Views + Workshop Complete
**Week 9-10** | **Story Points: ~60**

### Sprint Goals
1. ✅ Build Plan interface with Grid/Matrix/Outline views
2. ✅ Complete Workshop Chat features
3. ✅ Implement context injection fully
4. ✅ Start mentions tracking

### Sprint Backlog

#### Week 9 (Days 1-5)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 1 | Plan Interface layout | EPIC-03 | 5 | ⬜ |
| 1-2 | View Switcher component | EPIC-03 | 3 | ⬜ |
| 2-3 | Grid View implementation | EPIC-03 | 8 | ⬜ |
| 3-4 | Scene Card component | EPIC-03 | 5 | ⬜ |
| 4 | Drag & drop reordering | EPIC-03 | 5 | ⬜ |
| 5 | Scene labels system | EPIC-03 | 5 | ⬜ |

#### Week 10 (Days 6-10)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 6-7 | Matrix View implementation | EPIC-03 | 8 | ⬜ |
| 7 | Outline View implementation | EPIC-03 | 5 | ⬜ |
| 8 | Plan search & filter | EPIC-03 | 5 | ⬜ |
| 8-9 | Full context injection (scene, Codex) | EPIC-04 | 5 | ⬜ |
| 9 | Transfer from chat to scene | EPIC-04 | 5 | ⬜ |
| 10 | Pin chat panel | EPIC-04 | 3 | ⬜ |
| 10 | Tests | - | 3 | ⬜ |

### Deliverables
- [ ] Plan interface with header and navigation
- [ ] Grid View with scene cards
- [ ] Matrix View (scenes × Codex entries)
- [ ] Outline View (linear list)
- [ ] Drag & drop scene reordering
- [ ] Scene labels/status system
- [ ] Plan search and filter
- [ ] Full context injection in chat
- [ ] Transfer chat responses to scenes
- [ ] Pinnable chat panel

### Definition of Done
- [ ] All three Plan views functional
- [ ] Matrix correctly shows Codex mentions
- [ ] Drag & drop works smoothly
- [ ] Labels filter scenes correctly
- [ ] Chat context includes selected items
- [ ] Transfer to scene inserts content

---

## 🗓️ Sprint 6: Prompts System Foundation
**Week 11-12** | **Story Points: ~65**

### Sprint Goals
1. ✅ Build complete Prompts System
2. ✅ Implement Personas and Presets
3. ✅ Create prompt variables and components
4. ✅ Start advanced editor features

### Sprint Backlog

#### Week 11 (Days 1-5)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 1 | Prompt library interface | EPIC-05 | 5 | ⬜ |
| 1-2 | Prompt CRUD & model | EPIC-05 | 5 | ⬜ |
| 2 | Built-in/default prompts | EPIC-05 | 5 | ⬜ |
| 2-3 | Prompt types implementation | EPIC-05 | 5 | ⬜ |
| 3-4 | Prompt editor interface | EPIC-05 | 8 | ⬜ |
| 4-5 | Prompt variables system | EPIC-05 | 5 | ⬜ |
| 5 | Prompt preview | EPIC-05 | 5 | ⬜ |

#### Week 12 (Days 6-10)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 6 | Prompt Personas | EPIC-05 | 8 | ⬜ |
| 6-7 | Prompt Presets | EPIC-05 | 5 | ⬜ |
| 7-8 | Prompt Inputs | EPIC-05 | 5 | ⬜ |
| 8 | Prompt Components | EPIC-05 | 5 | ⬜ |
| 8-9 | Model settings tuning | EPIC-05 | 5 | ⬜ |
| 9 | Clone prompt feature | EPIC-05 | 2 | ⬜ |
| 9-10 | Prompt categories/folders | EPIC-05 | 3 | ⬜ |
| 10 | Tests | - | 4 | ⬜ |

### Deliverables
- [ ] Prompt library with search and filter
- [ ] Built-in prompts (Expand, Rephrase, Summarize, etc.)
- [ ] Custom prompt creation
- [ ] Prompt types (chat, prose, replacement, summary)
- [ ] Full prompt editor with tabs
- [ ] Variable system (`{{scene}}`, `{{selection}}`, etc.)
- [ ] Prompt Personas
- [ ] Prompt Presets
- [ ] Prompt Inputs (user input before run)
- [ ] Prompt Components (reusable blocks)
- [ ] Model parameter tuning
- [ ] Prompt organization (folders/categories)

### Definition of Done
- [ ] Prompts executable from chat and editor
- [ ] Variables resolve correctly
- [ ] Personas apply to prompts
- [ ] Presets configure model settings
- [ ] Inputs show form before execution
- [ ] Components insert into prompts

---

## 🗓️ Sprint 7: Advanced Editor + Snippets
**Week 13-14** | **Story Points: ~55**

### Sprint Goals
1. ✅ Implement Sections in editor
2. ✅ Build prose generation and text replacement
3. ✅ Create Snippets system
4. ✅ Add slash commands

### Sprint Backlog

#### Week 13 (Days 1-5)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 1-2 | Sections system | EPIC-06 | 8 | ⬜ |
| 2 | Section types (content/note/alternative) | EPIC-06 | 5 | ⬜ |
| 3 | Collapsible sections | EPIC-06 | 3 | ⬜ |
| 3-4 | Prose generation | EPIC-06 | 8 | ⬜ |
| 4-5 | Text replacement prompts | EPIC-06 | 5 | ⬜ |
| 5 | Slash commands | EPIC-06 | 5 | ⬜ |

#### Week 14 (Days 6-10)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 6 | Format menu AI options | EPIC-06 | 3 | ⬜ |
| 6-7 | Snippet CRUD | EPIC-07 | 5 | ⬜ |
| 7 | Snippet categories/tags | EPIC-07 | 3 | ⬜ |
| 8 | Pin snippets | EPIC-07 | 2 | ⬜ |
| 8 | Save from chat to snippet | EPIC-07 | 3 | ⬜ |
| 9 | Insert snippet to editor | EPIC-07 | 3 | ⬜ |
| 9 | Extract snippet to Codex/scene | EPIC-07 | 3 | ⬜ |
| 10 | Mentions tracking | EPIC-02 | 5 | ⬜ |
| 10 | Tests | - | 4 | ⬜ |

### Deliverables
- [ ] Sections within scenes
- [ ] Section types with different purposes
- [ ] Collapsible sections
- [ ] Prose generation with streaming
- [ ] Text replacement prompts (selection-based)
- [ ] Slash commands in editor
- [ ] Format menu with AI options
- [ ] Snippets CRUD
- [ ] Snippet tags and organization
- [ ] Pin/quick access snippets
- [ ] Save chat to snippet
- [ ] Insert snippet to editor
- [ ] Mentions tracking (Codex in scenes)

### Definition of Done
- [ ] Sections working with all types
- [ ] Prose generation inserts at cursor
- [ ] Text replacement transforms selection
- [ ] Slash commands show command menu
- [ ] Snippets saveable and insertable
- [ ] Mentions tracked and displayable

---

## 📊 Sprint Velocity Tracking

| Sprint | Planned | Completed | Variance |
|--------|---------|-----------|----------|
| Sprint 5 | 60 | - | - |
| Sprint 6 | 65 | - | - |
| Sprint 7 | 55 | - | - |

---

## 🎯 Phase Exit Criteria (Beta Ready)

After Sprint 7, the following must be complete:

### Core Writing
- [ ] Rich text editor with sections
- [ ] Prose generation from AI
- [ ] Text replacement prompts
- [ ] Slash commands

### Planning
- [ ] Grid, Matrix, Outline views
- [ ] Drag & drop reordering
- [ ] Scene labels and status

### AI Features
- [ ] Multiple AI providers connected
- [ ] Complete prompt system
- [ ] Workshop chat with context
- [ ] Personas and presets

### Organization
- [ ] Codex with all features
- [ ] Snippets system
- [ ] Mentions tracking

---

## ⚠️ Risks & Dependencies

### Critical Dependencies
- **Prompts System** depends on AI Connections (Sprint 3)
- **Matrix View** depends on Codex (Sprint 3-4)
- **Prose Generation** depends on AI services
- **Text Replacement** depends on Prompts

### Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Matrix performance with many entries | Medium | Medium | Virtual scrolling |
| Prompt variable complexity | Medium | Medium | Thorough testing |
| Editor stability with sections | Medium | High | TipTap extensions testing |
| Streaming in editor | Medium | Medium | Non-streaming fallback |

---

## 📝 Notes for Implementation

### Section Implementation in TipTap
- Use custom TipTap node for sections
- Section types stored as node attributes
- Collapsible using Vue component

### Matrix View Performance
- Consider virtual scrolling for rows
- Lazy load Codex data
- Cache mention counts

### Prompt Variable Resolution Order
1. Built-in variables (`{{scene}}`, `{{selection}}`)
2. Codex variables (detected mentions)
3. User input variables
4. Component expansion

---

## 🔗 Related Documents

- [EPIC-03: Story Planning](../03-EPIC-story-planning.md)
- [EPIC-04: Workshop Chat](../04-EPIC-workshop-chat.md)
- [EPIC-05: Prompts System](../05-EPIC-prompts-system.md)
- [EPIC-06: Manuscript Editor](../06-EPIC-manuscript-editor.md)
- [EPIC-07: Snippets](../07-EPIC-snippets.md)
