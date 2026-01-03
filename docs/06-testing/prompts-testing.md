# Testing Guide: Prompts System

**Feature:** Prompt Library Core (FG-05.1) + Prompt Editor Enhancement (FG-05.2)  
**Test Suite:** `tests/Feature/PromptTest.php`  
**Status:** ✅ 18/18 Tests Passing  
**Last Updated:** 2026-01-03

---

## Overview

Panduan testing komprehensif untuk Prompt Library system, mencakup automated tests (PHPUnit) dan manual QA checklist untuk memastikan reliability, security, dan user experience yang baik, yaitu: validasi CRUD operations, authorization rules, dan edge cases handling.

---

## 🧪 Automated Tests

### Test Summary

```bash
Total Tests: 18
✅ Passing: 18
❌ Failing: 0
⏱️ Duration: ~2.5s
```

### Running Tests

```bash
# Run all prompt tests
php artisan test --filter=PromptTest

# Run specific test
php artisan test --filter=test_user_can_create_prompt

# Run with coverage
php artisan test --filter=PromptTest --coverage

# Run in parallel
php artisan test --filter=PromptTest --parallel
```

---

## 📋 Test Cases

### Category 1: Access Control & Filtering

#### TC-P001: List Accessible Prompts
**Test:** `test_user_can_list_accessible_prompts`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: System prompts (2), user's prompts (3), other user's prompts (2)
When: User requests /api/prompts
Then: Returns 5 prompts (system + own only)
```

**Assertions:**
- ✅ Response status 200
- ✅ Returns exactly 5 prompts
- ✅ Includes system prompts
- ✅ Includes user's own prompts
- ✅ Excludes other users' prompts
- ✅ JSON structure valid

---

#### TC-P002: Filter Prompts by Type
**Test:** `test_user_can_filter_prompts_by_type`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: 2 chat prompts, 3 replacement prompts
When: User requests /api/prompts?type=chat
Then: Returns only 2 chat prompts
```

**Assertions:**
- ✅ Response status 200
- ✅ Returns 2 prompts
- ✅ All returned prompts have type="chat"

---

#### TC-P003: Search Prompts
**Test:** `test_user_can_search_prompts`  
**Priority:** 🟡 High

**Scenario:**
```
Given: Prompt with name "Unique Search Term", 4 other prompts
When: User searches "?search=Unique"
Then: Returns only 1 matching prompt
```

**Assertions:**
- ✅ Response status 200
- ✅ Returns 1 prompt
- ✅ Prompt name matches search term

---

#### TC-P004: Get Prompts by Type Endpoint
**Test:** `test_user_can_get_prompts_by_type`  
**Priority:** 🟡 High

**Scenario:**
```
Given: 3 chat prompts, 2 replacement prompts
When: User requests /api/prompts/type/chat
Then: Returns 3 chat prompts
```

**Assertions:**
- ✅ Response status 200
- ✅ Returns 3 prompts
- ✅ JSON structure valid

---

#### TC-P005: Cannot Access Other User's Prompts
**Test:** `test_user_cannot_access_other_users_prompts`  
**Priority:** 🔴 Critical (Security)

**Scenario:**
```
Given: Another user's private prompt
When: User requests /api/prompts/{other_user_prompt_id}
Then: Returns 403 Forbidden
```

**Assertions:**
- ✅ Response status 403
- ✅ Error message: "You do not have access to this prompt."

---

### Category 2: CRUD Operations

