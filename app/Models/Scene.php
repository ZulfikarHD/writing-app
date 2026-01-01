<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    /** @use HasFactory<\Database\Factories\SceneFactory> */
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'content',
        'summary',
        'position',
        'pov_character_id',
        'status',
        'word_count',
        'subtitle',
        'notes',
        'exclude_from_ai',
        'metadata',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'position' => 'integer',
            'word_count' => 'integer',
            'exclude_from_ai' => 'boolean',
            'metadata' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Chapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * @return HasMany<SceneRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(SceneRevision::class)->orderByDesc('created_at');
    }

    /**
     * @return BelongsToMany<SceneLabel, $this>
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(SceneLabel::class, 'scene_label');
    }

    /**
     * Get codex mentions for this scene.
     *
     * @return HasMany<CodexMention, $this>
     */
    public function codexMentions(): HasMany
    {
        return $this->hasMany(CodexMention::class);
    }

    /**
     * Calculate word count from content JSON.
     */
    public function calculateWordCount(): int
    {
        if (empty($this->content)) {
            return 0;
        }

        $text = $this->extractTextFromContent($this->content);

        return str_word_count($text);
    }

    /**
     * Extract plain text from TipTap JSON content recursively.
     *
     * @param  array<string, mixed>  $content
     */
    private function extractTextFromContent(array $content): string
    {
        $text = '';

        if (isset($content['text'])) {
            $text .= $content['text'].' ';
        }

        if (isset($content['content']) && is_array($content['content'])) {
            foreach ($content['content'] as $node) {
                $text .= $this->extractTextFromContent($node);
            }
        }

        return $text;
    }

    /**
     * Create a revision snapshot of the current content.
     */
    public function createRevision(): SceneRevision
    {
        return $this->revisions()->create([
            'content' => $this->content,
            'word_count' => $this->word_count,
        ]);
    }

    /**
     * Scope for non-archived scenes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Scene>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Scene>
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope for archived scenes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Scene>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Scene>
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
}
