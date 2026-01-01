<script setup lang="ts">
import ChapterGroup from '@/components/plan/ChapterGroup.vue';
import SearchFilter from '@/components/plan/SearchFilter.vue';
import Button from '@/components/ui/Button.vue';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import ContextMenu from '@/components/ui/ContextMenu.vue';
import Toast from '@/components/ui/Toast.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

interface Label {
    id: number;
    name: string;
    color: string;
    position: number;
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
    subtitle: string | null;
    labels: Label[];
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    act_id: number | null;
    word_count: number;
    scenes: Scene[];
}

interface Act {
    id: number;
    title: string;
    position: number;
}

interface Novel {
    id: number;
    title: string;
    word_count: number;
}

const props = defineProps<{
    novel: Novel;
    chapters: Chapter[];
    acts: Act[];
    labels: Label[];
}>();

const filterState = ref({
    query: '',
    status: null as string | null,
    labelIds: [] as number[],
});

const filteredChapters = ref<Chapter[]>([...props.chapters]);
const isSearching = ref(false);

// Confirmation dialog state
const confirmDialog = ref({
    show: false,
    title: '',
    message: '',
    variant: 'danger' as 'danger' | 'warning' | 'info',
    loading: false,
    onConfirm: () => {},
});

// Toast notification state
const toast = ref({
    show: false,
    variant: 'danger' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const contextMenu = ref<{
    position: { x: number; y: number } | undefined;
    items: Array<{
        label: string;
        action: () => void;
        variant?: 'default' | 'danger';
        divider?: boolean;
    }>;
}>({
    position: undefined,
    items: [],
});

const totalWordCount = computed(() => {
    return filteredChapters.value.reduce((total, chapter) => {
        return total + chapter.scenes.reduce((sum, scene) => sum + scene.word_count, 0);
    }, 0);
});

const totalSceneCount = computed(() => {
    return filteredChapters.value.reduce((total, chapter) => total + chapter.scenes.length, 0);
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const showConfirm = (options: {
    title: string;
    message: string;
    variant?: 'danger' | 'warning' | 'info';
    onConfirm: () => void | Promise<void>;
}) => {
    confirmDialog.value = {
        show: true,
        title: options.title,
        message: options.message,
        variant: options.variant || 'danger',
        loading: false,
        onConfirm: async () => {
            confirmDialog.value.loading = true;
            try {
                await options.onConfirm();
            } finally {
                confirmDialog.value.loading = false;
                confirmDialog.value.show = false;
            }
        },
    };
};

const handleSearch = async () => {
    const hasFilters = filterState.value.query || filterState.value.status || filterState.value.labelIds.length > 0;

    if (!hasFilters) {
        filteredChapters.value = [...props.chapters];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(`/api/novels/${props.novel.id}/scenes/search`, {
            params: {
                q: filterState.value.query || undefined,
                status: filterState.value.status || undefined,
                label_ids: filterState.value.labelIds.length > 0 ? filterState.value.labelIds : undefined,
            },
        });

        // Group filtered scenes by chapter
        const scenesByChapter = new Map<number, Scene[]>();
        response.data.scenes.forEach((scene: Scene & { chapter_title: string }) => {
            if (!scenesByChapter.has(scene.chapter_id)) {
                scenesByChapter.set(scene.chapter_id, []);
            }
            scenesByChapter.get(scene.chapter_id)!.push(scene);
        });

        // Filter chapters to only show those with matching scenes
        filteredChapters.value = props.chapters
            .filter((chapter) => scenesByChapter.has(chapter.id))
            .map((chapter) => ({
                ...chapter,
                scenes: scenesByChapter.get(chapter.id) || [],
            }));
    } catch (error) {
        console.error('Search failed:', error);
        showToast('danger', 'Search Failed', 'Unable to search scenes. Please try again.');
    } finally {
        isSearching.value = false;
    }
};

const handleSceneClick = (scene: Scene) => {
    router.visit(`/novels/${props.novel.id}/write/${scene.id}`);
};

const handleSceneContextMenu = (e: MouseEvent, scene: Scene) => {
    contextMenu.value = {
        position: { x: e.clientX, y: e.clientY },
        items: [
            {
                label: 'Open in Editor',
                action: () => router.visit(`/novels/${props.novel.id}/write/${scene.id}`),
            },
            {
                label: 'Duplicate Scene',
                action: async () => {
                    try {
                        await axios.post(`/api/scenes/${scene.id}/duplicate`);
                        router.reload();
                        showToast('success', 'Scene Duplicated', 'The scene has been duplicated successfully.');
                    } catch {
                        showToast('danger', 'Failed to Duplicate', 'Unable to duplicate the scene. Please try again.');
                    }
                },
            },
            { label: '', action: () => {}, divider: true },
            {
                label: 'Archive Scene',
                action: async () => {
                    try {
                        await axios.post(`/api/scenes/${scene.id}/archive`);
                        router.reload();
                        showToast('success', 'Scene Archived', 'The scene has been archived.');
                    } catch {
                        showToast('danger', 'Failed to Archive', 'Unable to archive the scene. Please try again.');
                    }
                },
            },
            {
                label: 'Delete Scene',
                variant: 'danger' as const,
                action: () => {
                    showConfirm({
                        title: 'Delete Scene',
                        message: `Are you sure you want to delete "${scene.title || 'Untitled'}"? This action cannot be undone.`,
                        variant: 'danger',
                        onConfirm: async () => {
                            try {
                                await axios.delete(`/api/scenes/${scene.id}`);
                                router.reload();
                                showToast('success', 'Scene Deleted', 'The scene has been deleted.');
                            } catch {
                                showToast('danger', 'Failed to Delete', 'Unable to delete the scene. Please try again.');
                            }
                        },
                    });
                },
            },
        ],
    };
};

const handleChapterContextMenu = (e: MouseEvent, chapter: Chapter) => {
    contextMenu.value = {
        position: { x: e.clientX, y: e.clientY },
        items: [
            {
                label: 'Add Scene',
                action: async () => {
                    try {
                        const response = await axios.post(`/api/chapters/${chapter.id}/scenes`, { title: 'New Scene' });
                        router.visit(`/novels/${props.novel.id}/write/${response.data.scene.id}`);
                    } catch {
                        showToast('danger', 'Failed to Create', 'Unable to create a new scene. Please try again.');
                    }
                },
            },
            { label: '', action: () => {}, divider: true },
            {
                label: 'Delete Chapter',
                variant: 'danger' as const,
                action: () => {
                    showConfirm({
                        title: 'Delete Chapter',
                        message: `Are you sure you want to delete "${chapter.title}"? All scenes in this chapter will be permanently deleted.`,
                        variant: 'danger',
                        onConfirm: async () => {
                            try {
                                await axios.delete(`/api/chapters/${chapter.id}`);
                                router.reload();
                                showToast('success', 'Chapter Deleted', 'The chapter and all its scenes have been deleted.');
                            } catch {
                                showToast('danger', 'Failed to Delete', 'Unable to delete the chapter. Please try again.');
                            }
                        },
                    });
                },
            },
        ],
    };
};

const handleScenesReorder = async (chapterId: number, scenes: { id: number; position: number }[]) => {
    try {
        await axios.post(`/api/chapters/${chapterId}/scenes/reorder`, { scenes });
    } catch {
        router.reload();
        showToast('danger', 'Reorder Failed', 'Unable to save the new scene order. Please try again.');
    }
};

const closeContextMenu = () => {
    contextMenu.value.position = undefined;
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${novel.title} - Plan`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </Link>
                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                        <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ novel.title }}</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400 sm:flex">
                            <span>{{ totalSceneCount }} scenes</span>
                            <span>{{ totalWordCount.toLocaleString() }} words</span>
                        </div>
                        <Button :href="`/novels/${novel.id}/write`" as="a">Open Editor</Button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mt-4 flex gap-1">
                    <Link
                        :href="`/novels/${novel.id}/write`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Write
                    </Link>
                    <span class="rounded-lg bg-violet-100 px-3 py-2 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        Plan
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Search & Filters -->
            <div class="mb-6">
                <SearchFilter v-model="filterState" :labels="labels" @search="handleSearch" />
            </div>

            <!-- Loading State -->
            <div v-if="isSearching" class="py-12 text-center">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Searching...</p>
            </div>

            <!-- Chapters Grid -->
            <div v-else class="space-y-4">
                <ChapterGroup
                    v-for="chapter in filteredChapters"
                    :key="chapter.id"
                    :chapter="chapter"
                    @scene-click="handleSceneClick"
                    @scene-context-menu="handleSceneContextMenu"
                    @scenes-reorder="handleScenesReorder"
                    @chapter-context-menu="handleChapterContextMenu"
                />

                <!-- Empty State -->
                <div v-if="filteredChapters.length === 0" class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
                    <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No scenes found</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ filterState.query || filterState.status || filterState.labelIds.length > 0 ? 'Try adjusting your filters' : 'Create your first chapter and scene in the editor' }}
                    </p>
                    <Button :href="`/novels/${novel.id}/write`" as="a" class="mt-4">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Start Writing
                    </Button>
                </div>
            </div>
        </main>

        <!-- Context Menu -->
        <ContextMenu :items="contextMenu.items" :position="contextMenu.position" @close="closeContextMenu" />

        <!-- Confirm Dialog -->
        <ConfirmDialog
            v-model="confirmDialog.show"
            :title="confirmDialog.title"
            :message="confirmDialog.message"
            :variant="confirmDialog.variant"
            :loading="confirmDialog.loading"
            @confirm="confirmDialog.onConfirm"
        />

        <!-- Toast Notification -->
        <Toast
            v-if="toast.show"
            :variant="toast.variant"
            :title="toast.title"
            :duration="5000"
            position="top-right"
            @close="toast.show = false"
        >
            {{ toast.message }}
        </Toast>
    </div>
</template>
