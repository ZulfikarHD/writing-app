<script setup lang="ts">
import ActGroup from '@/components/plan/ActGroup.vue';
import ChapterGroup from '@/components/plan/ChapterGroup.vue';
import CreateFromOutline from '@/components/plan/CreateFromOutline.vue';
import MatrixView from '@/components/plan/MatrixView.vue';
import OutlineView from '@/components/plan/OutlineView.vue';
import SceneCardSettings from '@/components/plan/SceneCardSettings.vue';
import SearchFilter from '@/components/plan/SearchFilter.vue';
import ViewSwitcher from '@/components/plan/ViewSwitcher.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import ContextMenu from '@/components/ui/overlays/ContextMenu.vue';
import FormModal from '@/components/ui/overlays/FormModal.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

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

interface CardSettings {
    size: 'compact' | 'normal' | 'large';
    showSummary: boolean;
    showLabels: boolean;
    showWordCount: boolean;
    showPov: boolean;
    showCodexMentions: boolean;
    gridAxis: 'vertical' | 'horizontal';
    cardWidth: 'small' | 'medium' | 'large';
    cardHeight: 'full' | 'small' | 'medium' | 'large';
}

type ViewType = 'grid' | 'matrix' | 'outline';

const props = defineProps<{
    novel: Novel;
    chapters: Chapter[];
    acts: Act[];
    labels: Label[];
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
    (e: 'chaptersUpdate', chapters: Chapter[]): void;
}>();

// View state - persisted to localStorage
const STORAGE_KEY = `plan-view-workspace-${props.novel.id}`;
const getStoredView = (): ViewType => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && ['grid', 'matrix', 'outline'].includes(stored)) {
            return stored as ViewType;
        }
    } catch {
        // localStorage not available
    }
    return 'grid';
};
const currentView = ref<ViewType>(getStoredView());
watch(currentView, (newView) => {
    try {
        localStorage.setItem(STORAGE_KEY, newView);
    } catch {
        // localStorage not available
    }
});

// Card settings - persisted to localStorage
const SETTINGS_KEY = `plan-settings-workspace-${props.novel.id}`;
const getStoredSettings = (): CardSettings => {
    try {
        const stored = localStorage.getItem(SETTINGS_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            // Ensure all new fields have defaults
            return {
                size: parsed.size || 'normal',
                showSummary: parsed.showSummary ?? true,
                showLabels: parsed.showLabels ?? true,
                showWordCount: parsed.showWordCount ?? true,
                showPov: parsed.showPov ?? true,
                showCodexMentions: parsed.showCodexMentions ?? true,
                gridAxis: parsed.gridAxis || 'vertical',
                cardWidth: parsed.cardWidth || 'medium',
                cardHeight: parsed.cardHeight || 'medium',
            };
        }
    } catch {
        // localStorage not available
    }
    return {
        size: 'normal',
        showSummary: true,
        showLabels: true,
        showWordCount: true,
        showPov: true,
        showCodexMentions: true,
        gridAxis: 'vertical',
        cardWidth: 'medium',
        cardHeight: 'medium',
    };
};
const cardSettings = ref<CardSettings>(getStoredSettings());
const showSettingsModal = ref(false);
const showCreateOutlineModal = ref(false);
watch(
    cardSettings,
    (newSettings) => {
        try {
            localStorage.setItem(SETTINGS_KEY, JSON.stringify(newSettings));
        } catch {
            // localStorage not available
        }
    },
    { deep: true }
);

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

// Local state for acts (SPA-like updates)
const localActs = ref<Act[]>([...props.acts]);

// Form modal state for creating/renaming
const formModal = ref({
    show: false,
    title: '',
    description: '',
    inputLabel: '',
    inputPlaceholder: '',
    initialValue: '',
    submitText: '',
    loading: false,
    variant: 'primary' as 'primary' | 'success' | 'warning' | 'danger',
    onSubmit: () => {},
});

