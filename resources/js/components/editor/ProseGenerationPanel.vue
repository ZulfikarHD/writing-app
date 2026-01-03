<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useProseGeneration, type ProseGenerationOptions } from '@/composables/useProseGeneration';

interface Props {
    sceneId: number;
    mode?: 'scene_beat' | 'continue' | 'custom';
    contentBefore?: string;
    isVisible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'scene_beat',
    contentBefore: '',
    isVisible: false,
});

const emit = defineEmits<{
    (e: 'apply', content: string): void;
    (e: 'discard'): void;
    (e: 'add-to-section', content: string, sectionType: string): void;
    (e: 'close'): void;
}>();

const {
    isGenerating,
    generatedContent,
    error,
    availablePrompts,
    availableConnections,
    availableModes,
    generate,
    abort,
    reset,
    fetchOptions,
} = useProseGeneration();

// Form state
const beat = ref('');
const instructions = ref('');
const selectedMode = ref(props.mode);
const selectedPromptId = ref<number | null>(null);
const selectedConnectionId = ref<number | null>(null);
const selectedModel = ref<string | null>(null);
const showAdvanced = ref(false);

// UI state
const showPromptMenu = ref(false);
const showSectionMenu = ref(false);

// Computed
const canGenerate = computed(() => {
    if (isGenerating.value) return false;
    if (selectedMode.value === 'scene_beat' && !beat.value.trim()) return false;
    return true;
});

const hasGeneratedContent = computed(() => generatedContent.value.length > 0);

const defaultConnection = computed(() => 
    availableConnections.value.find(c => c.is_default) || availableConnections.value[0]
);

const modeLabel = computed(() => {
    const mode = availableModes.value[selectedMode.value];
    return mode?.name || 'Generate';
});

// Methods
async function handleGenerate() {
    if (!canGenerate.value) return;

    const options: ProseGenerationOptions = {
        mode: selectedMode.value,
        beat: beat.value,
        instructions: instructions.value,
        content_before: props.contentBefore,
    };

    if (selectedPromptId.value) {
        options.prompt_id = selectedPromptId.value;
    }
    if (selectedConnectionId.value) {
        options.connection_id = selectedConnectionId.value;
    }
    if (selectedModel.value) {
        options.model = selectedModel.value;
    }

    await generate(props.sceneId, options);
}

function handleApply() {
    if (hasGeneratedContent.value) {
        emit('apply', generatedContent.value);
        handleClose();
    }
}

function handleRetry() {
    reset();
    handleGenerate();
}

function handleDiscard() {
    reset();
    emit('discard');
}

function handleAddToSection(sectionType: string) {
    if (hasGeneratedContent.value) {
        emit('add-to-section', generatedContent.value, sectionType);
        handleClose();
    }
    showSectionMenu.value = false;
}

function handleClose() {
    reset();
    beat.value = '';
    instructions.value = '';
    emit('close');
}

function selectPrompt(promptId: number | null) {
    selectedPromptId.value = promptId;
    showPromptMenu.value = false;
}

// Watch for mode changes
watch(() => props.mode, (newMode) => {
    selectedMode.value = newMode;
});

// Watch for visibility
watch(() => props.isVisible, async (visible) => {
    if (visible) {
        await fetchOptions();
        // Set default connection
        if (defaultConnection.value && !selectedConnectionId.value) {
            selectedConnectionId.value = defaultConnection.value.id;
        }
    }
});

