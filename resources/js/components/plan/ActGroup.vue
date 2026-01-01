<script setup lang="ts">
import { ref } from 'vue';
import { Motion, AnimatePresence } from 'motion-v';

interface Act {
    id: number;
    title: string;
    position: number;
}

const props = defineProps<{
    act: Act;
    chapterCount: number;
}>();

const emit = defineEmits<{
    (e: 'contextmenu', event: MouseEvent, act: Act): void;
    (e: 'rename', act: Act): void;
    (e: 'delete', act: Act): void;
    (e: 'addChapter', act: Act): void;
}>();

const isExpanded = ref(true);
const showActions = ref(false);

const handleContextMenu = (e: MouseEvent) => {
    e.preventDefault();
    emit('contextmenu', e, props.act);
};
</script>

<template>
    <div class="space-y-3">
        <!-- Act Header -->
        <div
            class="group flex cursor-pointer items-center gap-3 rounded-xl bg-gradient-to-r from-violet-50 to-violet-100/50 px-4 py-3.5 shadow-sm ring-1 ring-violet-100 transition-all hover:from-violet-100 hover:to-violet-50 hover:shadow dark:from-violet-900/30 dark:to-violet-900/10 dark:ring-violet-800/50 dark:hover:from-violet-900/40 dark:hover:to-violet-900/20"
            @click="isExpanded = !isExpanded"
            @contextmenu="handleContextMenu"
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
        >
            <!-- Expand/Collapse with animation -->
            <Motion
                :animate="{ rotate: isExpanded ? 90 : 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <svg
                    class="h-5 w-5 text-violet-500 dark:text-violet-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </Motion>

            <div class="flex flex-1 items-center gap-2">
                <span class="rounded-md bg-violet-200/60 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-violet-700 dark:bg-violet-800/50 dark:text-violet-300">
                    Act {{ act.position + 1 }}
                </span>
                <span class="font-semibold text-violet-900 dark:text-violet-100">{{ act.title }}</span>
            </div>

            <!-- Action Buttons -->
            <div
                :class="[
                    'flex items-center gap-1 transition-opacity',
                    showActions ? 'opacity-100' : 'opacity-0 group-hover:opacity-100',
                ]"
            >
                <!-- Add Chapter -->
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-violet-500 transition-all hover:bg-violet-200 hover:text-violet-700 active:scale-95 dark:text-violet-400 dark:hover:bg-violet-800 dark:hover:text-violet-300"
                    title="Add chapter to this act"
                    @click.stop="emit('addChapter', props.act)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>

                <!-- Rename -->
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-violet-500 transition-all hover:bg-violet-200 hover:text-violet-700 active:scale-95 dark:text-violet-400 dark:hover:bg-violet-800 dark:hover:text-violet-300"
                    title="Rename act"
                    @click.stop="emit('rename', props.act)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>

                <!-- Delete -->
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-violet-500 transition-all hover:bg-red-100 hover:text-red-600 active:scale-95 dark:text-violet-400 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                    title="Delete act"
                    @click.stop="emit('delete', props.act)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <span class="text-sm font-medium text-violet-500 dark:text-violet-400">
                {{ chapterCount }} {{ chapterCount === 1 ? 'chapter' : 'chapters' }}
            </span>
        </div>

        <!-- Act Content with animation -->
        <AnimatePresence mode="wait">
            <Motion
                v-if="isExpanded"
                :initial="{ opacity: 0, y: -8 }"
                :animate="{ opacity: 1, y: 0 }"
                :exit="{ opacity: 0, y: -8 }"
                :transition="{ type: 'spring', stiffness: 400, damping: 30 }"
                class="ml-4 space-y-4 border-l-2 border-violet-200 pl-4 dark:border-violet-800"
            >
                <slot />
            </Motion>
        </AnimatePresence>
    </div>
</template>
