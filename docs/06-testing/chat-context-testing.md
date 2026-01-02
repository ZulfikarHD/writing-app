# Test Plan: Chat Context Integration

**Feature:** FG-04.2 Context & Integration  
**Sprint:** 21  
**Status:** ✅ Complete  
**Last Updated:** 2026-01-02

---

## Overview

Test plan untuk Chat Context Integration feature yang memungkinkan penulis untuk inject novel context (scenes, codex entries, custom text) ke dalam AI conversations dengan token counting dan limit warnings.

---

## Automated Tests

### Unit Tests: TokenCounterService

**File:** `tests/Unit/TokenCounterServiceTest.php`  
**Status:** ✅ 13 tests passing

| Test ID | Scenario | Expected Behavior | Status |
|---------|----------|-------------------|--------|
| TC-U-001 | Estimate tokens for empty string | Returns 0 | ✅ Pass |
| TC-U-002 | Estimate tokens using char approximation | Returns ceil(length / 4) | ✅ Pass |
| TC-U-003 | Estimate tokens for longer text | Accurate estimation | ✅ Pass |
| TC-U-004 | Rounds up token estimate | Always ceil, never floor | ✅ Pass |
| TC-U-005 | Get model limit for known model | Returns correct limit for gpt-4o | ✅ Pass |
| TC-U-006 | Get model limit for Claude model | Returns 200000 for Claude | ✅ Pass |
| TC-U-007 | Get model limit for partial match | Matches "gpt-4" in "gpt-4-turbo" | ✅ Pass |
| TC-U-008 | Get model limit for unknown model | Returns default 8192 | ✅ Pass |
| TC-U-009 | Handles multibyte characters | Correctly counts UTF-8 chars | ✅ Pass |
| TC-U-010 | Extract text from TipTap JSON | Parses editor content | ✅ Pass |
| TC-U-011 | Extract text from plain string | Handles plain text | ✅ Pass |
| TC-U-012 | Truncates long content | Adds "..." at max length | ✅ Pass |
| TC-U-013 | Handles null content | Returns empty string | ✅ Pass |

**Run Command:**
```bash
php artisan test --filter=TokenCounterServiceTest
```

---

### Feature Tests: ChatContextController

**File:** `tests/Feature/ChatContextControllerTest.php`  
**Status:** ✅ 19 tests passing

| Test ID | Scenario | Expected Behavior | Status |
|---------|----------|-------------------|--------|
| TC-F-001 | User can list context items | Returns items with tokens and limit info | ✅ Pass |
| TC-F-002 | User can add scene context | Creates context item referencing scene | ✅ Pass |
| TC-F-003 | User can add codex context | Creates context item referencing codex | ✅ Pass |
| TC-F-004 | User can add custom context | Creates context with custom_content | ✅ Pass |
| TC-F-005 | Cannot add scene from other novel | Returns 404 error | ✅ Pass |
| TC-F-006 | User can toggle context item | is_active toggles true/false | ✅ Pass |
| TC-F-007 | User can remove context item | Deletes permanently | ✅ Pass |
| TC-F-008 | User cannot access other users context | Returns 403 forbidden | ✅ Pass |
| TC-F-009 | User can clear all context | Deletes all items from thread | ✅ Pass |
| TC-F-010 | User can get context preview | Returns full text and token count | ✅ Pass |
| TC-F-011 | User can get context sources | Lists available scenes and codex | ✅ Pass |
| TC-F-012 | User can bulk add context | Creates multiple items at once | ✅ Pass |
| TC-F-013 | Adding same context twice reactivates existing | No duplicate created | ✅ Pass |
| TC-F-014 | Token info is returned with context operations | Always includes token counts | ✅ Pass |
| TC-F-015 | Limit info warns when near limit | usage_percentage calculated correctly | ✅ Pass |
| TC-F-016 | Unauthenticated user cannot access context | Returns 401 unauthorized | ✅ Pass |
| TC-F-017 | Validation requires context_type | Returns 422 validation error | ✅ Pass |
| TC-F-018 | Validation requires reference_id for scene type | Returns 422 validation error | ✅ Pass |
| TC-F-019 | Validation requires custom_content for custom type | Returns 422 validation error | ✅ Pass |

