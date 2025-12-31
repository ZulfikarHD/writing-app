# 📚 AI-Assisted Novel Writing App - Scrum Workflow

## 🎯 Project Overview

**Nama Proyek:** AI-Assisted Novel Writing App  
**Tipe:** Single-user Web Application  
**Referensi:** NovelCrafter Documentation  
**Tanggal Dibuat:** 31 Desember 2024  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Timezone:** Asia/Jakarta (WIB)

---

## 🌟 Visi Produk

Membangun aplikasi web penulisan novel yang terintegrasi dengan AI, memberikan pengalaman menulis yang bersih dan bebas gangguan dengan fitur:

- ✍️ **Rich Text Editor** - Manuscript editing yang powerful
- 📊 **Story Planning** - Grid, Matrix, Outline, Timeline views
- 📖 **Codex Wiki** - Database untuk characters, locations, lore
- 💬 **AI Chat** - Brainstorming dengan context-aware AI
- 🎯 **Smart Prompts** - Customizable AI prompt templates dengan Personas, Presets, Components
- 🔌 **Multi-AI Support** - OpenAI, Claude, Gemini, Groq, Local LLMs
- 👥 **Collaboration** - Coauthoring dan Teams support
- 🚀 **Guided Onboarding** - Interactive tutorial untuk new users

---

## 🚀 Getting Started Flow

Karena aplikasi ini **tidak menggunakan sidebar-based navigation** seperti NovelCrafter, user membutuhkan **guided onboarding experience** yang comprehensive:

### Quick Start Journey:
```
1. Welcome Screen
   ↓
2. Account Setup (Email/Password)
   ↓
3. Create First Novel (Wizard)
   ↓
4. App Layout Tour (Interactive)
   ↓
5. First Writing Session Guide
   ↓
6. AI Setup Walkthrough (Optional)
   ↓
7. Dashboard (Ready to Write!)
```

### Key Onboarding Components:
| Step | Deskripsi | User Story |
|------|-----------|------------|
| Account Setup | Registrasi dan login flow | US-072 |
| Create Novel | Wizard dengan templates | US-066 |
| App Tour | Interactive layout introduction | US-111 |
| First Session | Guided writing experience | US-111 |
| AI Setup | Step-by-step API connection | US-111, Epic 7 |
| Import Option | Bring existing work | US-058, US-059 |
| Help & Tips | Context-aware assistance | US-090, US-074 |

