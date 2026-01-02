<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, watch, defineAsyncComponent } from 'vue';
import WorkspaceSidebar from '@/components/workspace/WorkspaceSidebar.vue';
import ScenesRightSidebar from '@/components/workspace/ScenesRightSidebar.vue';
import SceneDetailsSidebar from '@/components/workspace/SceneDetailsSidebar.vue';
import ModeNavigation from '@/components/workspace/ModeNavigation.vue';
import { useWorkspaceState, type WorkspaceMode } from '@/composables/useWorkspaceState';
import { useEditorSettings } from '@/composables/useEditorSettings';

// Lazy load mode panels for better initial load performance
const WritePanel = defineAsyncComponent(() => import('@/components/workspace/WritePanel.vue'));
const PlanPanel = defineAsyncComponent(() => import('@/components/workspace/PlanPanel.vue'));
const CodexPanel = defineAsyncComponent(() => import('@/components/workspace/CodexPanel.vue'));
const ChatPanel = defineAsyncComponent(() => import('@/components/workspace/ChatPanel.vue'));

// Lazy load modals
const CodexEntryModal = defineAsyncComponent(() => import('@/components/codex/modals/CodexEntryModal.vue'));
const CodexCreateModal = defineAsyncComponent(() => import('@/components/codex/modals/CodexCreateModal.vue'));
const QuickCreateModal = defineAsyncComponent(() => import('@/components/codex/modals/QuickCreateModal.vue'));

interface Label {
    id: number;
    name: string;
    color: string;
    position?: number;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    content: object | null;
    summary: string | null;
    status: string;
    word_count: number;
    subtitle: string | null;
    notes: string | null;
    pov_character_id: number | null;
    exclude_from_ai: boolean;
    labels?: Label[];
    codex_mentions_count?: number;
    codex_entries_count?: number;
    position?: number;
}

interface ChapterScene {
    id: number;
    chapter_id: number;
    title: string | null;
    position: number;
    status: string;
    word_count: number;
    summary?: string | null;
    subtitle?: string | null;
    pov_character_id?: number | null;
    labels?: Label[];
    codex_mentions_count?: number;
    codex_entries_count?: number;
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    act_id: number | null;
    word_count: number;
    scenes: ChapterScene[];
}

interface Act {
    id: number;
    title: string;
    position: number;
}

interface Novel {
    id: number;
    title: string;
    series_id: number | null;
    pov?: string;
    tense?: string;
    word_count: number;
}

const props = defineProps<{
    novel: Novel;
    chapters: Chapter[];
    acts: Act[];
    activeScene: Scene | null;
    labels: Label[];
    initialMode: WorkspaceMode;
}>();

// Initialize workspace state with mode from URL
const {
    mode,
    isWriteMode,
    isPlanMode,
    isCodexMode,
    isChatMode,
    sidebarCollapsed,
    setMode,
    toggleSidebar,
    activeCodexEntryId,
    openCodexEntry,
    closeCodexEntry,
} = useWorkspaceState(props.initialMode);

const { editorStyles } = useEditorSettings();

// Local state
const currentScene = ref<Scene | null>(props.activeScene);
const localChapters = ref<Chapter[]>([...props.chapters]);

// Sidebar states
const scenesSidebarOpen = ref(true); // Scenes sidebar (right) - open by default
const detailsSidebarOpen = ref(false); // Scene details sidebar

// Modal states
const quickCreateOpen = ref(false);
const quickCreateSelectedText = ref('');
const codexCreateOpen = ref(false);

// Toggle scenes sidebar (chapters & scenes tree)
const toggleScenesSidebar = () => {
    scenesSidebarOpen.value = !scenesSidebarOpen.value;
};

// Toggle details sidebar (scene metadata)
const toggleDetailsSidebar = () => {
    detailsSidebarOpen.value = !detailsSidebarOpen.value;
};

// Auto-close details sidebar when not in write mode
watch([() => isWriteMode.value, () => currentScene.value], ([writeMode]) => {
    if (!writeMode) {
        detailsSidebarOpen.value = false;
    }
});

// Word count computed
const totalWordCount = computed(() => {
    return localChapters.value.reduce(
        (t, ch) => t + ch.scenes.reduce((s, sc) => s + sc.word_count, 0),
        0
    );
});

// Handle mode change - update URL without full page reload
const handleModeChange = (newMode: WorkspaceMode) => {
    setMode(newMode);
    updateUrl();
};

