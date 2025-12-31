# 📖 Epic 3: Codex (World & Character Database)

**Epic ID:** EPIC-004  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 3-5  
**Total Story Points:** 77

---

## 📋 Deskripsi Epic

Membangun Codex - sebuah wiki/database terintegrasi untuk menyimpan dan mengelola semua informasi tentang dunia cerita. Codex mencakup karakter, lokasi, item, lore, organisasi, subplot, dan elemen cerita lainnya. Data Codex digunakan sebagai context untuk AI dan untuk tracking mentions di seluruh manuscript.

---

## 🎯 Goals

- Database terstruktur untuk semua story elements
- Integrasi seamless dengan AI context
- Tracking mentions otomatis di manuscript
- Relationship mapping antar entries
- Easy organization dengan tags dan categories
- Progression tracking untuk character development

---

## 📑 User Stories

### US-019: Codex Entry Types
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** membuat berbagai tipe entry di Codex,  
**Agar** saya dapat mengorganisir informasi dunia cerita dengan terstruktur.

#### Acceptance Criteria:
- [ ] Entry types tersedia: Character, Location, Item, Lore, Organization, Subplot
- [ ] Setiap entry memiliki: name, type, description
- [ ] Icon berbeda untuk setiap type
- [ ] Filter entries by type
- [ ] Custom entry types dapat dibuat (advanced)
- [ ] Type dipilih saat create entry (tidak bisa diubah setelahnya)

#### Technical Notes:
- Enum untuk predefined types
- Flexible schema untuk custom types

---

### US-020: Create & Edit Codex Entry
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** membuat dan mengedit Codex entries,  
**Agar** saya dapat mendokumentasikan detail dunia cerita.

#### Acceptance Criteria:
- [ ] Create new entry dengan name dan type
- [ ] Rich text editor untuk description/sheet
- [ ] Auto-save saat editing
- [ ] List view semua entries dengan search
- [ ] Sort by: name, type, recently edited
- [ ] Delete entry dengan konfirmasi
- [ ] Duplicate entry option
- [ ] Entry preview di list hover

#### Technical Notes:
- CRUD operations dengan soft delete
- Full-text search indexing

---

### US-021: Codex Entry Detail View
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** detail view yang comprehensive untuk setiap entry,  
**Agar** saya dapat melihat semua informasi dalam satu tempat.

#### Acceptance Criteria:
- [ ] Header dengan name, type icon, thumbnail
- [ ] Tabs: Description, Details, Relations, Mentions, Research, Progressions
- [ ] Description tab: rich text sheet
- [ ] Quick actions: edit, delete, duplicate
- [ ] Breadcrumb navigation
- [ ] Back to list button
- [ ] Share/export entry option

---

### US-022: Tags & Labels untuk Codex
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** memberikan tags pada entries,  
**Agar** saya dapat mengorganisir dan filter entries dengan mudah.

#### Acceptance Criteria:
- [ ] Add multiple tags ke entry
- [ ] Predefined tags: Protagonist, Antagonist, Supporting, etc.
- [ ] Custom tags dapat dibuat
- [ ] Tag colors dapat di-customize
- [ ] Filter entries by tags
- [ ] Tag management UI (create, edit, delete tags)
- [ ] Tags visible di list view

#### Technical Notes:
- Many-to-many relationship
- Tags scoped per novel

---

### US-023: Thumbnail & Images
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** menambah gambar/thumbnail ke entries,  
**Agar** saya dapat visualisasi karakter dan lokasi.

#### Acceptance Criteria:
- [ ] Upload image untuk entry (max 5MB)
- [ ] Image crop tool untuk thumbnail
- [ ] Thumbnail displayed di list dan detail view
- [ ] Supported formats: JPG, PNG, WebP
- [ ] Image gallery untuk entry (multiple images)
- [ ] Delete/replace image
- [ ] Placeholder image untuk entries tanpa gambar

#### Technical Notes:
- Image storage (local atau cloud)
- Image optimization dan resizing

---

### US-024: Aliases & Nicknames
**Prioritas:** 🔴 Tinggi | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** menambah aliases/nicknames ke entries,  
**Agar** AI dan highlight system mengenali nama alternatif.

#### Acceptance Criteria:
- [ ] Field untuk menambah multiple aliases
- [ ] Contoh: "Robert Smith" aliases: "Bob", "Bobby", "Mr. Smith"
- [ ] Aliases di-highlight di manuscript seperti nama utama
- [ ] AI context includes aliases
- [ ] Autocomplete saat typing alias di editor
- [ ] Case-insensitive matching

#### Technical Notes:
- Array field untuk aliases
- Regex pattern untuk highlighting

---

### US-025: Custom Detail Fields
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** custom fields untuk entry details,  
**Agar** saya dapat menambah atribut spesifik (Role, Species, Age, dll).

