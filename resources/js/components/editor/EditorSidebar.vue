<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';

interface ChapterScene {
    id: number;
    title: string | null;
    position: number;
    status: string;
    word_count: number;
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
    totalWordCount: number;
}>();

const emit = defineEmits<{
    (e: 'selectScene', sceneId: number): void;
    (e: 'close'): void;
}>();

// Local reactive copy of chapters for drag and drop
const localChapters = ref<Chapter[]>([...props.chapters.map((c) => ({ ...c, scenes: [...c.scenes] }))]);

const expandedChapters = ref<Set<number>>(new Set(props.chapters.map((c) => c.id)));
const isCreatingChapter = ref(false);
const newChapterTitle = ref('');
const isDraggingChapter = ref(false);
const isDraggingScene = ref(false);

const statusColors: Record<string, string> = {
    draft: 'bg-zinc-400',
    in_progress: 'bg-amber-400',
    completed: 'bg-green-400',
    needs_revision: 'bg-red-400',
};

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
        // Add to local state
        localChapters.value.push({
            ...res.data.chapter,
            scenes: [],
        });
        newChapterTitle.value = '';
        isCreatingChapter.value = false;
    } catch {
        // Reload on error to sync state
        router.reload();
    }
};

const createScene = async (chapterId: number) => {
    try {
        const res = await axios.post('/api/chapters/' + chapterId + '/scenes', {
            title: 'New Scene',
        });
        // Add to local state
        const chapter = localChapters.value.find((c) => c.id === chapterId);
        if (chapter) {
            chapter.scenes.push(res.data.scene);
        }
        emit('selectScene', res.data.scene.id);
    } catch {
        router.reload();
    }
};

// Handle chapter reorder
const onChapterDragEnd = async () => {
    isDraggingChapter.value = false;
    const chapters = localChapters.value.map((c, index) => ({
        id: c.id,
        position: index,
    }));
    try {
        await axios.post('/api/novels/' + props.novel.id + '/chapters/reorder', {
            chapters,
        });
    } catch {
        router.reload();
    }
};

// Handle scene reorder within a chapter
const onSceneDragEnd = async (chapterId: number) => {
    isDraggingScene.value = false;
    const chapter = localChapters.value.find((c) => c.id === chapterId);
    if (!chapter) return;

    const scenes = chapter.scenes.map((s, index) => ({
        id: s.id,
        position: index,
    }));
    try {
        await axios.post('/api/chapters/' + chapterId + '/scenes/reorder', {
            scenes,
        });
    } catch {
        router.reload();
    }
};

// Draggable options
const chapterDragOptions = computed(() => ({
    animation: 200,
    group: 'chapters',
    ghostClass: 'opacity-50',
    chosenClass: 'bg-violet-100 dark:bg-violet-900/30',
    handle: '.chapter-drag-handle',
}));

const sceneDragOptions = computed(() => ({
    animation: 200,
    group: 'scenes',
    ghostClass: 'opacity-50',
    chosenClass: 'bg-violet-100 dark:bg-violet-900/30',
}));
</script>

