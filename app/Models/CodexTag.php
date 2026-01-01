<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * CodexTag Model - Sprint 14 (US-12.4)
 *
 * Tags provide quick organizational labels for codex entries,
 * separate from Categories (which are for AI grouping).
 *
 * Tags are NOT sent to AI - they're purely for organization.
 *
 * @property int $id
 * @property int $novel_id
 * @property string $name
 * @property string|null $color
 * @property string|null $entry_type
 * @property bool $is_predefined
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodexTag extends Model
{
    use HasFactory;

    /**
     * Predefined tag sets per entry type.
     */
    public const PREDEFINED_TAGS = [
        'character' => [
            ['name' => 'Protagonist', 'color' => '#8B5CF6'],
            ['name' => 'Antagonist', 'color' => '#EF4444'],
            ['name' => 'Supporting', 'color' => '#3B82F6'],
            ['name' => 'Minor', 'color' => '#6B7280'],
            ['name' => 'Mentioned', 'color' => '#9CA3AF'],
        ],
        'location' => [
            ['name' => 'Major', 'color' => '#10B981'],
            ['name' => 'Minor', 'color' => '#6B7280'],
            ['name' => 'Historical', 'color' => '#F59E0B'],
        ],
        'item' => [
            ['name' => 'Weapon', 'color' => '#EF4444'],
            ['name' => 'Artifact', 'color' => '#8B5CF6'],
            ['name' => 'Tool', 'color' => '#3B82F6'],
        ],
    ];

    protected $fillable = [
        'novel_id',
        'name',
        'color',
        'entry_type',
        'is_predefined',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_predefined' => 'boolean',
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
     * @return BelongsToMany<CodexEntry, $this>
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(CodexEntry::class, 'codex_entry_tags');
    }

    /**
     * Scope to filter tags by entry type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexTag>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexTag>
     */
    public function scopeForType($query, ?string $type)
    {
        if ($type === null) {
            return $query;
        }

        return $query->where(function ($q) use ($type) {
            $q->whereNull('entry_type')
                ->orWhere('entry_type', $type);
        });
    }

    /**
     * Scope for predefined tags only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexTag>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexTag>
     */
    public function scopePredefined($query)
    {
        return $query->where('is_predefined', true);
    }

    /**
     * Scope for custom (user-created) tags only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexTag>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexTag>
     */
    public function scopeCustom($query)
    {
        return $query->where('is_predefined', false);
    }
}
