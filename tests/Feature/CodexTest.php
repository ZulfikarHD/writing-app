<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\CodexAlias;
use App\Models\CodexCategory;
use App\Models\CodexDetail;
use App\Models\CodexDetailDefinition;
use App\Models\CodexEntry;
use App\Models\CodexRelation;
use App\Models\CodexTag;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
    }

    // ==================== Entry CRUD Tests ====================

    public function test_can_create_codex_entry(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex", [
                'type' => 'character',
                'name' => 'Elena Blackwood',
                'description' => 'A skilled mage from the Northern Kingdom.',
                'ai_context_mode' => 'detected',
            ]);

        $response->assertCreated()
            ->assertJsonPath('entry.name', 'Elena Blackwood')
            ->assertJsonPath('entry.type', 'character')
            ->assertJsonStructure([
                'entry' => [
                    'id',
                    'type',
                    'name',
                    'description',
                    'ai_context_mode',
                ],
                'redirect',
            ]);

        $this->assertDatabaseHas('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Elena Blackwood',
            'type' => 'character',
        ]);
    }

    public function test_can_list_entries_by_type(): void
    {
        CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id, 'name' => 'Character 1']);
        CodexEntry::factory()->location()->create(['novel_id' => $this->novel->id, 'name' => 'Location 1']);
        CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id, 'name' => 'Character 2']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex?type=character");

        $response->assertOk();
        $entries = $response->json('entries');
        $this->assertCount(2, $entries);
        $this->assertTrue(collect($entries)->every(fn ($e) => $e['type'] === 'character'));
    }

    public function test_can_search_entries_by_name(): void
    {
        CodexEntry::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Elena Blackwood']);
        CodexEntry::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Marcus Stone']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex?search=Elena");

        $response->assertOk();
        $entries = $response->json('entries');
        $this->assertCount(1, $entries);
        $this->assertEquals('Elena Blackwood', $entries[0]['name']);
    }

    public function test_can_update_codex_entry(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Old Name',
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/{$entry->id}", [
                'name' => 'New Name',
                'description' => 'Updated description',
            ]);

        $response->assertOk()
            ->assertJsonPath('entry.name', 'New Name');

        $this->assertDatabaseHas('codex_entries', [
            'id' => $entry->id,
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_archive_codex_entry(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/archive");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $entry->refresh();
        $this->assertTrue($entry->is_archived);
    }

    public function test_can_restore_archived_entry(): void
    {
        $entry = CodexEntry::factory()->archived()->create([
            'novel_id' => $this->novel->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/restore");

        $response->assertOk();

        $entry->refresh();
        $this->assertFalse($entry->is_archived);
    }

    public function test_can_delete_codex_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/{$entry->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('codex_entries', ['id' => $entry->id]);
    }

    public function test_can_get_single_entry_via_api(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Test Entry',
            'type' => 'character',
            'description' => 'A test description',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/codex/{$entry->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'entry' => [
                    'id',
                    'type',
                    'name',
                    'description',
                    'thumbnail_path',
                    'ai_context_mode',
                ],
            ]);
    }

    // ==================== Alias Tests ====================

    public function test_can_add_alias_to_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/aliases", [
                'alias' => 'Blackwood',
            ]);

        $response->assertCreated()
            ->assertJsonPath('alias.alias', 'Blackwood');

        $this->assertDatabaseHas('codex_aliases', [
            'codex_entry_id' => $entry->id,
            'alias' => 'Blackwood',
        ]);
    }

    public function test_can_delete_alias(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $alias = CodexAlias::create([
            'codex_entry_id' => $entry->id,
            'alias' => 'Test Alias',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/aliases/{$alias->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('codex_aliases', ['id' => $alias->id]);
    }

    public function test_search_finds_entries_by_alias(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena Blackwood',
        ]);
        CodexAlias::create([
            'codex_entry_id' => $entry->id,
            'alias' => 'The Shadow Mage',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex?search=Shadow");

        $response->assertOk();
        $entries = $response->json('entries');
        $this->assertCount(1, $entries);
        $this->assertEquals('Elena Blackwood', $entries[0]['name']);
    }

    // ==================== Detail Tests ====================

    public function test_can_add_detail_to_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/details", [
                'key_name' => 'Age',
                'value' => '32',
            ]);

        $response->assertCreated()
            ->assertJsonPath('detail.key_name', 'Age')
            ->assertJsonPath('detail.value', '32');

        $this->assertDatabaseHas('codex_details', [
            'codex_entry_id' => $entry->id,
            'key_name' => 'Age',
            'value' => '32',
        ]);
    }

    public function test_can_update_detail(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $detail = CodexDetail::create([
            'codex_entry_id' => $entry->id,
            'key_name' => 'Age',
            'value' => '25',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/details/{$detail->id}", [
                'value' => '26',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('codex_details', [
            'id' => $detail->id,
            'value' => '26',
        ]);
    }

    public function test_can_delete_detail(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $detail = CodexDetail::create([
            'codex_entry_id' => $entry->id,
            'key_name' => 'Height',
            'value' => '180cm',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/details/{$detail->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('codex_details', ['id' => $detail->id]);
    }

    // ==================== Relation Tests ====================

    public function test_can_create_relation_between_entries(): void
    {
        $source = CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id, 'name' => 'Hero']);
        $target = CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id, 'name' => 'Villain']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$source->id}/relations", [
                'target_entry_id' => $target->id,
                'relation_type' => 'enemy',
                'label' => 'Arch Nemesis',
            ]);

        $response->assertCreated()
            ->assertJsonPath('relation.relation_type', 'enemy')
            ->assertJsonPath('relation.label', 'Arch Nemesis');

        $this->assertDatabaseHas('codex_relations', [
            'source_entry_id' => $source->id,
            'target_entry_id' => $target->id,
            'relation_type' => 'enemy',
        ]);
    }

    public function test_can_create_bidirectional_relation(): void
    {
        $entry1 = CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id]);
        $entry2 = CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry1->id}/relations", [
                'target_entry_id' => $entry2->id,
                'relation_type' => 'sibling',
                'is_bidirectional' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('relation.is_bidirectional', true);
    }

    public function test_can_delete_relation(): void
    {
        $entry1 = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $entry2 = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $relation = CodexRelation::create([
            'source_entry_id' => $entry1->id,
            'target_entry_id' => $entry2->id,
            'relation_type' => 'friend',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/relations/{$relation->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('codex_relations', ['id' => $relation->id]);
    }

    // ==================== Category Tests ====================

    public function test_can_create_category(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/categories", [
                'name' => 'Main Characters',
                'color' => '#8b5cf6',
            ]);

        $response->assertCreated()
            ->assertJsonPath('category.name', 'Main Characters');

        $this->assertDatabaseHas('codex_categories', [
            'novel_id' => $this->novel->id,
            'name' => 'Main Characters',
        ]);
    }

    public function test_can_assign_category_to_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $category = CodexCategory::create([
            'novel_id' => $this->novel->id,
            'name' => 'Protagonists',
            'color' => '#10b981',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/categories", [
                'category_ids' => [$category->id],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('codex_entry_categories', [
            'codex_entry_id' => $entry->id,
            'codex_category_id' => $category->id,
        ]);
    }

    public function test_can_filter_entries_by_category(): void
    {
        $category = CodexCategory::create([
            'novel_id' => $this->novel->id,
            'name' => 'Heroes',
            'color' => '#3b82f6',
            'sort_order' => 0,
        ]);

        $hero = CodexEntry::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Hero']);
        $hero->categories()->attach($category->id);

        CodexEntry::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Villain']);

        $response = $this->actingAs($this->user)
            ->get("/novels/{$this->novel->id}/codex?category={$category->id}");

        $response->assertOk();
    }

    // ==================== Quick Create Tests ====================

    public function test_can_quick_create_entry(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/quick-create", [
                'name' => 'Quick Character',
                'type' => 'character',
            ]);

        $response->assertCreated()
            ->assertJsonPath('entry.name', 'Quick Character');

        $this->assertDatabaseHas('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Quick Character',
            'type' => 'character',
        ]);
    }

    public function test_quick_create_adds_alias_if_different_from_name(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/quick-create", [
                'name' => 'Elena Blackwood',
                'type' => 'character',
                'add_alias' => 'Elena',
            ]);

        $response->assertCreated();

        $entry = CodexEntry::where('name', 'Elena Blackwood')->first();
        $this->assertDatabaseHas('codex_aliases', [
            'codex_entry_id' => $entry->id,
            'alias' => 'Elena',
        ]);
    }

    // ==================== Authorization Tests ====================

    public function test_unauthorized_user_cannot_access_entry(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $entry = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/codex/{$entry->id}");

        $response->assertForbidden();
    }

    public function test_unauthorized_user_cannot_update_entry(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $entry = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/{$entry->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertForbidden();
    }

    public function test_unauthorized_user_cannot_create_entry_in_other_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$otherNovel->id}/codex", [
                'type' => 'character',
                'name' => 'Hacked Entry',
            ]);

        $response->assertForbidden();
    }

    // ==================== Mention Tracking Tests ====================

    public function test_mentions_are_tracked_on_scan(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
        ]);

        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Elena walked into the room. Elena smiled.'],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/scan-mentions");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
        ]);
    }

    // ==================== Validation Tests ====================

    public function test_entry_requires_valid_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex", [
                'type' => 'invalid_type',
                'name' => 'Test Entry',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_entry_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex", [
                'type' => 'character',
                'name' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // ==================== Bulk Import/Export Tests ====================

    public function test_can_export_codex_as_json(): void
    {
        CodexEntry::factory()->count(3)->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/export/json");

        $response->assertOk()
            ->assertJsonStructure([
                'entries' => [
                    '*' => [
                        'type',
                        'name',
                        'description',
                    ],
                ],
            ]);
    }

    public function test_can_export_codex_as_csv(): void
    {
        CodexEntry::factory()->count(2)->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->get("/api/novels/{$this->novel->id}/codex/export/csv");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ==================== API for Editor Tests ====================

    public function test_can_get_entries_for_editor_highlighting(): void
    {
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Hero',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/editor");

        $response->assertOk()
            ->assertJsonStructure([
                'entries' => [
                    '*' => [
                        'id',
                        'type',
                        'name',
                        'description',
                        'ai_context_mode',
                        'aliases',
                        'details',
                    ],
                ],
            ]);
    }

    public function test_archived_entries_not_included_in_listing(): void
    {
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Active Entry',
            'is_archived' => false,
        ]);
        CodexEntry::factory()->archived()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Archived Entry',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex");

        $response->assertOk();
        $entries = $response->json('entries');
        $this->assertCount(1, $entries);
        $this->assertEquals('Active Entry', $entries[0]['name']);
    }

    // ==================== Sprint 13: Tracking Toggle Tests (US-12.2) ====================

    public function test_can_toggle_tracking_enabled(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'is_tracking_enabled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/{$entry->id}", [
                'is_tracking_enabled' => false,
            ]);

        $response->assertOk();

        $entry->refresh();
        $this->assertFalse($entry->is_tracking_enabled);
    }

    public function test_disabled_tracking_entry_not_scanned_for_mentions(): void
    {
        // Create entry with tracking disabled
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Common Word',
            'is_tracking_enabled' => false,
        ]);

        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Common Word appears here.'],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/scan-mentions");

        $response->assertOk();

        // Entry with tracking disabled should not have mentions
        $this->assertDatabaseMissing('codex_mentions', [
            'codex_entry_id' => $entry->id,
        ]);
    }

    // ==================== Sprint 13: Research Notes Tests (US-12.3) ====================

    public function test_can_update_research_notes(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'research_notes' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/{$entry->id}", [
                'research_notes' => 'This is my private research note.',
            ]);

        $response->assertOk();

        $entry->refresh();
        $this->assertEquals('This is my private research note.', $entry->research_notes);
    }

    public function test_research_notes_included_in_show_response(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'research_notes' => 'My private notes',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/codex/{$entry->id}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('entry')
                ->where('entry.research_notes', 'My private notes')
            );
    }

    // ==================== Sprint 13: External Links Tests (F-12.2.2) ====================

    public function test_can_add_external_link(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/external-links", [
                'title' => 'Reference Image',
                'url' => 'https://example.com/image.png',
                'notes' => 'Character reference',
            ]);

        $response->assertCreated()
            ->assertJsonPath('link.title', 'Reference Image')
            ->assertJsonPath('link.url', 'https://example.com/image.png');

        $this->assertDatabaseHas('codex_external_links', [
            'codex_entry_id' => $entry->id,
            'title' => 'Reference Image',
        ]);
    }

    public function test_can_update_external_link(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $link = $entry->externalLinks()->create([
            'title' => 'Old Title',
            'url' => 'https://old.com',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/external-links/{$link->id}", [
                'title' => 'New Title',
                'url' => 'https://new.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('link.title', 'New Title');

        $this->assertDatabaseHas('codex_external_links', [
            'id' => $link->id,
            'title' => 'New Title',
            'url' => 'https://new.com',
        ]);
    }

    public function test_can_delete_external_link(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $link = $entry->externalLinks()->create([
            'title' => 'To Delete',
            'url' => 'https://delete.me',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/external-links/{$link->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('codex_external_links', ['id' => $link->id]);
    }

    public function test_external_link_requires_valid_url(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/external-links", [
                'title' => 'Test Link',
                'url' => 'not-a-valid-url',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }

    public function test_unauthorized_user_cannot_add_external_link(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $entry = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/external-links", [
                'title' => 'Hacked Link',
                'url' => 'https://hacker.com',
            ]);

        $response->assertForbidden();
    }

    // ==================== Sprint 14: Tags System Tests (US-12.4) ====================

    public function test_can_create_tag(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/tags", [
                'name' => 'Protagonist',
                'color' => '#8B5CF6',
            ]);

        $response->assertCreated()
            ->assertJsonPath('tag.name', 'Protagonist')
            ->assertJsonPath('tag.color', '#8B5CF6');

        $this->assertDatabaseHas('codex_tags', [
            'novel_id' => $this->novel->id,
            'name' => 'Protagonist',
        ]);
    }

    public function test_can_list_tags_for_novel(): void
    {
        CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Hero',
            'color' => '#10B981',
        ]);
        CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Villain',
            'color' => '#EF4444',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/tags");

        $response->assertOk()
            ->assertJsonCount(2, 'tags');
    }

    public function test_can_assign_tag_to_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Main Character',
            'color' => '#8B5CF6',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/tags", [
                'tag_id' => $tag->id,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('codex_entry_tags', [
            'codex_entry_id' => $entry->id,
            'codex_tag_id' => $tag->id,
        ]);
    }

    public function test_can_remove_tag_from_entry(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'To Remove',
            'color' => '#EF4444',
        ]);
        $entry->tags()->attach($tag->id);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/{$entry->id}/tags/{$tag->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('codex_entry_tags', [
            'codex_entry_id' => $entry->id,
            'codex_tag_id' => $tag->id,
        ]);
    }

    public function test_cannot_create_duplicate_tag_name(): void
    {
        CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Existing Tag',
            'color' => '#8B5CF6',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/tags", [
                'name' => 'Existing Tag',
                'color' => '#EF4444',
            ]);

        $response->assertUnprocessable();
    }

    public function test_can_update_tag(): void
    {
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Old Name',
            'color' => '#8B5CF6',
            'is_predefined' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/tags/{$tag->id}", [
                'name' => 'New Name',
                'color' => '#EF4444',
            ]);

        $response->assertOk()
            ->assertJsonPath('tag.name', 'New Name')
            ->assertJsonPath('tag.color', '#EF4444');
    }

    public function test_cannot_update_predefined_tag(): void
    {
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Predefined',
            'color' => '#8B5CF6',
            'is_predefined' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/tags/{$tag->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertUnprocessable();
    }

    public function test_can_delete_tag(): void
    {
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'To Delete',
            'color' => '#EF4444',
            'is_predefined' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/tags/{$tag->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('codex_tags', ['id' => $tag->id]);
    }

    public function test_cannot_delete_predefined_tag(): void
    {
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Predefined',
            'color' => '#8B5CF6',
            'is_predefined' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/tags/{$tag->id}");

        $response->assertUnprocessable();
    }

    public function test_can_filter_entries_by_tag(): void
    {
        $tag = CodexTag::create([
            'novel_id' => $this->novel->id,
            'name' => 'Protagonist',
            'color' => '#8B5CF6',
        ]);

        $taggedEntry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Tagged Entry',
        ]);
        $taggedEntry->tags()->attach($tag->id);

        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Untagged Entry',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/novels/{$this->novel->id}/codex?tag={$tag->id}");

        $response->assertOk();
    }

    // ==================== Sprint 14: Detail Types & AI Visibility Tests (US-12.5, US-12.6) ====================

    public function test_can_add_detail_with_ai_visibility(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/details", [
                'key_name' => 'Secret Info',
                'value' => 'This is private',
                'ai_visibility' => 'never',
            ]);

        $response->assertCreated()
            ->assertJsonPath('detail.ai_visibility', 'never');

        $this->assertDatabaseHas('codex_details', [
            'codex_entry_id' => $entry->id,
            'key_name' => 'Secret Info',
            'ai_visibility' => 'never',
        ]);
    }

    public function test_can_update_detail_ai_visibility(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $detail = CodexDetail::create([
            'codex_entry_id' => $entry->id,
            'key_name' => 'Test',
            'value' => 'Value',
            'sort_order' => 0,
            'ai_visibility' => 'always',
            'type' => 'text',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/details/{$detail->id}", [
                'ai_visibility' => 'nsfw_only',
            ]);

        $response->assertOk()
            ->assertJsonPath('detail.ai_visibility', 'nsfw_only');
    }

    public function test_can_add_detail_with_type(): void
    {
        $entry = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/details", [
                'key_name' => 'Occupation',
                'value' => 'Knight',
                'type' => 'line',
            ]);

        $response->assertCreated()
            ->assertJsonPath('detail.type', 'line');
    }

    public function test_can_add_detail_from_preset(): void
    {
        $entry = CodexEntry::factory()->character()->create(['novel_id' => $this->novel->id]);

        // Add Story Role from preset (index 0 in SYSTEM_PRESETS)
        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/details/from-preset", [
                'preset_index' => 0,
                'value' => 'Protagonist',
            ]);

        $response->assertCreated()
            ->assertJsonPath('detail.key_name', 'Story Role');

        $this->assertDatabaseHas('codex_details', [
            'codex_entry_id' => $entry->id,
            'key_name' => 'Story Role',
            'value' => 'Protagonist',
        ]);
    }

    // ==================== Sprint 14: Detail Definition Tests (US-12.7) ====================

    public function test_can_list_detail_definitions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/detail-definitions");

        $response->assertOk()
            ->assertJsonStructure([
                'definitions',
                'presets',
            ]);
    }

    public function test_can_create_detail_definition(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/detail-definitions", [
                'name' => 'Custom Attribute',
                'type' => 'dropdown',
                'options' => ['Option A', 'Option B', 'Option C'],
                'entry_types' => ['character'],
                'ai_visibility' => 'always',
            ]);

        $response->assertCreated()
            ->assertJsonPath('definition.name', 'Custom Attribute')
            ->assertJsonPath('definition.type', 'dropdown');

        $this->assertDatabaseHas('codex_detail_definitions', [
            'novel_id' => $this->novel->id,
            'name' => 'Custom Attribute',
        ]);
    }

    public function test_dropdown_definition_requires_options(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/detail-definitions", [
                'name' => 'Invalid Dropdown',
                'type' => 'dropdown',
                'options' => ['Only One'],
            ]);

        $response->assertUnprocessable();
    }

    public function test_can_update_detail_definition(): void
    {
        $definition = CodexDetailDefinition::create([
            'novel_id' => $this->novel->id,
            'name' => 'Old Name',
            'type' => 'text',
            'ai_visibility' => 'always',
            'is_preset' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/codex/detail-definitions/{$definition->id}", [
                'name' => 'New Name',
                'ai_visibility' => 'never',
            ]);

        $response->assertOk()
            ->assertJsonPath('definition.name', 'New Name')
            ->assertJsonPath('definition.ai_visibility', 'never');
    }

    public function test_can_delete_detail_definition(): void
    {
        $definition = CodexDetailDefinition::create([
            'novel_id' => $this->novel->id,
            'name' => 'To Delete',
            'type' => 'text',
            'ai_visibility' => 'always',
            'is_preset' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/codex/detail-definitions/{$definition->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('codex_detail_definitions', ['id' => $definition->id]);
    }

    public function test_presets_filtered_by_entry_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/detail-definitions?entry_type=character");

        $response->assertOk();

        // Character presets should include Story Role, Pronouns, etc.
        $presets = $response->json('presets');
        $presetNames = collect($presets)->pluck('name')->toArray();
        $this->assertContains('Story Role', $presetNames);
        $this->assertContains('Pronouns', $presetNames);
    }

    // ==================== Sprint 14: AI Context Builder with Visibility ====================

    public function test_ai_context_excludes_never_visibility_details(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Test Entry',
            'ai_context_mode' => 'always',
        ]);

        // Create visible detail
        CodexDetail::create([
            'codex_entry_id' => $entry->id,
            'key_name' => 'Visible',
            'value' => 'This should appear',
            'sort_order' => 0,
            'ai_visibility' => 'always',
            'type' => 'text',
        ]);

        // Create hidden detail
        CodexDetail::create([
            'codex_entry_id' => $entry->id,
            'key_name' => 'Hidden',
            'value' => 'This should NOT appear',
            'sort_order' => 1,
            'ai_visibility' => 'never',
            'type' => 'text',
        ]);

        $builder = new \App\Services\Codex\CodexContextBuilder();
        $context = $builder->buildContext([$entry->id]);

        $this->assertCount(1, $context);
        $details = $context->first()['details'];

        // Only the visible detail should be included
        $this->assertCount(1, $details);
        $this->assertEquals('Visible', $details[0]['key']);
    }

    // ==================== Sprint 15: Duplicate Entry Tests (F-12.7.2) ====================

    public function test_can_duplicate_entry(): void
    {
        $original = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Original Entry',
            'type' => 'character',
            'description' => 'Original description',
            'research_notes' => 'Some research notes',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$original->id}/duplicate");

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('entry.name', 'Original Entry (Copy)')
            ->assertJsonStructure([
                'success',
                'entry' => ['id', 'type', 'name', 'description'],
                'redirect',
            ]);

        $this->assertDatabaseHas('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Original Entry (Copy)',
            'type' => 'character',
            'description' => 'Original description',
        ]);
    }

    public function test_duplicate_clones_aliases(): void
    {
        $original = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena Blackwood',
        ]);
        CodexAlias::create([
            'codex_entry_id' => $original->id,
            'alias' => 'Elena',
        ]);
        CodexAlias::create([
            'codex_entry_id' => $original->id,
            'alias' => 'The Shadow Mage',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$original->id}/duplicate");

        $response->assertCreated();

        $duplicateId = $response->json('entry.id');
        $duplicate = CodexEntry::find($duplicateId);

        $this->assertCount(2, $duplicate->aliases);
        $this->assertTrue($duplicate->aliases->pluck('alias')->contains('Elena'));
        $this->assertTrue($duplicate->aliases->pluck('alias')->contains('The Shadow Mage'));
    }

    public function test_duplicate_clones_details(): void
    {
        $original = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        CodexDetail::create([
            'codex_entry_id' => $original->id,
            'key_name' => 'Age',
            'value' => '25',
            'sort_order' => 0,
            'ai_visibility' => 'always',
            'type' => 'line',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$original->id}/duplicate");

        $response->assertCreated();

        $duplicateId = $response->json('entry.id');
        $duplicate = CodexEntry::find($duplicateId);

        $this->assertCount(1, $duplicate->details);
        $this->assertEquals('Age', $duplicate->details->first()->key_name);
        $this->assertEquals('25', $duplicate->details->first()->value);
    }

    public function test_duplicate_increments_name_on_multiple_copies(): void
    {
        $original = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Test Entry',
        ]);

        // First duplicate
        $this->actingAs($this->user)->postJson("/api/codex/{$original->id}/duplicate");

        // Second duplicate
        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$original->id}/duplicate");

        $response->assertCreated()
            ->assertJsonPath('entry.name', 'Test Entry (Copy 2)');
    }

    public function test_unauthorized_user_cannot_duplicate_entry(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $entry = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/{$entry->id}/duplicate");

        $response->assertForbidden();
    }

    // ==================== Sprint 15: Bulk Create Tests (US-12.12) ====================

    public function test_can_bulk_create_entries(): void
    {
        $input = "Alice | character | A young witch and the protagonist\nBob | character | Alice's mentor";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => false,
            ]);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('created_count', 2);

        $this->assertDatabaseHas('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Alice',
            'type' => 'character',
        ]);
        $this->assertDatabaseHas('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Bob',
            'type' => 'character',
        ]);
    }

    public function test_bulk_create_preview_mode(): void
    {
        $input = "Test Entry | character | Description here";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'preview' => true,
            ])
            ->assertJsonPath('total', 1);

        // Entry should NOT be created in preview mode
        $this->assertDatabaseMissing('codex_entries', [
            'novel_id' => $this->novel->id,
            'name' => 'Test Entry',
        ]);
    }

    public function test_bulk_create_returns_parse_errors(): void
    {
        $input = "Valid Entry | character | Good\nInvalid Entry | badtype | Bad type";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => false,
            ]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['parse_errors']);
    }

    public function test_bulk_create_skips_duplicates(): void
    {
        // Create existing entry
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Existing Entry',
        ]);

        $input = "Existing Entry | character | This one exists\nNew Entry | character | This one is new";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => false,
                'skip_duplicates' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('created_count', 1)
            ->assertJsonPath('skipped_count', 1);
    }

    public function test_bulk_create_ignores_comment_lines(): void
    {
        $input = "# This is a comment\nReal Entry | character | Not a comment";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('total', 1);
    }

    public function test_bulk_create_supports_all_types(): void
    {
        $input = "Char | character | A character
Loc | location | A place
Item | item | An object
Lore | lore | Some lore
Org | organization | A group
Plot | subplot | A story arc";

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/codex/bulk-create", [
                'input' => $input,
                'preview' => false,
            ]);

        $response->assertCreated()
            ->assertJsonPath('created_count', 6);
    }

    public function test_unauthorized_user_cannot_bulk_create(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$otherNovel->id}/codex/bulk-create", [
                'input' => 'Test | character | Desc',
            ]);

        $response->assertForbidden();
    }

    // ==================== Sprint 15: Swap Relation Tests (US-12.14) ====================

    public function test_can_swap_relation_direction(): void
    {
        $entry1 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Parent',
        ]);
        $entry2 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Child',
        ]);

        $relation = CodexRelation::create([
            'source_entry_id' => $entry1->id,
            'target_entry_id' => $entry2->id,
            'relation_type' => 'parent_of',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/relations/{$relation->id}/swap");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('relation.source.id', $entry2->id)
            ->assertJsonPath('relation.target.id', $entry1->id);

        $relation->refresh();
        $this->assertEquals($entry2->id, $relation->source_entry_id);
        $this->assertEquals($entry1->id, $relation->target_entry_id);
    }

    public function test_swap_preserves_relation_metadata(): void
    {
        $entry1 = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);
        $entry2 = CodexEntry::factory()->create(['novel_id' => $this->novel->id]);

        $relation = CodexRelation::create([
            'source_entry_id' => $entry1->id,
            'target_entry_id' => $entry2->id,
            'relation_type' => 'friend_of',
            'label' => 'Best Friends',
            'is_bidirectional' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/relations/{$relation->id}/swap");

        $response->assertOk()
            ->assertJsonPath('relation.relation_type', 'friend_of')
            ->assertJsonPath('relation.label', 'Best Friends')
            ->assertJsonPath('relation.is_bidirectional', true);
    }

    public function test_unauthorized_user_cannot_swap_relation(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $entry1 = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);
        $entry2 = CodexEntry::factory()->create(['novel_id' => $otherNovel->id]);

        $relation = CodexRelation::create([
            'source_entry_id' => $entry1->id,
            'target_entry_id' => $entry2->id,
            'relation_type' => 'friend',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/codex/relations/{$relation->id}/swap");

        $response->assertForbidden();
    }
}
