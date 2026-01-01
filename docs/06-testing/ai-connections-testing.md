# Testing Guide: AI Connections

## Overview

Testing guide untuk fitur AI Connections yang mencakup automated tests (PHPUnit feature tests) dan manual testing checklist untuk memastikan semua functionality bekerja dengan benar, yaitu: CRUD operations, connection testing, model fetching, encryption, authorization, dan error handling.

---

## Automated Tests

### Test File Location

```
tests/Feature/AIConnectionTest.php
```

### Running Tests

```bash
# Run all AI Connection tests
php artisan test --filter=AIConnectionTest

# Run specific test
php artisan test --filter=test_user_can_create_ai_connection

# Run with coverage
php artisan test --filter=AIConnectionTest --coverage
```

---

## Test Cases Summary

### ✅ Implemented Test Cases (20 total)

| Test ID | Test Name | Category | Description |
|---------|-----------|----------|-------------|
| AC-T01 | `test_settings_page_renders_correctly` | UI | Settings page loads |
| AC-T02 | `test_settings_ai_page_renders_correctly` | UI | AI settings page loads (⚠️ Skipped - requires Vite) |
| AC-T03 | `test_unauthenticated_user_cannot_access_api` | Auth | 401 for unauthenticated |
| AC-T04 | `test_user_can_get_list_of_connections` | CRUD | GET /api/ai-connections |
| AC-T05 | `test_user_can_get_available_providers` | Providers | GET /providers |
| AC-T06 | `test_user_can_create_ai_connection` | CRUD | POST /api/ai-connections |
| AC-T07 | `test_user_cannot_create_connection_without_required_fields` | Validation | Missing fields rejected |
| AC-T08 | `test_user_cannot_create_connection_with_invalid_provider` | Validation | Invalid provider rejected |
| AC-T09 | `test_connection_requiring_api_key_validates_key_presence` | Validation | API key required check |
| AC-T10 | `test_user_can_view_single_connection` | CRUD | GET /api/ai-connections/{id} |
| AC-T11 | `test_user_cannot_view_other_users_connection` | Authorization | 403 for other user |
| AC-T12 | `test_user_can_update_connection` | CRUD | PATCH /api/ai-connections/{id} |
| AC-T13 | `test_user_cannot_update_other_users_connection` | Authorization | 403 for other user |
| AC-T14 | `test_user_can_delete_connection` | CRUD | DELETE /api/ai-connections/{id} |
| AC-T15 | `test_user_cannot_delete_other_users_connection` | Authorization | 403 for other user |
| AC-T16 | `test_api_key_is_encrypted_in_database` | Security | Encryption verification |
| AC-T17 | `test_api_key_is_masked_in_response` | Security | Masking verification |
| AC-T18 | `test_only_one_connection_can_be_default` | Business Logic | Default connection management |
| AC-T19 | `test_connection_test_updates_status` | Connection Test | Test updates last_tested_at |
| AC-T20 | `test_can_test_connection` | Connection Test | Test connection endpoint |

---

## Manual Testing Checklist

### Prerequisites
- [ ] Database migrated: `php artisan migrate`
- [ ] Frontend built: `yarn run build` or `yarn run dev` running
- [ ] User registered and logged in
- [ ] At least one AI provider API key available (for testing)

---

### 1. UI & Navigation Tests

#### Settings Page Access
- [ ] Navigate to user dropdown → "AI Settings"
- [ ] Settings page loads without errors
- [ ] "AI Connections" card visible with "Manage" button
- [ ] Click "Manage" redirects to `/settings/ai`

#### AI Connections Page
- [ ] `/settings/ai` page loads correctly
- [ ] Page title: "AI Connections"
- [ ] "Add Connection" button visible
- [ ] Empty state shown if no connections
- [ ] Connection cards displayed if connections exist

---

### 2. Create Connection Tests

#### OpenAI Connection
- [ ] Click "Add Connection"
- [ ] Form modal opens
- [ ] Select provider: "OpenAI"
- [ ] Setup guide displayed with instructions
- [ ] Enter name: "Test OpenAI"
- [ ] Enter API key: `sk-test-key-here`
- [ ] Base URL auto-filled: `https://api.openai.com/v1`
- [ ] Check "Set as default connection"
- [ ] Click "Save Connection"
- [ ] Connection created successfully
- [ ] New connection card appears in list
- [ ] Default badge shown

#### Local Provider (Ollama)
- [ ] Click "Add Connection"
- [ ] Select provider: "Ollama (Local)"
- [ ] Setup guide shows installation instructions
- [ ] Enter name: "Local Ollama"
- [ ] API key field hidden (not required)
- [ ] Base URL: `http://localhost:11434`
- [ ] Save connection
- [ ] Connection created without API key

