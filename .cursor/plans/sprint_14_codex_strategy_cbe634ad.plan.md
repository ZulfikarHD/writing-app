---
name: Sprint 14 Codex Strategy
overview: Sprint 14 implements Tags System and Enhanced Details with full auto-save behavior. All changes sync immediately without manual intervention, following the same "auto-magic" pattern already established in Sprint 13 for mentions.
todos:
  - id: migration
    content: Create Sprint 14 database migration (codex_tags, codex_entry_tags, codex_detail_definitions, update codex_details)
    status: completed
  - id: models
    content: Create CodexTag and CodexDetailDefinition models, update CodexEntry and CodexDetail relationships
    status: completed
  - id: tag-controller
    content: Create CodexTagController with CRUD and entry assignment endpoints
    status: completed
  - id: definition-controller
    content: Create CodexDetailDefinitionController for detail type templates
    status: completed
  - id: update-controllers
    content: Update CodexController and CodexDetailController to include tags/types in responses
    status: completed
  - id: routes
    content: Register all new API routes in routes/web.php
    status: completed
  - id: seeder
    content: Create CodexPresetSeeder for built-in detail presets (Story Role, Pronouns, etc.)
    status: completed
  - id: tag-manager-vue
    content: Create TagManager.vue component with auto-save tag assignment
    status: completed
  - id: ai-visibility-vue
    content: Create AIVisibilityToggle.vue component for per-detail AI control
    status: completed
  - id: detail-manager-update
    content: Update DetailManager.vue to support types, definitions, and AI visibility
    status: completed
  - id: preset-picker-vue
    content: Create DetailPresetPicker.vue for quick detail creation from presets
    status: completed
  - id: index-filter
    content: Add tag filter dropdown to Codex/Index.vue
    status: completed
  - id: show-integration
    content: Integrate TagManager into Codex/Show.vue sidebar
    status: completed
  - id: context-builder
    content: Update CodexContextBuilder to respect ai_visibility when building AI context
    status: completed
  - id: tests
    content: Write feature tests for new tag and detail definition endpoints
    status: completed
---

# Sprint 14: Tags and Enhanced Details Development Strategy

## Phase 1: Feature Understanding

### What Data is Being Created/Managed

| Feature | Data Created | Owner (Creates) | Consumer (Views/Uses) |

|---------|--------------|-----------------|----------------------|

| Tags System | `codex_tags`, `codex_entry_tags` | Codex Index/Entry Page | Codex Index (filter), Entry Show |

| Enhanced Details | `codex_detail_definitions`, updated `codex_details` | Settings Modal / Entry Page | Entry Show, AI Context Builder |

| AI Visibility | `ai_visibility` per detail | Detail Manager | AI Context Builder |

| Detail Presets | `is_preset` definitions | Settings Modal | "Add from preset" picker |

### Data Flow (Auto-Everything Pattern)

```
User Action                    Backend                       UI Update
-----------                    -------                       ---------
Add tag to entry        -->    POST /api/codex/{id}/tags   --> Immediate UI update
Create new tag          -->    POST /api/novels/{id}/tags  --> Tag appears in dropdown
Change detail type      -->    PATCH /api/codex/details/{id} --> Field re-renders
Toggle AI visibility    -->    PATCH (inline)              --> Icon updates instantly
```

---

## Phase 2: Cross-Frontend Impact Mapping

### Codex Index Page (`Codex/Index.vue`)

| Feature | Impact | Priority |

|---------|--------|----------|

| Filter by Tags | Add tag filter dropdown alongside type/category | P0 |

| Tag display | Show tags below entry name in cards | P1 |

### Codex Show Page (`Codex/Show.vue`)

| Feature | Impact | Priority |

|---------|--------|----------|

| Tag Manager | New component in sidebar | P0 |

| Enhanced Details | Update DetailManager for types | P0 |

| AI Visibility toggle | Per-detail indicator + toggle | P0 |

