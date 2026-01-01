<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsageLog extends Model
{
    /** @use HasFactory<\Database\Factories\AIUsageLogFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ai_usage_logs';

    public const FEATURE_CHAT = 'chat';

    public const FEATURE_PROSE = 'prose';

    public const FEATURE_PROMPT = 'prompt';

    public const FEATURE_SUMMARIZE = 'summarize';

    public const FEATURE_OTHER = 'other';

    /**
     * Indicates if the model should be timestamped.
     * We only use created_at for usage logs.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'connection_id',
        'model',
        'input_tokens',
        'output_tokens',
        'estimated_cost',
        'feature_type',
        'metadata',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'estimated_cost' => 'decimal:6',
            'metadata' => 'array',
            'created_at' => 'datetime',
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
     * @return BelongsTo<AIConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(AIConnection::class, 'connection_id');
    }

    /**
     * Get total tokens for this log entry.
     */
    public function getTotalTokens(): int
    {
        return $this->input_tokens + $this->output_tokens;
    }
}