#### Validation Tests
- [ ] Try to save without name → error shown
- [ ] Try to save with name < 2 chars → error shown
- [ ] Try to save OpenAI without API key → error shown
- [ ] Try to save with invalid base URL → error shown
- [ ] Error messages displayed correctly

---

### 3. Connection List Tests

#### Display Tests
- [ ] All connections displayed in cards
- [ ] Each card shows:
  - [ ] Provider name with icon
  - [ ] Connection name
  - [ ] Masked API key (or "No API key required")
  - [ ] Base URL
  - [ ] Connection status (badge)
  - [ ] Default badge (if default)
  - [ ] Action buttons (Test, Edit, Delete)

#### Status Badges
- [ ] Untested connection: gray badge "Not tested"
- [ ] Tested successful: green badge "Connected"
- [ ] Tested failed: red badge "Failed"

---

### 4. Test Connection Tests

#### Successful Test
- [ ] Click "Test" button on a connection
- [ ] Button shows loading state
- [ ] Success message appears: "Connection successful. Found X models."
- [ ] Status badge updates to "Connected"
- [ ] Model count displayed
- [ ] Success message auto-dismisses after 5 seconds

#### Failed Test (Invalid API Key)
- [ ] Create connection with invalid API key
- [ ] Click "Test"
- [ ] Error message appears
- [ ] Status badge updates to "Failed"
- [ ] Error message describes the issue

#### Network Error Test
- [ ] Create Ollama connection (local)
- [ ] Ensure Ollama not running
- [ ] Click "Test"
- [ ] Connection timeout error shown
- [ ] Status badge updates to "Failed"

---

### 5. Edit Connection Tests

#### Basic Edit
- [ ] Click "Edit" button on a connection
- [ ] Form pre-filled with existing data
- [ ] Modal title: "Edit Connection"
- [ ] Change name
- [ ] Change API key
- [ ] Change base URL
- [ ] Toggle "Set as default"
- [ ] Save changes
- [ ] Connection updated in list

#### Change Provider Restrictions
- [ ] Provider field is disabled (cannot change)
- [ ] Only other fields editable

#### Validation on Edit
- [ ] Try to clear name → error shown
- [ ] Try invalid base URL → error shown
- [ ] Try to remove required API key → error shown

---

### 6. Delete Connection Tests

#### Delete Flow
- [ ] Click "Delete" button
- [ ] Confirmation dialog appears
- [ ] Click "Cancel" → dialog closes, nothing happens
- [ ] Click "Delete" again
- [ ] Confirm deletion
- [ ] Connection removed from list
- [ ] Success feedback (page reloads)

#### Delete Default Connection
- [ ] Delete the default connection
- [ ] Ensure no errors occur
- [ ] Other connections remain

---

### 7. Default Connection Tests

#### Set Default
- [ ] Create multiple connections
- [ ] Only one has "Default" badge initially
- [ ] Edit another connection
- [ ] Check "Set as default"
- [ ] Save
- [ ] Previous default loses badge
- [ ] New connection has default badge
- [ ] Only one default at a time

#### Toggle Default Off
- [ ] Edit default connection
- [ ] Uncheck "Set as default"
- [ ] Save
- [ ] Connection updated
- [ ] No connection is default now

---

### 8. Model Selector Component Tests

#### Display Models
- [ ] Navigate to a page using Model Selector
- [ ] Dropdown shows available connections
- [ ] Select a connection
- [ ] Models load and display
- [ ] Model name and context length shown

#### Empty State
- [ ] If no connections exist
- [ ] Empty state message displayed
- [ ] "Set up AI connection" button shown
- [ ] Click button → redirects to `/settings/ai`

#### Search Models
- [ ] Type in search box
- [ ] Models filtered by search term
- [ ] Clear search → all models shown

---

### 9. Security Tests

#### Authorization
- [ ] Create connection as User A
- [ ] Logout, login as User B
- [ ] Try to access User A's connection via API
- [ ] Expect 403 Forbidden
- [ ] User B cannot see User A's connections

#### API Key Security
- [ ] Create connection with API key
- [ ] Check response JSON
- [ ] API key should be masked: `sk-...xyz`
- [ ] Full key never exposed

#### CSRF Protection
- [ ] Verify CSRF token meta tag in `<head>`
- [ ] API calls include CSRF token
- [ ] Requests without token get 419 error

---

### 10. Edge Cases & Error Handling

#### Multiple Connections Same Provider
- [ ] Create 2 OpenAI connections
- [ ] Both work independently
- [ ] No conflicts

#### Large Number of Connections
- [ ] Create 10+ connections
- [ ] Page renders without performance issues
- [ ] All connections accessible

#### Long Names
- [ ] Create connection with 100 character name
- [ ] Name displays correctly (truncates if needed)