// Update URL to reflect current state
const updateUrl = () => {
    const url = new URL(window.location.href);

    // Update mode in query params
    if (mode.value !== 'write') {
        url.searchParams.set('mode', mode.value);
    } else {
        url.searchParams.delete('mode');
    }

    // Update codex entry if open
    if (activeCodexEntryId.value) {
        url.searchParams.set('entry', activeCodexEntryId.value.toString());
    } else {
        url.searchParams.delete('entry');
    }

    // Update URL without reload
    window.history.replaceState({}, '', url.toString());
};

// Watch for mode changes to update URL
watch(mode, updateUrl);
watch(activeCodexEntryId, updateUrl);

// Handle scene selection - accepts either scene ID or scene object
const handleSceneSelect = (sceneOrId: number | { id: number }) => {
    const sceneId = typeof sceneOrId === 'number' ? sceneOrId : sceneOrId.id;

    // Always switch to write mode when selecting a scene
    if (mode.value !== 'write') {
        setMode('write');
    }

    if (sceneId !== currentScene.value?.id) {
        router.visit(`/novels/${props.novel.id}/workspace/${sceneId}`, {
            preserveState: true,
            preserveScroll: true,
            only: ['activeScene'],
            onSuccess: (page) => {
                currentScene.value = (page.props as { activeScene: Scene | null }).activeScene;
            },
        });
    }
};

// Handle chapters update from sidebar
const handleChaptersUpdate = (chapters: Chapter[]) => {
    localChapters.value = chapters;
};

// Handle word count update from editor
const handleWordCountUpdate = (wordCount: number) => {
    if (currentScene.value) {
        currentScene.value.word_count = wordCount;
        // Update in chapters list too
        for (const chapter of localChapters.value) {
            const scene = chapter.scenes.find((s) => s.id === currentScene.value?.id);
            if (scene) {
                scene.word_count = wordCount;
                break;
            }
        }
    }
};

// Quick create handlers
const openQuickCreate = (selectedText?: string) => {
    quickCreateSelectedText.value = selectedText || '';
    quickCreateOpen.value = true;
};

const closeQuickCreate = () => {
    quickCreateOpen.value = false;
    quickCreateSelectedText.value = '';
};

// Codex create handlers
const openCodexCreate = () => {
    codexCreateOpen.value = true;
};

const closeCodexCreate = () => {
    codexCreateOpen.value = false;
};

// Handle codex entry created
const handleCodexCreated = () => {
    closeQuickCreate();
    closeCodexCreate();
    // Refresh codex data if in codex mode
    if (isCodexMode.value) {
        router.reload({ only: ['codexEntries'] });
    }
};

