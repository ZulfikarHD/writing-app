<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { WorkspaceMode } from '@/composables/useWorkspaceState';
import CodexQuickList from './CodexQuickList.vue';
import PromptsQuickList from './PromptsQuickList.vue';
import type { Prompt } from '@/composables/usePrompts';

interface Novel {
    id: number;
    title: string;
}

defineProps<{
    novel: Novel;
    totalWordCount: number;
    currentMode: WorkspaceMode;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'openCodexEntry', entryId: number): void;
    (e: 'openQuickCreate', selectedText?: string): void;
    (e: 'toggleScenesSidebar'): void;
    (e: 'selectPrompt', prompt: Prompt): void;
}>();

// Active tab state
type SidebarTab = 'scenes' | 'codex' | 'notes' | 'prompts';
const activeTab = ref<SidebarTab>('scenes');

const tabs: { id: SidebarTab; label: string; icon: string; color: string }[] = [
    {
        id: 'scenes',
        label: 'Scenes',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        color: 'text-amber-600 dark:text-amber-400',
    },
    {
        id: 'codex',
        label: 'Codex',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        color: 'text-violet-600 dark:text-violet-400',
    },
    {
        id: 'notes',
        label: 'Notes',
        icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        color: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        id: 'prompts',
        label: 'Prompts',
        icon: 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        color: 'text-pink-600 dark:text-pink-400',
    },
];

// Refs for quick list components
const codexQuickListRef = ref<InstanceType<typeof CodexQuickList> | null>(null);
const promptsQuickListRef = ref<InstanceType<typeof PromptsQuickList> | null>(null);

// Refresh methods
const refreshCodexList = () => {
    codexQuickListRef.value?.refresh();
};

const refreshPromptsList = () => {
    promptsQuickListRef.value?.refresh();
};

// Expose refresh methods
defineExpose({
    refreshCodexList,
    refreshPromptsList,
});
</script>

<template>
    <aside class="flex h-full w-72 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <Link
                href="/dashboard"
                class="flex items-center gap-2 text-sm font-semibold text-zinc-900 transition-all active:scale-95 dark:text-white"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </Link>
            <button
                type="button"
                class="rounded p-1 text-zinc-500 transition-all hover:bg-zinc-200 active:scale-95 dark:hover:bg-zinc-700 lg:hidden"
                @click="emit('close')"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
            <div class="grid grid-cols-2 gap-1.5 rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    :class="[
                        'flex flex-col items-center gap-1 rounded-md px-2 py-2 transition-all',
                        activeTab === tab.id
                            ? 'bg-white shadow-sm dark:bg-zinc-700'
                            : 'hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50',
                    ]"
                    :title="tab.label"
                    @click="activeTab = tab.id"
                >
                    <svg
                        :class="[
                            'h-5 w-5 transition-colors',
                            activeTab === tab.id ? tab.color : 'text-zinc-400 dark:text-zinc-500',
                        ]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                    </svg>
                    <span
                        :class="[
                            'text-xs font-medium transition-colors',
                            activeTab === tab.id
                                ? 'text-zinc-900 dark:text-white'
                                : 'text-zinc-500 dark:text-zinc-400',
                        ]"
                    >
                        {{ tab.label }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto p-3">
            <!-- Scenes Tab -->
            <div v-if="activeTab === 'scenes'" class="space-y-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg bg-gradient-to-r from-amber-500/10 to-orange-500/10 px-3 py-3 text-left transition-all hover:from-amber-500/20 hover:to-orange-500/20 active:scale-[0.98] dark:from-amber-500/20 dark:to-orange-500/20 dark:hover:from-amber-500/30 dark:hover:to-orange-500/30"
                    @click="emit('toggleScenesSidebar')"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/20 dark:bg-amber-500/30">
                        <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Open Scenes Panel</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">View chapters & scenes</div>
                    </div>
                    <svg class="h-4 w-4 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Codex Tab -->
            <div v-if="activeTab === 'codex'">
                <CodexQuickList
                    ref="codexQuickListRef"
                    :novel-id="novel.id"
                    @select="emit('openCodexEntry', $event)"
                    @create="emit('openQuickCreate')"
                />
            </div>

            <!-- Notes Tab -->
            <div v-if="activeTab === 'notes'" class="flex h-full items-center justify-center">
                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Scene notes coming soon</p>
                </div>
            </div>

            <!-- Prompts Tab -->
            <div v-if="activeTab === 'prompts'">
                <PromptsQuickList
                    ref="promptsQuickListRef"
                    @select="emit('selectPrompt', $event)"
                />
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium">{{ totalWordCount.toLocaleString() }}</span> total words
            </div>
        </div>
    </aside>
</template>
