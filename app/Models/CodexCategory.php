<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * CodexCategory Model
 *
 * Sprint 16 (US-12.13): Added tag integration.
 * Categories can now be linked to tags or detail values.
 * Entries with the linked tag/detail automatically appear in the category.
 *
 * @property int $id
 * @property int $novel_id
 * @property string $name
 * @property string|null $color
 * @property int|null $parent_id
 * @property int $sort_order
 * @property int|null $linked_tag_id
 * @property int|null $linked_detail_definition_id
 * @property string|null $linked_detail_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodexCategory extends Model
{
    protected $fillable = [
        'novel_id',
        'name',
        'color',
        'parent_id',
        'sort_order',
        'linked_tag_id',
        'linked_detail_definition_id',
        'linked_detail_value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * @return BelongsTo<CodexCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CodexCategory::class, 'parent_id');
    }

    /**
     * @return HasMany<CodexCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(CodexCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<CodexEntry, $this>
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(CodexEntry::class, 'codex_entry_categories');
    }

    /**
     * Sprint 16 (US-12.13): Tag linked to this category.
     * Entries with this tag automatically appear in this category.
     *
     * @return BelongsTo<CodexTag, $this>
     */
    public function linkedTag(): BelongsTo
    {
        return $this->belongsTo(CodexTag::class, 'linked_tag_id');
    }

    /**
     * Sprint 16 (US-12.13): Detail definition linked to this category.
     * Used together with linked_detail_value to auto-link entries.
     *
     * @return BelongsTo<CodexDetailDefinition, $this>
     */
    public function linkedDetailDefinition(): BelongsTo
    {
        return $this->belongsTo(CodexDetailDefinition::class, 'linked_detail_definition_id');
    }

    /**
     * Sprint 16 (US-12.13): Check if this category has auto-linking enabled.
     */
    public function hasAutoLinking(): bool
    {
        return $this->linked_tag_id !== null
            || ($this->linked_detail_definition_id !== null && $this->linked_detail_value !== null);
    }

    /**
     * Sprint 16 (US-12.13): Get entries that auto-belong to this category.
     *
     * Based on NovelCrafter's category linking:
     * - If linked to a tag: entries with that tag appear in category
     * - If linked to a detail definition + value: entries with that detail value appear
     *
     * @return Collection<int, CodexEntry>
     */
    public function getAutoLinkedEntries(): Collection
    {
        if (! $this->hasAutoLinking()) {
            return collect();
        }

        $query = CodexEntry::where('novel_id', $this->novel_id)
            ->where('is_archived', false);

        // Link by tag
        if ($this->linked_tag_id) {
            $query->whereHas('tags', fn ($q) => $q->where('codex_tags.id', $this->linked_tag_id));
        }

        // Link by detail value (dropdown type)
        if ($this->linked_detail_definition_id && $this->linked_detail_value) {
            $query->whereHas('details', fn ($q) => $q->where('definition_id', $this->linked_detail_definition_id)
                ->where('value', $this->linked_detail_value)
            );
        }

        return $query->get();
    }

    /**
     * Sprint 16 (US-12.13): Get all entries for this category.
     * Combines manually assigned entries with auto-linked entries.
     *
     * @return Collection<int, CodexEntry>
     */
    public function getAllEntries(): Collection
    {
        $manualEntries = $this->entries;
        $autoEntries = $this->getAutoLinkedEntries();

        return $manualEntries->merge($autoEntries)->unique('id');
    }

    /**
     * Sprint 16 (US-12.13): Get the count of all entries (manual + auto).
     */
    public function getTotalEntryCount(): int
    {
        return $this->getAllEntries()->count();
    }
}
