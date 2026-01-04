# 🧪 Sprint 32: Scene Beat & Writing Panel - Testing Guide

**Sprint:** Sprint 32  
**Features:** Scene Beat TipTap Node, Writing Panel Enhancements, Bug Fixes  
**Status:** ✅ Ready for Testing  
**Last Updated:** 2026-01-04

---

## 📋 Testing Overview

Sprint ini mencakup testing untuk:
1. Scene Beat sebagai TipTap custom node dengan AI generation
2. Selection bubble menu untuk text actions
3. Dark mode compatibility fixes
4. State management improvements
5. Various UX enhancements

---

## 🔧 Prerequisites

### Environment Setup
- [ ] Development server running (`yarn dev`)
- [ ] Database seeded dengan test data
- [ ] At least 1 AI connection configured dan active
- [ ] Test user logged in
- [ ] Test novel dengan minimal 1 scene exists

### Test Data Requirements
```bash
# Ensure prompts seeded
php artisan db:seed --class=PromptSeeder

# Verify AI connection exists
php artisan tinker --execute="dump(App\Models\AIConnection::where('user_id', 1)->count());"
```

---

## 🎯 Test Scenarios

## Test Group 1: Scene Beat - Basic Operations

### TC-32.1.1: Insert Scene Beat via Slash Command
**Priority:** P0 (Critical)

**Steps:**
1. Buka workspace write panel dengan scene aktif
2. Type `/` di editor untuk membuka slash command menu
3. Verify "Scene Beat" command visible di AI section dengan icon violet
4. Click "Scene Beat" command

**Expected:**
- ✅ Scene Beat block muncul di editor dengan purple border/background
- ✅ Beat editor UI showing dengan textarea kosong
- ✅ Word target buttons visible (200, 400, 600)
- ✅ Model selector visible
- ✅ Generate button visible tapi disabled
- ✅ Context menu icon (⋮) visible di top-right

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.1.2: Input Beat Text
**Priority:** P0 (Critical)

**Precondition:** Scene Beat block sudah inserted (TC-32.1.1)

**Steps:**
1. Click pada textarea di beat block
2. Type beat text: "Sarah walks into the abandoned library, dust covering every surface"
3. Observe Generate button state

**Expected:**
- ✅ Text input responsive, no lag
- ✅ Placeholder text disappears saat typing
- ✅ Generate button enabled after text input
- ✅ Beat text stored dalam node state

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.1.3: Word Target Selection
**Priority:** P1 (Important)

**Precondition:** Beat text sudah diinput

**Steps:**
1. Click button "200" word target
2. Observe visual feedback
3. Click button "400" word target
4. Click button "600" word target
5. Click "Custom" dan input "1000"

**Expected:**
- ✅ Selected button highlighted dengan accent color
- ✅ Previous selection cleared saat new target selected
- ✅ Custom input field appears saat "Custom" clicked
- ✅ Custom value accepted dan stored
- ✅ Invalid custom values (< 100 atau > 2000) rejected

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.1.4: AI Model Selection
**Priority:** P1 (Important)

**Precondition:** Beat text sudah diinput

**Steps:**
1. Click dropdown "Select Model"
2. Verify list of available models displayed
3. Select model (e.g., "gpt-4")
4. Observe selected model displayed

**Expected:**
- ✅ Model dropdown shows available models dari default AI connection
- ✅ Models grouped by connection jika multiple connections
- ✅ Default model auto-selected if configured
- ✅ Selected model stored untuk generation

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.1.5: Generate Prose - Happy Path
**Priority:** P0 (Critical)

**Precondition:** Beat text diinput, model selected, word target set

**Steps:**
1. Click "Generate" button
2. Observe generation process
3. Wait for generation complete
4. Verify generated content

**Expected:**
- ✅ Generate button shows loading state (spinner/disabled)
- ✅ Streaming text muncul real-time di preview area
- ✅ "Generating..." atau similar indicator visible
- ✅ Generated prose replaces beat block inline di editor
- ✅ Generated content matches beat description
- ✅ Word count approximately matches target (±20%)
- ✅ Prose properly formatted (paragraphs, capitalization)

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.1.6: Beat Context Menu Actions
**Priority:** P1 (Important)

