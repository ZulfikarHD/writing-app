<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useTextReplacement, type TextReplacementOptions } from '@/composables/useTextReplacement';

interface Props {
    selectedText: string;
    position?: { x: number; y: number };
    sceneId?: number;
    isVisible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    position: () => ({ x: 0, y: 0 }),
    sceneId: undefined,
    isVisible: false,
});

const emit = defineEmits<{
    (e: 'replace', newText: string): void;
    (e: 'close'): void;
}>();

const {
    isTransforming,
    originalText,
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
const showExpandMenu = ref(false);
const showRephraseMenu = ref(false);
const showShortenMenu = ref(false);
const showMoreOptions = ref(false);
const showPreview = ref(false);

// Rephrase selections
const selectedRephraseOptions = ref<string[]>([]);

// Methods
async function handleExpand(amount: string, method?: string) {
    showExpandMenu.value = false;
    showPreview.value = true;
    
    await transform(props.selectedText, {
        type: 'expand',
        expand_amount: amount,
        expand_method: method,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleRephrase(options: string[] = []) {
    showRephraseMenu.value = false;
    showPreview.value = true;
    
    await transform(props.selectedText, {
        type: 'rephrase',
        rephrase_options: options.length > 0 ? options : selectedRephraseOptions.value,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

async function handleShorten(amount: string) {
    showShortenMenu.value = false;
    showPreview.value = true;
    
    await transform(props.selectedText, {
        type: 'shorten',
        shorten_amount: amount,
        scene_id: props.sceneId,
    } as TextReplacementOptions);
}

function handleAccept() {
    if (transformedText.value) {
        emit('replace', transformedText.value);
        handleClose();
    }
}

function handleRetry() {
    reset();
    showPreview.value = false;
}

function handleClose() {
    reset();
    showExpandMenu.value = false;
    showRephraseMenu.value = false;
    showShortenMenu.value = false;
    showMoreOptions.value = false;
    showPreview.value = false;
    selectedRephraseOptions.value = [];
    emit('close');
}

function closeAllMenus() {
    showExpandMenu.value = false;
    showRephraseMenu.value = false;
    showShortenMenu.value = false;
    showMoreOptions.value = false;
}

// Word count check
const wordCount = computed(() => {
    return props.selectedText.trim().split(/\s+/).filter(Boolean).length;
});

const canShowMenu = computed(() => {
    return wordCount.value >= 4;
});

// Position styling
const menuStyle = computed(() => ({
    left: `${props.position.x}px`,
    top: `${props.position.y}px`,
}));

// Watch visibility
watch(() => props.isVisible, async (visible) => {
    if (visible) {
        await fetchOptions();
    } else {
        handleClose();
    }
});

// Click outside handler
function handleClickOutside(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('.text-replacement-menu') && !target.closest('.text-replacement-preview')) {
        handleClose();
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <!-- Main menu bubble -->
    <div
        v-if="isVisible && canShowMenu && !showPreview"
        class="text-replacement-menu fixed z-50 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl"
        :style="menuStyle"
    >
        <div class="flex items-center gap-1 p-1">
            <!-- Expand button with dropdown -->
            <div class="relative">
                <button
                    @click="showExpandMenu = !showExpandMenu; closeAllMenus(); showExpandMenu = true"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
                    title="Expand selected text"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    Expand
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Expand dropdown -->
                <div
                    v-if="showExpandMenu"
                    class="absolute top-full left-0 mt-1 w-48 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden z-10"
                >
                    <div class="py-1">
                        <div class="px-3 py-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase">Amount</div>
                        <button
                            v-for="(label, key) in expandAmounts"
                            :key="key"
                            @click="handleExpand(key)"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            {{ label }}
                        </button>
                    </div>
                    <div class="border-t border-zinc-200 dark:border-zinc-700 py-1">
                        <div class="px-3 py-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase">Method</div>
                        <button
                            @click="handleExpand('double', 'sensory_details')"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Add sensory details
                        </button>
                        <button
                            @click="handleExpand('double', 'inner_thoughts')"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Add inner thoughts
                        </button>
                        <button
                            @click="handleExpand('double', 'description')"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            More description
                        </button>
                        <button
                            @click="handleExpand('double', 'dialogue')"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Add dialogue
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rephrase button with dropdown -->
            <div class="relative">
                <button
                    @click="showRephraseMenu = !showRephraseMenu; closeAllMenus(); showRephraseMenu = true"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
                    title="Rephrase selected text"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Rephrase
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Rephrase dropdown -->
                <div
                    v-if="showRephraseMenu"
                    class="absolute top-full left-0 mt-1 w-56 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden z-10"
                >
                    <div class="py-1">
                        <button
                            @click="handleRephrase(['different_words'])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Use different words
                        </button>
                        <button
                            @click="handleRephrase(['show_dont_tell'])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Show, don't tell
                        </button>
                        <button
                            @click="handleRephrase(['add_inner_thoughts'])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Add inner thoughts
                        </button>
                        <button
                            @click="handleRephrase(['convert_to_dialogue'])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Convert to dialogue
                        </button>
                        <button
                            @click="handleRephrase(['passive_to_active'])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Passive to active voice
                        </button>
                    </div>
                    <div class="border-t border-zinc-200 dark:border-zinc-700 py-1">
                        <button
                            @click="handleRephrase([])"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            Rephrase naturally
                        </button>
                    </div>
                </div>
            </div>

            <!-- Shorten button with dropdown -->
            <div class="relative">
                <button
                    @click="showShortenMenu = !showShortenMenu; closeAllMenus(); showShortenMenu = true"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
                    title="Shorten selected text"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    Shorten
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Shorten dropdown -->
                <div
                    v-if="showShortenMenu"
                    class="absolute top-full left-0 mt-1 w-44 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden z-10"
                >
                    <div class="py-1">
                        <button
                            v-for="(label, key) in shortenAmounts"
                            :key="key"
                            @click="handleShorten(key)"
                            class="block w-full px-3 py-2 text-sm text-left text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>

            <!-- Close button -->
            <button
                @click="handleClose"
                class="p-1.5 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition-colors"
                title="Close"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Word count warning -->
        <div v-if="wordCount < 4 && wordCount > 0" class="px-3 py-2 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border-t border-zinc-200 dark:border-zinc-700">
            Select at least 4 words to use text replacement
        </div>
    </div>

    <!-- Preview panel -->
    <div
        v-if="showPreview"
        class="text-replacement-preview fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Text Replacement</h3>
                <button
                    @click="handleClose"
                    class="p-1 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
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
                        {{ originalText || selectedText }}
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
                    @click="handleClose"
                    class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                    :disabled="isTransforming"
                >
                    Cancel
                </button>
                <button
                    v-if="!isTransforming && transformedText"
                    @click="handleRetry"
                    class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                >
                    Retry
                </button>
                <button
                    v-if="isTransforming"
                    @click="abort"
                    class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                >
                    Stop
                </button>
                <button
                    v-else
                    @click="handleAccept"
                    :disabled="!transformedText"
                    class="px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 disabled:bg-violet-400 disabled:cursor-not-allowed rounded-md transition-colors"
                >
                    Accept
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-replacement-menu {
    transform: translateX(-50%) translateY(-100%);
    margin-top: -8px;
}
</style>
