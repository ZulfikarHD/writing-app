<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, provide } from 'vue';
import TipTapEditor from '@/components/editor/TipTapEditor.vue';
import EditorToolbar from '@/components/editor/EditorToolbar.vue';
import EditorSidebar from '@/components/editor/EditorSidebar.vue';
import EditorSettingsPanel from '@/components/editor/panels/EditorSettingsPanel.vue';
import SceneMetadataPanel from '@/components/editor/panels/SceneMetadataPanel.vue';
import MentionTooltip from '@/components/editor/MentionTooltip.vue';
// SelectionActionMenu removed - now integrated into SelectionBubbleMenu
import CodexSidebarPanel from '@/components/editor/panels/CodexSidebarPanel.vue';
import { ProgressionEditorModal, QuickCreateModal } from '@/components/codex';
import { useAutoSave } from '@/composables/useAutoSave';
import { useEditorSettings } from '@/composables/useEditorSettings';
import { useCodexHighlight } from '@/composables/useCodexHighlight';
import { useMentionTooltip } from '@/composables/useMentionTooltip';

interface Label {
    id: number;
    name: string;
    color: string;
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
}

interface ChapterScene {
    id: number;
    title: string | null;
    position: number;
    status: string;
    word_count: number;
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    scenes: ChapterScene[];
}

interface Novel {
    id: number;
    title: string;
}

const props = defineProps<{
    novel: Novel;
    chapters: Chapter[];
    activeScene: Scene | null;
    labels?: Label[];
}>();

const editorRef = ref<InstanceType<typeof TipTapEditor> | null>(null);
const editorContainerRef = ref<HTMLElement | null>(null);
const sidebarOpen = ref(true);
const settingsPanelOpen = ref(false);
const metadataPanelOpen = ref(false);
const codexPanelOpen = ref(false);
const selectedCodexEntryId = ref<number | null>(null);
const quickCreateOpen = ref(false);
const quickCreateSelectedText = ref('');
// Sprint 15: Progression modal state
const progressionModalOpen = ref(false);
const content = ref(props.activeScene?.content || null);
const wordCount = ref(props.activeScene?.word_count || 0);
const currentScene = ref<Scene | null>(props.activeScene);

// Provide sceneId for nested components (SceneBeatEditor in SectionNodeView)
// Use computed to make it reactive when activeScene changes
const editorSceneId = computed(() => props.activeScene?.id || 0);
provide('editorSceneId', editorSceneId);

const { settings, editorStyles } = useEditorSettings();

// Codex highlighting - loads entries for mention highlighting
const { entries: codexEntries, refresh: refreshCodexEntries } = useCodexHighlight({
    novelId: props.novel.id,
    enabled: true,
});

// Mention tooltip for hover preview
const {
    hoveredEntry: tooltipEntry,
    targetRect: tooltipTargetRect,
    closeTooltip,
    setupEventListeners: setupTooltipListeners,
    clearCache: clearTooltipCache,
} = useMentionTooltip({
    novelId: props.novel.id,
    debounceMs: 200,
});

const { saveStatus, triggerSave, forceSave } = useAutoSave({
    sceneId: props.activeScene?.id || 0,
    debounceMs: 500,
    onSaved: (newWordCount) => {
        wordCount.value = newWordCount;
    },
});

const handleEditorUpdate = () => {
    if (editorRef.value && props.activeScene) {
        const newContent = editorRef.value.editor?.getJSON();
        if (newContent) triggerSave(newContent);
    }
};

const handleSceneSelect = (sceneId: number) => {
    if (sceneId !== props.activeScene?.id) {
        router.visit('/novels/' + props.novel.id + '/write/' + sceneId);
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (editorRef.value) {
            const newContent = editorRef.value.editor?.getJSON();
            if (newContent) forceSave(newContent);
        }
    }
    // Open metadata panel with 'i' key
    if (e.key === 'i' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        metadataPanelOpen.value = !metadataPanelOpen.value;
    }
    // Open quick create codex with Ctrl+Shift+C
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        openQuickCreate();
    }
    // Sprint 15: Open progression modal with Ctrl+Shift+P
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
        e.preventDefault();
        openProgressionModal();
    }
};

