# 🗺️ Epic 2: Story Planning & Outline Interface

**Epic ID:** EPIC-003  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 2-4  
**Total Story Points:** 53

---

## 📋 Deskripsi Epic

Membangun interface perencanaan cerita yang memungkinkan penulis untuk mengorganisir struktur novel dalam berbagai view (Grid, Matrix, Outline), mengelola scene labels, dan memvisualisasikan timeline cerita.

---

## 🎯 Goals

- Struktur hierarkis yang jelas (Acts → Chapters → Scenes)
- Multiple view options untuk berbagai kebutuhan planning
- Visual timeline untuk overview pacing
- Fitur filter dan search yang powerful
- Analytics untuk character distribution dan pacing

---

## 📑 User Stories

### US-010: Hierarchical Story Structure
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** mengatur novel dalam struktur Acts, Chapters, dan Scenes,  
**Agar** saya dapat merencanakan cerita dengan terstruktur.

#### Acceptance Criteria:
- [ ] Novel dapat dibagi menjadi Acts (optional)
- [ ] Acts berisi Chapters
- [ ] Chapters berisi Scenes
- [ ] Dapat menambah/menghapus Acts
- [ ] Dapat menambah/menghapus Chapters dalam Act
- [ ] Dapat menambah/menghapus Scenes dalam Chapter
- [ ] Hierarchy ditampilkan dengan jelas di sidebar
- [ ] Support untuk novel tanpa Acts (langsung Chapter)

#### Technical Notes:
- Flexible structure: Act optional
- Database schema mendukung nested hierarchy

---

### US-011: Grid View
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** melihat plan dalam Grid view,  
**Agar** saya dapat melihat overview semua scenes sekaligus.

#### Acceptance Criteria:
- [ ] Grid view menampilkan cards untuk setiap scene
- [ ] Cards grouped by Chapter/Act
- [ ] Card menampilkan: judul scene, summary preview, word count
- [ ] Card menampilkan: status label, POV character
- [ ] Drag & drop untuk reorder scenes
- [ ] Drag & drop untuk move scene antar chapters
- [ ] Click card untuk jump ke editor
- [ ] Responsive: 4 columns desktop, 2 columns tablet, 1 column mobile

#### Technical Notes:
- Use CSS Grid atau Masonry layout
- Implement drag & drop dengan library (vue-draggable)

---

### US-012: Matrix View
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** Matrix view untuk tracking story elements,  
**Agar** saya dapat melihat karakter/lokasi mana yang muncul di setiap scene.

#### Acceptance Criteria:
- [ ] Matrix dengan scenes sebagai rows
- [ ] Columns dapat dipilih: Characters, Locations, Tags, Subplots
- [ ] Cell menunjukkan presence (ada/tidak) atau count
- [ ] Click cell untuk jump ke scene tersebut
- [ ] Filter columns berdasarkan kategori
- [ ] Sort rows berdasarkan chapter order
- [ ] Horizontal scroll untuk banyak columns
- [ ] Highlight active row/column on hover

#### Technical Notes:
- Data dari Codex entries
- Consider virtual scrolling untuk banyak scenes

---

### US-013: Outline View
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** Outline view yang linear,  
**Agar** saya dapat melihat summary semua scenes secara sequential.

#### Acceptance Criteria:
- [ ] List view menampilkan scenes berurutan
- [ ] Setiap item menampilkan: chapter, scene title, summary
- [ ] Collapsible chapters
- [ ] Word count per scene dan total
- [ ] Quick edit summary inline
- [ ] Click untuk jump ke editor
- [ ] Print-friendly layout

---

### US-014: Scene Labels & Status
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** memberikan label/status pada scenes,  
**Agar** saya dapat tracking progress penulisan.

#### Acceptance Criteria:
- [ ] Predefined labels: Draft, Revision, Final, Needs Work
- [ ] Custom label dapat dibuat
- [ ] Color coding untuk setiap label
- [ ] Assign label dari plan view (quick action)
- [ ] Assign label dari editor
- [ ] Filter plan view berdasarkan label
- [ ] Label statistics di dashboard (e.g., 5 Draft, 10 Final)

#### Technical Notes:
- Labels stored per-novel (customizable)
- Default labels seeded saat novel baru

---

### US-015: Scene Subtitles & Notes
**Prioritas:** 🟢 Rendah | **Story Points:** 2

**Sebagai** penulis,  
**Saya ingin** menambah subtitle atau catatan pada scene,  
**Agar** pembaca (atau saya sendiri) tahu konteks waktu/lokasi.

#### Acceptance Criteria:
- [ ] Field untuk subtitle scene
- [ ] Field untuk internal notes (tidak di-export)
- [ ] Subtitle muncul di plan view cards
- [ ] Notes muncul di scene metadata panel
- [ ] Optional: time/date field
- [ ] Optional: location field (link ke Codex)

---

### US-016: Outline Import
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengimport outline dari teks,  
**Agar** saya dapat dengan cepat setup struktur novel.

#### Acceptance Criteria:
- [ ] Paste text outline ke import dialog
- [ ] Auto-detect structure dari formatting (indentation, numbering)
- [ ] Preview hasil parsing sebelum confirm
- [ ] Support template: 3-Act, 5-Act, Hero's Journey
- [ ] Customize how headings map to Acts/Chapters/Scenes
- [ ] Option: create as summaries only atau full scenes

