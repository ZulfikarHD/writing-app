# 🧪 Testing Guide: Prompt Model & Sharing (Sprint 28)

**Feature:** FG-05.5 - Model Settings & Prompt Sharing  
**Sprint:** Sprint 28  
**Last Updated:** 2026-01-04

---

## 📋 Pre-Test Setup

### Prerequisites
- [ ] User logged in
- [ ] At least 2 custom prompts exist
- [ ] At least 1 prompt dengan inputs exist
- [ ] Browser support clipboard API (Chrome/Edge/Firefox modern versions)

### Test Data Preparation
```bash
php artisan db:seed --class=PromptSeeder
```

---

## ✅ Feature Checklist

### F-05.5.1: Tuning Model Settings

#### Stop Sequences
- [ ] Add stop sequence: Input field works
- [ ] Add stop sequence: Press Enter adds to list
- [ ] Add stop sequence: Empty string ignored
- [ ] Add stop sequence: Duplicate prevented
- [ ] Remove stop sequence: X button removes from list
- [ ] Stop sequences: Max 10 sequences enforced (UI hint)
- [ ] Stop sequences: Persisted on save
- [ ] Stop sequences: Display di card preview

#### Repetition Penalty
- [ ] Input field accepts 0.0 - 2.0 range
- [ ] Default value 1.0 shown di placeholder
- [ ] Step 0.1 working
- [ ] Value persisted on save
- [ ] Shows di card preview when set

#### Tooltips Enhancement
- [ ] Temperature tooltip shows with explanation
- [ ] Max Tokens tooltip shows usage guide
- [ ] Top P tooltip dengan practical examples
- [ ] Frequency Penalty tooltip clear explanation
- [ ] Presence Penalty tooltip detailed
- [ ] Repetition Penalty tooltip (if supported)
- [ ] Tooltips readable di dark mode

---

### F-05.5.2: Clone a Prompt

#### From PromptCard Menu
- [ ] Three-dot menu appears on hover/click
- [ ] Clone option visible di dropdown
- [ ] Click Clone: Creates copy dengan "(Copy)" suffix
- [ ] Cloned prompt editable immediately
- [ ] All settings preserved (inputs, model_settings, messages)
- [ ] Folder path preserved
- [ ] System prompts can be cloned
- [ ] Success toast shown after clone

#### From Editor Header
- [ ] Clone button visible di editor
- [ ] Works same as card menu clone
- [ ] Redirects to editor of cloned prompt

---

### F-05.5.3: Sharing Prompts

#### Export to Clipboard
- [ ] "Copy to Clipboard" option di PromptCard menu
- [ ] Click action: Data copied to clipboard
- [ ] Success toast: "Prompt copied to clipboard"
- [ ] Export includes all data (inputs, components refs, settings)
- [ ] Format: Compressed base64 dengan "NCPROMPT:" header
- [ ] Works on prompts dengan inputs
- [ ] Works on prompts dengan complex settings

#### Import from Clipboard
- [ ] "Create from Clipboard" option di New dropdown
- [ ] PromptImportModal opens
- [ ] Auto-read clipboard (if permission granted)
- [ ] Manual paste textarea available
- [ ] Invalid format shows error message
- [ ] Valid format enables preview

#### Import Preview
- [ ] Preview shows prompt name
- [ ] Preview shows type badge
- [ ] Preview shows message count
- [ ] Preview shows input count (if any)
- [ ] Preview shows component usage count (if any)
- [ ] Preview shows model settings summary
- [ ] Name conflict detected and highlighted
- [ ] Import button disabled untuk invalid data

#### Import Execution
- [ ] Click Import: Prompt added to library
- [ ] Success toast shown
- [ ] Redirects to editor for new prompt
- [ ] All data preserved (inputs, settings, messages)
- [ ] Components referenced but not imported (expected behavior)
- [ ] Modal closes after successful import

---

### F-05.5.4: Organize Prompts into Submenus

#### Folder Syntax
- [ ] TabGeneral shows folder hint below name field
- [ ] Format " / " (space-slash-space) explained
- [ ] Example: "Plot / Twist / Foreshadowing"
- [ ] Validation: No double separators allowed

#### FolderTree Display
- [ ] Library shows prompts grouped by folders
- [ ] Nested structure rendered correctly
- [ ] Expand/collapse folder arrows
- [ ] Folder icons color-coded
- [ ] Prompt count shown per folder
- [ ] Root-level prompts (no folder) shown first

#### Nested Selector
- [ ] PromptSelector shows folder structure
- [ ] Folders expandable/collapsible
- [ ] Nested folders indented correctly
- [ ] Prompt count badge per folder
- [ ] Search expands all folders
- [ ] Selected prompt highlighted

---

## 🔧 Manual Testing Procedures

### Test Case: TC-MS-01 - Stop Sequences Management

**Objective:** Verify stop sequences dapat ditambahkan dan dihapus

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Open prompt editor, go to General tab | Model settings visible | ☐ |
| 2 | Scroll to Stop Sequences section | Input field dan list visible | ☐ |
| 3 | Type "END" dan press Enter | Tag muncul dengan "END" | ☐ |
| 4 | Type "STOP" dan press Enter | Second tag muncul | ☐ |
| 5 | Try add duplicate "END" | Tidak ditambahkan, input cleared | ☐ |
| 6 | Try add empty string | Ignored, input cleared | ☐ |
| 7 | Click X pada "STOP" tag | Tag removed dari list | ☐ |
| 8 | Save prompt | Stop sequences tersimpan | ☐ |
| 9 | Reload prompt | Stop sequences loaded correctly | ☐ |

---

### Test Case: TC-MS-02 - Clone Prompt

