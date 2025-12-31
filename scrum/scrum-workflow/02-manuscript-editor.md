# 📝 Epic 1: Manuscript Editor (Writing Interface)

**Epic ID:** EPIC-002  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 1-3  
**Total Story Points:** 68

---

## 📋 Deskripsi Epic

Membangun interface editor manuskrip yang bersih dan bebas gangguan untuk menulis prosa novel. Editor harus mendukung rich text formatting, struktur scene/chapter, integrasi AI, dan berbagai tool produktivitas.

---

## 🎯 Goals

- Editor yang nyaman dan distraction-free
- Struktur novel yang terorganisir (Scene/Chapter)
- Integrasi AI untuk membantu penulisan
- Pengalaman pengguna yang optimal di desktop dan mobile

---

## 📑 User Stories

### US-001: Rich Text Editor Dasar
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** memiliki editor teks yang bersih dan bebas gangguan,  
**Agar** saya dapat fokus menulis prosa tanpa distraksi.

#### Acceptance Criteria:
- [ ] Editor menyediakan area penulisan yang bersih dan luas
- [ ] Mendukung formatting dasar: bold, italic, underline, strikethrough
- [ ] Mendukung heading levels (H1, H2, H3)
- [ ] Mendukung bullet list dan numbered list
- [ ] Mendukung text alignment (left, center, right, justify)
- [ ] Font dapat diubah (minimal 3 pilihan font)
- [ ] Font size dapat diatur (12px - 24px)
- [ ] Paragraph spacing dapat diatur
- [ ] Line width/margin dapat diatur
- [ ] Auto-save setiap 30 detik atau saat pause typing

#### Technical Notes:
- Gunakan TipTap atau ProseMirror sebagai editor framework
- Implementasi debounce untuk auto-save
- Store content dalam format JSON (untuk flexibility)

---

### US-002: Scene & Chapter Structure
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** mengorganisir novel saya dalam struktur scene dan chapter,  
**Agar** saya dapat mengelola cerita dengan lebih terstruktur.

#### Acceptance Criteria:
- [ ] Novel dapat dibagi menjadi multiple chapters
- [ ] Setiap chapter dapat memiliki multiple scenes
- [ ] Sidebar menampilkan daftar chapter dan scene
- [ ] Dapat menambah scene baru via tombol "+" atau slash command "/scene"
- [ ] Dapat menambah chapter baru
- [ ] Scene dapat di-expand/collapse di sidebar
- [ ] Navigasi antar scene dengan klik di sidebar
- [ ] Drag & drop untuk reorder scene dalam chapter
- [ ] Drag & drop untuk reorder chapter
- [ ] Scene yang sedang aktif ter-highlight di sidebar

#### Technical Notes:
- Nested structure: Novel → Chapter → Scene
- Consider lazy loading untuk novel besar

---

### US-003: Scene Metadata Panel
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** melihat dan mengedit metadata setiap scene,  
**Agar** saya dapat tracking informasi penting scene seperti POV dan word count.

#### Acceptance Criteria:
- [ ] Panel metadata muncul saat scene dipilih
- [ ] Menampilkan nomor scene
- [ ] Menampilkan word count scene
- [ ] Dapat memilih POV character untuk scene
- [ ] Dapat menambah subtitle/catatan scene
- [ ] Dapat menambah time/location note
- [ ] Dapat melihat chapter summary
- [ ] Panel dapat di-toggle show/hide

#### Technical Notes:
- Real-time word count calculation
- POV linked ke Codex character entries

---

### US-004: Action Menu Scene/Chapter
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengakses action menu untuk scene dan chapter,  
**Agar** saya dapat melakukan berbagai operasi dengan cepat.

#### Acceptance Criteria:
- [ ] Context menu muncul saat right-click pada scene
- [ ] Scene actions: duplicate, delete, archive, export
- [ ] Scene actions: split scene, merge with previous/next
- [ ] Scene actions: toggle numbering, add subtitle
- [ ] Scene actions: exclude from AI context
- [ ] Chapter actions: copy all prose, export chapter
- [ ] Chapter actions: copy all beats/summaries to clipboard
- [ ] Chapter actions: delete empty chapter, toggle numbering
- [ ] Whole novel menu: copy all prose, copy all summaries
- [ ] Whole novel menu: delete all unused empty scenes
- [ ] Konfirmasi dialog untuk aksi destruktif (delete)

