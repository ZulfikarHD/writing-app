<script setup lang="ts">
import type { WorkspaceMode } from '@/composables/useWorkspaceState';

defineProps<{
    currentMode: WorkspaceMode;
}>();

const emit = defineEmits<{
    (e: 'change', mode: WorkspaceMode): void;
}>();

const modes: { id: WorkspaceMode; label: string; icon: string; shortcut: string }[] = [
    {
        id: 'write',
        label: 'Write',
        icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        shortcut: '⌘1',
    },
    {
        id: 'plan',
        label: 'Plan',
        icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        shortcut: '⌘2',
    },
    {
        id: 'codex',
        label: 'Codex',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        shortcut: '⌘3',
    },
    {
        id: 'chat',
        label: 'Chat',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        shortcut: '⌘4',
    },
];
</script>

<template>
    <div class="flex items-center rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
        <button
            v-for="mode in modes"
            :key="mode.id"
            type="button"
            :class="[
                'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-all',
                currentMode === mode.id
                    ? 'bg-white text-violet-700 shadow-sm dark:bg-zinc-700 dark:text-violet-300'
                    : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
            ]"
            :title="`${mode.label} (${mode.shortcut})`"
            @click="emit('change', mode.id)"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="mode.icon" />
            </svg>
            <span class="hidden sm:inline">{{ mode.label }}</span>
        </button>
    </div>
</template>
