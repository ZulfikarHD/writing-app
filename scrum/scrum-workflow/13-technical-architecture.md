# 🏗️ Technical Architecture & Stack

**Version:** 1.0  
**Last Updated:** 31 Desember 2024

---

## 📋 Overview

Dokumen ini mendefinisikan arsitektur teknis dan technology stack untuk AI-Assisted Novel Writing App. Arsitektur dirancang untuk single-user web application dengan fokus pada performance, security, dan extensibility.

---

## 🎯 Architecture Goals

1. **Performance:** Responsif dengan dokumen besar (100k+ words)
2. **Security:** API keys terenkripsi, data user aman
3. **Extensibility:** Mudah menambah AI providers baru
4. **Offline Support:** Basic editing tanpa koneksi (future)
5. **Mobile Responsive:** Optimal di semua device sizes

---

## 🏛️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                          CLIENT (Vue.js)                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │  TipTap     │  │  Pinia      │  │  Vue Router (Wayfinder) │ │
│  │  Editor     │  │  State      │  │  Navigation             │ │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘ │
└───────────────────────────┬─────────────────────────────────────┘
                            │ HTTP/WebSocket
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        SERVER (Laravel)                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │  API        │  │  Services   │  │  Queue Jobs             │ │
│  │  Controllers│  │  Layer      │  │  (AI Processing)        │ │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘ │
└───────────────────────────┬─────────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│   Database    │   │    Cache      │   │  AI Providers │
│   (MySQL)     │   │   (Redis)     │   │  (External)   │
└───────────────┘   └───────────────┘   └───────────────┘
```

---

## 🛠️ Technology Stack

### Frontend

| Component | Technology | Versi | Alasan |
|-----------|------------|-------|--------|
| Framework | Vue.js 3 | ^3.4 | Composition API, performance |
| Build Tool | Vite | ^5.0 | Fast HMR, modern bundling |
| Router | Wayfinder | latest | Type-safe routing (Laravel) |
| State | Pinia | ^2.1 | Official Vue state management |
| Editor | TipTap | ^2.1 | Extensible, ProseMirror-based |
| UI | Tailwind CSS | ^3.4 | Utility-first, customizable |
| Icons | Heroicons | ^2.0 | Consistent icon set |
| HTTP | Axios | ^1.6 | HTTP client with interceptors |
| Drag & Drop | VueDraggable | ^4.1 | Sortable.js wrapper |

### Backend

| Component | Technology | Versi | Alasan |
|-----------|------------|-------|--------|
| Framework | Laravel | ^11.0 | Full-featured, PHP ecosystem |
| PHP | PHP | ^8.3 | Performance, typed properties |
| Auth | Laravel Sanctum | ^4.0 | SPA authentication |
| Queue | Laravel Queue | built-in | Background job processing |
| Events | Laravel Events | built-in | Real-time updates |

### Database & Storage

| Component | Technology | Versi | Alasan |
|-----------|------------|-------|--------|
| Database | MySQL | ^8.0 | Reliable, JSON support |
| Cache | Redis | ^7.0 | Session, cache, queue |
| File Storage | Local/S3 | - | Images, exports |
| Search | MySQL FTS | built-in | Full-text search |

### AI Integration

| Provider | SDK/Library | Notes |
|----------|-------------|-------|
| OpenAI | openai-php | GPT-4, GPT-3.5 |
| Anthropic | anthropic-sdk-php | Claude models |
| Google | google/cloud | Gemini API |
| Ollama | HTTP Client | Local models |
| LM Studio | HTTP Client | OpenAI compatible |

---

## 📁 Project Structure

### Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── NovelController.php
│   │   │   ├── SceneController.php
│   │   │   ├── ChapterController.php
│   │   │   ├── CodexController.php
│   │   │   ├── ChatController.php
│   │   │   ├── PromptController.php
│   │   │   └── AIController.php
│   │   └── Auth/
│   ├── Middleware/
│   └── Requests/
├── Services/
│   ├── AI/
│   │   ├── AIServiceInterface.php
│   │   ├── OpenAIService.php
│   │   ├── AnthropicService.php
│   │   ├── OllamaService.php
│   │   └── AIServiceFactory.php
│   ├── Novel/
│   │   ├── NovelService.php
│   │   ├── SceneService.php
│   │   └── ImportExportService.php
│   ├── Codex/
│   │   └── CodexService.php
│   └── Context/
│       └── ContextBuilderService.php
├── Models/
│   ├── User.php
│   ├── Novel.php
│   ├── Chapter.php
│   ├── Scene.php
│   ├── CodexEntry.php
│   ├── Chat.php
│   ├── ChatMessage.php
│   ├── Prompt.php
│   └── Snippet.php
├── Jobs/
│   ├── ProcessAIRequest.php
│   ├── IndexMentions.php
│   └── GenerateExport.php
└── Events/
    └── AIResponseReceived.php

database/
├── migrations/
└── seeders/

routes/
├── api.php
└── web.php
```

