<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import type { Editor } from '@tiptap/vue-3';
import { HIGHLIGHT_COLORS, type HighlightColor } from '@/extensions/HighlightMark';
import { useTextReplacement, type TextReplacementOptions } from '@/composables/useTextReplacement';

interface Props {
    editor: Editor | undefined;
    sceneId?: number;
    enableAI?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    sceneId: undefined,
    enableAI: true,
});

const emit = defineEmits<{
    (e: 'create-snippet', text: string): void;
    (e: 'create-codex-entry', text: string): void;
    (e: 'make-section', text: string): void;
    (e: 'replace-text', newText: string): void;
}>();

// Text replacement composable
const {
    isTransforming,
    transformedText,
    error,
    expandAmounts,
    shortenAmounts,
    transform,
    abort,
    reset,
    fetchOptions,
} = useTextReplacement();

// UI state
const showMenu = ref(false);
const menuPosition = ref({ x: 0, y: 0 });
const showExpandMenu = ref(false);
const showRephraseMenu = ref(false);
const showShortenMenu = ref(false);
const showHighlightMenu = ref(false);
const showPreview = ref(false);

// Store selection range when menu appears (to preserve it when clicking buttons)
const storedSelection = ref<{ from: number; to: number; text: string } | null>(null);

// Get selected text
const selectedText = computed(() => {
    if (!props.editor) return '';
    const { from, to, empty } = props.editor.state.selection;
    if (empty) return '';
    return props.editor.state.doc.textBetween(from, to, ' ');
});

// Word count of selection
const wordCount = computed(() => {
    const text = selectedText.value.trim();
    if (!text) return 0;
    return text.split(/\s+/).filter(Boolean).length;
});

// Can show AI tools (min 4 words)
const canShowAI = computed(() => {
    return props.enableAI && props.sceneId && wordCount.value >= 4;
});

// Debounce timer
let updateTimer: ReturnType<typeof setTimeout> | null = null;

// Update menu visibility based on selection
function updateMenuVisibility() {
    if (!props.editor) {
        showMenu.value = false;
        return;
    }

    const { from, to, empty } = props.editor.state.selection;

    // Don't show when no selection or inside code block
    if (empty || props.editor.isActive('codeBlock')) {
        showMenu.value = false;
        return;
    }

    // Don't show when preview is open
    if (showPreview.value) {
        showMenu.value = false;
        return;
    }

    // Don't show for very short selections (less than 2 chars)
    const selectedLength = to - from;
    if (selectedLength < 2) {
        showMenu.value = false;
        return;
    }

    // Calculate position
    const view = props.editor.view;
    const startCoords = view.coordsAtPos(from);
    const endCoords = view.coordsAtPos(to);

    menuPosition.value = {
        x: (startCoords.left + endCoords.left) / 2,
        y: startCoords.top - 10, // Above the selection
    };

    // Store the selection range and text (preserve for when buttons are clicked)
    const text = props.editor.state.doc.textBetween(from, to, ' ');
    storedSelection.value = { from, to, text };

    showMenu.value = true;
}

// Debounced update function
function debouncedUpdate() {
    if (updateTimer) {
        clearTimeout(updateTimer);
    }
    updateTimer = setTimeout(() => {
        updateMenuVisibility();
    }, 150); // 150ms debounce
}

// Watch editor selection changes with debounce
watch(() => props.editor?.state.selection, () => {
    debouncedUpdate();
}, { deep: true });

// Watch preview state to hide/show menu
watch(showPreview, (isOpen) => {
    if (isOpen) {
        showMenu.value = false;
    } else {
        debouncedUpdate();
    }
});

// Highlight color options
const highlightColorOptions = [
    { name: 'yellow' as HighlightColor, label: 'Yellow', color: HIGHLIGHT_COLORS.yellow },
    { name: 'green' as HighlightColor, label: 'Green', color: HIGHLIGHT_COLORS.green },
    { name: 'blue' as HighlightColor, label: 'Blue', color: HIGHLIGHT_COLORS.blue },
    { name: 'pink' as HighlightColor, label: 'Pink', color: HIGHLIGHT_COLORS.pink },
    { name: 'orange' as HighlightColor, label: 'Orange', color: HIGHLIGHT_COLORS.orange },
    { name: 'purple' as HighlightColor, label: 'Purple', color: HIGHLIGHT_COLORS.purple },
];