#### Technical Notes:
- Parser untuk berbagai format outline
- Templates sebagai predefined structures

---

### US-017: Search & Filter
**Prioritas:** 🔴 Tinggi | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mencari dan filter scenes,  
**Agar** saya dapat dengan cepat menemukan scene yang dicari.

#### Acceptance Criteria:
- [ ] Search box di plan interface
- [ ] Search by scene title
- [ ] Search by summary content
- [ ] Search by character/location (Codex tags)
- [ ] Filter by label/status
- [ ] Filter by POV character
- [ ] Filter by chapter/act
- [ ] Clear filters dengan satu klik
- [ ] Result count displayed

---

### US-018: Timeline Overview
**Prioritas:** 🟡 Sedang | **Story Points:** 4

**Sebagai** penulis,  
**Saya ingin** visualisasi timeline cerita,  
**Agar** saya dapat melihat pacing dan distribusi scenes.

#### Acceptance Criteria:
- [ ] Vertical timeline bar di sidebar atau panel
- [ ] Setiap scene ditandai sebagai marker
- [ ] Marker size berdasarkan word count
- [ ] Marker color berdasarkan label/status
- [ ] Click marker untuk jump ke scene
- [ ] Current scene ter-highlight
- [ ] Chapter divisions visible
- [ ] Collapsible/expandable
- [ ] Tooltip on hover menampilkan scene info

#### Technical Notes:
- SVG atau Canvas untuk rendering
- Responsive scaling

---

### US-081: Appearance Heatmap
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** visualisasi heatmap untuk character appearances,  
**Agar** saya dapat melihat distribution dan balance karakter di cerita.

#### Acceptance Criteria:
- [ ] Heatmap view di Plan interface
- [ ] Rows = Scenes/Chapters
- [ ] Columns = Characters (dari Codex)
- [ ] Cell color intensity = mention frequency
- [ ] Click cell untuk jump ke scene
- [ ] Filter characters to display
- [ ] Legend untuk color intensity
- [ ] Export heatmap sebagai image

#### Technical Notes:
- Data dari mentions tracking
- SVG atau Canvas rendering

---

### US-083: Characters per Scene Analysis
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** melihat analisis jumlah karakter per scene,  
**Agar** saya dapat identify scenes yang overcrowded atau lacking presence.

#### Acceptance Criteria:
- [ ] Character count per scene displayed di plan view
- [ ] Warning indicator jika > 5 characters (overcrowded)
- [ ] Warning indicator jika 0 characters (lacking)
- [ ] Sort scenes by character count
- [ ] Filter scenes by character count range
- [ ] Quick view: which characters in each scene

---

### US-088: Manual References in Plan
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** menambah manual references ke scenes,  
**Agar** saya dapat highlight key locations, timelines, atau plot points.

#### Acceptance Criteria:
- [ ] Add reference ke scene di plan view
- [ ] Reference types: Location, Timeline, Plot Point, Custom
- [ ] Reference links ke Codex entry atau scene
- [ ] References displayed sebagai badges di scene cards
- [ ] Filter by reference
- [ ] Reference summary view

#### FRD Reference:
> "In the plan view, manual references can be added to highlight key locations, timelines, or plot points across scenes."

---

### US-107: Scene Card Appearance Customization
**Prioritas:** 🟢 Rendah | **Story Points:** 2

**Sebagai** penulis,  
**Saya ingin** customize tampilan scene cards di Plan view,  
**Agar** saya dapat melihat informasi yang paling relevan.

#### Acceptance Criteria:
- [ ] Choose what info to show on cards:
  - Title
  - Summary preview
  - Word count
  - POV character
  - Status label
  - Thumbnail
- [ ] Card size options (compact, normal, large)
- [ ] Color coding options

#### Source:
> [Changing Scene Card Appearance - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/plan/changing-scene-card-appearance)

---

## 📊 Sprint Breakdown

### Sprint 2 (53 total → 16 points)
- US-010: Hierarchical Story Structure (8 pts)
- US-011: Grid View (5 pts)
- US-017: Search & Filter (3 pts)

### Sprint 3 (37 remaining → 13 points)
- US-012: Matrix View (5 pts)
- US-013: Outline View (3 pts)
- US-014: Scene Labels & Status (5 pts)

### Sprint 4 (24 remaining → 11 points)
- US-015: Scene Subtitles & Notes (2 pts)
- US-016: Outline Import (5 pts)
- US-018: Timeline Overview (4 pts)

### Sprint 7 (13 remaining → 13 points)
- US-081: Appearance Heatmap (5 pts)
- US-083: Characters per Scene Analysis (3 pts)
- US-088: Manual References in Plan (3 pts)
- US-107: Scene Card Appearance Customization (2 pts)

---

## 🔗 Dependencies

- Epic 1 (Manuscript Editor) US-002 untuk scene/chapter structure
- Epic 3 (Codex) untuk Matrix View character/location columns
- Epic 3 (Codex) untuk Heatmap dan Character Analysis

---

## 📝 Notes

- Plan view harus lightweight dan fast loading
- Consider virtual scrolling untuk novel dengan 100+ scenes
- Mobile: prioritaskan Grid dan Outline view
- Heatmap dan analysis views sangat membantu untuk pacing
- Card customization improves UX untuk different workflows
