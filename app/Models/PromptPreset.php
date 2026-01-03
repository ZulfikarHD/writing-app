<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromptPreset Model
 *
 * Represents saved prompt configurations with model settings and input values.
 * Presets are attached to specific prompts (parent-child relationship).
 *
 * @property int $id
 * @property int $user_id
 * @property int $prompt_id
 * @property string $name
 * @property string|null $model
 * @property float $temperature
 * @property int|null $max_tokens
 * @property float|null $top_p
 * @property float|null $frequency_penalty
 * @property float|null $presence_penalty
 * @property array|null $stop_sequences
 * @property array|null $input_values
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Prompt $prompt
 */
class PromptPreset extends Model
{
    /** @use HasFactory<\Database\Factories\PromptPresetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt_id',
        'name',
        'model',
        'temperature',
        'max_tokens',
        'top_p',
        'frequency_penalty',
        'presence_penalty',
        'stop_sequences',
        'input_values',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'max_tokens' => 'integer',
            'top_p' => 'float',
            'frequency_penalty' => 'float',
            'presence_penalty' => 'float',
            'stop_sequences' => 'array',
            'input_values' => 'array',
            'is_default' => 'boolean',
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
     * @return BelongsTo<Prompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Scope for default presets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPreset>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPreset>
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for presets of a specific prompt.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptPreset>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptPreset>
     */
    public function scopeForPrompt($query, int $promptId)
    {
        return $query->where('prompt_id', $promptId);
    }

    /**
     * Get model settings as an array suitable for AI requests.
     *
     * @return array<string, mixed>
     */
    public function getModelSettings(): array
    {
        $settings = [];

        if ($this->model !== null) {
            $settings['model'] = $this->model;
        }

        if ($this->temperature !== null) {
            $settings['temperature'] = $this->temperature;
        }

        if ($this->max_tokens !== null) {
            $settings['max_tokens'] = $this->max_tokens;
        }

        if ($this->top_p !== null) {
            $settings['top_p'] = $this->top_p;
        }

        if ($this->frequency_penalty !== null) {
            $settings['frequency_penalty'] = $this->frequency_penalty;
        }

        if ($this->presence_penalty !== null) {
            $settings['presence_penalty'] = $this->presence_penalty;
        }

        if ($this->stop_sequences !== null && count($this->stop_sequences) > 0) {
            $settings['stop'] = $this->stop_sequences;
        }

        return $settings;
    }

    /**
     * Set this preset as the default for its prompt.
     * Unsets any other default presets for the same prompt.
     */
    public function setAsDefault(): void
    {
        // Unset other defaults for this prompt
        static::where('prompt_id', $this->prompt_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