**Precondition:** Beat block exists di editor

**Steps:**
1. Click context menu icon (⋮) di beat block
2. Verify menu options: Hide, Delete, Clear
3. Test "Hide" → beat collapsed tapi masih exists
4. Click again → beat expands
5. Test "Clear" → beat text cleared tapi block tetap
6. Test "Delete" → beat block removed completely

**Expected:**
- ✅ Context menu opens on icon click
- ✅ All 3 options (Hide, Delete, Clear) visible
- ✅ Hide: Beat collapsed, dapat di-expand kembali
- ✅ Clear: Text cleared, block tetap exists untuk re-input
- ✅ Delete: Block removed entirely dari editor
- ✅ Undo (Ctrl+Z) works untuk restore deleted beat

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 2: Scene Beat - Error Handling

### TC-32.2.1: Generate Without AI Connection
**Priority:** P0 (Critical)

**Precondition:** User tidak punya active AI connection

**Steps:**
1. Deactivate all AI connections di settings
2. Insert Scene Beat block
3. Input beat text
4. Click "Generate"

**Expected:**
- ✅ Error message displayed: "No AI connection available"
- ✅ User directed to configure AI provider
- ✅ Beat block tetap intact
- ✅ No server error atau crash

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.2.2: Network Timeout During Generation
**Priority:** P1 (Important)

**Setup:** Simulate slow network atau API timeout

**Steps:**
1. Start generation process
2. Simulate network interruption (dev tools → offline)
3. Observe error handling

**Expected:**
- ✅ Loading state stops
- ✅ Error message displayed: "Network error" atau similar
- ✅ Retry option available
- ✅ Beat block not replaced dengan partial content
- ✅ User can retry generation

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.2.3: Empty Beat Text Generation
**Priority:** P0 (Critical)

**Steps:**
1. Insert Scene Beat block
2. Leave beat text empty
3. Try clicking Generate button

**Expected:**
- ✅ Generate button remains disabled
- ✅ Tooltip atau message: "Enter beat text to generate"
- ✅ No API call made

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 3: Selection Bubble Menu

### TC-32.3.1: Show Bubble Menu on Text Selection
**Priority:** P1 (Important)

**Steps:**
1. Di editor, write some text: "The old house stood silent"
2. Select text dengan mouse drag
3. Observe bubble menu appears

**Expected:**
- ✅ Bubble menu appears above selected text
- ✅ Menu contains formatting options (B, I, U)
- ✅ Menu contains "Replace" atau AI options
- ✅ Menu positioned correctly, not cut off screen
- ✅ Menu disappears saat selection cleared

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.3.2: Quick Format Actions
**Priority:** P1 (Important)

**Precondition:** Text selected, bubble menu visible

**Steps:**
1. Click "B" (Bold) button
2. Click "I" (Italic) button
3. Click "U" (Underline) button
4. Verify formatting applied

**Expected:**
- ✅ Each click applies corresponding format
- ✅ Text remains selected after format
- ✅ Multiple formats can be combined
- ✅ Visual feedback for active formats

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 4: Dark Mode Compatibility

### TC-32.4.1: Writing Panel Text Visibility - Dark Mode
**Priority:** P0 (Critical)

**Steps:**
1. Ensure light mode active
2. Navigate to workspace write panel
3. Type some text: "This is a test paragraph with multiple sentences to verify text visibility."
4. Toggle dark mode (theme switcher)
5. Verify text visibility

**Expected:**
- ✅ Light mode: Text color adalah dark (zinc-900 atau similar)
- ✅ Dark mode: Text color adalah light (zinc-100 atau similar)
- ✅ Text fully readable, good contrast dengan background
- ✅ No "invisible text" issue
- ✅ Transition smooth antara light and dark mode

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.4.2: Scene Beat Block - Dark Mode
**Priority:** P1 (Important)

**Steps:**
1. Insert Scene Beat block di dark mode
2. Input beat text
3. Verify UI element visibility
4. Toggle to light mode
5. Toggle back to dark mode