// Keyboard shortcuts
const handleKeyDown = (e: KeyboardEvent) => {
    // Ctrl+Shift+C - Quick create codex
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        openQuickCreate();
    }
    // Ctrl+1 - Write mode
    if ((e.ctrlKey || e.metaKey) && e.key === '1') {
        e.preventDefault();
        handleModeChange('write');
    }
    // Ctrl+2 - Plan mode
    if ((e.ctrlKey || e.metaKey) && e.key === '2') {
        e.preventDefault();
        handleModeChange('plan');
    }
    // Ctrl+3 - Codex mode
    if ((e.ctrlKey || e.metaKey) && e.key === '3') {
        e.preventDefault();
        handleModeChange('codex');
    }
    // Ctrl+4 - Chat mode
    if ((e.ctrlKey || e.metaKey) && e.key === '4') {
        e.preventDefault();
        handleModeChange('chat');
    }
    // Ctrl+B - Toggle sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);

    // Check for entry ID in URL on mount
    const url = new URL(window.location.href);
    const entryId = url.searchParams.get('entry');
    if (entryId && isCodexMode.value) {
        openCodexEntry(parseInt(entryId, 10));
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="flex h-screen bg-white dark:bg-zinc-900" :style="editorStyles">
        <Head :title="`${novel.title} - Workspace`" />

        <!-- Left Sidebar (Codex, Notes) -->
        <WorkspaceSidebar
            v-if="!sidebarCollapsed"
            :novel="novel"
            :total-word-count="totalWordCount"
            :current-mode="mode"
            @close="toggleSidebar"
            @open-codex-entry="openCodexEntry"
            @open-quick-create="openQuickCreate"
            @toggle-scenes-sidebar="toggleScenesSidebar"
        />

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Header with Mode Navigation -->
            <header class="flex items-center justify-between border-b border-zinc-200 bg-white px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <!-- Sidebar Toggle -->
                    <button
                        type="button"
                        class="rounded-md p-2 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                        @click="toggleSidebar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Novel Title -->
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">
                        {{ novel.title }}
                    </span>

                    <!-- Scene Title (Write Mode) -->
                    <template v-if="isWriteMode && currentScene">
                        <span class="text-zinc-300 dark:text-zinc-600">/</span>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ currentScene.title || 'Untitled Scene' }}
                        </span>
                    </template>
                </div>

                <!-- Mode Navigation -->
                <ModeNavigation
                    :current-mode="mode"
                    @change="handleModeChange"
                />

                <!-- Right Actions -->
                <div class="flex items-center gap-2">
                    <!-- Word Count -->
                    <span class="hidden text-sm text-zinc-500 dark:text-zinc-400 sm:inline">
                        {{ totalWordCount.toLocaleString() }} words
                    </span>

                    <!-- Scenes Sidebar Toggle -->
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                            scenesSidebarOpen
                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                        ]"
                        title="Toggle scenes panel"
                        @click="toggleScenesSidebar"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="hidden lg:inline">Scenes</span>
                    </button>

                    <!-- Scene Details Toggle (Write Mode only) -->
                    <button
                        v-if="isWriteMode && currentScene"
                        type="button"
                        :class="[
                            'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                            detailsSidebarOpen
                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                        ]"
                        title="Toggle scene details"
                        @click="toggleDetailsSidebar"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="hidden lg:inline">Details</span>
                    </button>

                    <!-- Exit Button -->
                    <a
                        href="/dashboard"
                        class="rounded-md px-3 py-1.5 text-sm font-medium text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    >
                        Exit
                    </a>
                </div>
            </header>

            <!-- Main Panel (Mode-specific content) -->
            <main class="flex-1 overflow-hidden">
                <!-- Write Mode -->
                <WritePanel
                    v-if="isWriteMode"
                    :novel="novel"
                    :scene="currentScene"
                    :labels="labels"
                    @word-count-update="handleWordCountUpdate"
                    @open-quick-create="openQuickCreate"
                    @open-codex-entry="openCodexEntry"
                />

                <!-- Plan Mode -->
                <PlanPanel
                    v-else-if="isPlanMode"
                    :novel="novel"
                    :chapters="localChapters"
                    :acts="acts"
                    :labels="labels"
                    @scene-click="handleSceneSelect"
                    @chapters-update="handleChaptersUpdate"
                />

                <!-- Codex Mode -->
                <CodexPanel
                    v-else-if="isCodexMode"
                    :novel="novel"
                    @entry-click="openCodexEntry"
                    @create-entry="openCodexCreate"
                />

                <!-- Chat Mode -->
                <ChatPanel
                    v-else-if="isChatMode"
                    :novel="novel"
                />
            </main>
        </div>

        <!-- Right Sidebar: Scenes (Chapters & Scenes Tree) -->
        <ScenesRightSidebar
            :novel="novel"
            :chapters="localChapters"
            :active-scene-id="currentScene?.id"
            :is-open="scenesSidebarOpen"
            @select-scene="handleSceneSelect"
            @chapters-update="handleChaptersUpdate"
            @close="scenesSidebarOpen = false"
        />

        <!-- Right Sidebar: Scene Details (Write Mode only) -->
        <SceneDetailsSidebar
            v-if="isWriteMode && detailsSidebarOpen"
            :scene="currentScene"
            :novel-id="novel.id"
            @close="detailsSidebarOpen = false"
            @open-codex-entry="openCodexEntry"
        />

        <!-- Codex Entry Modal -->
        <CodexEntryModal
            :show="!!activeCodexEntryId"
            :entry-id="activeCodexEntryId"
            :novel-id="novel.id"
            @close="closeCodexEntry"
        />

        <!-- Codex Create Modal -->
        <CodexCreateModal
            :show="codexCreateOpen"
            :novel-id="novel.id"
            @close="closeCodexCreate"
            @created="handleCodexCreated"
        />

        <!-- Quick Create Modal -->
        <QuickCreateModal
            :show="quickCreateOpen"
            :novel-id="novel.id"
            :selected-text="quickCreateSelectedText"
            @close="closeQuickCreate"
            @created="handleCodexCreated"
        />
    </div>
</template>
