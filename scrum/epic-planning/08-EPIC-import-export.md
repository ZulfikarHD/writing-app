# 📦 EPIC 8: Import/Export

**Epic ID:** EPIC-08  
**Priority:** 🟢 Medium  
**Total Story Points:** ~50  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** EPIC-02 (Codex), EPIC-03 (Story Planning)

---

## 📋 Epic Description

Build comprehensive import and export functionality that allows writers to bring in existing manuscripts and export their work to various formats including Word, Markdown, Scrivener, and Atticus.

**Reference:** [Novelcrafter Import](https://www.novelcrafter.com/help/docs/import/word) & [Export](https://www.novelcrafter.com/help/docs/export/novel)

---

## 🎯 Epic Goals

1. Import from Word (.docx) with structure parsing
2. Import from Markdown
3. Export to Word with formatting
4. Export to Markdown
5. Export to Scrivener format
6. Export to Atticus format
7. Export metadata and supplements

---

## 📑 Feature Groups

### FG-08.1: Import

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-08.1.1 | Import from Word (.docx) | 🔴 Critical | 8 |
| F-08.1.2 | Import from Markdown | 🟡 High | 5 |
| F-08.1.3 | Import Structure Detection | 🟡 High | 5 |
| F-08.1.4 | Import Preview & Mapping | 🟡 High | 5 |
| F-08.1.5 | Import HTML (Optional) | 🟢 Low | 3 |

### FG-08.2: Export

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-08.2.1 | Export Novel to Word | 🔴 Critical | 5 |
| F-08.2.2 | Export to Markdown | 🟡 High | 3 |
| F-08.2.3 | Export to Scrivener | 🟡 High | 5 |
| F-08.2.4 | Export to Atticus | 🟡 High | 3 |
| F-08.2.5 | Export Options & Settings | 🟡 High | 3 |
| F-08.2.6 | Export Metadata & Supplements | 🟢 Medium | 5 |

---

## 📝 Detailed User Stories

### US-08.1: Import from Word (.docx)
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer with existing manuscripts,  
**I want to** import Word documents,  
**So that** I can continue writing in the app.

#### Acceptance Criteria:
- [ ] Upload .docx file
- [ ] Parse document content
- [ ] Preserve basic formatting (bold, italic, etc.)
- [ ] Detect chapter breaks (headings, page breaks)
- [ ] Preview imported structure
- [ ] Map headings to chapters/scenes
- [ ] Import images (optional)
- [ ] Handle large documents

**Reference:** [Word (.docx) Import](https://www.novelcrafter.com/help/docs/import/word)

---

### US-08.2: Import from Markdown
**Priority:** 🟡 High | **Points:** 5

**As a** writer using Markdown,  
**I want to** import Markdown files,  
**So that** I can bring in my Markdown manuscripts.

#### Acceptance Criteria:
- [ ] Upload .md file or paste text
- [ ] Parse Markdown syntax
- [ ] Headings map to chapters/scenes
- [ ] Preserve formatting
- [ ] Handle multiple files (folder import)
- [ ] Front matter parsing (YAML)

**Reference:** [Markdown Import](https://www.novelcrafter.com/help/docs/import/markdown)

---

### US-08.3: Import Structure Detection
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** auto-detect document structure,  
**So that** the app correctly separates chapters and scenes.

#### Acceptance Criteria:
- [ ] Detect heading-based structure
- [ ] Detect separator-based structure (---, ***)
- [ ] Detect numbered chapters
- [ ] Detect page breaks as chapter separators
- [ ] Manual adjustment in preview
- [ ] Structure templates (single scene, chapter per heading, etc.)

---

### US-08.4: Import Preview & Mapping
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** preview and adjust import before confirming,  
**So that** the structure is correct.

#### Acceptance Criteria:
- [ ] Preview parsed structure (tree view)
- [ ] Edit chapter/scene titles
- [ ] Merge/split detected sections
- [ ] Change section type (chapter vs scene)
- [ ] Discard specific sections
- [ ] Summary of import (counts)

---

### US-08.5: Export Novel to Word
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** export my novel to Word format,  
**So that** I can share or submit my manuscript.

#### Acceptance Criteria:
- [ ] Export entire novel to .docx
- [ ] Include chapter headings
- [ ] Preserve formatting
- [ ] Page breaks between chapters
- [ ] Front matter option (title page)
- [ ] Export specific chapters/scenes
- [ ] Export options (font, margins, etc.)

**Reference:** [Export Novel](https://www.novelcrafter.com/help/docs/export/novel)

---

### US-08.6: Export to Markdown
**Priority:** 🟡 High | **Points:** 3

**As a** writer using Markdown-based tools,  
**I want to** export to Markdown,  
**So that** I can use my manuscript elsewhere.

#### Acceptance Criteria:
- [ ] Export to single .md file
- [ ] Export to multiple files (per chapter/scene)
- [ ] Include/exclude metadata as front matter
- [ ] Preserve formatting as Markdown syntax
- [ ] Export as ZIP for multiple files

---

### US-08.7: Export to Scrivener
**Priority:** 🟡 High | **Points:** 5

**As a** writer using Scrivener,  
**I want to** export in Scrivener-compatible format,  
**So that** I can continue in Scrivener.

#### Acceptance Criteria:
- [ ] Export structure as Scrivener-importable format
- [ ] Preserve chapter/scene hierarchy
- [ ] Include scene metadata
- [ ] Include Codex entries as research items
- [ ] Export instructions provided

**Reference:** [Export to Scrivener](https://www.novelcrafter.com/help/docs/export/to-scrivener)

---

### US-08.8: Export to Atticus
**Priority:** 🟡 High | **Points:** 3

**As a** writer using Atticus for formatting,  
**I want to** export in Atticus-compatible format,  
**So that** I can format my book for publishing.

#### Acceptance Criteria:
- [ ] Export compatible with Atticus import
- [ ] Structure preserved
- [ ] Formatting preserved
- [ ] Chapter breaks correct
- [ ] Export instructions provided

**Reference:** [Export to Atticus](https://www.novelcrafter.com/help/docs/export/to-atticus)

---

### US-08.9: Export Metadata & Supplements
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer,  
**I want to** export Codex, chats, and snippets,  
**So that** I can backup or transfer my world-building.

#### Acceptance Criteria:
- [ ] Export Codex entries as JSON/Markdown
- [ ] Export chat history
- [ ] Export snippets
- [ ] Export prompts (custom)
- [ ] Export as ZIP bundle
- [ ] Include/exclude options

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── ImportExport/
│       ├── WordImporter.php
│       ├── MarkdownImporter.php
│       ├── WordExporter.php
│       ├── MarkdownExporter.php
│       ├── ScrivenerExporter.php
│       ├── AtticusExporter.php
│       └── StructureDetector.php
├── Http/
│   └── Controllers/
│       ├── ImportController.php
│       └── ExportController.php
├── Jobs/
│   ├── ProcessImport.php
│   └── ProcessExport.php
```

### Frontend Structure

```
resources/js/
├── Components/
│   └── ImportExport/
│       ├── ImportWizard.vue
│       ├── ImportPreview.vue
│       ├── StructureMapper.vue
│       ├── ExportDialog.vue
│       └── ExportOptions.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/novels/{novel}/import` | Upload file for import |
| POST | `/api/novels/{novel}/import/preview` | Preview import structure |
| POST | `/api/novels/{novel}/import/confirm` | Confirm and process import |
| GET | `/api/novels/{novel}/export` | Get export options |
| POST | `/api/novels/{novel}/export/word` | Export to Word |
| POST | `/api/novels/{novel}/export/markdown` | Export to Markdown |
| POST | `/api/novels/{novel}/export/scrivener` | Export to Scrivener |
| POST | `/api/novels/{novel}/export/atticus` | Export to Atticus |
| POST | `/api/novels/{novel}/export/metadata` | Export metadata bundle |

---

## ✅ Definition of Done

- [ ] Word import with structure detection
- [ ] Markdown import working
- [ ] Import preview and mapping complete
- [ ] Word export with formatting
- [ ] Markdown export working
- [ ] Scrivener export working
- [ ] Atticus export working
- [ ] Metadata export working
- [ ] Large file handling
- [ ] Progress indicators for long operations
- [ ] Feature tests for import/export

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Complex Word parsing | High | Use robust library (phpoffice/phpword) |
| Large file memory issues | High | Stream processing, chunking |
| Format compatibility issues | Medium | Thorough testing, user feedback |
| Structure detection accuracy | Medium | Manual correction options |

---

## 📎 References

- [Word (.docx) Import](https://www.novelcrafter.com/help/docs/import/word)
- [Markdown Import](https://www.novelcrafter.com/help/docs/import/markdown)
- [Export Novel](https://www.novelcrafter.com/help/docs/export/novel)
- [Export to Scrivener](https://www.novelcrafter.com/help/docs/export/to-scrivener)
- [Export to Atticus](https://www.novelcrafter.com/help/docs/export/to-atticus)