#### TC-P006: Create Prompt
**Test:** `test_user_can_create_prompt`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: Valid prompt data
When: User posts /api/prompts
Then: Prompt is created successfully
```

**Request Body:**
```json
{
  "name": "Test Prompt",
  "type": "chat",
  "description": "Test description",
  "system_message": "System message",
  "user_message": "User message"
}
```

**Assertions:**
- ✅ Response status 201
- ✅ Prompt exists in database
- ✅ `user_id` matches current user
- ✅ `is_system` is false
- ✅ `usage_count` is 0
- ✅ `sort_order` auto-assigned
- ✅ Returns created prompt data

---

#### TC-P007: View Prompt Details
**Test:** `test_user_can_view_prompt_details`  
**Priority:** 🟡 High

**Scenario:**
```
Given: User's own prompt exists
When: User requests /api/prompts/{prompt_id}
Then: Returns full prompt details
```

**Assertions:**
- ✅ Response status 200
- ✅ Contains all prompt fields
- ✅ JSON structure matches schema

---

#### TC-P008: Update Prompt
**Test:** `test_user_can_update_prompt`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: User's own prompt
When: User patches /api/prompts/{prompt_id} with new data
Then: Prompt is updated
```

**Request Body:**
```json
{
  "name": "Updated Name",
  "description": "Updated description"
}
```

**Assertions:**
- ✅ Response status 200
- ✅ Database record updated
- ✅ Returns updated prompt
- ✅ `updated_at` timestamp changed

---

#### TC-P009: Delete Prompt
**Test:** `test_user_can_delete_prompt`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: User's own prompt
When: User deletes /api/prompts/{prompt_id}
Then: Prompt is deleted
```

**Assertions:**
- ✅ Response status 200
- ✅ Prompt removed from database
- ✅ Success message returned

---

#### TC-P010: Cannot Edit System Prompts
**Test:** `test_user_cannot_edit_system_prompts`  
**Priority:** 🔴 Critical (Security)

**Scenario:**
```
Given: System prompt (is_system = true)
When: User attempts to patch /api/prompts/{system_prompt_id}
Then: Returns 403 error
```

**Assertions:**
- ✅ Response status 403
- ✅ Error message: "You cannot edit this prompt."
- ✅ Database unchanged

---

#### TC-P011: Cannot Delete System Prompts
**Test:** `test_user_cannot_delete_system_prompts`  
**Priority:** 🔴 Critical (Security)

**Scenario:**
```
Given: System prompt
When: User attempts to delete /api/prompts/{system_prompt_id}
Then: Returns 403 error
```

**Assertions:**
- ✅ Response status 403
- ✅ Error message: "You cannot delete this prompt."
- ✅ Database unchanged

---

### Category 3: Clone Functionality

#### TC-P012: Clone System Prompt
**Test:** `test_user_can_clone_system_prompt`  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: System prompt
When: User posts /api/prompts/{system_prompt_id}/clone
Then: Creates user-owned copy
```

**Assertions:**
- ✅ Response status 201
- ✅ New prompt created in database
- ✅ Cloned prompt is user-owned (`is_system` = false)
- ✅ Cloned prompt has `user_id` = current user
- ✅ Name appended with "(Copy)"
- ✅ All content copied (system_message, user_message, etc.)
- ✅ `usage_count` reset to 0

---

#### TC-P013: Clone User Prompt
**Test:** `test_user_can_clone_own_prompt`  
**Priority:** 🟡 High

**Scenario:**
```
Given: User's own prompt
When: User clones it
Then: Creates duplicate
```

**Assertions:**
- ✅ Response status 201
- ✅ New prompt created
- ✅ Content matches original
- ✅ `usage_count` = 0

---

#### TC-P014: Clone with Custom Name
**Test:** `test_clone_with_custom_name`  
**Priority:** 🟢 Medium

**Scenario:**
```
Given: Any accessible prompt
When: User clones with { "name": "Custom Name" }
Then: Clone uses custom name
```

**Assertions:**
- ✅ Response status 201
- ✅ Cloned prompt name = "Custom Name"
- ✅ No "(Copy)" suffix

---

### Category 4: Usage Tracking

#### TC-P015: Record Usage
**Test:** `test_user_can_record_prompt_usage`  
**Priority:** 🟡 High

**Scenario:**
```
Given: Prompt with usage_count = 5
When: User posts /api/prompts/{prompt_id}/usage
Then: usage_count increments to 6
```