| Detail Presets | "Add from preset" button | P1 |

### Editor Page (`Editor/Index.vue`)

| Feature | Impact | Priority |

|---------|--------|----------|

| No direct changes | Editor uses existing CodexContextBuilder | N/A |

---

## Phase 3: Database Schema (New Migration)

### New Tables

**`codex_tags`** - Tag definitions per novel

```php
Schema::create('codex_tags', function (Blueprint $table) {
    $table->id();
    $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('color', 7)->nullable(); // Hex color
    $table->string('type')->nullable(); // Limit to specific entry types
    $table->boolean('is_predefined')->default(false);
    $table->timestamps();
    
    $table->unique(['novel_id', 'name']);
});
```

**`codex_entry_tags`** - Pivot table

```php
Schema::create('codex_entry_tags', function (Blueprint $table) {
    $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
    $table->foreignId('codex_tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['codex_entry_id', 'codex_tag_id']);
});
```

**`codex_detail_definitions`** - Detail type templates

```php
Schema::create('codex_detail_definitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('novel_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->enum('type', ['text', 'line', 'dropdown', 'codex_reference']);
    $table->json('options')->nullable(); // For dropdown options
    $table->json('entry_types')->nullable(); // Which codex types can use this
    $table->boolean('show_in_sidebar')->default(false);
    $table->enum('ai_visibility', ['always', 'never', 'nsfw_only'])->default('always');
    $table->integer('sort_order')->default(0);
    $table->boolean('is_preset')->default(false);
    $table->timestamps();
});
```

### Modified Tables

**`codex_details`** - Add definition reference and AI visibility

```php
Schema::table('codex_details', function (Blueprint $table) {
    $table->foreignId('definition_id')->nullable()
          ->constrained('codex_detail_definitions')->nullOnDelete();
    $table->enum('ai_visibility', ['always', 'never', 'nsfw_only'])->default('always');
});
```

---

## Phase 4: Backend Implementation

### New Models

**`App\Models\CodexTag`**

- Relationships: `belongsTo(Novel)`, `belongsToMany(CodexEntry)`
- Scopes: `scopeForType($type)` to filter by entry type

**`App\Models\CodexDetailDefinition`**

- Relationships: `belongsTo(Novel)`, `hasMany(CodexDetail)`
- Method: `getOptionsArray()` for dropdown values

### Modified Models

**`CodexEntry`** - Add tags relationship

```php
public function tags(): BelongsToMany
{
    return $this->belongsToMany(CodexTag::class, 'codex_entry_tags');
}
```

**`CodexDetail`** - Add definition and AI visibility

```php
protected $fillable = [..., 'definition_id', 'ai_visibility'];

public function definition(): BelongsTo
{
    return $this->belongsTo(CodexDetailDefinition::class, 'definition_id');
}
```

### New Controllers

**`CodexTagController`**

- `index(Novel)` - List tags for novel
- `store(Novel)` - Create new tag
- `update(CodexTag)` - Update tag
- `destroy(CodexTag)` - Delete tag
- `assignToEntry(CodexEntry)` - Add tag to entry
- `removeFromEntry(CodexEntry, CodexTag)` - Remove tag

**`CodexDetailDefinitionController`**

- `index(Novel)` - List definitions (includes system presets)
- `store(Novel)` - Create custom definition
- `update(CodexDetailDefinition)` - Update definition
- `destroy(CodexDetailDefinition)` - Delete definition

### Updated Controllers

**`CodexController`**

- Add tags to entry response in `show()`, `index()`, `apiShow()`
- Add `?tag=` filter parameter in `index()`

**`CodexDetailController`**

- Accept `definition_id` and `ai_visibility` in store/update
- Return detail type information in response

### Service Updates

**`CodexContextBuilder`** (existing from Sprint 13)

- Filter details by `ai_visibility`
- Respect `nsfw_only` mode when building context

---

## Phase 5: API Endpoints

