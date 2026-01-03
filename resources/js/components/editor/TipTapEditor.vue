<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import { CodexHighlight, type CodexEntry } from '@/extensions/CodexHighlight';
import { SectionNode } from '@/extensions/SectionNode';
import { SlashCommands, createSlashCommandsSuggestion, createAllSlashCommands, type AICommandEvent } from '@/extensions/SlashCommands';
import { HighlightMark } from '@/extensions/HighlightMark';
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import ProseGenerationPanel from './ProseGenerationPanel.vue';
import TextReplacementMenu from './TextReplacementMenu.vue';

interface Props {
    modelValue: object | null;
    placeholder?: string;
    editable?: boolean;
    autofocus?: boolean;
    codexEntries?: CodexEntry[];
    sceneId?: number;
    enableAI?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    placeholder: 'Start writing your story...',
    editable: true,
    autofocus: true,
    codexEntries: () => [],
    sceneId: undefined,
    enableAI: true,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: object): void;
    (e: 'update', editor: ReturnType<typeof useEditor>): void;
    (e: 'focus'): void;
    (e: 'blur'): void;
}>();

// AI Feature State
const showProseGeneration = ref(false);
const proseGenerationMode = ref<'scene_beat' | 'continue' | 'custom'>('scene_beat');
const contentBeforeCursor = ref('');
const beatContentForExpansion = ref('');

const showTextReplacement = ref(false);
const selectedText = ref('');
const selectionPosition = ref({ x: 0, y: 0 });

// AI Command handler
function handleAICommand(event: AICommandEvent) {
    if (!props.enableAI || !props.sceneId) return;
    
    // Get content before cursor for context
    if (editor.value) {
        const { from } = editor.value.state.selection;
        const textBefore = editor.value.state.doc.textBetween(0, from, ' ');
        contentBeforeCursor.value = textBefore;
    }
    
    proseGenerationMode.value = event.type === 'scene-beat' ? 'scene_beat' : event.type === 'continue' ? 'continue' : 'custom';
    showProseGeneration.value = true;
}

// Create slash commands with AI callback
const slashCommands = computed(() => {
    if (props.enableAI && props.sceneId) {
        return createAllSlashCommands(handleAICommand);
    }
    return createAllSlashCommands();
});

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    autofocus: props.autofocus ? 'end' : false,
    extensions: [
        StarterKit.configure({
            history: {
                depth: 100,
            },
        }),
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
            emptyEditorClass: 'is-editor-empty',
        }),
        CharacterCount,
        CodexHighlight.configure({
            entries: props.codexEntries,
        }),
        SectionNode,
        SlashCommands.configure({
            suggestion: createSlashCommandsSuggestion(slashCommands.value),
        }),
        HighlightMark,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-zinc dark:prose-invert max-w-none focus:outline-none min-h-[calc(100vh-16rem)] px-4 py-8 md:px-8 lg:px-16',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getJSON());
        emit('update', editor);
    },
    onFocus: () => {
        emit('focus');
    },
    onBlur: () => {
        emit('blur');
    },
    onSelectionUpdate: ({ editor }) => {
        // Handle text selection for replacement menu
        if (!props.enableAI || !props.sceneId) return;
        
        const { from, to, empty } = editor.state.selection;
        
        if (empty) {
            showTextReplacement.value = false;
            selectedText.value = '';
            return;
        }
        
        const text = editor.state.doc.textBetween(from, to, ' ');
        const wordCount = text.trim().split(/\s+/).filter(Boolean).length;
        
        // Only show replacement menu for 4+ words
        if (wordCount >= 4) {
            selectedText.value = text;
            
            // Get selection position for menu placement
            const view = editor.view;
            const coords = view.coordsAtPos(from);
            const editorRect = view.dom.getBoundingClientRect();
            
            selectionPosition.value = {
                x: (coords.left + view.coordsAtPos(to).left) / 2 - editorRect.left,
                y: coords.top - editorRect.top,
            };
            
            showTextReplacement.value = true;
        } else {
            showTextReplacement.value = false;
        }
    },
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (!editor.value) return;
        const currentContent = JSON.stringify(editor.value.getJSON());
        const newContent = JSON.stringify(newValue);
        if (currentContent !== newContent) {
            editor.value.commands.setContent(newValue || {});
        }
    },
    { deep: true }
);