#### Special Characters
- [ ] Name with emojis: "My AI 🤖"
- [ ] Name with unicode characters
- [ ] Saves and displays correctly

#### Network Timeouts
- [ ] Test connection with very slow/timeout API
- [ ] Appropriate error message shown
- [ ] UI doesn't hang

---

### 11. Responsive Design Tests

#### Desktop (1920x1080)
- [ ] Page layout optimal
- [ ] Connection cards in grid (2-3 columns)
- [ ] All text readable
- [ ] Buttons properly sized

#### Tablet (768x1024)
- [ ] Layout adapts to smaller screen
- [ ] Cards stack vertically or 2 columns
- [ ] Modal fits screen
- [ ] No horizontal scroll

#### Mobile (375x667)
- [ ] Single column layout
- [ ] Cards stack vertically
- [ ] Form fields full width
- [ ] Buttons touch-friendly (44x44px minimum)
- [ ] Modal scrollable if needed
- [ ] No content cut off

---

### 12. Loading States & Feedback

#### Loading Indicators
- [ ] "Test" button shows spinner when testing
- [ ] "Save" button shows loading state
- [ ] "Delete" button shows processing state
- [ ] Model selector shows loading while fetching

#### Success Feedback
- [ ] Connection created → success message
- [ ] Connection updated → success message
- [ ] Connection deleted → success message
- [ ] Test successful → success message with model count

#### Error Feedback
- [ ] Validation errors show below fields
- [ ] API errors show in toast/modal
- [ ] Network errors user-friendly message
- [ ] All errors dismissable

---

### 13. Accessibility Tests

#### Keyboard Navigation
- [ ] Tab through all form fields
- [ ] Enter to submit form
- [ ] Escape to close modal
- [ ] Arrow keys in dropdown
- [ ] Focus visible on all interactive elements

#### Screen Reader
- [ ] Form labels properly associated
- [ ] Error messages announced
- [ ] Status changes announced
- [ ] Button states announced (loading, disabled)

#### Color Contrast
- [ ] Text readable against backgrounds
- [ ] Status badges have sufficient contrast
- [ ] Error messages easily visible
- [ ] Focus indicators visible

---

### 14. Performance Tests

#### Page Load Time
- [ ] Settings page loads < 1 second
- [ ] AI connections page loads < 2 seconds
- [ ] No layout shift during load

#### API Response Time
- [ ] List connections < 500ms
- [ ] Create connection < 1 second
- [ ] Test connection < 5 seconds (depends on provider)
- [ ] Fetch models < 3 seconds

---

## Test Data Setup

### Sample Test Connections

```bash
# Create test connections via Tinker
php artisan tinker
```

```php
$user = User::first();

// OpenAI connection
$user->aiConnections()->create([
    'provider' => 'openai',
    'name' => 'Test OpenAI',
    'api_key_encrypted' => Crypt::encrypt('sk-test-key'),
    'base_url' => 'https://api.openai.com/v1',
    'is_active' => true,
    'is_default' => true,
]);

// Ollama connection
$user->aiConnections()->create([
    'provider' => 'ollama',
    'name' => 'Local Ollama',
    'base_url' => 'http://localhost:11434',
    'is_active' => true,
]);

// OpenRouter connection
$user->aiConnections()->create([
    'provider' => 'openrouter',
    'name' => 'OpenRouter',
    'api_key_encrypted' => Crypt::encrypt('sk-or-v1-test'),
    'base_url' => 'https://openrouter.ai/api/v1',
    'is_active' => true,
]);
```

---

## Known Issues & Limitations

| Issue | Workaround | Status |
|-------|------------|--------|
| Test requiring Vite build skipped in PHPUnit | Run `yarn run build` manually for full frontend testing | ⚠️ Expected |
| OpenRouter rate limiting during tests | Use delay between test connection calls | ⚠️ Expected |
| Local providers fail if not running | Expected behavior, show appropriate error | ✅ Working as intended |

---

## Regression Test Checklist

Run these tests before every release:

- [ ] All 20 automated tests passing
- [ ] Can create connection for each provider type
- [ ] Test connection works for valid credentials
- [ ] Test connection fails gracefully for invalid credentials
- [ ] API key encryption verified
- [ ] Authorization working (cannot access other user's connections)
- [ ] Default connection management working
- [ ] Responsive on mobile, tablet, desktop
- [ ] No console errors in browser
- [ ] No PHP errors in `storage/logs/laravel.log`

---

## Related Documentation

- **API Reference:** [AI Connections API](../04-api-reference/ai-connections.md)
- **Sprint Documentation:** [Sprint 03 - AI Connections](../10-sprints/sprint-03-ai-connections.md)

---

*Last Updated: 2026-01-01*
