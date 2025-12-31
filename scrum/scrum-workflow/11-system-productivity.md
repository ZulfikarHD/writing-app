# ⚙️ Epic 11: System & Productivity Features

**Epic ID:** EPIC-011  
**Prioritas:** 🟡 Sedang  
**Sprint Target:** 8-9, Backlog  
**Total Story Points:** 54

---

## 📋 Deskripsi Epic

Membangun fitur-fitur sistem dan productivity termasuk theme customization, backup/recovery, global search, statistics, settings management, dan localization.

---

## 🎯 Goals

- Customizable user experience (theme, layout)
- Data safety (backup, auto-recovery)
- Powerful search across all content
- Productivity tracking (word statistics)
- Settings portability (export/import)
- International support (i18n)

---

## 📑 User Stories

### US-068: Theme & Appearance Settings
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** customize appearance aplikasi,  
**Agar** saya nyaman menggunakan app dalam waktu lama.

#### Acceptance Criteria:
- [ ] Light/Dark mode toggle
- [ ] System preference auto-detect
- [ ] Accent color selection
- [ ] Font preferences (UI font)
- [ ] Font size (small/medium/large)
- [ ] Compact/comfortable spacing option
- [ ] Dyslexia-friendly mode
- [ ] Settings persist across sessions

---

### US-069: Collapsible UI Panels
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** collapsible dan resizable panels,  
**Agar** saya dapat customize workspace layout.

#### Acceptance Criteria:
- [ ] Sidebar dapat di-collapse
- [ ] Panel widths dapat di-resize
- [ ] Remember panel states per user
- [ ] Quick toggle shortcuts (keyboard)
- [ ] Reset to default layout option
- [ ] Multi-panel support (editor + chat side by side)

---

### US-070: Global Search
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** global search across project,  
**Agar** saya dapat menemukan content dimana saja.

#### Acceptance Criteria:
- [ ] Search box accessible dari anywhere (Ctrl+K)
- [ ] Search across: manuscript, summaries, Codex, snippets
- [ ] Instant results as-you-type
- [ ] Result preview dengan context
- [ ] Click result untuk navigate
- [ ] Filter by type (scenes, characters, etc.)
- [ ] Recent searches history

#### Technical Notes:
- Full-text search implementation
- Consider Elasticsearch atau Algolia untuk scale

---

### US-071: Backup & Restore
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** backup dan restore project,  
**Agar** data saya aman.

#### Acceptance Criteria:
- [ ] Export full project backup (ZIP/JSON)
- [ ] Include: all prose, Codex, chats, snippets, settings
- [ ] Import backup untuk restore
- [ ] Auto-backup option (daily/weekly)
- [ ] Backup to cloud storage (optional: Google Drive, Dropbox)
- [ ] Recovery dari auto-save jika crash

---

### US-073: API Keys Settings
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** manage API keys di settings,  
**Agar** saya dapat connect AI services.

#### Acceptance Criteria:
- [ ] Dedicated settings page untuk AI connections
- [ ] Add/edit/remove API keys
- [ ] Keys masked (show last 4 chars only)
- [ ] Test connection per provider
- [ ] Export/import settings (untuk multi-device)
- [ ] Clear all keys option

---

### US-074: Help & Documentation
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** pengguna,  
**Saya ingin** akses help dan dokumentasi,  
**Agar** saya dapat belajar menggunakan app.

#### Acceptance Criteria:
- [ ] Help icon/menu di header
- [ ] Tooltips untuk complex features
- [ ] In-app documentation/wiki
- [ ] Keyboard shortcuts reference
- [ ] Getting started guide
- [ ] FAQ section
- [ ] Contact support link

---

### US-077: Auto-Recovery & Data Safety
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** sistem recovery otomatis jika terjadi data loss,  
**Agar** tulisan saya tidak hilang karena masalah teknis.

#### Acceptance Criteria:
- [ ] Auto-save setiap 30 detik ke local storage
- [ ] Auto-save ke server setiap 2 menit atau saat pause typing
- [ ] Recovery notification saat login jika ada unsaved changes
- [ ] "Recover unsaved work" option dengan preview
- [ ] Conflict resolution jika local dan server berbeda
- [ ] Offline mode indicator
- [ ] Queue changes saat offline, sync saat online
- [ ] Manual "Save now" button dengan confirmation
- [ ] Recovery log viewable di settings

#### Technical Notes:
- IndexedDB untuk local storage
- Sync mechanism dengan conflict detection
- Service Worker untuk offline capability

#### FRD Reference:
> "If writing vanishes (e.g. internet glitch), there should be recovery options (as hinted by docs on revision history and quick-start guides)."

---

### US-082: Word Statistics Dashboard
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** dashboard statistik untuk word count dan progress,  
**Agar** saya dapat monitor produktivitas dan pacing cerita.

