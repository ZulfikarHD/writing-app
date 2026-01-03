# Testing Guide: Writing Tools (FG-06.3)

## Overview

Dokumen ini berisi comprehensive test plan untuk Writing Tools feature yang bertujuan untuk memastikan semua functionality bekerja dengan benar, yaitu: highlighting, beat workflow, subplot assignment, dan formatting tools.

---

## Automated Tests

### PHPUnit Tests

**File**: `tests/Feature/WritingToolsTest.php`

| Test ID | Scenario | Type | Status |
|---------|----------|------|--------|
| WT-001 | Can create beat section | Feature | ✅ Pass |
| WT-002 | Can mark beat section as completed | Feature | ✅ Pass |
| WT-003 | Beat section has correct default AI visibility | Feature | ✅ Pass |
| WT-004 | Can get novel subplots | Feature | ✅ Pass |
| WT-005 | Can assign subplot to scene | Feature | ✅ Pass |
| WT-006 | Cannot assign non-subplot entry to scene | Feature | ✅ Pass |
| WT-007 | Can get scene subplots | Feature | ✅ Pass |
| WT-008 | Can remove subplot from scene | Feature | ✅ Pass |
| WT-009 | Reassigning subplot updates existing progression | Feature | ✅ Pass |
| WT-010 | Cannot assign subplot from other novel | Feature | ✅ Pass |
| WT-011 | Cannot access other users scene subplots | Feature | ✅ Pass |
| WT-012 | Cannot access other users novel subplots | Feature | ✅ Pass |

**Run Command:**

```bash
php artisan test --filter=WritingToolsTest
```

**Expected Result**: All 12 tests passing with 32 assertions

---

## Manual Testing Checklist

### Desktop Testing

#### Text Highlighting

- [ ] **HL-001**: Highlight text dengan yellow color
  - Select text dalam editor
  - Click highlight button → select yellow
  - Text should have yellow background
  - Check TipTap JSON contains `highlight` mark

- [ ] **HL-002**: Highlight dengan berbagai colors
  - Test all 6 preset colors (yellow, green, blue, pink, orange, purple)
  - Each color should render correctly
  - Color picker should close after selection

- [ ] **HL-003**: Remove highlight
  - Select highlighted text
  - Click highlight button → click "Remove"
  - Highlight should be removed
  - Text returns to normal

- [ ] **HL-004**: Keyboard shortcut
  - Select text
  - Press Ctrl+Shift+H
  - Text should be highlighted yellow

- [ ] **HL-005**: Multiple highlights
  - Highlight text with yellow
  - Select same text again
  - Change to blue → yellow should be replaced with blue

- [ ] **HL-006**: Highlight persistence
  - Highlight text
  - Save scene (auto-save)
  - Refresh page
  - Highlights should persist

#### Beat Section Workflow

- [ ] **BEAT-001**: Create beat section
  - Use slash command `/beat` atau section menu
  - Beat section should be created dengan green color
  - Default `is_completed = false`

- [ ] **BEAT-002**: Beat completion checkbox
  - Click completion checkbox (circle icon)
  - Icon changes to checkmark with green color
  - Type badge shows line-through
  - Click again → unchecks

- [ ] **BEAT-003**: Expand to prose button
  - Add content to beat section (bullet points)
  - "Expand" button should appear (lightning icon)
  - Button hidden when section collapsed
  - Button hidden when section empty

- [ ] **BEAT-004**: Expand to prose flow
  - Click "Expand" button
  - ProseGenerationPanel opens
  - Beat content prefilled dalam beat input
  - Mode set to `scene_beat`
  - Can generate prose successfully

- [ ] **BEAT-005**: Beat completion persists
  - Mark beat as completed
  - Refresh page
  - Completion state should persist

#### Subplot Assignment

- [ ] **SUB-001**: Open subplot selector
  - Open Scene Info panel (Ctrl+I)
  - Scroll to "Subplots" section
  - Section visible with dropdown button

