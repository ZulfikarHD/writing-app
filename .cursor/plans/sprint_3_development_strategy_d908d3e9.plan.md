---
name: Sprint 3 Development Strategy
overview: Sprint 3 (Week 5-6) focuses on establishing AI Connections infrastructure and Codex Foundation, which are critical dependencies for all subsequent AI features. This sprint enables the core AI-assisted writing capabilities and world-building database.
todos:
  - id: ai-service-layer
    content: Create AI service abstraction layer (AIServiceInterface, Factory pattern) - enables all AI features
    status: pending
  - id: ai-connections-backend
    content: Build AIConnection model, migration, controller, and encrypted key storage
    status: pending
  - id: openai-service
    content: Implement OpenAIService with model listing and API testing (US-050)
    status: pending
  - id: anthropic-service
    content: Implement AnthropicService for Claude models (US-051)
    status: pending
  - id: ollama-service
    content: Implement OllamaService for local LLM support (US-054)
    status: pending
  - id: settings-page
    content: Create Settings page with AI Connections management UI
    status: pending
  - id: model-selector
    content: Build reusable ModelSelector component for model selection (US-056)
    status: pending
  - id: codex-backend
    content: Build CodexEntry model, migration, controller with CRUD operations (US-019, US-020)
    status: pending
  - id: codex-frontend
    content: Create Codex list page and entry detail view (US-021)
    status: pending
  - id: codex-aliases
    content: Implement aliases system for Codex entries (US-024)
    status: pending
  - id: matrix-view
    content: Build Matrix View component for scenes vs Codex entries (US-012)
    status: pending
  - id: outline-view
    content: Build Outline View component for linear scene display (US-013)
    status: pending
  - id: navigation-updates
    content: Add Settings link to user menu, Codex tab to Editor sidebar
    status: pending
  - id: tests-ai
    content: Write tests for AI services and connections
    status: pending
  - id: tests-codex
    content: Write tests for Codex CRUD operations
    status: pending
---

# Sprint 3: AI Connections and Codex Foundation Development Strategy

## Phase 1: Feature Understanding

### Sprint 3 Goals

- Connect to major AI providers (OpenAI, Anthropic/Claude, Ollama) - **CRITICAL DEPENDENCY**
- Basic Codex functionality (world database)
- Model selection UI
- Matrix and Outline views for planning

### Total Story Points: 47

- AI Connections: 18 pts (US-050, US-051, US-054, US-056)
- Codex Foundation: 21 pts (US-019, US-020, US-021, US-024)
- Planning Views: 8 pts (US-012, US-013)

---

## Phase 2: Cross-Frontend Impact Mapping

| Feature Name | Owner (Who Creates) | Consumer (Who Views) | Data Flow |

|--------------|---------------------|---------------------|-----------|

| AI Connections | User in Settings | Editor, Chat, All AI Features | Settings -> Encrypted Storage -> AI Services |

| Model Selection | User in Settings/Chat | Editor, Chat, Prompts | User selects -> Stored preference -> Used in AI calls |

| Codex Entry Types | System (Predefined) | User in Codex List | Enum -> Displayed as filter/icons |

| Codex CRUD | User in Codex Panel | Editor (mentions), Chat (context), Plan (Matrix) | Create -> Store -> Display/Link |

| Codex Aliases | User in Entry Detail | Editor (highlighting), AI (context) | Create alias -> Index -> Highlight/Include |

| Matrix View | System generates | User in Plan | Scene + Codex data -> Matrix visualization |

| Outline View | System generates | User in Plan | Scenes -> Linear list view |

---

## Phase 3: Missing Implementation Detection

### Owner Side (Data Creation)

**AI Connections:**

- [x] UI form for API key input - Settings page needed
- [x] Validation rules - API key format validation
- [x] Edit/Update capability - Update API keys
- [x] Delete capability - Remove connection
- [x] Test connection button - Verify API works
- [ ] **MISSING: Settings page/section for AI connections**
- [ ] **MISSING: Navigation to AI settings**

**Codex:**

- [x] UI form for creating entries - Codex page needed
- [x] Validation rules - Name required, type selection
- [x] Edit/Update capability - Entry editing
- [x] Delete capability - Soft delete
- [ ] **MISSING: Codex page component**
- [ ] **MISSING: Codex entry detail component**
- [ ] **MISSING: Navigation to Codex from Editor/Plan**

### Consumer Side (Data Display)

**AI Connections:**

- [ ] **MISSING: Connected providers indicator in UI**
- [ ] **MISSING: Model dropdown in Editor action menu**
- [ ] **MISSING: Model dropdown in Chat (future sprint)**
- [x] Empty state when no providers connected
- [x] Loading state during connection test

**Codex:**