### Tags System

| Method | Endpoint | Description |

|--------|----------|-------------|

| GET | `/api/novels/{novel}/codex/tags` | List all tags |

| POST | `/api/novels/{novel}/codex/tags` | Create tag |

| PATCH | `/api/codex/tags/{tag}` | Update tag |

| DELETE | `/api/codex/tags/{tag}` | Delete tag |

| POST | `/api/codex/{entry}/tags` | Add tag to entry |

| DELETE | `/api/codex/{entry}/tags/{tag}` | Remove tag from entry |

### Detail Definitions

| Method | Endpoint | Description |

|--------|----------|-------------|

| GET | `/api/novels/{novel}/codex/detail-definitions` | List definitions + presets |

| POST | `/api/novels/{novel}/codex/detail-definitions` | Create definition |

| PATCH | `/api/codex/detail-definitions/{def}` | Update definition |

| DELETE | `/api/codex/detail-definitions/{def}` | Delete definition |

---

## Phase 6: Frontend Implementation

### New Components

**`TagManager.vue`** - Tag assignment UI

- Inline tag input with autocomplete
- Color-coded tag pills
- Quick create new tag from input
- Auto-saves on change (no submit button)

**`DetailTypeSelector.vue`** - Detail type picker

- Radio buttons: Text, Line, Dropdown, Codex Reference
- Show options input for dropdown type
- Show entry selector for codex reference type

**`DetailPresetPicker.vue`** - Preset selection modal

- Grid of available presets
- Organized by entry type
- One-click add to entry

**`AIVisibilityToggle.vue`** - Per-detail AI control

- Three-state toggle: Always / Never / NSFW Only
- Visual indicator icons
- Inline update (auto-save)

### Updated Components

**`DetailManager.vue`**

- Support for different detail types
- Type indicator badge
- AI visibility toggle per detail
- "Add from preset" button
- Dropdown renders as select
- Codex reference renders as entry picker

**`CodexEntryCard.vue`**

- Display assigned tags below name
- Color-coded tag pills

**`Codex/Index.vue`**

- Add tag filter dropdown
- Multi-select tag filtering

**`Codex/Show.vue`**

- Add TagManager to sidebar
- Update DetailManager with new props

---

## Phase 7: Auto-Everything Implementation

### Pattern: Immediate Sync (No Queue Workers)

Every action saves immediately and syncs UI:

```typescript
// TagManager.vue - Example auto-save pattern
const addTag = async (tagId: number) => {
    // Optimistic UI update
    localTags.value.push(allTags.value.find(t => t.id === tagId));
    
    try {
        await axios.post(`/api/codex/${props.entryId}/tags`, { tag_id: tagId });
        emit('updated'); // Trigger parent reload if needed
    } catch {
        // Rollback on error
        localTags.value = localTags.value.filter(t => t.id !== tagId);
        showError('Failed to add tag');
    }
};
```

### Pattern: Live UI Updates

For cross-tab updates (like mentions), tags and details can use the same polling mechanism already in place:

```typescript
// Codex/Show.vue already polls /api/codex/{entry} every 5 seconds
// Just need to include tags in the response
```

---

## Phase 8: Implementation Order (Priority Sequence)

### Day 1-2: Database and Models

1. Create migration for Sprint 14 tables
2. Create `CodexTag` model
3. Create `CodexDetailDefinition` model
4. Update `CodexEntry` model (tags relationship)
5. Update `CodexDetail` model (definition, ai_visibility)

### Day 3-4: Backend Controllers and Routes

1. Create `CodexTagController`
2. Create `CodexDetailDefinitionController`
3. Update `CodexController` (include tags)
4. Update `CodexDetailController` (types, AI visibility)
5. Register routes
6. Seed system presets (Story Role, Pronouns, etc.)

### Day 5-6: Frontend Components

