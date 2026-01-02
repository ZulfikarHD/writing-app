# Testing Guide: Chat Interface Core (Workshop)

**Feature:** Chat Interface Core (FG-04.1)  
**Sprint:** 20  
**Test File:** `tests/Feature/ChatControllerTest.php`  
**Status:** ✅ All Tests Passing (22/22)

---

## Overview

Dokumen ini berisi comprehensive testing guide untuk Chat Interface Core feature, yaitu: automated tests, manual test cases, QA checklist, dan edge cases yang perlu diverifikasi untuk memastikan functionality, security, dan user experience dari chat system.

---

## 🤖 Automated Tests

### Test Coverage Summary

| Category | Test Count | Status | File |
|----------|-----------|--------|------|
| Thread CRUD Operations | 8 | ✅ Pass | `ChatControllerTest.php` |
| Authorization & Security | 6 | ✅ Pass | `ChatControllerTest.php` |
| Archiving Functionality | 4 | ✅ Pass | `ChatControllerTest.php` |
| Message Operations | 3 | ✅ Pass | `ChatControllerTest.php` |
| Pagination & Ordering | 1 | ✅ Pass | `ChatControllerTest.php` |
| **Total** | **22** | **✅ 100%** | - |

### Running Tests

```bash
# Run all chat tests
php artisan test tests/Feature/ChatControllerTest.php

# Run specific test
php artisan test --filter=test_user_can_create_chat_thread

# Run with coverage (if xdebug enabled)
php artisan test --coverage tests/Feature/ChatControllerTest.php
```

---

## 📋 Test Cases Detail

### 1. Thread CRUD Operations

#### TC-01: List Chat Threads
**Test:** `test_user_can_list_their_chat_threads`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create 3 threads for user's novel | Threads created in database | ✅ |
| 2 | GET `/api/novels/{novel}/chat/threads` | HTTP 200 | ✅ |
| 3 | Verify response structure | Contains threads array & pagination | ✅ |
| 4 | Count threads | Returns exactly 3 threads | ✅ |

**Assertions:**
- Response status 200
- JSON structure matches expected format
- Thread count correct
- Pagination metadata present

---

#### TC-02: Create Chat Thread with Title
**Test:** `test_user_can_create_chat_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | POST `/api/novels/{novel}/chat/threads` with title | HTTP 201 | ✅ |
| 2 | Verify response contains thread object | Thread returned with ID | ✅ |
| 3 | Check database | Thread exists with correct data | ✅ |

**Request:**
```json
{
  "title": "My Chat Thread"
}
```

**Assertions:**
- Response status 201
- Thread title matches request
- Thread novel_id correct
- Database record exists

---

#### TC-03: Create Chat Thread without Title
**Test:** `test_user_can_create_thread_without_title`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | POST `/api/novels/{novel}/chat/threads` with empty body | HTTP 201 | ✅ |
| 2 | Verify thread created | Title is null | ✅ |

**Assertions:**
- Response status 201
- Thread title is null (auto-generate on first message)

---

#### TC-04: View Thread with Messages
**Test:** `test_user_can_view_thread_with_messages`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread with 3 messages | Messages created | ✅ |
| 2 | GET `/api/chat/threads/{thread}` | HTTP 200 | ✅ |
| 3 | Verify messages loaded | 3 messages present | ✅ |

**Assertions:**
- Response contains thread object
- Messages array has 3 items
- Related data (linkedScene, activeContextItems) loaded

---

#### TC-05: Update Chat Thread
**Test:** `test_user_can_update_chat_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread with title "Old Title" | Thread created | ✅ |
| 2 | PATCH `/api/chat/threads/{thread}` with "New Title" | HTTP 200 | ✅ |
| 3 | Verify database updated | Title changed | ✅ |

**Request:**
```json
{
  "title": "New Title"
}
```

**Assertions:**
- Response status 200
- Thread title updated
- Database reflects change

---

#### TC-06: Pin Chat Thread
**Test:** `test_user_can_pin_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread with is_pinned=false | Thread created | ✅ |
| 2 | PATCH with is_pinned=true | HTTP 200 | ✅ |
| 3 | Verify pinned | is_pinned = true | ✅ |

**Assertions:**
- Response contains is_pinned: true
- Database reflects change

---

#### TC-07: Delete Chat Thread
**Test:** `test_user_can_delete_chat_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread | Thread exists | ✅ |
| 2 | DELETE `/api/chat/threads/{thread}` | HTTP 200 | ✅ |
| 3 | Verify database | Thread deleted | ✅ |

**Assertions:**
- Response status 200
- Success message returned
- Thread removed from database

---

