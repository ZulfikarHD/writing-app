<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenName extends Model
{
    /** @use HasFactory<\Database\Factories\PenNameFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'bio',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * @return HasMany<Novel, $this>
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class);
    }
}
