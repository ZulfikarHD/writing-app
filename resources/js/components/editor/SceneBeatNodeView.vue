<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { NodeViewWrapper } from '@tiptap/vue-3';
import { useProseGeneration } from '@/composables/useProseGeneration';
import ModelSelector from '@/components/ai/ModelSelector.vue';

interface GenerationMetadata {
    beatText: string;
    connectionId: number | null;
    modelId: string | null;
    wordTarget: number;
}

interface Props {
    node: {
        attrs: {
            beatText: string;
            wordTarget: number;
            connectionId: number | null;
            instructions: string;
        };
    };
    updateAttributes: (attrs: Record<string, unknown>) => void;
    deleteNode: () => void;
    getPos: () => number;
    editor: {
        commands: {
            replaceSceneBeatWithContent: (pos: number, content: string, metadata?: GenerationMetadata) => boolean;
        };
        extensionManager: {
            extensions: Array<{ name: string; options?: { sceneId?: number } }>;
        };
    };
}

const props = defineProps<Props>();

// Get sceneId from extension options
const sceneId = computed(() => {
    const sceneBeatExt = props.editor.extensionManager?.extensions.find((ext) => ext.name === 'sceneBeat');
    return sceneBeatExt?.options?.sceneId || 0;
});

// Prose generation composable
const {
    isGenerating,
    generatedContent,
    error,
    availableConnections,
    generate,
    abort,
    reset,
    fetchOptions,
} = useProseGeneration();

// UI State
const isExpanded = ref(false); // Full modal expanded
const activeTab = ref<'tweak' | 'preview'>('tweak');
const isHidden = ref(false);
const showPromptPreview = ref(false);
const generatedContentContainer = ref<HTMLElement | null>(null);
const expandedContentContainer = ref<HTMLElement | null>(null);

// Form State
const beatText = ref(props.node.attrs.beatText || '');
const instructions = ref(props.node.attrs.instructions || '');
const selectedWordTarget = ref<number>(props.node.attrs.wordTarget || 400);
const customWordTarget = ref<string>('');
const selectedModel = ref<string>('');
const selectedConnectionId = ref<number | undefined>(props.node.attrs.connectionId || undefined);

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

const systemMessage = ref(defaultSystemMessage);
const userMessage = ref('');

// Word target options
const wordTargets = [200, 400, 600];

// Effective word target
const effectiveWordTarget = computed(() => {
    const custom = parseInt(customWordTarget.value, 10);
    if (custom && custom > 0) return custom;
    return selectedWordTarget.value;
});

// Can generate
const canGenerate = computed(() => {
    return !isGenerating.value && beatText.value.trim().length > 0 && sceneId.value > 0;
});

// Construct user message from beat
const constructedUserMessage = computed(() => {
    let msg = `Write approximately ${effectiveWordTarget.value} words.\n\nScene beat to write:\n${beatText.value || '[Enter your beat description]'}`;
    if (userMessage.value.trim()) {
        msg = userMessage.value.trim() + '\n\n' + msg;
    }
    return msg;
});

