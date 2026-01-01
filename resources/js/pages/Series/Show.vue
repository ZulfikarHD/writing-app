<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface Novel {
    id: number;
    title: string;
    cover_path: string | null;
    word_count: number;
    series_order: number;
}

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    aliases: string[];
}

interface AvailableNovel {
    id: number;
    title: string;
    cover_path: string | null;
}

interface SeriesData {
    id: number;
    title: string;
    description: string | null;
    cover_path: string | null;
    genre: string | null;
    created_at: string;
    updated_at: string;
    novels: Novel[];
    codex_entries: CodexEntry[];
}

const props = defineProps<{
    series: SeriesData;
    availableNovels: AvailableNovel[];
}>();

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const confirmDialog = ref({
    show: false,
    title: '',
    message: '',
    loading: false,
    novelId: null as number | null,
});

const showAddNovelModal = ref(false);
const selectedNovelToAdd = ref<number | null>(null);
const addingNovel = ref(false);

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    location: { label: 'Location', icon: '📍', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    item: { label: 'Item', icon: '⚔️', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    lore: { label: 'Lore', icon: '📜', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    organization: { label: 'Organization', icon: '🏛️', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
    subplot: { label: 'Subplot', icon: '📖', color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
};

const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

const confirmRemoveNovel = (novel: Novel) => {
    confirmDialog.value = {
        show: true,
        title: 'Remove Novel',
        message: `Are you sure you want to remove "${novel.title}" from this series? The novel will not be deleted.`,
        loading: false,
        novelId: novel.id,
    };
};

const removeNovel = async () => {
    if (!confirmDialog.value.novelId) return;

    confirmDialog.value.loading = true;
    try {
        await axios.delete(`/api/series/${props.series.id}/novels/${confirmDialog.value.novelId}`);
        router.reload();
        showToast('success', 'Removed', 'Novel has been removed from the series.');
    } catch {
        showToast('danger', 'Error', 'Failed to remove novel.');
    } finally {
        confirmDialog.value.show = false;
        confirmDialog.value.loading = false;
    }
};

const addNovel = async () => {
    if (!selectedNovelToAdd.value) return;

    addingNovel.value = true;
    try {
        await axios.post(`/api/series/${props.series.id}/novels`, {
            novel_id: selectedNovelToAdd.value,
        });
        router.reload();
        showToast('success', 'Added', 'Novel has been added to the series.');
        showAddNovelModal.value = false;
        selectedNovelToAdd.value = null;
    } catch {
        showToast('danger', 'Error', 'Failed to add novel to series.');
    } finally {
        addingNovel.value = false;
    }
};

const totalWordCount = props.series.novels.reduce((sum, novel) => sum + novel.word_count, 0);
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${series.title} - Series`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            href="/series"
                            class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </Link>
                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                        <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ series.title }}</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button :href="`/series/${series.id}/edit`" as="a" variant="ghost" size="sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit
                        </Button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mt-4 flex gap-1">
                    <span class="rounded-lg bg-violet-100 px-3 py-2 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        Overview
                    </span>
                    <Link
                        :href="`/series/${series.id}/codex`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Series Codex
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Series Info -->
                    <Card>
                        <div class="flex items-start gap-4">
                            <!-- Series Cover -->
                            <div
                                class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600"
                            >
                                <svg class="h-10 w-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                                    />
                                </svg>
                            </div>

                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ series.title }}</h2>
                                <p v-if="series.genre" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ series.genre }}</p>
                                <p v-if="series.description" class="mt-2 text-zinc-600 dark:text-zinc-300">{{ series.description }}</p>

                                <!-- Stats -->
                                <div class="mt-4 flex items-center gap-6 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span>{{ series.novels.length }} {{ series.novels.length === 1 ? 'novel' : 'novels' }}</span>
                                    <span>{{ totalWordCount.toLocaleString() }} words</span>
                                    <span>{{ series.codex_entries.length }} codex entries</span>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Novels in Series -->
                    <Card>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Novels in Series</h2>
                            <Button v-if="availableNovels.length > 0" size="sm" variant="ghost" @click="showAddNovelModal = true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Novel
                            </Button>
                        </div>

                        <div v-if="series.novels.length > 0" class="space-y-3">
                            <div
                                v-for="novel in series.novels"
                                :key="novel.id"
                                class="group flex items-center justify-between rounded-lg border border-zinc-200 p-3 transition-colors hover:border-violet-300 dark:border-zinc-700 dark:hover:border-violet-600"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-sm font-medium text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                                        {{ novel.series_order }}
                                    </span>
                                    <div>
                                        <Link
                                            :href="`/novels/${novel.id}/write`"
                                            class="font-medium text-zinc-900 hover:text-violet-600 dark:text-white dark:hover:text-violet-400"
                                        >
                                            {{ novel.title }}
                                        </Link>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ novel.word_count.toLocaleString() }} words
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-zinc-400 opacity-0 transition-all hover:bg-zinc-100 hover:text-red-600 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-red-400"
                                    @click="confirmRemoveNovel(novel)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-else class="py-8 text-center">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No novels in this series yet.</p>
                            <Button v-if="availableNovels.length > 0" class="mt-3" size="sm" @click="showAddNovelModal = true">
                                Add Your First Novel
                            </Button>
                        </div>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Series Codex Preview -->
                    <Card>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Series Codex</h2>
                            <Link
                                :href="`/series/${series.id}/codex`"
                                class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                            >
                                View All
                            </Link>
                        </div>

                        <div v-if="series.codex_entries.length > 0" class="space-y-2">
                            <Link
                                v-for="entry in series.codex_entries.slice(0, 5)"
                                :key="entry.id"
                                :href="`/series-codex/${entry.id}`"
                                class="flex items-center gap-2 rounded-lg p-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                            >
                                <span :class="['flex h-8 w-8 items-center justify-center rounded-lg text-sm', getTypeColor(entry.type)]">
                                    {{ getTypeIcon(entry.type) }}
                                </span>
                                <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ entry.name }}</span>
                            </Link>

                            <p v-if="series.codex_entries.length > 5" class="pt-2 text-center text-sm text-zinc-500">
                                + {{ series.codex_entries.length - 5 }} more entries
                            </p>
                        </div>

                        <div v-else class="py-6 text-center">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No shared codex entries yet.</p>
                            <Link
                                :href="`/series/${series.id}/codex`"
                                class="mt-2 inline-block text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400"
                            >
                                Create your first entry
                            </Link>
                        </div>
                    </Card>
                </div>
            </div>
        </main>

        <!-- Add Novel Modal -->
        <div v-if="showAddNovelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Add Novel to Series</h3>

                <div class="mb-4 space-y-2">
                    <label
                        v-for="novel in availableNovels"
                        :key="novel.id"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700"
                        :class="{ 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20': selectedNovelToAdd === novel.id }"
                    >
                        <input
                            type="radio"
                            :value="novel.id"
                            v-model="selectedNovelToAdd"
                            class="h-4 w-4 border-zinc-300 text-violet-600 focus:ring-violet-500"
                        />
                        <span class="font-medium text-zinc-900 dark:text-white">{{ novel.title }}</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <Button variant="ghost" @click="showAddNovelModal = false; selectedNovelToAdd = null">Cancel</Button>
                    <Button :disabled="!selectedNovelToAdd || addingNovel" @click="addNovel">
                        {{ addingNovel ? 'Adding...' : 'Add Novel' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <ConfirmDialog
            v-model="confirmDialog.show"
            :title="confirmDialog.title"
            :message="confirmDialog.message"
            variant="danger"
            :loading="confirmDialog.loading"
            @confirm="removeNovel"
        />

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
