<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';
import { Motion, AnimatePresence } from 'motion-v';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface ChapterScene {
    id: number;
    title: string | null;
    position: number;
    status: string;
    word_count: number;
    labels?: Label[];
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    scenes: ChapterScene[];
}

interface Novel {
    id: number;
    title: string;
}

const props = defineProps<{
    novel: Novel;
    chapters: Chapter[];
    activeSceneId?: number;
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'selectScene', sceneId: number): void;
    (e: 'chaptersUpdate', chapters: Chapter[]): void;
    (e: 'close'): void;
}>();

// Local reactive copy of chapters for drag and drop
const localChapters = ref<Chapter[]>([...props.chapters.map((c) => ({ ...c, scenes: [...c.scenes] }))]);
const expandedChapters = ref<Set<number>>(new Set(props.chapters.map((c) => c.id)));
const isCreatingChapter = ref(false);
const newChapterTitle = ref('');
const isDraggingChapter = ref(false);
const isDraggingScene = ref(false);

// Watch for chapter updates from parent
watch(
    () => props.chapters,
    (newChapters) => {
        localChapters.value = [...newChapters.map((c) => ({ ...c, scenes: [...c.scenes] }))];
    },
    { deep: true }
);

const statusColors: Record<string, string> = {
    draft: 'bg-zinc-400',
    in_progress: 'bg-amber-400',
    completed: 'bg-green-400',
    needs_revision: 'bg-red-400',
};

const totalScenes = computed(() => localChapters.value.reduce((sum, c) => sum + c.scenes.length, 0));
const totalWords = computed(() =>
    localChapters.value.reduce((sum, c) => sum + c.scenes.reduce((s, scene) => s + scene.word_count, 0), 0)
);

const toggleChapter = (id: number) => {
    if (expandedChapters.value.has(id)) {
        expandedChapters.value.delete(id);
    } else {
        expandedChapters.value.add(id);
    }
};

const createChapter = async () => {
    if (!newChapterTitle.value.trim()) return;
    try {
        const res = await axios.post('/api/novels/' + props.novel.id + '/chapters', {
            title: newChapterTitle.value,
        });
        localChapters.value.push({
            ...res.data.chapter,
            scenes: [],
        });
        emit('chaptersUpdate', localChapters.value);
        newChapterTitle.value = '';
        isCreatingChapter.value = false;
    } catch {
        router.reload();
    }
};

const createScene = async (chapterId: number) => {
    try {
        const res = await axios.post('/api/chapters/' + chapterId + '/scenes', {
            title: 'New Scene',
        });
        const chapter = localChapters.value.find((c) => c.id === chapterId);
        if (chapter) {
            chapter.scenes.push(res.data.scene);
        }
        emit('chaptersUpdate', localChapters.value);
        emit('selectScene', res.data.scene.id);
    } catch {
        router.reload();
    }
};

const onChapterDragEnd = async () => {
    isDraggingChapter.value = false;
    const chapters = localChapters.value.map((c, index) => ({
        id: c.id,
        position: index,
    }));
    emit('chaptersUpdate', localChapters.value);
    try {
        await axios.post('/api/novels/' + props.novel.id + '/chapters/reorder', { chapters });
    } catch {
        router.reload();
    }
};

const onSceneDragEnd = async (chapterId: number) => {
    isDraggingScene.value = false;
    const chapter = localChapters.value.find((c) => c.id === chapterId);
    if (!chapter) return;

    emit('chaptersUpdate', localChapters.value);
    const scenes = chapter.scenes.map((s, index) => ({
        id: s.id,
        position: index,
    }));
    try {
        await axios.post('/api/chapters/' + chapterId + '/scenes/reorder', { scenes });
    } catch {
        router.reload();
    }
};

const chapterDragOptions = computed(() => ({
    animation: 200,
    group: 'chapters-right',
    ghostClass: 'opacity-50',
    chosenClass: 'dragging-chosen',
    handle: '.chapter-drag-handle',
}));

const sceneDragOptions = computed(() => ({
    animation: 200,
    group: 'scenes-right',
    ghostClass: 'opacity-50',
    chosenClass: 'dragging-chosen',
}));
</script>