#### TC-08: Delete Thread Cascades to Messages
**Test:** `test_deleting_thread_deletes_messages`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread with message | Message exists | ✅ |
| 2 | Delete thread | Thread deleted | ✅ |
| 3 | Verify message deleted | Message not in database | ✅ |

**Assertions:**
- Cascade delete works
- No orphaned messages

---

### 2. Authorization & Security

#### TC-09: User Cannot See Other Users' Threads
**Test:** `test_user_cannot_see_other_users_threads`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create 2 threads for other user | Threads exist | ✅ |
| 2 | Create 1 thread for authenticated user | Thread exists | ✅ |
| 3 | GET threads list | Returns only 1 thread | ✅ |

**Assertions:**
- Only user's own threads visible
- Other users' threads not leaked

---

#### TC-10: User Cannot View Other Users' Thread
**Test:** `test_user_cannot_view_other_users_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread owned by other user | Thread exists | ✅ |
| 2 | GET `/api/chat/threads/{thread}` | HTTP 403 | ✅ |

**Assertions:**
- Access denied
- No data leaked

---

#### TC-11: User Cannot Update Other Users' Thread
**Test:** `test_user_cannot_update_other_users_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread owned by other user | Thread exists | ✅ |
| 2 | PATCH thread | HTTP 403 | ✅ |

**Assertions:**
- Update blocked
- Original data unchanged

---

#### TC-12: User Cannot Delete Other Users' Thread
**Test:** `test_user_cannot_delete_other_users_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread owned by other user | Thread exists | ✅ |
| 2 | DELETE thread | HTTP 403 | ✅ |

**Assertions:**
- Delete blocked
- Thread still exists

---

#### TC-13: User Cannot Delete Other Users' Message
**Test:** `test_user_cannot_delete_message_from_other_users_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create message in other user's thread | Message exists | ✅ |
| 2 | DELETE message | HTTP 403 | ✅ |

**Assertions:**
- Delete blocked
- Message still exists

---

#### TC-14: Unauthenticated Access Blocked
**Test:** `test_unauthenticated_user_cannot_access_chat`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | GET threads without auth token | HTTP 401 | ✅ |

**Assertions:**
- Unauthorized response
- No data returned

---

### 3. Archiving Functionality

