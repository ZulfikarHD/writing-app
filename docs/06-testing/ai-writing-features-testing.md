# Testing Guide: AI Writing Features

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Feature:** FG-06.2 - AI Writing Features  
**Status:** ✅ Complete

## Overview

Panduan testing untuk AI Writing Features yang mencakup prose generation dari beats, text replacement (expand/rephrase/shorten), dan slash commands integration.

---

## Pre-Testing Requirements

### Environment Setup

```bash
# Ensure AI connections configured
php artisan db:seed --class=PromptSeeder

# Verify routes registered
php artisan route:list --name=prose
php artisan route:list --name=text.replace

# Check frontend built
yarn build
```

### Test Data Requirements

- [ ] At least 1 active AI connection (OpenAI/Anthropic)
- [ ] Novel with scenes created
- [ ] Scene with content for testing
- [ ] Prompts seeded (prose + replacement)

---

## Manual Testing Checklist

### A. Prose Generation Panel

#### A1. Panel Visibility
- [ ] **TC-001:** Open scene editor → Type `/` → See slash commands menu
- [ ] **TC-002:** Slash menu shows AI commands at top (Scene Beat, Continue Writing, AI Custom)
- [ ] **TC-003:** Select "Scene Beat" → ProseGenerationPanel appears at bottom center
- [ ] **TC-004:** Panel is responsive on mobile (< 768px width)
- [ ] **TC-005:** Panel has close button (X icon)

#### A2. Beat Input
- [ ] **TC-010:** Beat textarea accepts input
- [ ] **TC-011:** Beat textarea shows placeholder text
- [ ] **TC-012:** Character count or no max length enforced (should accept up to 5000 chars)
- [ ] **TC-013:** Additional instructions input field works
- [ ] **TC-014:** Instructions field optional

#### A3. Mode Selection
- [ ] **TC-020:** Scene Beat mode pre-selected when opened via `/scene-beat`
- [ ] **TC-021:** Continue mode pre-selected when opened via `/continue`
- [ ] **TC-022:** Mode buttons toggle correctly
- [ ] **TC-023:** Mode change updates UI appropriately

#### A4. Prompt & Connection Selection
- [ ] **TC-030:** Click "Advanced options" → Shows prompt selector
- [ ] **TC-031:** Prompt dropdown shows system + user prompts
- [ ] **TC-032:** Default prompt pre-selected
- [ ] **TC-033:** Connection dropdown shows active connections
- [ ] **TC-034:** Default connection pre-selected
- [ ] **TC-035:** Can change prompt and connection

#### A5. Generation Process
- [ ] **TC-040:** Click "Generate" → Loading spinner appears
- [ ] **TC-041:** Generate button disabled when beat empty (for scene_beat mode)
- [ ] **TC-042:** Generate button shows loading state during generation
- [ ] **TC-043:** Streaming text appears character by character
- [ ] **TC-044:** Typing indicator (cursor pulse) visible during streaming
- [ ] **TC-045:** Can abort generation with "Stop" button
- [ ] **TC-046:** Keyboard shortcut Ctrl+Enter triggers generate

#### A6. Post-Generation Actions
- [ ] **TC-050:** "Apply" button inserts text at cursor position
- [ ] **TC-051:** "Retry" button clears and regenerates
- [ ] **TC-052:** "Discard" button clears generated text
- [ ] **TC-053:** "Section" dropdown shows section types (Content, Alternative, Note)
- [ ] **TC-054:** "Add to Section" creates new section with generated text
- [ ] **TC-055:** Undo (Ctrl+Z) works after applying generated text
- [ ] **TC-056:** Panel closes after Apply/Discard

#### A7. Error Handling
- [ ] **TC-060:** Shows error message when no AI connection
- [ ] **TC-061:** Shows error message when API returns error
- [ ] **TC-062:** Shows error message when network timeout
- [ ] **TC-063:** Error message is user-friendly
- [ ] **TC-064:** Can retry after error

---

### B. Text Replacement Menu

#### B1. Menu Trigger
- [ ] **TC-100:** Select less than 4 words → Menu doesn't appear
- [ ] **TC-101:** Select 4+ words → Menu appears above selection
- [ ] **TC-102:** Menu positioned correctly (centered above selection)
- [ ] **TC-103:** Menu follows selection on different parts of page
- [ ] **TC-104:** Click outside menu → Menu closes

#### B2. Quick Actions
- [ ] **TC-110:** "Expand" button shows dropdown with options
- [ ] **TC-111:** Expand dropdown shows: Slightly, Double, Triple
- [ ] **TC-112:** Expand dropdown shows methods: Sensory details, Inner thoughts, Description, Dialogue
- [ ] **TC-113:** "Rephrase" button shows dropdown with options
- [ ] **TC-114:** Rephrase options: Different words, Show don't tell, Add inner thoughts, etc.
- [ ] **TC-115:** "Shorten" button shows dropdown with options
- [ ] **TC-116:** Shorten options: Half, Quarter, Single Paragraph

#### B3. Transformation Process
- [ ] **TC-120:** Click transformation option → Preview panel opens (fullscreen modal)
- [ ] **TC-121:** Preview shows original text on left/top
- [ ] **TC-122:** Preview shows "Generating..." loading state
- [ ] **TC-123:** Transformed text streams in on right/bottom
- [ ] **TC-124:** Typing indicator visible during streaming
- [ ] **TC-125:** Can abort with "Stop" button
- [ ] **TC-126:** "Cancel" button closes preview

#### B4. Preview Actions
- [ ] **TC-130:** "Accept" button replaces original with transformed text
- [ ] **TC-131:** "Retry" button regenerates with same settings
- [ ] **TC-132:** Undo (Ctrl+Z) works after accepting replacement
- [ ] **TC-133:** Preview panel closes after Accept
- [ ] **TC-134:** Selection cleared after accept

