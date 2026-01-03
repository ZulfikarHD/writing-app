# 📦 Sprint 28: Prompt Model & Sharing (FG-05.5)

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Status:** ✅ Completed

---

## 📋 Sprint Goals

Implementasi fitur tuning model settings dan sharing prompts, yaitu: **Model Parameter Tuning** (stop sequences, repetition penalty, tooltips) dan **Prompt Sharing** (export/import via clipboard). Fitur ini memungkinkan user melakukan fine-tuning parameter AI model per-prompt dan berbagi prompts dengan user lain melalui clipboard.

---

## ✨ Features Implemented

### 1. Enhanced Model Settings (F-05.5.1)
- **Stop Sequences**: Tag-style input untuk multiple stop sequences
- **Repetition Penalty**: Input field untuk model yang support parameter ini
- **Detailed Tooltips**: Penjelasan lengkap untuk setiap parameter model dengan guidance praktis
- **Responsive UI**: Layout yang rapi untuk semua control settings

### 2. Clone Prompt (F-05.5.2)
- **Card Dropdown Menu**: Action menu di PromptCard dengan Clone, Copy, Delete
- **Clone from Editor**: Clone button di prompt editor header
- **Auto-naming**: Clone otomatis diberi suffix "(Copy)"
- **Preserve Structure**: Clone mempertahankan semua settings, inputs, dan folder path

### 3. Prompt Sharing (F-05.5.3)
**Backend Implementation:**
- `PromptSharingService.php`: Export/import logic dengan kompresi
- `PromptSharingController.php`: REST endpoints untuk sharing
- Compressed base64 format: Efficient untuk sharing via clipboard
- Import validation: Validate structure sebelum import
- Preview before import: User bisa review sebelum add ke library

**Frontend Implementation:**
- `usePromptSharing.ts`: Composable untuk clipboard operations
- `PromptImportModal.vue`: Modal dengan preview dan validation
- Read from clipboard: Auto-detect prompt dari clipboard
- Manual paste support: Fallback jika clipboard API tidak available
- Copy to clipboard action: One-click copy dari PromptCard menu

### 4. Folder Organization (F-05.5.4)
- **Folder Parsing**: Parse " / " separator untuk folder hierarchy
- **FolderTree Component**: Display nested folder structure di library
- **Nested Selector**: PromptSelector support folder dropdown
- **Name Field Hint**: Helper text untuk folder syntax di TabGeneral
- **Virtual Folders**: No database table, pure naming convention

---

## 📁 File Structure

### Backend - New Files

```
app/
├── Services/Prompts/
│   └── PromptSharingService.php       ✨ NEW - Export/import logic
├── Http/Controllers/
│   └── PromptSharingController.php    ✨ NEW - Sharing endpoints
```

### Frontend - New Files

```
resources/js/
├── composables/
│   └── usePromptSharing.ts             ✨ NEW - Clipboard operations
├── components/prompts/
│   ├── PromptImportModal.vue           ✨ NEW - Import modal with preview
│   └── FolderTree.vue                  ✨ NEW - Nested folder display
```

### Frontend - Updated Files

```
resources/js/
├── composables/
│   └── usePrompts.ts                   ✏️ UPDATED - Folder utilities added
├── components/prompts/
│   ├── PromptCard.vue                  ✏️ UPDATED - Dropdown menu
│   ├── PromptSelector.vue              ✏️ UPDATED - Nested folders
│   └── editor/
│       └── TabGeneral.vue              ✏️ UPDATED - Stop sequences, tooltips, folder hint
├── Pages/Prompts/
│   └── Index.vue                       ✏️ UPDATED - Import modal, New dropdown
routes/
└── spa-api.php                         ✏️ UPDATED - Sharing routes
```

---

## 🔌 API Endpoints Summary

### Sharing Endpoints (NEW)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/prompts/{prompt}/export` | Export prompt as compressed base64 |
| POST | `/api/prompts/import/preview` | Preview imported prompt before saving |
| POST | `/api/prompts/import` | Import prompt to user's library |

> 📡 Full API documentation: [Prompts API](../04-api-reference/prompts.md)

---

## 🎯 Key Technical Decisions

### 1. Sharing Format
- **Compressed JSON**: gzcompress() untuk reduce size
- **Base64 encoding**: Safe untuk clipboard transfer
- **Structure preservation**: Export include all data (inputs, settings, messages)
- **Signature**: Magic header `NCPROMPT:` untuk validation

### 2. Folder Syntax
- **Separator**: " / " (space-slash-space) untuk readability
- **Case sensitive**: "Plot / Twist" ≠ "plot / twist"
- **No database table**: Virtual folders via naming convention
- **Display name**: Last part setelah final separator
- **Example**: "Brainstorm / Character / Backstory" → Display: "Backstory"

### 3. Model Settings Enhancement
- **Stop sequences**: Array of strings, max 10 sequences
- **Validation**: Trim empty strings, prevent duplicates
- **UI**: Tag-style input dengan add/remove
- **Tooltips**: Inline help text untuk setiap parameter

---

## 🎨 UI/UX Enhancements

### PromptCard Dropdown Menu
- Three-dot menu icon (kebab menu)
- Actions: Clone, Copy to Clipboard, Delete
- Hover states dan icons untuk clarity
- Confirmation untuk destructive actions

### Import Modal
- Auto-read dari clipboard (permission-based)
- Manual paste fallback
- Preview panel dengan:
  - Prompt name dan type
  - Message count
  - Input count
  - Component count
  - Model settings preview
- Name conflict detection
- Import button disabled until valid

### Model Settings Tooltips
Each parameter sekarang memiliki tooltip dengan:
- **What it does**: Penjelasan sederhana
- **When to use**: Use cases praktis
- **Typical values**: Range yang umum digunakan
- **Examples**: Contoh dampak perubahan value

---

## 📊 Business Rules

| Rule | Implementation | Validation |
|------|----------------|------------|
| BR-01: Stop sequences max 10 items | Frontend validation di TabGeneral | Array length check |
| BR-02: Component names must be unique per user | Database unique constraint | Backend validation |
| BR-03: Folder path max 3 levels deep | UI encouragement only | No hard limit |
| BR-04: Export includes all related data | Service layer composition | JSON structure check |
| BR-05: Import requires valid format | Signature check + JSON parse | Preview endpoint |

---

## 🔗 Related Documentation

- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **Testing Guide:** [Prompt Testing](../06-testing/prompts-testing.md)
- **User Journeys:** [Prompt Library Flow](../07-user-journeys/prompts/prompt-library-flow.md)
- **Previous Sprint:** [Sprint 27 - Advanced Features](sprint-27-prompt-advanced-features.md)

---

*Last Updated: 2026-01-04*