const openQuickCreate = () => {
    // Get selected text from editor
    const editor = editorRef.value?.editor;
    if (editor) {
        const { from, to } = editor.state.selection;
        const selectedText = editor.state.doc.textBetween(from, to, ' ');
        quickCreateSelectedText.value = selectedText;
    } else {
        quickCreateSelectedText.value = '';
    }
    quickCreateOpen.value = true;
};

const closeQuickCreate = () => {
    quickCreateOpen.value = false;
    quickCreateSelectedText.value = '';
};

// Sprint 15: Progression modal handlers
const openProgressionModal = () => {
    progressionModalOpen.value = true;
};

const closeProgressionModal = () => {
    progressionModalOpen.value = false;
};

const handleProgressionSaved = () => {
    // Could show a toast notification here
    closeProgressionModal();
};

const handleCodexCreated = () => {
    // Refresh codex entries to update highlighting
    refreshCodexEntries();
    // Clear tooltip cache so new entries show correctly
    clearTooltipCache();
};

// Sprint 15: Handle selection action menu (mobile-friendly quick create)
const handleSelectionCreateEntry = (text: string) => {
    quickCreateSelectedText.value = text;
    quickCreateOpen.value = true;
};

// Handle click on codex mentions
const handleMentionClick = (entryId: number) => {
    closeTooltip();
    selectedCodexEntryId.value = entryId;
    codexPanelOpen.value = true;
};

// Handle click from tooltip
const handleTooltipClick = (entryId: number) => {
    handleMentionClick(entryId);
};

// Close codex panel
const closeCodexPanel = () => {
    codexPanelOpen.value = false;
    selectedCodexEntryId.value = null;
};

// Handle editor content click for mention navigation
const handleEditorContentClick = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (target.classList.contains('codex-mention')) {
        const entryId = target.dataset.entryId;
        if (entryId) {
            handleMentionClick(parseInt(entryId, 10));
        }
    }
};

