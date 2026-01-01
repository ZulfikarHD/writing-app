<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Novel, $this>
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class);
    }

    /**
     * @return HasMany<PenName, $this>
     */
    public function penNames(): HasMany
    {
        return $this->hasMany(PenName::class);
    }

    /**
     * @return HasOne<UserOnboardingState, $this>
     */
    public function onboardingState(): HasOne
    {
        return $this->hasOne(UserOnboardingState::class);
    }

    /**
     * @return HasMany<AIConnection, $this>
     */
    public function aiConnections(): HasMany
    {
        return $this->hasMany(AIConnection::class);
    }

    /**
     * @return HasMany<AIModelCollection, $this>
     */
    public function aiModelCollections(): HasMany
    {
        return $this->hasMany(AIModelCollection::class);
    }

    /**
     * @return HasMany<AIUsageLog, $this>
     */
    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(AIUsageLog::class);
    }

    /**
     * Get the user's default AI connection.
     */
    public function defaultAIConnection(): ?AIConnection
    {
        return $this->aiConnections()->where('is_default', true)->where('is_active', true)->first();
    }

    public function defaultPenName(): ?PenName
    {
        return $this->penNames()->where('is_default', true)->first();
    }

    public function getOrCreateOnboardingState(): UserOnboardingState
    {
        return $this->onboardingState ?? $this->onboardingState()->create();
    }
}
