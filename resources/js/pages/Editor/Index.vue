<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import TipTapEditor from '@/components/editor/TipTapEditor.vue';
import EditorToolbar from '@/components/editor/EditorToolbar.vue';
import EditorSidebar from '@/components/editor/EditorSidebar.vue';
import EditorSettingsPanel from '@/components/editor/EditorSettingsPanel.vue';
import SceneMetadataPanel from '@/components/editor/SceneMetadataPanel.vue';
import { QuickCreateModal } from '@/components/codex';
import { useAutoSave } from '@/composables/useAutoSave';
import { useEditorSettings } from '@/composables/useEditorSettings';
import { useCodexHighlight } from '@/composables/useCodexHighlight';

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
const sidebarOpen = ref(true);
const settingsPanelOpen = ref(false);
const metadataPanelOpen = ref(false);
const quickCreateOpen = ref(false);
const quickCreateSelectedText = ref('');
const content = ref(props.activeScene?.content || null);
const wordCount = ref(props.activeScene?.word_count || 0);
const currentScene = ref<Scene | null>(props.activeScene);

const { settings, editorStyles } = useEditorSettings();

// Codex highlighting - loads entries for mention highlighting
const { entries: codexEntries, refresh: refreshCodexEntries } = useCodexHighlight({
    novelId: props.novel.id,
    enabled: true,
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

const handleCodexCreated = () => {
    // Refresh codex entries to update highlighting
    refreshCodexEntries();
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeyDown));

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
            />

            <main class="flex-1 overflow-y-auto">
                <div :class="['mx-auto transition-all duration-300', editorWidthClass]">
                    <TipTapEditor ref="editorRef" v-model="content" placeholder="Start writing your story..." :codex-entries="codexEntries" @update="handleEditorUpdate" />
                </div>
            </main>
        </div>

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
    </div>
</template>