// Keyboard shortcuts
function handleKeydown(e: KeyboardEvent) {
    if (!props.isVisible) return;
    
    // Ctrl/Cmd + Enter to generate
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        if (canGenerate.value) {
            handleGenerate();
        } else if (hasGeneratedContent.value) {
            handleApply();
        }
    }
    
    // Escape to close
    if (e.key === 'Escape') {
        e.preventDefault();
        if (isGenerating.value) {
            abort();
        } else {
            handleClose();
        }
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        v-if="isVisible"
        class="prose-generation-panel bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ modeLabel }}</span>
            </div>
            <button
                @click="handleClose"
                class="p-1 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-4">
            <!-- Mode selector (only if custom mode) -->
            <div v-if="selectedMode === 'custom'" class="flex gap-2">
                <button
                    v-for="(modeInfo, modeKey) in availableModes"
                    :key="modeKey"
                    @click="selectedMode = modeKey as 'scene_beat' | 'continue' | 'custom'"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-md transition-colors',
                        selectedMode === modeKey
                            ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300'
                            : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                    ]"
                >
                    {{ modeInfo.name }}
                </button>
            </div>

            <!-- Beat/Instructions input -->
            <div v-if="!hasGeneratedContent">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                    {{ selectedMode === 'scene_beat' ? 'Scene Beat' : selectedMode === 'continue' ? 'Guidance (optional)' : 'Instructions' }}
                </label>
                <textarea
                    v-model="beat"
                    :placeholder="selectedMode === 'scene_beat' 
                        ? 'Describe what happens in this beat...\nE.g., Elena discovers a hidden letter in the drawer. Emotional reaction.' 
                        : selectedMode === 'continue' 
                            ? 'Optional: Add guidance for how to continue...' 
                            : 'Enter your instructions...'"
                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent resize-none"
                    rows="4"
                    :disabled="isGenerating"
                ></textarea>
            </div>

            <!-- Additional instructions (for scene_beat mode) -->
            <div v-if="selectedMode === 'scene_beat' && !hasGeneratedContent">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                    Additional Instructions (optional)
                </label>
                <input
                    v-model="instructions"
                    type="text"
                    placeholder="E.g., Write with more dialogue, focus on sensory details..."
                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                    :disabled="isGenerating"
                />
            </div>

            <!-- Generated content display -->
            <div v-if="hasGeneratedContent || isGenerating" class="relative">
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-md bg-zinc-50 dark:bg-zinc-800/50 p-4 min-h-[150px] max-h-[300px] overflow-y-auto">
                    <div
                        v-if="isGenerating && !generatedContent"
                        class="flex items-center gap-2 text-zinc-500"
                    >
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">Generating prose...</span>
                    </div>
                    <div
                        v-else
                        class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-wrap"
                    >
                        {{ generatedContent }}
                        <span v-if="isGenerating" class="inline-block w-2 h-4 bg-violet-500 animate-pulse ml-0.5"></span>
                    </div>
                </div>
            </div>

            <!-- Error display -->
            <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
            </div>

            <!-- Advanced options toggle -->
            <button
                v-if="!hasGeneratedContent"
                @click="showAdvanced = !showAdvanced"
                class="flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors"
            >
                <svg
                    class="w-4 h-4 transition-transform"
                    :class="{ 'rotate-90': showAdvanced }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                Advanced options
            </button>

            <!-- Advanced options -->
            <div v-if="showAdvanced && !hasGeneratedContent" class="space-y-3 pl-4 border-l-2 border-zinc-200 dark:border-zinc-700">
                <!-- Prompt selector -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Prompt</label>
                    <div class="relative">
                        <button
                            @click="showPromptMenu = !showPromptMenu"
                            class="w-full px-3 py-2 text-left text-sm border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                        >
                            {{ selectedPromptId 
                                ? availablePrompts.find(p => p.id === selectedPromptId)?.name || 'Select prompt' 
                                : 'Default' }}
                        </button>
                        <div
                            v-if="showPromptMenu"
                            class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-lg max-h-48 overflow-y-auto"
                        >
                            <button
                                @click="selectPrompt(null)"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Default
                            </button>
                            <button
                                v-for="prompt in availablePrompts"
                                :key="prompt.id"
                                @click="selectPrompt(prompt.id)"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            >
                                {{ prompt.name }}
                                <span v-if="prompt.is_system" class="text-xs text-zinc-500 ml-1">(System)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Connection selector -->
                <div v-if="availableConnections.length > 1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">AI Connection</label>
                    <select
                        v-model="selectedConnectionId"
                        class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        <option v-for="conn in availableConnections" :key="conn.id" :value="conn.id">
                            {{ conn.name }} ({{ conn.provider }})
                            <span v-if="conn.is_default"> - Default</span>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center justify-between px-4 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            <!-- Left side: hint text -->
            <div class="text-xs text-zinc-500">
                <span v-if="!hasGeneratedContent">Press <kbd class="px-1 py-0.5 bg-zinc-200 dark:bg-zinc-700 rounded">Ctrl+Enter</kbd> to generate</span>
                <span v-else>Press <kbd class="px-1 py-0.5 bg-zinc-200 dark:bg-zinc-700 rounded">Ctrl+Enter</kbd> to apply</span>
            </div>

            <!-- Right side: action buttons -->
            <div class="flex items-center gap-2">
                <template v-if="!hasGeneratedContent && !isGenerating">
                    <button
                        @click="handleClose"
                        class="px-3 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <div class="relative">
                        <button
                            @click="handleGenerate"
                            :disabled="!canGenerate"
                            class="flex items-center gap-2 px-4 py-1.5 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 disabled:bg-violet-400 disabled:cursor-not-allowed rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                            Generate
                        </button>
                    </div>
                </template>

                <template v-else-if="isGenerating">
                    <button
                        @click="abort"
                        class="px-4 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                    >
                        Stop
                    </button>
                </template>

                <template v-else>
                    <button
                        @click="handleDiscard"
                        class="px-3 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                    >
                        Discard
                    </button>
                    <button
                        @click="handleRetry"
                        class="px-3 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                    >
                        Retry
                    </button>
                    
                    <!-- Section menu -->
                    <div class="relative">
                        <button
                            @click="showSectionMenu = !showSectionMenu"
                            class="px-3 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                        >
                            Section
                        </button>
                        <div
                            v-if="showSectionMenu"
                            class="absolute bottom-full right-0 mb-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-lg overflow-hidden"
                        >
                            <button
                                @click="handleAddToSection('content')"
                                class="block w-full px-4 py-2 text-sm text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Content Section
                            </button>
                            <button
                                @click="handleAddToSection('alternative')"
                                class="block w-full px-4 py-2 text-sm text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Alternative Section
                            </button>
                            <button
                                @click="handleAddToSection('note')"
                                class="block w-full px-4 py-2 text-sm text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Note Section
                            </button>
                        </div>
                    </div>

                    <button
                        @click="handleApply"
                        class="flex items-center gap-2 px-4 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Apply
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.prose-generation-panel {
    width: 100%;
    max-width: 600px;
}
</style>
