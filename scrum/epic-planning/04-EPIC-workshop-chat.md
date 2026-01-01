# 💬 EPIC 4: Workshop (Chat)

**Epic ID:** EPIC-04  
**Priority:** 🔴 Critical  
**Total Story Points:** ~50  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** EPIC-01 (AI Connections)

---

## 📋 Epic Description

Build a comprehensive AI chat interface (Workshop) that enables writers to brainstorm, ask questions, and get AI assistance in a conversational format. The chat integrates with novel context, Codex entries, and allows transferring information to the manuscript.

**Reference:** [Novelcrafter Chat Documentation](https://www.novelcrafter.com/help/docs/chat/the-chat-interface)

---

## 🎯 Epic Goals

1. Conversational AI interface for brainstorming
2. Multiple chat threads management
3. Context injection from novel, scenes, and Codex
4. Model selection per conversation
5. Transfer information from chat to manuscript/Codex
6. Pinnable chat panel in editor
7. Extract structured data from responses

---

## 📑 Feature Groups

### FG-04.1: Chat Interface Core

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-04.1.1 | Basic Chat Interface | 🔴 Critical | 8 |
| F-04.1.2 | Chat Threads Management | 🔴 Critical | 5 |
| F-04.1.3 | Message History | 🔴 Critical | 3 |
| F-04.1.4 | Model Selection in Chat | 🔴 Critical | 3 |
| F-04.1.5 | Message Streaming | 🔴 Critical | 5 |

### FG-04.2: Context & Integration

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-04.2.1 | Context Injection | 🔴 Critical | 8 |
| F-04.2.2 | Chat with Scene/Document | 🟡 High | 5 |
| F-04.2.3 | Pin Chat Panel | 🟡 High | 3 |
| F-04.2.4 | Context Preview & Token Count | 🟡 High | 3 |

### FG-04.3: Transfer & Extract

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-04.3.1 | Transferring Information from Chat | 🟡 High | 5 |
| F-04.3.2 | Extract to Codex | 🟡 High | 5 |
| F-04.3.3 | Extract Feature (Structured Data) | 🟢 Medium | 5 |

### FG-04.4: Uses & Workflows

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-04.4.1 | Brainstorming Tools | 🟡 High | 5 |
| F-04.4.2 | Using Chat with Plan | 🟢 Medium | 3 |
| F-04.4.3 | Quick Prompts in Chat | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-04.1: Basic Chat Interface
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** chat with AI in a conversational interface,  
**So that** I can brainstorm ideas and get writing assistance.

#### Acceptance Criteria:
- [ ] Chat window with message list
- [ ] Input field for new messages
- [ ] Send button and Enter key support
- [ ] Messages display with user/AI distinction
- [ ] Timestamps on messages
- [ ] Scroll to latest message
- [ ] Loading indicator while AI responds
- [ ] Error handling with retry option
- [ ] Copy message content button
- [ ] Regenerate last response button

**Reference:** [The Chat Interface](https://www.novelcrafter.com/help/docs/chat/the-chat-interface)

---

### US-04.2: Chat Threads Management
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** create and manage multiple chat threads,  
**So that** I can organize conversations by topic.

#### Acceptance Criteria:
- [ ] Create new thread
- [ ] Thread list sidebar
- [ ] Thread title (auto-generated or custom)
- [ ] Rename thread
- [ ] Delete thread with confirmation
- [ ] Switch between threads
- [ ] Thread last updated timestamp
- [ ] Search threads

**Reference:** [Using Chat](https://www.novelcrafter.com/help/docs/chat/using-chat)

---

### US-04.3: Message Streaming
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** see AI responses stream in real-time,  
**So that** I can read the response as it's generated.

#### Acceptance Criteria:
- [ ] Stream tokens as they arrive
- [ ] Smooth text rendering
- [ ] Stop generation button
- [ ] Handle stream interruption gracefully
- [ ] Show "generating..." indicator

---

### US-04.4: Model Selection in Chat
**Priority:** 🔴 Critical | **Points:** 3

**As a** writer,  
**I want to** choose which AI model to use in chat,  
**So that** I can use different models for different tasks.

#### Acceptance Criteria:
- [ ] Model selector dropdown in chat header
- [ ] Show currently selected model
- [ ] Default model from settings
- [ ] Per-thread model selection
- [ ] Show model name in message metadata

---

### US-04.5: Context Injection
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want to** include novel context in AI conversations,  
**So that** AI understands my story when answering questions.

#### Acceptance Criteria:
- [ ] Add current scene as context
- [ ] Add Codex entries as context
- [ ] Add novel summary as context
- [ ] Add recent scenes as context
- [ ] Context selector UI
- [ ] Preview context before sending
- [ ] Token count for context
- [ ] Auto-include detected Codex entries
- [ ] Manual context addition
- [ ] Clear context button

**Reference:** [Uses for Chat](https://www.novelcrafter.com/help/docs/chat/uses-for-chat)

---

### US-04.6: Chat with Scene/Document
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** start a chat about a specific scene,  
**So that** AI can help with that particular section.

#### Acceptance Criteria:
- [ ] "Chat with this scene" button in editor
- [ ] Scene content auto-added as context
- [ ] Relevant Codex entries auto-detected
- [ ] Chat opens with scene context set
- [ ] Thread linked to scene

---

### US-04.7: Pin Chat Panel
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** pin the chat panel beside the editor,  
**So that** I can chat while writing.

#### Acceptance Criteria:
- [ ] Pin button in chat header
- [ ] Chat panel docks to side of editor
- [ ] Resizable panel width
- [ ] Collapse/expand pinned panel
- [ ] Persisted pin state
- [ ] Works with other pinned panels

---

### US-04.8: Context Preview & Token Count
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** see what context will be sent to AI,  
**So that** I can optimize token usage.

#### Acceptance Criteria:
- [ ] Preview button shows full context
- [ ] Token count displayed
- [ ] Token limit warnings
- [ ] Individual context item token counts
- [ ] Remove context items from preview

---

### US-04.9: Transferring Information from Chat
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** transfer AI responses to my manuscript,  
**So that** I can use generated content in my novel.

#### Acceptance Criteria:
- [ ] "Insert to scene" button on messages
- [ ] Insert at cursor position
- [ ] Insert as new scene option
- [ ] Insert as section (alternative/note)
- [ ] Copy with formatting
- [ ] Edit before inserting

**Reference:** [Transferring Information from Chat](https://www.novelcrafter.com/help/docs/chat/transfering-information)

---

### US-04.10: Extract to Codex
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** extract Codex entries from chat responses,  
**So that** I can add characters/locations discussed to my Codex.

#### Acceptance Criteria:
- [ ] "Extract to Codex" button on messages
- [ ] Entry type selection
- [ ] Pre-fill name and description
- [ ] Edit before saving
- [ ] Multiple entries from one message
- [ ] Auto-detect potential entries

---

### US-04.11: Extract Feature (Structured Data)
**Priority:** 🟢 Medium | **Points:** 5

**As a** writer,  
**I want to** extract structured data from AI responses,  
**So that** I can create multiple items at once.

#### Acceptance Criteria:
- [ ] "Extract" modal for structured extraction
- [ ] Define extraction schema
- [ ] Preview extracted data
- [ ] Create Codex entries from extracted data
- [ ] Create scenes from extracted data
- [ ] Create outline from extracted data

**Reference:** [Extract Feature](https://www.novelcrafter.com/help/docs/organization/the-extract-feature)

---

### US-04.12: Brainstorming Tools
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** use quick brainstorming prompts,  
**So that** I can quickly generate ideas.

#### Acceptance Criteria:
- [ ] Quick prompt buttons: "What if...", "Help me with...", "Generate names for..."
- [ ] Character brainstorming prompts
- [ ] Plot brainstorming prompts
- [ ] Setting brainstorming prompts
- [ ] Custom quick prompts

---

### US-04.13: Using Chat with Plan
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** chat about my story plan,  
**So that** AI can help with plot development.

#### Acceptance Criteria:
- [ ] Include outline as context
- [ ] Include specific chapter/act as context
- [ ] Generate scene summaries via chat
- [ ] Suggest plot improvements

**Reference:** [Using Chat and Plan together](https://www.novelcrafter.com/help/docs/organization/chat-with-plan)

---

## 🗄️ Database Schema

### Table: `chat_threads`

```sql
CREATE TABLE chat_threads (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL,
    model VARCHAR(255) NULL, -- Per-thread model override
    context_settings JSON NULL, -- Context preferences
    is_pinned BOOLEAN DEFAULT FALSE,
    linked_scene_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_scene_id) REFERENCES scenes(id) ON DELETE SET NULL,
    INDEX idx_novel_user (novel_id, user_id)
);
```

### Table: `chat_messages`

```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    thread_id BIGINT UNSIGNED NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content LONGTEXT NOT NULL,
    model_used VARCHAR(255) NULL,
    tokens_input INT UNSIGNED NULL,
    tokens_output INT UNSIGNED NULL,
    context_snapshot JSON NULL, -- What context was included
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
    INDEX idx_thread_messages (thread_id, created_at)
);
```

### Table: `chat_context_items`

```sql
CREATE TABLE chat_context_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    thread_id BIGINT UNSIGNED NOT NULL,
    context_type ENUM('scene', 'codex', 'summary', 'outline', 'custom') NOT NULL,
    reference_id BIGINT UNSIGNED NULL, -- scene_id or codex_entry_id
    custom_content TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Chat/
│       ├── ChatService.php
│       ├── ContextBuilder.php
│       ├── MessageStreamer.php
│       └── ExtractService.php
├── Models/
│   ├── ChatThread.php
│   ├── ChatMessage.php
│   └── ChatContextItem.php
├── Http/
│   ├── Controllers/
│   │   └── ChatController.php
│   └── Requests/
│       └── SendMessageRequest.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Chat/
│       └── Index.vue
├── Components/
│   └── Chat/
│       ├── ChatWindow.vue
│       ├── ChatThreadList.vue
│       ├── ChatMessage.vue
│       ├── ChatInput.vue
│       ├── ContextSelector.vue
│       ├── ContextPreview.vue
│       ├── ModelSelector.vue
│       ├── ExtractModal.vue
│       ├── TransferMenu.vue
│       └── BrainstormingPrompts.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/chat/threads` | List threads |
| POST | `/api/novels/{novel}/chat/threads` | Create thread |
| GET | `/api/chat/threads/{thread}` | Get thread with messages |
| PATCH | `/api/chat/threads/{thread}` | Update thread |
| DELETE | `/api/chat/threads/{thread}` | Delete thread |
| POST | `/api/chat/threads/{thread}/messages` | Send message (streaming) |
| DELETE | `/api/chat/messages/{message}` | Delete message |
| POST | `/api/chat/messages/{message}/regenerate` | Regenerate response |
| POST | `/api/chat/messages/{message}/transfer` | Transfer to scene |
| POST | `/api/chat/messages/{message}/extract` | Extract data |
| GET | `/api/chat/threads/{thread}/context` | Get context items |
| POST | `/api/chat/threads/{thread}/context` | Add context item |

---

## ✅ Definition of Done

- [ ] Chat interface with streaming responses
- [ ] Multiple threads management
- [ ] Context injection working with scenes and Codex
- [ ] Model selection per thread
- [ ] Transfer to manuscript functional
- [ ] Extract to Codex functional
- [ ] Pinnable panel working
- [ ] Mobile-responsive
- [ ] Unit tests for services
- [ ] Feature tests for endpoints

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Streaming complexity | High | Use Server-Sent Events, test thoroughly |
| Context token limits | Medium | Token counting, smart truncation |
| Long conversation memory | Medium | Summarization, pagination |
| Race conditions in threads | Medium | Proper locking, optimistic UI |

---

## 📎 References

- [The Chat Interface](https://www.novelcrafter.com/help/docs/chat/the-chat-interface)
- [Using Chat](https://www.novelcrafter.com/help/docs/chat/using-chat)
- [Uses for Chat](https://www.novelcrafter.com/help/docs/chat/uses-for-chat)
- [Transferring Information from Chat](https://www.novelcrafter.com/help/docs/chat/transfering-information)
- [Extract Feature](https://www.novelcrafter.com/help/docs/organization/the-extract-feature)
- [Using Chat and Plan together](https://www.novelcrafter.com/help/docs/organization/chat-with-plan)
