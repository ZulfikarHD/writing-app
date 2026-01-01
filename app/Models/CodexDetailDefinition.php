<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CodexDetailDefinition Model - Sprint 14 (US-12.5, US-12.7)
 *
 * Defines detail templates/presets that can be used across codex entries.
 * Supports different types: text, line, dropdown, codex_reference.
 *
 * @property int $id
 * @property int|null $novel_id
 * @property string $name
 * @property string $type
 * @property array|null $options
 * @property array|null $entry_types
 * @property bool $show_in_sidebar
 * @property string $ai_visibility
 * @property int $sort_order
 * @property bool $is_preset
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodexDetailDefinition extends Model
{
    use HasFactory;

    public const TYPE_TEXT = 'text';

    public const TYPE_LINE = 'line';

    public const TYPE_DROPDOWN = 'dropdown';

    public const TYPE_CODEX_REFERENCE = 'codex_reference';

    public const AI_VISIBILITY_ALWAYS = 'always';

    public const AI_VISIBILITY_NEVER = 'never';

    public const AI_VISIBILITY_NSFW_ONLY = 'nsfw_only';

    /**
     * System presets as defined by NovelCrafter documentation.
     *
     * @see https://www.novelcrafter.com/help/docs/codex/codex-details-quick-create
     */
    public const SYSTEM_PRESETS = [
        [
            'name' => 'Story Role',
            'type' => self::TYPE_DROPDOWN,
            'options' => ['Protagonist', 'Antagonist', 'Supporting', 'Minor'],
            'entry_types' => ['character'],
            'show_in_sidebar' => true,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Pronouns',
            'type' => self::TYPE_DROPDOWN,
            'options' => ['he/him', 'she/her', 'they/them', 'other'],
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Backstory',
            'type' => self::TYPE_TEXT,
            'options' => null,
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Occupation',
            'type' => self::TYPE_LINE,
            'options' => null,
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Physical Appearance',
            'type' => self::TYPE_TEXT,
            'options' => null,
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_NEVER, // Often over-mentioned by AI
        ],
        [
            'name' => 'Voice Sheet',
            'type' => self::TYPE_TEXT,
            'options' => null,
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Fighting Style',
            'type' => self::TYPE_TEXT,
            'options' => null,
            'entry_types' => ['character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_NSFW_ONLY,
        ],
        [
            'name' => 'Location Type',
            'type' => self::TYPE_DROPDOWN,
            'options' => ['City', 'Town', 'Village', 'Building', 'Landmark', 'Region', 'Country', 'World'],
            'entry_types' => ['location'],
            'show_in_sidebar' => true,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Climate',
            'type' => self::TYPE_LINE,
            'options' => null,
            'entry_types' => ['location'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Item Type',
            'type' => self::TYPE_DROPDOWN,
            'options' => ['Weapon', 'Armor', 'Tool', 'Artifact', 'Consumable', 'Clothing', 'Vehicle'],
            'entry_types' => ['item'],
            'show_in_sidebar' => true,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Powers/Abilities',
            'type' => self::TYPE_TEXT,
            'options' => null,
            'entry_types' => ['item', 'character'],
            'show_in_sidebar' => false,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
        [
            'name' => 'Organization Type',
            'type' => self::TYPE_DROPDOWN,
            'options' => ['Government', 'Military', 'Religious', 'Criminal', 'Commercial', 'Educational', 'Secret Society'],
            'entry_types' => ['organization'],
            'show_in_sidebar' => true,
            'ai_visibility' => self::AI_VISIBILITY_ALWAYS,
        ],
    ];

    protected $fillable = [
        'novel_id',
        'name',
        'type',
        'options',
        'entry_types',
        'show_in_sidebar',
        'ai_visibility',
        'sort_order',
        'is_preset',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'entry_types' => 'array',
            'show_in_sidebar' => 'boolean',
            'is_preset' => 'boolean',
            'sort_order' => 'integer',
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
     * @return HasMany<CodexDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(CodexDetail::class, 'definition_id');
    }

    /**
     * Get available detail types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_LINE,
            self::TYPE_DROPDOWN,
            self::TYPE_CODEX_REFERENCE,
        ];
    }

    /**
     * Get available AI visibility modes.
     *
     * @return array<string>
     */
    public static function getAiVisibilityModes(): array
    {
        return [
            self::AI_VISIBILITY_ALWAYS,
            self::AI_VISIBILITY_NEVER,
            self::AI_VISIBILITY_NSFW_ONLY,
        ];
    }

    /**
     * Check if this definition can be used for a given entry type.
     */
    public function canBeUsedFor(string $entryType): bool
    {
        if ($this->entry_types === null) {
            return true; // Available for all types
        }

        return in_array($entryType, $this->entry_types, true);
    }

    /**
     * Get options as array (for dropdown type).
     *
     * @return array<string>
     */
    public function getOptionsArray(): array
    {
        return $this->options ?? [];
    }

    /**
     * Scope to filter definitions by entry type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>
     */
    public function scopeForEntryType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->whereNull('entry_types')
                ->orWhereJsonContains('entry_types', $type);
        });
    }

    /**
     * Scope for system presets only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>
     */
    public function scopePresets($query)
    {
        return $query->where('is_preset', true);
    }

    /**
     * Scope for custom (user-created) definitions only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexDetailDefinition>
     */
    public function scopeCustom($query)
    {
        return $query->where('is_preset', false);
    }
}
