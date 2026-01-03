<?php

namespace App\Services\Prompts;

use App\Models\Prompt;
use App\Models\PromptFolder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PromptService
{
    /**
     * Get all prompts accessible by a user (system + user's own).
     *
     * @return Collection<int, Prompt>
     */
    public function getAccessiblePrompts(User $user, ?string $type = null, ?string $search = null): Collection
    {
        $query = Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->orderBy('is_system', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($type) {
            $query->ofType($type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get prompts with pagination.
     */
    public function getPaginatedPrompts(
        User $user,
        int $perPage = 20,
        ?string $type = null,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->orderBy('is_system', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($type) {
            $query->ofType($type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get prompts by type for a user.
     *
     * @return Collection<int, Prompt>
     */
    public function getPromptsByType(User $user, string $type): Collection
    {
        return Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->ofType($type)
            ->orderBy('is_system', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get system prompts only.
     *
     * @return Collection<int, Prompt>
     */
    public function getSystemPrompts(?string $type = null): Collection
    {
        $query = Prompt::query()->system()->active();

        if ($type) {
            $query->ofType($type);
        }

        return $query->orderBy('sort_order')->get();
    }

    /**
     * Get user's own prompts only.
     *
     * @return Collection<int, Prompt>
     */
    public function getUserPrompts(User $user, ?string $type = null): Collection
    {
        $query = Prompt::query()
            ->where('user_id', $user->id)
            ->active();

        if ($type) {
            $query->ofType($type);
        }

        return $query->orderBy('sort_order')->get();
    }

    /**
     * Get a single prompt by ID.
     */
    public function getPrompt(int $id, User $user): ?Prompt
    {
        return Prompt::query()
            ->accessibleBy($user->id)
            ->find($id);
    }

    /**
     * Create a new prompt.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPrompt(User $user, array $data): Prompt
    {
        $data['user_id'] = $user->id;
        $data['is_system'] = false;

        // Set sort order if not provided
        if (! isset($data['sort_order'])) {
            $maxSortOrder = Prompt::query()
                ->where('user_id', $user->id)
                ->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSortOrder + 1;
        }

        return Prompt::create($data);
    }

    /**
     * Update an existing prompt.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePrompt(Prompt $prompt, User $user, array $data): Prompt
    {
        if (! $prompt->canBeEditedBy($user)) {
            throw new \InvalidArgumentException('You cannot edit this prompt.');
        }

        // Prevent changing system status
        unset($data['is_system'], $data['user_id']);

        $prompt->update($data);

        return $prompt->fresh();
    }

    /**
     * Delete a prompt.
     */
    public function deletePrompt(Prompt $prompt, User $user): bool
    {
        if (! $prompt->canBeDeletedBy($user)) {
            throw new \InvalidArgumentException('You cannot delete this prompt.');
        }

        return $prompt->delete();
    }

    /**
     * Clone a prompt for a user.
     */
    public function clonePrompt(Prompt $prompt, User $user, ?string $newName = null): Prompt
    {
        $cloneData = $prompt->only([
            'name',
            'description',
            'type',
            'system_message',
            'user_message',
            'model_settings',
        ]);

        $cloneData['name'] = $newName ?? $prompt->name.' (Copy)';
        $cloneData['user_id'] = $user->id;
        $cloneData['is_system'] = false;
        $cloneData['usage_count'] = 0;

        // Set sort order
        $maxSortOrder = Prompt::query()
            ->where('user_id', $user->id)
            ->max('sort_order') ?? 0;
        $cloneData['sort_order'] = $maxSortOrder + 1;

        return Prompt::create($cloneData);
    }

    /**
     * Record prompt usage.
     */
    public function recordUsage(Prompt $prompt): void
    {
        $prompt->incrementUsage();
    }

    /**
     * Reorder prompts.
     *
     * @param  array<int, int>  $orderMap  Array of [prompt_id => sort_order]
     */
    public function reorderPrompts(User $user, array $orderMap): void
    {
        DB::transaction(function () use ($user, $orderMap) {
            foreach ($orderMap as $promptId => $sortOrder) {
                Prompt::query()
                    ->where('id', $promptId)
                    ->where('user_id', $user->id)
                    ->update(['sort_order' => $sortOrder]);
            }
        });
    }

    /**
     * Get all folders for a user.
     *
     * @return Collection<int, PromptFolder>
     */
    public function getFolders(User $user): Collection
    {
        return PromptFolder::query()
            ->where('user_id', $user->id)
            ->with('children', 'prompts')
            ->root()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a folder.
     *
     * @param  array<string, mixed>  $data
     */
    public function createFolder(User $user, array $data): PromptFolder
    {
        $data['user_id'] = $user->id;

        if (! isset($data['sort_order'])) {
            $maxSortOrder = PromptFolder::query()
                ->where('user_id', $user->id)
                ->whereNull('parent_id')
                ->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSortOrder + 1;
        }

        return PromptFolder::create($data);
    }

    /**
     * Update a folder.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateFolder(PromptFolder $folder, array $data): PromptFolder
    {
        $folder->update($data);

        return $folder->fresh();
    }

    /**
     * Delete a folder (prompts inside will have folder_id set to null).
     */
    public function deleteFolder(PromptFolder $folder, User $user): bool
    {
        if ($folder->user_id !== $user->id) {
            throw new \InvalidArgumentException('You cannot delete this folder.');
        }

        return $folder->delete();
    }

    /**
     * Move a prompt to a folder.
     */
    public function movePromptToFolder(Prompt $prompt, ?int $folderId, User $user): Prompt
    {
        if (! $prompt->canBeEditedBy($user)) {
            throw new \InvalidArgumentException('You cannot move this prompt.');
        }

        if ($folderId) {
            $folder = PromptFolder::where('id', $folderId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $prompt->update(['folder_id' => $folderId]);

        return $prompt->fresh();
    }

    /**
     * Get prompt types with labels.
     *
     * @return array<string, string>
     */
    public function getPromptTypes(): array
    {
        return Prompt::getTypeLabels();
    }

    /**
     * Get prompt statistics for a user.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(User $user): array
    {
        $userPrompts = Prompt::where('user_id', $user->id)->count();
        $systemPrompts = Prompt::system()->count();

        $byType = Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $totalUsage = Prompt::query()
            ->accessibleBy($user->id)
            ->sum('usage_count');

        return [
            'user_prompts' => $userPrompts,
            'system_prompts' => $systemPrompts,
            'total' => $userPrompts + $systemPrompts,
            'by_type' => $byType,
            'total_usage' => $totalUsage,
        ];
    }
}
