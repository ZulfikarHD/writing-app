# 🧪 Testing Guide: Workshop Chat Uses & Workflows (Sprint 29)

**Feature:** FG-04.4 - Uses & Workflows  
**Sprint:** Sprint 29  
**Last Updated:** 2026-01-04

---

## 📋 Pre-Test Setup

### Prerequisites
- [ ] User logged in
- [ ] Novel created dengan scenes
- [ ] Chat threads exist
- [ ] Brainstorming prompts seeded
- [ ] Story outline/chapters exist (untuk outline context test)

### Test Data Preparation
```bash
# Seed brainstorming prompts
php artisan db:seed --class=PromptSeeder

# Verify prompts created
php artisan tinker --execute="echo App\Models\Prompt::where('is_system', true)->where('name', 'like', 'Brainstorm%')->count();"
# Should output: 16
```

---

## ✅ Feature Checklist

### F-04.4.1: Brainstorming Tools

#### BrainstormingPanel Display
- [ ] Empty state chat shows "Open Brainstorming Panel" button
- [ ] Click button: Panel slides in dengan animation
- [ ] Panel shows 4 category cards: Character, Plot, Setting, World
- [ ] Each category shows icon, name, dan prompt count
- [ ] Categories color-coded (Character: violet, Plot: blue, Setting: emerald, World: amber)
- [ ] Close button di header works

#### Category Navigation
- [ ] Click Character category: Shows 6 prompts
- [ ] Each prompt shows label dan preview text
- [ ] Back button returns to category grid
- [ ] Navigation smooth dengan animation

#### Prompt Selection
- [ ] Click prompt: Text inserted ke chat input
- [ ] Panel closes after selection
- [ ] Input field editable sebelum send
- [ ] Prompt text correct dan complete

#### Integration Points
- [ ] Brainstorm toggle di ChatHeader works
- [ ] Toggle state synced dengan panel visibility
- [ ] Button highlighted when panel active
- [ ] Panel visible below header when open

---

### F-04.4.2: Using Chat with Plan

#### Outline Context Tab
- [ ] ContextSelector shows "Outline" tab
- [ ] Tab accessible via click
- [ ] Tab highlight correct when active

#### Full Outline Option
- [ ] "Full Story Outline" button visible
- [ ] Button shows icon dan description
- [ ] Click adds outline as context
- [ ] Button disabled after added (dengan checkmark)
- [ ] Context badge shows "Story Outline"

#### Chapter Selection
- [ ] Chapter list visible below full outline option
- [ ] Each chapter shows title, scene count, token estimate
- [ ] Click chapter: Added as context
- [ ] Added chapters show checkmark
- [ ] Multiple chapters can be added
- [ ] Context badge shows chapter name

#### Context Preview
- [ ] Preview modal shows outline items
- [ ] Outline preview shows chapter structure
- [ ] Chapter preview shows scenes list
- [ ] Token count accurate
- [ ] Toggle outline context on/off works
- [ ] Remove outline context works

#### AI Integration
- [ ] Send message dengan outline context: AI references outline
- [ ] Chapter-specific questions: AI focuses pada chapter tersebut
- [ ] Token usage calculated correctly

---

### F-04.4.3: Quick Prompts in Chat

#### QuickPromptsDrawer - ChatInput
- [ ] "Prompts" button visible near Send button
- [ ] Button shows icon dan text (responsive)
- [ ] Click button: Drawer slides up from bottom
- [ ] Drawer width 320px, max-height 256px

#### Drawer Contents
- [ ] Search bar functional
- [ ] Recently used section shows top 5
- [ ] Recently used sorted by usage_count DESC
- [ ] System prompts section separated
- [ ] Custom prompts section separated
- [ ] Empty state shown jika no prompts
- [ ] Footer link to Prompts page works

#### Prompt Selection from Drawer
- [ ] Click prompt: Drawer closes
- [ ] Prompt without inputs: Message appears in input
- [ ] Prompt with inputs: InputForm modal opens
- [ ] Fill inputs → Send: Resolved prompt sent to chat
- [ ] Usage count incremented after selection