#### Acceptance Criteria:
- [ ] Total word count novel
- [ ] Word count per chapter/scene
- [ ] Daily word count tracking
- [ ] Writing streaks (berturut-turut hari menulis)
- [ ] Set word count goal (total dan daily)
- [ ] Progress bar menuju goal
- [ ] Graph: words written over time
- [ ] Average words per scene
- [ ] Longest/shortest scene indicator
- [ ] Export statistics

---

### US-085: Settings Export/Import
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** export dan import settings/AI connections,  
**Agar** saya dapat gunakan settings yang sama di multiple devices.

#### Acceptance Criteria:
- [ ] "Export Settings" button di Settings page
- [ ] Export includes: AI connections, prompts, theme preferences
- [ ] Export format: encrypted JSON file
- [ ] "Import Settings" dengan file upload
- [ ] Preview imported settings before apply
- [ ] Option: merge atau replace existing
- [ ] Exclude sensitive data option (API keys)
- [ ] Password protect export file (optional)

#### FRD Reference:
> "Settings for AI connections must be exportable and importable if the user uses multiple devices."

---

### US-089: Localization (i18n)
**Prioritas:** 🟢 Rendah | **Story Points:** 8

**Sebagai** pengguna,  
**Saya ingin** aplikasi mendukung berbagai bahasa,  
**Agar** saya dapat menggunakan app dalam bahasa yang saya pilih.

#### Acceptance Criteria:
- [ ] Language selector di settings
- [ ] Supported languages: English, Indonesian (minimum)
- [ ] All UI text translatable
- [ ] System messages dalam selected language
- [ ] Date/time format sesuai locale
- [ ] Number format sesuai locale
- [ ] Help docs in multiple languages

#### Technical Notes:
- Vue i18n plugin
- Laravel localization
- Translation files per language

---

### US-090: Enhanced Context Help & Tooltips
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** pengguna baru,  
**Saya ingin** inline help dan tooltips,  
**Agar** saya dapat memahami fitur tanpa baca dokumentasi.

#### Acceptance Criteria:
- [ ] Tooltip untuk semua icon buttons
- [ ] Help icon (?) di complex features
- [ ] Click help icon → explanation modal
- [ ] "What's this?" hover text
- [ ] First-time user onboarding tour
- [ ] Skip/don't show again option
- [ ] Link ke full documentation dari tooltip

#### FRD Reference:
> "Provide inline help tips and tooltips (e.g. explain what 'Exclude from AI' does) and link to documentation for advanced features."

---

### US-106: Pinning Feature
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** pin panels dan content untuk quick access,  
**Agar** saya dapat melihat informasi penting sambil menulis.

#### Acceptance Criteria:
- [ ] Pin chat panel beside editor
- [ ] Pin Codex entry untuk reference
- [ ] Pin snippet untuk reference
- [ ] Pinned items muncul di sidebar
- [ ] Maximum pinned items limit
- [ ] Unpin dengan satu klik
- [ ] Resize pinned panels

#### Source:
> [Pinning - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/app/pinning)

---

### US-109: App Layout Customization
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** customize app layout,  
**Agar** saya dapat mengatur workspace sesuai preferensi.

#### Acceptance Criteria:
- [ ] Choose sidebar position (left/right)
- [ ] Adjust panel widths
- [ ] Save layout presets
- [ ] Reset to default layout
- [ ] Remember layout per device

#### Source:
> [App Layout - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/app/app-layout)

---

## 📊 Sprint Breakdown

### Sprint 8 (23 points)
- US-070: Global Search (5 pts)
- US-077: Auto-Recovery (5 pts) - **CRITICAL for trust**
- US-068: Theme & Appearance (5 pts)
- US-069: Collapsible UI Panels (3 pts)
- US-082: Word Statistics Dashboard (5 pts)

### Sprint 9 (17 points)
- US-071: Backup & Restore (5 pts)
- US-073: API Keys Settings (3 pts)
- US-074: Help & Documentation (3 pts)
- US-085: Settings Export/Import (3 pts)
- US-106: Pinning Feature (3 pts)

### Backlog (Future - 14 points)
- US-089: Localization (8 pts)
- US-090: Enhanced Context Help & Tooltips (3 pts)
- US-109: App Layout Customization (3 pts)

---

## 🔗 Dependencies

- **Requires:** Epic 1 (Foundation) - User account for settings persistence
- US-077 (Auto-Recovery) should be early - builds user trust
- US-070 (Global Search) depends on content being indexable
- US-089 (i18n) can be done anytime, low dependency

---

## 📝 Notes

- **Auto-Recovery (US-077)** adalah CRITICAL untuk user trust - prioritaskan
- Theme settings should respect system preferences by default
- Global Search adalah productivity booster - very popular feature
- Statistics dashboard helps with writing motivation
- Localization opens international market (consider community translations)
- Pinning enhances multi-tasking workflow
- Consider keyboard shortcuts for all major actions
