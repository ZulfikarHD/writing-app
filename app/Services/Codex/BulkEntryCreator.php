<?php

namespace App\Services\Codex;

use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Support\Collection;

/**
 * Service for bulk creating codex entries from formatted text input.
 *
 * Sprint 15 (US-12.12): Enables rapid codex setup by parsing multi-line
 * input in the format: "Name | Type | Description"
 *
 * @see https://www.novelcrafter.com/help/docs/codex/the-codex
 */
class BulkEntryCreator
{
    /**
     * Valid entry types.
     *
     * @var array<string>
     */
    private array $validTypes;

    public function __construct()
    {
        $this->validTypes = CodexEntry::getTypes();
    }

    /**
     * Parse bulk input text into structured entry data.
     *
     * Format: "Name | Type | Description" (one per line)
     * - Name is required
     * - Type is required (character, location, item, lore, organization, subplot)
     * - Description is optional
     *
     * @param  string  $input  Multi-line input text
     * @return array{entries: array<array{line: int, name: string, type: string, description: string|null}>, errors: array<array{line: int, message: string, raw: string}>}
     */
    public function parse(string $input): array
    {
        $lines = array_filter(
            array_map('trim', explode("\n", $input)),
            fn ($line) => $line !== '' && ! str_starts_with($line, '#') // Allow comments
        );

        $entries = [];
        $errors = [];

        foreach (array_values($lines) as $index => $line) {
            $lineNumber = $index + 1;
            $result = $this->parseLine($line, $lineNumber);

            if (isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                $entries[] = $result['entry'];
            }
        }

        return [
            'entries' => $entries,
            'errors' => $errors,
        ];
    }

    /**
     * Parse a single line of bulk input.
     *
     * @return array{entry?: array{line: int, name: string, type: string, description: string|null}, error?: array{line: int, message: string, raw: string}}
     */
    private function parseLine(string $line, int $lineNumber): array
    {
        $parts = array_map('trim', explode('|', $line));

        // Must have at least name and type
        if (count($parts) < 2) {
            return [
                'error' => [
                    'line' => $lineNumber,
                    'message' => 'Invalid format. Expected: "Name | Type | Description"',
                    'raw' => $line,
                ],
            ];
        }

        $name = $parts[0];
        $type = strtolower($parts[1]);
        $description = $parts[2] ?? null;

        // Validate name
        if ($name === '') {
            return [
                'error' => [
                    'line' => $lineNumber,
                    'message' => 'Name cannot be empty',
                    'raw' => $line,
                ],
            ];
        }

        if (strlen($name) > 255) {
            return [
                'error' => [
                    'line' => $lineNumber,
                    'message' => 'Name exceeds maximum length of 255 characters',
                    'raw' => $line,
                ],
            ];
        }

        // Validate type
        if (! in_array($type, $this->validTypes, true)) {
            $suggestion = $this->suggestType($type);
            $message = "Invalid type: \"{$type}\"";
            if ($suggestion) {
                $message .= ". Did you mean \"{$suggestion}\"?";
            }
            $message .= ' Valid types: '.implode(', ', $this->validTypes);

            return [
                'error' => [
                    'line' => $lineNumber,
                    'message' => $message,
                    'raw' => $line,
                ],
            ];
        }

        // Validate description length
        if ($description !== null && strlen($description) > 10000) {
            return [
                'error' => [
                    'line' => $lineNumber,
                    'message' => 'Description exceeds maximum length of 10000 characters',
                    'raw' => $line,
                ],
            ];
        }

        return [
            'entry' => [
                'line' => $lineNumber,
                'name' => $name,
                'type' => $type,
                'description' => $description ?: null,
            ],
        ];
    }

