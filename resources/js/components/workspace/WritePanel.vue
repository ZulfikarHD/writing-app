<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import TipTapEditor from '@/components/editor/TipTapEditor.vue';
import EditorToolbar from '@/components/editor/EditorToolbar.vue';
import EditorSettingsPanel from '@/components/editor/panels/EditorSettingsPanel.vue';
import SceneMetadataPanel from '@/components/editor/panels/SceneMetadataPanel.vue';
import MentionTooltip from '@/components/editor/MentionTooltip.vue';
import SelectionActionMenu from '@/components/editor/SelectionActionMenu.vue';
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

interface Novel {
    id: number;
    title: string;
    pov?: string;
    tense?: string;
}

const props = defineProps<{
    novel: Novel;
    scene: Scene | null;
    labels: Label[];
}>();

const emit = defineEmits<{
    (e: 'wordCountUpdate', wordCount: number): void;
    (e: 'openQuickCreate', selectedText?: string): void;
    (e: 'openCodexEntry', entryId: number): void;
    (e: 'chatWithScene', sceneId: number): void;
}>();

const editorRef = ref<InstanceType<typeof TipTapEditor> | null>(null);
const editorContainerRef = ref<HTMLElement | null>(null);
const settingsPanelOpen = ref(false);
const metadataPanelOpen = ref(false);

const content = ref(props.scene?.content || null);
const wordCount = ref(props.scene?.word_count || 0);
const currentScene = ref<Scene | null>(props.scene);

const { settings } = useEditorSettings();

// Codex highlighting
const { entries: codexEntries, refresh: refreshCodexEntries } = useCodexHighlight({
    novelId: props.novel.id,
    enabled: true,
});

// Mention tooltip
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

// Auto-save with reactive scene ID
const currentSceneId = computed(() => currentScene.value?.id || 0);
const { saveStatus, triggerSave, forceSave } = useAutoSave({
    sceneId: currentSceneId,
    debounceMs: 500,
    onSaved: (newWordCount) => {
        wordCount.value = newWordCount;
        emit('wordCountUpdate', newWordCount);
    },
});

// Watch for scene changes and update content
watch(
    () => props.scene?.id,
    (newSceneId, oldSceneId) => {
        // Only update if scene ID actually changed
        if (newSceneId !== oldSceneId && props.scene) {
            currentScene.value = props.scene;
            content.value = props.scene.content || null;
            wordCount.value = props.scene.word_count || 0;
            
            // Update editor content
            if (editorRef.value?.editor) {
                if (props.scene.content) {
                    editorRef.value.editor.commands.setContent(props.scene.content);
                } else {
                    // Set empty content if scene has no content
                    editorRef.value.editor.commands.setContent({
                        type: 'doc',
                        content: [{ type: 'paragraph' }],
                    });
                }
            }
        }
    }
);

const handleEditorUpdate = () => {
    if (editorRef.value && props.scene) {
        const newContent = editorRef.value.editor?.getJSON();
        if (newContent) triggerSave(newContent);
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    // Ctrl+S - Force save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (editorRef.value) {
            const newContent = editorRef.value.editor?.getJSON();
            if (newContent) forceSave(newContent);
        }
    }
    // Ctrl+I - Open metadata panel
    if (e.key === 'i' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        metadataPanelOpen.value = !metadataPanelOpen.value;
    }
    // Ctrl+Shift+C - Quick create (handled by parent)
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        openQuickCreate();
    }
};

const openQuickCreate = () => {
    const editor = editorRef.value?.editor;
    let selectedText = '';
    if (editor) {
        const { from, to } = editor.state.selection;
        selectedText = editor.state.doc.textBetween(from, to, ' ');
    }
    emit('openQuickCreate', selectedText);
};

// Note: Codex creation is handled by parent component via QuickCreateModal
// When a new entry is created, parent should call this to refresh data
const refreshCodexData = () => {
    refreshCodexEntries();
    clearTooltipCache();
};

// Expose for parent component use
defineExpose({ refreshCodexData });

const handleSelectionCreateEntry = (text: string) => {
    emit('openQuickCreate', text);
};

const handleMentionClick = (entryId: number) => {
    closeTooltip();
    emit('openCodexEntry', entryId);
};

const handleTooltipClick = (entryId: number) => {
    handleMentionClick(entryId);
};

const handleEditorContentClick = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (target.classList.contains('codex-mention')) {
        const entryId = target.dataset.entryId;
        if (entryId) {
            handleMentionClick(parseInt(entryId, 10));
        }
    }
};

let cleanupTooltipListeners: (() => void) | null = null;

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);

    if (editorContainerRef.value) {
        cleanupTooltipListeners = setupTooltipListeners(editorContainerRef);
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

// Chat with scene handler
const handleChatWithScene = () => {
    if (currentScene.value?.id) {
        emit('chatWithScene', currentScene.value.id);
    }
};

const handleMetadataUpdated = (updated: Partial<Scene>) => {
    if (currentScene.value) {
        currentScene.value = { ...currentScene.value, ...updated };
    }
};

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
    <div class="flex h-full flex-col">
        <!-- Toolbar -->
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
            @align="handleAlign"
            @open-settings="openSettings"
            @open-info="openMetadata"
            @chat-with-scene="handleChatWithScene"
        />

        <!-- Editor Content -->
        <main ref="editorContainerRef" class="flex-1 overflow-y-auto">
            <div v-if="scene" :class="['mx-auto transition-all duration-300', editorWidthClass]">
                <TipTapEditor
                    ref="editorRef"
                    v-model="content"
                    placeholder="Start writing your story..."
                    :codex-entries="codexEntries"
                    @update="handleEditorUpdate"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="flex h-full items-center justify-center">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No scene selected</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Select a scene from the sidebar to start writing.</p>
                </div>
            </div>
        </main>

        <!-- Mention Tooltip -->
        <MentionTooltip
            :entry="tooltipEntry"
            :target-rect="tooltipTargetRect"
            :container-ref="editorContainerRef"
            @click="handleTooltipClick"
            @close="closeTooltip"
        />

        <!-- Selection Action Menu -->
        <SelectionActionMenu
            :container-ref="editorContainerRef"
            @create-entry="handleSelectionCreateEntry"
        />

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
    </div>
</template>
