# 🧪 Testing Guide: Codex Sprint 16 Features

**Version:** 1.0.0  
**Date:** 2026-01-01  
**Sprint:** Sprint 16  
**Status:** ✅ All Tests Passing

---

## 📋 Overview

Testing guide untuk Sprint 16 Codex v2 Enhancements yang mencakup Category Tag Integration (US-12.13), Mention Tracking in Summaries (F-12.1.4), dan Chat Mention Tracking infrastructure (F-12.1.5), yaitu: memastikan auto-linking categories bekerja dengan benar, mention detection akurat di content dan summary, dan source tracking berfungsi sesuai requirement.

---

## ✅ Test Summary

### Test Statistics

| Metric | Value |
|--------|-------|
| **Total Tests** | 100 |
| **Feature Tests** | 88 |
| **Unit Tests** | 12 |
| **Status** | ✅ All Passing |
| **Assertions** | 309 |
| **Coverage** | ~85% (Codex module) |

### Test Execution Time

```bash
Tests:    100 passed (309 assertions)
Duration: 2.38s
```

---

## 🧪 Automated Tests

### Feature Tests: Category Tag Integration

File: `tests/Feature/CodexTest.php`

#### TC-CT-001: Create Category with Linked Tag

**Test Method:** `test_can_create_category_with_linked_tag()`

**Purpose:** Verify category dapat dibuat dengan linked tag

**Steps:**
1. Create a tag
2. POST `/api/novels/{novel}/codex/categories` dengan `linked_tag_id`
3. Verify response includes linked tag details
4. Verify database record created dengan linking

**Assertions:**
- ✅ Response status 201 Created
- ✅ Category name matches
- ✅ `linked_tag_id` matches
- ✅ `has_auto_linking` is true
- ✅ Database record exists dengan correct tag link

---

#### TC-CT-002: Update Category with Linked Tag

**Test Method:** `test_can_update_category_with_linked_tag()`

**Purpose:** Verify existing category dapat di-update untuk add tag link

**Steps:**
1. Create category tanpa link
2. Create a tag
3. PATCH `/api/codex/categories/{category}` dengan `linked_tag_id`
4. Verify link applied

**Assertions:**
- ✅ Response status 200 OK
- ✅ `linked_tag_id` updated
- ✅ `has_auto_linking` is true

---

#### TC-CT-003: Create Category with Linked Detail Value

**Test Method:** `test_can_create_category_with_linked_detail_value()`

**Purpose:** Verify category dapat di-link ke detail definition + value

**Steps:**
1. Create detail definition (dropdown type) dengan options
2. POST category dengan `linked_detail_definition_id` dan `linked_detail_value`
3. Verify linking fields in response

**Assertions:**
- ✅ Response includes detail definition link
- ✅ Response includes detail value
- ✅ `has_auto_linking` is true

---

#### TC-CT-004: Category Auto-Links Entries by Tag

**Test Method:** `test_category_auto_links_entries_by_tag()`

**Purpose:** Verify entries dengan linked tag otomatis muncul di category

**Steps:**
1. Create category dengan `linked_tag_id`
2. Create entry WITH the tag
3. Create entry WITHOUT the tag
4. GET `/api/codex/categories/{category}/preview-entries`
5. Verify only tagged entry appears

**Assertions:**
- ✅ Preview returns only 1 entry
- ✅ Entry is the one dengan matching tag
- ✅ `count` is 1
- ✅ `has_auto_linking` is true

---

#### TC-CT-005: Category Auto-Links Entries by Detail Value

**Test Method:** `test_category_auto_links_entries_by_detail_value()`

**Purpose:** Verify entries dengan matching detail value otomatis muncul

**Steps:**
1. Create detail definition (dropdown)
2. Create category linked to definition + value "Protagonist"
3. Create entry WITH detail value "Protagonist"
4. Create entry WITH detail value "Antagonist"
5. Preview entries

**Assertions:**
- ✅ Only entry dengan "Protagonist" value appears
- ✅ Entry dengan "Antagonist" does NOT appear
- ✅ Count matches

---

#### TC-CT-006: Category Format Includes Auto-Link Fields

**Test Method:** `test_category_format_includes_auto_link_fields()`