    /**
     * Suggest a valid type based on fuzzy matching.
     */
    private function suggestType(string $invalid): ?string
    {
        $invalid = strtolower($invalid);

        // Common typos and aliases
        $aliases = [
            'char' => 'character',
            'characters' => 'character',
            'person' => 'character',
            'people' => 'character',
            'npc' => 'character',
            'loc' => 'location',
            'locations' => 'location',
            'place' => 'location',
            'places' => 'location',
            'items' => 'item',
            'object' => 'item',
            'objects' => 'item',
            'thing' => 'item',
            'things' => 'item',
            'lores' => 'lore',
            'history' => 'lore',
            'world' => 'lore',
            'worldbuilding' => 'lore',
            'org' => 'organization',
            'orgs' => 'organization',
            'organizations' => 'organization',
            'group' => 'organization',
            'groups' => 'organization',
            'faction' => 'organization',
            'factions' => 'organization',
            'subplots' => 'subplot',
            'plot' => 'subplot',
            'arc' => 'subplot',
            'story' => 'subplot',
        ];

        if (isset($aliases[$invalid])) {
            return $aliases[$invalid];
        }

        // Levenshtein distance for typos
        $minDistance = PHP_INT_MAX;
        $closest = null;

        foreach ($this->validTypes as $validType) {
            $distance = levenshtein($invalid, $validType);
            if ($distance < $minDistance && $distance <= 3) {
                $minDistance = $distance;
                $closest = $validType;
            }
        }

        return $closest;
    }

    /**
     * Validate parsed entries against existing data.
     *
     * @param  Novel  $novel  The novel to check against
     * @param  array<array{line: int, name: string, type: string, description: string|null}>  $entries
     * @return array{valid: array<array{line: int, name: string, type: string, description: string|null}>, warnings: array<array{line: int, name: string, message: string}>}
     */
    public function validate(Novel $novel, array $entries): array
    {
        $existingNames = $novel->codexEntries()
            ->pluck('name')
            ->map(fn ($name) => strtolower($name))
            ->toArray();

        $valid = [];
        $warnings = [];
        $seenNames = [];

        foreach ($entries as $entry) {
            $nameLower = strtolower($entry['name']);

            // Check for duplicates in existing entries
            if (in_array($nameLower, $existingNames, true)) {
                $warnings[] = [
                    'line' => $entry['line'],
                    'name' => $entry['name'],
                    'message' => 'An entry with this name already exists',
                ];
            }

            // Check for duplicates within the batch
            if (isset($seenNames[$nameLower])) {
                $warnings[] = [
                    'line' => $entry['line'],
                    'name' => $entry['name'],
                    'message' => 'Duplicate name in this batch (first seen on line '.$seenNames[$nameLower].')',
                ];
            }

            $seenNames[$nameLower] = $entry['line'];
            $valid[] = $entry;
        }

        return [
            'valid' => $valid,
            'warnings' => $warnings,
        ];
    }

    /**
     * Create entries in the database.
     *
     * @param  Novel  $novel  The novel to create entries for
     * @param  array<array{name: string, type: string, description: string|null}>  $entries
     * @param  bool  $skipDuplicates  Whether to skip entries with duplicate names
     * @return array{created: Collection<int, CodexEntry>, skipped: array<array{name: string, reason: string}>}
     */
    public function create(Novel $novel, array $entries, bool $skipDuplicates = true): array
    {
        $existingNames = $skipDuplicates
            ? $novel->codexEntries()->pluck('name')->map(fn ($n) => strtolower($n))->toArray()
            : [];

        $currentMaxOrder = $novel->codexEntries()->max('sort_order') ?? 0;
        $created = collect();
        $skipped = [];

        foreach ($entries as $index => $entryData) {
            $nameLower = strtolower($entryData['name']);

            // Skip duplicates if configured
            if ($skipDuplicates && in_array($nameLower, $existingNames, true)) {
                $skipped[] = [
                    'name' => $entryData['name'],
                    'reason' => 'Entry with this name already exists',
                ];

                continue;
            }

            $entry = $novel->codexEntries()->create([
                'name' => $entryData['name'],
                'type' => $entryData['type'],
                'description' => $entryData['description'],
                'ai_context_mode' => CodexEntry::CONTEXT_DETECTED,
                'sort_order' => $currentMaxOrder + $index + 1,
            ]);

            $created->push($entry);
            $existingNames[] = $nameLower; // Prevent duplicates within batch
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
        ];
    }
}
