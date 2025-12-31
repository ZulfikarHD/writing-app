# 📚 Glossary - Istilah & Definisi

**Version:** 1.0  
**Last Updated:** 31 Desember 2024

---

## 🎯 Tujuan

Dokumen ini mendefinisikan istilah-istilah yang digunakan dalam proyek AI-Assisted Novel Writing App untuk memastikan pemahaman yang konsisten.

---

## 📖 Istilah Umum (General Terms)

### Novel
Proyek penulisan utama yang berisi chapters, scenes, dan semua data terkait.

### Manuscript
Keseluruhan teks prosa dari novel, termasuk semua scenes.

### Chapter
Unit organisasi dalam novel, berisi satu atau lebih scenes.

### Scene
Unit terkecil dari konten cerita. Setiap scene memiliki prose, summary, dan metadata.

### Act
Level organisasi tertinggi (opsional), berisi satu atau lebih chapters. Umum dalam struktur 3-Act atau 5-Act.

### Prose
Teks naratif yang ditulis - isi cerita sebenarnya.

### Summary
Ringkasan singkat dari scene atau chapter yang menjelaskan apa yang terjadi.

### POV (Point of View)
Sudut pandang karakter dari mana scene diceritakan (First person, Third person limited, Third person omniscient).

### Tense
Waktu grammatikal cerita (Past tense, Present tense).

---

## 📝 Istilah Editor (Editor Terms)

### Focus Mode
Mode tampilan fullscreen tanpa UI elements untuk menulis tanpa distraksi.

### Typewriter Mode
Mode dimana baris yang sedang ditulis selalu di tengah layar.

### Slash Command
Perintah yang diakses dengan mengetik "/" di editor untuk trigger actions.

### Scene Beat
Instruksi atau outline singkat yang diberikan ke AI untuk generate prose. Menjelaskan apa yang harus terjadi di bagian cerita.

### Collapsible Section
Blok konten yang dapat di-expand/collapse, digunakan untuk menyimpan multiple versi atau draft AI.

### Text Transformation
Fitur AI yang memodifikasi teks yang dipilih (Expand, Rephrase, Shorten, etc.).

### Marker/Highlight
Penanda visual dalam teks untuk catatan internal penulis.

---

## 🗂️ Istilah Planning (Planning Terms)

### Grid View
Tampilan planning berbentuk kartu-kartu scene yang dapat di-drag & drop.

### Matrix View
Tampilan tabel yang menunjukkan hubungan antara scenes dan story elements (characters, locations).

### Outline View
Tampilan list linear dari semua scenes dengan summaries.

### Timeline
Visualisasi vertikal yang menunjukkan distribusi dan pacing scenes dalam novel.

### Scene Label/Status
Tag yang menunjukkan status penulisan scene (Draft, Revision, Final, Needs Work).

### Heatmap
Visualisasi intensitas warna yang menunjukkan frekuensi kemunculan karakter atau elemen.

---

## 📖 Istilah Codex (Codex Terms)

### Codex
Database wiki internal untuk menyimpan informasi tentang dunia cerita.

### Codex Entry
Satu item dalam Codex (karakter, lokasi, item, lore, dll).

### Entry Type
Kategori entry: Character, Location, Item, Lore, Organization, Subplot.

### Alias
Nama alternatif untuk entry (nickname, title, acronym). Contoh: "Bob" adalah alias untuk "Robert Smith".

### Description/Sheet
Teks deskriptif utama untuk entry yang dikirim ke AI sebagai context.

### Detail Fields
Atribut key-value tambahan (Age, Species, Role, etc.).

### Research Notes
Catatan tambahan yang TIDAK dikirim ke AI, untuk keperluan development internal.

### Relation
Hubungan antara dua entries (Father of, Member of, Located in).

### Mention
Kemunculan nama entry dalam manuscript atau dokumen lain.

### Mention Highlighting
Fitur yang meng-highlight nama Codex entries dalam editor dengan warna.

### AI Context Mode
Setting bagaimana entry dikirim ke AI:
- **Always Include:** Selalu masuk context (global entry)
- **Include When Detected:** Otomatis masuk jika nama muncul di scene aktif
- **Exclude from AI:** Tidak pernah otomatis, harus manual add
- **Never Include:** Private, tidak pernah dikirim ke AI

### Progression
Catatan evolusi/perubahan entry seiring waktu cerita.

### Series
Kumpulan novel yang berbagi Codex entries.

---

## 💬 Istilah AI/Chat (AI Terms)

### Chat Thread
Satu percakapan dengan AI, memiliki history messages.

### Context
Informasi yang diberikan ke AI sebagai latar belakang (scene content, Codex entries, outline).

### Context Injection
Proses memasukkan context ke prompt AI secara otomatis.

### Token
Unit terkecil yang diproses oleh AI model. Biasanya ~4 karakter per token.

### Prompt
Instruksi yang diberikan ke AI, terdiri dari system message dan user message.

### System Message
Instruksi ke AI tentang bagaimana berperilaku/merespon.

### User Message
Input dari pengguna ke AI.

### Temperature
Parameter AI yang mengontrol kreativitas/randomness. 0 = deterministic, 2 = very creative.

### Max Tokens
Batas maksimum panjang response AI.

### Streaming
Teknik menampilkan response AI secara incremental (karakter per karakter).

---

