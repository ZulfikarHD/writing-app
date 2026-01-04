<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import ModelSelector from '@/components/ai/ModelSelector.vue';
import { useProseGeneration } from '@/composables/useProseGeneration';

interface Props {
    sourceBeat: string | null;
    sourceConnectionId: number | null;
    sourceModelId: string | null;
    sourceWordTarget: number | null;
    isCollapsed: boolean;
    wordCount: number;
    sceneId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'toggle-collapse'): void;
    (e: 'regenerate', content: string, metadata: {
        beatText: string;
        connectionId: number | null;
        modelId: string | null;
        wordTarget: number;
    }): void;
    (e: 'open-menu', event: Event): void;
    (e: 'dissolve'): void;
}>();

// Prose generation composable
const {
    isGenerating,
    generatedContent,
    error,
    generate,
    abort,
    reset,
    fetchOptions,
} = useProseGeneration();

// Local state
const showRegeneratePanel = ref(false);
const showPromptPreview = ref(false);
const selectedModel = ref(props.sourceModelId || '');
const selectedConnectionId = ref<number | undefined>(props.sourceConnectionId || undefined);
const editedBeat = ref(props.sourceBeat || '');
const selectedWordTarget = ref(props.sourceWordTarget || 400);

// Word target options
const wordTargets = [200, 400, 600];

// Default system message template
const defaultSystemMessage = `You are an expert fiction writer.

Always keep the following rules in mind:
- Write in past tense and use General English spelling, grammar, and colloquialisms/slang.
- Write in active voice
- Always follow the "show, don't tell" principle.
- Avoid adverbs and cliches and overused/commonly used phrases. Aim for fresh and original descriptions.
- Convey events and story through dialogue.
- Mix short, punchy sentences with long, descriptive ones. Drop fill words to add variety.
- Skip "he/she said" dialogue tags and convey people's actions or face expressions through their speech
- Avoid mushy dialog and descriptions, have dialogue always continue the action, never stall or add unnecessary fluff.
- Put dialogue on its own paragraph to separate scene and action.
- Reduce indicators of uncertainty like "trying" or "maybe"

When writing text:
- NEVER conclude the scene on your own, follow the beat instructions very closely`;

// Construct user message from beat
const constructedUserMessage = computed(() => {
    if (!props.sourceBeat) return '';
    return `Write approximately ${props.sourceWordTarget || 400} words.\n\nScene beat to write:\n${props.sourceBeat}`;
});

// Full preview prompt
const previewPrompt = computed(() => {
    const parts: string[] = [];

    parts.push('=== SYSTEM MESSAGE ===');
    parts.push(defaultSystemMessage);
    parts.push('');
    parts.push('=== USER MESSAGE ===');
    parts.push(constructedUserMessage.value);

    return parts.join('\n');
});

// Word count of preview
const previewWordCount = computed(() => {
    return previewPrompt.value.split(/\s+/).filter(Boolean).length;
});

// Initialize
onMounted(async () => {
    await fetchOptions();
});

// Methods
function toggleRegeneratePanel() {
    showRegeneratePanel.value = !showRegeneratePanel.value;
    if (showRegeneratePanel.value) {
        // Reset to original values when opening
        editedBeat.value = props.sourceBeat || '';
        selectedWordTarget.value = props.sourceWordTarget || 400;
        // Close prompt preview if open
        showPromptPreview.value = false;
    }
}

function togglePromptPreview() {
    showPromptPreview.value = !showPromptPreview.value;
    if (showPromptPreview.value) {
        // Close regenerate panel if open
        showRegeneratePanel.value = false;
    }
}

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
}

async function handleRegenerate() {
    if (!editedBeat.value.trim() || !props.sceneId) return;

    reset();

    await generate(props.sceneId, {
        mode: 'scene_beat',
        beat: editedBeat.value,
        max_tokens: selectedWordTarget.value * 2,
        connection_id: selectedConnectionId.value,
    });

    if (generatedContent.value && !error.value) {
        emit('regenerate', generatedContent.value, {
            beatText: editedBeat.value,
            connectionId: selectedConnectionId.value || null,
            modelId: selectedModel.value || null,
            wordTarget: selectedWordTarget.value,
        });
        showRegeneratePanel.value = false;
    }
}

