<?php

namespace App\Services\Codex;

use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkImportService
{
    /**
     * Validation errors encountered during import.
     *
     * @var array<int, array<string, string>>
     */
    private array $errors = [];

    /**
     * Import entries from JSON data.
     *
     * @param  array<string, mixed>  $data
     * @return array{imported: int, skipped: int, errors: array<int, array<string, string>>}
     */
    public function importFromJson(Novel $novel, array $data): array
    {
        $this->errors = [];
        $imported = 0;
        $skipped = 0;

        if (! isset($data['entries']) || ! is_array($data['entries'])) {
            throw ValidationException::withMessages([
                'file' => ['Invalid JSON structure. Expected "entries" array.'],
            ]);
        }

        DB::beginTransaction();

        try {
            // First, import categories if present
            $categoryMap = $this->importCategories($novel, $data['categories'] ?? []);

            // Then import entries
            foreach ($data['entries'] as $index => $entryData) {
                $result = $this->importEntry($novel, $entryData, $categoryMap, $index);
                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $this->errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import categories and return a mapping of name to id.
     *
     * @param  array<int, array<string, mixed>>  $categories
     * @return array<string, int>
     */
    private function importCategories(Novel $novel, array $categories): array
    {
        $categoryMap = [];

        // Get existing categories
        $existing = $novel->codexCategories()->pluck('id', 'name')->all();

        foreach ($categories as $catData) {
            if (! isset($catData['name'])) {
                continue;
            }

            $name = trim($catData['name']);
            if (isset($existing[$name])) {
                $categoryMap[$name] = $existing[$name];
            } else {
                $category = $novel->codexCategories()->create([
                    'name' => $name,
                    'color' => $catData['color'] ?? null,
                    'sort_order' => $novel->codexCategories()->max('sort_order') + 1,
                ]);
                $categoryMap[$name] = $category->id;
            }
        }

        return $categoryMap;
    }

    /**
     * Import a single entry.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, int>  $categoryMap
     */
    private function importEntry(Novel $novel, array $data, array $categoryMap, int $index): bool
    {
        // Validate required fields
        if (empty($data['name'])) {
            $this->errors[] = [
                'index' => $index,
                'error' => 'Missing required field: name',
            ];

            return false;
        }

        $validTypes = CodexEntry::getTypes();
        $type = $data['type'] ?? 'character';
        if (! in_array($type, $validTypes)) {
            $this->errors[] = [
                'index' => $index,
                'name' => $data['name'],
                'error' => "Invalid type: {$type}. Valid types are: ".implode(', ', $validTypes),
            ];

            return false;
        }

        // Check for duplicate name
        $existingEntry = $novel->codexEntries()
            ->where('name', $data['name'])
            ->first();

        if ($existingEntry) {
            $this->errors[] = [
                'index' => $index,
                'name' => $data['name'],
                'error' => 'Entry with this name already exists',
            ];

            return false;
        }

        // Create entry
        $entry = $novel->codexEntries()->create([
            'name' => trim($data['name']),
            'type' => $type,
            'description' => $data['description'] ?? null,
            'ai_context_mode' => $data['ai_context_mode'] ?? 'detected',
            'is_archived' => $data['is_archived'] ?? false,
            'sort_order' => $novel->codexEntries()->max('sort_order') + 1,
        ]);

        // Add aliases
        if (! empty($data['aliases']) && is_array($data['aliases'])) {
            foreach ($data['aliases'] as $alias) {
                if (! empty($alias) && $alias !== $data['name']) {
                    $entry->aliases()->create(['alias' => trim($alias)]);
                }
            }
        }

        // Add details
        if (! empty($data['details']) && is_array($data['details'])) {
            $sortOrder = 0;
            foreach ($data['details'] as $detail) {
                if (isset($detail['key']) && isset($detail['value'])) {
                    $entry->details()->create([
                        'key_name' => trim($detail['key']),
                        'value' => trim($detail['value']),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }

        // Assign categories
        if (! empty($data['categories']) && is_array($data['categories'])) {
            $categoryIds = [];
            foreach ($data['categories'] as $catName) {
                if (isset($categoryMap[$catName])) {
                    $categoryIds[] = $categoryMap[$catName];
                }
            }
            if (! empty($categoryIds)) {
                $entry->categories()->attach($categoryIds);
            }
        }

        return true;
    }

    /**
     * Preview import without committing changes.
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: array<int, array<string, mixed>>, invalid: array<int, array<string, mixed>>, categories: int}
     */
    public function previewImport(Novel $novel, array $data): array
    {
        $valid = [];
        $invalid = [];
        $entries = $data['entries'] ?? [];

        $existingNames = $novel->codexEntries()->pluck('name')->all();
        $validTypes = CodexEntry::getTypes();

        foreach ($entries as $index => $entry) {
            $errors = [];

            if (empty($entry['name'])) {
                $errors[] = 'Missing name';
            } elseif (in_array($entry['name'], $existingNames)) {
                $errors[] = 'Entry already exists';
            }

            $type = $entry['type'] ?? 'character';
            if (! in_array($type, $validTypes)) {
                $errors[] = "Invalid type: {$type}";
            }

            if (empty($errors)) {
                $valid[] = [
                    'index' => $index,
                    'name' => $entry['name'],
                    'type' => $type,
                    'aliases' => count($entry['aliases'] ?? []),
                    'details' => count($entry['details'] ?? []),
                ];
            } else {
                $invalid[] = [
                    'index' => $index,
                    'name' => $entry['name'] ?? 'Unknown',
                    'errors' => $errors,
                ];
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
            'categories' => count($data['categories'] ?? []),
        ];
    }
}