**Run Command:**
```bash
php artisan test --filter=ChatContextControllerTest
```

---

## Manual Testing Checklist

### Setup Requirements

- [ ] Database seeded with novel, chapters, scenes, codex entries
- [ ] AI connection configured (can be mock)
- [ ] User authenticated
- [ ] Chat thread created for testing

---

### Functional Testing

#### Context Selector Component

| TC-M-001 | **Open Context Selector** |
|----------|---------------------------|
| **Steps** | 1. Open chat thread<br>2. Click "Add Context" button in ChatInput |
| **Expected** | Modal opens showing tabs: Scenes, Codex, Custom |
| **Status** | ☐ |

| TC-M-002 | **Search Scenes** |
|----------|-------------------|
| **Steps** | 1. Open Context Selector<br>2. Stay on "Scenes" tab<br>3. Type scene title in search box |
| **Expected** | Scene list filters in real-time as you type |
| **Status** | ☐ |

| TC-M-003 | **Select Multiple Scenes** |
|----------|---------------------------|
| **Steps** | 1. Open Context Selector → Scenes tab<br>2. Click checkboxes for 3 different scenes<br>3. Click "Add Selected" button |
| **Expected** | Modal closes, 3 scene contexts added to thread, token badge updates |
| **Status** | ☐ |

| TC-M-004 | **Add Codex Entry** |
|----------|---------------------|
| **Steps** | 1. Open Context Selector<br>2. Switch to "Codex" tab<br>3. Search for character name<br>4. Select entry<br>5. Click "Add Selected" |
| **Expected** | Codex context added, token count shown |
| **Status** | ☐ |

| TC-M-005 | **Add Custom Context** |
|----------|------------------------|
| **Steps** | 1. Open Context Selector<br>2. Switch to "Custom" tab<br>3. Type instruction: "Focus on emotional depth"<br>4. Click "Add Custom" |
| **Expected** | Custom context created, shown in preview |
| **Status** | ☐ |

#### Context Preview Component

| TC-M-006 | **View Context Items** |
|----------|------------------------|
| **Steps** | 1. Add 3+ context items<br>2. Click token badge in ChatInput |
| **Expected** | Context Preview modal opens showing all items with names, types, tokens |
| **Status** | ☐ |

| TC-M-007 | **Toggle Context Item Off** |
|----------|----------------------------|
| **Steps** | 1. Open Context Preview<br>2. Click toggle switch on an item to disable |
| **Expected** | Item turns gray/inactive, total token count decreases, usage bar updates |
| **Status** | ☐ |

| TC-M-008 | **Toggle Context Item On** |
|----------|---------------------------|
| **Steps** | 1. With inactive item visible<br>2. Click toggle to re-enable |
| **Expected** | Item becomes active again, token count increases |
| **Status** | ☐ |

| TC-M-009 | **Remove Context Item** |
|----------|-------------------------|
| **Steps** | 1. Open Context Preview<br>2. Click X/remove button on an item |
| **Expected** | Confirmation prompt → Item deleted → Token count updates |
| **Status** | ☐ |

| TC-M-010 | **Token Usage Warning** |
|----------|-------------------------|
| **Steps** | 1. Add many large scenes until usage > 80%<br>2. Observe usage bar |
| **Expected** | Bar turns yellow/red, warning message shown |
| **Status** | ☐ |

| TC-M-011 | **Clear All Context** |
|----------|----------------------|
| **Steps** | 1. Open Context Preview with multiple items<br>2. Click "Clear All" button |
| **Expected** | Confirmation prompt → All items removed → Token count = 0 |
| **Status** | ☐ |

#### Context in Chat Flow