**Objective:** Verify prompt dapat di-clone dengan semua data preserved

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Navigate to Prompts library | Prompt list visible | ☐ |
| 2 | Hover over prompt card | Three-dot menu appears | ☐ |
| 3 | Click three-dot menu | Dropdown shows: Clone, Copy, Delete | ☐ |
| 4 | Click "Clone" option | Loading state shown briefly | ☐ |
| 5 | - | New prompt created dengan "(Copy)" suffix | ☐ |
| 6 | - | Redirected to editor | ☐ |
| 7 | Check all tabs | All data sama dengan original | ☐ |
| 8 | Check inputs | Inputs preserved | ☐ |
| 9 | Check model settings | Settings preserved | ☐ |

---

### Test Case: TC-MS-03 - Export & Import Prompt

**Objective:** Verify prompt dapat di-export dan di-import via clipboard

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Open prompt card menu | Three-dot menu visible | ☐ |
| 2 | Click "Copy to Clipboard" | Success toast appears | ☐ |
| 3 | - | Clipboard contains base64 string | ☐ |
| 4 | - | String starts with "NCPROMPT:" | ☐ |
| 5 | Click "New" dropdown → "Create from Clipboard" | Import modal opens | ☐ |
| 6 | - | Clipboard auto-read (if permission) | ☐ |
| 7 | Preview panel shows prompt details | Name, type, counts visible | ☐ |
| 8 | Edit name jika conflict | Name field editable | ☐ |
| 9 | Click "Import Prompt" | Prompt added, redirect to editor | ☐ |
| 10 | Verify all data | Inputs, settings, messages correct | ☐ |

---

### Test Case: TC-MS-04 - Folder Organization

**Objective:** Verify folder structure works correctly

| Step | Action | Expected Result | Pass/Fail |
|------|--------|-----------------|-----------|
| 1 | Create prompt with name "Plot / Twist / Foreshadowing" | Name accepted | ☐ |
| 2 | Go to library | Prompt grouped under Plot → Twist | ☐ |
| 3 | FolderTree shows nested structure | Folders expandable | ☐ |
| 4 | Display name shows only "Foreshadowing" | Last part after separator | ☐ |
| 5 | PromptSelector shows folder dropdown | Nested structure visible | ☐ |
| 6 | Search for prompt | All folders expand automatically | ☐ |

---

## 🧪 Automated Test Scenarios

### Backend Tests
```php
// Test: PromptSharingServiceTest
test('exports prompt to compressed base64')
test('validates import data structure')
test('imports prompt successfully')
test('rejects invalid import format')
test('handles missing components gracefully')

// Test: PromptSharingControllerTest  
test('GET /prompts/{id}/export returns valid format')
test('POST /prompts/import/preview validates structure')
test('POST /prompts/import creates prompt')
test('import enforces unique name per user')
```

### Frontend Tests
```typescript
// Test: usePromptSharing.test.ts
test('copyToClipboard writes to navigator.clipboard')
test('readFromClipboard reads and validates format')
test('parsePromptData handles invalid JSON')
test('parsePromptData validates signature')

// Test: PromptImportModal.test.ts
test('validates clipboard data on mount')
test('shows error for invalid format')
test('disables import for invalid data')
test('emits imported event on success')

// Test: FolderTree.test.ts
test('renders nested folder structure')
test('expands/collapses folders')
test('shows correct prompt count per folder')
```

---

## 🐛 Edge Cases & Error Handling

| Scenario | Expected Behavior | Implementation |
|----------|-------------------|----------------|
| Empty stop sequences array | Save as null, tidak error | Filter empty before save |
| Stop sequence dengan newline | Trimmed automatically | `.trim()` before add |
| 11th stop sequence | Warning shown, add disabled | UI validation |
| Export prompt without inputs | Export success, inputs array kosong | Handle null/undefined |
| Import dengan missing component refs | Import success, show warning | Non-blocking warning |
| Clipboard permission denied | Show manual paste textarea | Graceful fallback |
| Import duplicate name | Auto-append "(1)", "(2)", etc. | Backend deduplication |
| Folder depth > 5 levels | Works but discouraged | UI hint only |
| Folder name dengan special chars | Allowed except " / " | No strict validation |

---

## 📱 Mobile Testing Checklist

- [ ] Stop sequences tag input works on mobile
- [ ] Card dropdown menu accessible via tap
- [ ] Import modal responsive layout
- [ ] Folder tree collapsible on narrow screens
- [ ] Quick prompts drawer slides from bottom
- [ ] Brainstorming panel full-screen on mobile
- [ ] Clipboard operations work on mobile browsers
- [ ] Toast notifications visible dan dismissable

---

## 🔒 Security Testing

- [ ] Export requires ownership verification
- [ ] Import cannot bypass folder restrictions
- [ ] Clipboard API requests user permission
- [ ] XSS prevention: Prompt content sanitized
- [ ] CSRF token validated on all mutations
- [ ] Rate limiting for clone operations
- [ ] No sensitive data exposed in export

---

## ⚡ Performance Testing

- [ ] Export large prompt (10+ inputs): < 500ms
- [ ] Import parsing: < 200ms
- [ ] FolderTree render dengan 100 prompts: < 1s
- [ ] PromptSelector open dengan folders: < 300ms
- [ ] Quick prompts drawer lazy load: < 200ms

---

## 🔗 Related Documentation

- **Sprint Doc:** [Sprint 28 - Model & Sharing](../10-sprints/sprint-28-prompt-model-sharing.md)
- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **Feature Spec:** `scrum/epic-planning/05-EPIC-prompts-system.md`

---

*Last Updated: 2026-01-04*
