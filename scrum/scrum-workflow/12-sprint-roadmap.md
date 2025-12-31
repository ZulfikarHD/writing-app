# 🗓️ Sprint Roadmap & Calendar

**Durasi Sprint:** 2 Minggu  
**Start Date:** TBD  
**Total Sprints:** 9 + Backlog  
**Total Story Points:** ~516  
**Total Durasi:** ~18 Minggu (~4.5 Bulan)

---

## 📊 Sprint Overview Timeline

```
Sprint  1 ━━━━━━━━━━━━━━━━ Foundation & Core Editor (36 pts)
Sprint  2 ━━━━━━━━━━━━━━━━ Editor Features & Planning Start (34 pts)
Sprint  3 ━━━━━━━━━━━━━━━━ AI Connections & Codex Foundation (52 pts)
Sprint  4 ━━━━━━━━━━━━━━━━ Codex Complete & Chat Start (67 pts)
Sprint  5 ━━━━━━━━━━━━━━━━ AI Features, Prompts & Snippets (57 pts)
Sprint  6 ━━━━━━━━━━━━━━━━ Import/Export & Advanced Prompts (63 pts)
Sprint  7 ━━━━━━━━━━━━━━━━ Export, Planning Analytics & Onboarding (75 pts)
Sprint  8 ━━━━━━━━━━━━━━━━ Collaboration, Archive & System (52 pts)
Sprint  9 ━━━━━━━━━━━━━━━━ Teams, Polish & Final Features (55 pts)
Backlog  ━━━━━━━━━━━━━━━━━ Future Enhancements (25 pts)
```

---

## 📅 Sprint 1: Foundation & Core Editor
**Minggu 1-2** | **Total Points: 36**

### Goals
- ✅ User authentication & account setup (ENTRY POINT)
- ✅ Dashboard & project management
- ✅ Core manuscript editor functional
- ✅ Scene/Chapter structure

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-072 | User Account & Profile | 5 | Foundation | 🔴 Tinggi |
| US-065 | Dashboard & Project List | 5 | Foundation | 🔴 Tinggi |
| US-066 | Novel Creation & Setup | 5 | Foundation | 🔴 Tinggi |
| US-001 | Rich Text Editor Dasar | 8 | Editor | 🔴 Tinggi |
| US-002 | Scene & Chapter Structure | 8 | Editor | 🔴 Tinggi |
| US-009 | Theme & Display Options | 5 | Editor | 🟢 Rendah |

**Total Points:** 36

### Deliverables
- [ ] Project scaffolding (Laravel + Vue + Inertia)
- [ ] Database schema setup (users, novels, chapters, scenes)
- [ ] User registration & login (Laravel Sanctum)
- [ ] Dashboard with novel list (grid/list view)
- [ ] Novel creation wizard
- [ ] Basic TipTap editor with formatting
- [ ] Scene/Chapter sidebar with hierarchy
- [ ] Auto-save functionality (30s debounce)
- [ ] Dark/Light mode toggle

### Dependencies
- None (this is the foundation)

### Sprint Ceremonies
- **Planning:** Day 1
- **Daily Standup:** Setiap hari kerja, 09:00 WIB
- **Review:** Day 10
- **Retrospective:** Day 10

---

## 📅 Sprint 2: Editor Features & Planning Start
**Minggu 3-4** | **Total Points: 34**

### Goals
- ✅ Complete editor metadata & actions
- ✅ Start story planning interface
- ✅ Hierarchical structure (Acts/Chapters/Scenes)

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-003 | Scene Metadata Panel | 5 | Editor | 🟡 Sedang |
| US-004 | Action Menu Scene/Chapter | 5 | Editor | 🟡 Sedang |
| US-010 | Hierarchical Story Structure | 8 | Planning | 🔴 Tinggi |
| US-011 | Grid View | 5 | Planning | 🔴 Tinggi |
| US-017 | Search & Filter | 3 | Planning | 🔴 Tinggi |
| US-015 | Scene Subtitles & Notes | 2 | Planning | 🟢 Rendah |
| US-014 | Scene Labels & Status | 5 | Planning | 🟡 Sedang |

