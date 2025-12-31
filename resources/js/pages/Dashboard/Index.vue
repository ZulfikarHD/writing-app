<script setup lang="ts">
import EmptyState from '@/components/dashboard/EmptyState.vue';
import NovelCard from '@/components/dashboard/NovelCard.vue';
import StatsCard from '@/components/dashboard/StatsCard.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { computed, ref } from 'vue';

interface Novel {
    id: number;
    title: string;
    description: string | null;
    genre: string | null;
    word_count: number;
    chapter_count: number;
    status: string;
    last_edited_at: string | null;
    pen_name: { id: number; name: string } | null;
}

interface Stats {
    total_novels: number;
    total_words: number;
    in_progress: number;
    completed: number;
}

const props = defineProps<{
    novels: Novel[];
    stats: Stats;
    showOnboarding: boolean;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as { name: string } | undefined);

const searchQuery = ref('');
const sortBy = ref<'recent' | 'title' | 'words'>('recent');

const filteredNovels = computed(() => {
    let result = [...props.novels];

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (novel) =>
                novel.title.toLowerCase().includes(query) ||
                novel.description?.toLowerCase().includes(query) ||
                novel.genre?.toLowerCase().includes(query)
        );
    }

    // Sort
    switch (sortBy.value) {
        case 'title':
            result.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'words':
            result.sort((a, b) => b.word_count - a.word_count);
            break;
        case 'recent':
        default:
            result.sort((a, b) => {
                const dateA = a.last_edited_at ? new Date(a.last_edited_at).getTime() : 0;
                const dateB = b.last_edited_at ? new Date(b.last_edited_at).getTime() : 0;
                return dateB - dateA;
            });
    }

    return result;
});

const formattedTotalWords = computed(() => {
    if (props.stats.total_words >= 1000000) {
        return `${(props.stats.total_words / 1000000).toFixed(1)}M`;
    }
    if (props.stats.total_words >= 1000) {
        return `${(props.stats.total_words / 1000).toFixed(1)}K`;
    }
    return props.stats.total_words.toString();
});

const flashSuccess = computed(() => page.props.flash?.success as string | undefined);
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <Head title="Dashboard" />

        <!-- Success Flash Message -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="flashSuccess"
                class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ flashSuccess }}
            </div>
        </Transition>

        <!-- Welcome Section -->
        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            class="mb-8"
        >
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Welcome back, {{ user?.name?.split(' ')[0] }}!
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Continue your writing journey or start something new.
            </p>
        </Motion>

        <!-- Stats Grid -->
        <div v-if="novels.length > 0" class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <StatsCard label="Total Novels" :value="stats.total_novels" icon="book" color="violet" :index="0" />
            <StatsCard label="Total Words" :value="formattedTotalWords" icon="words" color="blue" :index="1" />
            <StatsCard label="In Progress" :value="stats.in_progress" icon="progress" color="amber" :index="2" />
            <StatsCard label="Completed" :value="stats.completed" icon="check" color="green" :index="3" />
        </div>

        <!-- Search and Filter -->
        <div v-if="novels.length > 0" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1 sm:max-w-xs">
                <svg
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search novels..."
                    class="w-full rounded-lg border border-zinc-200 bg-white py-2 pr-4 pl-10 text-sm placeholder:text-zinc-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-500"
                />
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm text-zinc-500">Sort by:</span>
                <select
                    v-model="sortBy"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="recent">Recently Edited</option>
                    <option value="title">Title</option>
                    <option value="words">Word Count</option>
                </select>
            </div>
        </div>

        <!-- Novels Grid -->
        <div v-if="filteredNovels.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <NovelCard v-for="(novel, index) in filteredNovels" :key="novel.id" :novel="novel" :index="index" />
        </div>

        <!-- No Search Results -->
        <div
            v-else-if="novels.length > 0 && filteredNovels.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 py-16 dark:border-zinc-800"
        >
            <svg class="mb-4 h-12 w-12 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-zinc-600 dark:text-zinc-400">No novels found matching "{{ searchQuery }}"</p>
        </div>

        <!-- Empty State -->
        <EmptyState v-else />
    </AuthenticatedLayout>
</template>