#### Acceptance Criteria:
- [ ] Details tab dengan key-value fields
- [ ] Add custom field: label dan value
- [ ] Field types: text, number, date, select
- [ ] Reorder fields dengan drag & drop
- [ ] Delete field
- [ ] Template fields per entry type (e.g., Character always has "Age")
- [ ] Fields included dalam AI context

#### Technical Notes:
- JSON field untuk flexible schema
- Field templates per novel/type

---

### US-026: Research Notes
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** menyimpan research notes yang tidak dikirim ke AI,  
**Agar** saya dapat menyimpan info development yang private.

#### Acceptance Criteria:
- [ ] Research tab di entry detail
- [ ] Rich text editor untuk notes
- [ ] Clearly labeled "Not sent to AI"
- [ ] Attach links/URLs
- [ ] Image attachments
- [ ] Research notes separate dari main description

---

### US-027: Relations (Nested Entries)
**Prioritas:** 🟡 Sedang | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** menghubungkan entries satu sama lain,  
**Agar** saya dapat memetakan relationships (family, organizations, etc).

#### Acceptance Criteria:
- [ ] Relations tab menampilkan linked entries
- [ ] Add relation dengan searchable dropdown
- [ ] Relation type: custom label (e.g., "Father", "Member of", "Located in")
- [ ] Bidirectional relations (automatic reverse link)
- [ ] Nested view: clicking council shows all members
- [ ] Remove relation
- [ ] Relation visualization (simple graph/tree)
- [ ] Relations included dalam AI context saat entry dipanggil

#### Technical Notes:
- Self-referencing many-to-many
- Consider graph database untuk complex relations

---

### US-028: Mentions Tracking
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** melihat dimana entry di-mention dalam novel,  
**Agar** saya dapat tracking appearances dan consistency.

#### Acceptance Criteria:
- [ ] Mentions tab menampilkan semua occurrences
- [ ] Track mentions di: scenes, summaries, chats, snippets
- [ ] Show context preview untuk setiap mention
- [ ] Click mention untuk jump ke location
- [ ] Mini-timeline showing distribution of mentions
- [ ] Mention count displayed
- [ ] Auto-update saat content berubah

#### Technical Notes:
- Index names dan aliases
- Background job untuk re-indexing
- Efficient search algorithm

---

### US-029: Mention Highlighting in Editor
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** Codex entries di-highlight dalam editor,  
**Agar** saya dapat dengan mudah identify characters/locations dalam text.

#### Acceptance Criteria:
- [ ] Entry names highlighted dengan warna
- [ ] Alias names juga di-highlight
- [ ] Color coding by type (Character = blue, Location = green, etc.)
- [ ] Hover untuk preview entry info
- [ ] Click highlight untuk open entry detail
- [ ] Toggle highlighting on/off
- [ ] Settings untuk highlight colors

#### Technical Notes:
- Use TipTap marks untuk highlighting
- Performance optimization untuk long documents

---

### US-030: AI Context Controls
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengontrol bagaimana entries dikirim ke AI,  
**Agar** saya dapat manage context yang relevan.

#### Acceptance Criteria:
- [ ] AI context setting per entry:
  - Always include (global entry)
  - Include when detected (default)
  - Exclude from AI (manual only)
  - Never include (private)
- [ ] Global entries selalu masuk context
- [ ] Detected = auto-include when name appears in current scene
- [ ] Manual override per scene/chat
- [ ] Context preview sebelum send ke AI
- [ ] Token count indicator

#### Technical Notes:
- Smart detection algorithm
- Priority system untuk context limits

---

### US-031: Series Support (Shared Codex)
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** share Codex entries antar novels dalam series,  
**Agar** saya tidak perlu re-enter world data.

#### Acceptance Criteria:
- [ ] Create series grouping untuk novels
- [ ] Mark entry sebagai "Series-wide"
- [ ] Series entries accessible dari semua novels dalam series
- [ ] Option: copy to novel-specific atau keep shared
- [ ] Series management UI
- [ ] Import entries dari novel lain dalam series

#### Technical Notes:
- Series as parent entity
- Entry scoping (novel vs series)

---

### US-075: Codex Progression History
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** melihat history perubahan/evolusi Codex entries,  
**Agar** saya dapat tracking bagaimana karakter berkembang sepanjang cerita.

#### Acceptance Criteria:
- [ ] Tab "Progressions" di Codex entry detail
- [ ] Timeline menampilkan perubahan signifikan pada entry
- [ ] Setiap progression memiliki: timestamp, description, chapter reference
- [ ] Add progression manually dengan form
- [ ] Auto-detect progression dari scene summaries (optional AI feature)
- [ ] View "Codex additions" yang ditambahkan AI
- [ ] Progression dapat di-edit dan delete
- [ ] Export progression timeline untuk entry

#### Technical Notes:
- Separate table untuk progressions
- Linked ke scenes/chapters dimana perubahan terjadi

#### FRD Reference:
> "The app keeps a history of changes to each codex entry (for example, tracking how a character evolves) and shows 'codex additions' under the description."

---

