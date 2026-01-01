# 📚 NovelWrite Documentation Hub

## Overview

NovelWrite adalah aplikasi AI-Assisted Novel Writing, yaitu: platform modern untuk menulis novel dengan bantuan AI yang membantu penulis dalam setiap tahap proses penulisan.

## 📁 Struktur Dokumentasi

| Folder | Deskripsi |
|--------|-----------|
| `01-getting-started/` | Quick start, installation, checklist |
| `02-architecture/` | System overview, folder structure |
| `03-development/` | Development guides (backend & frontend) |
| `04-api-reference/` | Full API documentation per resource |
| `05-guides/` | How-to guides |
| `06-testing/` | Test plans per feature |
| `07-user-journeys/` | User journey diagrams |
| `08-deployment/` | Production deployment guides |
| `09-troubleshooting/` | FAQ and common issues |
| `10-sprints/` | Sprint documentation |
| `11-appendix/` | Glossary, resources |

## 🚀 Quick Links

### Sprint Documentation
- [Sprint 01 - Foundation & Core Editor](./10-sprints/sprint-01-foundation.md) ✅ Complete
- [Sprint 02 - Manuscript Editor](./10-sprints/sprint-02-manuscript-editor.md) ✅ Complete
- [Sprint 03 - AI Connections & UI System](./10-sprints/sprint-03-ai-ui-system.md) ✅ Complete
- [Sprint 04 - Codex System](./10-sprints/sprint-04-codex-system.md) ✅ Complete
- [Sprint 13 - Codex V2: Auto-Mentions & Research](./10-sprints/sprint-13-codex-v2-enhancements.md) ✅ Complete
- [Sprint 14 - Codex V2: Tags & Enhanced Details](./10-sprints/sprint-14-codex-tags-details.md) ✅ Complete ✨ NEW
- [Sprint 15 - Codex V2: Batch Operations & QoL](./10-sprints/sprint-15-codex-enhancements.md) ✅ Complete ✨ NEW

### API Reference
- [Authentication API](./04-api-reference/authentication.md)
- [Novels API](./04-api-reference/novels.md)
- [Profile API](./04-api-reference/profile.md)
- [Manuscript Editor API](./04-api-reference/manuscript-editor.md)
- [AI Connections API](./04-api-reference/ai-connections.md)
- [Codex API](./04-api-reference/codex.md) ✨ NEW
- [Series API](./04-api-reference/series.md) ✨ NEW

### Testing
- [Foundation Testing Guide](./06-testing/foundation-testing.md)
- [Manuscript Editor Testing Guide](./06-testing/manuscript-editor-testing.md)
- [AI Connections Testing Guide](./06-testing/ai-connections-testing.md)
- [Codex System Testing Guide](./06-testing/codex-testing.md)
- [Sprint 15 Testing Guide](./06-testing/sprint-15-testing.md) ✨ NEW

### User Journeys
- [Authentication Flow](./07-user-journeys/authentication/user-auth-flow.md)

---

## 📋 Feature Status

| Feature | Status | Sprint Doc | API Doc | Test Plan |
|---------|--------|------------|---------|-----------|
| Authentication | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | [Link](./04-api-reference/authentication.md) | [Link](./06-testing/foundation-testing.md) |
| Dashboard | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | N/A | [Link](./06-testing/foundation-testing.md) |
| Novel Management | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | [Link](./04-api-reference/novels.md) | [Link](./06-testing/foundation-testing.md) |
| Profile Settings | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | [Link](./04-api-reference/profile.md) | [Link](./06-testing/foundation-testing.md) |
| Onboarding | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | N/A | [Link](./06-testing/foundation-testing.md) |
| Rich Text Editor | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | [Link](./04-api-reference/manuscript-editor.md) | [Link](./06-testing/manuscript-editor-testing.md) |
| Scene/Chapter Structure | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | [Link](./04-api-reference/manuscript-editor.md) | [Link](./06-testing/manuscript-editor-testing.md) |
| Editor Settings | ✅ Complete | [Sprint 01](./10-sprints/sprint-01-foundation.md) | N/A | [Link](./06-testing/manuscript-editor-testing.md) |
| AI Connections | ✅ Complete | [Sprint 03](./10-sprints/sprint-03-ai-ui-system.md) | [Link](./04-api-reference/ai-connections.md) | [Link](./06-testing/ai-connections-testing.md) |
| UI Component Library | ✅ Complete | [Sprint 03](./10-sprints/sprint-03-ai-ui-system.md) | N/A | N/A |
| **Codex System** | ✅ Complete | [Sprint 04](./10-sprints/sprint-04-codex-system.md) | [Link](./04-api-reference/codex.md) | [Link](./06-testing/codex-testing.md) |
| **Series Management** | ✅ Complete | [Sprint 04](./10-sprints/sprint-04-codex-system.md) | [Link](./04-api-reference/series.md) | [Link](./06-testing/codex-testing.md) |
| **Codex V2 - Auto-Mentions & Research** | ✅ Complete | [Sprint 13](./10-sprints/sprint-13-codex-v2-enhancements.md) | [Link](./04-api-reference/codex.md) | [Link](./06-testing/codex-testing.md) |
| **Codex V2 - Tags & Enhanced Details** | ✅ Complete | [Sprint 14](./10-sprints/sprint-14-codex-tags-details.md) | [Link](./04-api-reference/codex.md) | [Link](./06-testing/codex-testing.md) |
| **Codex V2 - Batch Operations & QoL** | ✅ Complete | [Sprint 15](./10-sprints/sprint-15-codex-enhancements.md) | [Link](./04-api-reference/codex.md) | [Link](./06-testing/sprint-15-testing.md) |

