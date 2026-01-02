<?php

namespace App\Services\Chat;

use App\Models\Chapter;
use App\Models\Scene;

class TransferService
{
    /**
     * Transfer content to an existing scene.
     *
     * @param  Scene  $scene  The target scene
     * @param  string  $content  The markdown content to transfer
     * @param  string  $position  Where to insert: 'end' or 'cursor'
     * @return array{scene: Scene}
     */
    public function transferToScene(Scene $scene, string $content, string $position = 'end'): array
    {
        $tiptapContent = $this->convertMarkdownToTipTap($content);

        // Get current scene content
        $currentContent = $scene->content ?? [
            'type' => 'doc',
            'content' => [],
        ];

        // Merge content based on position
        if ($position === 'end') {
            // Append to end
            $currentContent['content'] = array_merge(
                $currentContent['content'] ?? [],
                $tiptapContent['content'] ?? []
            );
        } else {
            // For cursor position, we'll append to end as fallback
            // The frontend will handle actual cursor insertion via editor API
            $currentContent['content'] = array_merge(
                $currentContent['content'] ?? [],
                $tiptapContent['content'] ?? []
            );
        }

        // Calculate word count
        $scene->fill(['content' => $currentContent]);
        $wordCount = $scene->calculateWordCount();

        // Save the scene
        $scene->update([
            'content' => $currentContent,
            'word_count' => $wordCount,
        ]);

        // Update novel's last edited timestamp
        $scene->chapter->novel->update(['last_edited_at' => now()]);

        return [
            'scene' => $scene->fresh(),
        ];
    }

    /**
     * Create a new scene with the transferred content.
     *
     * @param  Chapter  $chapter  The chapter to add the scene to
     * @param  string  $content  The markdown content to transfer
     * @param  string  $title  The title for the new scene
     * @return array{scene: Scene}
     */
    public function transferToNewScene(Chapter $chapter, string $content, string $title): array
    {
        $tiptapContent = $this->convertMarkdownToTipTap($content);

        // Get max position in chapter
        $maxPosition = $chapter->scenes()->max('position') ?? 0;

        // Create the new scene
        $scene = $chapter->scenes()->create([
            'title' => $title,
            'content' => $tiptapContent,
            'position' => $maxPosition + 1,
            'status' => 'draft',
        ]);

        // Calculate word count
        $scene->fill(['content' => $tiptapContent]);
        $wordCount = $scene->calculateWordCount();
        $scene->update(['word_count' => $wordCount]);

        // Update novel's last edited timestamp
        $chapter->novel->update(['last_edited_at' => now()]);

        return [
            'scene' => $scene->fresh(),
        ];
    }

