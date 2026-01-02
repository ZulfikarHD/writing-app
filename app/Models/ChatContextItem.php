<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatContextItem extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'thread_id',
        'context_type',
        'reference_id',
        'custom_content',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ChatThread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }

    /**
     * Get the referenced scene if context_type is 'scene'.
     */
    public function getSceneAttribute(): ?Scene
    {
        if ($this->context_type === 'scene' && $this->reference_id) {
            return Scene::find($this->reference_id);
        }

        return null;
    }

    /**
     * Get the referenced codex entry if context_type is 'codex'.
     */
    public function getCodexEntryAttribute(): ?CodexEntry
    {
        if ($this->context_type === 'codex' && $this->reference_id) {
            return CodexEntry::find($this->reference_id);
        }

        return null;
    }

    /**
     * Get the content for this context item.
     */
    public function getContentAttribute(): ?string
    {
        if ($this->custom_content) {
            return $this->custom_content;
        }

        return match ($this->context_type) {
            'scene' => $this->scene?->content ? strip_tags(json_encode($this->scene->content)) : null,
            'codex' => $this->codexEntry ? "{$this->codexEntry->name}: {$this->codexEntry->description}" : null,
            default => null,
        };
    }

    /**
     * Scope to get only active context items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ChatContextItem>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ChatContextItem>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
