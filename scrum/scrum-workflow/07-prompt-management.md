# 🎯 Epic 6: Prompt and Template Management

**Epic ID:** EPIC-006  
**Prioritas:** 🟡 Sedang  
**Sprint Target:** 5-7  
**Total Story Points:** 58

---

## 📋 Deskripsi Epic

Membangun Prompt Library untuk membuat, menyimpan, dan mengelola custom AI prompts. Prompts dapat memiliki variables, templates, dan settings yang dapat di-reuse. Includes advanced features seperti Personas, Presets, dan Components.

---

## 🎯 Goals

- Reusable prompt templates
- Variable support untuk flexibility
- Easy testing dan preview
- Share prompts antar features
- Persona system untuk consistent AI behavior
- Preset configurations untuk quick access
- Modular components untuk reusability

---

## 📑 User Stories

### US-043: Prompt Library Interface
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** prompt library untuk mengelola prompts,  
**Agar** saya dapat menyimpan dan mengorganisir custom prompts.

#### Acceptance Criteria:
- [ ] List view menampilkan semua prompts
- [ ] Search prompts by name
- [ ] Filter by category
- [ ] Sort by name, date, usage count
- [ ] Create new prompt button
- [ ] Click prompt untuk edit
- [ ] Delete prompt dengan konfirmasi
- [ ] Duplicate prompt option

---

### US-044: Prompt Editor
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** editor untuk membuat dan edit prompts,  
**Agar** saya dapat menyesuaikan AI behavior.

#### Acceptance Criteria:
- [ ] Prompt name (required)
- [ ] Description (optional)
- [ ] Category selection
- [ ] System message field
- [ ] User message template field
- [ ] Variables/placeholders support: {{variable_name}}
- [ ] Variable definition: name, type, default value
- [ ] Preview pane showing resolved prompt
- [ ] Save dan Cancel buttons

#### Technical Notes:
- Handlebars-like syntax untuk variables
- Real-time preview update

---

### US-045: Prompt Variables
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** menambah variables ke prompts,  
**Agar** saya dapat membuat prompts yang dynamic.

#### Acceptance Criteria:
- [ ] Define variables dengan name dan type
- [ ] Variable types: text, number, select, boolean
- [ ] Default values untuk variables
- [ ] Variables muncul sebagai input saat prompt digunakan
- [ ] Validation untuk required variables
- [ ] Example: {{word_count}} type number, default 500
- [ ] Example: {{tone}} type select, options: formal, casual, dramatic

---

### US-046: Prompt Categories
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengkategorikan prompts,  
**Agar** saya dapat menemukan prompts yang tepat dengan cepat.

#### Acceptance Criteria:
- [ ] Predefined categories: Generation, Transformation, Analysis, Brainstorming
- [ ] Custom categories dapat dibuat
- [ ] Assign category saat create/edit prompt
- [ ] Filter library by category
- [ ] Category badge di prompt list
- [ ] Category management (add, rename, delete)

---

### US-047: Built-in Prompts
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** built-in prompts yang siap pakai,  
**Agar** saya dapat langsung menggunakan tanpa setup.

#### Acceptance Criteria:
- [ ] Pre-installed prompts: Expand, Rephrase, Shorten
- [ ] Pre-installed prompts: Change POV, Change Tense
- [ ] Pre-installed prompts: Summarize Scene, Detect Characters
- [ ] Pre-installed prompts: Continue Writing, Scene Beat
- [ ] Built-in prompts tidak dapat dihapus (only hide)
- [ ] Can duplicate built-in untuk customization
- [ ] Badge indicating "Built-in" vs "Custom"

---

### US-048: Prompt Preview & Test
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** preview dan test prompt sebelum use,  
**Agar** saya dapat memastikan prompt bekerja dengan benar.

#### Acceptance Criteria:
- [ ] Preview tab menampilkan resolved prompt
- [ ] Fill in sample values untuk variables
- [ ] Test button untuk send ke AI
- [ ] Response displayed dalam preview
- [ ] Copy resolved prompt ke clipboard
- [ ] Show token count
- [ ] Adjust model parameters (temperature, etc.) untuk test

---

### US-049: Model Parameters per Prompt
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengatur model parameters per prompt,  
**Agar** saya dapat fine-tune AI behavior untuk setiap use case.

#### Acceptance Criteria:
- [ ] Temperature slider (0-2)
- [ ] Top-p slider (0-1)
- [ ] Max tokens input
- [ ] Presence penalty (-2 to 2)
- [ ] Frequency penalty (-2 to 2)
- [ ] Option: use default settings atau custom
- [ ] Parameters saved dengan prompt
- [ ] Quick presets: Creative, Balanced, Precise