    /**
     * Convert markdown content to TipTap/ProseMirror JSON format.
     *
     * @param  string  $markdown  The markdown content
     * @return array The TipTap document structure
     */
    public function convertMarkdownToTipTap(string $markdown): array
    {
        $doc = [
            'type' => 'doc',
            'content' => [],
        ];

        // Split by double newlines to get paragraphs/blocks
        $blocks = preg_split('/\n{2,}/', trim($markdown));

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            // Check for headings
            if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $matches)) {
                $level = strlen($matches[1]);
                $text = trim($matches[2]);
                $doc['content'][] = [
                    'type' => 'heading',
                    'attrs' => ['level' => $level],
                    'content' => $this->parseInlineContent($text),
                ];

                continue;
            }

            // Check for horizontal rule
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $block)) {
                $doc['content'][] = ['type' => 'horizontalRule'];

                continue;
            }

            // Check for unordered list
            if (preg_match('/^[\-\*]\s+/', $block)) {
                $listItems = preg_split('/\n[\-\*]\s+/', $block);
                $doc['content'][] = [
                    'type' => 'bulletList',
                    'content' => array_map(function ($item) {
                        $item = preg_replace('/^[\-\*]\s+/', '', $item);

                        return [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => $this->parseInlineContent(trim($item)),
                                ],
                            ],
                        ];
                    }, $listItems),
                ];

                continue;
            }

            // Check for ordered list
            if (preg_match('/^\d+\.\s+/', $block)) {
                $listItems = preg_split('/\n\d+\.\s+/', $block);
                $doc['content'][] = [
                    'type' => 'orderedList',
                    'content' => array_map(function ($item) {
                        $item = preg_replace('/^\d+\.\s+/', '', $item);

                        return [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => $this->parseInlineContent(trim($item)),
                                ],
                            ],
                        ];
                    }, $listItems),
                ];

                continue;
            }

            // Check for blockquote
            if (preg_match('/^>\s+/', $block)) {
                $quoteContent = preg_replace('/^>\s*/m', '', $block);
                $doc['content'][] = [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => $this->parseInlineContent(trim($quoteContent)),
                        ],
                    ],
                ];

                continue;
            }

            // Check for code block
            if (preg_match('/^```(\w*)\n([\s\S]*?)```$/m', $block, $matches)) {
                $doc['content'][] = [
                    'type' => 'codeBlock',
                    'attrs' => ['language' => $matches[1] ?: null],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $matches[2],
                        ],
                    ],
                ];

                continue;
            }

            // Default: paragraph with possible line breaks
            $lines = explode("\n", $block);
            $paragraphContent = [];

            foreach ($lines as $index => $line) {
                $inlineContent = $this->parseInlineContent($line);
                $paragraphContent = array_merge($paragraphContent, $inlineContent);

                // Add hard break between lines (not after the last line)
                if ($index < count($lines) - 1) {
                    $paragraphContent[] = ['type' => 'hardBreak'];
                }
            }

            if (! empty($paragraphContent)) {
                $doc['content'][] = [
                    'type' => 'paragraph',
                    'content' => $paragraphContent,
                ];
            }
        }

        // Ensure at least one paragraph if empty
        if (empty($doc['content'])) {
            $doc['content'][] = [
                'type' => 'paragraph',
                'content' => [],
            ];
        }

        return $doc;
    }

    /**
     * Parse inline markdown content (bold, italic, links, code).
     *
     * @param  string  $text  The text to parse
     * @return array Array of text nodes with marks
     */
    protected function parseInlineContent(string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $result = [];

        // Pattern to match inline formatting
        // Order matters: bold+italic first, then bold, then italic, then code, then links
        $pattern = '/(\*\*\*(.+?)\*\*\*|\*\*(.+?)\*\*|\*(.+?)\*|`([^`]+)`|\[([^\]]+)\]\(([^)]+)\))/';

        $lastOffset = 0;
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $index => $match) {
            $fullMatch = $match[0];
            $offset = $match[1];

            // Add text before this match
            if ($offset > $lastOffset) {
                $beforeText = substr($text, $lastOffset, $offset - $lastOffset);
                if (! empty($beforeText)) {
                    $result[] = ['type' => 'text', 'text' => $beforeText];
                }
            }

            // Determine which pattern matched
            if (! empty($matches[2][$index][0])) {
                // Bold + Italic (***text***)
                $result[] = [
                    'type' => 'text',
                    'marks' => [['type' => 'bold'], ['type' => 'italic']],
                    'text' => $matches[2][$index][0],
                ];
            } elseif (! empty($matches[3][$index][0])) {
                // Bold (**text**)
                $result[] = [
                    'type' => 'text',
                    'marks' => [['type' => 'bold']],
                    'text' => $matches[3][$index][0],
                ];
            } elseif (! empty($matches[4][$index][0])) {
                // Italic (*text*)
                $result[] = [
                    'type' => 'text',
                    'marks' => [['type' => 'italic']],
                    'text' => $matches[4][$index][0],
                ];
            } elseif (! empty($matches[5][$index][0])) {
                // Inline code (`code`)
                $result[] = [
                    'type' => 'text',
                    'marks' => [['type' => 'code']],
                    'text' => $matches[5][$index][0],
                ];
            } elseif (! empty($matches[6][$index][0])) {
                // Link [text](url)
                $result[] = [
                    'type' => 'text',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => $matches[7][$index][0] ?? '',
                                'target' => '_blank',
                            ],
                        ],
                    ],
                    'text' => $matches[6][$index][0],
                ];
            }

            $lastOffset = $offset + strlen($fullMatch);
        }

        // Add remaining text after last match
        if ($lastOffset < strlen($text)) {
            $remainingText = substr($text, $lastOffset);
            if (! empty($remainingText)) {
                $result[] = ['type' => 'text', 'text' => $remainingText];
            }
        }

        // If no patterns matched, return the whole text
        if (empty($result) && ! empty($text)) {
            $result[] = ['type' => 'text', 'text' => $text];
        }

        return $result;
    }
}
