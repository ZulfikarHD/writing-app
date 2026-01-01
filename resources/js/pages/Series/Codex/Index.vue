<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
    aliases: string[];
}

interface Filters {
    type: string | null;
    search: string | null;
}

const props = defineProps<{
    series: { id: number; title: string };
    entries: CodexEntry[];
    types: string[];
    filters: Filters;
}>();

const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');
const showCreateModal = ref(false);

const createForm = useForm({
    type: 'character',
    name: '',
    description: '',
});

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

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

const filteredEntries = computed(() => {
    let entries = props.entries;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        entries = entries.filter(
            (e) =>
                e.name.toLowerCase().includes(query) ||
                e.description?.toLowerCase().includes(query) ||
                e.aliases.some((a) => a.toLowerCase().includes(query)),
        );
    }

    return entries;
});

const entriesByType = computed(() => {
    const grouped: Record<string, CodexEntry[]> = {};
    for (const entry of filteredEntries.value) {
        if (!grouped[entry.type]) {
            grouped[entry.type] = [];
        }
        grouped[entry.type].push(entry);
    }
    return grouped;
});

const applyFilters = () => {
    router.get(
        `/series/${props.series.id}/codex`,
        {
            type: selectedType.value || undefined,
            search: searchQuery.value || undefined,
        },
        { preserveState: true },
    );
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch(selectedType, () => {
    applyFilters();
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    router.get(`/series/${props.series.id}/codex`);
};

const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

const truncateDescription = (text: string | null, length: number = 100) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + '...';
};

const createEntry = async () => {
    try {
        await axios.post(`/api/series/${props.series.id}/codex`, {
            type: createForm.type,
            name: createForm.name,
            description: createForm.description || null,
        });
        router.reload();
        showToast('success', 'Created', 'Codex entry created successfully.');
        showCreateModal.value = false;
        createForm.reset();
    } catch {
        showToast('danger', 'Error', 'Failed to create entry.');
    }
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${series.title} - Series Codex`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="`/series/${series.id}`"
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
                        <span class="hidden text-sm text-zinc-500 dark:text-zinc-400 sm:inline">
                            {{ entries.length }} {{ entries.length === 1 ? 'entry' : 'entries' }}
                        </span>
                        <Button @click="showCreateModal = true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New Entry
                        </Button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mt-4 flex gap-1">
                    <Link
                        :href="`/series/${series.id}`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Overview
                    </Link>
                    <span class="rounded-lg bg-violet-100 px-3 py-2 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        Series Codex
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Info Banner -->
            <div class="mb-6 rounded-lg border border-violet-200 bg-violet-50 p-4 dark:border-violet-800 dark:bg-violet-900/20">
                <p class="text-sm text-violet-700 dark:text-violet-300">
                    <strong>Series Codex</strong> entries are shared across all novels in this series. Create characters, locations, and other
                    worldbuilding elements here to maintain consistency across your entire series.
                </p>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative flex-1 sm:max-w-xs">
                    <Input v-model="searchQuery" placeholder="Search entries...">
                        <template #prefix>
                            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </template>
                    </Input>
                </div>

                <select
                    v-model="selectedType"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option v-for="type in types" :key="type" :value="type">
                        {{ getTypeIcon(type) }} {{ getTypeLabel(type) }}
                    </option>
                </select>

                <button
                    v-if="searchQuery || selectedType"
                    type="button"
                    class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    @click="clearFilters"
                >
                    Clear filters
                </button>
            </div>

            <!-- Entries Grid -->
            <div v-if="filteredEntries.length > 0" class="space-y-8">
                <div v-for="(typeEntries, type) in entriesByType" :key="type">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-white">
                        <span>{{ getTypeIcon(type) }}</span>
                        <span>{{ getTypeLabel(type) }}</span>
                        <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">({{ typeEntries.length }})</span>
                    </h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <Link
                            v-for="entry in typeEntries"
                            :key="entry.id"
                            :href="`/series-codex/${entry.id}`"
                            class="group rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-violet-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-2xl"
                                    :class="getTypeColor(entry.type)"
                                >
                                    {{ getTypeIcon(entry.type) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-medium text-zinc-900 group-hover:text-violet-600 dark:text-white dark:group-hover:text-violet-400">
                                        {{ entry.name }}
                                    </h3>
                                    <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ truncateDescription(entry.description) || 'No description' }}
                                    </p>
                                    <div v-if="entry.aliases.length > 0" class="mt-2 flex flex-wrap gap-1">
                                        <span
                                            v-for="alias in entry.aliases.slice(0, 3)"
                                            :key="alias"
                                            class="inline-flex rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400"
                                        >
                                            {{ alias }}
                                        </span>
                                        <span v-if="entry.aliases.length > 3" class="text-xs text-zinc-400">
                                            +{{ entry.aliases.length - 3 }} more
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
                <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No entries found</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ searchQuery || selectedType ? 'Try adjusting your filters' : 'Create entries that will be shared across all novels in this series' }}
                </p>
                <Button class="mt-4" @click="showCreateModal = true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create First Entry
                </Button>
            </div>
        </main>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card class="mx-4 w-full max-w-md">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Create Series Codex Entry</h3>

                <form @submit.prevent="createEntry" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                        <select
                            v-model="createForm.type"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option v-for="type in types" :key="type" :value="type">
                                {{ getTypeIcon(type) }} {{ getTypeLabel(type) }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name *</label>
                        <Input v-model="createForm.name" placeholder="Enter name" required />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                        <textarea
                            v-model="createForm.description"
                            rows="3"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            placeholder="Describe this entry..."
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="ghost" @click="showCreateModal = false">Cancel</Button>
                        <Button type="submit" :disabled="createForm.processing || !createForm.name">
                            {{ createForm.processing ? 'Creating...' : 'Create Entry' }}
                        </Button>
                    </div>
                </form>
            </Card>
        </div>

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
