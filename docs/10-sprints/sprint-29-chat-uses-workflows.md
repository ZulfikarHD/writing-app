# 📦 Sprint 29: Workshop Chat Uses & Workflows (FG-04.4)

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Status:** ✅ Completed

---

## 📋 Sprint Goals

Implementasi fitur uses dan workflows untuk Workshop Chat, yaitu: **Brainstorming Tools** (kategori prompt untuk kreativitas), **Chat with Plan** (integrasi outline sebagai context), dan **Quick Prompts** (akses cepat ke saved prompts). Fitur ini memungkinkan writer menggunakan chat secara lebih efektif untuk berbagai creative workflows tanpa meninggalkan interface chat.

---

## ✨ Features Implemented

### 1. Brainstorming Tools (F-04.4.1)
- **BrainstormingPanel**: Panel kategori untuk brainstorming dengan 4 kategori:
  - **Character** (6 prompts): Backstory, Motivation, Conflict, Relationship, Voice, Arc
  - **Plot** (6 prompts): Twist, Escalation, Subplot, Resolution, Foreshadowing, Pacing
  - **Setting** (6 prompts): Description, History, Atmosphere, Details, Culture, Contrast
  - **World** (6 prompts): Rules, Magic System, Politics, Economy, Religion, Conflicts

- **Integration Points**:
  - Toggle button di ChatHeader untuk show/hide panel
  - Quick prompts di empty state ChatWindow
  - Click prompt → insert to chat input → editable before send

- **System Prompts**: 16 brainstorming prompts seeded via PromptSeeder
  - Organized dengan folder structure: `Brainstorm / Category / Name`
  - Full system prompts dengan detailed instructions
  - Auto-available untuk semua users

### 2. Using Chat with Plan (F-04.4.2)
- **Outline Context Type**: New tab "Outline" di ContextSelector
- **Full Outline**: Add complete story outline as context
- **Chapter-specific**: Select individual chapters untuk focused discussion
- **Context Preview**: Show chapter/scene structure di preview
- **AI Awareness**: Chat responses reference outline context

**Data Flow**:
```
User clicks Outline tab → 
  Choose "Full Outline" or specific chapter → 
    Context added to thread → 
      AI responses consider story structure
```

### 3. Quick Prompts in Chat (F-04.4.3)
- **QuickPromptsDrawer**: Slide-up drawer untuk quick access
  - Recently used prompts (top 5 by usage_count)
  - System prompts section
  - Custom prompts section
  - Search functionality
  - Link to manage prompts page

- **Prompt Execution Integration**:
  - Header: PromptSelector emits `promptSelected` event
  - Input: "Prompts" button near Send button
  - Execution: PromptExecutionWrapper handles prompts with inputs
  - Auto-send: Resolved prompt messages dikirim ke chat

- **Dual Access Points**:
  - ChatHeader: Full PromptSelector for browsing all prompts
  - ChatInput: Quick access untuk recent/favorite prompts

---

## 📁 File Structure

### Frontend - New Files

```
resources/js/
├── components/chat/
│   ├── BrainstormingPanel.vue          ✨ NEW - Categorized brainstorming prompts
│   └── QuickPromptsDrawer.vue          ✨ NEW - Quick access to saved prompts
```

### Frontend - Updated Files

```
resources/js/
├── components/chat/
│   ├── ChatHeader.vue                  ✏️ UPDATED - Brainstorm toggle, prompt event
│   ├── ChatInput.vue                   ✏️ UPDATED - Quick prompts button
│   ├── ChatWindow.vue                  ✏️ UPDATED - Brainstorming panel integration
│   ├── ContextSelector.vue             ✏️ UPDATED - Outline tab dengan chapter selection
│   └── index.ts                        ✏️ UPDATED - Export new components
├── components/workspace/
│   └── ChatPanel.vue                   ✏️ UPDATED - Prompt execution wiring
```

### Backend - Updated Files

```
database/seeders/
└── PromptSeeder.php                    ✏️ UPDATED - 16 brainstorming prompts
```

---

## 🔌 Integration dengan Existing Features

### Prompt System Integration
- Menggunakan existing `PromptExecutionWrapper` untuk handle prompts dengan inputs
- Reuse `usePromptExecution` composable dari Sprint 27
- Leverage `PromptSelector` component yang sudah ada
- Compatible dengan Personas & Presets dari Sprint 26

### Context System Integration
- Extend existing context types: `scene`, `codex`, `summary`, `outline`, `custom`
- Reuse `useChatContext` composable infrastructure
- Compatible dengan existing context preview dan management
- Backend `ContextBuilder` siap untuk outline context (implementation via ChatContextController)

---

## 🎯 Key Technical Decisions

### 1. Brainstorming Panel Architecture
- **Component-based**: Reusable categories dengan config array
- **No API calls**: Prompts dari seeder, load via PromptSelector
- **Color-coded**: Each category punya warna untuk visual grouping
- **Mobile-responsive**: Slide-up drawer on mobile devices