**Total Points:** 33

### Deliverables
- [ ] Scene metadata panel (POV, word count, notes)
- [ ] Context menu for scene/chapter actions
- [ ] Acts/Chapters/Scenes hierarchy
- [ ] Drag & drop reordering
- [ ] Grid view for plan interface
- [ ] Scene cards with status labels
- [ ] Search across scenes
- [ ] Filter by label/status

### Dependencies
- Sprint 1 (Foundation & Editor base)

---

## 📅 Sprint 3: AI Connections & Codex Foundation
**Minggu 5-6** | **Total Points: 52**

### Goals
- ✅ Connect to major AI providers (CRITICAL DEPENDENCY)
- ✅ Basic Codex functionality
- ✅ Model selection UI

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-050 | OpenAI Connection | 5 | AI Connections | 🔴 Tinggi |
| US-051 | Anthropic/Claude Connection | 5 | AI Connections | 🔴 Tinggi |
| US-054 | Ollama Connection (Local) | 5 | AI Connections | 🔴 Tinggi |
| US-056 | Model Selection UI | 3 | AI Connections | 🔴 Tinggi |
| US-019 | Codex Entry Types | 5 | Codex | 🔴 Tinggi |
| US-020 | Create & Edit Codex Entry | 8 | Codex | 🔴 Tinggi |
| US-021 | Codex Entry Detail View | 5 | Codex | 🔴 Tinggi |
| US-024 | Aliases & Nicknames | 3 | Codex | 🔴 Tinggi |
| US-012 | Matrix View | 5 | Planning | 🟡 Sedang |
| US-013 | Outline View | 3 | Planning | 🟡 Sedang |

**Total Points:** 47

### Deliverables
- [ ] API key management UI
- [ ] OpenAI integration (GPT-4, GPT-4o)
- [ ] Anthropic integration (Claude 3)
- [ ] Ollama local LLM support
- [ ] Model selection dropdown
- [ ] Secure encrypted key storage
- [ ] Codex entry types (Character, Location, Item, Lore, Organization, Subplot)
- [ ] Codex CRUD operations
- [ ] Entry detail view with tabs
- [ ] Alias system for AI context
- [ ] Matrix view (scenes × characters/locations)
- [ ] Outline view (linear list)

### Dependencies
- Sprint 1-2 (Foundation complete)
- **This sprint enables all AI features in later sprints**

---

## 📅 Sprint 4: Codex Complete & Chat Start
**Minggu 7-8** | **Total Points: 67**

### Goals
- ✅ Complete Codex features (tags, images, relations)
- ✅ Start AI Chat interface
- ✅ Additional AI connections

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-022 | Tags & Labels untuk Codex | 3 | Codex | 🟡 Sedang |
| US-023 | Thumbnail & Images | 5 | Codex | 🟡 Sedang |
| US-027 | Relations (Nested Entries) | 8 | Codex | 🟡 Sedang |
| US-025 | Custom Detail Fields | 5 | Codex | 🟡 Sedang |
| US-035 | Basic Chat Interface | 8 | AI Chat | 🔴 Tinggi |
| US-036 | Chat Threads Management | 5 | AI Chat | 🔴 Tinggi |
| US-039 | Model Selection in Chat | 3 | AI Chat | 🔴 Tinggi |
| US-052 | Google Gemini Connection | 5 | AI Connections | 🟡 Sedang |
| US-053 | OpenAI-Compatible Endpoints | 5 | AI Connections | 🟡 Sedang |
| US-055 | LM Studio Connection | 3 | AI Connections | 🟡 Sedang |
| US-098 | Groq Connection | 3 | AI Connections | 🟡 Sedang |
| US-057 | Usage & Cost Tracking | 3 | AI Connections | 🟢 Rendah |
| US-016 | Outline Import | 5 | Planning | 🟡 Sedang |
| US-018 | Timeline Overview | 4 | Planning | 🟡 Sedang |

**Total Points:** 65