// Full preview prompt
const previewPrompt = computed(() => {
    const parts: string[] = [];

    parts.push('=== SYSTEM MESSAGE ===');
    parts.push(systemMessage.value);

    if (instructions.value.trim()) {
        parts.push('');
        parts.push('Additional instructions:');
        parts.push(instructions.value.trim());
    }

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

// Sync with node attributes
watch(beatText, (value) => props.updateAttributes({ beatText: value }));
watch(instructions, (value) => props.updateAttributes({ instructions: value }));
watch(selectedWordTarget, (value) => props.updateAttributes({ wordTarget: value }));
watch(selectedConnectionId, (value) => props.updateAttributes({ connectionId: value }));

// Auto-scroll generated content as it streams in
watch(generatedContent, async () => {
    if (isGenerating.value) {
        await nextTick();
        // Auto-scroll compact view
        if (generatedContentContainer.value) {
            generatedContentContainer.value.scrollTop = generatedContentContainer.value.scrollHeight;
        }
        // Auto-scroll expanded view
        if (expandedContentContainer.value) {
            expandedContentContainer.value.scrollTop = expandedContentContainer.value.scrollHeight;
        }
    }
});

// Methods
function selectWordTarget(target: number) {
    selectedWordTarget.value = target;
    customWordTarget.value = '';
}

function handleCustomTargetInput(e: Event) {
    const target = e.target as HTMLInputElement;
    const value = parseInt(target.value, 10);
    if (value && value > 0 && value <= 5000) {
        selectedWordTarget.value = value;
    }
}

async function handleGenerate() {
    if (!canGenerate.value) return;

    await generate(sceneId.value, {
        mode: 'scene_beat',
        beat: beatText.value,
        instructions: instructions.value || undefined,
        max_tokens: effectiveWordTarget.value * 2,
        connection_id: selectedConnectionId.value,
    });

    // If generation successful, replace beat with content wrapped in a Section
    if (generatedContent.value && !error.value) {
        const pos = props.getPos();
        const metadata: GenerationMetadata = {
            beatText: beatText.value,
            connectionId: selectedConnectionId.value || null,
            modelId: selectedModel.value || null,
            wordTarget: effectiveWordTarget.value,
        };
        props.editor.commands.replaceSceneBeatWithContent(pos, generatedContent.value, metadata);
    }
}

function handleKeydown(event: KeyboardEvent) {
    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        event.preventDefault();
        handleGenerate();
    }
}

function handleDelete() {
    props.deleteNode();
}

function handleHide() {
    isHidden.value = true;
}

function handleShow() {
    isHidden.value = false;
}

function handleClearBeat() {
    beatText.value = '';
    instructions.value = '';
    userMessage.value = '';
    reset();
}

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
}

function toggleExpand() {
    isExpanded.value = !isExpanded.value;
}

function togglePromptPreview() {
    showPromptPreview.value = !showPromptPreview.value;
}
</script>

