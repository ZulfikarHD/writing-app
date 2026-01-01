# 📚 Novel Writing App - Epic Planning Master Index

**Version:** 1.0  
**Last Updated:** January 1, 2026  
**Reference:** [Novelcrafter Documentation](https://www.novelcrafter.com/help/docs)

---

## 🎯 Vision Statement

Build a comprehensive AI-assisted novel writing application that empowers writers to:
- **Plan** their stories with visual tools (Grid, Matrix, Outline views)
- **Build** a rich world database (Codex) with characters, locations, and lore
- **Write** with AI assistance seamlessly integrated into the editor
- **Chat** with AI for brainstorming and idea generation
- **Organize** their work with proper version control and archiving
- **Collaborate** with co-authors and teams

---

## 📊 Program Structure Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         NOVEL WRITING APP                               │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌────────────┐  │
│  │   EPIC 1    │   │   EPIC 2    │   │   EPIC 3    │   │   EPIC 4   │  │
│  │     AI      │   │   CODEX     │   │   STORY     │   │  WORKSHOP  │  │
│  │ CONNECTIONS │   │   SYSTEM    │   │  PLANNING   │   │   (CHAT)   │  │
│  └─────────────┘   └─────────────┘   └─────────────┘   └────────────┘  │
│                                                                         │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌────────────┐  │
│  │   EPIC 5    │   │   EPIC 6    │   │   EPIC 7    │   │   EPIC 8   │  │
│  │  PROMPTS    │   │ MANUSCRIPT  │   │  SNIPPETS   │   │  IMPORT/   │  │
│  │   SYSTEM    │   │   EDITOR    │   │             │   │   EXPORT   │  │
│  └─────────────┘   └─────────────┘   └─────────────┘   └────────────┘  │
│                                                                         │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌────────────┐  │
│  │   EPIC 9    │   │   EPIC 10   │   │   EPIC 11   │   │   EPIC 12  │  │
│  │ORGANIZATION │   │COLLABORATION│   │   SYSTEM    │   │  CODEX V2  │  │
│  │  & SAFETY   │   │             │   │ PRODUCTIVITY│   │ ENHANCEMENTS│ │
│  └─────────────┘   └─────────────┘   └─────────────┘   └────────────┘  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 📁 Epic Index

| Epic | Name | Priority | Dependency | Est. Duration |
|------|------|----------|------------|---------------|
| [EPIC-01](./01-EPIC-ai-connections.md) | AI Connections | 🔴 Critical | Foundation | 2-3 Sprints |
| [EPIC-02](./02-EPIC-codex.md) | Codex System | 🔴 Critical | EPIC-01 | 3-4 Sprints |
| [EPIC-03](./03-EPIC-story-planning.md) | Story Planning | 🔴 Critical | EPIC-02 | 2-3 Sprints |
| [EPIC-04](./04-EPIC-workshop-chat.md) | Workshop (Chat) | 🔴 Critical | EPIC-01 | 2-3 Sprints |
| [EPIC-05](./05-EPIC-prompts-system.md) | Prompts System | 🟡 High | EPIC-01, EPIC-04 | 3-4 Sprints |
| [EPIC-06](./06-EPIC-manuscript-editor.md) | Manuscript Editor (Advanced) | 🟡 High | EPIC-01, EPIC-02 | 2-3 Sprints |
| [EPIC-07](./07-EPIC-snippets.md) | Snippets | 🟡 High | EPIC-04 | 1-2 Sprints |
| [EPIC-08](./08-EPIC-import-export.md) | Import/Export | 🟢 Medium | EPIC-02, EPIC-03 | 2-3 Sprints |
| [EPIC-09](./09-EPIC-organization.md) | Organization & Safety | 🟢 Medium | EPIC-03 | 2-3 Sprints |
| [EPIC-10](./10-EPIC-collaboration.md) | Collaboration | 🟢 Medium | All Core Epics | 2-3 Sprints |
| [EPIC-11](./11-EPIC-system-features.md) | System & Productivity | 🟢 Medium | Foundation | 2-3 Sprints |
| [EPIC-12](./12-EPIC-codex-v2-enhancements.md) | Codex v2 Enhancements | 🔴 Critical | EPIC-02 | 3-4 Sprints |

---

## 🗓️ Sprint Distribution Overview

### Phase 1: Foundation (Sprint 3-4) - AI & Data Infrastructure
- **Sprint 3:** AI Connections Core + Codex Foundation
- **Sprint 4:** Codex Complete + Chat Foundation

### Phase 2: Core Features (Sprint 5-7) - Planning & Writing Tools
- **Sprint 5:** Story Planning Views + Workshop Chat
- **Sprint 6:** Prompts System Foundation
- **Sprint 7:** Advanced Editor Features + Snippets

### Phase 3: Enhancement (Sprint 8-10) - Productivity & Integration
- **Sprint 8:** Import/Export + Organization
- **Sprint 9:** Advanced Prompts + System Features
- **Sprint 10:** Collaboration + Polish

### Phase 4: Refinement (Sprint 11-12) - Quality & Scale
- **Sprint 11:** Collaboration Teams + Performance
- **Sprint 12:** Final Polish + Documentation

---

## 📈 Dependency Graph

```
                    ┌──────────────────┐
                    │   Foundation     │
                    │  (Sprint 1-2)    │
                    └────────┬─────────┘
                             │
              ┌──────────────┴──────────────┐
              │                             │
              ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │   EPIC 1: AI    │           │   EPIC 11:      │
    │  Connections    │           │    System       │
    │   (Critical)    │           │   Features      │
    └────────┬────────┘           └─────────────────┘
             │
    ┌────────┴────────┬──────────────┬──────────────┐
    │                 │              │              │
    ▼                 ▼              ▼              ▼
┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
│ EPIC 2  │    │ EPIC 4  │    │ EPIC 5  │    │ EPIC 6  │
│ Codex   │    │Workshop │    │Prompts  │    │Editor   │
│ System  │    │ (Chat)  │    │ System  │    │Advanced │
└────┬────┘    └────┬────┘    └────┬────┘    └─────────┘
     │              │              │
     │              │              ▼
     │              │         ┌─────────┐
     │              │         │ EPIC 7  │
     │              └────────▶│Snippets │
     │                        └─────────┘
     │
     ├──────────────┬──────────────┬──────────────┐
     │              │              │              │
     ▼              ▼              ▼              ▼
┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐
│ EPIC 3  │   │ EPIC 8  │   │ EPIC 9  │   │ EPIC 12 │
│ Story   │   │Import/  │   │Organiz- │   │Codex v2 │
│Planning │   │ Export  │   │ation    │   │Enhance  │
└─────────┘   └─────────┘   └─────────┘   └─────────┘
                   │
                   ▼
            ┌─────────────┐
            │  EPIC 10:   │
            │Collaboration│
            └─────────────┘
```

---

## 🎯 Release Milestones

### MVP Alpha (End of Sprint 5) - ~200 Story Points
**Target:** Internal testing with core features
- ✅ AI Connections (OpenAI, Anthropic, Ollama)
- ✅ Basic Codex (entries, types, aliases)
- ✅ Story Planning (Grid, Matrix, Outline views)
- ✅ Basic Workshop Chat

### MVP Beta (End of Sprint 7) - ~350 Story Points
**Target:** Beta testers with feature completeness
- ✅ All Alpha features
- ✅ Complete Codex (relations, progressions, categories)
- ✅ Full Prompts System (library, presets, personas)
- ✅ Snippets System
- ✅ Advanced Editor (sections, text replacement)

### Release Candidate (End of Sprint 10) - ~480 Story Points
**Target:** Production-ready for early adopters
- ✅ All Beta features
- ✅ Import/Export (Word, Markdown, Scrivener, Atticus)
- ✅ Organization (archiving, revision history)
- ✅ Basic Collaboration

### General Availability (End of Sprint 12) - ~550 Story Points
**Target:** Full public release
- ✅ All RC features
- ✅ Teams & Advanced Collaboration
- ✅ Performance Optimization
- ✅ Complete Documentation

---

## 📊 Story Points Summary by Epic

| Epic | Total Points | High Priority | Medium Priority | Low Priority |
|------|--------------|---------------|-----------------|--------------|
| EPIC-01: AI Connections | ~55 | 35 | 15 | 5 |
| EPIC-02: Codex System | ~95 | 50 | 35 | 10 |
| EPIC-03: Story Planning | ~60 | 30 | 20 | 10 |
| EPIC-04: Workshop Chat | ~50 | 30 | 15 | 5 |
| EPIC-05: Prompts System | ~80 | 45 | 25 | 10 |
| EPIC-06: Manuscript Editor | ~55 | 30 | 20 | 5 |
| EPIC-07: Snippets | ~25 | 15 | 8 | 2 |
| EPIC-08: Import/Export | ~50 | 25 | 20 | 5 |
| EPIC-09: Organization | ~45 | 25 | 15 | 5 |
| EPIC-10: Collaboration | ~50 | 30 | 15 | 5 |
| EPIC-11: System Features | ~35 | 15 | 15 | 5 |
| EPIC-12: Codex v2 Enhancements | ~110 | 70 | 30 | 10 |
| **TOTAL** | **~710** | **400** | **233** | **77** |

---

## 📝 Quick Links

### Epic Documents
- [EPIC-01: AI Connections](./01-EPIC-ai-connections.md)
- [EPIC-02: Codex System](./02-EPIC-codex.md)
- [EPIC-03: Story Planning](./03-EPIC-story-planning.md)
- [EPIC-04: Workshop (Chat)](./04-EPIC-workshop-chat.md)
- [EPIC-05: Prompts System](./05-EPIC-prompts-system.md)
- [EPIC-06: Manuscript Editor](./06-EPIC-manuscript-editor.md)
- [EPIC-07: Snippets](./07-EPIC-snippets.md)
- [EPIC-08: Import/Export](./08-EPIC-import-export.md)
- [EPIC-09: Organization & Safety](./09-EPIC-organization.md)
- [EPIC-10: Collaboration](./10-EPIC-collaboration.md)
- [EPIC-11: System & Productivity](./11-EPIC-system-features.md)
- [EPIC-12: Codex v2 Enhancements](./12-EPIC-codex-v2-enhancements.md)

### Sprint Plans
- [Sprint 3-4: AI & Data Infrastructure](./sprints/sprint-03-04-ai-data.md)
- [Sprint 5-7: Core Features](./sprints/sprint-05-07-core-features.md)
- [Sprint 8-10: Enhancement](./sprints/sprint-08-10-enhancement.md)
- [Sprint 11-12: Refinement](./sprints/sprint-11-12-refinement.md)

### Reference
- [Novelcrafter Documentation](https://www.novelcrafter.com/help/docs)
- [Technical Architecture](../scrum-workflow/13-technical-architecture.md)
- [Risk Management](../scrum-workflow/14-risk-management.md)

---

*This document serves as the master index for all epic planning. Each epic document contains detailed feature breakdowns, user stories, and acceptance criteria.*