### Deliverables
- [ ] Codex tags & color coding
- [ ] Image upload for entries (thumbnails)
- [ ] Relations mapping between entries
- [ ] Custom detail fields (key-value)
- [ ] Basic chat window with history
- [ ] Chat threads (create, rename, delete)
- [ ] Model selector per chat
- [ ] Message streaming
- [ ] Google Gemini support
- [ ] Groq fast inference support
- [ ] OpenAI-compatible endpoints (xAI, etc.)
- [ ] LM Studio support
- [ ] Usage tracking dashboard
- [ ] Outline import from text
- [ ] Timeline visualization

### Dependencies
- Sprint 3 (AI Connections foundation)

---

## 📅 Sprint 5: AI Features, Prompts & Snippets
**Minggu 9-10** | **Total Points: 57**

### Goals
- ✅ Integrated AI tools in editor
- ✅ Context injection for AI
- ✅ Prompt library foundation
- ✅ Snippets system
- ✅ Codex mentions & AI controls

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-005 | Integrated AI Tools di Editor | 8 | Editor | 🔴 Tinggi |
| US-037 | Context Injection | 8 | AI Chat | 🔴 Tinggi |
| US-040 | Chat with Scene/Document | 5 | AI Chat | 🔴 Tinggi |
| US-038 | Pin Chat Panel | 3 | AI Chat | 🟡 Sedang |
| US-043 | Prompt Library Interface | 5 | Prompts | 🔴 Tinggi |
| US-047 | Built-in Prompts | 5 | Prompts | 🔴 Tinggi |
| US-046 | Prompt Categories | 3 | Prompts | 🟡 Sedang |
| US-032 | Create & Manage Snippets | 5 | Snippets | 🔴 Tinggi |
| US-033 | Pin & Quick Access | 3 | Snippets | 🟡 Sedang |
| US-034 | Extract to Codex/Scene | 5 | Snippets | 🟡 Sedang |
| US-028 | Mentions Tracking | 5 | Codex | 🔴 Tinggi |
| US-030 | AI Context Controls | 5 | Codex | 🔴 Tinggi |

**Total Points:** 60

### Deliverables
- [ ] AI summarize scene
- [ ] AI detect characters
- [ ] "Chat with this scene" button
- [ ] Context from novel/scene/Codex
- [ ] Context preview & token count
- [ ] Pinned chat beside editor
- [ ] Prompt library UI
- [ ] Built-in prompts (Expand, Rephrase, Summarize, etc.)
- [ ] Prompt categories
- [ ] Snippets CRUD
- [ ] Pin snippets for quick access
- [ ] Extract snippet to Codex/Scene
- [ ] Mentions tracking (where entry appears)
- [ ] AI context controls (always/detected/exclude/never)

### Dependencies
- Sprint 3-4 (AI Connections & Codex)

---

## 📅 Sprint 6: Import/Export & Advanced Prompts
**Minggu 11-12** | **Total Points: 63**

### Goals
- ✅ Import from Word/Markdown
- ✅ Slash commands & AI generation
- ✅ Advanced prompt features (editor, variables, personas)
- ✅ Complete Codex features

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-006 | Slash Command Generation | 8 | Editor | 🔴 Tinggi |
| US-007 | Text Replacement Prompts | 5 | Editor | 🟡 Sedang |
| US-076 | Collapsible Sections in Editor | 8 | Editor | 🔴 Tinggi |
| US-078 | Enhanced Scene Beats Workflow | 5 | Editor | 🔴 Tinggi |
| US-058 | Import dari Word (.docx) | 8 | Import/Export | 🔴 Tinggi |
| US-059 | Import dari Markdown | 5 | Import/Export | 🟡 Sedang |
| US-044 | Prompt Editor | 8 | Prompts | 🔴 Tinggi |
| US-045 | Prompt Variables | 5 | Prompts | 🔴 Tinggi |
| US-093 | Prompt Personas | 8 | Prompts | 🔴 Tinggi |
| US-041 | Brainstorming Tools | 5 | AI Chat | 🟡 Sedang |
| US-042 | Extract from Chat | 5 | AI Chat | 🟡 Sedang |
| US-108 | Extract Feature (Structured Data) | 5 | AI Chat | 🟡 Sedang |
| US-100 | Subplots Management | 5 | Codex | 🟡 Sedang |
| US-101 | Codex Categories | 3 | Codex | 🟡 Sedang |

