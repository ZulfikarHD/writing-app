<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import { Link } from '@inertiajs/vue3';
import ViewSwitcher from './ViewSwitcher.vue';

type ViewType = 'grid' | 'matrix' | 'outline';

interface Novel {
    id: number;
    title: string;
    word_count: number;
}

defineProps<{
    novel: Novel;
    currentView: ViewType;
    totalSceneCount: number;
    totalWordCount: number;
}>();

const emit = defineEmits<{
    (e: 'viewChange', view: ViewType): void;
    (e: 'openSettings'): void;
}>();
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <!-- Top row: Navigation & Actions -->
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
                    <Button :href="`/novels/${novel.id}/workspace`" as="a">Open Workspace</Button>
                </div>
            </div>

            <!-- Bottom row: Tabs & View Controls -->
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <!-- Navigation tabs -->
                <div class="flex gap-1">
                    <Link
                        :href="`/novels/${novel.id}/workspace`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Write
                    </Link>
                    <span class="rounded-lg bg-violet-100 px-3 py-2 text-sm font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        Plan
                    </span>
                    <Link
                        :href="`/novels/${novel.id}/workspace?mode=codex`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        Codex
                    </Link>
                </div>

                <!-- View controls -->
                <div class="flex items-center gap-2">
                    <ViewSwitcher :model-value="currentView" @update:model-value="emit('viewChange', $event)" />

                    <!-- Settings button -->
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition-colors hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200"
                        title="View Settings"
                        @click="emit('openSettings')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
        </div>
    </header>
</template>