| TC-M-012 | **Context Included in AI Prompt** |
|----------|----------------------------------|
| **Steps** | 1. Add scene context with specific character name<br>2. Send message: "Tell me about [character]"<br>3. Check AI response |
| **Expected** | AI response references information from the scene context |
| **Status** | ☐ |

| TC-M-013 | **Inactive Context Not Included** |
|----------|----------------------------------|
| **Steps** | 1. Add scene context<br>2. Toggle it OFF<br>3. Send message asking about scene content |
| **Expected** | AI does not have access to disabled context |
| **Status** | ☐ |

| TC-M-014 | **Context Persists Across Messages** |
|----------|--------------------------------------|
| **Steps** | 1. Add 2 context items<br>2. Send message<br>3. Send follow-up message<br>4. Check both responses |
| **Expected** | Both AI responses have access to same context |
| **Status** | ☐ |

| TC-M-015 | **Token Badge Shows Count** |
|----------|----------------------------|
| **Steps** | 1. Add context items<br>2. Observe badge in ChatInput |
| **Expected** | Badge shows accurate token count (e.g., "2.5K tokens") |
| **Status** | ☐ |

---

### UI/UX Testing

#### Responsive Design

| TC-M-016 | **Desktop: Context Selector** |
|----------|------------------------------|
| **Device** | Desktop (1920x1080) |
| **Steps** | Open Context Selector, interact with all tabs |
| **Expected** | Modal centered, all content visible, no overflow |
| **Status** | ☐ |

| TC-M-017 | **Mobile: Context Selector** |
|----------|------------------------------|
| **Device** | Mobile (375x667) |
| **Steps** | Open Context Selector on phone |
| **Expected** | Modal fills screen, scrollable, touch-friendly buttons |
| **Status** | ☐ |

| TC-M-018 | **Tablet: Context Preview** |
|----------|----------------------------|
| **Device** | Tablet (768x1024) |
| **Steps** | Open Context Preview with many items |
| **Expected** | List scrollable, toggles work, no layout issues |
| **Status** | ☐ |

#### Loading States

| TC-M-019 | **Context Loading Indicator** |
|----------|------------------------------|
| **Steps** | 1. Open Context Selector<br>2. Observe initial load |
| **Expected** | Skeleton/spinner shown while fetching scenes and codex |
| **Status** | ☐ |

| TC-M-020 | **Add Context Loading** |
|----------|------------------------|
| **Steps** | 1. Select scenes<br>2. Click "Add Selected"<br>3. Observe |
| **Expected** | Button shows loading state, disabled during request |
| **Status** | ☐ |

#### Error Handling

| TC-M-021 | **Add Invalid Context** |
|----------|------------------------|
| **Steps** | 1. Try to add scene from different novel (via API manipulation) |
| **Expected** | Error toast: "Scene not found" |
| **Status** | ☐ |

| TC-M-022 | **Network Error** |
|----------|-------------------|
| **Steps** | 1. Disable network<br>2. Try to add context |
| **Expected** | Error toast: "Network error, please try again" |
| **Status** | ☐ |

| TC-M-023 | **Validation Error: Empty Custom** |
|----------|-----------------------------------|
| **Steps** | 1. Open Custom Context tab<br>2. Leave textarea empty<br>3. Click "Add Custom" |
| **Expected** | Validation error: "Content is required" |
| **Status** | ☐ |

#### Dark Mode

| TC-M-024 | **Context Selector Dark Mode** |
|----------|-------------------------------|
| **Steps** | 1. Enable dark mode<br>2. Open Context Selector |
| **Expected** | All colors inverted correctly, readable, no contrast issues |
| **Status** | ☐ |

| TC-M-025 | **Context Preview Dark Mode** |
|----------|------------------------------|
| **Steps** | 1. Enable dark mode<br>2. Open Context Preview |
| **Expected** | Token usage bar visible, items readable |
| **Status** | ☐ |

---

### Performance Testing