<template>
    <NodeViewWrapper class="scene-beat-wrapper my-4" data-scene-beat>
        <!-- Hidden State -->
        <div
            v-if="isHidden"
            class="flex items-center gap-2 px-3 py-2 bg-zinc-800/50 border border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-800 transition-colors"
            @click="handleShow"
        >
            <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="text-xs font-semibold text-violet-400 uppercase tracking-wide">Scene Beat</span>
            <span v-if="beatText" class="text-xs text-zinc-500 truncate max-w-[200px]">{{ beatText }}</span>
            <span v-else class="text-xs text-zinc-500">(click to expand)</span>
            <button
                type="button"
                class="ml-auto p-1 text-zinc-500 hover:text-red-400 transition-colors"
                title="Delete"
                @click.stop="handleDelete"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Compact View (Initial State - like Novelcrafter) -->
        <div v-else-if="!isExpanded" class="scene-beat-compact bg-zinc-900 border border-zinc-700 rounded-lg overflow-hidden">
            <!-- Header Row -->
            <div class="flex items-center gap-2 px-3 py-2 border-b border-zinc-800">
                <!-- Drag Handle -->
                <div class="cursor-grab text-zinc-600 hover:text-zinc-400" data-drag-handle>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                    </svg>
                </div>
                <!-- Label -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wide">Scene Beat</span>
                </div>
                <div class="flex-1"></div>
                <!-- Hide Button -->
                <button
                    type="button"
                    class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
                    @click="handleHide"
                >
                    Hide
                </button>
                <!-- Delete Button -->
                <button
                    type="button"
                    class="p-1 text-zinc-500 hover:text-red-400 transition-colors"
                    title="Delete"
                    @click="handleDelete"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <!-- Beat Text Input -->
            <div class="px-3 py-2">
                <textarea
                    v-model="beatText"
                    placeholder="Start writing, or type '/' for commands..."
                    rows="1"
                    class="w-full bg-transparent text-sm text-zinc-300 placeholder-zinc-600 focus:outline-none resize-none"
                    :disabled="isGenerating"
                    @keydown="handleKeydown"
                ></textarea>
            </div>

            <!-- Generated Content Preview (Streaming) -->
            <div v-if="generatedContent && !showPromptPreview" class="px-3 pb-2">
                <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg overflow-hidden">
                    <!-- Preview Header -->
                    <div class="flex items-center justify-between px-2 py-1.5 border-b border-zinc-700 bg-zinc-800/80">
                        <div class="flex items-center gap-1.5">
                            <svg v-if="isGenerating" class="w-3 h-3 text-violet-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <svg v-else class="w-3 h-3 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-xs font-medium text-zinc-400">
                                {{ isGenerating ? 'Generating...' : 'Generated Content' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-zinc-500">{{ generatedContent.split(/\s+/).filter(Boolean).length }} words</span>
                            <button
                                v-if="!isGenerating"
                                type="button"
                                class="text-xs text-zinc-500 hover:text-zinc-300"
                                @click="copyToClipboard(generatedContent)"
                                title="Copy to clipboard"
                            >
                                📋
                            </button>
                        </div>
                    </div>

                    <!-- Preview Content -->
                    <div ref="generatedContentContainer" class="p-2 max-h-48 overflow-y-auto">
                        <div class="text-sm text-zinc-300 whitespace-pre-wrap leading-relaxed">
                            {{ generatedContent }}
                            <span v-if="isGenerating" class="inline-block w-0.5 h-4 bg-violet-400 animate-pulse ml-0.5 align-middle"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prompt Preview (Collapsible) -->
            <div v-if="showPromptPreview && !generatedContent" class="px-3 pb-3 pt-0">
                <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg overflow-hidden">
                    <!-- Preview Header -->
                    <div class="flex items-center justify-between px-2 py-1.5 border-b border-zinc-700 bg-zinc-800/80">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="text-xs font-medium text-zinc-400">Prompt Preview</span>
                        </div>
                        <button
                            type="button"
                            class="text-xs text-zinc-500 hover:text-zinc-300"
                            @click="copyToClipboard(previewPrompt)"
                            title="Copy to clipboard"
                        >
                            📋
                        </button>
                    </div>

                    <!-- Preview Content -->
                    <div class="p-2 max-h-40 overflow-y-auto">
                        <div class="space-y-2">
                            <!-- System Message -->
                            <div>
                                <div class="text-[10px] font-medium text-zinc-500 uppercase tracking-wide mb-1">System Message</div>
                                <div class="text-xs text-zinc-400 font-mono leading-relaxed whitespace-pre-wrap">{{ systemMessage.substring(0, 150) }}{{ systemMessage.length > 150 ? '...' : '' }}</div>
                            </div>

                            <!-- User Message -->
                            <div>
                                <div class="text-[10px] font-medium text-zinc-500 uppercase tracking-wide mb-1">User Message</div>
                                <div class="text-xs text-zinc-300 font-mono leading-relaxed whitespace-pre-wrap">{{ constructedUserMessage }}</div>
                            </div>

                            <!-- Word Count -->
                            <div class="pt-1 border-t border-zinc-700">
                                <span class="text-[10px] text-zinc-500">Total: {{ previewWordCount }} words</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Row -->
            <div class="flex items-center gap-2 px-3 py-2 border-t border-zinc-800">
                <!-- Word Targets -->
                <div class="flex items-center gap-1">
                    <button
                        v-for="target in wordTargets"
                        :key="target"
                        type="button"
                        :class="[
                            'px-2 py-1 text-xs rounded border transition-colors',
                            selectedWordTarget === target && !customWordTarget
                                ? 'bg-zinc-700 border-zinc-600 text-white'
                                : 'bg-transparent border-zinc-700 text-zinc-500 hover:border-zinc-600 hover:text-zinc-400',
                        ]"
                        @click="selectWordTarget(target)"
                    >
                        {{ target }}
                    </button>
                </div>

                <!-- Preview Prompt Button -->
                <button
                    type="button"
                    :class="[
                        'p-1.5 rounded transition-colors',
                        showPromptPreview
                            ? 'text-violet-400 bg-zinc-800'
                            : 'text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800',
                    ]"
                    title="Preview prompt"
                    @click="togglePromptPreview"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                <!-- Pencil (Expand to full modal) -->
                <button
                    type="button"
                    class="p-1.5 text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 rounded transition-colors"
                    title="Edit prompt template"
                    @click="toggleExpand"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>

                <!-- Context Button -->
                <button
                    type="button"
                    class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Context
                </button>

                <div class="flex-1"></div>

                <!-- Error -->
                <div v-if="error" class="text-xs text-red-400 truncate max-w-[200px]" :title="error">
                    ⚠️ {{ error }}
                </div>
            </div>

            <!-- Footer Row -->
            <div class="flex items-center justify-between px-3 py-2 border-t border-zinc-800 bg-zinc-800/30">
                <!-- Model Selector -->
                <ModelSelector
                    v-model="selectedModel"
                    v-model:connection-id="selectedConnectionId"
                    size="sm"
                    placeholder="Select Model"
                    class="scene-beat-model-selector"
                />

                <!-- Clear Beat Button -->
                <button
                    type="button"
                    class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
                    @click="handleClearBeat"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Clear Beat
                </button>

                <!-- Generate Button -->
                <button
                    v-if="isGenerating"
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 transition-colors"
                    @click="abort"
                >
                    Stop
                </button>
                <button
                    v-else
                    type="button"
                    :disabled="!canGenerate"
                    class="px-3 py-1.5 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 disabled:bg-zinc-700 disabled:text-zinc-500 disabled:cursor-not-allowed rounded transition-colors"
                    @click="handleGenerate"
                >
                    Generate
                </button>
            </div>
        </div>

        <!-- Expanded Modal View (Full Tweak/Preview) -->
        <div v-else class="scene-beat-expanded bg-zinc-900 border border-zinc-700 rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-700 bg-zinc-800/50">
                <div class="flex items-center gap-3">
                    <div class="cursor-grab text-zinc-500 hover:text-zinc-400" data-drag-handle>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Generate Text</h3>
                </div>
                <button
                    type="button"
                    class="text-xs text-zinc-400 hover:text-white transition-colors"
                    @click="toggleExpand"
                >
                    × Close
                </button>
            </div>

            <!-- Warning Banner -->
            <div v-if="!beatText.trim()" class="mx-4 mt-3 px-3 py-2 bg-amber-900/30 border border-amber-700/50 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs text-amber-200">Your message for this prompt is empty. Please enter a message to generate a response.</span>
            </div>

            <!-- Tabs -->
            <div class="flex items-center justify-between px-4 pt-3">
                <div class="flex gap-1">
                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 text-xs font-medium rounded-md transition-colors flex items-center gap-1.5',
                            activeTab === 'tweak'
                                ? 'bg-zinc-700 text-white'
                                : 'text-zinc-400 hover:text-white hover:bg-zinc-800',
                        ]"
                        @click="activeTab = 'tweak'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Tweak
                    </button>
                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 text-xs font-medium rounded-md transition-colors flex items-center gap-1.5',
                            activeTab === 'preview'
                                ? 'bg-zinc-700 text-white'
                                : 'text-zinc-400 hover:text-white hover:bg-zinc-800',
                        ]"
                        @click="activeTab = 'preview'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-zinc-500">Presets</span>
                    <button type="button" class="text-xs text-zinc-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-4 max-h-[60vh] overflow-y-auto">
                <!-- Tweak Tab -->
                <div v-if="activeTab === 'tweak'" class="space-y-4">
                    <!-- Words Section -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-zinc-300">Words</span>
                                <span class="text-[10px] px-1.5 py-0.5 bg-amber-600/20 text-amber-400 rounded">Required</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-zinc-500 mb-2">How many words should the AI write?</p>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="target in wordTargets"
                                :key="target"
                                type="button"
                                :class="[
                                    'px-3 py-1.5 text-xs rounded-md border transition-colors',
                                    selectedWordTarget === target && !customWordTarget
                                        ? 'bg-zinc-700 border-zinc-600 text-white'
                                        : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-600 hover:text-zinc-300',
                                ]"
                                @click="selectWordTarget(target)"
                            >
                                {{ target }}
                            </button>
                            <span class="text-xs text-zinc-500">OR</span>
                            <input
                                v-model="customWordTarget"
                                type="text"
                                placeholder="e.g. 300"
                                class="flex-1 max-w-[100px] px-3 py-1.5 text-xs bg-zinc-800 border border-zinc-700 rounded-md text-zinc-300 placeholder-zinc-600 focus:outline-none focus:border-zinc-500"
                                @input="handleCustomTargetInput"
                            />
                        </div>
                    </div>

                    <!-- System Message Section -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-300">System Message</span>
                            <button
                                type="button"
                                class="text-xs text-zinc-500 hover:text-zinc-300 flex items-center gap-1"
                                @click="systemMessage = defaultSystemMessage"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset
                            </button>
                        </div>
                        <p class="text-[11px] text-zinc-500 mb-2">The system instructions and writing rules for the AI</p>
                        <textarea
                            v-model="systemMessage"
                            rows="6"
                            class="w-full px-3 py-2 text-xs bg-zinc-800 border border-zinc-700 rounded-md text-zinc-300 placeholder-zinc-600 focus:outline-none focus:border-zinc-500 resize-none font-mono"
                        ></textarea>
                        <div class="flex justify-end gap-2 mt-1">
                            <button type="button" class="text-[11px] text-zinc-500 hover:text-zinc-300" @click="copyToClipboard(systemMessage)">📋 Copy</button>
                        </div>
                    </div>

                    <!-- User Message / Instructions Section -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-300">User Message</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 mb-2">Additional instructions for this specific generation</p>
                        <textarea
                            v-model="userMessage"
                            placeholder="e.g. Focus on the character's internal monologue..."
                            rows="2"
                            class="w-full px-3 py-2 text-xs bg-zinc-800 border border-zinc-700 rounded-md text-zinc-300 placeholder-zinc-600 focus:outline-none focus:border-zinc-500 resize-none"
                        ></textarea>
                    </div>

                    <!-- Beat Description (the main input) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-300">Scene Beat</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 mb-2">Describe what happens in this scene beat</p>
                        <textarea
                            v-model="beatText"
                            placeholder="The protagonist enters the dark forest and discovers a hidden path..."
                            rows="4"
                            class="w-full px-3 py-2 text-sm bg-zinc-800 border border-zinc-700 rounded-md text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-violet-500 resize-none"
                            :disabled="isGenerating"
                            @keydown="handleKeydown"
                        ></textarea>
                    </div>

                    <!-- Additional Context -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-300">Additional Context</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 mb-2">Any additional information to provide to the AI</p>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs bg-zinc-800 border border-zinc-700 rounded-md text-zinc-400 hover:text-zinc-300 hover:border-zinc-600 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Context
                        </button>
                    </div>

                    <!-- Generated Content Preview (while generating) -->
                    <div v-if="generatedContent" class="pt-3 border-t border-zinc-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-zinc-500">{{ isGenerating ? 'Generating...' : 'Generated Content' }}</span>
                                <svg v-if="isGenerating" class="w-3 h-3 text-violet-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-xs text-zinc-500">{{ generatedContent.split(/\s+/).filter(Boolean).length }} words</span>
                        </div>
                        <div ref="expandedContentContainer" class="text-sm text-zinc-300 whitespace-pre-wrap max-h-48 overflow-y-auto bg-zinc-800 rounded-md p-3">
                            {{ generatedContent }}
                            <span v-if="isGenerating" class="inline-block w-0.5 h-4 bg-violet-400 animate-pulse ml-0.5 align-middle"></span>
                        </div>
                    </div>

                    <!-- Error Display -->
                    <div v-if="error" class="p-3 bg-red-900/20 border border-red-800 rounded-lg text-xs text-red-400">
                        {{ error }}
                    </div>
                </div>

                <!-- Preview Tab -->
                <div v-if="activeTab === 'preview'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs bg-zinc-800 border border-zinc-700 rounded-md text-zinc-400 hover:text-zinc-300 hover:border-zinc-600 transition-colors"
                            @click="copyToClipboard(previewPrompt)"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy to Clipboard
                        </button>
                        <span class="text-xs text-zinc-500">{{ previewWordCount }} words total</span>
                    </div>

                    <!-- System Message Preview -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-400">System Message</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-zinc-500">{{ systemMessage.split(/\s+/).filter(Boolean).length }} words</span>
                                <button type="button" class="text-xs text-zinc-500 hover:text-zinc-300" @click="copyToClipboard(systemMessage)">📋 Copy</button>
                            </div>
                        </div>
                        <div class="bg-zinc-800 border border-zinc-700 rounded-md p-3 max-h-40 overflow-y-auto">
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap font-mono leading-relaxed">{{ systemMessage }}</pre>
                        </div>
                    </div>

                    <!-- User Message Preview -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-zinc-400">User Message</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-zinc-500">{{ constructedUserMessage.split(/\s+/).filter(Boolean).length }} words</span>
                                <button type="button" class="text-xs text-zinc-500 hover:text-zinc-300" @click="copyToClipboard(constructedUserMessage)">📋 Copy</button>
                            </div>
                        </div>
                        <div class="bg-zinc-800 border border-zinc-700 rounded-md p-3 max-h-40 overflow-y-auto">
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap font-mono leading-relaxed">{{ constructedUserMessage }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-4 py-3 border-t border-zinc-700 bg-zinc-800/30">
                <!-- Model Selector -->
                <div class="flex items-center gap-2">
                    <ModelSelector
                        v-model="selectedModel"
                        v-model:connection-id="selectedConnectionId"
                        size="sm"
                        placeholder="Select Model"
                        class="scene-beat-model-selector"
                    />
                    <span class="text-[10px] text-zinc-500">Max. Output: ~{{ effectiveWordTarget * 3 }} words</span>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        v-if="isGenerating"
                        type="button"
                        class="px-4 py-2 text-xs font-medium text-red-400 hover:text-red-300 transition-colors"
                        @click="abort"
                    >
                        Stop
                    </button>
                    <button
                        v-else
                        type="button"
                        :disabled="!canGenerate"
                        :title="!sceneId ? 'Scene ID required' : !beatText.trim() ? 'Enter a beat description' : 'Generate prose'"
                        class="px-4 py-2 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 disabled:bg-zinc-700 disabled:text-zinc-500 disabled:cursor-not-allowed rounded-md transition-colors"
                        @click="handleGenerate"
                    >
                        Generate
                    </button>
                </div>
            </div>
        </div>
    </NodeViewWrapper>
</template>

<style scoped>
.scene-beat-wrapper {
    outline: none;
}

.scene-beat-compact,
.scene-beat-expanded {
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

textarea {
    font-family: inherit;
}

/* Custom scrollbar */
.max-h-40::-webkit-scrollbar,
.max-h-48::-webkit-scrollbar,
.max-h-\[60vh\]::-webkit-scrollbar {
    width: 6px;
}

.max-h-40::-webkit-scrollbar-track,
.max-h-48::-webkit-scrollbar-track,
.max-h-\[60vh\]::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-40::-webkit-scrollbar-thumb,
.max-h-48::-webkit-scrollbar-thumb,
.max-h-\[60vh\]::-webkit-scrollbar-thumb {
    background: #52525b;
    border-radius: 3px;
}

/* Smooth scrolling for streaming content */
.max-h-48 {
    scroll-behavior: smooth;
}

/* Override model selector styles for dark theme */
:deep(.scene-beat-model-selector) {
    background: #27272a;
    border-color: #3f3f46;
}
</style>
