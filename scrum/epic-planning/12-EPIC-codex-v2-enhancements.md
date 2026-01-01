# 📚 EPIC 12: Codex v2 - Enhancements & Novelcrafter Parity

**Epic ID:** EPIC-12  
**Priority:** 🔴 Critical  
**Total Story Points:** ~110  
**Est. Duration:** 3-4 Sprints  
**Dependencies:** EPIC-02 (Codex System - Completed)  
**Status:** 🆕 New

---

## 📋 Epic Description

This epic addresses the gaps between our current Codex implementation and the comprehensive feature set offered by [Novelcrafter](https://www.novelcrafter.com/help/docs/codex/the-codex). Based on a thorough analysis, this epic focuses on:

1. **Auto-updating mentions** (eliminating manual rescan)
2. **Research/Notes section** (private notes NOT sent to AI)
3. **Tags system** (separate from Categories)
4. **Enhanced Details** with types and AI visibility controls
5. **Relations → AI Context integration**
6. **Improved UX** for bulk codex management
7. **Editor integration** for progressions

---

## 🎯 Epic Goals

1. ✅ Eliminate manual "Rescan" requirement for mentions
2. ✅ Provide private research notes separate from AI-visible description
3. ✅ Enable quick organization with Tags (separate from Categories)
4. ✅ Support different detail types (text, dropdown, line, codex reference)
5. ✅ Pull related codex entries into AI context automatically
6. ✅ Add progression from editor via slash command
7. ✅ Tracking toggle to disable mention tracking per entry
8. ✅ Improve UX for managing large numbers of codex entries

---

## 📎 Novelcrafter Reference Documentation

| Feature | Novelcrafter Docs | Current Status |
|---------|------------------|----------------|
| Codex Overview | [The Codex](https://www.novelcrafter.com/help/docs/codex/the-codex) | ✅ Implemented |
| Entry Anatomy | [Anatomy of a Codex Entry](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry) | ⚠️ Partial |
| Entry Types | [Codex Types](https://www.novelcrafter.com/help/docs/codex/codex-types) | ✅ Implemented |
| Aliases & Mentions | [Aliases & Mentions](https://www.novelcrafter.com/help/docs/codex/aliases) | ⚠️ Manual rescan |
| Description | [Description Guidelines](https://www.novelcrafter.com/help/docs/codex/guidelines-for-descriptions) | ✅ Implemented |
| Categories | [Codex Categories](https://www.novelcrafter.com/help/docs/codex/codex-categories) | ⚠️ Missing tag-based |
| Details | [Codex Details](https://www.novelcrafter.com/help/docs/codex/codex-details) | ⚠️ Missing types |
| Details Quick Create | [Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create) | ⚠️ Missing presets |
| Adding Details | [Adding Details](https://www.novelcrafter.com/help/docs/codex/character-codex-details) | ✅ Implemented |
| Relations | [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations) | ⚠️ Missing AI pull |
| Progressions | [Progressions/Additions](https://www.novelcrafter.com/help/docs/codex/progressions-additions) | ⚠️ Missing editor cmd |
| Detail Progressions | [Progressions on Details](https://www.novelcrafter.com/help/docs/codex/progressions-codex-details) | ✅ Implemented |
| Series Codex | [Series Codex](https://www.novelcrafter.com/help/docs/codex/series-codex) | ✅ Implemented |

---

## 📑 Feature Groups

### FG-12.1: Auto-Mentions System (Critical)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.1.1 | Auto-scan Mentions on Scene Save | 🔴 Critical | 8 |
| F-12.1.2 | Background Job Queue for Mention Scanning | 🔴 Critical | 5 |
| F-12.1.3 | Tracking Toggle per Entry | 🔴 Critical | 3 |
| F-12.1.4 | Mention Tracking Across Summaries | 🟡 High | 5 |
| F-12.1.5 | Mention Tracking in Chat | 🟢 Medium | 5 |

### FG-12.2: Research/Notes Tab (High)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.2.1 | Research Notes Field (Not sent to AI) | 🔴 Critical | 5 |
| F-12.2.2 | External Links Storage | 🟡 High | 3 |
| F-12.2.3 | Notes Word Count | 🟢 Medium | 1 |

### FG-12.3: Tags System (High)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.3.1 | Tags Separate from Categories | 🔴 Critical | 5 |
| F-12.3.2 | Tag Management (Create/Edit/Delete) | 🔴 Critical | 3 |
| F-12.3.3 | Filter by Tags | 🟡 High | 3 |
| F-12.3.4 | Predefined Tags per Type | 🟢 Medium | 3 |
| F-12.3.5 | Tag Colors | 🟢 Medium | 2 |

### FG-12.4: Enhanced Details System (High)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.4.1 | Detail Types (Text, Line, Dropdown) | 🔴 Critical | 8 |
| F-12.4.2 | Codex Reference Detail Type | 🟡 High | 5 |
| F-12.4.3 | AI Visibility per Detail | 🔴 Critical | 5 |
| F-12.4.4 | Detail Templates/Presets | 🟡 High | 5 |
| F-12.4.5 | Show Detail in Sidebar | 🟢 Medium | 3 |

### FG-12.5: Relations → AI Context (Critical)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.5.1 | Auto-pull Related Entries to AI Context | 🔴 Critical | 8 |
| F-12.5.2 | Cascade Level Control | 🟡 High | 3 |
| F-12.5.3 | Swap Relation Direction UI | 🟢 Medium | 2 |

### FG-12.6: Editor Integration (High)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.6.1 | Add Progression via Slash Command | 🔴 Critical | 8 |
| F-12.6.2 | Inline Codex Preview on Hover | 🟡 High | 5 |
| F-12.6.3 | Quick Create Codex from Selection | 🟡 High | 5 |

### FG-12.7: UX Improvements (Medium)

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-12.7.1 | Bulk Entry Creation Modal | 🟡 High | 5 |
| F-12.7.2 | Duplicate Entry | 🟡 High | 2 |
| F-12.7.3 | Type Change After Creation | 🟢 Medium | 2 |
| F-12.7.4 | Thumbnail Upload Improvements | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-12.1: Auto-scan Mentions on Scene Save
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer with hundreds of codex entries,  
**I want** mentions to be tracked automatically when I save a scene,  
**So that** I don't have to manually click "Rescan" for every entry.

#### Acceptance Criteria:
- [ ] Mentions scan triggers automatically when scene content is saved
- [ ] Scan runs in background (non-blocking UI)
- [ ] Uses queue/job system for performance
- [ ] Only scans the modified scene, not entire novel
- [ ] Updates mention counts in real-time after scan completes
- [ ] Works for both name and all aliases

#### Technical Notes:
```php
// Scene model observer
class SceneObserver
{
    public function updated(Scene $scene): void
    {
        if ($scene->isDirty('content')) {
            ScanSceneMentions::dispatch($scene);
        }
    }
}
```

**Reference:** [Aliases & Mentions](https://www.novelcrafter.com/help/docs/codex/aliases)

---

### US-12.2: Tracking Toggle per Entry
**Priority:** 🔴 Critical | **Points:** 3

**As a** writer,  
**I want** to disable mention tracking for specific entries,  
**So that** common words like "Red" don't highlight everywhere.

#### Acceptance Criteria:
- [ ] Toggle "Track this entry by name/alias" in entry settings
- [ ] When disabled, entry won't appear in mention lists
- [ ] When disabled, won't be highlighted in editor
- [ ] Still available for manual AI context selection
- [ ] Visual indicator when tracking is disabled

#### Database Migration:
```php
Schema::table('codex_entries', function (Blueprint $table) {
    $table->boolean('is_tracking_enabled')->default(true);
});
```

**Reference:** [Anatomy - Tracking Tab](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)

---

### US-12.3: Research Notes Tab
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want** a separate section for research notes that won't be sent to AI,  
**So that** I can store development notes, inspiration, and spoilers privately.

#### Acceptance Criteria:
- [ ] New "Research" tab in entry detail view
- [ ] "Notes" rich text field (NOT sent to AI)
- [ ] Clear label indicating "Not sent to AI"
- [ ] "External" section for storing URLs/links
- [ ] Notes word count displayed
- [ ] Auto-save on change

#### Database Migration:
```php
Schema::table('codex_entries', function (Blueprint $table) {
    $table->text('research_notes')->nullable();
});

Schema::create('codex_external_links', function (Blueprint $table) {
    $table->id();
    $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('url');
    $table->timestamps();
});
```

**Reference:** [Anatomy - Research Tab](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)

---

### US-12.4: Tags System (Separate from Categories)
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want** quick tags for my entries (like "Protagonist", "Antagonist"),  
**So that** I can filter and organize without creating full categories.

#### Acceptance Criteria:
- [ ] Tags are separate from Categories
- [ ] Multiple tags per entry
- [ ] Tags are NOT sent to AI (organizational only)
- [ ] Predefined tags per type (e.g., Character: Protagonist, Antagonist, Supporting)
- [ ] Custom tags can be created
- [ ] Filter entries by tag
- [ ] Tags displayed below entry name in list/detail views

#### Database Schema:
```php
Schema::create('codex_tags', function (Blueprint $table) {
    $table->id();
    $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('color', 7)->nullable();
    $table->string('type')->nullable(); // Limit to specific entry types
    $table->boolean('is_predefined')->default(false);
    $table->timestamps();
});

Schema::create('codex_entry_tags', function (Blueprint $table) {
    $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
    $table->foreignId('codex_tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['codex_entry_id', 'codex_tag_id']);
});
```

**Reference:** [Anatomy - Tags/Labels](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)

---

### US-12.5: Enhanced Detail Types
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want** different types of detail fields (text, dropdown, reference),  
**So that** I can have structured data like "Story Role: Protagonist".

#### Acceptance Criteria:
- [ ] Detail types: Text (multiline), Line (single line), Dropdown
- [ ] Dropdown options configurable per detail definition
- [ ] Codex Reference type (links to another entry)
- [ ] Type displayed visually in detail list
- [ ] Validation based on type
- [ ] Migration for existing text details

#### Detail Type Definitions:
```php
Schema::create('codex_detail_definitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('novel_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignId('series_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->enum('type', ['text', 'line', 'dropdown', 'codex_reference']);
    $table->json('options')->nullable(); // For dropdown options
    $table->json('entry_types')->nullable(); // Which codex types can use this
    $table->boolean('show_in_sidebar')->default(false);
    $table->enum('ai_visibility', ['always', 'never', 'nsfw_only'])->default('always');
    $table->integer('sort_order')->default(0);
    $table->boolean('is_preset')->default(false); // Quick create presets
    $table->timestamps();
});

Schema::table('codex_details', function (Blueprint $table) {
    $table->foreignId('definition_id')->nullable()->constrained('codex_detail_definitions')->nullOnDelete();
});
```

**Reference:** [Codex Details](https://www.novelcrafter.com/help/docs/codex/codex-details)

---

### US-12.6: AI Visibility per Detail
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want** to control which details are sent to AI,  
**So that** sensitive info (NSFW, spoilers) stays private unless needed.

#### Acceptance Criteria:
- [ ] Per-detail AI visibility: Always, Never, NSFW Only
- [ ] Visual indicator of AI visibility status
- [ ] Bulk update AI visibility
- [ ] Filter to show "AI visible" details only
- [ ] Respect visibility in AI context building

#### AI Visibility Modes:
| Mode | Description |
|------|-------------|
| `always` | Always included in AI context |
| `never` | Never included (private notes in details) |
| `nsfw_only` | Only included when NSFW prompt is used |

**Reference:** [Codex Details - AI Tab](https://www.novelcrafter.com/help/docs/codex/codex-details)

---

### US-12.7: Detail Presets (Quick Create)
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want** pre-built detail templates like "Story Role" or "Pronouns",  
**So that** I can quickly add common attributes.

#### Acceptance Criteria:
- [ ] Built-in presets: Story Role (dropdown), Backstory (text), Occupation (line), Pronouns (dropdown), Physical Appearance (text), Voice Sheet (text)
- [ ] "Add from preset" button in detail section
- [ ] Presets show suggested options
- [ ] Can customize after adding
- [ ] Novel/Series-level custom presets

#### Built-in Presets:
```php
$presets = [
    [
        'name' => 'Story Role',
        'type' => 'dropdown',
        'options' => ['Protagonist', 'Antagonist', 'Supporting', 'Minor'],
        'entry_types' => ['character'],
        'show_in_sidebar' => true,
    ],
    [
        'name' => 'Pronouns',
        'type' => 'dropdown',
        'options' => ['he/him', 'she/her', 'they/them', 'other'],
        'entry_types' => ['character'],
    ],
    [
        'name' => 'Backstory',
        'type' => 'text',
        'entry_types' => ['character'],
    ],
    [
        'name' => 'Occupation',
        'type' => 'line',
        'entry_types' => ['character'],
    ],
    [
        'name' => 'Physical Appearance',
        'type' => 'text',
        'entry_types' => ['character'],
        'ai_visibility' => 'never', // Often over-mentioned by AI
    ],
    [
        'name' => 'Voice Sheet',
        'type' => 'text',
        'entry_types' => ['character'],
    ],
    [
        'name' => 'Fighting Style',
        'type' => 'text',
        'entry_types' => ['character'],
        'ai_visibility' => 'nsfw_only',
    ],
];
```

**Reference:** [Codex Details Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create)

---

### US-12.8: Relations Pull into AI Context
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want** related entries to automatically be included in AI context,  
**So that** when "The Council of Five" is mentioned, all members' info is available.

#### Acceptance Criteria:
- [ ] When entry is detected in text, related entries are also pulled
- [ ] Configurable cascade depth (1 level, 2 levels, all)
- [ ] Only pulls if related entry has AI context mode != "never"
- [ ] Visual indicator in context preview
- [ ] Performance optimized (cached relations)
- [ ] Prevent circular reference loops

#### Implementation:
```php
class CodexContextBuilder
{
    public function buildContext(array $detectedEntryIds, int $cascadeDepth = 1): array
    {
        $entries = CodexEntry::whereIn('id', $detectedEntryIds)->get();
        $relatedIds = [];
        
        foreach ($entries as $entry) {
            $relatedIds = array_merge(
                $relatedIds,
                $this->getRelatedEntryIds($entry, $cascadeDepth)
            );
        }
        
        // Include related entries in context
        $allEntries = CodexEntry::whereIn('id', array_unique([...$detectedEntryIds, ...$relatedIds]))
            ->where('ai_context_mode', '!=', 'never')
            ->get();
            
        return $this->formatForAI($allEntries);
    }
}
```

**Reference:** [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations)

---

### US-12.9: Add Progression from Editor
**Priority:** 🔴 Critical | **Points:** 8

**As a** writer,  
**I want** to add codex progressions directly from the editor via slash command,  
**So that** I can track character changes without leaving my writing flow.

#### Acceptance Criteria:
- [ ] `/codex` or `/progression` slash command in editor
- [ ] Opens inline progression form
- [ ] Select codex entry from dropdown
- [ ] Enter progression note
- [ ] Optionally link to current scene (auto-filled)
- [ ] Choose mode: Addition or Replacement
- [ ] Optionally link to specific detail
- [ ] Progression appears in codex entry timeline

#### Editor Integration:
```typescript
// TipTap extension
const CodexProgressionCommand = Extension.create({
    name: 'codexProgression',
    
    addCommands() {
        return {
            openProgressionModal: () => ({ commands }) => {
                // Open modal with current scene pre-selected
                emit('open-progression-modal', {
                    sceneId: currentSceneId,
                });
                return true;
            },
        };
    },
    
    addKeyboardShortcuts() {
        return {
            'Mod-Shift-P': () => this.editor.commands.openProgressionModal(),
        };
    },
});
```

**Reference:** [Progressions/Additions](https://www.novelcrafter.com/help/docs/codex/progressions-additions)

---

### US-12.10: Inline Codex Preview on Hover
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want** to see a quick preview of codex entries when hovering over mentions,  
**So that** I can reference character details without leaving the editor.

#### Acceptance Criteria:
- [ ] Hover over highlighted mention shows tooltip
- [ ] Tooltip shows: Name, Type, Thumbnail, Brief description
- [ ] Key details displayed (configurable)
- [ ] Click to open full entry
- [ ] Tooltip dismisses on mouse leave
- [ ] Performance optimized (lazy load)

#### Component:
```vue
<MentionTooltip
    :entry-id="hoveredEntryId"
    :position="tooltipPosition"
    @open-entry="navigateToEntry"
/>
```

**Reference:** [Anatomy - Mentions Tracker](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)

---

### US-12.11: Quick Create Codex from Selection
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want** to select text and quickly create a codex entry,  
**So that** I can build my world database while writing.

#### Acceptance Criteria:
- [ ] Select text in editor → "Create Codex Entry" option
- [ ] Pre-fills entry name with selected text
- [ ] Quick modal with: Name, Type, Brief description
- [ ] Option to add selected text as alias if different
- [ ] Returns to editor after creation
- [ ] New entry immediately available for highlighting

**Reference:** [Codex Details Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create)

---

### US-12.12: Bulk Entry Creation
**Priority:** 🟡 High | **Points:** 5

**As a** writer setting up a new novel,  
**I want** to create multiple codex entries at once,  
**So that** I can quickly populate my world database.

#### Acceptance Criteria:
- [ ] "Bulk Create" button in codex index
- [ ] Modal with multi-line input (one entry per line)
- [ ] Format: `Name | Type | Brief Description`
- [ ] Preview before creation
- [ ] Error handling for invalid lines
- [ ] Success summary with links to created entries

#### Example Input:
```
Alice | character | The protagonist, a young witch
Bob | character | Alice's mentor
The Dark Forest | location | A mysterious forest
Moonstone Staff | item | Alice's magical staff
```

---

### US-12.13: Categories with Tag Integration
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want** entries to be auto-assigned to categories based on tags,  
**So that** my organization stays consistent.

#### Acceptance Criteria:
- [ ] Category can be linked to a tag
- [ ] Entries with that tag auto-appear in category
- [ ] Category can also link to specific detail values
- [ ] Visual grouping in sidebar
- [ ] Can collapse category groups

**Reference:** [Codex Categories](https://www.novelcrafter.com/help/docs/codex/codex-categories)

---

### US-12.14: Swap Relation Direction
**Priority:** 🟢 Medium | **Points:** 2

**As a** writer,  
**I want** to quickly swap the direction of a relation,  
**So that** I can fix mistakes without deleting and recreating.

#### Acceptance Criteria:
- [ ] Swap button on relation item
- [ ] Swaps source ↔ target
- [ ] Updates both entries' displays
- [ ] Confirmation before swap

**Reference:** [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations)

---

### US-12.15: Type Change After Creation
**Priority:** 🟢 Medium | **Points:** 2

**As a** writer,  
**I want** to change an entry's type after creation,  
**So that** I can reclassify entries as my story develops.

#### Acceptance Criteria:
- [ ] Type dropdown editable in entry edit form
- [ ] Warning about type-specific details that may become irrelevant
- [ ] Update search indexes after type change
- [ ] History log of type change

**Reference:** [Codex Types](https://www.novelcrafter.com/help/docs/codex/codex-types)

---

## 🗄️ Database Migrations Summary

### New Tables:
1. `codex_tags` - Tag definitions
2. `codex_entry_tags` - Entry-Tag pivot
3. `codex_external_links` - Research links
4. `codex_detail_definitions` - Detail type definitions

### Modified Tables:
1. `codex_entries`:
   - Add `research_notes` (text, nullable)
   - Add `is_tracking_enabled` (boolean, default true)
   
2. `codex_details`:
   - Add `definition_id` (foreign key, nullable)
   - Add `ai_visibility` (enum: always, never, nsfw_only)

---

## 🏗️ Technical Architecture

### New Services:
```
app/Services/Codex/
├── AutoMentionScanner.php    # Background mention scanning
├── CodexContextBuilder.php   # AI context with relations
├── DetailDefinitionService.php
└── TagService.php
```

### New Jobs:
```
app/Jobs/
└── ScanSceneMentions.php     # Queue job for mention scanning
```

### New Components:
```
resources/js/Components/Codex/
├── TagManager.vue
├── ResearchTab.vue
├── DetailTypeSelector.vue
├── DetailPresetPicker.vue
├── BulkCreateModal.vue
└── ProgressionEditorModal.vue
```

### Editor Extensions:
```
resources/js/Extensions/
├── CodexProgression.ts       # /progression slash command
└── QuickCreateCodex.ts       # Selection → Create entry
```

---

## 🔀 New API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/codex/{entry}/tags` | Add tag to entry |
| DELETE | `/api/codex/{entry}/tags/{tag}` | Remove tag |
| GET | `/api/novels/{novel}/codex/tags` | List all tags |
| POST | `/api/novels/{novel}/codex/tags` | Create tag |
| PATCH | `/api/codex/tags/{tag}` | Update tag |
| DELETE | `/api/codex/tags/{tag}` | Delete tag |
| GET | `/api/novels/{novel}/codex/detail-definitions` | List detail definitions |
| POST | `/api/novels/{novel}/codex/detail-definitions` | Create definition |
| PATCH | `/api/codex/detail-definitions/{def}` | Update definition |
| POST | `/api/codex/{entry}/external-links` | Add external link |
| DELETE | `/api/codex/external-links/{link}` | Remove link |
| POST | `/api/novels/{novel}/codex/bulk-create` | Bulk create entries |

---

## 📊 Sprint Distribution

### Sprint 13: Auto-Mentions & Research (~30 points)
- US-12.1: Auto-scan Mentions on Scene Save (8)
- US-12.2: Tracking Toggle per Entry (3)
- US-12.3: Research Notes Tab (5)
- US-12.8: Relations Pull into AI Context (8)
- F-12.1.2: Background Job Queue (5)
- F-12.2.2: External Links (3) - if time permits

### Sprint 14: Tags & Enhanced Details (~35 points)
- US-12.4: Tags System (5)
- US-12.5: Enhanced Detail Types (8)
- US-12.6: AI Visibility per Detail (5)
- US-12.7: Detail Presets (5)
- F-12.3.2: Tag Management (3)
- F-12.3.3: Filter by Tags (3)
- F-12.4.5: Show Detail in Sidebar (3)
- F-12.3.4: Predefined Tags (3)

### Sprint 15: Editor Integration & UX (~30 points)
- US-12.9: Add Progression from Editor (8)
- US-12.10: Inline Codex Preview on Hover (5)
- US-12.11: Quick Create from Selection (5)
- US-12.12: Bulk Entry Creation (5)
- US-12.14: Swap Relation Direction (2)
- US-12.15: Type Change After Creation (2)
- F-12.7.2: Duplicate Entry (2)

### Sprint 16: Polish & Integration (~15 points)
- US-12.13: Categories with Tag Integration (5)
- F-12.1.4: Mention Tracking in Summaries (5)
- F-12.1.5: Mention Tracking in Chat (5)
- Bug fixes and polish

---

## ✅ Definition of Done

- [ ] All auto-mention scanning working without manual rescan
- [ ] Research tab fully functional with AI separation
- [ ] Tags system complete and separate from Categories
- [ ] All detail types implemented with AI visibility
- [ ] Relations automatically pull into AI context
- [ ] Slash command for progressions working in editor
- [ ] Tracking toggle functional per entry
- [ ] Bulk create modal working
- [ ] All new API endpoints documented
- [ ] Unit tests (80%+ coverage for new code)
- [ ] Feature tests for all new endpoints
- [ ] Mobile-responsive new components
- [ ] Performance benchmarks met (mention scan < 500ms)

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Auto-scan performance with large novels | High | Medium | Use queue jobs, scan only changed scenes |
| Detail type migration complexity | Medium | Medium | Gradual migration, backward compatibility |
| AI context token overflow with relations | Medium | Low | Cascade depth limits, smart pruning |
| Editor extension conflicts | Low | Medium | Thorough TipTap testing |

---

## 📎 References

- [The Codex](https://www.novelcrafter.com/help/docs/codex/the-codex)
- [Anatomy of a Codex Entry](https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry)
- [Codex Types](https://www.novelcrafter.com/help/docs/codex/codex-types)
- [Aliases & Mentions](https://www.novelcrafter.com/help/docs/codex/aliases)
- [Description Guidelines](https://www.novelcrafter.com/help/docs/codex/guidelines-for-descriptions)
- [Series Codex](https://www.novelcrafter.com/help/docs/codex/series-codex)
- [Codex Categories](https://www.novelcrafter.com/help/docs/codex/codex-categories)
- [Codex Details](https://www.novelcrafter.com/help/docs/codex/codex-details)
- [Codex Details Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create)
- [Adding Codex Details](https://www.novelcrafter.com/help/docs/codex/character-codex-details)
- [Codex Relations](https://www.novelcrafter.com/help/docs/codex/codex-relations)
- [Progressions/Additions](https://www.novelcrafter.com/help/docs/codex/progressions-additions)
- [Progressions on Codex Details](https://www.novelcrafter.com/help/docs/codex/progressions-codex-details)

---

## 📝 Notes

### Why This Epic Matters

The current Codex implementation has the foundation but lacks the polish and automation that makes Novelcrafter's Codex so powerful. The biggest pain point is the **manual rescan requirement** - with hundreds or thousands of entries, this becomes unworkable. 

This epic prioritizes:
1. **Automation** - Mentions track themselves
2. **Privacy** - Clear separation of AI-visible vs private notes
3. **Organization** - Tags + Categories for flexible grouping
4. **AI Intelligence** - Relations pull context automatically
5. **Writing Flow** - Add progressions without leaving editor

### Backward Compatibility

All changes should maintain backward compatibility with existing data. The detail type system should gracefully handle existing text-only details.

---

*This epic document was created based on a comprehensive analysis comparing the current implementation with [Novelcrafter's Codex documentation](https://www.novelcrafter.com/help/docs/codex/the-codex).*