- [ ] **SUB-002**: View available subplots
  - Click "Add subplot" button
  - Dropdown shows list of subplots dari codex
  - Only shows subplot type entries
  - Ordered alphabetically

- [ ] **SUB-003**: Assign subplot
  - Select subplot dari dropdown
  - Subplot badge appears dengan remove button
  - Dropdown closes automatically
  - Subplot tidak muncul di dropdown lagi

- [ ] **SUB-004**: Remove subplot
  - Click X button pada subplot badge
  - Subplot removed
  - Subplot muncul kembali di dropdown

- [ ] **SUB-005**: Multiple subplots
  - Assign 3 different subplots
  - All badges should display
  - Can remove any subplot independently

- [ ] **SUB-006**: No subplots available
  - Novel tanpa subplot entries
  - Should show message: "No subplots in codex..."

#### Blockquote Formatting

- [ ] **BQ-001**: Apply blockquote
  - Select paragraph atau create new
  - Click blockquote button dalam toolbar
  - Text should have violet left border + italic
  - Button shows active state (violet highlight)

- [ ] **BQ-002**: Remove blockquote
  - Click blockquote button again
  - Blockquote formatting removed
  - Button active state removed

- [ ] **BQ-003**: Keyboard shortcut
  - Select text atau position cursor
  - Press Ctrl+Shift+B
  - Blockquote applied/removed

### Mobile Testing (Responsive)

#### iPhone/Android (< 640px)

- [ ] **MOB-001**: Toolbar buttons visible
  - All formatting buttons accessible
  - No horizontal overflow
  - Icons readable at small size

- [ ] **MOB-002**: Highlight dropdown
  - Color picker grid responsive
  - Touch targets adequate (min 44x44px)
  - Dropdown doesn't overflow screen

- [ ] **MOB-003**: Beat expand button
  - "Expand" button visible on mobile
  - Button text hidden, only icon shown
  - Tap triggers prose generation

- [ ] **MOB-004**: Subplot dropdown
  - Dropdown scrollable if many subplots
  - Touch targets adequate
  - Badges wrap properly

#### Tablet (640px - 1024px)

- [ ] **TAB-001**: Layout responsive
  - Editor toolbar tidak overflow
  - Scene metadata panel width appropriate
  - All features accessible

---

## Edge Cases Testing

| ID | Scenario | Expected Behavior | Status |
|----|----------|-------------------|--------|
| EDGE-001 | Highlight overlapping text | Later highlight replaces earlier | ⏳ Manual |
| EDGE-002 | Beat dengan 0 words | Expand button hidden | ⏳ Manual |
| EDGE-003 | Assign same subplot twice | Updates existing progression | ✅ Auto |
| EDGE-004 | Delete subplot entry | Assignment remains, shows entry missing | ⏳ Manual |
| EDGE-005 | Scene with 10+ subplots | Dropdown scrollable | ⏳ Manual |
| EDGE-006 | Blockquote dalam section | Formatting applies correctly | ⏳ Manual |
| EDGE-007 | Completion checkbox rapid toggle | No race condition | ⏳ Manual |
| EDGE-008 | Highlight during autosave | Save doesn't interfere | ⏳ Manual |

---

## Security Testing

| ID | Test | Expected Result | Status |
|----|------|-----------------|--------|
| SEC-001 | Assign subplot from other user's novel | 404 Not Found | ✅ Auto |
| SEC-002 | Access other user's scene subplots | 403 Forbidden | ✅ Auto |
| SEC-003 | Access other user's novel subplots | 403 Forbidden | ✅ Auto |
| SEC-004 | SQL injection in subplot note | Sanitized, no injection | ⏳ Manual |
| SEC-005 | XSS in highlight content | Escaped, no XSS | ⏳ Manual |

---

## Performance Testing

| ID | Test | Acceptance Criteria | Status |
|----|------|---------------------|--------|
| PERF-001 | Load scene with 50 highlights | < 500ms render | ⏳ Manual |
| PERF-002 | Load scene with 20 beat sections | < 1s render | ⏳ Manual |
| PERF-003 | Dropdown with 100 subplots | < 200ms open | ⏳ Manual |
| PERF-004 | Toggle beat completion 20x rapid | No lag, all update | ⏳ Manual |
| PERF-005 | Expand beat with 500 word content | < 100ms panel open | ⏳ Manual |