// Watch for props.acts changes
watch(
    () => props.acts,
    (newActs) => {
        localActs.value = [...newActs];
    },
    { deep: true }
);

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

const hasFilters = computed(() => {
    return filterState.value.query || filterState.value.status || filterState.value.labelIds.length > 0;
});

// Group chapters by act (using local state for SPA-like updates)
const getChaptersGroupedByAct = () => {
    if (localActs.value.length === 0) {
        return [{ act: null, chapters: filteredChapters.value }];
    }

    const groups: { act: Act | null; chapters: Chapter[] }[] = [];
    const actMap = new Map(localActs.value.map((act) => [act.id, act]));
    const chaptersWithAct: Chapter[] = [];
    const chaptersWithoutAct: Chapter[] = [];

    filteredChapters.value.forEach((chapter) => {
        if (chapter.act_id && actMap.has(chapter.act_id)) {
            chaptersWithAct.push(chapter);
        } else {
            chaptersWithoutAct.push(chapter);
        }
    });

    // Group by act
    localActs.value.forEach((act) => {
        const actChapters = chaptersWithAct.filter((c) => c.act_id === act.id);
        // Include act even if empty to allow adding chapters
        groups.push({ act, chapters: actChapters });
    });

    // Add chapters without acts
    if (chaptersWithoutAct.length > 0) {
        groups.push({ act: null, chapters: chaptersWithoutAct });
    }

    return groups;
};

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
    if (!hasFilters.value) {
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

        const scenesByChapter = new Map<number, Scene[]>();
        response.data.scenes.forEach((scene: Scene & { chapter_title: string }) => {
            if (!scenesByChapter.has(scene.chapter_id)) {
                scenesByChapter.set(scene.chapter_id, []);
            }
            scenesByChapter.get(scene.chapter_id)!.push(scene);
        });

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
    emit('sceneClick', scene);
};