### Frontend (Vue.js)

```
resources/js/
├── app.js
├── bootstrap.js
├── Pages/
│   ├── Dashboard/
│   │   └── Index.vue
│   ├── Editor/
│   │   ├── Index.vue
│   │   ├── SceneEditor.vue
│   │   └── components/
│   ├── Plan/
│   │   ├── Index.vue
│   │   ├── GridView.vue
│   │   ├── MatrixView.vue
│   │   └── OutlineView.vue
│   ├── Codex/
│   │   ├── Index.vue
│   │   └── EntryDetail.vue
│   ├── Chat/
│   │   └── Index.vue
│   ├── Prompts/
│   │   └── Index.vue
│   └── Settings/
│       └── Index.vue
├── Components/
│   ├── Editor/
│   │   ├── TipTapEditor.vue
│   │   ├── SlashCommand.vue
│   │   └── TextTransform.vue
│   ├── Common/
│   │   ├── Modal.vue
│   │   ├── Dropdown.vue
│   │   └── Toast.vue
│   └── Layout/
│       ├── Sidebar.vue
│       ├── Header.vue
│       └── MainLayout.vue
├── Composables/
│   ├── useAI.js
│   ├── useNovel.js
│   ├── useCodex.js
│   └── useTheme.js
├── Stores/
│   ├── novelStore.js
│   ├── editorStore.js
│   ├── codexStore.js
│   └── settingsStore.js
└── Utils/
    ├── api.js
    ├── helpers.js
    └── constants.js
```

---

## 🗄️ Database Schema

### Core Tables

```sql
-- Users
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Novels
CREATE TABLE novels (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    title VARCHAR(255),
    pen_name VARCHAR(255),
    cover_path VARCHAR(255),
    default_pov ENUM('first', 'third'),
    default_tense ENUM('past', 'present'),
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Chapters
CREATE TABLE chapters (
    id BIGINT PRIMARY KEY,
    novel_id BIGINT FOREIGN KEY,
    act_id BIGINT NULLABLE,
    title VARCHAR(255),
    position INT,
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Scenes
CREATE TABLE scenes (
    id BIGINT PRIMARY KEY,
    chapter_id BIGINT FOREIGN KEY,
    title VARCHAR(255),
    content LONGTEXT,
    summary TEXT,
    position INT,
    pov_character_id BIGINT NULLABLE,
    status VARCHAR(50) DEFAULT 'draft',
    word_count INT DEFAULT 0,
    subtitle VARCHAR(255),
    notes TEXT,
    exclude_from_ai BOOLEAN DEFAULT FALSE,
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    archived_at TIMESTAMP
);

-- Codex Entries
CREATE TABLE codex_entries (
    id BIGINT PRIMARY KEY,
    novel_id BIGINT FOREIGN KEY,
    type ENUM('character', 'location', 'item', 'lore', 'organization', 'subplot', 'custom'),
    name VARCHAR(255),
    aliases JSON,
    description LONGTEXT,
    details JSON,
    research_notes TEXT,
    thumbnail_path VARCHAR(255),
    ai_context_mode ENUM('always', 'detected', 'manual', 'never') DEFAULT 'detected',
    tags JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Codex Relations
CREATE TABLE codex_relations (
    id BIGINT PRIMARY KEY,
    source_entry_id BIGINT FOREIGN KEY,
    target_entry_id BIGINT FOREIGN KEY,
    relation_type VARCHAR(100),
    created_at TIMESTAMP
);

-- Chats
CREATE TABLE chats (
    id BIGINT PRIMARY KEY,
    novel_id BIGINT FOREIGN KEY,
    title VARCHAR(255),
    model VARCHAR(100),
    context_type VARCHAR(50),
    context_id BIGINT NULLABLE,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Chat Messages
CREATE TABLE chat_messages (
    id BIGINT PRIMARY KEY,
    chat_id BIGINT FOREIGN KEY,
    role ENUM('system', 'user', 'assistant'),
    content LONGTEXT,
    tokens_used INT,
    created_at TIMESTAMP
);

-- Prompts
CREATE TABLE prompts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    name VARCHAR(255),
    description TEXT,
    category VARCHAR(100),
    system_message TEXT,
    user_template TEXT,
    variables JSON,
    model_params JSON,
    is_builtin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Snippets
CREATE TABLE snippets (
    id BIGINT PRIMARY KEY,
    novel_id BIGINT FOREIGN KEY,
    title VARCHAR(255),
    content LONGTEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Revision History
CREATE TABLE revisions (
    id BIGINT PRIMARY KEY,
    revisionable_type VARCHAR(255),
    revisionable_id BIGINT,
    content LONGTEXT,
    created_at TIMESTAMP
);

-- AI Connections
CREATE TABLE ai_connections (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    provider VARCHAR(50),
    api_key_encrypted TEXT,
    base_url VARCHAR(255),
    settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔌 API Design

### REST Endpoints

```
# Authentication
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/user