**Assertions:**
- ✅ Response status 200
- ✅ `usage_count` incremented
- ✅ Database updated

---

#### TC-P016: Usage Only for Accessible Prompts
**Test:** `test_cannot_record_usage_for_inaccessible_prompts`  
**Priority:** 🔴 Critical (Security)

**Scenario:**
```
Given: Another user's private prompt
When: User attempts to record usage
Then: Returns 403 Forbidden
```

**Assertions:**
- ✅ Response status 403
- ✅ `usage_count` unchanged

---

### Category 5: Reordering

#### TC-P017: Reorder Prompts
**Test:** `test_user_can_reorder_prompts`  
**Priority:** 🟢 Medium

**Scenario:**
```
Given: 3 user prompts with sort_order [0, 1, 2]
When: User posts reorder with { 1: 0, 2: 1, 3: 2 }
Then: Sort orders updated in database
```

**Assertions:**
- ✅ Response status 200
- ✅ Database `sort_order` values updated
- ✅ Only user's own prompts reordered

---

### Category 6: Types

#### TC-P018: Get Prompt Types
**Test:** `test_user_can_get_prompt_types`  
**Priority:** 🟡 High

**Scenario:**
```
When: User requests /api/prompts/types
Then: Returns all available types with labels
```

**Assertions:**
- ✅ Response status 200
- ✅ Returns types object
- ✅ Contains all 4 types (chat, prose, replacement, summary)
- ✅ Each type has label

---

## 🧪 Manual Testing Checklist

### UI/UX Testing

#### Prompt Library Page

- [ ] **Page Load**
  - [ ] Page loads without errors
  - [ ] Statistics cards display correct counts
  - [ ] Empty state shows when no prompts
  - [ ] Loading states display correctly

- [ ] **Search & Filter**
  - [ ] Search input filters prompts in real-time
  - [ ] Type filter chips work correctly
  - [ ] Clear filters resets to all prompts
  - [ ] No results state displays correctly

- [ ] **Prompt Cards**
  - [ ] Cards display name, description, type badge
  - [ ] System badge shows for system prompts
  - [ ] Usage count displays correctly
  - [ ] Hover reveals actions (Edit, Clone, Delete)
  - [ ] Card animations smooth (scale on press)

- [ ] **Create Prompt**
  - [ ] "Create Prompt" button opens editor
  - [ ] Form validation works (name required)
  - [ ] Type selector shows all 4 types
  - [ ] System/User message textareas work
  - [ ] Model settings section expandable
  - [ ] Save button creates prompt
  - [ ] Success toast appears
  - [ ] New prompt appears in list immediately

- [ ] **Edit Prompt**
  - [ ] Edit button opens editor with existing data
  - [ ] Cannot edit system prompts (button disabled)
  - [ ] Save updates prompt correctly
  - [ ] Cancel closes editor without saving
  - [ ] Updated prompt reflects changes immediately

- [ ] **Clone Prompt**
  - [ ] Clone button works for both system and user prompts
  - [ ] Modal/dialog asks for new name
  - [ ] Cloned prompt appears in list
  - [ ] Cloned prompt is editable
  - [ ] Cloned prompt marked as user-owned

- [ ] **Delete Prompt**
  - [ ] Delete button shows confirmation dialog
  - [ ] Confirm deletes prompt
  - [ ] Cancel preserves prompt
  - [ ] System prompts cannot be deleted
  - [ ] Deleted prompt removed from list

---

#### Workspace Integration

- [ ] **Sidebar Prompts Section**
  - [ ] Section appears in workspace sidebar
  - [ ] Collapsible/expandable
  - [ ] Shows list of prompts
  - [ ] "Manage Prompts" link navigates to `/prompts`
  - [ ] Empty state if no prompts

---

### Responsive Design

- [ ] **Desktop (1920x1080)**
  - [ ] Grid layout displays 3-4 cards per row
  - [ ] All elements properly spaced
  - [ ] Modal/editor doesn't overflow
  - [ ] Sidebar collapses nicely

