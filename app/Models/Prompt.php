<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prompt Model
 *
 * Represents an AI prompt template for various writing tasks.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $folder_id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string|null $system_message
 * @property string|null $user_message
 * @property array|null $model_settings
 * @property bool $is_system
 * @property bool $is_active
 * @property int $sort_order
 * @property int $usage_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Prompt extends Model
{
    /** @use HasFactory<\Database\Factories\PromptFactory> */
    use HasFactory;

    public const TYPE_CHAT = 'chat';

    public const TYPE_PROSE = 'prose';

    public const TYPE_REPLACEMENT = 'replacement';

    public const TYPE_SUMMARY = 'summary';

    protected $fillable = [
        'user_id',
        'folder_id',
        'name',
        'description',
        'type',
        'system_message',
        'user_message',
        'model_settings',
        'is_system',
        'is_active',
        'sort_order',
        'usage_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'model_settings' => 'array',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'usage_count' => 'integer',
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
     * @return BelongsTo<PromptFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(PromptFolder::class);
    }

    /**
     * Scope for active prompts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Prompt>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Prompt>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for system prompts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Prompt>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Prompt>
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user prompts (non-system).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Prompt>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Prompt>
     */
    public function scopeUserOwned($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope for a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Prompt>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Prompt>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for prompts accessible by a user (system + user's own).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Prompt>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Prompt>
     */
    public function scopeAccessibleBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_system', true)
                ->orWhere('user_id', $userId);
        });
    }

    /**
     * Get available prompt types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CHAT,
            self::TYPE_PROSE,
            self::TYPE_REPLACEMENT,
            self::TYPE_SUMMARY,
        ];
    }

    /**
     * Get type labels for display.
     *
     * @return array<string, string>
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_CHAT => 'Workshop Chat',
            self::TYPE_PROSE => 'Scene Beat Completion',
            self::TYPE_REPLACEMENT => 'Text Replacement',
            self::TYPE_SUMMARY => 'Scene Summarization',
        ];
    }

    /**
     * Check if this prompt can be deleted by a user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        if ($this->is_system) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Check if this prompt can be edited by a user.
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($this->is_system) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