**Total Points:** 83

### Deliverables
- [ ] "/" command menu in editor
- [ ] Scene Beat, Continue, Dialogue commands
- [ ] Text transformation prompts (selection toolbar)
- [ ] AI-generated collapsible sections
- [ ] Scene beats workflow (simple/detailed modes)
- [ ] Word (.docx) import with structure parsing
- [ ] Markdown import
- [ ] Full prompt editor with system/user messages
- [ ] Variable support ({{variable}})
- [ ] Prompt Personas (AI memory across projects)
- [ ] Brainstorming quick prompts
- [ ] Extract AI response to novel/Codex
- [ ] Structured data extraction
- [ ] Subplots as Codex entry type
- [ ] Codex categories for organization

### Dependencies
- Sprint 5 (AI features in editor, Prompt library)

---

## 📅 Sprint 7: Export, Planning Analytics & Onboarding
**Minggu 13-14** | **Total Points: 75**

### Goals
- ✅ Complete export features
- ✅ Planning analytics (heatmap, character analysis)
- ✅ Onboarding experience for new users
- ✅ Advanced prompt features
- ✅ Additional AI connection options

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-060 | Export ke Word (.docx) | 5 | Import/Export | 🔴 Tinggi |
| US-061 | Export ke Markdown | 3 | Import/Export | 🟡 Sedang |
| US-062 | Export Metadata & Supplements | 3 | Import/Export | 🟡 Sedang |
| US-105 | Export to Atticus | 3 | Import/Export | 🟡 Sedang |
| US-081 | Appearance Heatmap | 5 | Planning | 🟡 Sedang |
| US-083 | Characters per Scene Analysis | 3 | Planning | 🟡 Sedang |
| US-088 | Manual References in Plan | 3 | Planning | 🟢 Rendah |
| US-107 | Scene Card Appearance Customization | 2 | Planning | 🟢 Rendah |
| US-111 | Getting Started / Onboarding Experience | 8 | Foundation | 🔴 Tinggi |
| US-080 | Novel Templates | 5 | Foundation | 🟡 Sedang |
| US-079 | Novel Covers | 3 | Foundation | 🟡 Sedang |
| US-067 | Pen Names Management | 3 | Foundation | 🟢 Rendah |
| US-048 | Prompt Preview & Test | 5 | Prompts | 🟡 Sedang |
| US-049 | Model Parameters per Prompt | 3 | Prompts | 🟡 Sedang |
| US-094 | Prompt Presets | 5 | Prompts | 🔴 Tinggi |
| US-095 | Prompt Components | 5 | Prompts | 🔴 Tinggi |
| US-086 | OpenRouter OAuth Integration | 3 | AI Connections | 🟡 Sedang |
| US-096 | Model Collections | 3 | AI Connections | 🟡 Sedang |
| US-084 | Marker/Highlighter Tool | 5 | Editor | 🟡 Sedang |

**Total Points:** 75

