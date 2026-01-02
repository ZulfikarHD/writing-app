# Testing Guide: Chat Enhancement Features (Sprint 23)

## Overview

Panduan testing untuk Chat Enhancement Features yang diimplementasikan di Sprint 23, yaitu: F1 (Codex Alias Auto-Detection), F2 (Delete Chat Confirmation), F3 (Rename from Thread List), dan F4 (Real-time Chat Updates).

---

## Test Environment Setup

### Prerequisites

1. **Laravel Reverb Server** (untuk F4 testing)
   ```bash
   php artisan reverb:start
   ```

2. **Development Server**
   ```bash
   composer run dev
   # atau
   php artisan serve & yarn dev
   ```

3. **Test Data**
   - Minimal 1 novel dengan beberapa scenes
   - Minimal 3-5 codex entries dengan aliases
   - Minimal 2-3 chat threads

---

## F1: Codex Alias Auto-Detection

### Unit Tests

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| F1-U01 | `detectAliases()` dengan text kosong | Returns empty array | ☐ Manual |
| F1-U02 | `detectAliases()` dengan no matches | Returns empty array | ☐ Manual |
| F1-U03 | `detectAliases()` dengan single match | Returns 1 entry | ☐ Manual |
| F1-U04 | `detectAliases()` dengan multiple matches | Returns unique entries only | ☐ Manual |
| F1-U05 | Case-insensitive matching | "John" matches "john" | ☐ Manual |
| F1-U06 | Word boundary matching | "John" tidak match "Johnny" | ☐ Manual |
| F1-U07 | Longest match priority | "John Smith" wins over "John" | ☐ Manual |
| F1-U08 | `linkAliasesInHtml()` generates valid HTML | Anchor tags dengan data attributes | ☐ Manual |

### Integration Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F1-I01 | Alias lookup API | GET `/api/novels/{id}/codex/alias-lookup` | 200 dengan lookup map | ☐ Manual |
| F1-I02 | Lookup includes names | Check entry names in response | All names present (lowercase keys) | ☐ Manual |
| F1-I03 | Lookup includes aliases | Check aliases in response | All aliases present | ☐ Manual |
| F1-I04 | Authorization check | Access other user's novel | 403 Forbidden | ☐ Manual |

### UI/UX Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F1-X01 | Alias detection on typing | 1. Open chat<br>2. Type character name<br>3. Wait 300ms | Alias chip appears above input | ☐ Manual |
| F1-X02 | Add alias as context | 1. See detected alias<br>2. Click + button | Context added, chip disappears | ☐ Manual |
| F1-X03 | Dismiss alias | 1. See detected alias<br>2. Click × button | Chip disappears, doesn't reappear | ☐ Manual |
| F1-X04 | Add all aliases | 1. Type text with 3+ aliases<br>2. Click "Add all" | All chips become contexts | ☐ Manual |
| F1-X05 | Type color coding | 1. Type different entry types | Chips colored by type (character=blue, location=green) | ☐ Manual |
| F1-X06 | Already in context | 1. Add codex as context<br>2. Type same name | No duplicate chip appears | ☐ Manual |

---

## F2: Delete Chat Confirmation

### UI/UX Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F2-X01 | Delete button visible on hover | 1. Hover over thread item | Trash icon appears | ☐ Manual |
| F2-X02 | Click delete shows modal | 1. Hover thread<br>2. Click trash icon | Confirmation modal opens | ☐ Manual |
| F2-X03 | Modal shows thread title | 1. Open delete modal | Modal message includes chat title | ☐ Manual |
| F2-X04 | Cancel closes modal | 1. Open delete modal<br>2. Click Cancel | Modal closes, thread intact | ☐ Manual |
| F2-X05 | Confirm deletes thread | 1. Open delete modal<br>2. Click Delete | Thread removed from list | ☐ Manual |
| F2-X06 | Escape key cancels | 1. Open delete modal<br>2. Press Escape | Modal closes | ☐ Manual |
| F2-X07 | Click outside closes | 1. Open delete modal<br>2. Click backdrop | Modal closes | ☐ Manual |
| F2-X08 | Delete active thread | 1. Select a thread<br>2. Delete it | Thread deleted, UI handles gracefully | ☐ Manual |
| F2-X09 | Spring animation | 1. Open delete modal | Modal bounces in with spring effect | ☐ Manual |

---

## F3: Rename from Thread List

### UI/UX Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F3-X01 | Rename button visible on hover | 1. Hover over thread item | Pencil icon appears | ☐ Manual |
| F3-X02 | Click pencil starts edit | 1. Hover thread<br>2. Click pencil | Title becomes input field | ☐ Manual |
| F3-X03 | Text pre-selected | 1. Start editing | All text selected | ☐ Manual |
| F3-X04 | Enter submits rename | 1. Edit title<br>2. Press Enter | Title saved, exits edit mode | ☐ Manual |
| F3-X05 | Escape cancels rename | 1. Edit title<br>2. Press Escape | Original title restored | ☐ Manual |
| F3-X06 | Blur submits rename | 1. Edit title<br>2. Click elsewhere | Title saved if changed | ☐ Manual |
| F3-X07 | Empty string rejected | 1. Clear title<br>2. Press Enter | Keeps original title | ☐ Manual |
| F3-X08 | Same title no API call | 1. Start edit<br>2. Submit without change | No network request | ☐ Manual |
| F3-X09 | Click thread while editing | 1. Start editing thread A<br>2. Click thread B | Submits A, selects B | ☐ Manual |
| F3-X10 | Long title handling | 1. Enter very long title (100+ chars) | Input accepts, API may truncate | ☐ Manual |