**Purpose:** Verify API response format includes all auto-linking fields

**Steps:**
1. Create category dengan tag link
2. GET `/api/novels/{novel}/codex/categories`
3. Verify response structure

**Assertions:**
- ✅ Response includes `linked_tag_id`
- ✅ Response includes `linked_tag` object
- ✅ Response includes `linked_detail_definition_id`
- ✅ Response includes `linked_detail_definition` object
- ✅ Response includes `linked_detail_value`
- ✅ Response includes `has_auto_linking`
- ✅ Response includes `auto_linked_count`
- ✅ Response includes `total_entry_count`

---

### Feature Tests: Summary Mention Tracking

File: `tests/Feature/CodexTest.php`

#### TC-SM-001: Mentions Tracked from Scene Summary

**Test Method:** `test_mentions_are_tracked_from_scene_summary()`

**Purpose:** Verify mentions detected di scene summary

**Steps:**
1. Create entry "Elena"
2. Create scene dengan `content: null`, `summary: "Elena arrives at the castle."`
3. Run scan mentions
4. Verify mention created

**Assertions:**
- ✅ Mention record exists
- ✅ Entry ID matches
- ✅ Scene ID matches

---

#### TC-SM-002: Mentions from Both Content and Summary

**Test Method:** `test_mentions_tracked_from_both_content_and_summary()`

**Purpose:** Verify mentions detected dan combined dari content + summary

**Steps:**
1. Create entry "Elena"
2. Create scene dengan content containing "Elena walked in."
3. Set summary "Elena meets Marcus."
4. Scan mentions
5. Verify mention with `source: 'both'`

**Assertions:**
- ✅ Mention exists
- ✅ `mention_count` is 2 (1 from content + 1 from summary)
- ✅ `source` field is 'both'

---

#### TC-SM-003: Scene Observer Triggers on Summary Change

**Test Method:** `test_scene_observer_triggers_on_summary_change()`

**Purpose:** Verify SceneObserver auto-scans saat summary berubah

**Steps:**
1. Create entry "Marcus"
2. Create scene dengan no content, no summary
3. Verify no mention exists
4. Update scene summary to "Marcus discovers a secret."
5. Verify mention auto-created (via observer)

**Assertions:**
- ✅ Initial state: no mention
- ✅ After summary update: mention exists
- ✅ Mention count correct

---

#### TC-SM-004: Source is Content When Only in Content

**Test Method:** `test_mention_source_is_content_when_only_in_content()`

**Purpose:** Verify `source` field set correctly ke 'content'

**Steps:**
1. Create scene dengan mention in content only
2. Set summary tanpa mention
3. Scan
4. Verify `source: 'content'`

**Assertions:**
- ✅ `source` field is 'content'
- ✅ Mention count correct

---

#### TC-SM-005: Source is Summary When Only in Summary

**Test Method:** `test_mention_source_is_summary_when_only_in_summary()`

**Purpose:** Verify `source` field set correctly ke 'summary'

**Steps:**
1. Create scene dengan content without mention
2. Set summary dengan mention
3. Scan
4. Verify `source: 'summary'`

**Assertions:**
- ✅ `source` field is 'summary'
- ✅ Mention count correct

---

### Unit Tests: MentionTracker Service

File: `tests/Unit/MentionTrackerTest.php`

#### TC-MT-001: Detects Mentions in Content

**Purpose:** Basic sanity check - mention detection di content works

**Assertions:**
- ✅ Single mention detected
- ✅ Mention count accurate

---

#### TC-MT-002: Counts Multiple Mentions

**Purpose:** Verify counter logic accurate untuk multiple occurrences

**Assertions:**
- ✅ "Elena smiled. Elena waved. Elena left." → count = 3

---

#### TC-MT-003: Detects Mentions by Alias

**Purpose:** Verify alias detection works

**Assertions:**
- ✅ Entry "Elena Blackwood" dengan alias "The Shadow Mage"
- ✅ Text "The Shadow Mage appeared" → mention detected

---

#### TC-MT-004: Ignores Entries with Tracking Disabled

**Purpose:** Respect `is_tracking_enabled = false`

**Assertions:**
- ✅ Entry dengan tracking disabled tidak di-scan
- ✅ No mention created untuk disabled entry