- [ ] **Tablet (768x1024)**
  - [ ] Grid adapts to 2 cards per row
  - [ ] Touch targets appropriate size
  - [ ] Editor fits screen

- [ ] **Mobile (375x667)**
  - [ ] Cards stack vertically
  - [ ] Search/filter accessible
  - [ ] Editor usable (consider slide-up modal)
  - [ ] Actions accessible via long-press or menu

---

### Dark Mode

- [ ] **Theme Consistency**
  - [ ] All components support dark mode
  - [ ] Colors readable in dark mode
  - [ ] Borders/separators visible
  - [ ] Hover states work in dark mode
  - [ ] Badges/labels styled correctly

---

### Accessibility

- [ ] **Keyboard Navigation**
  - [ ] Tab through all interactive elements
  - [ ] Enter/Space triggers actions
  - [ ] Escape closes modals
  - [ ] Focus indicators visible

- [ ] **Screen Readers**
  - [ ] Buttons have descriptive labels
  - [ ] Form inputs have labels
  - [ ] Error messages announced
  - [ ] Success messages announced

---

### Performance

- [ ] **Load Time**
  - [ ] Page loads within 2 seconds
  - [ ] No layout shift during load
  - [ ] Images/icons load quickly

- [ ] **Interactions**
  - [ ] Search response < 300ms
  - [ ] Filter toggle instant
  - [ ] CRUD operations < 1 second
  - [ ] No lag during typing

---

## 🔒 Security Testing

### Authorization Tests

- [ ] **Cannot Access Other User's Prompts**
  - [ ] Direct URL access to other's prompt returns 403
  - [ ] API endpoint returns 403
  - [ ] Prompt not shown in list

- [ ] **System Prompt Protection**
  - [ ] Edit button disabled for system prompts
  - [ ] Delete button disabled
  - [ ] API rejects edit/delete requests

- [ ] **Token Validation**
  - [ ] Requests without token return 401
  - [ ] Expired token returns 401
  - [ ] Invalid token returns 401

---

### Input Validation

- [ ] **XSS Prevention**
  - [ ] HTML in name escaped correctly
  - [ ] Script tags in description don't execute
  - [ ] Special characters handled safely

- [ ] **SQL Injection**
  - [ ] Search with `'; DROP TABLE prompts; --` safe
  - [ ] Filter with SQL keywords safe

- [ ] **JSON Injection**
  - [ ] Invalid JSON in model_settings rejected
  - [ ] Malformed JSON doesn't break app

---

## 🐛 Edge Cases

### Data Edge Cases

- [ ] **Empty Data**
  - [ ] Empty prompt name shows validation error
  - [ ] Empty description allowed
  - [ ] Empty system_message allowed
  - [ ] Empty model_settings allowed

- [ ] **Very Long Data**
  - [ ] Name > 255 chars truncated/rejected
  - [ ] Very long description displays with ellipsis
  - [ ] Long system_message scrollable in editor

- [ ] **Special Characters**
  - [ ] Emoji in name/description works
  - [ ] Quotes, apostrophes handled correctly
  - [ ] Unicode characters supported

---

### Concurrent Actions

- [ ] **Multiple Users**
  - [ ] User A creates prompt while User B browses
  - [ ] User A deletes prompt while User B viewing

- [ ] **Multiple Tabs**
  - [ ] Edit in Tab 1, delete in Tab 2
  - [ ] Create in Tab 1 reflects in Tab 2 after refresh

---

### Network Conditions

- [ ] **Slow Network**
  - [ ] Loading states appear
  - [ ] Actions don't duplicate if clicked multiple times
  - [ ] Timeout errors handled gracefully

- [ ] **Offline**
  - [ ] Appropriate error message
  - [ ] App doesn't crash
  - [ ] Retry option available

---

## ✅ QA Sign-Off Checklist