# Novels
GET    /api/novels
POST   /api/novels
GET    /api/novels/{id}
PUT    /api/novels/{id}
DELETE /api/novels/{id}

# Chapters
GET    /api/novels/{novelId}/chapters
POST   /api/novels/{novelId}/chapters
PUT    /api/chapters/{id}
DELETE /api/chapters/{id}
POST   /api/chapters/reorder

# Scenes
GET    /api/chapters/{chapterId}/scenes
POST   /api/chapters/{chapterId}/scenes
GET    /api/scenes/{id}
PUT    /api/scenes/{id}
DELETE /api/scenes/{id}
POST   /api/scenes/reorder
POST   /api/scenes/{id}/archive
POST   /api/scenes/{id}/restore

# Codex
GET    /api/novels/{novelId}/codex
POST   /api/novels/{novelId}/codex
GET    /api/codex/{id}
PUT    /api/codex/{id}
DELETE /api/codex/{id}
GET    /api/codex/{id}/mentions

# Chat
GET    /api/novels/{novelId}/chats
POST   /api/novels/{novelId}/chats
GET    /api/chats/{id}
DELETE /api/chats/{id}
POST   /api/chats/{id}/messages

# AI
POST   /api/ai/generate
POST   /api/ai/transform
POST   /api/ai/summarize
POST   /api/ai/detect-characters
GET    /api/ai/models

# Prompts
GET    /api/prompts
POST   /api/prompts
PUT    /api/prompts/{id}
DELETE /api/prompts/{id}
POST   /api/prompts/{id}/test

# Import/Export
POST   /api/novels/{id}/import
GET    /api/novels/{id}/export
POST   /api/novels/{id}/backup

# Settings
GET    /api/settings
PUT    /api/settings
GET    /api/ai-connections
POST   /api/ai-connections
DELETE /api/ai-connections/{id}
POST   /api/ai-connections/{id}/test
```

---

## 🔐 Security Considerations

### API Key Storage
```php
// Encrypt API keys before storing
$encrypted = Crypt::encryptString($apiKey);

// Decrypt when using
$apiKey = Crypt::decryptString($encrypted);
```

### Authentication
- Laravel Sanctum untuk SPA auth
- CSRF protection
- Rate limiting pada API endpoints

### Data Protection
- Input validation dengan Form Requests
- SQL injection prevention via Eloquent
- XSS prevention dengan Vue's automatic escaping

---

## ⚡ Performance Optimizations

### Frontend
- Lazy loading routes
- Virtual scrolling untuk long lists
- Debounced auto-save
- Local storage caching

### Backend
- Redis caching untuk frequent queries
- Queue jobs untuk AI requests
- Database indexing
- Eager loading relationships

### Editor Performance
- Lazy load TipTap extensions
- Efficient re-renders dengan Vue reactivity
- Chunked document loading untuk large novels

---

## 🔄 Real-time Features

### WebSocket Events (Laravel Broadcasting)
```php
// AI response streaming
broadcast(new AIChunkReceived($chatId, $chunk));

// Collaboration (future)
broadcast(new SceneUpdated($sceneId, $content));
```

---

## 📦 Deployment Architecture

```
┌─────────────────────────────────────────────────┐
│                   Load Balancer                 │
└─────────────────────────┬───────────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        ▼                 ▼                 ▼
┌───────────────┐ ┌───────────────┐ ┌───────────────┐
│   Web Server  │ │   Web Server  │ │   Web Server  │
│   (Laravel)   │ │   (Laravel)   │ │   (Laravel)   │
└───────────────┘ └───────────────┘ └───────────────┘
        │                 │                 │
        └─────────────────┼─────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        ▼                 ▼                 ▼
┌───────────────┐ ┌───────────────┐ ┌───────────────┐
│   MySQL       │ │   Redis       │ │   S3 Storage  │
│   (Primary)   │ │   (Cluster)   │ │   (Images)    │
└───────────────┘ └───────────────┘ └───────────────┘
```

### Environment Requirements
- PHP 8.3+
- MySQL 8.0+
- Redis 7.0+
- Node.js 20+ (untuk build)
- Composer 2.x
- Yarn 1.22+

---

## 📝 Development Setup

```bash
# Clone repository
git clone [repo-url]
cd novel-writer-app

# Install PHP dependencies
composer install

# Install Node dependencies
yarn install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development servers
php artisan serve
yarn dev

# Run queue worker (separate terminal)
php artisan queue:work
```

---

## 🧪 Testing Strategy

### Backend
- PHPUnit untuk unit & feature tests
- Laravel Dusk untuk browser tests
- Test coverage target: 80%

### Frontend
- Vitest untuk unit tests
- Playwright untuk E2E tests
- Component testing dengan Vue Test Utils

---

## 📚 Documentation Links

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide)
- [TipTap Documentation](https://tiptap.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [OpenAI API Reference](https://platform.openai.com/docs)
- [Anthropic API Reference](https://docs.anthropic.com)
