<?php

namespace App\Services\Plan;

class OutlineParser
{
    /**
     * Parse an outline text into a structured format.
     *
     * @return array{acts: array<int, array{title: string, chapters: array<int, array{title: string, summary: string|null}>}>}
     */
    public function parse(string $outline): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $outline)));
        $result = ['acts' => []];
        $currentAct = null;
        $currentChapter = null;

        foreach ($lines as $line) {
            $parsed = $this->parseLine($line);

            if ($parsed['type'] === 'act') {
                if ($currentChapter !== null && $currentAct !== null) {
                    $result['acts'][count($result['acts']) - 1]['chapters'][] = $currentChapter;
                }
                if ($currentAct !== null && empty($result['acts'][count($result['acts']) - 1]['chapters'] ?? [])) {
                    // No chapters in act yet, this is fine
                }
                $currentAct = ['title' => $parsed['title'], 'chapters' => []];
                $result['acts'][] = $currentAct;
                $currentChapter = null;
            } elseif ($parsed['type'] === 'chapter') {
                if ($currentChapter !== null && ! empty($result['acts'])) {
                    $result['acts'][count($result['acts']) - 1]['chapters'][] = $currentChapter;
                }
                $currentChapter = ['title' => $parsed['title'], 'summary' => null];

                // If no act yet, create a default one
                if (empty($result['acts'])) {
                    $result['acts'][] = ['title' => 'Act 1', 'chapters' => []];
                }
            } elseif ($parsed['type'] === 'content' && $currentChapter !== null) {
                // Append to current chapter summary
                $currentChapter['summary'] = $currentChapter['summary']
                    ? $currentChapter['summary']."\n".$parsed['content']
                    : $parsed['content'];
            }
        }

        // Add the last chapter if exists
        if ($currentChapter !== null && ! empty($result['acts'])) {
            $result['acts'][count($result['acts']) - 1]['chapters'][] = $currentChapter;
        }

        return $result;
    }

    /**
     * Parse a single line to determine its type.
     *
     * @return array{type: string, title?: string, content?: string}
     */
    private function parseLine(string $line): array
    {
        // Check for Act patterns
        if (preg_match('/^Act\s*(\d+|[IVX]+)?\s*[-:]?\s*(.+)?$/i', $line, $matches)) {
            $title = ! empty($matches[2]) ? trim($matches[2]) : 'Act '.($matches[1] ?? '1');

            return ['type' => 'act', 'title' => $title];
        }

        // Check for numbered act patterns like "1. Introduction" or "# Introduction"
        if (preg_match('/^#+\s+(.+)$/', $line, $matches)) {
            // Markdown header - could be act
            return ['type' => 'act', 'title' => trim($matches[1])];
        }

        // Check for Chapter patterns
        if (preg_match('/^Chapter\s*(\d+|[IVX]+)?\s*[-:]?\s*(.+)?$/i', $line, $matches)) {
            $title = ! empty($matches[2]) ? trim($matches[2]) : 'Chapter '.($matches[1] ?? '1');

            return ['type' => 'chapter', 'title' => $title];
        }

        // Check for numbered chapter patterns
        if (preg_match('/^\d+[.)\s]+(.+)$/', $line, $matches)) {
            return ['type' => 'chapter', 'title' => trim($matches[1])];
        }

        // Check for bullet points (could be chapters or content)
        if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
            $content = trim($matches[1]);
            // If it looks like a title (short, no punctuation at end), treat as chapter
            if (strlen($content) < 50 && ! preg_match('/[.!?]$/', $content)) {
                return ['type' => 'chapter', 'title' => $content];
            }

            return ['type' => 'content', 'content' => $content];
        }

        // Check for indented content (summary)
        if (preg_match('/^\s{2,}(.+)$/', $line, $matches)) {
            return ['type' => 'content', 'content' => trim($matches[1])];
        }

        // Default: treat as content if we have context, otherwise as chapter
        if (strlen($line) > 100 || preg_match('/[.!?]$/', $line)) {
            return ['type' => 'content', 'content' => $line];
        }

        return ['type' => 'chapter', 'title' => $line];
    }

    /**
     * Validate parsed structure.
     *
     * @param  array{acts: array}  $structure
     */
    public function validate(array $structure): bool
    {
        if (empty($structure['acts'])) {
            return false;
        }

        foreach ($structure['acts'] as $act) {
            if (empty($act['title'])) {
                return false;
            }
        }

        return true;
    }
}