Before marking feature as "Done", verify:

### Functionality
- [ ] All automated tests passing
- [ ] All CRUD operations work
- [ ] Authorization rules enforced
- [ ] Edge cases handled
- [ ] Error messages clear and helpful

### User Experience
- [ ] UI matches design system
- [ ] Animations smooth (iOS-style springs)
- [ ] Loading states appropriate
- [ ] Empty states informative
- [ ] Success feedback provided

### Performance
- [ ] Page load < 2s
- [ ] API responses < 1s
- [ ] No memory leaks
- [ ] No console errors

### Security
- [ ] Authorization tested
- [ ] Input validation working
- [ ] No sensitive data exposed
- [ ] CORS configured correctly

### Compatibility
- [ ] Works on Chrome, Firefox, Safari, Edge
- [ ] Responsive on mobile, tablet, desktop
- [ ] Dark mode supported
- [ ] Keyboard navigation functional

---

---

## 🧪 Sprint 25: Prompt Editor Enhancement Tests

### Category 7: Prompt Inputs

#### TC-P019: List Prompt Inputs
**Test:** Manual  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: Prompt with 3 inputs defined
When: User requests /api/prompts/{id}/inputs
Then: Returns all 3 inputs sorted by sort_order
```

---

#### TC-P020: Create Prompt Input
**Test:** Manual  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: User's own prompt
When: User posts /api/prompts/{id}/inputs with valid data
Then: Input is created
```

**Request Body:**
```json
{
  "name": "word_count",
  "label": "Target Word Count",
  "type": "select",
  "options": [{ "value": "500", "label": "Short" }],
  "is_required": true
}
```

---

#### TC-P021: Cannot Create Input for System Prompt
**Test:** Manual  
**Priority:** 🔴 Critical (Security)

**Scenario:**
```
Given: System prompt
When: User attempts to create input
Then: Returns 403 Forbidden
```

---

### Category 8: Prompt Components