watch(
    () => props.editable,
    (newValue) => {
        editor.value?.setEditable(newValue);
    }
);

// Watch for codex entries changes and update the extension
watch(
    () => props.codexEntries,
    (newEntries) => {
        if (editor.value) {
            // Re-configure the CodexHighlight extension with new entries
            // This will trigger a re-render of decorations
            const codexHighlightExtension = editor.value.extensionManager.extensions.find(
                (ext) => ext.name === 'codexHighlight'
            );
            if (codexHighlightExtension) {
                codexHighlightExtension.options.entries = newEntries;
                // Force editor to re-render decorations
                const { state } = editor.value;
                const tr = state.tr;
                editor.value.view.dispatch(tr);
            }
        }
    },
    { deep: true }
);

const wordCount = computed(() => {
    return editor.value?.storage.characterCount.words() ?? 0;
});

const characterCount = computed(() => {
    return editor.value?.storage.characterCount.characters() ?? 0;
});

// Undo/Redo
const undo = () => editor.value?.chain().focus().undo().run();
const redo = () => editor.value?.chain().focus().redo().run();
const canUndo = computed(() => editor.value?.can().undo() ?? false);
const canRedo = computed(() => editor.value?.can().redo() ?? false);

// Basic formatting
const toggleBold = () => editor.value?.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run();
const toggleUnderline = () => editor.value?.chain().focus().toggleUnderline().run();
const toggleStrike = () => editor.value?.chain().focus().toggleStrike().run();

const isBold = computed(() => editor.value?.isActive('bold') ?? false);
const isItalic = computed(() => editor.value?.isActive('italic') ?? false);
const isUnderline = computed(() => editor.value?.isActive('underline') ?? false);
const isStrike = computed(() => editor.value?.isActive('strike') ?? false);

// Headings
const setHeading = (level: 1 | 2 | 3) => editor.value?.chain().focus().toggleHeading({ level }).run();
const setParagraph = () => editor.value?.chain().focus().setParagraph().run();
const currentHeadingLevel = computed(() => {
    if (editor.value?.isActive('heading', { level: 1 })) return 1;
    if (editor.value?.isActive('heading', { level: 2 })) return 2;
    if (editor.value?.isActive('heading', { level: 3 })) return 3;
    return 0;
});

// Lists
const toggleBulletList = () => editor.value?.chain().focus().toggleBulletList().run();
const toggleOrderedList = () => editor.value?.chain().focus().toggleOrderedList().run();
const isBulletList = computed(() => editor.value?.isActive('bulletList') ?? false);
const isOrderedList = computed(() => editor.value?.isActive('orderedList') ?? false);

// Text alignment
const setTextAlign = (align: 'left' | 'center' | 'right' | 'justify') => {
    editor.value?.chain().focus().setTextAlign(align).run();
};
const currentTextAlign = computed(() => {
    if (editor.value?.isActive({ textAlign: 'center' })) return 'center';
    if (editor.value?.isActive({ textAlign: 'right' })) return 'right';
    if (editor.value?.isActive({ textAlign: 'justify' })) return 'justify';
    return 'left';
});

// Sections
const insertSection = (type: 'content' | 'note' | 'alternative' | 'beat' = 'content') => {
    editor.value?.chain().focus().insertSection({ type }).run();
};

// Blockquote
const toggleBlockquote = () => editor.value?.chain().focus().toggleBlockquote().run();
const isBlockquote = computed(() => editor.value?.isActive('blockquote') ?? false);

