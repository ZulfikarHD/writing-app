<?php

namespace App\Services\Codex;

use App\Models\Novel;
use Illuminate\Support\Collection;

class BulkExportService
{
    /**
     * Export all codex entries for a novel as JSON.
     *
     * @return array<string, mixed>
     */
    public function exportAsJson(Novel $novel): array
    {
        $entries = $novel->codexEntries()
            ->with(['aliases', 'details', 'categories'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return [
            'version' => '1.0',
            'exported_at' => now()->toISOString(),
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
            ],
            'entries' => $entries->map(fn ($entry) => $this->formatEntry($entry))->values()->all(),
            'categories' => $novel->codexCategories()->orderBy('sort_order')->get()->map(fn ($cat) => [
                'name' => $cat->name,
                'color' => $cat->color,
            ])->values()->all(),
        ];
    }

    /**
     * Export all codex entries for a novel as CSV.
     */
    public function exportAsCsv(Novel $novel): string
    {
        $entries = $novel->codexEntries()
            ->with(['aliases', 'details', 'categories'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $headers = [
            'Name',
            'Type',
            'Description',
            'AI Context Mode',
            'Aliases',
            'Details',
            'Categories',
            'Is Archived',
        ];

        $rows = $entries->map(function ($entry) {
            return [
                $entry->name,
                $entry->type,
                $entry->description ?? '',
                $entry->ai_context_mode,
                $entry->aliases->pluck('alias')->join(', '),
                $entry->details->map(fn ($d) => "{$d->key_name}: {$d->value}")->join('; '),
                $entry->categories->pluck('name')->join(', '),
                $entry->is_archived ? 'Yes' : 'No',
            ];
        });

        return $this->generateCsv($headers, $rows);
    }

    /**
     * Format a single entry for JSON export.
     *
     * @return array<string, mixed>
     */
    private function formatEntry($entry): array
    {
        return [
            'name' => $entry->name,
            'type' => $entry->type,
            'description' => $entry->description,
            'ai_context_mode' => $entry->ai_context_mode,
            'is_archived' => $entry->is_archived,
            'aliases' => $entry->aliases->pluck('alias')->values()->all(),
            'details' => $entry->details->map(fn ($d) => [
                'key' => $d->key_name,
                'value' => $d->value,
            ])->values()->all(),
            'categories' => $entry->categories->pluck('name')->values()->all(),
        ];
    }

    /**
     * Generate CSV content from headers and rows.
     *
     * @param  array<int, string>  $headers
     * @param  Collection<int, array<int, string>>  $rows
     */
    private function generateCsv(array $headers, Collection $rows): string
    {
        $output = fopen('php://temp', 'r+');

        // Write BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
