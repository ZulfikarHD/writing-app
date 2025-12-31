# 💬 Epic 5: AI/Chat Interface

**Epic ID:** EPIC-006  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 4-6  
**Total Story Points:** 47

---

## 📋 Deskripsi Epic

Membangun interface chat untuk interaksi bebas dengan AI (LLM). Chat mendukung multiple threads, context dari novel, dan berbagai tools untuk brainstorming dan analisis.

---

## 🎯 Goals

- Free-form conversation dengan AI
- Context-aware responses
- Multiple concurrent chats
- Specialized tools untuk writing tasks
- Easy extraction of AI-generated content

---

## 📑 User Stories

### US-035: Basic Chat Interface
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** chat interface untuk berinteraksi dengan AI,  
**Agar** saya dapat brainstorming dan mendapat bantuan kreatif.

#### Acceptance Criteria:
- [ ] Chat window dengan message history
- [ ] Input field untuk typing messages
- [ ] Send button dan Enter key untuk submit
- [ ] AI responses ditampilkan dengan clear formatting
- [ ] Loading indicator saat AI processing
- [ ] Markdown rendering untuk AI responses
- [ ] Code blocks formatting jika ada
- [ ] Error handling dengan clear messages
- [ ] Retry failed message option

#### Technical Notes:
- Streaming response untuk better UX
- Store chat history di database

---

### US-036: Chat Threads Management
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** membuat dan mengelola multiple chat threads,  
**Agar** saya dapat memisahkan topik diskusi.

#### Acceptance Criteria:
- [ ] Create new chat thread
- [ ] List semua chat threads
- [ ] Rename chat thread
- [ ] Delete chat thread dengan konfirmasi
- [ ] Switch between threads
- [ ] Each thread maintains own history
- [ ] Thread auto-named based on first message (optional)
- [ ] Sort by: recent, alphabetical
- [ ] Fork/duplicate chat thread
- [ ] Archive chat thread (hide but don't delete)
- [ ] Unarchive from archive list
- [ ] Export chat transcript

---

### US-037: Context Injection
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** AI memahami konteks novel saya,  
**Agar** responses relevan dengan cerita saya.

#### Acceptance Criteria:
- [ ] Option untuk include context: None, Current Scene, Current Chapter, Full Novel
- [ ] Auto-inject relevant Codex entries
- [ ] Manually add specific Codex entries to context
- [ ] Context indicator showing what's included
- [ ] Preview context before sending (optional)
- [ ] Context token count displayed
- [ ] Warning jika context terlalu besar

#### Technical Notes:
- Smart context selection untuk optimize tokens
- Prioritize most relevant information

---

### US-038: Pin Chat Panel
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** pin chat panel beside manuscript,  
**Agar** saya dapat menulis sambil berkonsultasi dengan AI.

#### Acceptance Criteria:
- [ ] Pin button di chat header
- [ ] Pinned chat muncul di side panel
- [ ] Split view: editor + chat
- [ ] Resize panel width
- [ ] Unpin untuk fullscreen chat
- [ ] Remember pinned state

---

### US-039: Model Selection in Chat
**Prioritas:** 🔴 Tinggi | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** memilih model AI untuk setiap chat,  
**Agar** saya dapat menggunakan model yang sesuai kebutuhan.

#### Acceptance Criteria:
- [ ] Model selector dropdown di chat interface
- [ ] Show available models dari semua connected providers
- [ ] Show model info (name, provider, capability)
- [ ] Disable unavailable models dengan reason
- [ ] Remember last used model per thread
- [ ] Quick switch model mid-conversation

---

### US-040: Chat with Scene/Document
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** memulai chat dengan context specific scene atau document,  
**Agar** AI langsung memahami apa yang sedang saya kerjakan.

#### Acceptance Criteria:
- [ ] "Chat with this scene" button di editor
- [ ] "Chat about this entry" di Codex detail
- [ ] Pre-filled context dengan selected content
- [ ] System prompt explaining the context
- [ ] Option untuk analyze, improve, or discuss
- [ ] Chat thread linked ke source scene/entry

---

### US-041: Brainstorming Tools
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** AI brainstorming tools,  
**Agar** saya dapat generate ideas untuk plot, character, dll.

#### Acceptance Criteria:
- [ ] Quick prompts: "Generate plot ideas for..."
- [ ] Quick prompts: "Create character profile for..."
- [ ] Quick prompts: "Suggest dialogue between..."
- [ ] Quick prompts: "Describe setting for..."
- [ ] Customizable quick prompt buttons
- [ ] Results dapat di-copy atau extract ke Codex

---

### US-042: Extract from Chat
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengextract content dari chat ke novel atau Codex,  
**Agar** saya dapat menggunakan AI-generated content dalam cerita.

#### Acceptance Criteria:
- [ ] Select text dari AI response
- [ ] Option: Copy to clipboard
- [ ] Option: Insert ke current scene
- [ ] Option: Create Codex entry
- [ ] Option: Create new snippet
- [ ] Confirmation dialog sebelum insert
- [ ] Maintain formatting saat extract

---

### US-108: Extract Feature (Structured Data Extraction)
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** extract structured data dari chat conversations,  
**Agar** AI-generated content dapat langsung menjadi Codex entries atau scenes.

#### Acceptance Criteria:
- [ ] Select text di chat response
- [ ] "Extract" option di context menu
- [ ] Choose extraction type: Codex Entry, Scene, Snippet
- [ ] Auto-detect structure (character name, description, etc.)
- [ ] Preview extracted data sebelum save
- [ ] Edit extracted data sebelum save
- [ ] Extract multiple items dari satu response

#### Source:
> [Extract Feature - NovelCrafter Docs](https://www.novelcrafter.com/help/docs/organization/extract)

---

## 📊 Sprint Breakdown

### Sprint 4 (47 total → 16 points)
- US-035: Basic Chat Interface (8 pts)
- US-036: Chat Threads Management (5 pts)
- US-039: Model Selection (3 pts)

### Sprint 5 (31 remaining → 16 points)
- US-037: Context Injection (8 pts)
- US-040: Chat with Scene/Document (5 pts)
- US-038: Pin Chat Panel (3 pts)

### Sprint 6 (15 remaining → 15 points)
- US-041: Brainstorming Tools (5 pts)
- US-042: Extract from Chat (5 pts)
- US-108: Extract Feature (5 pts)

---

## 🔗 Dependencies

- Epic 7 (AI Connections) must be complete first
- Epic 3 (Codex) untuk context injection
- Epic 1 (Manuscript Editor) untuk scene context

---

## 📝 Notes

- Implement streaming responses untuk better UX
- Consider chat history limit untuk performance
- Token counting penting untuk cost awareness
- Extract feature adalah productivity booster
- Consider AI-assisted structure detection untuk extraction