// Highlight
const setHighlight = (color: string) => editor.value?.chain().focus().setHighlight(color).run();
const toggleHighlight = (color: string) => editor.value?.chain().focus().toggleHighlight(color).run();
const unsetHighlight = () => editor.value?.chain().focus().unsetHighlight().run();
const isHighlight = computed(() => editor.value?.isActive('highlight') ?? false);
const currentHighlightColor = computed(() => {
    if (!editor.value) return null;
    const attrs = editor.value.getAttributes('highlight');
    return attrs?.color || null;
});

// AI Prose Generation handlers
function handleApplyProse(content: string) {
    if (!editor.value) return;
    
    editor.value.chain().focus().insertContent(content).run();
    showProseGeneration.value = false;
}

function handleDiscardProse() {
    showProseGeneration.value = false;
}

function handleAddToSection(content: string, sectionType: string) {
    if (!editor.value) return;
    
    // Insert as a new section with the generated content
    editor.value.chain().focus().insertSection({ 
        type: sectionType as 'content' | 'note' | 'alternative' | 'beat',
        content: content 
    }).run();
    
    showProseGeneration.value = false;
}

function handleCloseProseGeneration() {
    showProseGeneration.value = false;
    beatContentForExpansion.value = '';
}

// Text Replacement handlers
function handleReplaceText(newText: string) {
    if (!editor.value) return;
    
    editor.value.chain().focus().deleteSelection().insertContent(newText).run();
    showTextReplacement.value = false;
    selectedText.value = '';
}

function handleCloseTextReplacement() {
    showTextReplacement.value = false;
    selectedText.value = '';
}

// Open prose generation programmatically
function openProseGeneration(mode: 'scene_beat' | 'continue' | 'custom' = 'scene_beat') {
    if (!props.enableAI || !props.sceneId) return;
    
    if (editor.value) {
        const { from } = editor.value.state.selection;
        const textBefore = editor.value.state.doc.textBetween(0, from, ' ');
        contentBeforeCursor.value = textBefore;
    }
    
    proseGenerationMode.value = mode;
    showProseGeneration.value = true;
}

// Handle expand beat to prose event from SectionNodeView
const editorContainerRef = ref<HTMLElement | null>(null);

const handleExpandBeatToProse = (event: CustomEvent) => {
    if (!props.enableAI || !props.sceneId) return;
    
    const { content } = event.detail;
    beatContentForExpansion.value = content;
    proseGenerationMode.value = 'scene_beat';
    showProseGeneration.value = true;
};

onMounted(() => {
    // Listen for expand-beat-to-prose custom events
    editorContainerRef.value?.addEventListener('expand-beat-to-prose', handleExpandBeatToProse as EventListener);
});

onBeforeUnmount(() => {
    editorContainerRef.value?.removeEventListener('expand-beat-to-prose', handleExpandBeatToProse as EventListener);
    editor.value?.destroy();
});

defineExpose({
    editor,
    wordCount,
    characterCount,
    // Undo/Redo
    undo,
    redo,
    canUndo,
    canRedo,
    // Basic formatting
    toggleBold,
    toggleItalic,
    toggleUnderline,
    toggleStrike,
    isBold,
    isItalic,
    isUnderline,
    isStrike,
    // Headings
    setHeading,
    setParagraph,
    currentHeadingLevel,
    // Lists
    toggleBulletList,
    toggleOrderedList,
    isBulletList,
    isOrderedList,
    // Blockquote
    toggleBlockquote,
    isBlockquote,
    // Highlight
    setHighlight,
    toggleHighlight,
    unsetHighlight,
    isHighlight,
    currentHighlightColor,
    // Text alignment
    setTextAlign,
    currentTextAlign,
    // Sections
    insertSection,
    // AI features
    openProseGeneration,
    showProseGeneration,
    showTextReplacement,
});
</script>