#### Technical Notes:
- Gunakan contextmenu component yang reusable
- Implement soft delete untuk archive

---

### US-005: Integrated AI Tools di Editor
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** mengakses AI tools langsung dari editor,  
**Agar** saya dapat meminta bantuan AI tanpa meninggalkan halaman menulis.

#### Acceptance Criteria:
- [ ] Action menu scene memiliki opsi "Summarize this scene"
- [ ] Action menu scene memiliki opsi "Detect characters"
- [ ] Action menu scene memiliki opsi "Chat with this scene"
- [ ] Hasil summarize dapat disimpan ke scene metadata
- [ ] Detected characters dapat ditambahkan ke Codex
- [ ] Chat membuka panel chat dengan konteks scene
- [ ] Loading indicator saat AI processing
- [ ] Error handling jika AI gagal

#### Technical Notes:
- Integrate dengan AI service layer
- Pass scene content sebagai context ke AI

---

### US-006: Slash Command Generation
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** menggunakan slash command untuk generate konten dengan AI,  
**Agar** saya dapat membuat prose baru dengan cepat.

#### Acceptance Criteria:
- [ ] Typing "/" membuka command menu
- [ ] Command tersedia: Scene Beat, Continue, Dialogue, Description
- [ ] Setelah pilih command, muncul input untuk instruksi
- [ ] AI generate content berdasarkan instruksi
- [ ] Preview hasil generate sebelum insert
- [ ] Opsi: Apply (terima), Retry (regenerate), Discard (buang)
- [ ] Opsi: Insert as collapsible section
- [ ] Generated content ter-highlight/berbeda style sementara

#### Technical Notes:
- Implement command palette pattern
- Consider streaming response untuk UX lebih baik

---

### US-007: Text Replacement Prompts
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** melakukan transformasi teks yang dipilih menggunakan AI,  
**Agar** saya dapat improve atau modify prose dengan bantuan AI.

#### Acceptance Criteria:
- [ ] Selection toolbar muncul saat teks di-highlight
- [ ] Built-in prompts: Expand, Rephrase, Shorten
- [ ] Built-in prompts: Change POV, Change Tense
- [ ] Built-in prompts: Add inner thoughts, Add description
- [ ] Dapat menggunakan custom prompt
- [ ] Preview hasil sebelum replace
- [ ] Opsi: Apply, Retry, Discard
- [ ] Original text tersimpan untuk undo

#### Technical Notes:
- Store selection range untuk accurate replacement
- Implement undo stack untuk revert

---

### US-008: Focus Mode
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mode fokus yang fullscreen,  
**Agar** saya dapat menulis tanpa gangguan UI elements.

#### Acceptance Criteria:
- [ ] Tombol "Focus Mode" di toolbar
- [ ] Focus mode menyembunyikan sidebar dan toolbar
- [ ] Hanya menampilkan editor dan teks
- [ ] Keyboard shortcut untuk toggle (F11 atau Ctrl+Shift+F)
- [ ] Press Escape untuk keluar focus mode
- [ ] Optional: typewriter mode (current line di tengah)

---

### US-009: Theme & Display Options
**Prioritas:** 🟢 Rendah | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengatur tampilan editor sesuai preferensi,  
**Agar** saya nyaman menulis dalam jangka waktu lama.

#### Acceptance Criteria:
- [ ] Toggle Dark Mode / Light Mode
- [ ] Pilihan font: Serif, Sans-serif, Monospace
- [ ] Dyslexia-friendly font option (OpenDyslexic)
- [ ] Adjustable line height
- [ ] Adjustable editor width (narrow, medium, wide)
- [ ] Preferences tersimpan per user
- [ ] Theme change instant tanpa reload

#### Technical Notes:
- Use CSS variables untuk theming
- Store preferences di localStorage dan sync ke server

---

