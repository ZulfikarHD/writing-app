<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import { BulkCreateModal, BulkExportButton, BulkImportModal } from '@/components/codex';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    color: string | null;
}

// Sprint 14: Tag interface
interface Tag {
    id: number;
    name: string;
    color: string | null;
    entry_type?: string | null;
}

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
    aliases: string[];
    categories: Category[];
    tags?: Tag[]; // Sprint 14
    is_series_entry?: boolean;
}

interface Filters {
    type: string | null;
    category: string | null;
    tag: string | null; // Sprint 14
    search: string | null;
}

interface Series {
    id: number;
    title: string;
}

const props = defineProps<{
    novel: { id: number; title: string; series_id?: number | null };
    series?: Series | null;
    entries: CodexEntry[];
    seriesEntries?: CodexEntry[];
    categories: Category[];
    tags?: Tag[]; // Sprint 14
    types: string[];
    filters: Filters;
}>();

const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');
const selectedCategory = ref(props.filters.category || '');
const selectedTag = ref(props.filters.tag || ''); // Sprint 14
const viewMode = ref<'grid' | 'list'>('grid');
const showImportModal = ref(false);
const showBulkCreateModal = ref(false); // Sprint 15

const handleImportComplete = () => {
    showImportModal.value = false;
    router.reload();
};

