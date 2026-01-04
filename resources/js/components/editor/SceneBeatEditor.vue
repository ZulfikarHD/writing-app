<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useProseGeneration, type ProseGenerationOptions } from '@/composables/useProseGeneration';

interface Props {
    sceneId: number;
    initialBeat?: string;
    isCollapsed?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    initialBeat: '',
    isCollapsed: false,
});

const emit = defineEmits<{
    (e: 'generate', content: string): void;
    (e: 'hide'): void;
    (e: 'delete'): void;
    (e: 'clear'): void;
    (e: 'update:beat', value: string): void;
}>();

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

// Local state
const beatText = ref(props.initialBeat);
const selectedWordTarget = ref<number>(400);
const customWordTarget = ref<number | null>(null);
const isEditingTarget = ref(false);
const selectedConnectionId = ref<number | null>(null);
const showContextMenu = ref(false);

// Word target options
const wordTargets = [200, 400, 600];

// Default connection
const defaultConnection = computed(() => 
    availableConnections.value.find(c => c.is_default) || availableConnections.value[0]
);

const currentConnection = computed(() => {
    if (selectedConnectionId.value) {
        return availableConnections.value.find(c => c.id === selectedConnectionId.value);
    }
    return defaultConnection.value;
});

const connectionLabel = computed(() => {
    if (currentConnection.value) {
        return `${currentConnection.value.name}`;
    }
    return 'Select Model';
});

// Can generate
const canGenerate = computed(() => {
    return !isGenerating.value && beatText.value.trim().length > 0;
});

// Effective word target
const effectiveWordTarget = computed(() => {
    return customWordTarget.value ?? selectedWordTarget.value;
});

// Initialize
onMounted(async () => {
    await fetchOptions();
    if (defaultConnection.value) {
        selectedConnectionId.value = defaultConnection.value.id;
    }
});

// Watch for initial beat changes
watch(() => props.initialBeat, (newBeat) => {
    if (newBeat && !beatText.value) {
        beatText.value = newBeat;
    }
});

// Watch beatText and emit updates
watch(beatText, (value) => {
    emit('update:beat', value);
});

// Methods
function selectWordTarget(target: number) {
    selectedWordTarget.value = target;
    customWordTarget.value = null;
    isEditingTarget.value = false;
}

function startEditingTarget() {
    isEditingTarget.value = true;
}

function saveCustomTarget(event: Event) {
    const input = event.target as HTMLInputElement;
    const value = parseInt(input.value, 10);
    if (value && value > 0 && value <= 2000) {
        customWordTarget.value = value;
        selectedWordTarget.value = value;
    }
    isEditingTarget.value = false;
}

async function handleGenerate() {
    if (!canGenerate.value) return;

    const options: ProseGenerationOptions = {
        mode: 'scene_beat',
        beat: beatText.value,
        max_tokens: effectiveWordTarget.value * 2, // Approximate word to token ratio
    };

    if (selectedConnectionId.value) {
        options.connection_id = selectedConnectionId.value;
    }

    await generate(props.sceneId, options);
    
    if (generatedContent.value && !error.value) {
        emit('generate', generatedContent.value);
    }
}

function handleKeydown(event: KeyboardEvent) {
    // Cmd/Ctrl + Enter to generate
    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        event.preventDefault();
        handleGenerate();
    }
}

function handleClear() {
    beatText.value = '';
    reset();
    emit('clear');
}

function handleHide() {
    emit('hide');
}

function handleDelete() {
    emit('delete');
}

function toggleConnectionMenu() {
    showContextMenu.value = !showContextMenu.value;
}

function selectConnection(connectionId: number) {
    selectedConnectionId.value = connectionId;
    showContextMenu.value = false;
}
</script>