---

## 🛠️ Tech Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | Laravel | v12 |
| Frontend | Vue 3 | v3 |
| Routing | Inertia.js | v2 |
| Styling | Tailwind CSS | v4 |
| Animation | Motion-V | Latest |
| Package Manager | Yarn | v1.22 |
| PHP Version | PHP | v8.4 |

---

## 🔗 Sprint 04 - Codex System

### Features Delivered

#### Core Codex Features
- **Codex Entries**: Manajemen entry dengan 6 tipe (character, location, item, lore, organization, subplot)
- **Aliases**: Alternative names untuk setiap entry, digunakan untuk mention detection
- **Details**: Key-value pairs untuk structured data (height, age, occupation, etc.)
- **Relations**: Menghubungkan entries dengan berbagai relation types
- **Progressions**: Track perubahan entry sepanjang cerita
- **Categories**: Custom categories per novel dengan color coding
- **Mentions**: Auto-tracking mentions dalam scene content dengan heatmap visualization

#### Codex Enhancements
- **Bulk Import/Export**: JSON dan CSV support
- **Quick Create**: Create entry langsung dari editor
- **AI Context Control**: Per-entry control untuk AI inclusion
- **Mention Heatmap**: Visual representation mentions across scenes

#### Series Management
- **Series CRUD**: Create dan manage book series
- **Novel Assignment**: Assign novels to series dengan ordering
- **Series Codex**: Codex entries di level series (shared across all novels)
- **Inheritance**: Series entries otomatis visible di novel codex

### Quick Links
- [Sprint Documentation](./10-sprints/sprint-04-codex-system.md)
- [Codex API Documentation](./04-api-reference/codex.md)
- [Series API Documentation](./04-api-reference/series.md)
- [Testing Guide](./06-testing/codex-testing.md)

---

## 🔗 Sprint 13 - Codex V2: Auto-Mentions & Research

### Features Delivered

#### Sprint 13: Auto-Mentions & Research (Novelcrafter Parity)
- **Auto-Scan Mentions**: Synchronous mention scanning (no queue worker needed!)
- **Live Polling**: Real-time mention updates every 5 seconds
- **Tracking Toggle**: Per-entry control untuk mention tracking (`is_tracking_enabled`)
- **Research Notes**: Private notes field yang TIDAK dikirim ke AI
- **External Links**: Store reference links untuk research purposes
- **Context Builder**: Auto-include related entries dalam AI context

### Philosophy: "Auto-Save Everything"
Sprint ini mengimplementasikan filosofi bahwa **semua operasi Codex harus bekerja seperti editor auto-save** - otomatis, synchronous, real-time, tanpa memerlukan queue worker atau klik manual. Hasilnya adalah UX yang seamless di mana mentions update otomatis saat user menulis di editor.

