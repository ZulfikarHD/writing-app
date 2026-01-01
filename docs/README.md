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
- [Sprint 04 - Codex System](./10-sprints/sprint-04-codex-system.md) ✅ Complete ✨ NEW

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
- [Codex System Testing Guide](./06-testing/codex-testing.md) ✨ NEW

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

**Last Updated:** 2026-01-01
