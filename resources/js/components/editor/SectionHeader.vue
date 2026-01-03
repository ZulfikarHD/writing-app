<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { SECTION_TYPES } from '@/extensions/SectionNode';

interface Props {
    type: 'content' | 'note' | 'alternative' | 'beat';
    title: string | null;
    isCollapsed: boolean;
    excludeFromAi: boolean;
    color: string;
    wordCount: number;
    isEditing: boolean;
    editedTitle: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'toggle-collapse'): void;
    (e: 'toggle-ai-visibility'): void;
    (e: 'start-title-edit'): void;
    (e: 'save-title-edit'): void;
    (e: 'cancel-title-edit'): void;
    (e: 'update:edited-title', value: string): void;
    (e: 'open-menu'): void;
}>();

const titleInputRef = ref<HTMLInputElement | null>(null);

const typeLabel = computed(() => SECTION_TYPES[props.type] || 'Section');

const handleStartEdit = async () => {
    emit('start-title-edit');
    await nextTick();
    titleInputRef.value?.focus();
    titleInputRef.value?.select();
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
        emit('save-title-edit');
    } else if (e.key === 'Escape') {
        emit('cancel-title-edit');
    }
};
</script>

<template>
    <div
        class="section-header flex items-center gap-2 px-3 py-2 border-b border-zinc-200 dark:border-zinc-700"
        :style="{ borderBottomColor: `${color}20` }"
    >
        <!-- Collapse Toggle -->
        <button
            type="button"
            class="flex-shrink-0 p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors"
            :title="isCollapsed ? 'Expand section' : 'Collapse section'"
            @click="emit('toggle-collapse')"
        >
            <svg
                class="w-4 h-4 text-zinc-500 dark:text-zinc-400 transition-transform"
                :class="{ '-rotate-90': isCollapsed }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Type Badge -->
        <span
            class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded"
            :style="{
                backgroundColor: `${color}20`,
                color: color,
            }"
        >
            {{ typeLabel }}
        </span>

        <!-- Title -->
        <div class="flex-1 min-w-0">
            <input
                v-if="isEditing"
                ref="titleInputRef"
                type="text"
                class="w-full px-2 py-0.5 text-sm bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                :value="editedTitle"
                placeholder="Section title..."
                @input="emit('update:edited-title', ($event.target as HTMLInputElement).value)"
                @keydown="handleKeydown"
                @blur="emit('save-title-edit')"
            />
            <button
                v-else
                type="button"
                class="text-sm text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white truncate block w-full text-left"
                @click="handleStartEdit"
            >
                {{ title || 'Untitled section' }}
            </button>
        </div>

        <!-- Word Count (visible on hover) -->
        <span
            class="flex-shrink-0 text-xs text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity"
        >
            {{ wordCount }} {{ wordCount === 1 ? 'word' : 'words' }}
        </span>

        <!-- AI Visibility Toggle -->
        <button
            type="button"
            class="flex-shrink-0 p-1 rounded transition-colors"
            :class="excludeFromAi
                ? 'text-zinc-400 dark:text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                : 'text-indigo-500 hover:bg-indigo-100 dark:hover:bg-indigo-900/30'"
            :title="excludeFromAi ? 'Hidden from AI' : 'Visible to AI'"
            @click="emit('toggle-ai-visibility')"
        >
            <svg
                v-if="!excludeFromAi"
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg
                v-else
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>

        <!-- Drag Handle (for reordering) -->
        <div
            class="flex-shrink-0 p-1 cursor-grab text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity"
            data-drag-handle
            title="Drag to reorder"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
            </svg>
        </div>

        <!-- Menu Button -->
        <button
            type="button"
            class="flex-shrink-0 p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors opacity-0 group-hover:opacity-100"
            title="Section options"
            @click="emit('open-menu')"
        >
            <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.section-header {
    user-select: none;
}
</style>