### Quick Links
- [Sprint 13 Documentation](./10-sprints/sprint-13-codex-v2-enhancements.md)
- [Codex API Documentation - Sprint 13 Section](./04-api-reference/codex.md#research-notes--external-links-sprint-13)
- [Testing Guide - Sprint 13 Tests](./06-testing/codex-testing.md#sprint-13-test-cases)

---

## 🔗 Sprint 14 - Codex V2: Tags & Enhanced Details

### Features Delivered

#### US-12.4: Tags System
- **Custom Tags**: Create organizational labels dengan color coding
- **Predefined Tags**: 11 system tags per entry type (Protagonist, Major, Weapon, dll)
- **Tag Filtering**: Filter entries by assigned tags di Index page
- **NOT Sent to AI**: Pure organizational tool, separate dari Categories
- **Auto-Save Assignment**: Instant tag add/remove tanpa manual save

#### US-12.5: Enhanced Detail Types
- **Text Type**: Multi-line text input untuk backstory, notes
- **Line Type**: Single-line input untuk occupation, simple facts
- **Dropdown Type**: Pre-defined options dengan select UI
- **Codex Reference**: Link ke entry lain (schema ready, UI Sprint 16+)

#### US-12.6: AI Visibility per Detail
- **Always**: Always included in AI context
- **Never**: Private notes yang TIDAK dikirim ke AI
- **NSFW Only**: Only included dengan NSFW prompts
- **Granular Control**: Per-detail toggle dengan visual indicators (👁️/🔒/🔞)

#### US-12.7: Detail Presets
- **12 System Presets**: Story Role, Pronouns, Backstory, Occupation, dll
- **Filtered by Type**: Presets relevant ke entry type shown
- **One-Click Add**: "Add from Preset" dengan default values
- **Type-Aware**: Each preset has correct type dan AI visibility pre-set

### Stats
- **20+ Tests Added**: Tags CRUD, definitions, AI visibility filtering
- **3 New Tables**: codex_tags, codex_entry_tags, codex_detail_definitions
- **14 New API Endpoints**: Full tag & definition management
- **2,500+ Lines Added**: Backend + frontend implementation

### Quick Links
- [Sprint 14 Documentation](./10-sprints/sprint-14-codex-tags-details.md)
- [Codex API Documentation - Sprint 14 Section](./04-api-reference/codex.md#sprint-14-tags-system--enhanced-details)
- [NovelCrafter Parity Reference](https://www.novelcrafter.com/help/docs/codex/codex-details)

---

## 🔗 Sprint 15 - Codex V2: Batch Operations & QoL

### Features Delivered

#### F-12.7.2: Duplicate Entry
- **Deep Clone**: Clone entry dengan aliases, details, progressions
- **Smart Naming**: Auto-append "(Copy)" dengan increment untuk duplicates
- **Selective Clone**: Skip thumbnail, relations, mentions (by design)
- **Instant Redirect**: Navigate ke duplicate entry after creation

#### US-12.12: Bulk Create Entries
- **Text-Based Input**: Format "Name | Type | Description" (one per line)
- **Fuzzy Type Matching**: 40+ aliases (char → character, loc → location)
- **Preview Mode**: See what will be created before committing
- **Duplicate Detection**: Skip atau warn untuk existing entries
- **Comment Support**: Lines dengan # prefix ignored
- **Error Reporting**: Line-by-line validation dengan suggestions

#### US-12.14: Swap Relation Direction
- **One-Click Swap**: Reverse source ↔ target tanpa delete
- **Preserve Metadata**: Keep type, label, bidirectional flag
- **Instant Update**: No page reload needed

### Stats
- **18+ Tests Added**: Duplicate, bulk create, swap, edge cases
- **1 New Service**: BulkEntryCreator dengan parse, validate, create
- **3 New Endpoints**: duplicate, bulk-create, swap
- **3 New Components**: BulkCreateModal, CodexHoverTooltip, ProgressionEditorModal

### Quick Links
- [Sprint 15 Documentation](./10-sprints/sprint-15-codex-enhancements.md)
- [Sprint 15 Testing Guide](./06-testing/sprint-15-testing.md)
- [Codex API Documentation - Sprint 15 Section](./04-api-reference/codex.md#sprint-15-editor-integration--ux-enhancements)

---

## 🔗 Previous Sprints

### Sprint 03 - AI Connections & UI System
- **AI Provider Management**: Connect multiple AI providers (OpenAI, Anthropic, OpenRouter, Ollama, Groq, LM Studio)
- **Secure API Key Storage**: Encrypted storage with masked display
- **Connection Testing**: Test API credentials with detailed feedback
- **Model Discovery**: Fetch and display available models from each provider
- **Default Connection**: Set preferred AI provider as default
- **UI Component Library**: Comprehensive reusable components

[View Sprint 03 Documentation](./10-sprints/sprint-03-ai-ui-system.md)

---

---

*Last Updated: 2026-01-01*  
*Latest Features: Sprint 14 (Tags & Enhanced Details), Sprint 15 (Batch Operations)*