// Formatting state
const isBold = computed(() => props.editor?.isActive('bold') ?? false);
const isItalic = computed(() => props.editor?.isActive('italic') ?? false);
const isUnderline = computed(() => props.editor?.isActive('underline') ?? false);
const isStrike = computed(() => props.editor?.isActive('strike') ?? false);
const isHighlight = computed(() => props.editor?.isActive('highlight') ?? false);
const currentHighlightColor = computed(() => {
    if (!props.editor) return null;
    const attrs = props.editor.getAttributes('highlight');
    return attrs?.color || null;
});

// Formatting actions
const toggleBold = () => props.editor?.chain().focus().toggleBold().run();
const toggleItalic = () => props.editor?.chain().focus().toggleItalic().run();
const toggleUnderline = () => props.editor?.chain().focus().toggleUnderline().run();
const toggleStrike = () => props.editor?.chain().focus().toggleStrike().run();

const setHighlightColor = (color: string) => {
    props.editor?.chain().focus().setHighlight(color).run();
    showHighlightMenu.value = false;
};

const removeHighlight = () => {
    props.editor?.chain().focus().unsetHighlight().run();
    showHighlightMenu.value = false;
};

// Close all dropdowns
const closeAllMenus = () => {
    showExpandMenu.value = false;
    showRephraseMenu.value = false;
    showShortenMenu.value = false;
    showHighlightMenu.value = false;
};