| TC-M-026 | **Large Scene Context** |
|----------|------------------------|
| **Setup** | Scene with 5000+ words |
| **Steps** | Add scene to context |
| **Expected** | Token count calculated < 1s, no UI freeze |
| **Status** | ☐ |

| TC-M-027 | **Bulk Add 20 Scenes** |
|----------|------------------------|
| **Steps** | Select 20 scenes, click "Add Selected" |
| **Expected** | All added within 2s, token counts accurate |
| **Status** | ☐ |

| TC-M-028 | **Context Preview with 50 Items** |
|----------|----------------------------------|
| **Steps** | Add 50 context items, open preview |
| **Expected** | List renders smoothly, scroll works, no lag |
| **Status** | ☐ |

---

### Edge Cases

| TC-M-029 | **Add Duplicate Context** |
|----------|--------------------------|
| **Steps** | 1. Add scene to context<br>2. Try to add same scene again |
| **Expected** | System reactivates existing item instead of creating duplicate |
| **Status** | ☐ |

| TC-M-030 | **Delete Referenced Scene** |
|----------|---------------------------|
| **Scenario** | User deletes a scene that's in context |
| **Expected** | Context item shows "Scene not found" or auto-removed |
| **Status** | ☐ |

| TC-M-031 | **Context Over Limit** |
|----------|----------------------|
| **Steps** | Add contexts until tokens > model limit (128K for GPT-4o) |
| **Expected** | Warning shown, "Add Selected" disabled or shows error |
| **Status** | ☐ |

| TC-M-032 | **Empty Novel (No Scenes)** |
|----------|---------------------------|
| **Setup** | Novel with no chapters/scenes |
| **Steps** | Open Context Selector → Scenes tab |
| **Expected** | Empty state: "No scenes yet. Create scenes in the manuscript editor." |
| **Status** | ☐ |

| TC-M-033 | **Empty Codex** |
|----------|----------------|
| **Setup** | Novel with no codex entries |
| **Steps** | Open Context Selector → Codex tab |
| **Expected** | Empty state: "No codex entries. Add entries in the Codex." |
| **Status** | ☐ |

---

## Integration Testing

### Chat with Context Flow

| Integration Test | Scenario |
|------------------|----------|
| **IT-001** | **Full Flow:** Create thread → Add scene context → Add codex context → Send message → AI response includes context → Toggle context off → Send new message → AI response without context |
| **IT-002** | **Context Persistence:** Add contexts → Close tab → Reopen thread → Context still attached |
| **IT-003** | **Multi-Tab Sync:** Open thread in 2 tabs → Add context in tab 1 → Tab 2 updates automatically (if polling/websocket enabled) |
| **IT-004** | **Token Limit Flow:** Add many contexts → Reach 80% limit → Warning shown → Try to add more → Prevented or warned |

---

## Regression Testing

After implementing context feature, verify these existing features still work:

- [ ] **Chat without context:** Send messages without any context attached
- [ ] **Thread creation:** Create new threads
- [ ] **Thread deletion:** Delete threads with context attached
- [ ] **Message streaming:** AI responses stream correctly
- [ ] **Model selection:** Different models work with context
- [ ] **Thread archiving:** Archive threads with context
- [ ] **Scene linking:** Threads linked to scenes still work

---

## Browser Compatibility

Test on:

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (macOS)
- [ ] Safari (iOS)
- [ ] Chrome (Android)

---

## Accessibility Testing

- [ ] **Keyboard Navigation:** Can open/close modals with keyboard
- [ ] **Screen Reader:** Context items announced correctly
- [ ] **Focus Management:** Focus trapped in modal when open
- [ ] **ARIA Labels:** All buttons have descriptive labels

---

## Related Documentation

- **API Reference:** [Chat Context API](../04-api-reference/chat.md#context-management-endpoints)
- **User Journeys:** [Context Integration Flow](../07-user-journeys/chat-context/)
- **Sprint Documentation:** [Sprint FG-04.2](../10-sprints/sprint-fg-04.2-context-integration.md)

---

*Last Updated: 2026-01-02*
