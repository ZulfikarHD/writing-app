<script setup lang="ts">
import { computed } from 'vue';
import { Motion, AnimatePresence } from 'motion-v';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    content: object | null;
    summary: string | null;
    status: string;
    word_count: number;
    subtitle: string | null;
    notes: string | null;
    pov_character_id: number | null;
    exclude_from_ai: boolean;
    labels?: Label[];
    codex_mentions_count?: number;
    codex_entries_count?: number;
    position?: number;
}

const props = defineProps<{
    scene: Scene | null;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'openCodexEntry', entryId: number): void;
}>();

// Performance mode
const { isReducedMotion, sidebarSpringConfig, backdropBlurClass, buttonTransitionClass, scaleActiveClass } = usePerformanceMode();

const statusLabels: Record<string, { label: string; color: string; bgColor: string }> = {
    draft: { label: 'Draft', color: 'text-zinc-600 dark:text-zinc-400', bgColor: 'bg-zinc-100 dark:bg-zinc-700' },
    in_progress: { label: 'In Progress', color: 'text-amber-600 dark:text-amber-400', bgColor: 'bg-amber-100 dark:bg-amber-900/30' },
    completed: { label: 'Completed', color: 'text-green-600 dark:text-green-400', bgColor: 'bg-green-100 dark:bg-green-900/30' },
    needs_revision: { label: 'Needs Revision', color: 'text-red-600 dark:text-red-400', bgColor: 'bg-red-100 dark:bg-red-900/30' },
};

const formattedWordCount = computed(() => {
    if (!props.scene) return '0';
    const wc = props.scene.word_count;
    if (wc >= 1000) {
        return `${(wc / 1000).toFixed(1)}k`;
    }
    return wc.toString();
});

const currentStatus = computed(() => {
    const status = props.scene?.status || 'draft';
    return statusLabels[status] || statusLabels.draft;
});
</script>

<template>
    <AnimatePresence>
        <Motion
            v-if="scene"
            :initial="isReducedMotion ? { x: '100%' } : { x: '100%', opacity: 0 }"
            :animate="isReducedMotion ? { x: 0 } : { x: 0, opacity: 1 }"
            :exit="isReducedMotion ? { x: '100%' } : { x: '100%', opacity: 0 }"
            :transition="sidebarSpringConfig"
            :class="['flex h-full w-72 flex-col border-l border-zinc-200 bg-zinc-50/80 dark:border-zinc-700 dark:bg-zinc-800/60', backdropBlurClass]"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Scene Details</h3>
                <button
                    type="button"
                    :class="['rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300', buttonTransitionClass, scaleActiveClass]"
                    @click="emit('close')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Scene Title -->
                <div class="mb-5">
                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ scene.title || 'Untitled Scene' }}
                    </h4>
                    <p v-if="scene.subtitle" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ scene.subtitle }}
                    </p>
                </div>

                <!-- Status & Word Count -->
                <div class="mb-5 flex items-center gap-3">
                    <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium', currentStatus.bgColor, currentStatus.color]">
                        <span class="h-1.5 w-1.5 rounded-full" :class="currentStatus.color.replace('text-', 'bg-')" />
                        {{ currentStatus.label }}
                    </span>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ formattedWordCount }} words
                    </span>
                </div>

                <!-- Labels -->
                <div v-if="scene.labels && scene.labels.length > 0" class="mb-5">
                    <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Labels
                    </h5>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="label in scene.labels"
                            :key="label.id"
                            class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium"
                            :style="{ backgroundColor: label.color + '20', color: label.color }"
                        >
                            <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: label.color }" />
                            {{ label.name }}
                        </span>
                    </div>
                </div>

                <!-- Summary -->
                <div v-if="scene.summary" class="mb-5">
                    <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Summary
                    </h5>
                    <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
                        {{ scene.summary }}
                    </p>
                </div>

                <!-- Notes -->
                <div v-if="scene.notes" class="mb-5">
                    <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Notes
                    </h5>
                    <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ scene.notes }}
                        </p>
                    </div>
                </div>

                <!-- Codex Mentions -->
                <div v-if="scene.codex_mentions_count && scene.codex_mentions_count > 0" class="mb-5">
                    <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Codex
                    </h5>
                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ scene.codex_mentions_count }} mentions</span>
                    </div>
                </div>

                <!-- AI Settings -->
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        AI Settings
                    </h5>
                    <div class="flex items-center gap-2 text-sm">
                        <span
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                scene.exclude_from_ai
                                    ? 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
                                    : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                            ]"
                        >
                            <svg v-if="scene.exclude_from_ai" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ scene.exclude_from_ai ? 'Excluded from AI' : 'Included in AI' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <button
                    type="button"
                    :class="['flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-700', buttonTransitionClass, scaleActiveClass]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Scene Details
                </button>
            </div>
        </Motion>
    </AnimatePresence>
</template>