---

#### TC-MT-005: Clears Mentions When Content Removed

**Purpose:** Cleanup logic works

**Assertions:**
- ✅ Initial mention exists
- ✅ After content cleared → mention deleted

---

#### TC-MT-006: Detects Mentions in Summary

**Purpose:** Summary scanning works (Sprint 16 feature)

**Assertions:**
- ✅ Content: null
- ✅ Summary: "Elena arrives at the castle."
- ✅ Mention created dengan `source: 'summary'`

---

#### TC-MT-007: Combines Mentions from Content and Summary

**Purpose:** Aggregation logic correct

**Assertions:**
- ✅ Content: "Elena walked in." (1 mention)
- ✅ Summary: "Elena meets Marcus." (1 mention)
- ✅ Total count: 2
- ✅ Source: 'both'

---

#### TC-MT-008: Source is Content When Only in Content

**Purpose:** Source determination logic correct

**Assertions:**
- ✅ Content has mention, summary doesn't
- ✅ `source` field: 'content'

---

#### TC-MT-009: Source is Summary When Only in Summary

**Purpose:** Source determination logic correct

**Assertions:**
- ✅ Summary has mention, content doesn't
- ✅ `source` field: 'summary'

---

#### TC-MT-010: Clears Mentions When Both Empty

**Purpose:** Cleanup untuk kedua sources

**Assertions:**
- ✅ Both content and summary set to empty/null
- ✅ All mentions cleared

---

#### TC-MT-011: Updates Source When Location Changes

**Purpose:** Re-scan updates source correctly

**Assertions:**
- ✅ Initial scan: mention in content → source: 'content'
- ✅ Update: move mention to summary only
- ✅ Re-scan: source updates to 'summary'

---

#### TC-MT-012: Detection is Case Insensitive

**Purpose:** Regex matching case insensitive

**Assertions:**
- ✅ "ELENA shouted. elena whispered." → 2 mentions

---

## 📋 Manual Testing Checklist

### Category Tag Integration

#### Setup
- [ ] Create a novel
- [ ] Create beberapa tags (e.g., "Protagonist", "Antagonist", "Supporting")
- [ ] Create detail definition (dropdown) "Story Role" dengan options: "Main", "Side", "Background"
- [ ] Create beberapa codex entries dengan tags dan details

#### Test Cases

**MTC-001: Create Category with Tag Link**
- [ ] Navigate ke Codex page
- [ ] Click entry → Categories section → "Manage Categories"
- [ ] Click "Create New Category"
- [ ] Name: "Main Characters"
- [ ] Select tag: "Protagonist" dari dropdown "Auto-link by Tag"
- [ ] Click "Create Category"
- [ ] ✅ Category created successfully
- [ ] ✅ Category shows "Auto" badge
- [ ] ✅ Entry count matches entries dengan "Protagonist" tag

**MTC-002: Edit Existing Category to Add Link**
- [ ] Open category list
- [ ] Click edit icon next to a category
- [ ] Select a tag dari "Auto-link by Tag" dropdown
- [ ] ✅ Preview shows entries yang akan auto-link
- [ ] Click "Save Changes"
- [ ] ✅ Category updated, auto-linked entries shown

**MTC-003: Link Category to Detail Value**
- [ ] Create category "Main Cast"
- [ ] Select detail definition: "Story Role"
- [ ] Select value: "Main"
- [ ] ✅ Preview shows entries dengan detail "Story Role = Main"
- [ ] Save
- [ ] ✅ Entries auto-appear in category

**MTC-004: Remove Tag Link**
- [ ] Edit category dengan existing tag link
- [ ] Set "Auto-link by Tag" to "None"
- [ ] Save
- [ ] ✅ Auto-linked entries no longer appear
- [ ] ✅ Manually assigned entries still present

**MTC-005: Category with Both Manual and Auto Entries**
- [ ] Create category dengan tag link
- [ ] ✅ Auto-linked entries shown
- [ ] Manually assign additional entry (tanpa the tag)
- [ ] ✅ Total count = manual + auto
- [ ] ✅ All entries visible in listing

---

### Summary Mention Tracking

#### Setup
- [ ] Create entry "Elena Blackwood" dengan alias "Elena"
- [ ] Enable tracking untuk entry
- [ ] Open editor untuk create/edit scene

