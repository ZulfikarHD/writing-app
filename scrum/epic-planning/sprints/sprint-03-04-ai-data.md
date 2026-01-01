# 🚀 Sprint 3-4: AI & Data Infrastructure

**Phase:** 1 - Foundation  
**Duration:** 4 weeks (2 sprints)  
**Total Story Points:** ~110  
**Focus:** AI Connections, Codex Foundation, Workshop Chat

---

## 📋 Phase Overview

This phase establishes the critical AI infrastructure and data foundation that enables all subsequent AI-powered features. **Sprint 3 is the most critical sprint** as it unblocks all future AI features.

---

## 🗓️ Sprint 3: AI Connections & Codex Foundation
**Week 5-6** | **Story Points: ~52**

### Sprint Goals
1. ✅ Establish AI service architecture
2. ✅ Connect to major AI providers (OpenAI, Anthropic, OpenRouter, Ollama)
3. ✅ Build Codex core (entry types, CRUD, aliases)
4. ✅ Create Settings page infrastructure

### Sprint Backlog

#### Week 5 (Days 1-5)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 1 | Create AI Service Interface & Factory pattern | EPIC-01 | 3 | ⬜ |
| 1 | Create `ai_connections` migration | EPIC-01 | 1 | ⬜ |
| 1-2 | Implement OpenAIService with model listing | EPIC-01 | 5 | ⬜ |
| 2 | API key encryption utilities | EPIC-01 | 2 | ⬜ |
| 2-3 | Implement AnthropicService | EPIC-01 | 5 | ⬜ |
| 3 | Create Settings page & AI Connections UI | EPIC-01 | 5 | ⬜ |
| 4 | Implement OpenRouterService | EPIC-01 | 5 | ⬜ |
| 4-5 | Connection test endpoint | EPIC-01 | 2 | ⬜ |
| 5 | Model Selector component | EPIC-01 | 5 | ⬜ |

#### Week 6 (Days 6-10)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 6 | Implement OllamaService | EPIC-01 | 5 | ⬜ |
| 6 | Implement GroqService | EPIC-01 | 3 | ⬜ |
| 7 | Create `codex_entries` migration | EPIC-02 | 2 | ⬜ |
| 7 | CodexEntry model with types | EPIC-02 | 5 | ⬜ |
| 8 | Codex CRUD controller | EPIC-02 | 3 | ⬜ |
| 8 | Codex list page | EPIC-02 | 3 | ⬜ |
| 9 | Codex entry detail view | EPIC-02 | 5 | ⬜ |
| 9 | Codex aliases system | EPIC-02 | 3 | ⬜ |
| 10 | AI Connection tests | EPIC-01 | 3 | ⬜ |
| 10 | Codex tests | EPIC-02 | 2 | ⬜ |

### Deliverables
- [ ] AI Service abstraction layer (interface + factory)
- [ ] OpenAI, Anthropic, OpenRouter, Ollama, Groq connections
- [ ] Settings page with AI Connections management
- [ ] Model Selector component
- [ ] API key encrypted storage
- [ ] Connection test functionality
- [ ] Codex entry types (Character, Location, Item, Lore, Organization, Subplot)
- [ ] Codex CRUD operations
- [ ] Codex entry detail view
- [ ] Aliases system for Codex

### Definition of Done
- [ ] All providers can be connected and tested
- [ ] Model selector shows available models from connected providers
- [ ] Codex entries can be created with all types
- [ ] Aliases searchable and functional
- [ ] 80%+ test coverage on new code

---

## 🗓️ Sprint 4: Codex Complete & Chat Start
**Week 7-8** | **Story Points: ~58**

### Sprint Goals
1. ✅ Complete Codex features (details, relations, images)
2. ✅ Build Workshop Chat interface
3. ✅ Add additional AI connections
4. ✅ Start context injection

### Sprint Backlog

