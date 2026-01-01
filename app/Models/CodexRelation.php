<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodexRelation extends Model
{
    protected $fillable = [
        'source_entry_id',
        'target_entry_id',
        'relation_type',
        'label',
        'is_bidirectional',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_bidirectional' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CodexEntry, $this>
     */
    public function sourceEntry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'source_entry_id');
    }

    /**
     * @return BelongsTo<CodexEntry, $this>
     */
    public function targetEntry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'target_entry_id');
    }

    /**
     * Get common relation types for characters.
     *
     * @return array<string, string>
     */
    public static function getCharacterRelationTypes(): array
    {
        return [
            'parent_of' => 'Parent of',
            'child_of' => 'Child of',
            'sibling_of' => 'Sibling of',
            'spouse_of' => 'Spouse of',
            'friend_of' => 'Friend of',
            'enemy_of' => 'Enemy of',
            'mentor_of' => 'Mentor of',
            'student_of' => 'Student of',
            'works_for' => 'Works for',
            'employs' => 'Employs',
        ];
    }

    /**
     * Get common relation types for locations.
     *
     * @return array<string, string>
     */
    public static function getLocationRelationTypes(): array
    {
        return [
            'located_in' => 'Located in',
            'contains' => 'Contains',
            'near' => 'Near',
            'connected_to' => 'Connected to',
        ];
    }

    /**
     * Get common relation types for items.
     *
     * @return array<string, string>
     */
    public static function getItemRelationTypes(): array
    {
        return [
            'owned_by' => 'Owned by',
            'owns' => 'Owns',
            'created_by' => 'Created by',
            'found_at' => 'Found at',
        ];
    }
}