<template>
    <AnimatePresence>
        <Motion
            v-if="isOpen"
            :initial="{ x: '100%', opacity: 0 }"
            :animate="{ x: 0, opacity: 1 }"
            :exit="{ x: '100%', opacity: 0 }"
            :transition="{ type: 'spring', stiffness: 400, damping: 40 }"
            class="flex h-full w-72 flex-col border-l border-zinc-200 bg-zinc-50/95 backdrop-blur-sm dark:border-zinc-700 dark:bg-zinc-800/95"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Scenes</h2>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-zinc-400 transition-all hover:bg-zinc-200 hover:text-zinc-600 active:scale-95 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                    @click="emit('close')"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="flex items-center gap-4 border-b border-zinc-200 px-4 py-2 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    {{ localChapters.length }} chapters
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ totalScenes }} scenes
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    {{ totalWords.toLocaleString() }} words
                </span>
            </div>

            <!-- Chapters List -->
            <div class="flex-1 overflow-y-auto p-3">
                <draggable
                    v-model="localChapters"
                    item-key="id"
                    v-bind="chapterDragOptions"
                    @start="isDraggingChapter = true"
                    @end="onChapterDragEnd"
                >
                    <template #item="{ element: chapter }">
                        <div class="mb-1">
                            <!-- Chapter Header -->
                            <div
                                class="group flex w-full items-center gap-1 rounded-lg px-2 py-2 transition-colors hover:bg-zinc-200/70 dark:hover:bg-zinc-700/50"
                            >
                                <button
                                    type="button"
                                    class="chapter-drag-handle cursor-grab rounded p-1 text-zinc-400 opacity-0 transition-opacity hover:bg-zinc-300 group-hover:opacity-100 dark:text-zinc-500 dark:hover:bg-zinc-600"
                                    title="Drag to reorder"
                                >
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="9" cy="5" r="1.5" />
                                        <circle cx="15" cy="5" r="1.5" />
                                        <circle cx="9" cy="12" r="1.5" />
                                        <circle cx="15" cy="12" r="1.5" />
                                        <circle cx="9" cy="19" r="1.5" />
                                        <circle cx="15" cy="19" r="1.5" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="flex flex-1 items-center gap-2 rounded-md px-1 py-0.5 text-left text-sm font-medium text-zinc-800 dark:text-zinc-200"
                                    @click="toggleChapter(chapter.id)"
                                >
                                    <Motion
                                        :animate="{ rotate: expandedChapters.has(chapter.id) ? 90 : 0 }"
                                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                                    >
                                        <svg class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Motion>
                                    <svg class="h-4 w-4 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span class="flex-1 truncate">{{ chapter.title }}</span>
                                    <span class="rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-normal text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                                        {{ chapter.scenes.length }}
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-zinc-400 opacity-0 transition-all hover:bg-emerald-100 hover:text-emerald-600 group-hover:opacity-100 dark:text-zinc-500 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400"
                                    title="Add scene"
                                    @click.stop="createScene(chapter.id)"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Scenes List with Tree Lines -->
                            <AnimatePresence>
                                <Motion
                                    v-if="expandedChapters.has(chapter.id)"
                                    :initial="{ opacity: 0, height: 0 }"
                                    :animate="{ opacity: 1, height: 'auto' }"
                                    :exit="{ opacity: 0, height: 0 }"
                                    :transition="{ duration: 0.2 }"
                                    class="overflow-hidden"
                                >
                                    <div class="relative ml-5 border-l-2 border-zinc-200 pl-3 dark:border-zinc-700">
                                        <draggable
                                            v-model="chapter.scenes"
                                            item-key="id"
                                            v-bind="sceneDragOptions"
                                            @start="isDraggingScene = true"
                                            @end="onSceneDragEnd(chapter.id)"
                                        >
                                            <template #item="{ element: scene }">
                                                <div class="relative">
                                                    <!-- Tree connector line -->
                                                    <div class="absolute -left-3 top-1/2 h-px w-3 bg-zinc-200 dark:bg-zinc-700" />
                                                    <button
                                                        type="button"
                                                        :class="[
                                                            'flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-sm transition-all',
                                                            scene.id === activeSceneId
                                                                ? 'bg-violet-100 font-medium text-violet-900 shadow-sm ring-1 ring-violet-300 dark:bg-violet-900/40 dark:text-violet-200 dark:ring-violet-700'
                                                                : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700/50',
                                                        ]"
                                                        @click="emit('selectScene', scene.id)"
                                                    >
                                                        <span :class="['h-2 w-2 shrink-0 rounded-full ring-2 ring-white dark:ring-zinc-800', statusColors[scene.status] || 'bg-zinc-400']" />
                                                        <span class="flex-1 truncate">{{ scene.title || 'Untitled' }}</span>
                                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ scene.word_count }}</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </draggable>

                                        <!-- Empty state -->
                                        <div v-if="chapter.scenes.length === 0" class="relative">
                                            <div class="absolute -left-3 top-1/2 h-px w-3 bg-zinc-200 dark:bg-zinc-700" />
                                            <div class="flex items-center gap-2 px-2.5 py-2 text-xs italic text-zinc-400 dark:text-zinc-500">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Click + to add a scene
                                            </div>
                                        </div>
                                    </div>
                                </Motion>
                            </AnimatePresence>
                        </div>
                    </template>
                </draggable>

                <!-- Empty Chapters State -->
                <div v-if="localChapters.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="mb-3 h-12 w-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No chapters yet</p>
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Create your first chapter below</p>
                </div>
            </div>

            <!-- Footer: Add Chapter -->
            <div class="border-t border-zinc-200 p-3 dark:border-zinc-700">
                <AnimatePresence>
                    <Motion
                        v-if="isCreatingChapter"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :exit="{ opacity: 0, y: 10 }"
                        :transition="{ duration: 0.15 }"
                    >
                        <input
                            v-model="newChapterTitle"
                            type="text"
                            placeholder="Chapter title..."
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            @keyup.enter="createChapter"
                            @keyup.escape="isCreatingChapter = false"
                        />
                        <div class="mt-2 flex gap-2">
                            <button
                                type="button"
                                class="flex-1 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white transition-all hover:bg-violet-700 active:scale-[0.98]"
                                @click="createChapter"
                            >
                                Create
                            </button>
                            <button
                                type="button"
                                class="flex-1 rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-600 transition-all hover:bg-zinc-100 active:scale-[0.98] dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                @click="isCreatingChapter = false"
                            >
                                Cancel
                            </button>
                        </div>
                    </Motion>
                </AnimatePresence>

                <button
                    v-if="!isCreatingChapter"
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-zinc-300 px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:border-violet-400 hover:bg-violet-50 hover:text-violet-600 active:scale-[0.98] dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-violet-500 dark:hover:bg-violet-900/20 dark:hover:text-violet-400"
                    @click="isCreatingChapter = true"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Chapter
                </button>
            </div>
        </Motion>
    </AnimatePresence>
</template>

<style scoped>
:deep(.dragging-chosen) {
    background-color: rgb(237 233 254);
}

:global(.dark) :deep(.dragging-chosen) {
    background-color: rgb(139 92 246 / 0.3);
}
</style>
