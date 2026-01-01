<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Motion, AnimatePresence } from 'motion-v';
import draggable from 'vuedraggable';
import SceneCard from './SceneCard.vue';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    summary: string | null;
    position: number;
    status: string;
    word_count: number;
    pov_character_id: number | null;
    pov_character?: { id: number; name: string } | null;
    subtitle: string | null;
    labels: Label[];
    codex_mentions_count?: number;
    codex_entries_count?: number;
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    act_id: number | null;
    word_count: number;
    scenes: Scene[];
}

interface CardSettings {
    size: 'compact' | 'normal' | 'large';
    showSummary: boolean;
    showLabels: boolean;
    showWordCount: boolean;
    showPov: boolean;
    showCodexMentions?: boolean;
}

const props = defineProps<{
    chapter: Chapter;
    cardSettings?: CardSettings;
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
    (e: 'sceneContextMenu', event: MouseEvent, scene: Scene): void;
    (e: 'scenesReorder', chapterId: number, scenes: { id: number; position: number }[]): void;
    (e: 'chapterContextMenu', event: MouseEvent, chapter: Chapter): void;
    (e: 'sceneMoveToChapter', sceneId: number, targetChapterId: number, position: number): void;
    (e: 'rename', chapter: Chapter): void;
    (e: 'delete', chapter: Chapter): void;
    (e: 'addScene', chapter: Chapter): void;
}>();

const isExpanded = ref(true);
const localScenes = ref<Scene[]>([...props.chapter.scenes]);
const isDragging = ref(false);
const showActions = ref(false);

// Watch for external changes to scenes
watch(
    () => props.chapter.scenes,
    (newScenes) => {
        localScenes.value = [...newScenes];
    },
    { deep: true }
);

const formattedWordCount = computed(() => {
    const total = localScenes.value.reduce((sum, s) => sum + s.word_count, 0);
    if (total >= 1000) {
        return `${(total / 1000).toFixed(1)}k`;
    }
    return total.toString();
});

const sceneDragOptions = computed(() => ({
    animation: 200,
    group: 'scenes',
    ghostClass: 'drag-ghost',
    chosenClass: 'drag-chosen',
}));

const onSceneDragChange = (evt: { added?: { element: Scene; newIndex: number }; removed?: { element: Scene }; moved?: { element: Scene; newIndex: number } }) => {
    // Handle cross-chapter move
    if (evt.added) {
        const scene = evt.added.element;
        // Update the scene's chapter_id locally
        scene.chapter_id = props.chapter.id;
        emit('sceneMoveToChapter', scene.id, props.chapter.id, evt.added.newIndex);
    }
};

const onSceneDragEnd = () => {
    isDragging.value = false;
    const scenes = localScenes.value.map((s, index) => ({
        id: s.id,
        position: index,
    }));
    emit('scenesReorder', props.chapter.id, scenes);
};

const handleChapterContextMenu = (e: MouseEvent) => {
    e.preventDefault();
    emit('chapterContextMenu', e, props.chapter);
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow hover:shadow dark:border-zinc-700 dark:bg-zinc-800/80">
        <!-- Chapter Header -->
        <div
            class="group flex cursor-pointer items-center justify-between px-4 py-3.5 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/30"
            @click="isExpanded = !isExpanded"
            @contextmenu="handleChapterContextMenu"
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
        >
            <div class="flex items-center gap-2.5">
                <!-- Expand/Collapse with animation -->
                <Motion
                    :animate="{ rotate: isExpanded ? 90 : 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                >
                    <svg
                        class="h-4 w-4 text-zinc-400 dark:text-zinc-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Motion>

                <!-- Chapter icon -->
                <svg class="h-4.5 w-4.5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>

                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ chapter.title }}</h3>
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                    {{ localScenes.length }} {{ localScenes.length === 1 ? 'scene' : 'scenes' }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Action Buttons -->
                <div
                    :class="[
                        'flex items-center gap-1 transition-opacity',
                        showActions ? 'opacity-100' : 'opacity-0 group-hover:opacity-100',
                    ]"
                >
                    <!-- Add Scene -->
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-zinc-400 transition-all hover:bg-emerald-100 hover:text-emerald-600 active:scale-95 dark:text-zinc-500 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400"
                        title="Add scene to this chapter"
                        @click.stop="emit('addScene', props.chapter)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>

                    <!-- Rename -->
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-zinc-400 transition-all hover:bg-blue-100 hover:text-blue-600 active:scale-95 dark:text-zinc-500 dark:hover:bg-blue-900/30 dark:hover:text-blue-400"
                        title="Rename chapter"
                        @click.stop="emit('rename', props.chapter)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>

                    <!-- Delete -->
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-zinc-400 transition-all hover:bg-red-100 hover:text-red-600 active:scale-95 dark:text-zinc-500 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                        title="Delete chapter"
                        @click.stop="emit('delete', props.chapter)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>

                <span class="ml-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ formattedWordCount }} words</span>
            </div>
        </div>

        <!-- Scenes Grid with animation -->
        <AnimatePresence mode="wait">
            <Motion
                v-if="isExpanded"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                :transition="{ type: 'spring', stiffness: 400, damping: 35, mass: 0.8 }"
                class="overflow-hidden border-t border-zinc-200 dark:border-zinc-700"
            >
                <div class="p-4">
                    <draggable
                        v-model="localScenes"
                        item-key="id"
                        v-bind="sceneDragOptions"
                        class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        @start="isDragging = true"
                        @end="onSceneDragEnd"
                        @change="onSceneDragChange"
                    >
                        <template #item="{ element: scene }">
                            <SceneCard
                                :scene="scene"
                                :draggable="true"
                                :card-settings="cardSettings"
                                @click="emit('sceneClick', scene)"
                                @contextmenu="(e, s) => emit('sceneContextMenu', e, s)"
                            />
                        </template>
                    </draggable>

                    <!-- Empty state -->
                    <div v-if="localScenes.length === 0" class="rounded-xl border-2 border-dashed border-zinc-200 py-8 text-center dark:border-zinc-700">
                        <svg class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">No scenes in this chapter</p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Drag a scene here or click + to add</p>
                    </div>
                </div>
            </Motion>
        </AnimatePresence>
    </div>
</template>

<style scoped>
/* Custom classes for vuedraggable - classList.add() doesn't accept space-separated strings */
:deep(.drag-ghost) {
    opacity: 0.5;
}

:deep(.drag-chosen) {
    box-shadow: 0 0 0 2px rgb(139 92 246); /* ring-2 ring-violet-500 */
}
</style>