**Expected:**
- ✅ Beat block border/background visible di dark mode
- ✅ Beat text input readable (light text on dark background)
- ✅ Buttons dan icons visible dengan adequate contrast
- ✅ No "invisible buttons" issue
- ✅ Model selector dropdown readable

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 5: State Management & Content Persistence

### TC-32.5.1: Content Persistence - Panel Switch
**Priority:** P0 (Critical)

**Steps:**
1. Navigate to workspace → Write panel
2. Type text: "Original content that should persist"
3. Wait for auto-save indicator (should save automatically)
4. Switch to "Chat" panel
5. Switch back to "Write" panel
6. Verify content displayed

**Expected:**
- ✅ Original content tetap visible di write panel
- ✅ No reversion to previous state
- ✅ No "content loss" saat switch panel
- ✅ Auto-save indicator showed save success sebelum switch
- ✅ Write panel me-refetch latest content dari server

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.5.2: Content Persistence - Scene Switch
**Priority:** P0 (Critical)

**Steps:**
1. Di write panel, edit scene A content
2. Wait for auto-save
3. Switch to scene B (via sidebar atau scene selector)
4. Edit scene B content
5. Switch back to scene A
6. Verify scene A shows latest edited content

**Expected:**
- ✅ Scene A content matches last edit before switch
- ✅ Scene B content matches last edit
- ✅ No cross-scene content leak
- ✅ Each scene maintains independent state

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.5.3: Content Persistence - Page Reload
**Priority:** P1 (Important)

**Steps:**
1. Edit content di write panel
2. Wait for auto-save
3. Perform hard refresh (Ctrl+Shift+R)
4. Verify content after reload

**Expected:**
- ✅ Latest content visible after reload
- ✅ No "old content" displayed
- ✅ Auto-save working correctly before reload
- ✅ Database contains correct content

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 6: UI/UX Enhancements

### TC-32.6.1: Sidebar Text Size Improvement
**Priority:** P2 (Enhancement)

**Steps:**
1. Open workspace dengan sidebar visible
2. Observe sidebar section titles (Codex, Prompts, Scenes, dll)
3. Compare dengan previous version (jika available)
4. Test di mobile viewport (< 768px)

**Expected:**
- ✅ Sidebar title text noticeably larger than before
- ✅ Text readable di mobile viewport
- ✅ No layout break atau overflow
- ✅ Font size approximately `text-base` (16px) instead of `text-sm` (14px)
- ✅ Better visual hierarchy

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.6.2: Model Selector - Auto-Fetch Models
**Priority:** P1 (Important)

**Steps:**
1. Configure AI connection di settings dengan default flag
2. Navigate to scene beat atau prose generation
3. Open model selector dropdown immediately
4. Verify models available

**Expected:**
- ✅ Models auto-fetched saat default connection set
- ✅ No need manual action untuk load models
- ✅ Loading indicator saat fetching (jika applicable)
- ✅ Models displayed immediately di dropdown
- ✅ No empty dropdown issue

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Group 7: Integration & Regression

### TC-32.7.1: Existing Prose Generation Still Works
**Priority:** P0 (Critical)

**Purpose:** Ensure existing prose generation flow tidak broken oleh changes

**Steps:**
1. Navigate to any scene with sections
2. Use existing prose generation panel (bukan beat)
3. Generate prose dengan custom mode
4. Verify generation success

**Expected:**
- ✅ Existing prose generation UI accessible
- ✅ Custom prompt input working
- ✅ Generation streaming working
- ✅ Generated content applied correctly
- ✅ No regression dari Sprint 31 features

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.7.2: Text Replacement Still Works
**Priority:** P0 (Critical)

**Steps:**
1. Select text di editor (> 4 words)
2. Access text replacement menu
3. Choose "Expand" action
4. Generate replacement
5. Apply replacement

**Expected:**
- ✅ Text replacement menu accessible
- ✅ All replacement options (Expand, Rephrase, Shorten) working
- ✅ Preview panel showing before/after
- ✅ Apply replaces text correctly
- ✅ No regression dari Sprint 31 features

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-32.7.3: Slash Commands - All Commands Available
**Priority:** P1 (Important)

