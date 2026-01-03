<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useWorkspaceState, type WorkspaceMode } from '@/composables/useWorkspaceState';
import SidebarToolSection from './SidebarToolSection.vue';
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

const {
    isToolExpanded,
    toggleToolExpanded,
    isToolPinned,
    toggleToolPinned,
} = useWorkspaceState();
</script>

<template>
    <aside class="flex h-full w-64 flex-col border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
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

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Scenes Toggle Button (opens right sidebar) -->
            <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg bg-gradient-to-r from-amber-500/10 to-orange-500/10 px-3 py-2.5 text-left transition-all hover:from-amber-500/20 hover:to-orange-500/20 active:scale-[0.98] dark:from-amber-500/20 dark:to-orange-500/20 dark:hover:from-amber-500/30 dark:hover:to-orange-500/30"
                    @click="emit('toggleScenesSidebar')"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/20 dark:bg-amber-500/30">
                        <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Scenes</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Manage chapters & scenes</div>
                    </div>
                    <svg class="h-4 w-4 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Codex Tool Section -->
            <SidebarToolSection
                name="codex"
                title="Codex"
                icon="book"
                :expanded="isToolExpanded('codex')"
                :pinned="isToolPinned('codex')"
                @toggle="toggleToolExpanded('codex')"
                @pin="toggleToolPinned('codex')"
            >
                <CodexQuickList
                    :novel-id="novel.id"
                    @select="emit('openCodexEntry', $event)"
                    @create="emit('openQuickCreate')"
                />
            </SidebarToolSection>

            <!-- Notes Tool Section (placeholder for future) -->
            <SidebarToolSection
                name="notes"
                title="Notes"
                icon="notes"
                :expanded="isToolExpanded('notes')"
                :pinned="isToolPinned('notes')"
                @toggle="toggleToolExpanded('notes')"
                @pin="toggleToolPinned('notes')"
            >
                <div class="px-2 py-3 text-center text-xs text-zinc-500 dark:text-zinc-400">
                    Scene notes will appear here
                </div>
            </SidebarToolSection>

            <!-- Prompts Tool Section -->
            <SidebarToolSection
                name="prompts"
                title="Prompts"
                icon="prompts"
                :expanded="isToolExpanded('prompts')"
                :pinned="isToolPinned('prompts')"
                @toggle="toggleToolExpanded('prompts')"
                @pin="toggleToolPinned('prompts')"
            >
                <PromptsQuickList
                    @select="emit('selectPrompt', $event)"
                />
            </SidebarToolSection>
        </div>

        <!-- Footer -->
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium">{{ totalWordCount.toLocaleString() }}</span> total words
            </div>
        </div>
    </aside>
</template>