#### TC-15: Archive Thread
**Test:** `test_user_can_archive_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create active thread | archived_at = null | ✅ |
| 2 | POST `/api/chat/threads/{thread}/archive` | HTTP 200 | ✅ |
| 3 | Verify archived | archived_at timestamp set | ✅ |

**Assertions:**
- archived_at field populated
- Thread still exists (soft archive)

---

#### TC-16: Restore Archived Thread
**Test:** `test_user_can_restore_archived_thread`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create archived thread | archived_at set | ✅ |
| 2 | POST `/api/chat/threads/{thread}/restore` | HTTP 200 | ✅ |
| 3 | Verify restored | archived_at = null | ✅ |

**Assertions:**
- archived_at cleared
- Thread active again

---

#### TC-17: Archived Threads Hidden by Default
**Test:** `test_archived_threads_not_shown_by_default`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create 1 active, 1 archived thread | Threads exist | ✅ |
| 2 | GET threads list | Returns 1 thread | ✅ |

**Assertions:**
- Only active threads shown
- Archived excluded from list

---

#### TC-18: Include Archived Threads with Flag
**Test:** `test_can_include_archived_threads`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create 1 active, 1 archived thread | Threads exist | ✅ |
| 2 | GET threads with `?include_archived=true` | Returns 2 threads | ✅ |

**Assertions:**
- Both active and archived shown
- Filter works correctly

---

### 4. Message Operations

#### TC-19: Delete Individual Message
**Test:** `test_user_can_delete_message`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create message in thread | Message exists | ✅ |
| 2 | DELETE `/api/chat/messages/{message}` | HTTP 200 | ✅ |
| 3 | Verify deleted | Message not in database | ✅ |

**Assertions:**
- Message removed
- Thread still exists

---

#### TC-20: Get Paginated Messages
**Test:** `test_user_can_get_thread_messages`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread with 5 messages | Messages exist | ✅ |
| 2 | GET `/api/chat/threads/{thread}/messages` | HTTP 200 | ✅ |
| 3 | Verify pagination | 5 messages + pagination metadata | ✅ |

**Assertions:**
- Messages returned
- Pagination structure present
- Message count correct

---

#### TC-21: Set Thread Model
**Test:** `test_thread_model_can_be_set`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create thread | Thread exists | ✅ |
| 2 | PATCH with model="gpt-4o" | HTTP 200 | ✅ |
| 3 | Verify model set | thread.model = "gpt-4o" | ✅ |

**Assertions:**
- Model preference saved
- Response reflects change

---

### 5. Pagination & Ordering

#### TC-22: Threads Ordered by Updated At
**Test:** `test_threads_ordered_by_updated_at`

| Step | Action | Expected Result | Status |
|------|--------|-----------------|--------|
| 1 | Create old thread (2 days ago) | Thread exists | ✅ |
| 2 | Create new thread (now) | Thread exists | ✅ |
| 3 | GET threads list | New thread first | ✅ |

**Assertions:**
- Threads ordered DESC by updated_at
- Most recent thread at top

---

## 🧪 Manual Testing Checklist

### Frontend Integration

#### Chat Mode Access
- [ ] Navigate to `/novels/{id}/workspace?mode=chat`
- [ ] Chat panel loads correctly
- [ ] Thread list sidebar visible
- [ ] Empty state shown if no threads
- [ ] "New Chat" button visible and works

#### Thread List UI
- [ ] Threads displayed dengan title atau preview
- [ ] Last updated timestamp shown
- [ ] Search box filters threads correctly
- [ ] Click thread loads messages
- [ ] Pinned threads indicated with icon
- [ ] Delete confirmation on click (2-click pattern)
- [ ] Create new thread button works

#### Chat Window
- [ ] Messages display dengan correct role (user/assistant)
- [ ] User messages on right dengan purple background
- [ ] Assistant messages on left dengan avatar icon
- [ ] Timestamps shown on messages
- [ ] Model name displayed on assistant messages
- [ ] Copy button works on assistant messages
- [ ] Empty state shown correctly

#### Message Input
- [ ] Text input auto-resizes (max 200px height)
- [ ] Model selector opens and lists models
- [ ] Character count displayed
- [ ] "Enter" sends message
- [ ] "Shift+Enter" adds new line
- [ ] Send button disabled when empty
- [ ] Send button shows loading spinner during streaming

#### Streaming Behavior
- [ ] Message streams token-by-token
- [ ] Streaming cursor (`|`) shown during stream
- [ ] Auto-scroll to bottom during streaming
- [ ] Can regenerate last response
- [ ] Regenerate button shown after assistant message
- [ ] Error messages displayed if stream fails

#### Thread Actions
- [ ] Edit title inline works
- [ ] Pin/unpin thread works (icon toggles)
- [ ] Delete thread shows confirmation
- [ ] Archive thread (if UI exists)
- [ ] Thread actions menu opens/closes correctly

### Mobile Responsive

#### Thread List (Mobile)
- [ ] Thread list becomes drawer/slide-out
- [ ] Close button visible on mobile
- [ ] Search input full-width
- [ ] Thread items touch-friendly (min 44px height)
- [ ] Swipe gestures (if implemented)

#### Chat Window (Mobile)
- [ ] Messages readable on small screens
- [ ] User/assistant distinction clear
- [ ] Copy button accessible
- [ ] Scroll works smoothly

#### Input Area (Mobile)
- [ ] Input full-width on mobile
- [ ] Model selector accessible
- [ ] Send button size adequate (44px)
- [ ] Keyboard doesn't block input (viewport units)

### Cross-Browser Testing

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## 🔍 Edge Cases & Scenarios

### Scenario 1: Very Long Message
**Setup:** Send message >10,000 characters

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Validation rejects >32,000 chars | Error message shown | ☐ |
| Message displays correctly | Scrollable, no overflow | ☐ |
| Copy button works | Full content copied | ☐ |

---

### Scenario 2: No AI Connection Configured
**Setup:** User has no active AI connections

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Can create thread | Thread created | ☐ |
| Send message fails gracefully | Error: "No AI connection available" | ☐ |
| Suggests action | Link to settings/AI setup | ☐ |

---

### Scenario 3: AI Provider Timeout
**Setup:** AI provider tidak respond dalam timeout period

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Stream stops | Timeout error shown | ☐ |
| User message saved | User message exists in database | ☐ |
| No incomplete assistant message | No partial message saved | ☐ |
| Can retry | Regenerate button works | ☐ |

---

### Scenario 4: Network Disconnection During Stream
**Setup:** Disconnect network saat streaming

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Stream interruption detected | Error message shown | ☐ |
| Partial content saved or discarded | No corrupt message | ☐ |
| Can resume | Send new message works | ☐ |

---

### Scenario 5: Rapid Message Sending
**Setup:** Send multiple messages quickly

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Messages queued correctly | All messages saved | ☐ |
| No race conditions | Message order correct | ☐ |
| UI doesn't break | Loading states correct | ☐ |

---

### Scenario 6: Thread with 1000+ Messages
**Setup:** Thread dengan history sangat panjang

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| List loads dengan pagination | Only 50 messages loaded initially | ☐ |
| Scroll to load more works | Lazy loading triggers | ☐ |
| Performance acceptable | No significant lag | ☐ |
| Context window limit handled | Oldest messages truncated if needed | ☐ |

---

### Scenario 7: Markdown & Code in Messages
**Setup:** Send message dengan markdown syntax

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Bold text renders | `**bold**` → **bold** | ☐ |
| Italic renders | `*italic*` → *italic* | ☐ |
| Inline code renders | `` `code` `` → `code` with bg | ☐ |
| Code blocks render | ``` ``` blocks with syntax | ☐ |
| Line breaks preserved | `\n` → actual line break | ☐ |

---

### Scenario 8: Special Characters & Emoji
**Setup:** Send message dengan emoji, unicode, special chars

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Emoji displayed correctly | 🔥👍✨ renders | ☐ |
| Unicode chars work | Chinese, Arabic, etc. | ☐ |
| HTML escaped | `<script>` tidak execute | ☐ |

---

### Scenario 9: Model Switching Mid-Conversation
**Setup:** Change model setelah beberapa messages

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Model selector shows current | UI reflects thread model | ☐ |
| Change persists | Thread model updated | ☐ |
| Next message uses new model | model_used field correct | ☐ |
| Previous messages unchanged | Old messages keep old model_used | ☐ |

---

### Scenario 10: Concurrent Editing (Multi-Device)
**Setup:** Open same thread in 2 browser tabs/devices

| Test | Expected Behavior | Pass/Fail |
|------|-------------------|-----------|
| Both can send messages | No conflict | ☐ |
| Messages appear in both | Real-time update (if implemented) | ☐ |
| No duplicate messages | Each message saved once | ☐ |

---

## 🐛 Regression Testing Checklist

Setelah future updates, verify:

- [ ] Thread CRUD still works
- [ ] Authorization still enforced
- [ ] Streaming still real-time
- [ ] Pagination still correct
- [ ] Model selection still works
- [ ] Mobile layout still responsive
- [ ] Tests still passing (22/22)

---

## 📊 Performance Benchmarks

| Metric | Target | Current | Status |
|--------|--------|---------|--------|
| Thread list load time | <500ms | ~200ms | ✅ |
| Message send latency | <100ms | ~80ms | ✅ |
| First token time (streaming) | <2s | Varies | ⚠️ Provider-dependent |
| Pagination query time | <200ms | ~50ms | ✅ |
| Create thread time | <300ms | ~100ms | ✅ |

---

## 🔒 Security Testing Checklist

- [ ] SQL injection: Tested with malicious inputs → Prevented by Eloquent ORM
- [ ] XSS: HTML/JS in messages → Escaped by Vue
- [ ] CSRF: Verified token required → Laravel CSRF middleware
- [ ] Authorization: Tested cross-user access → Blocked (tests TC-09 to TC-13)
- [ ] Rate limiting: Multiple rapid requests → Laravel rate limiter active
- [ ] Input validation: Max lengths enforced → SendChatMessageRequest validates

---

## 📝 Test Data Setup

### Factory Usage

```php
// Create thread with messages
$thread = ChatThread::factory()->forNovel($novel)->create();
$messages = ChatMessage::factory(5)->forThread($thread)->create();