function selectWordTarget(target: number) {
    selectedWordTarget.value = target;
}
</script>

<template>
    <div class="generated-section-header">
        <!-- Main Header Row -->
        <div
            class="flex items-center gap-2 px-3 py-2 border-b border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20"
        >
            <!-- Collapse Toggle -->
            <button
                type="button"
                class="flex-shrink-0 p-1 rounded hover:bg-amber-200 dark:hover:bg-amber-900/40 transition-colors"
                :title="isCollapsed ? 'Expand section' : 'Collapse section'"
                @click="emit('toggle-collapse')"
            >
                <svg
                    class="w-4 h-4 text-amber-600 dark:text-amber-400 transition-transform"
                    :class="{ '-rotate-90': isCollapsed }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- AI Generated Badge -->
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">
                    AI Generated
                </span>
            </div>

            <!-- Beat Preview (truncated) -->
            <div
                v-if="sourceBeat && !showRegeneratePanel"
                class="flex-1 min-w-0 text-xs text-amber-700/70 dark:text-amber-300/60 truncate italic"
                :title="sourceBeat"
            >
                "{{ sourceBeat.substring(0, 60) }}{{ sourceBeat.length > 60 ? '...' : '' }}"
            </div>
            <div v-else class="flex-1"></div>

            <!-- Word Count -->
            <span class="flex-shrink-0 text-xs text-amber-600/60 dark:text-amber-400/50">
                {{ wordCount }} words
            </span>

            <!-- View Prompt Button -->
            <button
                v-if="sourceBeat"
                type="button"
                class="flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded transition-colors"
                :class="showPromptPreview
                    ? 'bg-violet-500 text-white hover:bg-violet-600'
                    : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60'"
                @click="togglePromptPreview"
                title="View the original prompt used for generation"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ showPromptPreview ? 'Hide' : 'View Prompt' }}</span>
            </button>

            <!-- Regenerate Button -->
            <button
                type="button"
                class="flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded transition-colors"
                :class="showRegeneratePanel
                    ? 'bg-amber-500 text-white hover:bg-amber-600'
                    : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60'"
                @click="toggleRegeneratePanel"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ showRegeneratePanel ? 'Cancel' : 'Regenerate' }}</span>
            </button>

            <!-- Dissolve Button -->
            <button
                type="button"
                class="flex-shrink-0 p-1 rounded hover:bg-amber-200 dark:hover:bg-amber-900/40 text-amber-600 dark:text-amber-400 transition-colors"
                title="Remove section wrapper (keep content)"
                @click="emit('dissolve')"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </button>

            <!-- Menu Button -->
            <button
                type="button"
                class="flex-shrink-0 p-1 rounded hover:bg-amber-200 dark:hover:bg-amber-900/40 transition-colors"
                title="Section options"
                @click.stop="emit('open-menu', $event)"
            >
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
        </div>

        <!-- Regenerate Panel (expandable) -->
        <div
            v-if="showRegeneratePanel"
            class="px-3 py-3 border-b border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/30 space-y-3"
        >
            <!-- Beat Text Editor -->
            <div>
                <label class="block text-xs font-medium text-amber-700 dark:text-amber-300 mb-1">
                    Scene Beat
                </label>
                <textarea
                    v-model="editedBeat"
                    rows="2"
                    class="w-full px-3 py-2 text-sm bg-white dark:bg-zinc-800 border border-amber-300 dark:border-amber-800 rounded-lg text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                    placeholder="Describe what should happen..."
                    :disabled="isGenerating"
                ></textarea>
            </div>

            <!-- Controls Row -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Word Target -->
                <div class="flex items-center gap-1">
                    <span class="text-xs text-amber-600 dark:text-amber-400">Words:</span>
                    <button
                        v-for="target in wordTargets"
                        :key="target"
                        type="button"
                        :class="[
                            'px-2 py-1 text-xs rounded transition-colors',
                            selectedWordTarget === target
                                ? 'bg-amber-500 text-white'
                                : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60',
                        ]"
                        @click="selectWordTarget(target)"
                    >
                        {{ target }}
                    </button>
                </div>

                <!-- Model Selector -->
                <div class="flex-1 min-w-[200px]">
                    <ModelSelector
                        v-model="selectedModel"
                        v-model:connection-id="selectedConnectionId"
                        size="sm"
                        placeholder="Select Model"
                        class="generated-section-model-selector"
                    />
                </div>

                <!-- Generate Button -->
                <button
                    v-if="isGenerating"
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 transition-colors"
                    @click="abort"
                >
                    Stop
                </button>
                <button
                    v-else
                    type="button"
                    :disabled="!editedBeat.trim() || !sceneId"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-amber-500 hover:bg-amber-600 disabled:bg-zinc-300 disabled:text-zinc-500 disabled:cursor-not-allowed rounded transition-colors"
                    @click="handleRegenerate"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Regenerate
                </button>
            </div>

            <!-- Generation Preview -->
            <div v-if="generatedContent" class="pt-2 border-t border-amber-200 dark:border-amber-900/50">
                <div class="text-xs text-amber-600 dark:text-amber-400 mb-1">Preview:</div>
                <div class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap max-h-32 overflow-y-auto bg-white dark:bg-zinc-800 rounded p-2">
                    {{ generatedContent }}
                    <span v-if="isGenerating" class="inline-block w-2 h-4 bg-amber-500 animate-pulse ml-0.5"></span>
                </div>
            </div>

            <!-- Error Display -->
            <div v-if="error" class="p-2 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-800 rounded text-xs text-red-600 dark:text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Prompt Preview Panel (expandable) -->
        <div
            v-if="showPromptPreview"
            class="px-3 py-3 border-b border-violet-200 dark:border-violet-900/50 bg-violet-50/50 dark:bg-violet-950/30 space-y-3"
        >
            <!-- Header -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <h4 class="text-sm font-semibold text-violet-700 dark:text-violet-300">Original Prompt</h4>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-violet-600/70 dark:text-violet-400/60">{{ previewWordCount }} words</span>
                    <button
                        type="button"
                        class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                        @click="copyToClipboard(previewPrompt)"
                        title="Copy to clipboard"
                    >
                        📋 Copy
                    </button>
                </div>
            </div>

            <!-- System Message -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-violet-700 dark:text-violet-300">System Message</span>
                    <span class="text-xs text-violet-600/60 dark:text-violet-400/50">{{ defaultSystemMessage.split(/\s+/).filter(Boolean).length }} words</span>
                </div>
                <div class="bg-white dark:bg-zinc-800 border border-violet-200 dark:border-violet-800 rounded-lg p-3 max-h-40 overflow-y-auto">
                    <pre class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap font-mono leading-relaxed">{{ defaultSystemMessage }}</pre>
                </div>
            </div>

            <!-- User Message -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-violet-700 dark:text-violet-300">User Message</span>
                    <span class="text-xs text-violet-600/60 dark:text-violet-400/50">{{ constructedUserMessage.split(/\s+/).filter(Boolean).length }} words</span>
                </div>
                <div class="bg-white dark:bg-zinc-800 border border-violet-200 dark:border-violet-800 rounded-lg p-3 max-h-32 overflow-y-auto">
                    <pre class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap font-mono leading-relaxed">{{ constructedUserMessage }}</pre>
                </div>
            </div>

            <!-- Metadata Info -->
            <div class="flex flex-wrap gap-3 text-xs text-violet-600/70 dark:text-violet-400/60">
                <div v-if="sourceWordTarget">
                    <span class="font-medium">Target Words:</span> {{ sourceWordTarget }}
                </div>
                <div v-if="sourceConnectionId">
                    <span class="font-medium">Connection ID:</span> {{ sourceConnectionId }}
                </div>
                <div v-if="sourceModelId">
                    <span class="font-medium">Model:</span> {{ sourceModelId }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.generated-section-header {
    user-select: none;
}

/* Custom scrollbar for preview */
.max-h-32::-webkit-scrollbar,
.max-h-40::-webkit-scrollbar {
    width: 4px;
}

.max-h-32::-webkit-scrollbar-track,
.max-h-40::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-32::-webkit-scrollbar-thumb {
    background: #d97706;
    border-radius: 2px;
}

.max-h-40::-webkit-scrollbar-thumb {
    background: #8b5cf6;
    border-radius: 2px;
}

/* Override model selector styles */
:deep(.generated-section-model-selector) {
    background: white;
}

:deep(.dark .generated-section-model-selector) {
    background: #27272a;
}
</style>