// Cleanup function for tooltip listeners
let cleanupTooltipListeners: (() => void) | null = null;

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    
    // Setup tooltip hover listeners on editor content
    if (editorContainerRef.value) {
        cleanupTooltipListeners = setupTooltipListeners(editorContainerRef);
        // Add click listener for mention navigation
        editorContainerRef.value.addEventListener('click', handleEditorContentClick);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
    if (cleanupTooltipListeners) {
        cleanupTooltipListeners();
    }
    if (editorContainerRef.value) {
        editorContainerRef.value.removeEventListener('click', handleEditorContentClick);
    }
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const totalWordCount = computed(() => {
    return props.chapters.reduce((t, ch) => t + ch.scenes.reduce((s, sc) => s + sc.word_count, 0), 0);
});

// Toolbar handlers
const handleHeading = (level: 0 | 1 | 2 | 3) => {
    if (!editorRef.value) return;
    if (level === 0) {
        editorRef.value.setParagraph();
    } else {
        editorRef.value.setHeading(level);
    }
};

const handleAlign = (align: 'left' | 'center' | 'right' | 'justify') => {
    editorRef.value?.setTextAlign(align);
};

const openSettings = () => {
    settingsPanelOpen.value = true;
};

const closeSettings = () => {
    settingsPanelOpen.value = false;
};

const openMetadata = () => {
    metadataPanelOpen.value = true;
};

const closeMetadata = () => {
    metadataPanelOpen.value = false;
};

const handleMetadataUpdated = (updated: Partial<Scene>) => {
    if (currentScene.value) {
        currentScene.value = { ...currentScene.value, ...updated };
    }
};

// Compute editor width class
const editorWidthClass = computed(() => {
    switch (settings.value.editorWidth) {
        case 'narrow':
            return 'max-w-xl';
        case 'wide':
            return 'max-w-4xl';
        default:
            return 'max-w-3xl';
    }
});
</script>

<template>
    <div class="flex h-screen bg-white dark:bg-zinc-900" :style="editorStyles">
        <Head :title="novel.title + ' - Editor'" />

        <EditorSidebar
            v-if="sidebarOpen"
            :novel="novel"
            :chapters="chapters"
            :active-scene-id="activeScene?.id"
            :total-word-count="totalWordCount"
            @select-scene="handleSceneSelect"
            @close="toggleSidebar"
        />

        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="flex items-center justify-between border-b border-zinc-200 bg-white px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-md p-2 text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800"
                        @click="toggleSidebar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <Link :href="'/dashboard'" class="text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400">
                        {{ novel.title }}
                    </Link>
                    <span class="text-zinc-300 dark:text-zinc-600">/</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ activeScene?.title || 'Untitled Scene' }}</span>
                </div>
                <Link href="/dashboard" class="rounded-md px-3 py-1.5 text-sm font-medium text-zinc-600 transition-all hover:bg-zinc-100 active:scale-95 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    Exit
                </Link>
            </header>

            <EditorToolbar
                v-if="editorRef"
                :can-undo="editorRef.canUndo"
                :can-redo="editorRef.canRedo"
                :is-bold="editorRef.isBold"
                :is-italic="editorRef.isItalic"
                :is-underline="editorRef.isUnderline"
                :is-strike="editorRef.isStrike"
                :is-bullet-list="editorRef.isBulletList"
                :is-ordered-list="editorRef.isOrderedList"
                :is-blockquote="editorRef.isBlockquote"
                :is-highlight="editorRef.isHighlight"
                :current-highlight-color="editorRef.currentHighlightColor"
                :current-heading-level="editorRef.currentHeadingLevel"
                :current-text-align="editorRef.currentTextAlign"
                :word-count="wordCount"
                :save-status="saveStatus"
                @undo="editorRef.undo"
                @redo="editorRef.redo"
                @bold="editorRef.toggleBold"
                @italic="editorRef.toggleItalic"
                @underline="editorRef.toggleUnderline"
                @strike="editorRef.toggleStrike"
                @heading="handleHeading"
                @bullet-list="editorRef.toggleBulletList"
                @ordered-list="editorRef.toggleOrderedList"
                @blockquote="editorRef.toggleBlockquote"
                @highlight="(color: string) => editorRef?.setHighlight(color)"
                @remove-highlight="editorRef.unsetHighlight"
                @align="handleAlign"
                @open-settings="openSettings"
                @open-info="openMetadata"
            />

            <main ref="editorContainerRef" class="flex-1 overflow-y-auto">
                <div :class="['mx-auto transition-all duration-300', editorWidthClass]">
                    <TipTapEditor ref="editorRef" v-model="content" placeholder="Start writing your story..." :codex-entries="codexEntries" :scene-id="activeScene?.id" :enable-a-i="true" @update="handleEditorUpdate" />
                </div>
            </main>
        </div>

        <!-- Codex Sidebar Panel -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="codexPanelOpen"
                class="w-80 shrink-0 border-l border-zinc-200 dark:border-zinc-700"
            >
                <CodexSidebarPanel :novel-id="novel.id" @close="closeCodexPanel" />
            </aside>
        </Transition>

        <!-- Mention Tooltip -->
        <MentionTooltip
            :entry="tooltipEntry"
            :target-rect="tooltipTargetRect"
            :container-ref="editorContainerRef"
            @click="handleTooltipClick"
            @close="closeTooltip"
        />

        <!-- Selection Action Menu removed - now integrated into SelectionBubbleMenu -->

        <!-- Settings Panel -->
        <EditorSettingsPanel :open="settingsPanelOpen" @close="closeSettings" />

        <!-- Metadata Panel -->
        <SceneMetadataPanel
            :open="metadataPanelOpen"
            :scene="currentScene"
            :novel-id="novel.id"
            :available-labels="labels"
            @close="closeMetadata"
            @updated="handleMetadataUpdated"
        />

        <!-- Quick Create Codex Modal -->
        <QuickCreateModal
            :show="quickCreateOpen"
            :novel-id="novel.id"
            :selected-text="quickCreateSelectedText"
            @close="closeQuickCreate"
            @created="handleCodexCreated"
        />

        <!-- Sprint 15: Progression Modal -->
        <ProgressionEditorModal
            :show="progressionModalOpen"
            :novel-id="novel.id"
            :scene-id="activeScene?.id"
            :scene-name="activeScene?.title || undefined"
            @close="closeProgressionModal"
            @saved="handleProgressionSaved"
        />
    </div>
</template>