#### Week 7 (Days 1-5)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 1 | Codex Details (attributes) system | EPIC-02 | 5 | ⬜ |
| 1-2 | Codex Details Quick Create | EPIC-02 | 3 | ⬜ |
| 2 | Codex thumbnail upload | EPIC-02 | 3 | ⬜ |
| 2-3 | Codex Relations system | EPIC-02 | 8 | ⬜ |
| 3-4 | Codex Categories | EPIC-02 | 5 | ⬜ |
| 4 | AI Context Controls | EPIC-02 | 5 | ⬜ |
| 5 | Codex tags & search | EPIC-02 | 3 | ⬜ |

#### Week 8 (Days 6-10)

| Day | Task | Epic | Points | Status |
|-----|------|------|--------|--------|
| 6 | Chat threads table & model | EPIC-04 | 3 | ⬜ |
| 6-7 | Basic Chat Interface | EPIC-04 | 8 | ⬜ |
| 7 | Message streaming implementation | EPIC-04 | 5 | ⬜ |
| 8 | Chat threads management | EPIC-04 | 5 | ⬜ |
| 8 | Model selection in chat | EPIC-04 | 3 | ⬜ |
| 9 | Context injection foundation | EPIC-04 | 5 | ⬜ |
| 9 | LM Studio connection | EPIC-01 | 3 | ⬜ |
| 10 | OpenAI-Compatible endpoints | EPIC-01 | 3 | ⬜ |
| 10 | Integration tests | - | 3 | ⬜ |

### Deliverables
- [ ] Codex entry details (key-value attributes)
- [ ] Codex relations mapping
- [ ] Codex thumbnail images
- [ ] Codex categories for organization
- [ ] AI context controls (always/detected/manual/never)
- [ ] Workshop Chat interface with streaming
- [ ] Chat threads management
- [ ] Model selection per chat
- [ ] Basic context injection
- [ ] LM Studio & OpenAI-compatible connections

### Definition of Done
- [ ] Codex fully functional with all features
- [ ] Relations visible on both entries
- [ ] Chat interface sends/receives messages
- [ ] Streaming responses working
- [ ] Context from novel included in chat
- [ ] All tests passing

---

## 📊 Sprint Velocity Tracking

| Sprint | Planned | Completed | Variance |
|--------|---------|-----------|----------|
| Sprint 3 | 52 | - | - |
| Sprint 4 | 58 | - | - |

---

## ⚠️ Risks & Dependencies

### Critical Dependencies
- **Sprint 3 AI Connections** blocks all AI features in later sprints
- **Codex** required for Matrix View, Mentions, AI Context
- **Chat foundation** required for Prompts execution

### Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| AI provider API changes | Low | High | Abstract via interface |
| Streaming complexity | Medium | Medium | Start with non-streaming fallback |
| Codex data model changes | Low | Medium | Plan schema carefully upfront |
| API key security concerns | Medium | Critical | Security audit, encryption |

---

## 📝 Notes for Implementation

### AI Service Architecture
```
app/Services/AI/
├── Contracts/
│   └── AIServiceInterface.php  # Common interface
├── AIServiceFactory.php        # Factory for creating service instances
├── BaseAIService.php           # Shared functionality
├── OpenAIService.php
├── AnthropicService.php
├── OpenRouterService.php
├── OllamaService.php
├── GroqService.php
├── LMStudioService.php
└── OpenAICompatibleService.php
```

### Key Patterns
1. **Factory Pattern** - `AIServiceFactory::make($provider)` returns correct service
2. **Interface Segregation** - All services implement `AIServiceInterface`
3. **Encryption** - Use `Crypt::encryptString()` for API keys
4. **Streaming** - Use Server-Sent Events for real-time responses

---

## 🔗 Related Documents

- [EPIC-01: AI Connections](../01-EPIC-ai-connections.md)
- [EPIC-02: Codex System](../02-EPIC-codex.md)
- [EPIC-04: Workshop Chat](../04-EPIC-workshop-chat.md)