### Reference:
> [NovelCrafter Quick Start](https://www.novelcrafter.com/help/getting-started/quick-start/creating-your-first-novel)

---

## 📁 Struktur Dokumen Scrum

### 📑 Core Documents

| File | Deskripsi | Status |
|------|-----------|--------|
| [00-index.md](./00-index.md) | Overview & Navigation (file ini) | ✅ Complete |
| [10-sprint-roadmap.md](./10-sprint-roadmap.md) | Sprint calendar & milestones | ✅ Complete |
| [11-technical-architecture.md](./11-technical-architecture.md) | Tech stack & database schema | ✅ Complete |
| [12-risk-management.md](./12-risk-management.md) | Project risks & mitigation | ✅ Complete |
| [13-glossary.md](./13-glossary.md) | Terms & definitions | ✅ Complete |
| [14-gap-analysis.md](./14-gap-analysis.md) | FRD vs Scrum traceability | ✅ Complete |

### 🎯 Epic Files

| No | Epic | File | Sprint | Story Points | Prioritas |
|----|------|------|--------|--------------|-----------|
| 1 | Manuscript Editor | [01-manuscript-editor.md](./01-manuscript-editor.md) | 1-3 | 68 | 🔴 Tinggi |
| 2 | Story Planning | [02-story-planning.md](./02-story-planning.md) | 2-4 | 53 | 🔴 Tinggi |
| 3 | Codex (World Database) | [03-codex.md](./03-codex.md) | 3-6 | 77 | 🔴 Tinggi |
| 4 | Snippets | [04-snippets.md](./04-snippets.md) | 4-5 | 13 | 🟡 Sedang |
| 5 | AI/Chat Interface | [05-ai-chat.md](./05-ai-chat.md) | 4-6 | 47 | 🔴 Tinggi |
| 6 | Prompt Management | [06-prompt-management.md](./06-prompt-management.md) | 5-7 | 58 | 🟡 Sedang |
| 7 | AI Connections | [07-ai-connections.md](./07-ai-connections.md) | 3-4 | 48 | 🔴 Tinggi |
| 8 | Import/Export | [08-import-export.md](./08-import-export.md) | 6-8 | 43 | 🟡 Sedang |
| 9 | Organization & Misc | [09-organization.md](./09-organization.md) | 7-9 | 103 | 🟢 Rendah |

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Total Epics** | 9 |
| **Total User Stories** | 111 |
| **Total Story Points** | **510** |
| **Sprint Duration** | 2 minggu |
| **Total Sprints** | 9-10 |
| **Estimated Duration** | ~5 bulan |

### Story Points per Epic:
| Epic | Points |
|------|--------|
| 1. Manuscript Editor | 68 |
| 2. Story Planning | 53 |
| 3. Codex | 77 |
| 4. Snippets | 13 |
| 5. AI/Chat | 47 |
| 6. Prompt Management | 58 |
| 7. AI Connections | 48 |
| 8. Import/Export | 43 |
| 9. Organization | 103 |
| **TOTAL** | **510** |

---

## 🗓️ Sprint Timeline Overview

```
Week  1-2  ████████ Sprint 1: Foundation & Core Editor
Week  3-4  ████████ Sprint 2: Editor Complete & Planning Start
Week  5-6  ████████ Sprint 3: AI Connections & Codex Foundation
Week  7-8  ████████ Sprint 4: Codex Complete & Chat Start
Week  9-10 ████████ Sprint 5: AI Features & Prompts
Week 11-12 ████████ Sprint 6: Import/Export & Prompt Advanced
Week 13-14 ████████ Sprint 7: Export Complete & Organization
Week 15-16 ████████ Sprint 8: Collaboration & Statistics
Week 17-18 ████████ Sprint 9: Final Polish & Launch Prep
```

### 🎯 Release Milestones

| Milestone | Sprint | Features |
|-----------|--------|----------|
| **MVP** | End of Sprint 4 | Editor, AI Connections, Basic Codex, Basic Chat |
| **Beta** | End of Sprint 6 | + Slash Commands, Import, Prompts, Personas |
| **RC** | End of Sprint 8 | + Export, Revision History, Collaboration |
| **GA** | End of Sprint 9 | + Polish, Help, Teams, All Features |

---

## 📋 Epic Summary

### Epic 1: Manuscript Editor (68 pts)
Core writing interface dengan:
- Rich text editor dengan formatting
- Scene/Chapter structure
- AI integration (summarize, detect characters)
- Slash commands untuk generation
- Text transformation prompts
- Focus mode, themes
- Collapsible sections untuk AI-generated content
- Enhanced scene beats workflow
- Marker/Highlighter tool

### Epic 2: Story Planning (53 pts)
Planning interface dengan:
- Grid, Matrix, Outline views
- Scene labels & status
- Timeline visualization
- Search & filter
- Outline import
- Appearance heatmap
- Characters per scene analysis
- Scene card customization
- Manual references

### Epic 3: Codex (77 pts)
World & character database:
- Entry types (Character, Location, Item, Lore, Subplot, etc.)
- Tags, aliases, thumbnails
- Relations (family, organizations)
- Mentions tracking & highlighting
- AI context controls
- Series support
- Progression history
- Categories & Quick Create
- Detail progressions

### Epic 4: Snippets (13 pts)
Quick notes system:
- Create & manage snippets
- Pin for quick access
- Extract to Codex/Scene

### Epic 5: AI/Chat Interface (47 pts)
Chat with AI:
- Multiple chat threads
- Context injection (novel, scene, codex)
- Model selection
- Brainstorming tools
- Extract to novel
- Structured data extraction (Extract Feature)

### Epic 6: Prompt Management (58 pts)
Prompt library:
- Create custom prompts
- Variables & templates
- Built-in prompts
- Preview & test
- Model parameters
- Prompt Personas (AI memory)
- Prompt Presets (configurations)
- Prompt Components (reusable)
- Sharing & Submenus

### Epic 7: AI Connections (48 pts)
Connect to AI providers:
- OpenAI (GPT-4, GPT-4o)
- Anthropic (Claude)
- Google (Gemini)
- Groq (fast inference)
- Local (Ollama, LM Studio)
- OpenRouter OAuth
- Model Collections
- NSFW guidance
- Usage tracking

### Epic 8: Import/Export (43 pts)
Data management:
- Import from Word, Markdown, HTML
- Export to Word, Markdown
- Export to Atticus
- Archive management
- Revision history

### Epic 9: Organization (103 pts)
Supporting features:
- Dashboard & project list
- Novel creation & setup with templates
- Theme customization
- Global search
- Backup & restore
- User account
- Auto-recovery & data safety
- Novel covers
- Word statistics dashboard
- Settings export/import
- Collaboration & Coauthoring
- Teams feature
- Pinning
- App layout customization
- Localization (i18n)
- Context help & tooltips
- **Getting Started / Onboarding Experience** (guided tutorial for new users)

---

## 👥 Definition of Ready (DoR)

Sebuah User Story dianggap "Ready" jika memenuhi:

- [x] Deskripsi user story jelas dengan format "Sebagai [role], saya ingin [fitur], agar [manfaat]"
- [x] Acceptance Criteria terdefinisi dengan jelas
- [x] Story Points sudah diestimasi
- [x] Tidak ada blocker atau dependensi yang belum terselesaikan
- [x] Mockup/wireframe tersedia (jika diperlukan)
- [x] Technical approach sudah didiskusikan

---

## ✅ Definition of Done (DoD)

Sebuah User Story dianggap "Done" jika memenuhi:

- [x] Semua Acceptance Criteria terpenuhi
- [x] Code sudah di-review dan di-merge
- [x] Unit tests ditulis dan passing (coverage minimal 80%)
- [x] Integration tests passing
- [x] No critical/high bugs
- [x] Dokumentasi diperbarui
- [x] Performance acceptable (< 3s load time)
- [x] Responsive design (mobile-friendly)
- [x] Accessibility standards terpenuhi (WCAG 2.1 AA)

---

## 🏷️ Story Point Reference

| Points | Effort | Contoh |
|--------|--------|--------|
| 1 | Trivial | Perubahan teks, fix typo |
| 2 | Kecil | Simple UI component, minor bug fix |
| 3 | Sedang | Form dengan validasi, API endpoint sederhana |
| 5 | Medium | Feature lengkap dengan CRUD, integrasi API |
| 8 | Besar | Complex feature dengan banyak state, AI integration |
| 13 | Sangat Besar | Full module dengan multiple components |
| 21 | Epic-level | Pecah menjadi stories lebih kecil |

---

## 🎨 Tech Stack

### Frontend
| Component | Technology |
|-----------|------------|
| Framework | Vue.js 3 + Vite |
| State | Pinia |
| Router | Wayfinder |
| Editor | TipTap (ProseMirror) |
| UI | Tailwind CSS |

### Backend
| Component | Technology |
|-----------|------------|
| Framework | Laravel 11 |
| Auth | Laravel Sanctum |
| Database | MySQL 8.0 |
| Cache | Redis |
| Queue | Laravel Queue + Redis |

### AI Integration
| Provider | Models |
|----------|--------|
| OpenAI | GPT-4, GPT-4o, GPT-3.5-turbo |
| Anthropic | Claude 3 Opus, Sonnet, Haiku |
| Google | Gemini Pro |
| Groq | Llama, Mixtral |
| Local | Ollama, LM Studio |

---

## 🔄 Scrum Workflow

```
Product Backlog → Sprint Planning → Sprint Backlog → Daily Scrum → Sprint Review → Sprint Retrospective
       ↑                                                                              |
       └──────────────────────────────────────────────────────────────────────────────┘
```

### Ceremonies
| Ceremony | Duration | When |
|----------|----------|------|
| Sprint Duration | 2 minggu | - |
| Daily Standup | 15 menit | Setiap hari kerja, 09:00 WIB |
| Sprint Planning | 2-4 jam | Awal sprint |
| Sprint Review | 1-2 jam | Akhir sprint |
| Sprint Retrospective | 1 jam | Setelah review |

---

## 📊 FRD Coverage Status

Berdasarkan Gap Analysis:

| Category | Coverage |
|----------|----------|
| Manuscript Editor | 100% |
| Story Planning | 100% |
| Codex | 100% |
| Snippets | 100% |
| AI/Chat | 100% |
| Prompt Management | 100% |
| AI Connections | 100% |
| Import/Export | 100% |
| Organization | 100% |
| **TOTAL** | **100%** |

---

## ⚠️ Key Risks

| Risk | Level | Mitigation |
|------|-------|------------|
| AI API Cost Overruns | 🔴 Critical | Spending limits, local models |
| Large Doc Performance | 🟠 High | Lazy loading, virtual scroll |
| TipTap Limitations | 🟠 High | Custom extensions, prototype early |
| Scope Creep | 🟠 High | Strict prioritization, MVP focus |
| Collaboration Complexity | 🟠 High | Phase rollout, start with basic features |

Lihat detail di [12-risk-management.md](./12-risk-management.md)

---

## 📞 Referensi

- **FRD:** [Functional Requirements for an AI-Assisted Novel Writing App.pdf](../Functional%20Requirements%20for%20an%20AI-Assisted%20Novel%20Writing%20App.pdf)
- **FRD Markdown:** [FRD-AI-Assistant.md](../FRD-AI-Assistant.md)
- **NovelCrafter:** [novelcrafter.com](https://www.novelcrafter.com)
- **NovelCrafter Docs:** [docs.novelcrafter.com](https://docs.novelcrafter.com)

---

## 📁 File Navigation Quick Links

### Epic Files
1. [📝 Manuscript Editor](./01-manuscript-editor.md)
2. [🗺️ Story Planning](./02-story-planning.md)
3. [📖 Codex](./03-codex.md)
4. [📝 Snippets](./04-snippets.md)
5. [💬 AI/Chat](./05-ai-chat.md)
6. [🎯 Prompt Management](./06-prompt-management.md)
7. [🔌 AI Connections](./07-ai-connections.md)
8. [📤 Import/Export](./08-import-export.md)
9. [⚙️ Organization](./09-organization.md)

### Supporting Documents
- [🗓️ Sprint Roadmap](./10-sprint-roadmap.md)
- [🏗️ Technical Architecture](./11-technical-architecture.md)
- [⚠️ Risk Management](./12-risk-management.md)
- [📚 Glossary](./13-glossary.md)
- [📊 Gap Analysis](./14-gap-analysis.md)

---

*Dokumen ini di-generate berdasarkan "Functional Requirements for an AI-Assisted Novel Writing App.pdf" dengan cross-reference ke NovelCrafter documentation.*

*Last Updated: 31 Desember 2024*