**Steps:**
1. Type `/` di editor
2. Verify all slash commands present:
   - AI section: Scene Beat, Continue Writing, AI Custom
   - Codex section: Codex Addition
   - Formatting section: Section, Heading 1-6, dll
3. Test each command execution

**Expected:**
- ✅ All existing commands tetap available
- ✅ Scene Beat command added di AI section
- ✅ Command icons dan labels correct
- ✅ Filtering working (type after `/`)
- ✅ Each command executes correctly

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## 📱 Mobile Testing

### MC-32.1: Scene Beat on Mobile
**Priority:** P1 (Important)

**Device:** Mobile viewport (375px width)

**Steps:**
1. Access workspace di mobile viewport
2. Insert Scene Beat
3. Input beat text (use mobile keyboard)
4. Generate prose
5. Verify responsive behavior

**Expected:**
- ✅ Beat block responsive, not overflow screen
- ✅ Textarea usable dengan mobile keyboard
- ✅ Buttons touchable, adequate tap target size (>44px)
- ✅ Model selector dropdown usable di mobile
- ✅ Generated content readable without horizontal scroll

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### MC-32.2: Sidebar Text Readability - Mobile
**Priority:** P1 (Important)

**Steps:**
1. View workspace sidebar di mobile viewport
2. Observe section titles dan labels
3. Verify readability

**Expected:**
- ✅ Text size adequate untuk mobile (tidak terlalu kecil)
- ✅ No squinting required untuk read labels
- ✅ Font size approximately 16px (touch-friendly)

**Actual:** _____________

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## 🔍 Browser Compatibility

Test di browsers berikut:

### Desktop Browsers
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest, macOS)

### Mobile Browsers
- [ ] Chrome Mobile (Android)
- [ ] Safari Mobile (iOS)

**Note:** Focus testing di Chrome/Edge (primary target). Other browsers basic smoke testing.

---

## ⚠️ Known Issues & Limitations

### Current Sprint
- None identified yet

### Future Improvements
- Beat templates atau presets untuk common scene types
- Batch beat generation untuk multiple beats sekaligus
- Beat versioning (save multiple versions dari generated prose)

---

## 📊 Test Summary Template

```
Total Test Cases: ___
Passed: ___
Failed: ___
Blocked: ___
Pass Rate: ____%

Critical Issues Found: ___
Regressions Found: ___

Overall Status: ☐ Ready to Ship ☐ Needs Fixes ☐ Blocked

Tester Name: _______________
Test Date: __________________
Environment: ________________
```

---

## 🐛 Bug Report Template

Jika menemukan bug saat testing, gunakan format berikut:

```markdown
## Bug ID: BUG-32-XXX

**Title:** [Short descriptive title]

**Severity:** ☐ Critical ☐ High ☐ Medium ☐ Low

**Test Case:** TC-32.X.X

**Steps to Reproduce:**
1. Step 1
2. Step 2
3. Step 3

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Screenshots:**
[Attach if applicable]

**Browser/Device:**
[Browser and version]

**Additional Notes:**
[Any other relevant information]
```

---

## 📝 Testing Notes

### Tips untuk Testers
1. **Test di dark mode AND light mode** - Dark mode fixes adalah critical
2. **Test content persistence thoroughly** - Ini adalah high-priority bug fix
3. **Verify auto-save before switching panels** - Lihat auto-save indicator
4. **Test dengan real AI connections** - Mock responses bisa miss edge cases
5. **Test undo/redo** (Ctrl+Z/Ctrl+Y) - Ensure editor history intact

### Common Pitfalls
- Forget to check dark mode → Always toggle theme saat testing
- Not waiting for auto-save → Wait 2-3 detik setelah edit
- Testing dengan stale cache → Hard refresh (Ctrl+Shift+R) when needed
- Not testing mobile viewport → Use DevTools responsive mode

---

*Last Updated: 2026-01-04*  
*Testing Coordinator: QA Team*