- [ ] **MISSING: Codex entries list in sidebar/panel**
- [ ] **MISSING: Entry quick preview on hover**
- [ ] **MISSING: Codex link in Editor navigation**
- [ ] **MISSING: Matrix View uses Codex data**
- [x] Empty state for no entries
- [x] Loading states

### Integration Points

**New Database Tables Needed:**

- `ai_connections` - Store provider configs + encrypted keys
- `codex_entries` - Main codex entries table
- `codex_entry_aliases` - Aliases for entries

**New API Endpoints:**

- `GET/POST /api/ai-connections` - List/create connections
- `DELETE /api/ai-connections/{id}` - Remove connection
- `POST /api/ai-connections/{id}/test` - Test connection
- `GET /api/novels/{novel}/codex` - List entries
- `POST /api/novels/{novel}/codex` - Create entry
- `GET/PATCH/DELETE /api/codex/{id}` - Entry CRUD

**Navigation Updates:**

- Add "Codex" link in Editor sidebar
- Add "Settings" / "AI Connections" in user menu
- Add Matrix/Outline view tabs in Plan interface

---

## Phase 4: Gap Analysis

### Critical Gaps Identified

1. **Settings Infrastructure Missing**

   - No Settings page exists
   - No AI Connections management UI
   - Need to add Settings route and page

2. **Codex Infrastructure Missing**

   - No Codex models, controllers, or views
   - No Codex navigation entry point
   - Need complete Codex module creation

3. **AI Service Layer Missing**

   - No AI service abstraction
   - No provider-specific implementations
   - Need service factory pattern for multi-provider support

4. **Model Selection UI Missing**

   - No model selector component
   - No available models listing
   - Need reusable model dropdown component

### Dependency Warnings

- Matrix View requires Codex entries (must build Codex first)
- AI tools in Editor (Sprint 5) require AI Connections (must complete in Sprint 3)
- Chat (Sprint 4) requires AI Connections (must complete in Sprint 3)

---

## Phase 5: Implementation Sequencing

### P0 (Critical) - Must Complete First

1. AI Connections infrastructure (backend services)
2. OpenAI Connection (US-050)
3. Codex Entry Types + CRUD (US-019, US-020)

### P1 (Important) - Core Features

4. Anthropic/Claude Connection (US-051)
5. Ollama Connection (US-054)
6. Codex Entry Detail View (US-021)
7. Model Selection UI (US-056)

### P2 (Enhancement) - After Core Complete

8. Aliases & Nicknames (US-024)
9. Matrix View (US-012)
10. Outline View (US-013)

### Parallel Work Opportunities

- Backend AI services can be built while frontend AI settings is developed
- Codex backend can be built while AI connections frontend is developed
- Matrix/Outline views can be built in parallel after Codex foundation

---

## Phase 6: Detailed Recommendations

### New Pages/Routes Needed

| Page | Purpose | Priority | Route |

|------|---------|----------|-------|

| Settings/Index | Main settings hub | P0 | `/settings` |

| Settings/AIConnections | AI provider management | P0 | `/settings/ai-connections` |

| Codex/Index | Codex entries list | P0 | `/novels/{novel}/codex` |

| Codex/Show | Entry detail view | P0 | `/novels/{novel}/codex/{entry}` |

### Updates to Existing Pages

| Page | Update | Location | Priority |

|------|--------|----------|----------|

| Editor/Index | Add Codex sidebar tab | Sidebar component | P1 |

| Plan/Index | Add Matrix/Outline view tabs | View switcher | P1 |

| Dashboard | Show AI connection status | Header/user menu | P2 |

| Layout | Add Settings link | User dropdown menu | P0 |

### Navigation/Menu Changes

| Frontend | Add Menu Item | Parent | Priority |

|----------|---------------|--------|----------|

| Main Layout | "Settings" | User dropdown | P0 |

| Editor Sidebar | "Codex" tab | Sidebar tabs | P1 |

| Plan | "Matrix" / "Outline" tabs | View selector | P1 |

### New Components Needed

| Component | Used By | Priority |

|-----------|---------|----------|

| `AIConnectionCard.vue` | Settings/AIConnections | P0 |

| `ModelSelector.vue` | Editor, Chat, Prompts | P0 |

| `CodexEntryList.vue` | Codex/Index | P0 |

| `CodexEntryCard.vue` | Codex list | P0 |

| `CodexEntryForm.vue` | Create/Edit entry | P0 |

| `CodexDetailTabs.vue` | Entry detail view | P1 |

| `AliasManager.vue` | Entry detail | P1 |

| `MatrixView.vue` | Plan page | P2 |

| `OutlineView.vue` | Plan page | P2 |

---

## Phase 7: Example User Journeys

### Journey 1: Setting Up OpenAI Connection

**Owner Journey (User configures AI):**

