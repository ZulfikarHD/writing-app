<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromptInput Model
 *
 * Represents a dynamic input field for a prompt that users fill before execution.
 *
 * @property int $id
 * @property int $prompt_id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property array|null $options
 * @property string|null $default_value
 * @property string|null $placeholder
 * @property string|null $description
 * @property bool $is_required
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PromptInput extends Model
{
    /** @use HasFactory<\Database\Factories\PromptInputFactory> */
    use HasFactory;

    public const TYPE_TEXT = 'text';

    public const TYPE_TEXTAREA = 'textarea';

    public const TYPE_SELECT = 'select';

    public const TYPE_NUMBER = 'number';

    public const TYPE_CHECKBOX = 'checkbox';

    protected $fillable = [
        'prompt_id',
        'name',
        'label',
        'type',
        'options',
        'default_value',
        'placeholder',
        'description',
        'is_required',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Prompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Get available input types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_TEXTAREA,
            self::TYPE_SELECT,
            self::TYPE_NUMBER,
            self::TYPE_CHECKBOX,
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
            self::TYPE_TEXT => 'Text Input',
            self::TYPE_TEXTAREA => 'Text Area',
            self::TYPE_SELECT => 'Dropdown Select',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_CHECKBOX => 'Checkbox',
        ];
    }
}