### US-076: Collapsible Sections in Editor
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** menyimpan AI-generated content sebagai collapsible sections,  
**Agar** saya dapat compare berbagai versi dan melakukan modular editing.

#### Acceptance Criteria:
- [ ] Option "Insert as Section" saat AI generate content
- [ ] Sections ditampilkan sebagai collapsible blocks
- [ ] Section memiliki: title, content, timestamp
- [ ] Expand/collapse individual section
- [ ] Expand/collapse all sections
- [ ] Apply section content to main prose
- [ ] Delete section
- [ ] Reorder sections via drag & drop
- [ ] Compare multiple sections side-by-side
- [ ] Sections tersimpan dengan scene

#### Technical Notes:
- TipTap custom node untuk sections
- Store sections dalam JSON structure

#### FRD Reference:
> "After generation, the user can apply (accept) the text, retry (regenerate), discard, or insert it as a collapsible section for modular editing."

---

### US-078: Enhanced Scene Beats Workflow
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** workflow yang detail untuk crafting scene beats,  
**Agar** saya dapat memberikan instruksi yang tepat ke AI.

#### Acceptance Criteria:
- [ ] Beat editor panel setelah memilih "/" → "Scene Beat"
- [ ] Simple mode: single text input untuk quick beats
- [ ] Detailed mode: structured form dengan:
  - Character actions
  - Dialogue hints
  - Setting description
  - Emotional tone
  - Word count target
- [ ] Beat templates: "Continue story", "Dialogue scene", "Action scene", "Description"
- [ ] Save beat as template untuk reuse
- [ ] Beat history untuk scene
- [ ] Preview beat instructions sebelum generate
- [ ] Adjust AI parameters (model, temperature) dari beat panel

#### Technical Notes:
- Modal/panel component untuk beat crafting
- Templates stored per user

#### FRD Reference:
> "For example, choosing 'Scene Beat' and typing instructions lets the AI produce new prose content under the current scene."

---

### US-084: Marker/Highlighter Tool
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** highlight important notes dalam manuscript,  
**Agar** saya dapat menandai bagian yang perlu revisi atau attention.

#### Acceptance Criteria:
- [ ] Highlighter tool di editor toolbar
- [ ] Multiple highlight colors: yellow, green, pink, blue
- [ ] Custom highlight colors
- [ ] Add note/comment ke highlight
- [ ] List all highlights di sidebar
- [ ] Jump to highlight dari list
- [ ] Remove highlight
- [ ] Filter highlights by color
- [ ] Highlights tidak ter-export (internal use)

#### Technical Notes:
- TipTap mark extension untuk highlights

#### FRD Reference:
> "Marker/Highlighter - Highlight important notes"

---

## 📊 Sprint Breakdown

### Sprint 1 (68 total → 21 points)
- US-001: Rich Text Editor Dasar (8 pts)
- US-002: Scene & Chapter Structure (8 pts)
- US-009: Theme & Display Options (5 pts)

### Sprint 2 (47 remaining → 18 points)
- US-003: Scene Metadata Panel (5 pts)
- US-004: Action Menu Scene/Chapter (5 pts)
- US-005: Integrated AI Tools (8 pts)

### Sprint 3 (29 remaining → 29 points)
- US-006: Slash Command Generation (8 pts)
- US-007: Text Replacement Prompts (5 pts)
- US-008: Focus Mode (3 pts)
- US-076: Collapsible Sections in Editor (8 pts)
- US-078: Enhanced Scene Beats Workflow (5 pts)

### Sprint 5 (5 remaining → 5 points)
- US-084: Marker/Highlighter Tool (5 pts)

---

## 🔗 Dependencies

- Epic 7 (AI Connections) harus selesai sebelum US-005, US-006, US-007
- Epic 3 (Codex) diperlukan untuk POV character selection di US-003
- Epic 6 (Prompt Management) untuk beat templates

---

## 📝 Notes

- Prioritaskan mobile responsiveness sejak awal
- Editor harus smooth dengan dokumen > 100k words
- Consider offline support untuk draft editing
- Scene beats adalah fitur CORE untuk AI prose generation
- Collapsible sections memungkinkan modular editing workflow
