<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';
import { useWorkspaceState, type WorkspaceMode } from '@/composables/useWorkspaceState';
import SidebarToolSection from './SidebarToolSection.vue';
import CodexQuickList from './CodexQuickList.vue';

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
    totalWordCount: number;
    currentMode: WorkspaceMode;
}>();

const emit = defineEmits<{
    (e: 'selectScene', sceneId: number): void;
    (e: 'chaptersUpdate', chapters: Chapter[]): void;
    (e: 'close'): void;
    (e: 'openCodexEntry', entryId: number): void;
    (e: 'openQuickCreate', selectedText?: string): void;
}>();

const {
    isToolExpanded,
    toggleToolExpanded,
    isToolPinned,
    toggleToolPinned,
} = useWorkspaceState();

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
    group: 'chapters',
    ghostClass: 'opacity-50',
    chosenClass: 'dragging-chosen',
    handle: '.chapter-drag-handle',
}));

const sceneDragOptions = computed(() => ({
    animation: 200,
    group: 'scenes',
    ghostClass: 'opacity-50',
    chosenClass: 'dragging-chosen',
}));
</script>

<template>
    <aside class="flex h-full w-64 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <Link
                href="/dashboard"
                class="flex items-center gap-2 text-sm font-semibold text-zinc-900 transition-all active:scale-95 dark:text-white"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </Link>
            <button
                type="button"
                class="rounded p-1 text-zinc-500 transition-all hover:bg-zinc-200 active:scale-95 dark:hover:bg-zinc-700 lg:hidden"
                @click="emit('close')"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Scenes Tool Section -->
            <SidebarToolSection
                name="scenes"
                title="Scenes"
                icon="document"
                :expanded="isToolExpanded('scenes')"
                :pinned="isToolPinned('scenes')"
                @toggle="toggleToolExpanded('scenes')"
                @pin="toggleToolPinned('scenes')"
            >
                <!-- Chapters List -->
                <draggable
                    v-model="localChapters"
                    item-key="id"
                    v-bind="chapterDragOptions"
                    @start="isDraggingChapter = true"
                    @end="onChapterDragEnd"
                >
                    <template #item="{ element: chapter }">
                        <div class="mb-0.5">
                            <!-- Chapter Header -->
                            <div class="group flex w-full items-center gap-1 rounded-md px-1 py-1.5 hover:bg-zinc-200 dark:hover:bg-zinc-700">
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
                                    class="flex flex-1 items-center gap-2 rounded-md px-1 py-0.5 text-left text-sm font-semibold text-zinc-800 dark:text-zinc-200"
                                    @click="toggleChapter(chapter.id)"
                                >
                                    <svg
                                        :class="['h-3.5 w-3.5 text-zinc-400 transition-transform dark:text-zinc-500', expandedChapters.has(chapter.id) ? 'rotate-90' : '']"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <!-- Chapter icon -->
                                    <svg class="h-4 w-4 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span class="flex-1 truncate">{{ chapter.title }}</span>
                                    <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">{{ chapter.scenes.length }}</span>
                                </button>

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

                            <!-- Scenes List with Tree Lines -->
                            <div v-if="expandedChapters.has(chapter.id)" class="relative ml-4 border-l-2 border-zinc-200 pl-3 dark:border-zinc-700">
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
                                                    'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm transition-all',
                                                    scene.id === activeSceneId
                                                        ? 'bg-violet-100 font-medium text-violet-900 ring-1 ring-violet-300 dark:bg-violet-900/30 dark:text-violet-200 dark:ring-violet-700'
                                                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700/50',
                                                ]"
                                                @click="emit('selectScene', scene.id)"
                                            >
                                                <!-- Scene icon -->
                                                <svg class="h-3.5 w-3.5 shrink-0 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span :class="['h-2 w-2 shrink-0 rounded-full', statusColors[scene.status] || 'bg-zinc-400']" />
                                                <span class="flex-1 truncate">{{ scene.title || 'Untitled' }}</span>
                                                <span class="text-xs text-zinc-400">{{ scene.word_count }}</span>
                                            </button>
                                        </div>
                                    </template>
                                </draggable>

                                <!-- Empty state with tree connector -->
                                <div v-if="chapter.scenes.length === 0" class="relative">
                                    <div class="absolute -left-3 top-1/2 h-px w-3 bg-zinc-200 dark:bg-zinc-700" />
                                    <div class="flex items-center gap-2 px-2 py-2 text-xs italic text-zinc-400 dark:text-zinc-500">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Click + to add a scene
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>

                <!-- Add Chapter -->
                <div v-if="isCreatingChapter" class="mt-2 px-1">
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
            </SidebarToolSection>

            <!-- Codex Tool Section -->
            <SidebarToolSection
                name="codex"
                title="Codex"
                icon="book"
                :expanded="isToolExpanded('codex')"
                :pinned="isToolPinned('codex')"
                @toggle="toggleToolExpanded('codex')"
                @pin="toggleToolPinned('codex')"
            >
                <CodexQuickList
                    :novel-id="novel.id"
                    @select="emit('openCodexEntry', $event)"
                    @create="emit('openQuickCreate')"
                />
            </SidebarToolSection>

            <!-- Notes Tool Section (placeholder for future) -->
            <SidebarToolSection
                name="notes"
                title="Notes"
                icon="notes"
                :expanded="isToolExpanded('notes')"
                :pinned="isToolPinned('notes')"
                @toggle="toggleToolExpanded('notes')"
                @pin="toggleToolPinned('notes')"
            >
                <div class="px-2 py-3 text-center text-xs text-zinc-500 dark:text-zinc-400">
                    Scene notes will appear here
                </div>
            </SidebarToolSection>
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium">{{ totalWordCount.toLocaleString() }}</span> total words
            </div>
        </div>
    </aside>
</template>

<style scoped>
/* Drag and drop chosen class - vuedraggable requires single class without spaces */
:deep(.dragging-chosen) {
    background-color: rgb(237 233 254); /* violet-100 */
}

:global(.dark) :deep(.dragging-chosen) {
    background-color: rgb(139 92 246 / 0.3); /* violet-900/30 */
}
</style>