// Sprint 15: Handle bulk create completion
const handleBulkCreateComplete = () => {
    showBulkCreateModal.value = false;
    router.reload();
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

const filteredSeriesEntries = computed(() => {
    if (!props.seriesEntries) return [];
    let entries = props.seriesEntries;

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

const seriesEntriesByType = computed(() => {
    const grouped: Record<string, CodexEntry[]> = {};
    for (const entry of filteredSeriesEntries.value) {
        if (!grouped[entry.type]) {
            grouped[entry.type] = [];
        }
        grouped[entry.type].push(entry);
    }
    return grouped;
});

const hasSeriesEntries = computed(() => filteredSeriesEntries.value.length > 0);

const applyFilters = () => {
    router.get(
        `/novels/${props.novel.id}/codex`,
        {
            type: selectedType.value || undefined,
            category: selectedCategory.value || undefined,
            tag: selectedTag.value || undefined, // Sprint 14
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

watch([selectedType, selectedCategory, selectedTag], () => {
    applyFilters();
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    selectedCategory.value = '';
    selectedTag.value = ''; // Sprint 14
    router.get(`/novels/${props.novel.id}/codex`);
};

// Sprint 14: Helper for tag colors
const getContrastColor = (hexColor: string | null): string => {
    if (!hexColor) return '#374151';
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#1F2937' : '#FFFFFF';
};

const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

const truncateDescription = (text: string | null, length: number = 100) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + '...';
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${novel.title} - Codex`" />

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
                        <span class="hidden text-sm text-zinc-500 dark:text-zinc-400 sm:inline">
                            {{ entries.length }} {{ entries.length === 1 ? 'entry' : 'entries' }}
                        </span>
                        <Button variant="ghost" size="sm" @click="showImportModal = true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Import
                        </Button>
                        <BulkExportButton :novel-id="novel.id" :disabled="entries.length === 0" />
                        <!-- Bulk Create Button (Sprint 15: US-12.12) -->
                        <Button variant="ghost" size="sm" @click="showBulkCreateModal = true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Bulk Create
                        </Button>
                        <Button :href="`/novels/${novel.id}/codex/create`" as="a">
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
                        :href="`/novels/${novel.id}/write`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Write
                    </Link>
                    <Link
                        :href="`/novels/${novel.id}/plan`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Plan
                    </Link>
                    <span class="rounded-lg bg-violet-100 px-3 py-2 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"> Codex </span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search -->
                    <div class="relative flex-1 sm:max-w-xs">
                        <Input v-model="searchQuery" placeholder="Search entries..." class="pl-9">
                            <template #prefix>
                                <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </Input>
                    </div>

                    <!-- Type Filter -->
                    <select
                        v-model="selectedType"
                        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">All Types</option>
                        <option v-for="type in types" :key="type" :value="type">
                            {{ getTypeIcon(type) }} {{ getTypeLabel(type) }}
                        </option>
                    </select>

                    <!-- Category Filter -->
                    <select
                        v-if="categories.length > 0"
                        v-model="selectedCategory"
                        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>

                    <!-- Tag Filter (Sprint 14) -->
                    <select
                        v-if="tags && tags.length > 0"
                        v-model="selectedTag"
                        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">All Tags</option>
                        <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                            {{ tag.name }}
                        </option>
                    </select>

                    <!-- Clear Filters -->
                    <button
                        v-if="searchQuery || selectedType || selectedCategory || selectedTag"
                        type="button"
                        class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>
                </div>

                <!-- View Mode Toggle -->
                <div class="flex items-center gap-1 rounded-lg border border-zinc-200 p-1 dark:border-zinc-700">
                    <button
                        type="button"
                        class="rounded-md p-1.5 transition-colors"
                        :class="viewMode === 'grid' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'"
                        @click="viewMode = 'grid'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="rounded-md p-1.5 transition-colors"
                        :class="viewMode === 'list' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'"
                        @click="viewMode = 'list'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="space-y-8">
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
                            :href="`/codex/${entry.id}`"
                            class="group rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-violet-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-violet-600"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Thumbnail -->
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-2xl"
                                    :class="getTypeColor(entry.type)"
                                >
                                    {{ entry.thumbnail_path ? '' : getTypeIcon(entry.type) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-medium text-zinc-900 group-hover:text-violet-600 dark:text-white dark:group-hover:text-violet-400">
                                        {{ entry.name }}
                                    </h3>
                                    <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ truncateDescription(entry.description) || 'No description' }}
                                    </p>
                                    <!-- Aliases -->
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
                                    <!-- Tags (Sprint 14) -->
                                    <div v-if="entry.tags && entry.tags.length > 0" class="mt-2 flex flex-wrap gap-1">
                                        <span
                                            v-for="tag in entry.tags.slice(0, 2)"
                                            :key="tag.id"
                                            class="inline-flex rounded-full px-1.5 py-0.5 text-xs font-medium"
                                            :style="{
                                                backgroundColor: tag.color || '#E5E7EB',
                                                color: getContrastColor(tag.color),
                                            }"
                                        >
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="entry.tags.length > 2" class="text-xs text-zinc-400">
                                            +{{ entry.tags.length - 2 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-400">Type</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase md:table-cell dark:text-zinc-400">
                                Aliases
                            </th>
                            <th class="hidden px-4 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase lg:table-cell dark:text-zinc-400">
                                AI Mode
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr
                            v-for="entry in filteredEntries"
                            :key="entry.id"
                            class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                            @click="router.visit(`/codex/${entry.id}`)"
                        >
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ getTypeIcon(entry.type) }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ entry.name }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="getTypeColor(entry.type)">
                                    {{ getTypeLabel(entry.type) }}
                                </span>
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-zinc-500 md:table-cell dark:text-zinc-400">
                                {{ entry.aliases.slice(0, 3).join(', ') }}
                                <span v-if="entry.aliases.length > 3">...</span>
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-zinc-500 capitalize lg:table-cell dark:text-zinc-400">
                                {{ entry.ai_context_mode }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Series Entries Section -->
            <div v-if="hasSeriesEntries && viewMode === 'grid'" class="mt-10 space-y-6">
                <div class="flex items-center gap-3 border-t border-zinc-200 pt-8 dark:border-zinc-700">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Series Codex</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Inherited from <Link :href="`/series/${series?.id}`" class="text-violet-600 hover:underline dark:text-violet-400">{{ series?.title }}</Link>
                        </p>
                    </div>
                </div>

                <div v-for="(typeEntries, type) in seriesEntriesByType" :key="`series-${type}`">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <span>{{ getTypeIcon(type) }}</span>
                        <span>{{ getTypeLabel(type) }}</span>
                        <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">({{ typeEntries.length }})</span>
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <Link
                            v-for="entry in typeEntries"
                            :key="`series-entry-${entry.id}`"
                            :href="`/series-codex/${entry.id}`"
                            class="group relative rounded-lg border border-violet-200 bg-violet-50/50 p-4 transition-all hover:border-violet-400 hover:shadow-md dark:border-violet-800 dark:bg-violet-900/20 dark:hover:border-violet-600"
                        >
                            <!-- Series Badge -->
                            <span class="absolute right-2 top-2 inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900 dark:text-violet-300">
                                Series
                            </span>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-2xl"
                                    :class="getTypeColor(entry.type)"
                                >
                                    {{ getTypeIcon(entry.type) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate font-medium text-zinc-900 group-hover:text-violet-600 dark:text-white dark:group-hover:text-violet-400">
                                        {{ entry.name }}
                                    </h4>
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
            <div v-if="filteredEntries.length === 0 && !hasSeriesEntries" class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
                <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No entries found</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ searchQuery || selectedType || selectedCategory ? 'Try adjusting your filters' : 'Get started by creating your first Codex entry' }}
                </p>
                <Button :href="`/novels/${novel.id}/codex/create`" as="a" class="mt-4">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create First Entry
                </Button>
            </div>
        </main>

        <!-- Import Modal -->
        <BulkImportModal
            :show="showImportModal"
            :novel-id="novel.id"
            @close="showImportModal = false"
            @imported="handleImportComplete"
        />

        <!-- Bulk Create Modal (Sprint 15: US-12.12) -->
        <BulkCreateModal
            :show="showBulkCreateModal"
            :novel-id="novel.id"
            @close="showBulkCreateModal = false"
            @created="handleBulkCreateComplete"
        />
    </div>
</template>