#### PromptSelector Integration - ChatHeader
- [ ] PromptSelector visible di header
- [ ] Select prompt: Triggers execution
- [ ] Prompt dengan inputs: Shows input form
- [ ] Prompt without inputs: Auto-sends to chat
- [ ] Selection cleared after execution (reusable)

#### Backdrop & Close
- [ ] Click backdrop: Drawer closes
- [ ] Click close button: Drawer closes
- [ ] Drawer closes after selection
- [ ] No drawer multiple instances

---

## 🔧 Manual Testing Procedures

### Test Case: TC-WC-01 - Brainstorming Character Development

**Objective:** Use brainstorming tools untuk develop karakter

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Open workspace, go to Chat panel | Chat panel loads | ☐ |
| 2 | Ensure empty state visible | "Open Brainstorming Panel" button visible | ☐ |
| 3 | Click brainstorming button | Panel slides in smoothly | ☐ |
| 4 | Click "Character" category (violet) | 6 character prompts shown | ☐ |
| 5 | Review prompts: Backstory, Motivation, Conflict, etc. | All 6 prompts visible dengan preview | ☐ |
| 6 | Click "Explore motivations" | Prompt text inserted to input | ☐ |
| 7 | Panel closes | Chat input focused dengan prompt text | ☐ |
| 8 | Edit prompt text jika needed | Text editable | ☐ |
| 9 | Send message | AI responds dengan character motivation ideas | ☐ |

**Expected AI Response Content:**
- Discusses character desires, fears, goals
- Asks probing questions about character psychology
- Provides specific motivation examples

---

### Test Case: TC-WC-02 - Chat with Outline Context

**Objective:** Add story outline sebagai context untuk planning discussion

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Open chat dengan existing thread | Chat messages visible | ☐ |
| 2 | Click Context (+) button | ContextSelector dropdown opens | ☐ |
| 3 | Click "Outline" tab | Outline options visible | ☐ |
| 4 | See "Full Story Outline" button | Button prominent dengan icon | ☐ |
| 5 | See chapter list below | Chapters listed dengan scene counts | ☐ |
| 6 | Click "Chapter 3: The Confrontation" | Context added | ☐ |
| 7 | Context badge appears | "Chapter 3: The Confrontation" badge shown | ☐ |
| 8 | Type: "How can I improve pacing here?" | Message sent dengan context | ☐ |
| 9 | AI responds | Response references Chapter 3 structure | ☐ |
| 10 | Click context badge | Context preview opens | ☐ |
| 11 | Preview shows chapter scenes | Scene list visible | ☐ |

**Expected AI Response Quality:**
- References specific chapter name
- Discusses pacing relative to outline
- Suggests scene-level improvements

---

### Test Case: TC-WC-03 - Quick Prompts Access

**Objective:** Quick access ke saved prompts dari chat input

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Open chat panel | Chat loaded | ☐ |
| 2 | Locate "Prompts" button near Send | Button visible dengan icon | ☐ |
| 3 | Click "Prompts" button | QuickPromptsDrawer slides up | ☐ |
| 4 | Search bar visible | Search input functional | ☐ |
| 5 | "Recently Used" section shown | Top 5 prompts by usage | ☐ |
| 6 | Type "character" di search | Filtered results shown | ☐ |
| 7 | Click system prompt | Drawer closes, prompt text in input | ☐ |
| 8 | Clear input, reopen drawer | Drawer reopens | ☐ |
| 9 | Select prompt dengan inputs | InputForm modal opens | ☐ |
| 10 | Fill inputs → Submit | Resolved prompt sent to chat | ☐ |
| 11 | Click "Manage Prompts" footer link | Navigates to /prompts page | ☐ |

---

### Test Case: TC-WC-04 - Header Prompt Execution

**Objective:** Execute saved prompts dari ChatHeader

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Locate PromptSelector di chat header | Selector visible (width 192px) | ☐ |
| 2 | Click selector dropdown | Prompts list opens | ☐ |
| 3 | Browse system dan custom prompts | Both sections visible | ☐ |
| 4 | Select prompt WITHOUT inputs | Prompt executes, message sent | ☐ |
| 5 | Select prompt WITH inputs | InputForm modal appears | ☐ |
| 6 | Fill inputs dengan test values | All inputs fillable | ☐ |
| 7 | Submit input form | Resolved prompt sent to chat | ☐ |
| 8 | AI responds | Response follows prompt instructions | ☐ |
| 9 | PromptSelector selection cleared | Can select same prompt again | ☐ |