<template>
    <div class="scene-beat-editor bg-zinc-900 border border-zinc-700 rounded-lg overflow-hidden">
        <!-- Header Row -->
        <div class="flex items-center justify-between px-3 py-2 border-b border-zinc-700 bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <!-- Drag Handle -->
                <div class="cursor-grab text-zinc-500 hover:text-zinc-400" data-drag-handle>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                    </svg>
                </div>
                
                <!-- Scene Beat Label -->
                <div class="flex items-center gap-1.5 text-violet-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="text-xs font-semibold uppercase tracking-wide">Scene Beat</span>
                </div>
            </div>
            
            <div class="flex items-center gap-1">
                <!-- Hide Button -->
                <button
                    type="button"
                    class="px-2 py-1 text-xs text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 rounded transition-colors"
                    @click="handleHide"
                >
                    Hide
                </button>
                
                <!-- Delete Button -->
                <button
                    type="button"
                    class="p-1.5 text-zinc-500 hover:text-red-400 hover:bg-zinc-700 rounded transition-colors"
                    title="Delete beat"
                    @click="handleDelete"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-3">
            <textarea
                v-model="beatText"
                placeholder="Start writing, or type '/' for commands..."
                class="w-full min-h-[80px] bg-transparent text-zinc-200 placeholder-zinc-500 resize-none focus:outline-none text-sm leading-relaxed"
                :disabled="isGenerating"
                @keydown="handleKeydown"
            ></textarea>
            
            <!-- Generated Content Preview -->
            <div v-if="generatedContent" class="mt-3 pt-3 border-t border-zinc-700">
                <div class="text-xs text-zinc-500 mb-2">Generated:</div>
                <div class="text-sm text-zinc-300 whitespace-pre-wrap">
                    {{ generatedContent }}
                    <span v-if="isGenerating" class="inline-block w-2 h-4 bg-violet-500 animate-pulse ml-0.5"></span>
                </div>
            </div>
            
            <!-- Error Display -->
            <div v-if="error" class="mt-3 p-2 bg-red-900/20 border border-red-800 rounded text-xs text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Controls Row -->
        <div class="flex flex-wrap items-center gap-2 px-3 py-2 border-t border-zinc-700 bg-zinc-800/30">
            <!-- Word Target Buttons -->
            <div class="flex items-center gap-1">
                <button
                    v-for="target in wordTargets"
                    :key="target"
                    type="button"
                    :class="[
                        'px-2 py-1 text-xs rounded transition-colors',
                        selectedWordTarget === target && !customWordTarget
                            ? 'bg-zinc-600 text-white'
                            : 'bg-zinc-700 text-zinc-400 hover:bg-zinc-600 hover:text-zinc-200',
                    ]"
                    @click="selectWordTarget(target)"
                >
                    {{ target }}
                </button>
                
                <!-- Custom Word Target -->
                <div v-if="isEditingTarget" class="flex items-center">
                    <input
                        type="number"
                        :value="customWordTarget || selectedWordTarget"
                        min="50"
                        max="2000"
                        step="50"
                        class="w-16 px-2 py-1 text-xs bg-zinc-700 text-white rounded focus:outline-none focus:ring-1 focus:ring-violet-500"
                        @blur="saveCustomTarget"
                        @keydown.enter="saveCustomTarget"
                    />
                </div>
                <button
                    v-else
                    type="button"
                    :class="[
                        'p-1 rounded transition-colors',
                        customWordTarget
                            ? 'bg-zinc-600 text-white'
                            : 'text-zinc-500 hover:text-zinc-300 hover:bg-zinc-700',
                    ]"
                    title="Custom word target"
                    @click="startEditingTarget"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>

            <!-- Context Button -->
            <button
                type="button"
                class="flex items-center gap-1 px-2 py-1 text-xs text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 rounded transition-colors"
                title="Add context from scene"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Context
            </button>
        </div>

        <!-- Footer Row -->
        <div class="flex items-center justify-between px-3 py-2 border-t border-zinc-700 bg-zinc-800/50">
            <!-- Model Selector -->
            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 px-2 py-1.5 text-xs bg-zinc-700 text-zinc-300 hover:bg-zinc-600 rounded transition-colors"
                    @click="toggleConnectionMenu"
                >
                    <svg class="w-3.5 h-3.5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ connectionLabel }}</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <!-- Connection Dropdown -->
                <div
                    v-if="showContextMenu"
                    class="absolute bottom-full left-0 mb-1 w-56 bg-zinc-800 border border-zinc-700 rounded-lg shadow-lg overflow-hidden z-10"
                >
                    <div class="py-1">
                        <button
                            v-for="connection in availableConnections"
                            :key="connection.id"
                            type="button"
                            :class="[
                                'w-full px-3 py-2 text-left text-xs transition-colors',
                                selectedConnectionId === connection.id
                                    ? 'bg-violet-900/30 text-violet-300'
                                    : 'text-zinc-300 hover:bg-zinc-700',
                            ]"
                            @click="selectConnection(connection.id)"
                        >
                            <div class="font-medium">{{ connection.name }}</div>
                            <div class="text-zinc-500">{{ connection.provider }}</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Clear Beat Button -->
                <button
                    v-if="beatText || generatedContent"
                    type="button"
                    class="px-3 py-1.5 text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
                    @click="handleClear"
                >
                    Clear Beat
                </button>
                
                <!-- Generate/Stop Button -->
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
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-violet-600 hover:bg-violet-700 disabled:bg-zinc-700 disabled:text-zinc-500 disabled:cursor-not-allowed rounded transition-colors"
                    @click="handleGenerate"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Generate
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scene-beat-editor {
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

/* Hide number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
