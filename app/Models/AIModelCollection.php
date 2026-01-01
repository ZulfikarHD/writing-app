<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIModelCollection extends Model
{
    /** @use HasFactory<\Database\Factories\AIModelCollectionFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ai_model_collections';

    protected $fillable = [
        'user_id',
        'name',
        'models',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'models' => 'array',
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
}
