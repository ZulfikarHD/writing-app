<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOnboardingState extends Model
{
    /** @use HasFactory<\Database\Factories\UserOnboardingStateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'welcome_completed',
        'first_novel_created',
        'editor_toured',
        'codex_introduced',
        'ai_chat_introduced',
        'onboarding_skipped',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'welcome_completed' => 'boolean',
            'first_novel_created' => 'boolean',
            'editor_toured' => 'boolean',
            'codex_introduced' => 'boolean',
            'ai_chat_introduced' => 'boolean',
            'onboarding_skipped' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return $this->onboarding_skipped || $this->completed_at !== null;
    }

    public function markWelcomeComplete(): void
    {
        $this->update(['welcome_completed' => true]);
        $this->checkCompletion();
    }

    public function markFirstNovelCreated(): void
    {
        $this->update(['first_novel_created' => true]);
        $this->checkCompletion();
    }

    public function skip(): void
    {
        $this->update([
            'onboarding_skipped' => true,
            'completed_at' => now(),
        ]);
    }

    protected function checkCompletion(): void
    {
        if ($this->welcome_completed && $this->first_novel_created) {
            $this->update(['completed_at' => now()]);
        }
    }
}
