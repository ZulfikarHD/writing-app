<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatThread extends Model
{
    /** @use HasFactory<\Database\Factories\ChatThreadFactory> */
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'user_id',
        'title',
        'model',
        'connection_id',
        'context_settings',
        'is_pinned',
        'linked_scene_id',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context_settings' => 'array',
            'is_pinned' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Novel, $this>
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<AIConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(AIConnection::class, 'connection_id');
    }

    /**
     * @return BelongsTo<Scene, $this>
     */
    public function linkedScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'linked_scene_id');
    }

    /**
     * @return HasMany<ChatMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'thread_id')->orderBy('created_at');
    }

    /**
     * @return HasMany<ChatContextItem, $this>
     */
    public function contextItems(): HasMany
    {
        return $this->hasMany(ChatContextItem::class, 'thread_id');
    }

    /**
     * Get active context items.
     *
     * @return HasMany<ChatContextItem, $this>
     */
    public function activeContextItems(): HasMany
    {
        return $this->contextItems()->where('is_active', true);
    }

    /**
     * Scope to exclude archived threads.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ChatThread>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ChatThread>
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope to get only archived threads.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ChatThread>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ChatThread>
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Get the latest message in this thread.
     */
    public function latestMessage(): ?ChatMessage
    {
        return $this->messages()->latest('created_at')->first();
    }

    /**
     * Generate a title from the first message if not set.
     */
    public function generateTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $firstMessage = $this->messages()->where('role', 'user')->first();

        if ($firstMessage) {
            return str($firstMessage->content)->limit(50)->toString();
        }

        return 'New Chat';
    }
}