---

### US-093: Prompt Personas
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** membuat Prompt Personas sebagai "memory" untuk AI,  
**Agar** AI konsisten mengingat preferensi saya di semua prompts dan projects.

#### Acceptance Criteria:
- [ ] Create Persona dari Prompt Library (+ New → Persona)
- [ ] Persona berisi informasi yang AI harus ingat
- [ ] Contoh: pen name preferences, style guides, writing rules
- [ ] Apply persona ke specific prompt types:
  - Scene beat completion
  - Text replacement
  - Chat
- [ ] Apply persona ke specific projects atau all projects
- [ ] Multiple personas dapat aktif
- [ ] Edit/delete personas
- [ ] Persona content preview sebelum send ke AI

#### Source:
> [Prompt Personas - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/prompt-personas/prompt-personas)

---

### US-094: Prompt Presets
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** menyimpan Prompt Presets dengan settings dan model tertentu,  
**Agar** saya dapat reuse konfigurasi prompt favorit dengan cepat.

#### Acceptance Criteria:
- [ ] Create preset dari prompt detail (General tab → + New Preset)
- [ ] Preset berisi: name, selected AI model, custom instructions
- [ ] Quick access ke preset dari prompt dropdown
- [ ] Multiple presets per prompt
- [ ] Edit/delete preset
- [ ] Set default preset untuk prompt
- [ ] Preset tidak mengubah original prompt structure

#### Source:
> [Prompt Presets - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/prompt-presets/prompt-presets)

---

### US-095: Prompt Components
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** membuat reusable Prompt Components,  
**Agar** saya dapat menjaga konsistensi instructions di berbagai prompts.

#### Acceptance Criteria:
- [ ] Create component dari Prompt Library
- [ ] Component berisi content/instructions yang reusable
- [ ] Include component dalam prompt dengan reference syntax
- [ ] Update component → auto-update di semua prompts yang menggunakan
- [ ] List prompts yang menggunakan component
- [ ] Component categories untuk organization
- [ ] Import/export components

#### Source:
> [Prompt Components - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/prompts/prompt-library)

---

### US-103: Sharing Prompts
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** share prompts dengan orang lain,  
**Agar** saya dapat berbagi template yang berguna.

#### Acceptance Criteria:
- [ ] Copy prompt ke clipboard (shareable format)
- [ ] Import prompt dari clipboard
- [ ] Export prompt sebagai file
- [ ] Import prompt dari file
- [ ] Prompt marketplace/gallery (future)

#### Source:
> [Sharing Prompts - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/prompts/sharing-prompts)

---

### US-104: Prompt Submenus
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengorganisir prompts ke dalam submenus,  
**Agar** prompt menu tidak terlalu panjang dan mudah dinavigasi.

#### Acceptance Criteria:
- [ ] Create submenu/folder untuk prompts
- [ ] Drag prompts ke submenu
- [ ] Nested submenus support
- [ ] Submenu icons
- [ ] Collapse/expand submenus

#### Source:
> [Organize Prompts into Submenus - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/prompts/organize-your-prompts-into-submenus)

---

## 📊 Sprint Breakdown

### Sprint 5 (58 total → 13 points)
- US-043: Prompt Library Interface (5 pts)
- US-047: Built-in Prompts (5 pts)
- US-046: Prompt Categories (3 pts)

### Sprint 6 (45 remaining → 21 points)
- US-044: Prompt Editor (8 pts)
- US-045: Prompt Variables (5 pts)
- US-093: Prompt Personas (8 pts)

### Sprint 7 (24 remaining → 18 points)
- US-048: Prompt Preview & Test (5 pts)
- US-049: Model Parameters per Prompt (3 pts)
- US-094: Prompt Presets (5 pts)
- US-095: Prompt Components (5 pts)

### Sprint 9 (6 remaining → 6 points)
- US-103: Sharing Prompts (3 pts)
- US-104: Prompt Submenus (3 pts)

---

## 🔗 Dependencies

- Epic 7 (AI Connections) untuk testing prompts
- Epic 1 (Manuscript Editor) menggunakan prompts untuk generation
- Epic 5 (AI Chat) menggunakan prompts

---

## 📝 Notes

- Personas adalah game-changer untuk consistent AI output
- Presets allow quick switching between configurations
- Components enable DRY principle for prompts
- Consider import/export prompts untuk sharing
- Version control untuk prompts (optional future feature)
- Analytics: track which prompts most used