const handleSceneContextMenu = (e: MouseEvent, scene: Scene) => {
    contextMenu.value = {
        position: { x: e.clientX, y: e.clientY },
        items: [
            {
                label: 'Open in Editor',
                action: () => emit('sceneClick', scene),
            },
            {
                label: 'Duplicate Scene',
                action: async () => {
                    try {
                        await axios.post(`/api/scenes/${scene.id}/duplicate`);
                        router.reload({ only: ['chapters'] });
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
                        router.reload({ only: ['chapters'] });
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
                                router.reload({ only: ['chapters'] });
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
                label: 'Rename Chapter',
                action: () => handleRenameChapter(chapter),
            },
            {
                label: 'Add Scene',
                action: () => handleAddSceneToChapter(chapter),
            },
            { label: '', action: () => {}, divider: true },
            {
                label: 'Delete Chapter',
                variant: 'danger' as const,
                action: () => handleDeleteChapter(chapter),
            },
        ],
    };
};

const handleScenesReorder = async (chapterId: number, scenes: { id: number; position: number }[]) => {
    try {
        await axios.post(`/api/chapters/${chapterId}/scenes/reorder`, { scenes });
    } catch {
        router.reload({ only: ['chapters'] });
        showToast('danger', 'Reorder Failed', 'Unable to save the new scene order. Please try again.');
    }
};

const handleSceneMoveToChapter = async (sceneId: number, targetChapterId: number) => {
    try {
        await axios.patch(`/api/scenes/${sceneId}`, {
            chapter_id: targetChapterId,
        });

        const targetChapter = filteredChapters.value.find((c) => c.id === targetChapterId);
        if (targetChapter) {
            const scenes = targetChapter.scenes.map((s, index) => ({
                id: s.id,
                position: index,
            }));
            await axios.post(`/api/chapters/${targetChapterId}/scenes/reorder`, { scenes });
        }

        showToast('success', 'Scene Moved', 'The scene has been moved to the new chapter.');
    } catch {
        router.reload();
        showToast('danger', 'Move Failed', 'Unable to move the scene. Please try again.');
    }
};

const closeContextMenu = () => {
    contextMenu.value.position = undefined;
};

// Show form modal helper
const showFormModal = (options: {
    title: string;
    description?: string;
    inputLabel: string;
    inputPlaceholder: string;
    initialValue?: string;
    submitText: string;
    variant?: 'primary' | 'success' | 'warning' | 'danger';
    onSubmit: (value: string) => void | Promise<void>;
}) => {
    formModal.value = {
        show: true,
        title: options.title,
        description: options.description || '',
        inputLabel: options.inputLabel,
        inputPlaceholder: options.inputPlaceholder,
        initialValue: options.initialValue || '',
        submitText: options.submitText,
        loading: false,
        variant: options.variant || 'primary',
        onSubmit: async (value: string) => {
            formModal.value.loading = true;
            try {
                await options.onSubmit(value);
                formModal.value.show = false;
            } catch {
                // Error is handled in the callback
            } finally {
                formModal.value.loading = false;
            }
        },
    };
};

// Add a new Act (opens modal)
const handleAddAct = () => {
    const nextPosition = localActs.value.length;
    showFormModal({
        title: 'Create New Act',
        description: 'Enter a name for the new act.',
        inputLabel: 'Act Name',
        inputPlaceholder: 'Enter act name...',
        initialValue: `Act ${nextPosition + 1}`,
        submitText: 'Create Act',
        variant: 'primary',
        onSubmit: async (title: string) => {
            try {
                const response = await axios.post(`/api/novels/${props.novel.id}/acts`, {
                    title,
                    position: nextPosition,
                });
                // SPA-like update: Add to local state
                localActs.value.push(response.data.act);
                showToast('success', 'Act Created', `"${title}" has been created.`);
            } catch (error) {
                console.error('Add act error:', error);
                showToast('danger', 'Failed to Create', 'Unable to create a new act. Please try again.');
                throw error;
            }
        },
    });
};

// Add a new Chapter (opens modal)
const handleAddChapter = (actId: number | null = null) => {
    const nextPosition = filteredChapters.value.length;
    // If actId not provided and there are acts, use the last act
    const targetActId = actId ?? (localActs.value.length > 0 ? localActs.value[localActs.value.length - 1].id : null);

    showFormModal({
        title: 'Create New Chapter',
        description: 'Enter a name for the new chapter.',
        inputLabel: 'Chapter Name',
        inputPlaceholder: 'Enter chapter name...',
        initialValue: `Chapter ${nextPosition + 1}`,
        submitText: 'Create Chapter',
        variant: 'primary',
        onSubmit: async (title: string) => {
            try {
                const response = await axios.post(`/api/novels/${props.novel.id}/chapters`, {
                    title,
                    position: nextPosition,
                    act_id: targetActId,
                });
                // SPA-like update: Add to local state
                filteredChapters.value.push({
                    ...response.data.chapter,
                    scenes: [],
                });
                emit('chaptersUpdate', filteredChapters.value);
                showToast('success', 'Chapter Created', `"${title}" has been created.`);
            } catch (error) {
                console.error('Add chapter error:', error);
                showToast('danger', 'Failed to Create', 'Unable to create a new chapter. Please try again.');
                throw error;
            }
        },
    });
};

// Add chapter to specific act (from ActGroup button)
const handleAddChapterToAct = (act: Act) => {
    handleAddChapter(act.id);
};

// Add scene to specific chapter (from ChapterGroup button)
const handleAddSceneToChapter = async (chapter: Chapter) => {
    try {
        const response = await axios.post(`/api/chapters/${chapter.id}/scenes`, { title: 'New Scene' });
        // SPA-like update: Add scene to chapter
        const targetChapter = filteredChapters.value.find((c) => c.id === chapter.id);
        if (targetChapter) {
            targetChapter.scenes.push(response.data.scene);
            emit('chaptersUpdate', filteredChapters.value);
        }
        emit('sceneClick', response.data.scene);
    } catch {
        showToast('danger', 'Failed to Create', 'Unable to create a new scene. Please try again.');
    }
};

// Rename Act (opens modal)
const handleRenameAct = (act: Act) => {
    showFormModal({
        title: 'Rename Act',
        description: `Rename "${act.title}" to a new name.`,
        inputLabel: 'Act Name',
        inputPlaceholder: 'Enter new name...',
        initialValue: act.title,
        submitText: 'Rename',
        variant: 'primary',
        onSubmit: async (newTitle: string) => {
            if (newTitle === act.title) {
                formModal.value.show = false;
                return;
            }
            try {
                await axios.patch(`/api/acts/${act.id}`, { title: newTitle });
                // SPA-like update: Update local state
                const targetAct = localActs.value.find((a) => a.id === act.id);
                if (targetAct) {
                    targetAct.title = newTitle;
                }
                showToast('success', 'Act Renamed', `Act renamed to "${newTitle}".`);
            } catch {
                showToast('danger', 'Failed to Rename', 'Unable to rename the act. Please try again.');
                throw new Error('Rename failed');
            }
        },
    });
};

// Rename Chapter (opens modal)
const handleRenameChapter = (chapter: Chapter) => {
    showFormModal({
        title: 'Rename Chapter',
        description: `Rename "${chapter.title}" to a new name.`,
        inputLabel: 'Chapter Name',
        inputPlaceholder: 'Enter new name...',
        initialValue: chapter.title,
        submitText: 'Rename',
        variant: 'primary',
        onSubmit: async (newTitle: string) => {
            if (newTitle === chapter.title) {
                formModal.value.show = false;
                return;
            }
            try {
                await axios.patch(`/api/chapters/${chapter.id}`, { title: newTitle });
                // SPA-like update: Update local state
                const targetChapter = filteredChapters.value.find((c) => c.id === chapter.id);
                if (targetChapter) {
                    targetChapter.title = newTitle;
                    emit('chaptersUpdate', filteredChapters.value);
                }
                showToast('success', 'Chapter Renamed', `Chapter renamed to "${newTitle}".`);
            } catch {
                showToast('danger', 'Failed to Rename', 'Unable to rename the chapter. Please try again.');
                throw new Error('Rename failed');
            }
        },
    });
};

// Delete Act (shows confirm dialog)
const handleDeleteAct = (act: Act) => {
    showConfirm({
        title: 'Delete Act',
        message: `Are you sure you want to delete "${act.title}"? Chapters in this act will be unassigned but not deleted.`,
        variant: 'danger',
        onConfirm: async () => {
            try {
                await axios.delete(`/api/acts/${act.id}`);
                // SPA-like update: Remove from local state and unassign chapters
                localActs.value = localActs.value.filter((a) => a.id !== act.id);
                filteredChapters.value.forEach((chapter) => {
                    if (chapter.act_id === act.id) {
                        chapter.act_id = null;
                    }
                });
                emit('chaptersUpdate', filteredChapters.value);
                showToast('success', 'Act Deleted', `"${act.title}" has been deleted.`);
            } catch {
                showToast('danger', 'Failed to Delete', 'Unable to delete the act. Please try again.');
            }
        },
    });
};

// Delete Chapter (shows confirm dialog)
const handleDeleteChapter = (chapter: Chapter) => {
    showConfirm({
        title: 'Delete Chapter',
        message: `Are you sure you want to delete "${chapter.title}"? All scenes in this chapter will be permanently deleted.`,
        variant: 'danger',
        onConfirm: async () => {
            try {
                await axios.delete(`/api/chapters/${chapter.id}`);
                // SPA-like update: Remove from local state
                filteredChapters.value = filteredChapters.value.filter((c) => c.id !== chapter.id);
                emit('chaptersUpdate', filteredChapters.value);
                showToast('success', 'Chapter Deleted', `"${chapter.title}" and all its scenes have been deleted.`);
            } catch {
                showToast('danger', 'Failed to Delete', 'Unable to delete the chapter. Please try again.');
            }
        },
    });
};

// Act context menu handler
const handleActContextMenu = (e: MouseEvent, act: Act) => {
    e.preventDefault();
    contextMenu.value = {
        position: { x: e.clientX, y: e.clientY },
        items: [
            {
                label: 'Rename Act',
                action: () => handleRenameAct(act),
            },
            {
                label: 'Add Chapter to Act',
                action: () => handleAddChapterToAct(act),
            },
            { label: '', action: () => {}, divider: true },
            {
                label: 'Delete Act',
                variant: 'danger' as const,
                action: () => handleDeleteAct(act),
            },
        ],
    };
};

onMounted(() => {
    filteredChapters.value = [...props.chapters];
});

// Watch for chapter changes from parent
watch(
    () => props.chapters,
    (newChapters) => {
        if (!hasFilters.value) {
            filteredChapters.value = [...newChapters];
        }
    },
    { deep: true }
);
</script>

<template>
    <div class="flex h-full flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Plan</h2>
                <div class="hidden items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400 sm:flex">
                    <span>{{ totalSceneCount }} scenes</span>
                    <span>{{ totalWordCount.toLocaleString() }} words</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <ViewSwitcher v-model="currentView" />

                <!-- Add Act Button -->
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-violet-700"
                    @click="handleAddAct"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Add Act</span>
                </button>

                <!-- Add Chapter Button -->
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg border border-violet-300 px-3 py-1.5 text-sm font-medium text-violet-700 transition-colors hover:bg-violet-50 dark:border-violet-700 dark:text-violet-300 dark:hover:bg-violet-900/20"
                    @click="handleAddChapter(null)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Add Chapter</span>
                </button>

                <!-- Create from Outline Button -->
                <button
                    type="button"
                    class="hidden items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 lg:flex"
                    @click="showCreateOutlineModal = true"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    From Outline
                </button>

                <!-- View Settings Button -->
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg border border-zinc-200 p-1.5 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-700 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    title="View Settings"
                    @click="showSettingsModal = true"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <!-- Search & Filters (not shown for matrix view) -->
            <div v-if="currentView !== 'matrix'" class="mb-4">
                <SearchFilter v-model="filterState" :labels="labels" @search="handleSearch" />
            </div>

            <!-- View Content -->
            <Transition
                mode="out-in"
                enter-active-class="transition-opacity duration-150"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <!-- Grid View -->
                <div v-if="currentView === 'grid'" key="grid" class="space-y-4">
                    <!-- Loading State -->
                    <div v-if="isSearching" class="py-12 text-center">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Searching...</p>
                    </div>

                    <template v-else>
                        <!-- Group by Acts if available -->
                        <template v-if="localActs.length > 0">
                            <template v-for="group in getChaptersGroupedByAct()" :key="group.act?.id ?? 'unassigned'">
                                <ActGroup
                                    v-if="group.act"
                                    :act="group.act"
                                    :chapter-count="group.chapters.length"
                                    @contextmenu="handleActContextMenu"
                                    @rename="handleRenameAct"
                                    @delete="handleDeleteAct"
                                    @add-chapter="handleAddChapterToAct"
                                >
                                    <ChapterGroup
                                        v-for="chapter in group.chapters"
                                        :key="chapter.id"
                                        :chapter="chapter"
                                        :card-settings="cardSettings"
                                        @scene-click="handleSceneClick"
                                        @scene-context-menu="handleSceneContextMenu"
                                        @scenes-reorder="handleScenesReorder"
                                        @chapter-context-menu="handleChapterContextMenu"
                                        @scene-move-to-chapter="handleSceneMoveToChapter"
                                        @rename="handleRenameChapter"
                                        @delete="handleDeleteChapter"
                                        @add-scene="handleAddSceneToChapter"
                                    />

                                    <!-- Empty state for acts with no chapters -->
                                    <div v-if="group.chapters.length === 0" class="rounded-xl border-2 border-dashed border-violet-200 py-6 text-center dark:border-violet-800/50">
                                        <p class="text-sm text-violet-500 dark:text-violet-400">No chapters in this act</p>
                                        <button
                                            type="button"
                                            class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-violet-100 px-3 py-1.5 text-sm font-medium text-violet-700 transition-colors hover:bg-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:hover:bg-violet-900/50"
                                            @click="handleAddChapterToAct(group.act!)"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Chapter
                                        </button>
                                    </div>
                                </ActGroup>

                                <template v-else>
                                    <!-- Unassigned Chapters Section -->
                                    <div v-if="group.chapters.length > 0" class="space-y-4">
                                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Unassigned Chapters</h3>
                                        <ChapterGroup
                                            v-for="chapter in group.chapters"
                                            :key="chapter.id"
                                            :chapter="chapter"
                                            :card-settings="cardSettings"
                                            @scene-click="handleSceneClick"
                                            @scene-context-menu="handleSceneContextMenu"
                                            @scenes-reorder="handleScenesReorder"
                                            @chapter-context-menu="handleChapterContextMenu"
                                            @scene-move-to-chapter="handleSceneMoveToChapter"
                                            @rename="handleRenameChapter"
                                            @delete="handleDeleteChapter"
                                            @add-scene="handleAddSceneToChapter"
                                        />
                                    </div>
                                </template>
                            </template>
                        </template>

                        <!-- No Acts: Direct chapter list -->
                        <template v-else>
                            <ChapterGroup
                                v-for="chapter in filteredChapters"
                                :key="chapter.id"
                                :chapter="chapter"
                                :card-settings="cardSettings"
                                @scene-click="handleSceneClick"
                                @scene-context-menu="handleSceneContextMenu"
                                @scenes-reorder="handleScenesReorder"
                                @chapter-context-menu="handleChapterContextMenu"
                                @scene-move-to-chapter="handleSceneMoveToChapter"
                                @rename="handleRenameChapter"
                                @delete="handleDeleteChapter"
                                @add-scene="handleAddSceneToChapter"
                            />
                        </template>

                        <!-- Empty State -->
                        <div
                            v-if="filteredChapters.length === 0"
                            class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700"
                        >
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No scenes found</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ hasFilters ? 'Try adjusting your filters' : 'Create your first chapter and scene' }}
                            </p>
                        </div>
                    </template>
                </div>

                <!-- Matrix View -->
                <MatrixView
                    v-else-if="currentView === 'matrix'"
                    key="matrix"
                    :novel-id="novel.id"
                    :labels="labels"
                    :is-searching="isSearching"
                    @scene-click="handleSceneClick"
                    @refresh="router.reload({ only: ['chapters'] })"
                />

                <!-- Outline View -->
                <OutlineView
                    v-else-if="currentView === 'outline'"
                    key="outline"
                    :novel-id="novel.id"
                    :chapters="filteredChapters"
                    :acts="acts"
                    :is-searching="isSearching"
                    :has-filters="hasFilters"
                    @scene-click="handleSceneClick"
                />
            </Transition>
        </div>

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

        <!-- Scene Card Settings Modal -->
        <SceneCardSettings v-model="showSettingsModal" :settings="cardSettings" @update:settings="cardSettings = $event" />

        <!-- Create from Outline Modal -->
        <CreateFromOutline v-model="showCreateOutlineModal" :novel-id="novel.id" @created="router.reload()" />

        <!-- Form Modal (for creating/renaming acts and chapters) -->
        <FormModal
            v-model="formModal.show"
            :title="formModal.title"
            :description="formModal.description"
            :input-label="formModal.inputLabel"
            :input-placeholder="formModal.inputPlaceholder"
            :initial-value="formModal.initialValue"
            :submit-text="formModal.submitText"
            :loading="formModal.loading"
            :variant="formModal.variant"
            @submit="formModal.onSubmit"
            @cancel="formModal.show = false"
        />

        <!-- Toast Notification -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
