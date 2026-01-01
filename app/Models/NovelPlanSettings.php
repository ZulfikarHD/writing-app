<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelPlanSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'user_id',
        'current_view',
        'matrix_mode',
        'grid_axis',
        'card_width',
        'card_height',
        'show_auto_references',
        'custom_matrix_entries',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_auto_references' => 'boolean',
            'custom_matrix_entries' => 'array',
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
     * Get or create plan settings for a novel and user.
     */
    public static function getOrCreate(int $novelId, int $userId): self
    {
        return self::firstOrCreate(
            ['novel_id' => $novelId, 'user_id' => $userId],
            [
                'current_view' => 'grid',
                'matrix_mode' => 'codex',
                'grid_axis' => 'vertical',
                'card_width' => 'medium',
                'card_height' => 'medium',
                'show_auto_references' => true,
            ]
        );
    }

    /**
     * Get default settings as array.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'current_view' => 'grid',
            'matrix_mode' => 'codex',
            'grid_axis' => 'vertical',
            'card_width' => 'medium',
            'card_height' => 'medium',
            'show_auto_references' => true,
            'custom_matrix_entries' => null,
        ];
    }
}
