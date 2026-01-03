<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import type { ModelSettings } from '@/composables/usePrompts';
import { usePresets } from '@/composables/usePresets';
import type { Preset } from '@/composables/usePresets';

interface Props {
    name: string;
    type: 'chat' | 'prose' | 'replacement' | 'summary';
    types: Record<string, string>;
    modelSettings: ModelSettings;
    isEditable: boolean;
    isCreating: boolean;
    promptId?: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:name': [value: string];
    'update:type': [value: 'chat' | 'prose' | 'replacement' | 'summary'];
    'update:modelSettings': [value: ModelSettings];
    'openPresetEditor': [preset: Preset | null, isCreating: boolean];
    'applyPreset': [preset: Preset];
}>();

const { fetchPresetsForPrompt } = usePresets();

// Presets state
const presets = ref<Preset[]>([]);
const isLoadingPresets = ref(false);
const selectedPresetId = ref<number | null>(null);
const showPresetDropdown = ref(false);

// Load presets when prompt ID is available
async function loadPresets() {
    if (!props.promptId) return;
    
    isLoadingPresets.value = true;
    try {
        presets.value = await fetchPresetsForPrompt(props.promptId);
    } catch {
        // Silently fail
    } finally {
        isLoadingPresets.value = false;
    }
}

// Watch for prompt ID changes
watch(() => props.promptId, () => {
    if (props.promptId) {
        loadPresets();
    }
}, { immediate: true });

// Selected preset
const selectedPreset = computed(() => {
    if (!selectedPresetId.value) return null;
    return presets.value.find(p => p.id === selectedPresetId.value) || null;
});

// Apply preset settings
function applyPreset(preset: Preset) {
    selectedPresetId.value = preset.id;
    showPresetDropdown.value = false;
    
    // Apply model settings from preset
    const newSettings: ModelSettings = {};
    if (preset.temperature !== null) newSettings.temperature = preset.temperature;
    if (preset.max_tokens !== null) newSettings.max_tokens = preset.max_tokens;
    if (preset.top_p !== null) newSettings.top_p = preset.top_p;
    if (preset.frequency_penalty !== null) newSettings.frequency_penalty = preset.frequency_penalty;
    if (preset.presence_penalty !== null) newSettings.presence_penalty = preset.presence_penalty;
    
    emit('update:modelSettings', newSettings);
    emit('applyPreset', preset);
}

// Clear preset selection
function clearPreset() {
    selectedPresetId.value = null;
}

// Open preset editor (create new)
function openNewPreset() {
    emit('openPresetEditor', null, true);
}

// Open preset editor (edit existing)
function editPreset(preset: Preset) {
    emit('openPresetEditor', preset, false);
}

function updateModelSetting(key: keyof ModelSettings, value: number | string[] | undefined) {
    // Clear preset selection when manually changing settings
    selectedPresetId.value = null;
    emit('update:modelSettings', {
        ...props.modelSettings,
        [key]: value || undefined,
    });
}

// Stop sequences management
const newStopSequence = ref('');

function addStopSequence() {
    const trimmed = newStopSequence.value.trim();
    if (!trimmed) return;
    
    const current = props.modelSettings.stop_sequences || [];
    if (!current.includes(trimmed)) {
        updateModelSetting('stop_sequences', [...current, trimmed]);
    }
    newStopSequence.value = '';
}

function removeStopSequence(index: number) {
    const current = props.modelSettings.stop_sequences || [];
    updateModelSetting('stop_sequences', current.filter((_, i) => i !== index));
}

function handleStopSequenceKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addStopSequence();
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Name -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Name <span v-if="isEditable" class="text-red-500">*</span>
            </label>
            <input
                :value="name"
                type="text"
                :disabled="!isEditable"
                placeholder="Enter prompt name..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                @input="emit('update:name', ($event.target as HTMLInputElement).value)"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                <span class="font-medium">Tip:</span> Organize prompts into folders using the format
                <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">Folder / Prompt Name</code>
                (e.g., "Writing / Character Development / Backstory Generator")
            </p>
        </div>

        <!-- Type -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Type <span v-if="isEditable && isCreating" class="text-red-500">*</span>
            </label>
            <select
                :value="type"
                :disabled="!isEditable || !isCreating"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                @change="emit('update:type', ($event.target as HTMLSelectElement).value as 'chat' | 'prose' | 'replacement' | 'summary')"
            >
                <option v-for="(label, value) in types" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                Determines where and how this prompt can be used.
            </p>
        </div>

        <!-- Presets Section (only when editing existing prompt) -->
        <div v-if="promptId && !isCreating" class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-white">Presets</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Quick-apply saved model settings and input values
                    </p>
                </div>
                <Button
                    v-if="isEditable"
                    variant="ghost"
                    size="sm"
                    @click="openNewPreset"
                >
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Preset
                </Button>
            </div>

            <!-- Loading state -->
            <div v-if="isLoadingPresets" class="flex items-center gap-2 text-sm text-zinc-500">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading presets...
            </div>

            <!-- No presets -->
            <div v-else-if="presets.length === 0" class="text-sm text-zinc-500 dark:text-zinc-400">
                No presets saved yet. Create one to quickly apply your favorite settings.
            </div>

            <!-- Preset list -->
            <div v-else class="space-y-2">
                <!-- Selected preset indicator -->
                <div v-if="selectedPreset" class="mb-3 flex items-center gap-2 rounded-lg bg-violet-50 px-3 py-2 dark:bg-violet-950/30">
                    <svg class="h-4 w-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-violet-700 dark:text-violet-300">
                        Using preset: {{ selectedPreset.name }}
                    </span>
                    <button
                        type="button"
                        class="ml-auto text-violet-600 hover:text-violet-700 dark:text-violet-400"
                        @click="clearPreset"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Preset buttons -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset.id"
                        type="button"
                        class="group relative flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors"
                        :class="[
                            selectedPresetId === preset.id
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-950/30 dark:text-violet-300'
                                : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600',
                        ]"
                        @click="applyPreset(preset)"
                    >
                        <span>{{ preset.name }}</span>
                        <Badge v-if="preset.is_default" variant="info" size="sm">Default</Badge>
                        
                        <!-- Edit button (on hover) -->
                        <button
                            v-if="isEditable"
                            type="button"
                            class="ml-1 opacity-0 transition-opacity group-hover:opacity-100"
                            title="Edit preset"
                            @click.stop="editPreset(preset)"
                        >
                            <svg class="h-3.5 w-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </button>
                </div>
            </div>
        </div>

        <!-- Model Settings Section -->
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Model Settings</h3>
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Override model settings for this prompt. Leave empty to use defaults.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Temperature -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Temperature
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="Controls the AI's creativity. Higher values (up to 2) make output more random and creative. Lower values make it more focused and deterministic. Best for brainstorming when high, consistency when low."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.temperature"
                        type="number"
                        :disabled="!isEditable"
                        min="0"
                        max="2"
                        step="0.1"
                        placeholder="0.7"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('temperature', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        0 = focused & predictable, 2 = creative & diverse
                    </p>
                </div>

                <!-- Max Tokens -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Max Tokens
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="Hard limit on response length. The AI stops exactly at this limit, even mid-sentence. Set low for quick responses, high for longer content. Consider using word targets in prompts for more natural endings."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.max_tokens"
                        type="number"
                        :disabled="!isEditable"
                        min="1"
                        placeholder="2048"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('max_tokens', parseInt(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Maximum length of AI response
                    </p>
                </div>

                <!-- Top P -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Top P
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="Controls word choice diversity. Lower values (e.g., 0.5) restrict to common words, fixing errors like strange symbols. Higher values allow more vocabulary range. Useful when Temperature is high but output is too random."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.top_p"
                        type="number"
                        :disabled="!isEditable"
                        min="0"
                        max="1"
                        step="0.1"
                        placeholder="1"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('top_p', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Vocabulary diversity (lower = safer words)
                    </p>
                </div>

                <!-- Frequency Penalty -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Frequency Penalty
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="Reduces word repetition based on frequency. Higher values push the AI to use different words when it keeps using the same ones (like 'very' multiple times). Don't set too high or it may avoid necessary words like character names."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.frequency_penalty"
                        type="number"
                        :disabled="!isEditable"
                        min="-2"
                        max="2"
                        step="0.1"
                        placeholder="0"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('frequency_penalty', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Use if AI overuses the same words
                    </p>
                </div>

                <!-- Presence Penalty -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Presence Penalty
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="Encourages introducing new topics and ideas. Higher values push the AI to explore new concepts rather than dwelling on the same topic. Great for brainstorming, but high values may cause it to ignore important context from your prompt."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.presence_penalty"
                        type="number"
                        :disabled="!isEditable"
                        min="-2"
                        max="2"
                        step="0.1"
                        placeholder="0"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('presence_penalty', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Use if AI gets stuck on same topics
                    </p>
                </div>

                <!-- Repetition Penalty -->
                <div>
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Repetition Penalty
                        </label>
                        <span
                            class="cursor-help text-zinc-400 dark:text-zinc-500"
                            title="General-purpose tool that discourages any kind of repetition. Directly prevents the AI from repeating words it recently used. If you see repeated sentences or phrases, a slight increase can help. Not all models support this."
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <input
                        :value="modelSettings.repetition_penalty"
                        type="number"
                        :disabled="!isEditable"
                        min="0"
                        max="2"
                        step="0.1"
                        placeholder="1"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                        @input="updateModelSetting('repetition_penalty', parseFloat(($event.target as HTMLInputElement).value) || undefined)"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        General anti-repetition (not all models support)
                    </p>
                </div>
            </div>

            <!-- Stop Sequences -->
            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Stop Sequences
                </label>
                <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Text sequences that will stop the AI generation when encountered.
                </p>
                
                <!-- Current stop sequences -->
                <div v-if="modelSettings.stop_sequences?.length" class="mb-2 flex flex-wrap gap-1.5">
                    <span
                        v-for="(seq, index) in modelSettings.stop_sequences"
                        :key="index"
                        class="inline-flex items-center gap-1 rounded-md bg-zinc-200 px-2 py-1 text-xs font-mono text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                    >
                        <span class="max-w-[150px] truncate">{{ seq.replace(/\n/g, '\\n').replace(/\t/g, '\\t') }}</span>
                        <button
                            v-if="isEditable"
                            type="button"
                            class="ml-0.5 text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400"
                            @click="removeStopSequence(index)"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                </div>
                
                <!-- Add new stop sequence -->
                <div v-if="isEditable" class="flex gap-2">
                    <input
                        v-model="newStopSequence"
                        type="text"
                        placeholder="Enter stop sequence (e.g., \n\n)"
                        class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-mono focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        @keydown="handleStopSequenceKeydown"
                    />
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        :disabled="!newStopSequence.trim()"
                        @click="addStopSequence"
                    >
                        Add
                    </Button>
                </div>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Use \n for newline, \t for tab. Press Enter to add.
                </p>
            </div>
        </div>
    </div>
</template>
