<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromptComponent Model
 *
 * Represents a reusable component that can be inserted into prompts using [[name]] syntax.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $label
 * @property string $content
 * @property string|null $description
 * @property bool $is_system
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PromptComponent extends Model
{
    /** @use HasFactory<\Database\Factories\PromptComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'label',
        'content',
        'description',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
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
     * Scope for system components.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptComponent>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptComponent>
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for components accessible by a user (system + user's own).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptComponent>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptComponent>
     */
    public function scopeAccessibleBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_system', true)
                ->orWhere('user_id', $userId);
        });
    }

    /**
     * Check if this component can be deleted by a user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        if ($this->is_system) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Check if this component can be edited by a user.
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($this->is_system) {
            return false;
        }

        return $this->user_id === $user->id;
    }
}
