# 🔌 EPIC 1: AI Connections

**Epic ID:** EPIC-01  
**Priority:** 🔴 Critical  
**Total Story Points:** ~55  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** Foundation (Sprint 1-2)

---

## 📋 Epic Description

Establish the infrastructure for connecting to multiple AI providers, enabling users to bring their own API keys and use their preferred AI models. This is the **critical foundation** that enables all AI-powered features across the application.

**Reference:** [Novelcrafter AI Connections](https://www.novelcrafter.com/help/docs/ai-connections/openai)

---

## 🎯 Epic Goals

1. Support multiple AI providers with a unified interface
2. Secure API key storage with encryption
3. Provider-specific configuration options
4. Model selection and management UI
5. Usage tracking and cost visibility
6. Local LLM support for privacy-conscious users

---

## 📑 Feature Groups

### FG-01.1: Provider Connections (Core)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-01.1.1 | OpenAI Connection | 🔴 Critical | 5 |
| F-01.1.2 | Anthropic/Claude Connection | 🔴 Critical | 5 |
| F-01.1.3 | OpenRouter Connection | 🔴 Critical | 5 |
| F-01.1.4 | Ollama (Local LLM) | 🟡 High | 5 |
| F-01.1.5 | Groq Connection | 🟡 High | 3 |
| F-01.1.6 | LM Studio Connection | 🟡 High | 3 |
| F-01.1.7 | OpenAI API Compatible | 🟢 Medium | 5 |
| F-01.1.8 | Anyscale Endpoints | 🟢 Low | 3 |

### FG-01.2: Model Management

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-01.2.1 | Model Selection UI | 🔴 Critical | 5 |
| F-01.2.2 | Model Collections (Favorites) | 🟡 High | 3 |
| F-01.2.3 | Managing AI Models List | 🟡 High | 3 |
| F-01.2.4 | NSFW Model Support | 🟢 Medium | 2 |
| F-01.2.5 | Model Info Display | 🟢 Medium | 2 |

### FG-01.3: Settings & Security

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-01.3.1 | API Key Encryption | 🔴 Critical | 3 |
| F-01.3.2 | Connection Test Endpoint | 🔴 Critical | 2 |
| F-01.3.3 | Settings Page UI | 🔴 Critical | 5 |
| F-01.3.4 | Usage & Cost Tracking | 🟢 Medium | 5 |

---

## 📝 Detailed User Stories

### US-01.1: OpenAI Connection
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer using OpenAI's GPT models,  
**I want to** connect my OpenAI API key to the application,  
**So that** I can use GPT-4, GPT-4o, and other OpenAI models for writing assistance.

#### Acceptance Criteria:
- [ ] User can enter OpenAI API key in Settings
- [ ] API key is encrypted before storage
- [ ] User can test connection validity
- [ ] Success/error feedback is displayed
- [ ] Available models are fetched and listed
- [ ] Connection status indicator in UI
- [ ] Key can be updated or removed

#### Technical Notes:
- Use OpenAI SDK for PHP/JS
- Fetch models via `/v1/models` endpoint
- Encrypt with Laravel's `Crypt` facade
- Store in `ai_connections` table

---

### US-01.2: Anthropic/Claude Connection
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer preferring Claude models,  
**I want to** connect my Anthropic API key,  
**So that** I can use Claude 3, Claude 3.5, and other Anthropic models.

#### Acceptance Criteria:
- [ ] User can enter Anthropic API key
- [ ] API key is encrypted
- [ ] Connection test validates key
- [ ] Claude models (Opus, Sonnet, Haiku) are listed
- [ ] Model selection includes Claude variants

#### Technical Notes:
- Use Anthropic SDK
- Different endpoint structure from OpenAI
- Handle Claude-specific parameters

---

### US-01.3: OpenRouter Connection
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer wanting access to multiple AI providers,  
**I want to** connect via OpenRouter,  
**So that** I can access models from various providers through a single API.

#### Acceptance Criteria:
- [ ] User can enter OpenRouter API key
- [ ] Connection test validates key
- [ ] List of available models from all providers shown
- [ ] Model selection includes provider info
- [ ] Pricing info displayed per model
- [ ] OAuth integration option (optional enhancement)

#### Technical Notes:
- OpenRouter uses OpenAI-compatible API
- Additional headers required: `HTTP-Referer`, `X-Title`
- Model list comes from `/models` endpoint

**Reference:** [OpenRouter - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/ai-connections/openrouter)

---

### US-01.4: Ollama Local LLM
**Priority:** 🟡 High | **Points:** 5

**As a** privacy-conscious writer,  
**I want to** connect to a local Ollama instance,  
**So that** I can use local LLMs without sending data to external servers.

#### Acceptance Criteria:
- [ ] User can enter Ollama server URL (default: http://localhost:11434)
- [ ] Connection test checks Ollama availability
- [ ] List of locally installed models shown
- [ ] No API key required
- [ ] Handle CORS configuration guidance
- [ ] Offline functionality supported

#### Technical Notes:
- Ollama API is OpenAI-compatible
- CORS must be configured: `OLLAMA_ORIGINS`
- Models fetched from `/api/tags`

**Reference:** [Ollama - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/ai-connections/ollama)

---

### US-01.5: Groq Connection
**Priority:** 🟡 High | **Points:** 3

**As a** writer wanting fast inference,  
**I want to** connect to Groq's API,  
**So that** I can use fast LLaMA and Mixtral models.

#### Acceptance Criteria:
- [ ] User can enter Groq API key
- [ ] Connection test validates key
- [ ] Available Groq models listed
- [ ] Fast inference noted in UI

**Reference:** [Groq - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/ai-connections/groq)

---

### US-01.6: LM Studio Connection
**Priority:** 🟡 High | **Points:** 3

**As a** writer using LM Studio,  
**I want to** connect to my local LM Studio server,  
**So that** I can use models downloaded in LM Studio.

#### Acceptance Criteria:
- [ ] User can enter LM Studio server URL
- [ ] Connection test validates server
- [ ] Loaded model detected
- [ ] No API key required
- [ ] Setup instructions provided

**Reference:** [LM Studio - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/ai-connections/lm-studio)

---

### US-01.7: OpenAI API Compatible Endpoints
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer using alternative AI providers,  
**I want to** connect to any OpenAI-compatible API,  
**So that** I can use providers like Together AI, xAI, Mistral, etc.

#### Acceptance Criteria:
- [ ] User can enter custom base URL
- [ ] User can enter API key
- [ ] Optional: Custom headers configuration
- [ ] Connection test validates endpoint
- [ ] Model list fetched from endpoint
- [ ] Provider name customizable

**Reference:** [OpenAI SDK Compatible - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/ai-connections/openai-sdk-compatible)

---

### US-01.8: Model Selection UI
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer with multiple AI connections,  
**I want to** easily select which model to use,  
**So that** I can choose the right model for each task.

#### Acceptance Criteria:
- [ ] Dropdown/modal shows all available models
- [ ] Models grouped by provider
- [ ] Model info tooltip (context length, cost)
- [ ] Recently used models at top
- [ ] Search/filter models
- [ ] Default model setting
- [ ] Per-prompt model override
- [ ] Visual indicator of selected model

---

### US-01.9: Model Collections
**Priority:** 🟡 High | **Points:** 3

**As a** writer with favorite models,  
**I want to** create model collections,  
**So that** I can quickly access my preferred models.

#### Acceptance Criteria:
- [ ] Create named collections
- [ ] Add/remove models to collections
- [ ] Filter model selector by collection
- [ ] Default collection setting

**Reference:** [Model Collections - Novelcrafter Docs](https://www.novelcrafter.com/help/docs/models/model-collections)

---

### US-01.10: Connection Test Endpoint
**Priority:** 🔴 Critical | **Points:** 2

**As a** writer setting up AI connections,  
**I want to** test my connection before saving,  
**So that** I know my API key and settings are correct.

#### Acceptance Criteria:
- [ ] "Test Connection" button on each provider form
- [ ] Visual feedback during test (spinner)
- [ ] Success message with model count
- [ ] Error message with details if failed
- [ ] Rate limit handling

---

### US-01.11: API Key Encryption
**Priority:** 🔴 Critical | **Points:** 3

**As a** user storing API keys,  
**I want to** ensure my keys are encrypted,  
**So that** my credentials are secure.

#### Acceptance Criteria:
- [ ] API keys encrypted with Laravel Crypt
- [ ] Keys never exposed to frontend
- [ ] Keys decrypted only when making API calls
- [ ] Audit log for key access (optional)

---

### US-01.12: Settings Page UI
**Priority:** 🔴 Critical | **Points:** 5

**As a** user managing AI connections,  
**I want to** access a dedicated Settings page,  
**So that** I can manage all my AI connections in one place.

#### Acceptance Criteria:
- [ ] Settings accessible from user menu
- [ ] AI Connections tab/section
- [ ] List of connected providers with status
- [ ] Add new connection flow
- [ ] Edit/delete existing connections
- [ ] Connection health indicators
- [ ] Responsive design (mobile-friendly)

---

### US-01.13: Usage & Cost Tracking
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer monitoring AI usage,  
**I want to** see usage statistics and estimated costs,  
**So that** I can manage my AI spending.

#### Acceptance Criteria:
- [ ] Track tokens used per request
- [ ] Aggregate usage by day/week/month
- [ ] Estimate cost based on model pricing
- [ ] Usage dashboard in Settings
- [ ] Export usage data
- [ ] Set usage alerts (optional)

---

## 🗄️ Database Schema

### Table: `ai_connections`

```sql
CREATE TABLE ai_connections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('openai', 'anthropic', 'openrouter', 'ollama', 'groq', 'lm_studio', 'openai_compatible', 'anyscale') NOT NULL,
    name VARCHAR(255) NOT NULL,
    api_key_encrypted TEXT NULL,
    base_url VARCHAR(500) NULL,
    settings JSON NULL, -- Provider-specific settings
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    last_tested_at TIMESTAMP NULL,
    last_test_status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_provider (user_id, provider),
    INDEX idx_user_active (user_id, is_active)
);
```

### Table: `ai_model_collections`

```sql
CREATE TABLE ai_model_collections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    models JSON NOT NULL, -- Array of model identifiers
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table: `ai_usage_logs`

```sql
CREATE TABLE ai_usage_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    model VARCHAR(255) NOT NULL,
    input_tokens INT UNSIGNED DEFAULT 0,
    output_tokens INT UNSIGNED DEFAULT 0,
    estimated_cost DECIMAL(10, 6) NULL,
    feature_type ENUM('chat', 'prose', 'prompt', 'summarize', 'other') NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ai_connections(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at)
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── AI/
│       ├── Contracts/
│       │   └── AIServiceInterface.php
│       ├── AIServiceFactory.php
│       ├── OpenAIService.php
│       ├── AnthropicService.php
│       ├── OpenRouterService.php
│       ├── OllamaService.php
│       ├── GroqService.php
│       ├── LMStudioService.php
│       └── OpenAICompatibleService.php
├── Models/
│   ├── AIConnection.php
│   ├── AIModelCollection.php
│   └── AIUsageLog.php
├── Http/
│   ├── Controllers/
│   │   ├── AIConnectionController.php
│   │   └── SettingsController.php
│   └── Requests/
│       └── StoreAIConnectionRequest.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Settings/
│       ├── Index.vue
│       ├── AIConnections.vue
│       └── Usage.vue
├── Components/
│   └── AI/
│       ├── AIConnectionCard.vue
│       ├── AIConnectionForm.vue
│       ├── ModelSelector.vue
│       ├── ModelCollectionManager.vue
│       ├── ConnectionStatus.vue
│       └── UsageDashboard.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/ai-connections` | List user's connections |
| POST | `/api/ai-connections` | Create new connection |
| GET | `/api/ai-connections/{id}` | Get connection details |
| PATCH | `/api/ai-connections/{id}` | Update connection |
| DELETE | `/api/ai-connections/{id}` | Delete connection |
| POST | `/api/ai-connections/{id}/test` | Test connection |
| GET | `/api/ai-connections/{id}/models` | List available models |
| GET | `/api/ai-models/collections` | List model collections |
| POST | `/api/ai-models/collections` | Create collection |
| GET | `/api/ai-usage/stats` | Get usage statistics |

---

## ✅ Definition of Done

- [ ] All provider connections implemented and tested
- [ ] API keys securely encrypted
- [ ] Settings page fully functional
- [ ] Model selector working across all features
- [ ] Connection tests passing for all providers
- [ ] Usage tracking recording all AI calls
- [ ] Mobile-responsive UI
- [ ] Unit tests for all services (80%+ coverage)
- [ ] Feature tests for all endpoints
- [ ] Documentation updated

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| API key security breach | Critical | Use Laravel Crypt, never expose to frontend, audit logging |
| Provider API changes | High | Abstract via interface, version-specific handling |
| Rate limiting | Medium | Implement retry with exponential backoff |
| CORS issues (local LLMs) | Medium | Provide detailed setup documentation |
| Provider downtime | Medium | Allow multiple connections, fallback options |

---

## 📎 References

- [Novelcrafter AI Connections Overview](https://www.novelcrafter.com/help/docs/ai-connections/openai)
- [OpenRouter Documentation](https://www.novelcrafter.com/help/docs/ai-connections/openrouter)
- [Ollama Documentation](https://www.novelcrafter.com/help/docs/ai-connections/ollama)
- [Model Management](https://www.novelcrafter.com/help/docs/models/model-management)
