<script setup lang="ts">
import type { SidebarTool } from '@/composables/useWorkspaceState';

defineProps<{
    name: SidebarTool;
    title: string;
    icon: 'document' | 'book' | 'notes' | 'chat';
    expanded: boolean;
    pinned: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggle'): void;
    (e: 'pin'): void;
}>();

const iconPaths: Record<string, string> = {
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    book: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    notes: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    chat: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
};
</script>

<template>
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <!-- Section Header -->
        <button
            type="button"
            class="group flex w-full items-center gap-2 px-3 py-2.5 text-left transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700/50"
            @click="emit('toggle')"
        >
            <!-- Expand/Collapse Chevron -->
            <svg
                :class="['h-3 w-3 text-zinc-400 transition-transform', expanded ? 'rotate-90' : '']"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>

            <!-- Icon -->
            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths[icon]" />
            </svg>

            <!-- Title -->
            <span class="flex-1 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ title }}
            </span>

            <!-- Pin Button -->
            <button
                type="button"
                :class="[
                    'rounded p-1 transition-all',
                    pinned
                        ? 'text-violet-600 dark:text-violet-400'
                        : 'text-zinc-400 opacity-0 hover:text-zinc-600 group-hover:opacity-100 dark:text-zinc-500 dark:hover:text-zinc-300',
                ]"
                :title="pinned ? 'Unpin section' : 'Pin section'"
                @click.stop="emit('pin')"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        v-if="pinned"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                        fill="currentColor"
                    />
                    <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                    />
                </svg>
            </button>
        </button>

        <!-- Content -->
        <div v-if="expanded" class="px-2 pb-2">
            <slot />
        </div>
    </div>
</template>