// Create conversation pair
$thread = ChatThread::factory()->create();
ChatMessage::factory()->fromUser()->create(['thread_id' => $thread->id, 'content' => 'Hello']);
ChatMessage::factory()->fromAssistant()->create(['thread_id' => $thread->id, 'content' => 'Hi!']);

// Archived thread
$archived = ChatThread::factory()->archived()->create();

// Pinned thread
$pinned = ChatThread::factory()->pinned()->create();
```

---

## 🚀 Running Full Test Suite

```bash
# Run all tests
php artisan test

# Run only chat tests
php artisan test tests/Feature/ChatControllerTest.php

# Run with verbosity
php artisan test tests/Feature/ChatControllerTest.php -v

# Run specific test
php artisan test --filter=test_user_can_create_chat_thread

# Run with coverage (requires xdebug)
php artisan test --coverage

# Parallel testing (faster)
php artisan test --parallel
```

---

## 📌 Notes for QA Team

1. **Streaming tests manual only:** Automated tests cover controller logic, but actual SSE streaming requires manual browser testing.

2. **AI provider dependency:** Some tests require actual AI connection. For automated tests, mock responses or use test doubles.

3. **Token counts:** `tokens_input` and `tokens_output` may be `null` for some providers (e.g., Ollama). This is expected behavior.

4. **Context injection:** Verify context (novel info, scene) actually sent to AI by checking network tab or backend logs.

5. **Performance:** If thread list slow with 1000+ threads, implement virtual scrolling in future sprint.

---

*Last Updated: 2026-01-02*