### 2. Outline Context Structure
```typescript
interface ExtendedContextSources {
    // ... existing fields ...
    outline?: {
        chapters: Array<{
            id: number;
            title: string;
            tokens: number;
            scenes_count: number;
        }>;
    };
}
```

### 3. Quick Prompts Strategy
- **Usage tracking**: Sort by `usage_count` DESC untuk "recently used"
- **Lazy loading**: Prompts load saat drawer dibuka pertama kali
- **Search**: Client-side filter untuk fast response
- **Dual display**: System prompts separated dari custom prompts

---

## 📊 Business Rules

| Rule | Implementation | Validation |
|------|----------------|------------|
| BR-01: Brainstorming prompts are system prompts | Seeder dengan `is_system: true` | Cannot be edited/deleted by users |
| BR-02: Recent prompts show max 5 | Frontend filter by usage_count | Sort DESC, slice(0, 5) |
| BR-03: Outline context includes chapter structure | Backend ContextBuilder format | Token counting included |
| BR-04: Quick prompts drawer closes after selection | Event emission pattern | Auto-close on select |
| BR-05: Brainstorming folder structure | Name format: "Brainstorm / Category / Name" | Consistent separator |

---

## 🎨 UI/UX Highlights

### Brainstorming Panel
- **Empty State Integration**: Button di ChatWindow empty state
- **Category Grid**: 2x2 grid dengan icon, nama, count
- **Expandable**: Click category → see prompt list
- **Back Navigation**: Easy return to categories
- **Smooth Animations**: Motion-based transitions (200-250ms)

### Quick Prompts Drawer
- **Bottom-anchored**: Muncul dari bawah input area
- **Compact Design**: Width 320px, max-height 256px
- **Section Separation**: Recent, System, Custom dengan visual dividers
- **Search Bar**: Filter prompts by name/description
- **Footer Link**: Quick access ke Prompts management page

### Outline Context Tab
- **Full Outline Option**: Prominent button dengan icon
- **Chapter List**: Scrollable list dengan scene count dan tokens
- **Added Indicator**: Green checkmark untuk items sudah ditambahkan
- **Disabled State**: Prevent duplicate additions
- **Helper Tip**: Explanation tentang benefit menggunakan outline

---

## 🔄 User Workflows

### Workflow 1: Character Brainstorming
1. User buka Chat Panel
2. Click "Brainstorm" button di header atau empty state
3. Select "Character" category
4. Click "Explore motivations" prompt
5. Prompt inserted ke chat input (editable)
6. Send → AI responds dengan character motivation ideas

### Workflow 2: Chat with Story Plan
1. User sudah punya outline/chapters
2. Open Chat Panel
3. Click Context (+) button
4. Switch ke "Outline" tab
5. Select "Chapter 3" atau "Full Story Outline"
6. Ask: "How can I improve pacing in this chapter?"
7. AI responds dengan context-aware suggestions

### Workflow 3: Quick Saved Prompt Access
1. User punya custom chat prompts di library
2. Mid-conversation di chat
3. Click "Prompts" button near Send
4. Quick drawer muncul dengan recently used
5. Click saved prompt → executes immediately
6. If prompt has inputs → form appears → fill → send
7. AI responds sesuai prompt instructions

---

## 📊 Statistics & Metrics

### Brainstorming Prompts Count
- **Total**: 16 system prompts
- **Character**: 4 prompts
- **Plot**: 4 prompts
- **Setting**: 4 prompts
- **World**: 4 prompts

### Component Additions
- **New Components**: 2 (BrainstormingPanel, QuickPromptsDrawer)
- **Updated Components**: 6 (ChatHeader, ChatInput, ChatWindow, ContextSelector, ChatPanel, index.ts)
- **Lines Added**: ~800 lines (estimated)

---

## 🔗 Related Documentation

- **API Reference:** [Chat API](../04-api-reference/chat.md)
- **Testing Guide:** [Chat Testing](../06-testing/chat-testing.md)
- **User Journeys:** [Chat Uses Flow](../07-user-journeys/chat/)
- **Related Sprints:** 
  - [Sprint 20 - Chat Interface Core](sprint-20-chat-interface-core.md)
  - [Sprint 21 - Chat Context Integration](sprint-21-chat-context-integration.md)
  - [Sprint 27 - Prompt Advanced Features](sprint-27-prompt-advanced-features.md)

---

## 🚀 Future Enhancements (Not in This Sprint)

- [ ] Custom brainstorming categories per user
- [ ] Favorite prompts system untuk quick access
- [ ] Brainstorming history/suggestions based on usage
- [ ] Act-level outline context selection
- [ ] Outline context with auto-detected relevant chapters

---

*Last Updated: 2026-01-04*