<template>
    <div ref="editorContainerRef" class="tiptap-editor relative">
        <EditorContent :editor="editor" />
        
        <!-- Prose Generation Panel -->
        <div v-if="enableAI && sceneId" class="prose-generation-container">
            <ProseGenerationPanel
                :scene-id="sceneId"
                :mode="proseGenerationMode"
                :content-before="contentBeforeCursor"
                :initial-beat="beatContentForExpansion"
                :is-visible="showProseGeneration"
                @apply="handleApplyProse"
                @discard="handleDiscardProse"
                @add-to-section="handleAddToSection"
                @close="handleCloseProseGeneration"
            />
        </div>
        
        <!-- Text Replacement Menu -->
        <TextReplacementMenu
            v-if="enableAI && sceneId"
            :selected-text="selectedText"
            :position="selectionPosition"
            :scene-id="sceneId"
            :is-visible="showTextReplacement"
            @replace="handleReplaceText"
            @close="handleCloseTextReplacement"
        />
    </div>
</template>

<style>
.tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #a1a1aa;
    pointer-events: none;
    height: 0;
}

.dark .tiptap p.is-editor-empty:first-child::before {
    color: #71717a;
}

.tiptap:focus {
    outline: none;
}

.tiptap ::selection {
    background-color: #ddd6fe;
}

.dark .tiptap ::selection {
    background-color: #5b21b6;
}

.tiptap {
    font-family: var(--editor-font-family, 'Georgia', serif);
    font-size: var(--editor-font-size, 1.125rem);
    line-height: var(--editor-line-height, 1.8);
}

.tiptap p {
    margin-bottom: 1em;
}

.tiptap h1,
.tiptap h2,
.tiptap h3 {
    font-family: system-ui, -apple-system, sans-serif;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}

/* Text alignment */
.tiptap p[style*="text-align: left"],
.tiptap h1[style*="text-align: left"],
.tiptap h2[style*="text-align: left"],
.tiptap h3[style*="text-align: left"] {
    text-align: left;
}

.tiptap p[style*="text-align: center"],
.tiptap h1[style*="text-align: center"],
.tiptap h2[style*="text-align: center"],
.tiptap h3[style*="text-align: center"] {
    text-align: center;
}

.tiptap p[style*="text-align: right"],
.tiptap h1[style*="text-align: right"],
.tiptap h2[style*="text-align: right"],
.tiptap h3[style*="text-align: right"] {
    text-align: right;
}

.tiptap p[style*="text-align: justify"],
.tiptap h1[style*="text-align: justify"],
.tiptap h2[style*="text-align: justify"],
.tiptap h3[style*="text-align: justify"] {
    text-align: justify;
}

/* List styles */
.tiptap ul {
    list-style-type: disc;
    padding-left: 1.5em;
    margin-bottom: 1em;
}

.tiptap ol {
    list-style-type: decimal;
    padding-left: 1.5em;
    margin-bottom: 1em;
}

.tiptap li {
    margin-bottom: 0.25em;
}

.tiptap li p {
    margin-bottom: 0.25em;
}

/* Codex mention highlighting */
.codex-mention {
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.codex-mention:hover {
    background-color: rgba(139, 92, 246, 0.15);
}

.dark .codex-mention:hover {
    background-color: rgba(139, 92, 246, 0.25);
}

/* Highlight mark */
.tiptap mark[data-highlight] {
    border-radius: 2px;
    padding: 0 2px;
}

/* Blockquote */
.tiptap blockquote {
    border-left: 3px solid #a78bfa;
    padding-left: 1rem;
    margin-left: 0;
    margin-right: 0;
    font-style: italic;
    color: #71717a;
}

.dark .tiptap blockquote {
    border-left-color: #8b5cf6;
    color: #a1a1aa;
}

/* Prose generation panel positioning */
.prose-generation-container {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 40;
    max-width: 100%;
    padding: 0 1rem;
}

@media (min-width: 768px) {
    .prose-generation-container {
        bottom: 4rem;
    }
}
</style>