1. User clicks profile dropdown -> "Settings"
2. User navigates to "AI Connections" tab
3. User clicks "+ Add Connection" -> selects "OpenAI"
4. User pastes API key, clicks "Test Connection"
5. System validates key, shows success message
6. Connection card appears showing "OpenAI Connected"

**Consumer Journey (User uses AI):**

1. User opens Editor -> scene action menu
2. User sees "Summarize with AI" option (only if connected)
3. User clicks -> model dropdown shows available GPT models
4. User selects GPT-4, clicks generate
5. AI returns summary, user sees result

### Journey 2: Creating a Codex Character Entry

**Owner Journey (User creates character):**

1. User opens Editor sidebar -> clicks "Codex" tab
2. User clicks "+ New Entry" button
3. User selects type "Character" from dropdown
4. User enters name "Alice" and description
5. User adds aliases: "Al", "Alice Smith"
6. User clicks "Save" -> entry appears in list

**Consumer Journey (User uses Codex in Editor):**

1. User types "Alice" in scene prose
2. System (future) highlights "Alice" as Codex mention
3. User hovers -> sees character preview card
4. User clicks -> navigates to full Codex entry

### Journey 3: Using Matrix View in Plan

**Owner Journey (N/A - System generates):**

- Matrix data is auto-generated from scenes + Codex entries

**Consumer Journey (User views Matrix):**

1. User opens Plan view for novel
2. User clicks "Matrix" tab in view selector
3. System shows scenes as rows, characters as columns
4. User sees which characters appear in which scenes
5. User clicks cell -> jumps to that scene
6. User filters columns by Codex type (Characters only)

---

## Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── AI/
│       ├── AIServiceInterface.php
│       ├── OpenAIService.php
│       ├── AnthropicService.php
│       ├── OllamaService.php
│       └── AIServiceFactory.php
├── Models/
│   ├── AIConnection.php
│   ├── CodexEntry.php
│   └── CodexEntryAlias.php (or JSON field)
├── Http/
│   └── Controllers/
│       ├── AIConnectionController.php
│       ├── CodexController.php
│       └── SettingsController.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   ├── Settings/
│   │   ├── Index.vue
│   │   └── AIConnections.vue
│   └── Codex/
│       ├── Index.vue
│       └── Show.vue
├── Components/
│   ├── AI/
│   │   ├── AIConnectionCard.vue
│   │   ├── ModelSelector.vue
│   │   └── ConnectionStatus.vue
│   ├── Codex/
│   │   ├── CodexEntryList.vue
│   │   ├── CodexEntryCard.vue
│   │   ├── CodexEntryForm.vue
│   │   └── AliasManager.vue
│   └── Plan/
│       ├── MatrixView.vue
│       └── OutlineView.vue
```

### Database Migrations

```sql
-- ai_connections
CREATE TABLE ai_connections (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    provider ENUM('openai', 'anthropic', 'ollama', 'google', 'groq', 'openai_compatible'),
    name VARCHAR(255),
    api_key_encrypted TEXT NULL,
    base_url VARCHAR(255) NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_tested_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- codex_entries
CREATE TABLE codex_entries (
    id BIGINT PRIMARY KEY,
    novel_id BIGINT FOREIGN KEY,
    type ENUM('character', 'location', 'item', 'lore', 'organization', 'subplot'),
    name VARCHAR(255),
    aliases JSON NULL,
    description LONGTEXT NULL,
    thumbnail_path VARCHAR(255) NULL,
    ai_context_mode ENUM('always', 'detected', 'manual', 'never') DEFAULT 'detected',
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

## Risk Assessment

| Risk | Impact | Mitigation |

|------|--------|------------|

| API key security breach | Critical | Use Laravel Crypt for encryption, never expose to frontend |

| AI provider rate limits | High | Implement retry logic with exponential backoff |

| Ollama CORS issues | Medium | Document OLLAMA_ORIGINS setup, provide troubleshooting |

| Large Codex performance | Medium | Virtual scrolling, pagination, lazy loading |

| Complex Matrix rendering | Medium | Use CSS Grid, consider canvas for large novels |

---

## Testing Requirements

- Unit tests for AI service classes
- Feature tests for AI connection CRUD
- Feature tests for Codex CRUD
- Browser/E2E tests for Model Selector component
- Integration tests for AI API calls (mocked)

---

## Definition of Done for Sprint 3

- [ ] User can add OpenAI, Anthropic, and Ollama connections
- [ ] User can test and verify AI connections work
- [ ] Model selector shows available models from connected providers
- [ ] User can create Character, Location, Item, Lore, Organization Codex entries
- [ ] User can view and edit Codex entry details
- [ ] User can add aliases to Codex entries
- [ ] Matrix view shows scenes vs Codex entries
- [ ] Outline view shows linear scene list with summaries
- [ ] All features work on mobile (responsive)
- [ ] 80%+ test coverage on new code