---

## 🐛 Edge Cases & Error Handling

| Scenario | Expected Behavior | Test Result |
|----------|-------------------|-------------|
| No outline exists | Outline tab shows "No outline available" message | ☐ |
| Empty brainstorm category | Category shows 0 prompts, tidak error | ☐ |
| Quick prompts drawer: no chat prompts | "No chat prompts available" message | ☐ |
| Select prompt saat streaming | Button disabled, cannot select | ☐ |
| Outline context: very large chapter | Token warning shown, can still add | ☐ |
| Quick prompts: all prompts used recently | Recently used section maxes at 5 | ☐ |
| Brainstorm toggle saat panel open | Panel closes smoothly | ☐ |
| Multiple context types active | All work together without conflict | ☐ |
| Click backdrop: drawers close | All drawers respond to backdrop | ☐ |
| Keyboard shortcuts | Enter sends, Esc closes drawers | ☐ |

---

## 📱 Mobile Testing Checklist

### Responsiveness
- [ ] Brainstorming panel: Full-screen on mobile
- [ ] Category grid: Stacks vertically on narrow screens
- [ ] Quick prompts drawer: Bottom sheet behavior
- [ ] Context selector: Touch-friendly tabs
- [ ] Outline list: Scrollable dengan touch
- [ ] Button labels: Hide text, show icons only on mobile
- [ ] All drawers: Swipe-to-close support (optional)

### Touch Interactions
- [ ] Tap brainstorm category: Opens smoothly
- [ ] Tap prompt: Inserts to input
- [ ] Long-press: No unwanted menus
- [ ] Scroll brainstorm prompts: Smooth scrolling
- [ ] Pinch-zoom: Disabled pada drawers

---

## 🔒 Security Testing

- [ ] Outline context: Only accessible novels
- [ ] Brainstorming prompts: Cannot be edited by users
- [ ] System prompts: Cannot be deleted
- [ ] Context injection: Sanitized before AI call
- [ ] CSRF protection: All context additions protected

---

## ⚡ Performance Testing

- [ ] Open brainstorming panel: < 100ms
- [ ] Quick prompts drawer open: < 150ms
- [ ] Outline tab load: < 300ms (depends on novel size)
- [ ] Category expand: < 50ms
- [ ] Prompt selection: Instant feedback
- [ ] Drawer animations: 60 FPS smooth

---

## ✅ Quick Verification Checklist

Before marking sprint as complete:

### Core Functionality
- [ ] Brainstorming panel accessible dan functional
- [ ] All 4 categories work dengan prompts
- [ ] Outline context type works end-to-end
- [ ] Quick prompts drawer shows dan executes prompts
- [ ] Header PromptSelector executes correctly
- [ ] Prompt execution with inputs works in chat

### UI/UX Polish
- [ ] All animations smooth (motion library)
- [ ] Color coding consistent
- [ ] Icons meaningful dan recognizable
- [ ] Empty states informative
- [ ] Loading states shown appropriately
- [ ] Error messages helpful

### Integration
- [ ] Works dengan existing chat features
- [ ] Compatible dengan context system
- [ ] PromptExecutionWrapper integration seamless
- [ ] No conflicts dengan other panels/drawers

### Mobile Experience
- [ ] Responsive layout works
- [ ] Touch targets adequate size (44x44px minimum)
- [ ] Drawers slide correctly
- [ ] No horizontal scroll issues

---

## 🔗 Related Documentation

- **Sprint Doc:** [Sprint 29 - Chat Uses & Workflows](../10-sprints/sprint-29-chat-uses-workflows.md)
- **API Reference:** [Chat API](../04-api-reference/chat.md)
- **Feature Spec:** `scrum/epic-planning/04-EPIC-workshop-chat.md`
- **Reference:** [Novelcrafter Chat Uses](https://www.novelcrafter.com/help/docs/chat/uses-for-chat)

---

*Last Updated: 2026-01-04*