---

## F4: Real-time Chat Updates

### Setup Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F4-S01 | Echo initialized | 1. Open browser console<br>2. Check `window.Echo` | Echo instance exists | ☐ Manual |
| F4-S02 | Reverb connection | 1. Start Reverb server<br>2. Check browser network tab | WebSocket connected | ☐ Manual |
| F4-S03 | Channel subscription | 1. Open chat thread<br>2. Check console | "Subscribed to chat.thread.X" | ☐ Manual |

### Real-time Event Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F4-R01 | Receive new message | 1. Open chat in 2 tabs<br>2. Send message from tab 1 | Message appears in tab 2 | ☐ Manual |
| F4-R02 | Thread title update | 1. Open thread list in 2 tabs<br>2. Rename from tab 1 | Title updates in tab 2 | ☐ Manual |
| F4-R03 | Channel cleanup | 1. Open thread A<br>2. Switch to thread B<br>3. Check subscriptions | Unsubscribed from A, subscribed to B | ☐ Manual |
| F4-R04 | Reconnection handling | 1. Disconnect WiFi<br>2. Reconnect | Echo reconnects automatically | ☐ Manual |

### Authorization Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| F4-A01 | Own thread subscription | Subscribe to own thread | Success | ☐ Manual |
| F4-A02 | Other user's thread | Subscribe to other user's thread | Auth error | ☐ Manual |
| F4-A03 | Non-existent thread | Subscribe to thread ID 999999 | Auth error | ☐ Manual |

---

## Cross-Feature Tests

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| CF-01 | Delete while editing | 1. Start rename<br>2. Click delete | Modal opens, edit cancelled | ☐ Manual |
| CF-02 | Alias + Send message | 1. Type with alias<br>2. Add as context<br>3. Send | Context included in API call | ☐ Manual |
| CF-03 | Realtime + Delete | 1. Delete in tab 1<br>2. Tab 2 has thread open | Tab 2 handles gracefully | ☐ Manual |
| CF-04 | Alias in AI response | 1. Ask about character<br>2. AI mentions character name | Future: Should be clickable link | ☐ Manual |

---

## Edge Cases

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| E-01 | Empty novel (no codex) | 1. Open chat in empty novel<br>2. Type anything | No alias detection (empty lookup) | ☐ Manual |
| E-02 | Special characters in alias | 1. Create codex "Dr. Smith"<br>2. Type "Dr. Smith" in chat | Correctly detected | ☐ Manual |
| E-03 | Unicode aliases | 1. Create codex "東京"<br>2. Type "東京" in chat | Correctly detected | ☐ Manual |
| E-04 | Very long thread title | 1. Create thread with 255 char title<br>2. Delete modal | Modal handles overflow gracefully | ☐ Manual |
| E-05 | Network error on delete | 1. Disconnect network<br>2. Try delete | Error toast, modal stays open | ☐ Manual |
| E-06 | Network error on rename | 1. Disconnect network<br>2. Try rename | Error toast, reverts to original | ☐ Manual |
| E-07 | Rapid typing alias detection | 1. Type very fast<br>2. Clear and retype | Debounce works, no duplicate detections | ☐ Manual |
| E-08 | Echo not loaded | 1. Block pusher-js in devtools<br>2. Open chat | Graceful fallback, no JS errors | ☐ Manual |

---

## Performance Tests

| Test ID | Scenario | Metric | Target | Status |
|---------|----------|--------|--------|--------|
| P-01 | Alias lookup API | Response time | < 200ms | ☐ Manual |
| P-02 | 100 codex entries lookup | Response time | < 500ms | ☐ Manual |
| P-03 | Alias detection (1000 chars) | UI freeze | None | ☐ Manual |
| P-04 | Thread list with 50 threads | Render time | < 100ms | ☐ Manual |
| P-05 | WebSocket message latency | Event to UI | < 100ms | ☐ Manual |

---

## Accessibility Tests

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| A-01 | Delete modal keyboard nav | Tab navigates between buttons | ☐ Manual |
| A-02 | Rename input focus trap | Focus stays in input until submit/cancel | ☐ Manual |
| A-03 | Screen reader delete confirm | Modal announced with role="alertdialog" | ☐ Manual |
| A-04 | Alias chip buttons | Buttons have aria-labels | ☐ Manual |
| A-05 | Focus management after delete | Focus moves to next thread or create button | ☐ Manual |

---

## Mobile Tests

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| M-01 | Delete on touch | Long press or tap trash icon works | ☐ Manual |
| M-02 | Rename on touch | Tap pencil opens input | ☐ Manual |
| M-03 | Modal on small screen | Modal centered, scrollable if needed | ☐ Manual |
| M-04 | Alias chips wrapping | Chips wrap properly on narrow screens | ☐ Manual |
| M-05 | Virtual keyboard on rename | Input visible when keyboard opens | ☐ Manual |

---

## Test Commands

```bash
# Run all tests (if automated tests exist)
php artisan test --filter=Chat

# Start Reverb for real-time testing
php artisan reverb:start --debug

# Check routes
php artisan route:list --path=chat
php artisan route:list --path=codex | grep alias
```

---

## Related Documentation

- **Sprint Documentation:** [Sprint 23 - Chat Enhancement Features](../10-sprints/sprint-23-chat-enhancement-features.md)
- **API Reference:** [Chat API](../04-api-reference/chat.md)
- **Previous Testing:** [Chat Context Testing](./chat-context-testing.md)

---

*Last Updated: 2026-01-02*
