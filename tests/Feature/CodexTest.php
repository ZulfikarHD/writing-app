<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\CodexAlias;
use App\Models\CodexCategory;
use App\Models\CodexDetail;
use App\Models\CodexEntry;
use App\Models\CodexRelation;
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
}
