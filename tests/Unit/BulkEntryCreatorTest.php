<?php

namespace Tests\Unit;

use App\Models\CodexEntry;
use App\Models\Novel;
use App\Models\User;
use App\Services\Codex\BulkEntryCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for BulkEntryCreator service.
 *
 * Sprint 15 (US-12.12): Tests the parsing, validation, and creation
 * of bulk codex entries from formatted text input.
 */
class BulkEntryCreatorTest extends TestCase
{
    use RefreshDatabase;

    private BulkEntryCreator $creator;

    private Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = new BulkEntryCreator;
        $user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $user->id]);
    }

    // ==================== Parsing Tests ====================

    public function test_parses_valid_single_line(): void
    {
        $input = 'Alice | character | A young witch';
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals('Alice', $result['entries'][0]['name']);
        $this->assertEquals('character', $result['entries'][0]['type']);
        $this->assertEquals('A young witch', $result['entries'][0]['description']);
    }

    public function test_parses_multiple_lines(): void
    {
        $input = "Alice | character | A witch\nBob | character | A wizard\nCastle | location | A fortress";
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertCount(3, $result['entries']);
    }

    public function test_parses_line_without_description(): void
    {
        $input = 'Gandalf | character';
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['entries']);
        $this->assertNull($result['entries'][0]['description']);
    }

    public function test_ignores_comment_lines(): void
    {
        $input = "# This is a comment\nAlice | character | Not a comment";
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['entries']);
        $this->assertEquals('Alice', $result['entries'][0]['name']);
    }

    public function test_ignores_empty_lines(): void
    {
        $input = "Alice | character | A witch\n\n\nBob | character | A wizard";
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertCount(2, $result['entries']);
    }

    public function test_trims_whitespace(): void
    {
        $input = '  Alice  |  character  |  A witch  ';
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertEquals('Alice', $result['entries'][0]['name']);
        $this->assertEquals('character', $result['entries'][0]['type']);
        $this->assertEquals('A witch', $result['entries'][0]['description']);
    }

    // ==================== Error Detection Tests ====================

    public function test_detects_invalid_format_missing_pipe(): void
    {
        $input = 'Alice character';
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertEmpty($result['entries']);
        $this->assertStringContainsString('Invalid format', $result['errors'][0]['message']);
    }

    public function test_detects_empty_name(): void
    {
        $input = ' | character | Description';
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Name cannot be empty', $result['errors'][0]['message']);
    }

    public function test_detects_invalid_type(): void
    {
        $input = 'Alice | invalidtype | Description';
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Invalid type', $result['errors'][0]['message']);
    }

    public function test_suggests_correct_type_for_typo(): void
    {
        $input = 'Alice | charcter | Description';
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Did you mean "character"', $result['errors'][0]['message']);
    }

    public function test_suggests_correct_type_for_plural(): void
    {
        $input = 'Alice | characters | Description';
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Did you mean "character"', $result['errors'][0]['message']);
    }

    public function test_line_numbers_in_errors(): void
    {
        $input = "Valid | character | OK\nInvalid | badtype | Bad";
        $result = $this->creator->parse($input);

        $this->assertCount(1, $result['errors']);
        $this->assertEquals(2, $result['errors'][0]['line']);
    }

    // ==================== Type Validation Tests ====================

    public function test_accepts_all_valid_types(): void
    {
        $types = ['character', 'location', 'item', 'lore', 'organization', 'subplot'];

        foreach ($types as $type) {
            $result = $this->creator->parse("Test | {$type} | Description");
            $this->assertEmpty($result['errors'], "Type '{$type}' should be valid");
            $this->assertEquals($type, $result['entries'][0]['type']);
        }
    }

    public function test_type_is_case_insensitive(): void
    {
        $input = 'Alice | CHARACTER | Description';
        $result = $this->creator->parse($input);

        $this->assertEmpty($result['errors']);
        $this->assertEquals('character', $result['entries'][0]['type']);
    }

    // ==================== Validation Against Existing Data Tests ====================

    public function test_warns_on_duplicate_name(): void
    {
        // Create existing entry
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Alice',
        ]);

        $parsed = $this->creator->parse('Alice | character | Description');
        $result = $this->creator->validate($this->novel, $parsed['entries']);

        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('already exists', $result['warnings'][0]['message']);
    }

    public function test_warns_on_duplicate_within_batch(): void
    {
        $parsed = $this->creator->parse("Alice | character | First\nAlice | character | Second");
        $result = $this->creator->validate($this->novel, $parsed['entries']);

        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('Duplicate name', $result['warnings'][0]['message']);
    }

    public function test_validation_is_case_insensitive(): void
    {
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Alice',
        ]);

        $parsed = $this->creator->parse('alice | character | Lowercase');
        $result = $this->creator->validate($this->novel, $parsed['entries']);

        $this->assertCount(1, $result['warnings']);
    }

    // ==================== Creation Tests ====================

    public function test_creates_entries(): void
    {
        $parsed = $this->creator->parse("Alice | character | A witch\nBob | character | A wizard");
        $result = $this->creator->create($this->novel, $parsed['entries']);

        $this->assertCount(2, $result['created']);
        $this->assertDatabaseHas('codex_entries', ['novel_id' => $this->novel->id, 'name' => 'Alice']);
        $this->assertDatabaseHas('codex_entries', ['novel_id' => $this->novel->id, 'name' => 'Bob']);
    }

    public function test_sets_default_ai_context_mode(): void
    {
        $parsed = $this->creator->parse('Test | character | Description');
        $result = $this->creator->create($this->novel, $parsed['entries']);

        $entry = $result['created']->first();
        $this->assertEquals(CodexEntry::CONTEXT_DETECTED, $entry->ai_context_mode);
    }

    public function test_skips_duplicates_when_configured(): void
    {
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Alice',
        ]);

        $parsed = $this->creator->parse("Alice | character | Duplicate\nBob | character | New");
        $result = $this->creator->create($this->novel, $parsed['entries'], skipDuplicates: true);

        $this->assertCount(1, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertEquals('Bob', $result['created']->first()->name);
    }

    public function test_sets_sort_order_incrementally(): void
    {
        // Create existing entries with sort_order
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'sort_order' => 10,
        ]);

        $parsed = $this->creator->parse("New1 | character | First\nNew2 | character | Second");
        $result = $this->creator->create($this->novel, $parsed['entries']);

        $entries = $result['created'];
        $this->assertEquals(11, $entries[0]->sort_order);
        $this->assertEquals(12, $entries[1]->sort_order);
    }
}
