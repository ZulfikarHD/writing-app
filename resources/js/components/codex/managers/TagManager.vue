<script setup lang="ts">
/**
 * TagManager.vue - Sprint 14 (US-12.4)
 *
 * Manages tag assignment for codex entries.
 * Tags are organizational labels NOT sent to AI.
 *
 * Features:
 * - Auto-save on tag add/remove (no submit button)
 * - Color-coded tag pills
 * - Quick create new tags inline
 * - Autocomplete from available tags
 */
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

interface Tag {
    id: number;
    name: string;
    color: string | null;
    is_predefined?: boolean;
}

const props = defineProps<{
    entryId: number;
    novelId: number;
    assignedTags: Tag[];
    availableTags: Tag[];
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

// Local state for optimistic updates
const localTags = ref<Tag[]>([...props.assignedTags]);
const allTags = ref<Tag[]>([...props.availableTags]);

// Watch for prop changes (from polling updates)
watch(() => props.assignedTags, (newTags) => {
    localTags.value = [...newTags];
}, { deep: true });

watch(() => props.availableTags, (newTags) => {
    allTags.value = [...newTags];
}, { deep: true });

// UI state
const isAdding = ref(false);
const searchQuery = ref('');
const newTagColor = ref('#8B5CF6');
const loading = ref(false);
const error = ref<string | null>(null);

// Filter tags that aren't already assigned
const unassignedTags = computed(() => {
    const assignedIds = new Set(localTags.value.map(t => t.id));
    return allTags.value.filter(t => !assignedIds.has(t.id));
});

// Filter by search query
const filteredTags = computed(() => {
    if (!searchQuery.value.trim()) {
        return unassignedTags.value;
    }
    const query = searchQuery.value.toLowerCase();
    return unassignedTags.value.filter(t =>
        t.name.toLowerCase().includes(query)
    );
});

// Check if we can create a new tag with current search
const canCreateTag = computed(() => {
    if (!searchQuery.value.trim()) return false;
    const query = searchQuery.value.toLowerCase();
    return !allTags.value.some(t => t.name.toLowerCase() === query);
});

// Auto-save: Add tag to entry
const addTag = async (tag: Tag) => {
    // Optimistic update
    localTags.value.push(tag);
    searchQuery.value = '';
    isAdding.value = false;

    try {
        await axios.post(`/api/codex/${props.entryId}/tags`, {
            tag_id: tag.id,
        });
        emit('updated');
    } catch (err: unknown) {
        // Rollback
        localTags.value = localTags.value.filter(t => t.id !== tag.id);
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add tag';
        setTimeout(() => error.value = null, 3000);
    }
};

// Auto-save: Remove tag from entry
const removeTag = async (tag: Tag) => {
    // Optimistic update
    const previousTags = [...localTags.value];
    localTags.value = localTags.value.filter(t => t.id !== tag.id);

    try {
        await axios.delete(`/api/codex/${props.entryId}/tags/${tag.id}`);
        emit('updated');
    } catch {
        // Rollback
        localTags.value = previousTags;
        error.value = 'Failed to remove tag';
        setTimeout(() => error.value = null, 3000);
    }
};

// Create a new tag and assign it
const createAndAssignTag = async () => {
    if (!searchQuery.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        // Create the tag
        const response = await axios.post(`/api/novels/${props.novelId}/codex/tags`, {
            name: searchQuery.value.trim(),
            color: newTagColor.value,
        });

        const newTag = response.data.tag;

        // Add to available tags
        allTags.value.push(newTag);

        // Assign to entry
        await addTag(newTag);

        searchQuery.value = '';
        newTagColor.value = '#8B5CF6';
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to create tag';
    } finally {
        loading.value = false;
    }
};

// Predefined colors for quick selection
const colorOptions = [
    '#8B5CF6', // Purple
    '#3B82F6', // Blue
    '#10B981', // Green
    '#F59E0B', // Amber
    '#EF4444', // Red
    '#EC4899', // Pink
    '#6B7280', // Gray
];

const getContrastColor = (hexColor: string | null): string => {
    if (!hexColor) return '#374151';
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#1F2937' : '#FFFFFF';
};
</script>

<template>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Tags</h3>
            <span class="text-xs text-zinc-400 dark:text-zinc-500">Not sent to AI</span>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400"
        >
            {{ error }}
        </div>

        <!-- Assigned Tags -->
        <div class="flex flex-wrap gap-2">
            <span
                v-for="tag in localTags"
                :key="tag.id"
                class="group inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium transition-all"
                :style="{
                    backgroundColor: tag.color || '#E5E7EB',
                    color: getContrastColor(tag.color),
                }"
            >
                {{ tag.name }}
                <button
                    type="button"
                    class="ml-0.5 rounded-full p-0.5 opacity-60 transition-opacity hover:opacity-100"
                    :style="{ color: getContrastColor(tag.color) }"
                    title="Remove tag"
                    @click="removeTag(tag)"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>

            <!-- Add Tag Button -->
            <button
                v-if="!isAdding"
                type="button"
                class="inline-flex items-center gap-1 rounded-full border border-dashed border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-500 transition-colors hover:border-violet-400 hover:text-violet-600 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-violet-500 dark:hover:text-violet-400"
                @click="isAdding = true"
            >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Tag
            </button>
        </div>

        <!-- Add Tag Dropdown -->
        <div v-if="isAdding" class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <Input
                v-model="searchQuery"
                placeholder="Search or create tag..."
                size="sm"
                class="mb-2"
                autofocus
                @keydown.escape="isAdding = false; searchQuery = ''"
                @keydown.enter="canCreateTag ? createAndAssignTag() : (filteredTags[0] && addTag(filteredTags[0]))"
            />

            <!-- Available Tags -->
            <div v-if="filteredTags.length > 0" class="mb-2 max-h-32 space-y-1 overflow-y-auto">
                <button
                    v-for="tag in filteredTags"
                    :key="tag.id"
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    @click="addTag(tag)"
                >
                    <span
                        class="h-3 w-3 rounded-full"
                        :style="{ backgroundColor: tag.color || '#E5E7EB' }"
                    />
                    <span class="text-zinc-700 dark:text-zinc-300">{{ tag.name }}</span>
                    <span v-if="tag.is_predefined" class="text-xs text-zinc-400">(preset)</span>
                </button>
            </div>

            <!-- Create New Tag -->
            <div v-if="canCreateTag" class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Create new tag:</span>
                    <span class="font-medium text-zinc-900 dark:text-white">"{{ searchQuery }}"</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex gap-1">
                        <button
                            v-for="color in colorOptions"
                            :key="color"
                            type="button"
                            class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110"
                            :class="newTagColor === color ? 'border-zinc-900 dark:border-white' : 'border-transparent'"
                            :style="{ backgroundColor: color }"
                            @click="newTagColor = color"
                        />
                    </div>
                    <Button
                        size="sm"
                        :loading="loading"
                        :disabled="loading"
                        @click="createAndAssignTag"
                    >
                        Create
                    </Button>
                </div>
            </div>

            <!-- No Results -->
            <p v-if="filteredTags.length === 0 && !canCreateTag" class="text-center text-sm text-zinc-400 dark:text-zinc-500">
                No matching tags
            </p>

            <!-- Cancel Button -->
            <div class="mt-2 flex justify-end">
                <Button
                    size="sm"
                    variant="ghost"
                    @click="isAdding = false; searchQuery = ''"
                >
                    Cancel
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <p v-if="localTags.length === 0 && !isAdding" class="mt-2 text-xs text-zinc-400 italic dark:text-zinc-500">
            No tags assigned. Tags help organize entries without affecting AI context.
        </p>
    </div>
</template>