### Deliverables
- [ ] Export to Word with formatting options
- [ ] Export to Markdown (single/multiple files)
- [ ] Export Codex, chats, snippets (ZIP)
- [ ] Atticus-compatible export preset
- [ ] Character appearance heatmap
- [ ] Characters per scene analysis
- [ ] Manual references in plan view
- [ ] Scene card customization
- [ ] Welcome screen & guided wizard
- [ ] Interactive app tour
- [ ] First writing session guide
- [ ] AI setup walkthrough
- [ ] Novel templates (3-Act, Hero's Journey, etc.)
- [ ] Novel cover upload
- [ ] Pen names management
- [ ] Prompt preview & testing
- [ ] Model parameters (temperature, etc.)
- [ ] Prompt presets
- [ ] Prompt components (reusable)
- [ ] OpenRouter OAuth
- [ ] Model collections (favorites)
- [ ] Highlighter tool in editor

### Dependencies
- Sprint 6 (Import/Export, Prompts)

---

## 📅 Sprint 8: Collaboration, Archive & System
**Minggu 15-16** | **Total Points: 52**

### Goals
- ✅ Collaboration & coauthoring (MAJOR FEATURE)
- ✅ Data safety (archive, revision history)
- ✅ System productivity features
- ✅ Complete Codex progression features

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-091 | Collaboration & Coauthoring | 13 | Collaboration | 🔴 Tinggi |
| US-063 | Archive Management | 5 | Import/Export | 🟡 Sedang |
| US-064 | Revision History | 8 | Import/Export | 🔴 Tinggi |
| US-087 | HTML Import | 3 | Import/Export | 🟢 Rendah |
| US-070 | Global Search | 5 | System | 🔴 Tinggi |
| US-077 | Auto-Recovery & Data Safety | 5 | System | 🔴 Tinggi |
| US-068 | Theme & Appearance Settings | 5 | System | 🟡 Sedang |
| US-069 | Collapsible UI Panels | 3 | System | 🟡 Sedang |
| US-082 | Word Statistics Dashboard | 5 | System | 🟡 Sedang |
| US-075 | Codex Progression History | 5 | Codex | 🔴 Tinggi |
| US-029 | Mention Highlighting in Editor | 5 | Codex | 🔴 Tinggi |
| US-102 | Codex Details Quick Create | 3 | Codex | 🟡 Sedang |
| US-110 | Progressions on Codex Details | 3 | Codex | 🟡 Sedang |

**Total Points:** 68

### Deliverables
- [ ] Invite collaborators via email
- [ ] Role-based access (Viewer/Editor)
- [ ] Shared project management
- [ ] Activity log for collaborative edits
- [ ] Scene archiving (soft delete)
- [ ] Archive browser & restore
- [ ] Version history UI
- [ ] Compare versions (diff view)
- [ ] HTML import
- [ ] Global search (Ctrl+K)
- [ ] Search across all content types
- [ ] Auto-save to local storage
- [ ] Offline sync queue
- [ ] Recovery notification
- [ ] Full theme customization
- [ ] Collapsible/resizable panels
- [ ] Word statistics & progress tracking
- [ ] Writing streaks & goals
- [ ] Codex progression history
- [ ] Mention highlighting in editor
- [ ] Quick create Codex details
- [ ] Progressions on individual details

### Dependencies
- Sprint 7 (Most features complete)

---

## 📅 Sprint 9: Teams, Polish & Final Features
**Minggu 17-18** | **Total Points: 55**

### Goals
- ✅ Teams feature
- ✅ Bug fixes & polish
- ✅ Help & documentation
- ✅ Settings management
- ✅ Final editor features

### User Stories

| ID | Story | Points | Epic | Prioritas |
|----|-------|--------|------|-----------|
| US-092 | Teams Feature | 8 | Collaboration | 🔴 Tinggi |
| US-008 | Focus Mode | 3 | Editor | 🟢 Rendah |
| US-071 | Backup & Restore | 5 | System | 🔴 Tinggi |
| US-073 | API Keys Settings | 3 | System | 🟡 Sedang |
| US-074 | Help & Documentation | 3 | System | 🟢 Rendah |
| US-085 | Settings Export/Import | 3 | System | 🟡 Sedang |
| US-106 | Pinning Feature | 3 | System | 🟡 Sedang |
| US-103 | Sharing Prompts | 3 | Prompts | 🟡 Sedang |
| US-104 | Prompt Submenus | 3 | Prompts | 🟢 Rendah |
| US-026 | Research Notes | 3 | Codex | 🟢 Rendah |
| US-097 | NSFW Model Guidance | 2 | AI Connections | 🟢 Rendah |
| US-099 | Anyscale Endpoints | 3 | AI Connections | 🟢 Rendah |
| - | Bug Fixes & Polish | 8 | - | 🔴 Tinggi |
| - | Performance Optimization | 5 | - | 🔴 Tinggi |

**Total Points:** 55

### Deliverables
- [ ] Team creation & management
- [ ] Invite team members
- [ ] Share with entire team
- [ ] Team dashboard
- [ ] Focus/zen mode (fullscreen)
- [ ] Typewriter mode
- [ ] Full project backup (ZIP/JSON)
- [ ] Restore from backup
- [ ] Auto-backup scheduling
- [ ] API keys settings page
- [ ] In-app help & tooltips
- [ ] Keyboard shortcuts reference
- [ ] Getting started guide
- [ ] Export/import all settings
- [ ] Pin panels (chat, Codex, snippets)
- [ ] Share prompts (copy/export)
- [ ] Prompt submenus/folders
- [ ] Research notes (not sent to AI)
- [ ] NSFW model documentation
- [ ] Anyscale endpoints support
- [ ] Bug fixes from testing
- [ ] Performance optimizations
- [ ] Final QA

### Dependencies
- Sprint 8 (Collaboration complete)

---

## 📦 Backlog (Future Release)
**Total Points: 25**

### User Stories

| ID | Story | Points | Epic | Notes |
|----|-------|--------|------|-------|
| US-031 | Series Support (Shared Codex) | 3 | Codex | Complex feature |
| US-089 | Localization (i18n) | 8 | System | English + Indonesian |
| US-090 | Enhanced Context Help & Tooltips | 3 | System | UX enhancement |
| US-109 | App Layout Customization | 3 | System | Advanced customization |
| - | Mobile App (PWA) | 8 | - | Future expansion |

---

## 📈 Velocity & Capacity Planning

### Assumed Velocity
- **Team Size:** 1 developer (solo project)
- **Base Velocity:** 25-35 story points per sprint
- **Buffer:** 15% untuk unexpected issues
- **Note:** Some sprints are heavier (40+ pts) - may need adjustment during execution

### Sprint Capacity

| Sprint | Planned Points | Cumulative | Notes |
|--------|----------------|------------|-------|
| 1 | 36 | 36 | Foundation - critical |
| 2 | 33 | 69 | Editor completion |
| 3 | 47 | 116 | AI foundation - critical |
| 4 | 65 | 181 | Heavy sprint |
| 5 | 60 | 241 | AI features |
| 6 | 83 | 324 | Heavy sprint - may overflow |
| 7 | 75 | 399 | Heavy sprint - may overflow |
| 8 | 68 | 467 | Collaboration |
| 9 | 55 | 522 | Polish & final |

**Total Planned:** ~516 Story Points  
**Backlog:** ~25 Story Points

### Velocity Adjustment Notes
- Sprint 6 & 7 are heavy - consider moving some 🟢 Rendah items to backlog
- Track actual velocity after Sprint 1-2 and adjust
- Buffer sprints can absorb overflow

---

## 🎯 Release Milestones

### MVP (End of Sprint 4) - ~181 pts
- ✅ User authentication & dashboard
- ✅ Full editor with scenes/chapters
- ✅ AI connections (OpenAI, Claude, Ollama, Gemini, Groq)
- ✅ Basic Codex (entries, tags, relations)
- ✅ Basic Chat with context
- ✅ Story planning views (Grid, Matrix, Outline, Timeline)

**Target:** Minimal viable product untuk internal testing

### Beta (End of Sprint 6) - ~324 pts
- ✅ All MVP features
- ✅ Slash commands & AI generation
- ✅ Collapsible sections & scene beats
- ✅ Import dari Word/Markdown
- ✅ Full prompt library with personas
- ✅ Snippets system
- ✅ Mentions tracking & AI context controls
- ✅ Extract features

**Target:** Feature-complete untuk beta testers

### Release Candidate (End of Sprint 8) - ~467 pts
- ✅ All Beta features
- ✅ Full export functionality
- ✅ Onboarding experience
- ✅ Collaboration & coauthoring
- ✅ Revision history & archive
- ✅ Global search
- ✅ Word statistics
- ✅ Complete Codex with progressions

**Target:** Production-ready, final testing

### GA (End of Sprint 9) - ~522 pts
- ✅ All features complete
- ✅ Teams feature
- ✅ Bug fixes & polish
- ✅ Performance optimized
- ✅ Documentation complete

**Target:** General availability / launch

---

## 📊 Story Points by Epic

| Epic | Total Points | Sprints |
|------|--------------|---------|
| 1. Foundation | 34 | 1, 7 |
| 2. Manuscript Editor | 68 | 1, 2, 5, 6, 7, 9 |
| 3. Story Planning | 53 | 2, 3, 4, 7 |
| 4. Codex | 77 | 3, 4, 5, 6, 8 |
| 5. Snippets | 13 | 5 |
| 6. AI/Chat | 47 | 4, 5, 6 |
| 7. Prompt Management | 58 | 5, 6, 7, 9 |
| 8. AI Connections | 48 | 3, 4, 7, 9 |
| 9. Import/Export | 43 | 6, 7, 8 |
| 10. Collaboration | 21 | 8, 9 |
| 11. System & Productivity | 54 | 8, 9, Backlog |
| **TOTAL** | **516** | |

---

## 📊 Burndown Chart Template

```
Story Points
550 │ ·
    │  ·
500 │   ·
    │    ·  Sprint 1-2
450 │     ·
    │      ·
400 │       ·  Sprint 3-4
    │        ·
350 │         ·
    │          ·  Sprint 5-6
300 │           ·
    │            ·
250 │             ·
    │              ·  Sprint 7
200 │               ·
    │                ·
150 │                 ·  Sprint 8
    │                  ·
100 │                   ·
    │                    ·  Sprint 9
 50 │                     ·
    │                      ·
  0 │                       · Done!
    └──────────────────────────────→ Time
     S1  S2  S3  S4  S5  S6  S7  S8  S9
```

---

## ⚠️ Sprint Buffer & Contingency

Setiap sprint memiliki 15% buffer untuk:
- Bug fixes dari sprint sebelumnya
- Technical debt
- Unexpected complexity
- Sickness/personal time

### Overflow Strategy
Jika sprint overflow, prioritaskan:
1. 🔴 Tinggi stories → **MUST complete**
2. 🟡 Sedang stories → Move ke sprint berikutnya
3. 🟢 Rendah stories → Move ke backlog

### Heavy Sprint Mitigation
Sprints 6 & 7 are particularly heavy. Options:
- Start early on 🟢 Rendah items in previous sprint
- Move some 🟢 Rendah to backlog
- Consider extending sprint duration if needed

---

## 🔗 Critical Dependencies Graph

```
Sprint 1: Foundation + Editor Base
    │
    ├── Sprint 2: Editor Features + Planning
    │       │
    │       └── Sprint 3: AI Connections (CRITICAL) + Codex
    │               │
    │               ├── Sprint 4: Codex + Chat
    │               │       │
    │               │       └── Sprint 5: AI Features + Prompts + Snippets
    │               │               │
    │               │               └── Sprint 6: Import + Advanced Prompts
    │               │                       │
    │               │                       └── Sprint 7: Export + Onboarding
    │               │                               │
    │               │                               └── Sprint 8: Collaboration + System
    │               │                                       │
    │               │                                       └── Sprint 9: Teams + Polish
    │               │
    │               └── (AI Connections enables all AI features)
    │
    └── (Foundation enables everything)
```

---

## 📝 Notes

- Sprint timeline dapat adjust berdasarkan velocity aktual
- Review priorities setiap Sprint Planning
- Update roadmap saat major changes
- Track actual vs planned velocity
- Heavy sprints (6, 7) may need scope adjustment
- Consider hiring help for Sprint 6-7 if available
- **AI Connections (Sprint 3) is critical** - blocks most features
- **Collaboration (Sprint 8) is a growth driver** - prioritize stability

---

*Last Updated: 31 Desember 2024*
*Generated from Epic Files: 01-foundation.md through 11-system-productivity.md*