## 🎯 Istilah Prompt (Prompt Terms)

### Prompt Library
Koleksi prompts yang dapat di-reuse.

### Prompt Variable
Placeholder dalam prompt yang diisi saat digunakan ({{word_count}}, {{tone}}).

### Prompt Category
Pengelompokan prompts: Generation, Transformation, Analysis, Brainstorming.

### Built-in Prompt
Prompt default yang sudah ada dalam sistem (Expand, Rephrase, Shorten).

### Custom Prompt
Prompt yang dibuat oleh user.

---

## 🔌 Istilah AI Connection (Connection Terms)

### Provider
Penyedia layanan AI (OpenAI, Anthropic, Google, Local).

### API Key
Kunci rahasia untuk mengakses API provider.

### Endpoint
URL server AI untuk mengirim requests.

### Model
Specific AI model dari provider (GPT-4, Claude 3, Llama 2).

### OpenAI-Compatible
API yang mengikuti format OpenAI, bisa digunakan dengan berbagai providers.

### Local Model
AI model yang berjalan di komputer user, bukan cloud.

### Ollama
Software untuk menjalankan local LLMs.

### LM Studio
GUI application untuk menjalankan local LLMs.

---

## 📤 Istilah Import/Export (Import/Export Terms)

### Import
Membawa konten dari file eksternal ke dalam aplikasi.

### Export
Menyimpan konten aplikasi ke file eksternal.

### Archive
Menyimpan scene/content yang dihapus untuk kemungkinan restore.

### Revision History
Catatan semua versi sebelumnya dari content untuk undo/restore.

### Backup
Salinan lengkap semua data proyek.

---

## 🏗️ Istilah Teknis (Technical Terms)

### SPA (Single Page Application)
Aplikasi web yang berjalan dalam satu halaman tanpa full page reload.

### SSR (Server-Side Rendering)
Rendering halaman di server sebelum dikirim ke browser.

### API (Application Programming Interface)
Interface untuk komunikasi antar sistem.

### CRUD
Create, Read, Update, Delete - operasi dasar database.

### Webhook
HTTP callback untuk notifikasi events.

### WebSocket
Protokol untuk komunikasi real-time bidirectional.

### Queue
Sistem untuk memproses tasks secara asynchronous.

### Cache
Penyimpanan sementara untuk data yang sering diakses.

### Encryption
Proses mengamankan data dengan algoritma kriptografi.

---

## 📊 Istilah Scrum (Scrum Terms)

### Epic
Kumpulan besar user stories yang membentuk satu fitur major.

### User Story
Deskripsi fitur dari perspektif user: "Sebagai [role], saya ingin [feature], agar [benefit]".

### Acceptance Criteria
Kondisi yang harus dipenuhi agar story dianggap selesai.

### Story Point
Estimasi relatif kompleksitas dan effort untuk story.

### Sprint
Periode waktu tetap (2 minggu) untuk development.

### Sprint Backlog
Daftar stories yang akan dikerjakan dalam sprint.

### Product Backlog
Daftar semua stories yang belum dikerjakan.

### Velocity
Jumlah story points yang diselesaikan per sprint.

### Definition of Done (DoD)
Kriteria untuk menganggap story selesai.

### Definition of Ready (DoR)
Kriteria untuk story siap diambil ke sprint.

### Burndown Chart
Grafik yang menunjukkan progress story points dalam sprint.

---

## 🎨 Istilah UI/UX (UI/UX Terms)

### Responsive Design
Design yang menyesuaikan dengan berbagai ukuran layar.

### Mobile-First
Pendekatan design yang memprioritaskan mobile experience.

### Dark Mode
Tema tampilan dengan background gelap.

### Light Mode
Tema tampilan dengan background terang.

### Sidebar
Panel navigasi di sisi kiri/kanan layar.

### Modal
Dialog popup yang menutupi konten utama.

### Toast
Notifikasi kecil yang muncul sebentar.

### Tooltip
Teks penjelasan yang muncul saat hover.

### Dropdown
Menu yang muncul saat klik tombol.

### Drag & Drop
Interaksi memindahkan item dengan mouse/touch.

---

## 📝 Singkatan (Abbreviations)

| Singkatan | Kepanjangan |
|-----------|-------------|
| AI | Artificial Intelligence |
| API | Application Programming Interface |
| CRUD | Create, Read, Update, Delete |
| CSS | Cascading Style Sheets |
| DoD | Definition of Done |
| DoR | Definition of Ready |
| FRD | Functional Requirements Document |
| HTML | HyperText Markup Language |
| HTTP | HyperText Transfer Protocol |
| JSON | JavaScript Object Notation |
| LLM | Large Language Model |
| MVP | Minimum Viable Product |
| OAuth | Open Authorization |
| POV | Point of View |
| REST | Representational State Transfer |
| SPA | Single Page Application |
| SQL | Structured Query Language |
| UI | User Interface |
| URL | Uniform Resource Locator |
| UX | User Experience |
| WIB | Waktu Indonesia Barat |
| WYSIWYG | What You See Is What You Get |

---

## 📚 Referensi

- [NovelCrafter Documentation](https://www.novelcrafter.com/help/docs)
- [OpenAI API Reference](https://platform.openai.com/docs)
- [Anthropic API Reference](https://docs.anthropic.com)
- [Scrum Guide](https://scrumguides.org)