// AI Transformation handlers
async function handleExpand(amount: string, method?: string) {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'expand',
        expand_amount: amount,
        expand_method: method,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleRephrase(options: string[] = []) {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'rephrase',
        rephrase_options: options,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleShorten(amount: string) {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'shorten',
        shorten_amount: amount,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

// Quick AI actions (new ones)
async function handleRateify() {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'rephrase',
        rephrase_options: ['add_emotion', 'dramatic_tension'],
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleDictatify() {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'rephrase',
        rephrase_options: ['convert_to_dialogue'],
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleShowDontTell() {
    closeAllMenus();
    showPreview.value = true;

    await transform(selectedText.value, {
        type: 'rephrase',
        rephrase_options: ['show_dont_tell'],
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

// Preview actions
function handleAcceptTransformation() {
    if (transformedText.value && props.editor) {
        props.editor.chain().focus().deleteSelection().insertContent(transformedText.value).run();
        emit('replace-text', transformedText.value);
        handleClosePreview();
    }
}

function handleRetry() {
    reset();
    showPreview.value = false;
}

function handleClosePreview() {
    reset();
    showPreview.value = false;
}

// Action handlers
function handleCreateSnippet() {
    closeAllMenus();
    emit('create-snippet', selectedText.value);
}

function handleCreateCodexEntry() {
    closeAllMenus();
    emit('create-codex-entry', selectedText.value);
}

function handleMakeSection() {
    if (!props.editor || !storedSelection.value) return;

    const { from, to, text } = storedSelection.value;
    closeAllMenus();

    // Create section node with the stored text as content
    const { state, view } = props.editor;
    const sectionType = state.schema.nodes.section;
    const paragraphType = state.schema.nodes.paragraph;

    if (sectionType && paragraphType) {
        // Create paragraph with the text
        const paragraphNode = paragraphType.create(null, text ? state.schema.text(text) : null);

        // Create section with the paragraph
        const sectionNode = sectionType.create({
            type: 'content',
            color: '#6366f1',
            isCollapsed: false,
            excludeFromAi: false,
            isCompleted: false,
        }, paragraphNode);

        // Replace the selection with the section
        const tr = state.tr.replaceWith(from, to, sectionNode);
        view.dispatch(tr);
        props.editor.commands.focus();
    }
}

// Fetch options on mount
onMounted(async () => {
    await fetchOptions();
});

// Menu style for positioning
const menuStyle = computed(() => ({
    position: 'fixed' as const,
    left: `${menuPosition.value.x}px`,
    top: `${menuPosition.value.y}px`,
    transform: 'translateX(-50%) translateY(-100%)',
    zIndex: 50,
}));
</script>

<template>
    <div>
        <!-- Main Bubble Menu -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showMenu && editor"
                :style="menuStyle"
                class="selection-bubble-menu bg-zinc-900 dark:bg-zinc-800 rounded-lg shadow-xl border border-zinc-700 overflow-hidden">
                <!-- Row 1: Word count and basic info -->
                <div class="flex items-center justify-between px-2 py-1 border-b border-zinc-700 bg-zinc-800/50">
                    <span class="text-xs font-medium text-zinc-300">
                        {{ wordCount }} {{ wordCount === 1 ? 'Word' : 'Words' }}
                    </span>
                </div>

                <!-- Row 2: Formatting buttons -->
                <div class="flex items-center gap-0.5 px-1.5 py-1 border-b border-zinc-700">
                    <!-- Bold -->
                    <button
                        type="button"
                        :class="[
                            'p-1.5 rounded transition-colors',
                            isBold ? 'bg-violet-600 text-white' : 'text-zinc-300 hover:bg-zinc-700',
                        ]"
                        title="Bold (Ctrl+B)"
                        @click="toggleBold"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
                        </svg>
                    </button>

                    <!-- Italic -->
                    <button
                        type="button"
                        :class="[
                            'p-1.5 rounded transition-colors',
                            isItalic ? 'bg-violet-600 text-white' : 'text-zinc-300 hover:bg-zinc-700',
                        ]"
                        title="Italic (Ctrl+I)"
                        @click="toggleItalic"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="4" x2="10" y2="4" />
                            <line x1="14" y1="20" x2="5" y2="20" />
                            <line x1="15" y1="4" x2="9" y2="20" />
                        </svg>
                    </button>

                    <!-- Underline -->
                    <button
                        type="button"
                        :class="[
                            'p-1.5 rounded transition-colors',
                            isUnderline ? 'bg-violet-600 text-white' : 'text-zinc-300 hover:bg-zinc-700',
                        ]"
                        title="Underline (Ctrl+U)"
                        @click="toggleUnderline"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" />
                            <line x1="4" y1="21" x2="20" y2="21" />
                        </svg>
                    </button>

                    <!-- Strikethrough -->
                    <button
                        type="button"
                        :class="[
                            'p-1.5 rounded transition-colors',
                            isStrike ? 'bg-violet-600 text-white' : 'text-zinc-300 hover:bg-zinc-700',
                        ]"
                        title="Strikethrough"
                        @click="toggleStrike"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 4H9a3 3 0 00-3 3v1a3 3 0 003 3h6a3 3 0 013 3v1a3 3 0 01-3 3H6" />
                            <line x1="4" y1="12" x2="20" y2="12" />
                        </svg>
                    </button>

                    <!-- Highlight with dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            :class="[
                                'flex items-center gap-0.5 p-1.5 rounded transition-colors',
                                isHighlight ? 'bg-violet-600 text-white' : 'text-zinc-300 hover:bg-zinc-700',
                            ]"
                            title="Highlight"
                            @click="showHighlightMenu = !showHighlightMenu; closeAllMenus(); showHighlightMenu = true"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Highlight color dropdown -->
                        <div
                            v-if="showHighlightMenu"
                            class="absolute top-full left-0 mt-1 p-2 bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 z-10"
                        >
                            <div class="grid grid-cols-3 gap-1 mb-2">
                                <button
                                    v-for="option in highlightColorOptions"
                                    :key="option.name"
                                    type="button"
                                    class="w-6 h-6 rounded-md border border-zinc-600 transition-transform hover:scale-110"
                                    :style="{ backgroundColor: option.color }"
                                    :title="option.label"
                                    @click="setHighlightColor(option.color)"
                                />
                            </div>
                            <button
                                v-if="isHighlight"
                                type="button"
                                class="w-full flex items-center gap-1.5 px-2 py-1 text-xs text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 rounded transition-colors"
                                @click="removeHighlight"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>

                    <div class="w-px h-5 bg-zinc-700 mx-1" />

                    <!-- Action buttons -->
                    <button
                        type="button"
                        class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Create Snippet from selection"
                        @click="handleCreateSnippet"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Snippet
                    </button>

                    <button
                        type="button"
                        class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Create Codex Entry from selection"
                        @click="handleCreateCodexEntry"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Codex Entry
                    </button>

                    <button
                        type="button"
                        class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Convert selection to Section"
                        @click="handleMakeSection"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <line x1="3" y1="9" x2="21" y2="9" />
                        </svg>
                        Section
                    </button>
                </div>

                <!-- Row 3: AI Writing Tools (shown when >= 4 words selected) -->
                <div v-if="canShowAI" class="flex items-center gap-0.5 px-1.5 py-1">
                    <!-- Expand dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                            @click="showExpandMenu = !showExpandMenu; closeAllMenus(); showExpandMenu = true"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            Expand
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            v-if="showExpandMenu"
                            class="absolute top-full left-0 mt-1 w-44 bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 overflow-hidden z-10"
                        >
                            <div class="py-1">
                                <div class="px-3 py-1 text-[10px] font-semibold text-zinc-500 uppercase">Amount</div>
                                <button
                                    v-for="(label, key) in expandAmounts"
                                    :key="key"
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleExpand(key)"
                                >
                                    {{ label }}
                                </button>
                            </div>
                            <div class="border-t border-zinc-700 py-1">
                                <div class="px-3 py-1 text-[10px] font-semibold text-zinc-500 uppercase">Method</div>
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleExpand('double', 'sensory_details')"
                                >
                                    + Sensory details
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleExpand('double', 'inner_thoughts')"
                                >
                                    + Inner thoughts
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleExpand('double', 'dialogue')"
                                >
                                    + Dialogue
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Rephrase dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                            @click="showRephraseMenu = !showRephraseMenu; closeAllMenus(); showRephraseMenu = true"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Rephrase
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            v-if="showRephraseMenu"
                            class="absolute top-full left-0 mt-1 w-48 bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 overflow-hidden z-10"
                        >
                            <div class="py-1">
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleRephrase(['different_words'])"
                                >
                                    Use different words
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleRephrase(['passive_to_active'])"
                                >
                                    Passive → Active voice
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleRephrase([])"
                                >
                                    Rephrase naturally
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Shorten dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                            @click="showShortenMenu = !showShortenMenu; closeAllMenus(); showShortenMenu = true"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            Shorten
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            v-if="showShortenMenu"
                            class="absolute top-full left-0 mt-1 w-40 bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 overflow-hidden z-10"
                        >
                            <div class="py-1">
                                <button
                                    v-for="(label, key) in shortenAmounts"
                                    :key="key"
                                    type="button"
                                    class="block w-full px-3 py-1.5 text-xs text-left text-zinc-300 hover:bg-zinc-700 transition-colors"
                                    @click="handleShorten(key)"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="w-px h-5 bg-zinc-700 mx-0.5" />

                    <!-- Quick AI actions -->
                    <button
                        type="button"
                        class="px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Add dramatic tension and emotion"
                        @click="handleRateify"
                    >
                        Rate-ify
                    </button>

                    <button
                        type="button"
                        class="px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Convert to dialogue"
                        @click="handleDictatify"
                    >
                        Dictat-ify
                    </button>

                    <button
                        type="button"
                        class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-zinc-300 hover:bg-zinc-700 rounded transition-colors"
                        title="Show, don't tell - transform telling into showing"
                        @click="handleShowDontTell"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Show, don't tell
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Preview Modal for AI Transformation -->
        <Teleport to="body">
            <div
                v-if="showPreview"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="handleClosePreview"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Text Transformation</h3>
                        <button
                            type="button"
                            class="p-1 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                            @click="handleClosePreview"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-auto p-4 space-y-4">
                        <!-- Original text -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Original</label>
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-md text-sm text-zinc-700 dark:text-zinc-300 max-h-32 overflow-y-auto">
                                {{ selectedText }}
                            </div>
                        </div>

                        <!-- Transformed text -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ isTransforming ? 'Generating...' : 'Replacement' }}
                            </label>
                            <div class="p-3 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-md text-sm text-zinc-700 dark:text-zinc-300 max-h-48 overflow-y-auto">
                                <template v-if="isTransforming && !transformedText">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-zinc-500">Transforming text...</span>
                                    </div>
                                </template>
                                <template v-else>
                                    {{ transformedText }}
                                    <span v-if="isTransforming" class="inline-block w-2 h-4 bg-violet-500 animate-pulse ml-0.5"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <button
                            type="button"
                            class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                            :disabled="isTransforming"
                            @click="handleClosePreview"
                        >
                            Cancel
                        </button>
                        <button
                            v-if="!isTransforming && transformedText"
                            type="button"
                            class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                            @click="handleRetry"
                        >
                            Retry
                        </button>
                        <button
                            v-if="isTransforming"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                            @click="abort"
                        >
                            Stop
                        </button>
                        <button
                            v-else
                            type="button"
                            :disabled="!transformedText"
                            class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 disabled:bg-violet-400 disabled:cursor-not-allowed rounded-md transition-colors"
                            @click="handleAcceptTransformation"
                        >
                            Accept
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.selection-bubble-menu {
    animation: fadeIn 0.1s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
