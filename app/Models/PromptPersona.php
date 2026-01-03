<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromptPersona Model
 *
 * Represents a persona that shares instructions across prompts and projects.
 * Comparable to "memory" features in AI chat apps.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $system_message
 * @property array|null $interaction_types ['chat', 'prose', 'replacement', 'summary']
 * @property array|null $project_ids null = all projects
 * @property bool $is_default
 * @property bool $is_archived
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class PromptPersona extends Model
{
    /** @use HasFactory<\Database\Factories\PromptPersonaFactory> */
    use HasFactory;

    public const TYPE_CHAT = 'chat';

    public const TYPE_PROSE = 'prose';

    public const TYPE_REPLACEMENT = 'replacement';

    public const TYPE_SUMMARY = 'summary';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'system_message',
        'interaction_types',
        'project_ids',
        'is_default',
        'is_archived',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interaction_types' => 'array',
            'project_ids' => 'array',
            'is_default' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active (non-archived) personas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPersona>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPersona>
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for archived personas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPersona>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPersona>
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope for personas matching an interaction type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPersona>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPersona>
     */
    public function scopeForInteractionType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->whereNull('interaction_types')
                ->orWhereJsonContains('interaction_types', $type);
        });
    }

    /**
     * Scope for personas applicable to a project.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPersona>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPersona>
     */
    public function scopeForProject($query, ?int $projectId)
    {
        return $query->where(function ($q) use ($projectId) {
            $q->whereNull('project_ids'); // null = all projects
            if ($projectId) {
                $q->orWhereJsonContains('project_ids', $projectId);
            }
        });
    }

    /**
     * Check if this persona applies to a specific interaction type.
     */
    public function appliesTo(string $type): bool
    {
        if ($this->interaction_types === null) {
            return true; // null means all types
        }

        return in_array($type, $this->interaction_types, true);
    }

    /**
     * Check if this persona applies to a specific project.
     */
    public function appliesToProject(?int $projectId): bool
    {
        if ($this->project_ids === null) {
            return true; // null means all projects
        }

        if ($projectId === null) {
            return true; // If no project specified, apply anyway
        }

        return in_array($projectId, $this->project_ids, true);
    }

    /**
     * Get available interaction types.
     *
     * @return array<string>
     */
    public static function getInteractionTypes(): array
    {
        return [
            self::TYPE_CHAT,
            self::TYPE_PROSE,
            self::TYPE_REPLACEMENT,
            self::TYPE_SUMMARY,
        ];
    }

    /**
     * Get interaction type labels for display.
     *
     * @return array<string, string>
     */
    public static function getInteractionTypeLabels(): array
    {
        return [
            self::TYPE_CHAT => 'Workshop Chat',
            self::TYPE_PROSE => 'Scene Beat Completion',
            self::TYPE_REPLACEMENT => 'Text Replacement',
            self::TYPE_SUMMARY => 'Scene Summarization',
        ];
    }
}