---

## Regression Testing

Setelah implement Writing Tools, verify features berikut masih berfungsi:

### Editor Core

- [ ] Basic text editing (type, delete, undo, redo)
- [ ] Bold, italic, underline, strikethrough
- [ ] Headings (H1, H2, H3)
- [ ] Lists (bullet, numbered)
- [ ] Text alignment (left, center, right, justify)

### Sections System

- [ ] Create content/note/alternative sections
- [ ] Collapse/expand sections
- [ ] Toggle AI visibility per section
- [ ] Reorder sections via drag & drop
- [ ] Delete sections

### AI Writing Features

- [ ] Prose generation panel
- [ ] Text replacement menu
- [ ] Slash commands
- [ ] Chat with scene

### Scene Metadata

- [ ] Title, subtitle, summary editing
- [ ] Status updates
- [ ] Label assignment
- [ ] Notes editing
- [ ] AI exclusion toggle

---

## Cross-Browser Testing

| Browser | Version | Desktop | Mobile | Status |
|---------|---------|---------|--------|--------|
| Chrome | Latest | ✅ | ✅ | ⏳ |
| Firefox | Latest | ✅ | ✅ | ⏳ |
| Safari | Latest | ✅ | ✅ | ⏳ |
| Edge | Latest | ✅ | N/A | ⏳ |

---

## Accessibility Testing

- [ ] **A11Y-001**: Keyboard navigation
  - Tab through all interactive elements
  - Highlight button accessible via keyboard
  - Dropdown navigable with arrow keys

- [ ] **A11Y-002**: Screen reader
  - Button labels announced correctly
  - Completion checkbox state announced
  - Dropdown options announced

- [ ] **A11Y-003**: Color contrast
  - All text readable on highlighted backgrounds
  - Button states have sufficient contrast
  - Color picker icons distinguishable

- [ ] **A11Y-004**: Focus indicators
  - Visible focus ring on all focusable elements
  - Focus order logical
  - No keyboard traps

---

## Test Data Setup

### Prerequisites

```bash
# 1. Create test novel
php artisan tinker --execute="
\$user = User::first();
\$novel = Novel::factory()->create(['user_id' => \$user->id]);
\$chapter = Chapter::factory()->create(['novel_id' => \$novel->id]);
\$scene = Scene::factory()->create(['chapter_id' => \$chapter->id]);
echo 'Scene ID: ' . \$scene->id;
"

# 2. Create subplot codex entries
php artisan tinker --execute="
\$novel = Novel::find(1);
CodexEntry::factory()->count(5)->create([
    'novel_id' => \$novel->id,
    'type' => 'subplot'
]);
echo 'Subplots created';
"

# 3. Create beat sections
php artisan tinker --execute="
\$scene = Scene::find(1);
SceneSection::factory()->create([
    'scene_id' => \$scene->id,
    'type' => 'beat',
    'title' => 'Scene Planning'
]);
echo 'Beat section created';
"
```

---

## Bug Tracking

| ID | Description | Severity | Status | Fixed In |
|----|-------------|----------|--------|----------|
| BUG-001 | (None reported yet) | - | - | - |

---

## Test Coverage

- **Backend**: 12 automated tests, 32 assertions
- **API Endpoints**: 100% coverage
- **Authorization**: 100% coverage
- **Edge Cases**: 8 scenarios identified
- **Manual Tests**: 50+ checkpoints

---

## Testing Schedule

| Phase | Duration | Responsible | Status |
|-------|----------|-------------|--------|
| Unit Tests | 1 day | Backend Dev | ✅ Complete |
| API Tests | 1 day | Backend Dev | ✅ Complete |
| Manual QA | 2 days | QA Team | ⏳ Pending |
| UAT | 3 days | Product Owner | ⏳ Pending |

---

*Last Updated: 2026-01-04*
