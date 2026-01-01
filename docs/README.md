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

### API Reference
- [Authentication API](./04-api-reference/authentication.md)
- [Novels API](./04-api-reference/novels.md)
- [Profile API](./04-api-reference/profile.md)
- [Manuscript Editor API](./04-api-reference/manuscript-editor.md)
- [AI Connections API](./04-api-reference/ai-connections.md) ✨ NEW

### Testing
- [Foundation Testing Guide](./06-testing/foundation-testing.md)
- [Manuscript Editor Testing Guide](./06-testing/manuscript-editor-testing.md)
- [AI Connections Testing Guide](./06-testing/ai-connections-testing.md) ✨ NEW

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

---

## 🔗 Sprint 03 - AI Connections & UI System

### Features Delivered
- **AI Provider Management**: Connect multiple AI providers (OpenAI, Anthropic, OpenRouter, Ollama, Groq, LM Studio)
- **Secure API Key Storage**: Encrypted storage with masked display
- **Connection Testing**: Test API credentials with detailed feedback
- **Model Discovery**: Fetch and display available models from each provider
- **Default Connection**: Set preferred AI provider as default
- **UI Component Library**: Comprehensive reusable components (Forms, Badges, Buttons, Modals, Alerts, Toasts)
- **Feedback System**: Toast notifications dan confirmation dialogs untuk better UX
- **Error Handling**: Consistent error display dengan Alert components

### Quick Links
- [Sprint Documentation](./10-sprints/sprint-03-ai-ui-system.md)
- [API Documentation](./04-api-reference/ai-connections.md)
- [Testing Guide](./06-testing/ai-connections-testing.md)

---

**Last Updated:** 2026-01-01