#### Test Cases

**MTC-006: Mention in Content Only**
- [ ] Type in editor: "Elena walked into the room."
- [ ] Leave summary empty
- [ ] Save scene
- [ ] Navigate ke Codex → Elena's entry
- [ ] ✅ Mention count: 1
- [ ] ✅ Scene appears in Mentions list

**MTC-007: Mention in Summary Only**
- [ ] Clear scene content
- [ ] Add summary: "Elena arrives at the castle and meets the king."
- [ ] Save
- [ ] Check Codex entry
- [ ] ✅ Mention count: 2 (2 "Elena" in summary)
- [ ] ✅ Scene appears in Mentions list

**MTC-008: Mention in Both Content and Summary**
- [ ] Content: "Elena smiled."
- [ ] Summary: "Elena meets Marcus."
- [ ] Save
- [ ] Check Codex entry
- [ ] ✅ Mention count: 2 (1 from each)
- [ ] ✅ Scene appears in Mentions list

**MTC-009: Live Update - Write in One Tab, See in Another**
- [ ] Open Editor di tab 1
- [ ] Open Codex Elena's entry di tab 2
- [ ] In tab 1: Type "Elena walked. Elena smiled."
- [ ] Auto-save triggers
- [ ] Wait 5 seconds (polling interval)
- [ ] ✅ Tab 2 shows toast "Mentions Updated"
- [ ] ✅ Mention count updates without refresh
- [ ] ✅ Live indicator pulses

**MTC-010: Clear Summary Removes Mentions**
- [ ] Scene dengan mention in summary
- [ ] Content empty
- [ ] Verify mention exists in Codex
- [ ] Clear summary
- [ ] Save
- [ ] ✅ Mention removed dari Codex
- [ ] ✅ Scene no longer in Mentions list

**MTC-011: Tracking Toggle Disables Summary Scanning**
- [ ] Create scene dengan "Elena" in summary
- [ ] Navigate to Elena's Codex entry
- [ ] Toggle "Enable Tracking" OFF
- [ ] Wait 5 seconds (polling)
- [ ] ✅ Mention disappears
- [ ] Toggle ON
- [ ] ✅ Mention reappears

---

### UI/UX Testing

**MTC-012: Category Manager UI**
- [ ] Open Category Manager
- [ ] ✅ Tag dropdown shows available tags
- [ ] ✅ Detail definition dropdown shows only dropdown types
- [ ] ✅ Detail value dropdown populates setelah definition selected
- [ ] ✅ Preview updates saat selection changes
- [ ] ✅ "Auto" badge visible untuk linked categories
- [ ] ✅ Entry count shows total (manual + auto)

**MTC-013: Live Polling Feedback**
- [ ] Open Codex entry dengan tracking enabled
- [ ] ✅ "Live" badge dengan pulsing dot visible
- [ ] Edit scene di another tab untuk add mention
- [ ] ✅ Toast appears "Mentions Updated"
- [ ] ✅ Mention count updates
- [ ] ✅ No page refresh needed

**MTC-014: Mobile Responsiveness**
- [ ] Test pada mobile device / responsive mode
- [ ] Category Manager modal
  - [ ] ✅ Dropdowns accessible
  - [ ] ✅ Preview scrollable
  - [ ] ✅ Buttons tidak overlap
- [ ] Codex Show page
  - [ ] ✅ Live badge visible
  - [ ] ✅ Toast appears correctly
  - [ ] ✅ Mentions list readable

---

### Edge Cases

**MTC-015: Category Link to Non-Existent Tag**
- [ ] Manually set `linked_tag_id` ke deleted tag (via tinker)
- [ ] Open category list API
- [ ] ✅ No error thrown
- [ ] ✅ `linked_tag` returns null
- [ ] ✅ `has_auto_linking` is false

**MTC-016: Circular Auto-Linking**
- [ ] Create tag "Test"
- [ ] Create category linked to tag "Test"
- [ ] Create entry dengan tag "Test"
- [ ] ✅ Entry appears in category
- [ ] Remove tag dari entry
- [ ] ✅ Entry disappears dari category
- [ ] Re-add tag
- [ ] ✅ Entry reappears