#### TC-P022: List Components
**Test:** Manual  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: 2 system components, 3 user components
When: User requests /api/prompt-components
Then: Returns 5 components
```

---

#### TC-P023: Create Component
**Test:** Manual  
**Priority:** 🔴 Critical

**Scenario:**
```
Given: Valid component data
When: User posts /api/prompt-components
Then: Component is created
```

---

#### TC-P024: Unique Component Name per User
**Test:** Manual  
**Priority:** 🟡 High

**Scenario:**
```
Given: User has component named "genre_fantasy"
When: User attempts to create another with same name
Then: Returns 422 validation error
```

---

### Category 9: Variable Autocomplete UI

#### TC-P025: Autocomplete Triggers on {
**Test:** Manual UI  
**Priority:** 🔴 Critical

**Steps:**
1. Open prompt editor
2. Focus on system message textarea
3. Type `{`
4. **Verify:** Autocomplete dropdown appears
5. **Verify:** Variables grouped by category
6. **Verify:** Can search by typing

---

#### TC-P026: Variable Selection
**Test:** Manual UI  
**Priority:** 🔴 Critical

**Steps:**
1. Trigger autocomplete with `{`
2. Use arrow keys to navigate
3. Press Enter to select
4. **Verify:** Variable inserted at cursor
5. **Verify:** Dropdown closes

---

### Category 10: Multi-Message UI

#### TC-P027: Add Message
**Test:** Manual UI  
**Priority:** 🟡 High

**Steps:**
1. Open Instructions tab
2. Click "Add Message" button
3. **Verify:** New message block appears
4. **Verify:** Default role is "User"

---

#### TC-P028: Toggle Message Role
**Test:** Manual UI  
**Priority:** 🟡 High

**Steps:**
1. Add a message
2. Click role badge ("User")
3. **Verify:** Role toggles to "AI"
4. **Verify:** Visual styling changes

---

#### TC-P029: Reorder Messages
**Test:** Manual UI  
**Priority:** 🟢 Medium

**Steps:**
1. Add 3 messages
2. Drag message 3 to position 1
3. **Verify:** Order updated
4. **Verify:** Animation smooth

---

### Category 11: Preview Tab

#### TC-P030: Token Count Display
**Test:** Manual UI  
**Priority:** 🟡 High

**Steps:**
1. Open Preview tab
2. **Verify:** Token count displayed (~X tokens)
3. Edit system message
4. **Verify:** Token count updates

---

#### TC-P031: Copy to Clipboard
**Test:** Manual UI  
**Priority:** 🟢 Medium

**Steps:**
1. Open Preview tab
2. Click "Copy" button
3. **Verify:** Button changes to "Copied!"
4. **Verify:** Full prompt in clipboard

---

#### TC-P032: Sample Data Resolution
**Test:** Manual UI  
**Priority:** 🟡 High

**Steps:**
1. Add `{scene.title}` to system message
2. Open Preview tab
3. Toggle "Show with sample data"
4. **Verify:** Variable replaced with sample value
5. **Verify:** Purple highlight on resolved variable

---

## 🧪 QA Checklist: Sprint 25 Features

### Prompt Editor Tabs

- [ ] **General Tab**
  - [ ] Name field works (required)
  - [ ] Type selector shows 4 options
  - [ ] Model settings inputs work
  - [ ] Cannot edit system prompts

- [ ] **Instructions Tab**
  - [ ] System message textarea works
  - [ ] User message textarea works
  - [ ] Variable autocomplete triggers on `{`
  - [ ] Autocomplete shows categories
  - [ ] Selection inserts variable
  - [ ] ESC closes autocomplete

- [ ] **Advanced Tab**
  - [ ] "Add Input" creates new input
  - [ ] Input form fields work
  - [ ] Select type shows options editor
  - [ ] Remove input works
  - [ ] Components section displays

- [ ] **Description Tab**
  - [ ] Textarea editable
  - [ ] Tips box visible
  - [ ] Character count accurate

- [ ] **Preview Tab**
  - [ ] Shows system/user messages
  - [ ] Shows additional messages
  - [ ] Token count displayed
  - [ ] Copy button works
  - [ ] Sample data toggle works
  - [ ] Variable highlighting works

### Workspace Modal

- [ ] **Prompt Modal opens** from workspace sidebar
- [ ] **All 5 tabs** present in modal
- [ ] **Save/Cancel** buttons work
- [ ] **Clone/Delete** buttons work
- [ ] **Unsaved changes** warning appears

---

## 🚨 Known Issues

**None currently reported.**

---

## 📊 Test Coverage

### Backend Coverage

```
File: PromptController.php
Coverage: 100% (all methods tested)

File: PromptService.php
Coverage: 95% (folder methods not fully tested)

File: Prompt.php
Coverage: 100% (all scopes and methods tested)

File: PromptFolder.php
Coverage: 60% (relationships tested, service methods pending)
```

### Frontend Coverage

```
File: Prompts/Index.vue
Coverage: Manual testing only (E2E tests recommended)

File: PromptCard.vue
Coverage: Manual testing only

File: PromptEditor.vue
Coverage: Manual testing only
```

---

## 📝 Test Data

### Seeder for Testing

```bash
# Run seeder to generate test prompts
php artisan db:seed --class=PromptSeeder

# Creates:
# - 4 system prompts (1 for each type)
# - 10 user prompts (mixed types)
```

### Factory Usage

```php
// Create system prompt
Prompt::factory()->system()->chat()->create();

// Create user prompt
Prompt::factory()->create(['user_id' => $user->id]);

// Create prompt with specific type
Prompt::factory()->prose()->create();

// Create inactive prompt
Prompt::factory()->inactive()->create();
```

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 24: Prompts Library Core](../10-sprints/sprint-24-prompts-library-core.md)
- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **User Journeys:** [Prompts User Journeys](../07-user-journeys/prompts/)

---

*Last Updated: 2026-01-03*
