<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Scene {
    id: number;
    title: string | null;
    chapter: { id: number; title: string } | null;
}

interface Detail {
    id: number;
    key_name: string;
}

interface Progression {
    id: number;
    story_timestamp: string | null;
    note: string;
    new_value: string | null;
    mode: 'addition' | 'replace';
    scene: Scene | null;
    detail: Detail | null;
}

const props = defineProps<{
    progressions: Progression[];
    novelId: number;
}>();

// State for expanded progression
const expandedId = ref<number | null>(null);

// Sort progressions by scene position if available, otherwise by id
const sortedProgressions = computed(() => {
    return [...props.progressions].sort((a, b) => {
        // If both have scenes, sort by scene id (assuming sequential creation)
        if (a.scene && b.scene) {
            return a.scene.id - b.scene.id;
        }
        // Scenes come before no-scene progressions
        if (a.scene && !b.scene) return -1;
        if (!a.scene && b.scene) return 1;
        // Fallback to id
        return a.id - b.id;
    });
});

// Toggle expanded state
const toggleExpanded = (id: number) => {
    expandedId.value = expandedId.value === id ? null : id;
};

// Navigate to scene
const navigateToScene = (sceneId: number) => {
    router.visit(`/novels/${props.novelId}/write/${sceneId}`);
};

// Get mode badge color
const getModeColor = (mode: 'addition' | 'replace'): string => {
    return mode === 'replace'
        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
};

// Truncate text helper
const truncateText = (text: string, maxLength: number = 50): string => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};
</script>

<template>
    <div v-if="sortedProgressions.length > 0" class="space-y-4">
        <!-- Timeline Container -->
        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-4 top-0 h-full w-0.5 bg-gradient-to-b from-violet-400 via-violet-500 to-violet-400 dark:from-violet-600 dark:via-violet-500 dark:to-violet-600" />

            <!-- Timeline nodes -->
            <div class="space-y-4">
                <div
                    v-for="(prog, index) in sortedProgressions"
                    :key="prog.id"
                    class="relative pl-10"
                >
                    <!-- Node circle -->
                    <button
                        type="button"
                        class="absolute left-2 top-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-violet-500 bg-white transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:border-violet-400 dark:bg-zinc-900"
                        :class="{ 'bg-violet-500 dark:bg-violet-400': expandedId === prog.id }"
                        @click="toggleExpanded(prog.id)"
                    >
                        <span class="text-xs font-bold" :class="expandedId === prog.id ? 'text-white' : 'text-violet-600 dark:text-violet-400'">
                            {{ index + 1 }}
                        </span>
                    </button>

                    <!-- Content card -->
                    <div
                        class="rounded-lg border border-zinc-200 bg-white p-3 transition-all dark:border-zinc-700 dark:bg-zinc-800"
                        :class="{ 'ring-2 ring-violet-500 ring-offset-2 dark:ring-offset-zinc-900': expandedId === prog.id }"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <!-- Scene/Timestamp label -->
                                <div class="mb-1 flex flex-wrap items-center gap-2 text-xs">
                                    <span v-if="prog.story_timestamp" class="font-medium text-violet-600 dark:text-violet-400">
                                        {{ prog.story_timestamp }}
                                    </span>
                                    <button
                                        v-if="prog.scene"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-zinc-600 transition-colors hover:bg-violet-100 hover:text-violet-700 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-violet-900/30 dark:hover:text-violet-300"
                                        @click.stop="navigateToScene(prog.scene.id)"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                        {{ prog.scene.chapter?.title ? `${prog.scene.chapter.title} -` : '' }}
                                        {{ prog.scene.title || 'Untitled' }}
                                    </button>
                                </div>

                                <!-- Note preview or full -->
                                <p class="text-sm text-zinc-900 dark:text-white">
                                    {{ expandedId === prog.id ? prog.note : truncateText(prog.note) }}
                                </p>

                                <!-- Expand button if truncated -->
                                <button
                                    v-if="prog.note.length > 50 && expandedId !== prog.id"
                                    type="button"
                                    class="mt-1 text-xs text-violet-600 hover:underline dark:text-violet-400"
                                    @click="toggleExpanded(prog.id)"
                                >
                                    Show more
                                </button>
                            </div>

                            <!-- Mode badge -->
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="getModeColor(prog.mode)"
                            >
                                {{ prog.mode === 'replace' ? 'Replace' : 'Addition' }}
                            </span>
                        </div>

                        <!-- Expanded details -->
                        <Transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-40"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="opacity-100 max-h-40"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div v-if="expandedId === prog.id" class="mt-3 overflow-hidden border-t border-zinc-100 pt-3 dark:border-zinc-700">
                                <!-- Detail change if applicable -->
                                <div v-if="prog.detail && prog.new_value" class="mb-2">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ prog.mode === 'replace' ? 'Replaces' : 'Updates' }}:
                                    </span>
                                    <p class="text-sm">
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ prog.detail.key_name }}:</span>
                                        <span class="ml-1 text-zinc-900 dark:text-white">{{ prog.new_value }}</span>
                                    </p>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="prog.scene"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-md bg-violet-100 px-2 py-1 text-xs font-medium text-violet-700 transition-colors hover:bg-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:hover:bg-violet-900/50"
                                        @click="navigateToScene(prog.scene.id)"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                        Open in Editor
                                    </button>
                                    <button
                                        type="button"
                                        class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                                        @click="toggleExpanded(prog.id)"
                                    >
                                        Collapse
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- End marker -->
            <div class="absolute -bottom-2 left-2 flex h-5 w-5 items-center justify-center rounded-full bg-violet-500 dark:bg-violet-400">
                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Summary -->
        <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400">
            <span>
                <strong class="text-zinc-900 dark:text-white">{{ sortedProgressions.length }}</strong>
                {{ sortedProgressions.length === 1 ? 'progression' : 'progressions' }} tracked
            </span>
            <span>
                {{ sortedProgressions.filter(p => p.scene).length }} linked to scenes
            </span>
        </div>
    </div>

    <!-- Empty state -->
    <div v-else class="py-8 text-center">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
            <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No progressions tracked yet</p>
        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
            Add progressions to track how this entry changes throughout your story
        </p>
    </div>
</template>