1. Create `TagManager.vue`
2. Create `AIVisibilityToggle.vue`
3. Update `DetailManager.vue` for types
4. Create `DetailPresetPicker.vue`
5. Create `DetailTypeSelector.vue`

### Day 7-8: Integration and Polish

1. Update `Codex/Index.vue` (tag filter)
2. Update `Codex/Show.vue` (tag manager)
3. Update `CodexEntryCard.vue` (tag display)
4. Test auto-save behavior
5. Mobile responsiveness

### Day 9-10: Testing and Documentation

1. Feature tests for new endpoints
2. Test AI context builder with visibility
3. Update API documentation
4. Polish edge cases

---

## Phase 9: Key Files to Modify

### Backend

- `database/migrations/2026_01_XX_sprint14_tags_and_details.php` (new)
- `app/Models/CodexTag.php` (new)
- `app/Models/CodexDetailDefinition.php` (new)
- `app/Models/CodexEntry.php` (add tags relationship)
- `app/Models/CodexDetail.php` (add definition_id, ai_visibility)
- `app/Http/Controllers/CodexTagController.php` (new)
- `app/Http/Controllers/CodexDetailDefinitionController.php` (new)
- `app/Http/Controllers/CodexController.php` (update responses)
- `app/Http/Controllers/CodexDetailController.php` (update for types)
- `app/Services/Codex/CodexContextBuilder.php` (respect AI visibility)
- `routes/web.php` (add new routes)
- `database/seeders/CodexPresetSeeder.php` (new - system presets)

### Frontend

- `resources/js/components/codex/TagManager.vue` (new)
- `resources/js/components/codex/AIVisibilityToggle.vue` (new)
- `resources/js/components/codex/DetailTypeSelector.vue` (new)
- `resources/js/components/codex/DetailPresetPicker.vue` (new)
- `resources/js/components/codex/DetailManager.vue` (major update)
- `resources/js/components/codex/CodexEntryCard.vue` (add tags)
- `resources/js/components/codex/index.ts` (export new components)
- `resources/js/pages/Codex/Index.vue` (tag filter)
- `resources/js/pages/Codex/Show.vue` (tag manager integration)

---

## Phase 10: Auto-Everything Checklist

Ensure all features match the auto-save philosophy:

- [x] **Tags**: Add/remove tag -> instant save, no submit button
- [x] **Detail Types**: Change type -> saves immediately
- [x] **AI Visibility**: Toggle -> instant update with visual feedback
- [x] **Presets**: Select preset -> detail created immediately
- [x] **Filter by Tags**: Select tag filter -> immediate filter (client-side or instant API)
- [x] **Cross-tab sync**: Tags/details included in polling response

---

## NovelCrafter Parity Checklist (Sprint 14)

From [Codex Details](https://www.novelcrafter.com/help/docs/codex/codex-details):

- [ ] Detail types: Text, Line, Dropdown, Codex Reference
- [ ] AI visibility per detail (Always, Never, NSFW Only)
- [ ] Show in sidebar option for dropdown values
- [ ] Detail definitions at novel/series level

From [Codex Details Quick Create](https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create):

- [ ] Built-in presets (Story Role, Pronouns, Backstory, etc.)
- [ ] Add from preset button
- [ ] Quick create within entry page

From [Codex Categories](https://www.novelcrafter.com/help/docs/codex/codex-categories):

- [ ] Tags separate from Categories (Tags = quick labels, Categories = AI grouping)
- [ ] Tag filtering in index view
- [ ] Predefined tags per entry type
- [ ] Tag colors

---

## Risk Mitigation

| Risk | Mitigation |

|------|------------|

| Detail type migration breaks existing data | Default all existing details to `type: 'text'`, `ai_visibility: 'always'` |

| Performance with many tags | Index on `novel_id`, limit tags per novel, lazy load in UI |

| Complex dropdown UI | Keep simple single-select first, multi-select later if needed |

| Codex reference circular | Prevent selecting self, limit depth in queries |