#### B5. Error Handling
- [ ] **TC-140:** Shows error when no AI connection
- [ ] **TC-141:** Shows error when API fails
- [ ] **TC-142:** Error message is clear and actionable
- [ ] **TC-143:** Can retry after error

---

### C. Slash Commands Integration

#### C1. Command List
- [ ] **TC-200:** Type `/` → Slash commands menu appears
- [ ] **TC-201:** AI commands appear at top of list (purple/violet icons)
- [ ] **TC-202:** AI commands have category indicator
- [ ] **TC-203:** Can filter commands by typing (e.g., `/scene`)
- [ ] **TC-204:** Arrow keys navigate command list
- [ ] **TC-205:** Enter key selects command
- [ ] **TC-206:** Escape key closes menu

#### C2. Command Execution
- [ ] **TC-210:** `/` + "Scene Beat" → Opens ProseGenerationPanel in scene_beat mode
- [ ] **TC-211:** `/` + "Continue Writing" → Opens ProseGenerationPanel in continue mode
- [ ] **TC-212:** `/` + "AI Custom" → Opens ProseGenerationPanel in custom mode
- [ ] **TC-213:** Command clears the `/` character
- [ ] **TC-214:** Focus stays in editor after command

---

### D. Context Building

#### D1. Scene Context
- [ ] **TC-300:** Generation includes content before cursor
- [ ] **TC-301:** Generation respects scene summary/beats
- [ ] **TC-302:** Generation uses scene POV if set
- [ ] **TC-303:** Generation maintains novel genre/style

#### D2. Codex Integration
- [ ] **TC-310:** Mentioned codex entries included in context
- [ ] **TC-311:** Only AI-visible codex entries used
- [ ] **TC-312:** Character details reflected in generated prose
- [ ] **TC-313:** World-building details reflected in output

---

### E. Performance Testing

#### E1. Load Time
- [ ] **TC-400:** Panel opens < 100ms
- [ ] **TC-401:** Slash menu appears < 50ms
- [ ] **TC-402:** First content chunk arrives < 3s
- [ ] **TC-403:** Streaming doesn't block UI

#### E2. Large Content
- [ ] **TC-410:** Can generate with 5000 char beat
- [ ] **TC-411:** Can replace 50000 char selection (max)
- [ ] **TC-412:** Long generation doesn't freeze browser
- [ ] **TC-413:** Can abort long generation

---

### F. Mobile Responsiveness

#### F1. ProseGeneration Panel (Mobile)
- [ ] **TC-500:** Panel scales to mobile width
- [ ] **TC-501:** Beat textarea usable on mobile
- [ ] **TC-502:** Buttons have touch-friendly size (min 44px)
- [ ] **TC-503:** Dropdown menus work on touch
- [ ] **TC-504:** Panel doesn't overflow viewport

#### F2. TextReplacement Menu (Mobile)
- [ ] **TC-510:** Selection menu appears on mobile
- [ ] **TC-511:** Menu positioned correctly on small screens
- [ ] **TC-512:** Buttons large enough for touch
- [ ] **TC-513:** Preview modal fullscreen on mobile
- [ ] **TC-514:** Can scroll preview content on mobile

---

### G. Integration Testing

#### G1. Editor Integration
- [ ] **TC-600:** Generated text maintains editor formatting
- [ ] **TC-601:** Replaced text maintains surrounding formatting
- [ ] **TC-602:** Undo/redo history preserved
- [ ] **TC-603:** Word count updates after generation
- [ ] **TC-604:** Autosave triggered after text insertion

#### G2. Multi-User Scenarios
- [ ] **TC-610:** Different users see their own AI connections
- [ ] **TC-611:** Different users see their own custom prompts
- [ ] **TC-612:** System prompts visible to all users
- [ ] **TC-613:** No connection leaked between users

---

## Automated Test Coverage

### Backend Tests

```bash
php artisan test --filter=ProseGenerationTest
php artisan test --filter=TextReplacementTest
```

| Test Class | Test Cases | Coverage |
|------------|------------|----------|
| ProseGenerationServiceTest | 12 | Service methods, context building, streaming |
| TextReplacementServiceTest | 15 | Transform methods, validation, options |
| ProseGenerationControllerTest | 8 | Endpoints, authorization, SSE streaming |
| TextReplacementControllerTest | 10 | Endpoints, validation, error handling |

### Frontend Tests (if applicable)

```bash
yarn test components/editor/ProseGenerationPanel
yarn test components/editor/TextReplacementMenu
yarn test composables/useProseGeneration
yarn test composables/useTextReplacement
```

---

## Bug Report Template

```markdown
**Test Case:** TC-XXX
**Environment:** Browser + OS
**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**

**Actual Result:**

**Screenshots/Video:**

**Console Errors:**

**Additional Context:**
```

---

## Known Issues & Limitations

| Issue ID | Description | Workaround | Priority |
|----------|-------------|------------|----------|
| - | - | - | - |

---

## Test Sign-Off

| Role | Name | Date | Status |
|------|------|------|--------|
| Developer | [Name] | YYYY-MM-DD | ✅ Passed |
| QA | [Name] | YYYY-MM-DD | ⏳ Pending |
| Product Owner | [Name] | YYYY-MM-DD | ⏳ Pending |

---

## Related Documentation

- **API Reference:** [AI Writing Features API](../04-api-reference/ai-writing-features.md)
- **User Journeys:** [AI Writing Features Flows](../07-user-journeys/ai-writing-features/)
- **Sprint Documentation:** [Sprint 31: AI Writing Features](../10-sprints/sprint-31-ai-writing-features.md)

---

*Last Updated: 2026-01-04*