**MTC-017: Large Summary (> 1000 words)**
- [ ] Create scene dengan content: empty
- [ ] Summary: Large text dengan 50+ mentions
- [ ] Save
- [ ] ✅ Scene saves without timeout
- [ ] ✅ Mentions detected correctly
- [ ] ✅ Performance acceptable (< 2 seconds)

**MTC-018: Special Characters in Mentions**
- [ ] Entry name: "Dr. O'Brien"
- [ ] Scene summary: "Dr. O'Brien arrived."
- [ ] ✅ Mention detected correctly
- [ ] ✅ Apostrophe handled
- [ ] ✅ Period in name handled

**MTC-019: Mention at Summary Start/End**
- [ ] Summary: "Elena met Marcus. They talked about Elena."
- [ ] ✅ Both mentions detected (start and end boundaries)

**MTC-020: Empty Content but Non-Empty Summary**
- [ ] Content: `null` or empty TipTap structure
- [ ] Summary: "Test summary with Elena."
- [ ] ✅ Mention detected
- [ ] ✅ No error thrown
- [ ] ✅ Source: 'summary'

---

## 🐛 Known Issues / Limitations

### Current Limitations

1. **Chat Mention Tracking**
   - Status: Infrastructure ready, awaiting chat feature implementation
   - Impact: Cannot test chat mention scanning until chat_messages table exists

2. **Category Auto-Linking Performance**
   - Performance: Calculated on-demand (not cached)
   - Impact: May be slow untuk novels dengan > 1000 entries
   - Mitigation: Consider caching untuk large novels (future optimization)

3. **Summary Mention Positions**
   - Limitation: Positions stored dari content only (summary positions not stored)
   - Impact: Cannot highlight mentions in summary (future feature)
   - Reason: TipTap editor tidak render summary, so highlight not needed yet

### Resolved Issues

- ✅ **Issue:** Observer tidak trigger pada summary-only changes
  - **Fix:** Added `wasChanged('summary')` check di SceneObserver
  - **Test:** TC-SM-003 verifies fix

- ✅ **Issue:** Source field tidak update saat mention moves
  - **Fix:** Re-scan correctly determines source setiap kali
  - **Test:** TC-MT-011 verifies fix

---

## 🔧 Running Tests

### Run All Tests

```bash
cd C:\Project\Website\writing-app
php artisan test
```

### Run Codex Tests Only

```bash
php artisan test --filter=CodexTest
```

### Run MentionTracker Unit Tests

```bash
php artisan test --filter=MentionTrackerTest
```

### Run Sprint 16 Specific Tests

```bash
# Category integration tests
php artisan test --filter="test_can_create_category_with_linked_tag|test_category_auto_links"

# Summary tracking tests
php artisan test --filter="test_mentions.*summary|test_mention_source"
```

### Run with Coverage (if xdebug enabled)

```bash
php artisan test --coverage --min=80
```

---

## 📊 Test Metrics

### Coverage by Component

| Component | Coverage | Status |
|-----------|----------|--------|
| CodexCategory Model | ~90% | ✅ Excellent |
| CodexMention Model | ~85% | ✅ Good |
| MentionTracker Service | ~95% | ✅ Excellent |
| ChatMentionTracker | Not testable yet | ⚠️ Awaiting chat feature |
| SceneObserver | ~80% | ✅ Good |
| CodexCategoryController | ~85% | ✅ Good |
| CategoryManager.vue | Manual only | ⚠️ No e2e setup |

### Test Execution Performance

| Suite | Tests | Duration | Status |
|-------|-------|----------|--------|
| Feature Tests (Codex) | 88 | ~1.8s | ✅ Fast |
| Unit Tests (MentionTracker) | 12 | ~0.6s | ✅ Very Fast |
| **Total** | **100** | **~2.4s** | ✅ Excellent |

---

## 🔗 Related Documentation

- **Sprint Documentation:** [Sprint 16 - Codex Enhancements](../10-sprints/sprint-16-codex-enhancements.md)
- **API Reference:** [Codex API](../04-api-reference/codex.md)
- **Previous Testing:** [Codex Sprint 15 Testing](./codex-sprint15-testing.md)

---

*Last Updated: 2026-01-01*
