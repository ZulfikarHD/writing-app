# 📤 Epic 8: Content Import/Export & Data Management

**Epic ID:** EPIC-008  
**Prioritas:** 🟡 Sedang  
**Sprint Target:** 6-8  
**Total Story Points:** 43

---

## 📋 Deskripsi Epic

Membangun fitur import dari Word, Markdown, dan HTML, export ke berbagai format termasuk compatibility dengan Atticus, archive management, dan revision history untuk semua konten.

---

## 🎯 Goals

- Import existing work dengan mudah
- Export ke standard formats
- Data safety dengan revision history
- Archive untuk konten yang dihapus
- Compatibility dengan publishing tools

---

## 📑 User Stories

### US-058: Import dari Word (.docx)
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** mengimport novel dari file Word,  
**Agar** saya dapat melanjutkan project yang sudah ada.

#### Acceptance Criteria:
- [ ] Upload .docx file
- [ ] Parse document structure berdasarkan headings:
  - Heading 1 = Acts atau Chapters
  - Heading 2 = Chapters (jika H1 = Acts)
  - Scene breaks: "***" atau blank lines
- [ ] Preview parsed structure sebelum import
- [ ] Choose: import as prose atau as summaries
- [ ] Map styling ke app formatting
- [ ] Progress indicator untuk large files
- [ ] Error handling untuk invalid files
- [ ] Option: merge dengan existing atau replace

#### Technical Notes:
- Use library seperti mammoth.js untuk parsing
- Handle images (optional import)

---

### US-059: Import dari Markdown
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengimport dari Markdown files,  
**Agar** saya dapat import dari berbagai sources.

#### Acceptance Criteria:
- [ ] Upload .md file
- [ ] Parse headings: # = Act, ## = Chapter, ### = Scene
- [ ] Scene content = paragraphs under heading
- [ ] Preview dan confirm structure
- [ ] Handle front-matter YAML (optional)
- [ ] Import multiple .md files sebagai chapters

---

### US-060: Export ke Word (.docx)
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** export novel ke Word format,  
**Agar** saya dapat share atau submit ke publisher.

#### Acceptance Criteria:
- [ ] Export full novel sebagai .docx
- [ ] Include: title page, chapter headings, prose
- [ ] Options toggle:
  - Include Act titles (for Vellum/Atticus compatibility)
  - Include scene subtitles
  - Include summaries as comments atau footnotes
- [ ] Select specific chapters/scenes untuk partial export
- [ ] Standard manuscript formatting option
- [ ] Custom formatting options (font, spacing, margins)

#### Technical Notes:
- Use docx library untuk generation
- Template system untuk formatting

---

### US-061: Export ke Markdown
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** export ke Markdown,  
**Agar** saya dapat use di Scrivener atau other tools.

#### Acceptance Criteria:
- [ ] Export sebagai single .md file atau multiple files
- [ ] Heading structure mirroring app structure
- [ ] Include front-matter dengan metadata (optional)
- [ ] Compatible dengan Scrivener import
- [ ] Scene separators (---)

---

### US-062: Export Metadata & Supplements
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** export Codex, chats, dan snippets,  
**Agar** saya dapat backup semua data saya.

#### Acceptance Criteria:
- [ ] Export dialog dengan checkboxes:
  - [ ] Prose
  - [ ] Summaries
  - [ ] Codex entries
  - [ ] Chat histories
  - [ ] Snippets
- [ ] Export format: JSON atau structured folder
- [ ] Download sebagai ZIP file
- [ ] Include images jika ada

---

### US-063: Archive Management
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** archive scenes/content yang dihapus,  
**Agar** saya dapat recover jika diperlukan.

#### Acceptance Criteria:
- [ ] "Archive" action di scene menu (bukan hard delete)
- [ ] Archived scenes go ke archive repository
- [ ] Archive view di plan interface
- [ ] Browse archived scenes
- [ ] Restore archived scene ke specific location
- [ ] Permanently delete dari archive
- [ ] Archive retention policy (optional: auto-delete after X days)

---

### US-064: Revision History
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** revision history untuk content,  
**Agar** saya dapat revert ke versi sebelumnya.

#### Acceptance Criteria:
- [ ] Auto-save creates revision points
- [ ] Revision history untuk: scenes, summaries, Codex entries
- [ ] History button/icon di editable fields
- [ ] Timeline view menampilkan past versions
- [ ] Preview any version
- [ ] Restore specific version
- [ ] Compare two versions (diff view)
- [ ] Retention: keep last 50 revisions atau 30 days

#### Technical Notes:
- Efficient storage: store diffs bukan full copies
- Index untuk fast retrieval

---

### US-087: HTML Import
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** import novel dari HTML files,  
**Agar** saya dapat import dari web sources atau other apps.

#### Acceptance Criteria:
- [ ] Upload .html file
- [ ] Parse HTML structure (headings, paragraphs)
- [ ] Convert HTML formatting ke app format
- [ ] Preview parsed structure
- [ ] Handle images (optional import)
- [ ] Clean up unwanted HTML artifacts

#### FRD Reference:
> NovelCrafter supports import from "Word, Markdown, or HTML formats"

---

### US-105: Export to Atticus
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** export novel ke format yang compatible dengan Atticus,  
**Agar** saya dapat formatting dan publish dengan Atticus.

#### Acceptance Criteria:
- [ ] Export preset untuk Atticus
- [ ] Exclude act titles (Atticus requirement)
- [ ] Use asterisks (***) sebagai scene dividers
- [ ] Export sebagai Word (.docx)
- [ ] Instructions untuk import ke Atticus

#### Source:
> [Export to Atticus - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/export/novel)

---

## 📊 Sprint Breakdown

### Sprint 6 (43 total → 13 points)
- US-058: Import dari Word (8 pts)
- US-059: Import dari Markdown (5 pts)

### Sprint 7 (30 remaining → 14 points)
- US-060: Export ke Word (5 pts)
- US-061: Export ke Markdown (3 pts)
- US-062: Export Metadata (3 pts)
- US-105: Export to Atticus (3 pts)

### Sprint 8 (16 remaining → 16 points)
- US-063: Archive Management (5 pts)
- US-064: Revision History (8 pts)
- US-087: HTML Import (3 pts)

---

## 🔗 Dependencies

- Epic 1 (Manuscript Editor) untuk scene structure
- Epic 3 (Codex) untuk export Codex

---

## 📝 Notes

- Import harus handle edge cases dengan graceful error handling
- Consider background processing untuk large files
- Revision history penting untuk user confidence
- HTML import berguna untuk content dari web atau ebook conversion
- Atticus export preset simplifies publishing workflow
