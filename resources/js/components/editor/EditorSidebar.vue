<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

interface ChapterScene { id: number; title: string | null; position: number; status: string; word_count: number; }
interface Chapter { id: number; title: string; position: number; scenes: ChapterScene[]; }
interface Novel { id: number; title: string; }

const props = defineProps<{ novel: Novel; chapters: Chapter[]; activeSceneId?: number; totalWordCount: number; }>();
const emit = defineEmits<{ (e: 'selectScene', sceneId: number): void; (e: 'close'): void; }>();

const expandedChapters = ref<Set<number>>(new Set(props.chapters.map(c => c.id)));
const isCreatingChapter = ref(false);
const newChapterTitle = ref('');

const toggleChapter = (id: number) => { expandedChapters.value.has(id) ? expandedChapters.value.delete(id) : expandedChapters.value.add(id); };

const createChapter = async () => {
    if (!newChapterTitle.value.trim()) return;
    await axios.post('/api/novels/' + props.novel.id + '/chapters', { title: newChapterTitle.value });
    newChapterTitle.value = '';
    isCreatingChapter.value = false;
    window.location.reload();
};

const createScene = async (chapterId: number) => {
    const res = await axios.post('/api/chapters/' + chapterId + '/scenes', { title: 'New Scene' });
    emit('selectScene', res.data.scene.id);
};

const statusColors: Record<string, string> = { draft: 'bg-zinc-400', in_progress: 'bg-amber-400', completed: 'bg-green-400', needs_revision: 'bg-red-400' };
</script>

<template>
    <aside class="flex h-full w-64 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <Link href="/dashboard" class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Back
            </Link>
            <button type="button" class="rounded p-1 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700 lg:hidden" @click="emit('close')">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-2">
            <div v-for="chapter in chapters" :key="chapter.id" class="mb-2">
                <button type="button" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm font-medium text-zinc-700 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-700" @click="toggleChapter(chapter.id)">
                    <svg :class="['h-3 w-3 transition-transform', expandedChapters.has(chapter.id) ? 'rotate-90' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    <span class="flex-1 truncate">{{ chapter.title }}</span>
                    <button type="button" class="rounded p-0.5 hover:bg-zinc-300 dark:hover:bg-zinc-600" @click.stop="createScene(chapter.id)"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                </button>
                <div v-if="expandedChapters.has(chapter.id)" class="ml-4 mt-1 space-y-0.5">
                    <button v-for="scene in chapter.scenes" :key="scene.id" type="button" :class="['flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm', scene.id === activeSceneId ? 'bg-violet-100 text-violet-900 dark:bg-violet-900/30 dark:text-violet-200' : 'text-zinc-600 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700']" @click="emit('selectScene', scene.id)">
                        <span :class="['h-2 w-2 rounded-full', statusColors[scene.status] || 'bg-zinc-400']"></span>
                        <span class="flex-1 truncate">{{ scene.title || 'Untitled' }}</span>
                        <span class="text-xs text-zinc-400">{{ scene.word_count }}</span>
                    </button>
                </div>
            </div>
            <div v-if="isCreatingChapter" class="mt-2 px-2">
                <input v-model="newChapterTitle" type="text" placeholder="Chapter title..." class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-700" @keyup.enter="createChapter" @keyup.escape="isCreatingChapter = false" />
            </div>
            <button v-else type="button" class="mt-2 flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-zinc-500 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700" @click="isCreatingChapter = true">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add Chapter
            </button>
        </div>
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="text-xs text-zinc-500 dark:text-zinc-400"><span class="font-medium">{{ totalWordCount.toLocaleString() }}</span> total words</div>
        </div>
    </aside>
</template>