<template>
    <aside class="flex h-full w-64 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <Link href="/dashboard" class="flex items-center gap-2 text-sm font-semibold text-zinc-900 transition-all active:scale-95 dark:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </Link>
            <button type="button" class="rounded p-1 text-zinc-500 transition-all hover:bg-zinc-200 active:scale-95 dark:hover:bg-zinc-700 lg:hidden" @click="emit('close')">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-2">
            <!-- Chapters List (Draggable) -->
            <draggable
                v-model="localChapters"
                item-key="id"
                v-bind="chapterDragOptions"
                @start="isDraggingChapter = true"
                @end="onChapterDragEnd"
            >
                <template #item="{ element: chapter }">
                    <div class="mb-2">
                        <!-- Chapter Header -->
                        <div class="group flex w-full items-center gap-1 rounded-md px-1 py-1 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                            <!-- Drag Handle -->
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

                            <!-- Expand/Collapse -->
                            <button
                                type="button"
                                class="flex flex-1 items-center gap-2 rounded-md px-1 py-0.5 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                @click="toggleChapter(chapter.id)"
                            >
                                <svg
                                    :class="['h-3 w-3 transition-transform', expandedChapters.has(chapter.id) ? 'rotate-90' : '']"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="flex-1 truncate">{{ chapter.title }}</span>
                            </button>

                            <!-- Add Scene -->
                            <button
                                type="button"
                                class="rounded p-1 text-zinc-400 opacity-0 transition-all hover:bg-zinc-300 hover:text-zinc-600 group-hover:opacity-100 dark:text-zinc-500 dark:hover:bg-zinc-600 dark:hover:text-zinc-300"
                                title="Add scene"
                                @click.stop="createScene(chapter.id)"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Scenes List (Draggable) -->
                        <div v-if="expandedChapters.has(chapter.id)" class="ml-3 mt-1">
                            <draggable
                                v-model="chapter.scenes"
                                item-key="id"
                                v-bind="sceneDragOptions"
                                @start="isDraggingScene = true"
                                @end="onSceneDragEnd(chapter.id)"
                            >
                                <template #item="{ element: scene }">
                                    <button
                                        type="button"
                                        :class="[
                                            'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm transition-all',
                                            scene.id === activeSceneId
                                                ? 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-200'
                                                : 'text-zinc-600 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700',
                                        ]"
                                        @click="emit('selectScene', scene.id)"
                                    >
                                        <span :class="['h-2 w-2 shrink-0 rounded-full', statusColors[scene.status] || 'bg-zinc-400']" />
                                        <span class="flex-1 truncate">{{ scene.title || 'Untitled' }}</span>
                                        <span class="text-xs text-zinc-400">{{ scene.word_count }}</span>
                                    </button>
                                </template>
                            </draggable>

                            <!-- Empty state -->
                            <div v-if="chapter.scenes.length === 0" class="px-2 py-2 text-xs text-zinc-400 dark:text-zinc-500">
                                No scenes yet
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>

            <!-- Add Chapter -->
            <div v-if="isCreatingChapter" class="mt-2 px-2">
                <input
                    v-model="newChapterTitle"
                    type="text"
                    placeholder="Chapter title..."
                    class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                    @keyup.enter="createChapter"
                    @keyup.escape="isCreatingChapter = false"
                />
                <div class="mt-1 flex gap-1">
                    <button
                        type="button"
                        class="flex-1 rounded-md bg-violet-600 px-2 py-1 text-xs font-medium text-white transition-all hover:bg-violet-700 active:scale-95"
                        @click="createChapter"
                    >
                        Create
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-md border border-zinc-300 px-2 py-1 text-xs font-medium text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-700"
                        @click="isCreatingChapter = false"
                    >
                        Cancel
                    </button>
                </div>
            </div>
            <button
                v-else
                type="button"
                class="mt-2 flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-zinc-500 transition-all hover:bg-zinc-200 active:scale-[0.98] dark:text-zinc-400 dark:hover:bg-zinc-700"
                @click="isCreatingChapter = true"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Chapter
            </button>
        </div>

        <!-- Quick Links -->
        <div class="border-t border-zinc-200 px-2 py-2 dark:border-zinc-700">
            <div class="flex gap-1">
                <Link
                    :href="`/novels/${novel.id}/plan`"
                    class="flex flex-1 items-center justify-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-medium text-zinc-600 transition-all hover:bg-zinc-200 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-700"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Plan
                </Link>
                <Link
                    :href="`/novels/${novel.id}/codex`"
                    class="flex flex-1 items-center justify-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-medium text-zinc-600 transition-all hover:bg-zinc-200 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-700"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Codex
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium">{{ totalWordCount.toLocaleString() }}</span> total words
            </div>
        </div>
    </aside>
</template>
