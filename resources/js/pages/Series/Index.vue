<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import ConfirmDialog from '@/components/ui/overlays/ConfirmDialog.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface SeriesItem {
    id: number;
    title: string;
    description: string | null;
    cover_path: string | null;
    genre: string | null;
    novels_count: number;
    created_at: string;
}

defineProps<{
    series: SeriesItem[];
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
    seriesId: null as number | null,
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

const confirmDelete = (series: SeriesItem) => {
    confirmDialog.value = {
        show: true,
        title: 'Delete Series',
        message: `Are you sure you want to delete "${series.title}"? Novels in this series will not be deleted, but will be unlinked from the series.`,
        loading: false,
        seriesId: series.id,
    };
};

const deleteSeries = async () => {
    if (!confirmDialog.value.seriesId) return;

    confirmDialog.value.loading = true;
    try {
        await axios.delete(`/api/series/${confirmDialog.value.seriesId}`);
        router.reload();
        showToast('success', 'Deleted', 'Series has been deleted successfully.');
    } catch {
        showToast('danger', 'Error', 'Failed to delete series.');
    } finally {
        confirmDialog.value.show = false;
        confirmDialog.value.loading = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head title="My Series" />

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
                        <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">My Series</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm text-zinc-500 dark:text-zinc-400 sm:inline">
                            {{ series.length }} {{ series.length === 1 ? 'series' : 'series' }}
                        </span>
                        <Button href="/series/create" as="a">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New Series
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Series Grid -->
            <div v-if="series.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="item in series" :key="item.id" class="group relative transition-all hover:shadow-lg">
                    <Link :href="`/series/${item.id}`" class="block">
                        <!-- Cover Image or Placeholder -->
                        <div
                            class="mb-4 flex h-32 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600"
                        >
                            <svg class="h-12 w-12 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                                />
                            </svg>
                        </div>

                        <!-- Series Info -->
                        <h3 class="font-semibold text-zinc-900 group-hover:text-violet-600 dark:text-white dark:group-hover:text-violet-400">
                            {{ item.title }}
                        </h3>
                        <p v-if="item.description" class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ item.description }}
                        </p>

                        <!-- Stats -->
                        <div class="mt-3 flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                    />
                                </svg>
                                {{ item.novels_count }} {{ item.novels_count === 1 ? 'novel' : 'novels' }}
                            </span>
                            <span v-if="item.genre" class="flex items-center gap-1">
                                {{ item.genre }}
                            </span>
                        </div>
                    </Link>

                    <!-- Actions -->
                    <div class="absolute right-4 top-4 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Link
                            :href="`/series/${item.id}/edit`"
                            class="rounded-lg bg-white/90 p-2 text-zinc-600 shadow-sm transition-colors hover:bg-white hover:text-zinc-900 dark:bg-zinc-800/90 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </Link>
                        <button
                            type="button"
                            class="rounded-lg bg-white/90 p-2 text-red-600 shadow-sm transition-colors hover:bg-white hover:text-red-700 dark:bg-zinc-800/90 dark:text-red-400 dark:hover:bg-zinc-800 dark:hover:text-red-300"
                            @click.stop.prevent="confirmDelete(item)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                        </button>
                    </div>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-lg border-2 border-dashed border-zinc-200 py-16 text-center dark:border-zinc-700">
                <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No series yet</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Create a series to organize your novels and share a Codex across them.
                </p>
                <Button href="/series/create" as="a" class="mt-4">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Series
                </Button>
            </div>
        </main>

        <!-- Confirm Dialog -->
        <ConfirmDialog
            v-model="confirmDialog.show"
            :title="confirmDialog.title"
            :message="confirmDialog.message"
            variant="danger"
            :loading="confirmDialog.loading"
            @confirm="deleteSeries"
        />

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