### US-100: Subplots Management
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengelola subplots sebagai Codex entry type,  
**Agar** saya dapat tracking story threads secara terstruktur.

#### Acceptance Criteria:
- [ ] Subplot sebagai Codex entry type
- [ ] Summary dan details untuk subplot
- [ ] Alias assignment (SP1, SP2, etc.) untuk easy reference
- [ ] Track subplot progressions dengan Codex Addition
- [ ] Link subplot ke scenes dimana ia muncul
- [ ] Matrix view dapat filter by Subplots
- [ ] AI dapat mengakses subplot info

#### Source:
> [Subplots - NovelCrafter Docs](https://docs.novelcrafter.com/en/articles/9310212-subplots)

---

### US-101: Codex Categories
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengorganisir Codex entries ke dalam categories,  
**Agar** saya dapat menemukan entries dengan lebih mudah.

#### Acceptance Criteria:
- [ ] Create custom categories
- [ ] Assign category ke entry
- [ ] Filter entries by category
- [ ] Category hierarchy (parent/child)
- [ ] Category icons/colors
- [ ] Default categories per entry type

---

### US-102: Codex Details Quick Create
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** quickly create Codex details inline,  
**Agar** saya dapat menambah atribut tanpa banyak klik.

#### Acceptance Criteria:
- [ ] Quick add field dari detail view
- [ ] Autocomplete untuk common field names
- [ ] Inline editing untuk existing fields
- [ ] Bulk add multiple fields
- [ ] Copy fields dari template

---

### US-110: Progressions on Codex Details
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** track progressions untuk individual Codex details,  
**Agar** saya dapat tracking perubahan specific attributes sepanjang cerita.

#### Acceptance Criteria:
- [ ] Add progression ke specific detail field (not just entry)
- [ ] Contoh: Character age progression dari 18 → 25 → 30
- [ ] Timeline view untuk detail progressions
- [ ] Link progression ke chapter/scene

#### Source:
> [Progressions on Codex Details - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/codex/progressions-on-codex-details)

---

## 📊 Sprint Breakdown

### Sprint 3 (77 total → 21 points)
- US-019: Codex Entry Types (5 pts)
- US-020: Create & Edit Codex Entry (8 pts)
- US-021: Codex Entry Detail View (5 pts)
- US-024: Aliases & Nicknames (3 pts)

### Sprint 4 (56 remaining → 21 points)
- US-022: Tags & Labels (3 pts)
- US-023: Thumbnail & Images (5 pts)
- US-027: Relations (8 pts)
- US-025: Custom Detail Fields (5 pts)

### Sprint 5 (35 remaining → 21 points)
- US-028: Mentions Tracking (5 pts)
- US-029: Mention Highlighting (5 pts)
- US-030: AI Context Controls (5 pts)
- US-075: Codex Progression History (5 pts)
- US-026: Research Notes (3 pts) - carry over jika waktu

### Sprint 6 (14 remaining → 14 points)
- US-100: Subplots Management (5 pts)
- US-101: Codex Categories (3 pts)
- US-102: Codex Details Quick Create (3 pts)
- US-110: Progressions on Codex Details (3 pts)

### Backlog:
- US-031: Series Support (3 pts) - future release

---

## 🔗 Dependencies

- Epic 1 (Manuscript Editor) untuk mention highlighting di editor
- Epic 7 (AI Connections) untuk AI context features
- US-023 (Images) membutuhkan storage setup

---

## 🎨 UI/UX Considerations

### Desktop Layout
```
┌─────────────────────────────────────────────────────┐
│ Codex                          [+ New Entry] [⚙️]  │
├─────────────────────────────────────────────────────┤
│ 🔍 Search...            │ Filter: All Types ▼      │
├──────────────┬──────────────────────────────────────┤
│ Entry List   │ Entry Detail                        │
│              │ ┌───────────────────────────────────┐│
│ 👤 Alice     │ │ [img] Alice                      ││
│ 👤 Bob       │ │ Type: Character                  ││
│ 📍 Castle    │ │ Tags: Protagonist, Hero          ││
│ 📦 Sword     │ ├───────────────────────────────────┤│
│ 📜 Magic     │ │ [Desc] [Details] [Relations]     ││
│ 📊 Subplot1  │ │ [Mentions] [Research] [Progress] ││
│              │ ├───────────────────────────────────┤│
│              │ │ Description content...           ││
│              │ │                                  ││
└──────────────┴─┴───────────────────────────────────┘
```

### Mobile Layout
- Full-screen list view
- Tap entry → full-screen detail
- Bottom sheet untuk quick actions
- Swipe actions untuk edit/delete

---

## 📝 Notes

- Codex adalah fitur kunci untuk AI context
- Prioritaskan basic CRUD dan AI integration dulu
- Performance critical: bisa ada ratusan entries
- Consider offline caching untuk entries
- Mobile: focus pada list dan detail view, edit bisa simplified
- Progression tracking penting untuk series/long novels
- Subplots tracking helps with complex storylines
