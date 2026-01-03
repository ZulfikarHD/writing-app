<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import Input from '@/components/ui/forms/Input.vue';
import { Motion } from 'motion-v';
import { usePresets } from '@/composables/usePresets';
import { useConfirm } from '@/composables/useConfirm';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import type { Preset, PresetFormData } from '@/composables/usePresets';
import type { Prompt } from '@/composables/usePrompts';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    preset: Preset | null;
    prompt: Prompt | null;
    isCreating?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', preset: Preset): void;
    (e: 'updated', preset: Preset): void;
    (e: 'deleted', id: number): void;
}>();

const { isReducedMotion, quickSpringConfig } = usePerformanceMode();
const { createPreset, updatePreset, deletePreset, setPresetAsDefault } = usePresets();
const { confirm: showConfirm } = useConfirm();

// Form state
const name = ref('');
const model = ref<string | null>(null);
const temperature = ref<number>(0.7);
const maxTokens = ref<number | null>(null);
const topP = ref<number | null>(null);
const frequencyPenalty = ref<number | null>(null);
const presencePenalty = ref<number | null>(null);
const stopSequences = ref<string[]>([]);
const inputValues = ref<Record<string, unknown>>({});
const isDefault = ref(false);

// UI state
const isLoading = ref(false);
const hasChanges = ref(false);
const newStopSequence = ref('');

// Toast state
const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

function showToastMessage(variant: 'success' | 'danger' | 'warning' | 'info', title: string, message: string) {
    toast.value = { show: true, variant, title, message };
}

// Initialize form from preset
function initializeForm() {
    if (props.isCreating) {
        // Reset for new preset with prompt defaults
        name.value = '';
        model.value = props.prompt?.model_settings?.model ?? null;
        temperature.value = props.prompt?.model_settings?.temperature ?? 0.7;
        maxTokens.value = props.prompt?.model_settings?.max_tokens ?? null;
        topP.value = props.prompt?.model_settings?.top_p ?? null;
        frequencyPenalty.value = props.prompt?.model_settings?.frequency_penalty ?? null;
        presencePenalty.value = props.prompt?.model_settings?.presence_penalty ?? null;
        stopSequences.value = [];
        inputValues.value = {};
        isDefault.value = false;
        hasChanges.value = false;
    } else if (props.preset) {
        name.value = props.preset.name;
        model.value = props.preset.model;
        temperature.value = props.preset.temperature;
        maxTokens.value = props.preset.max_tokens;
        topP.value = props.preset.top_p;
        frequencyPenalty.value = props.preset.frequency_penalty;
        presencePenalty.value = props.preset.presence_penalty;
        stopSequences.value = props.preset.stop_sequences || [];
        inputValues.value = props.preset.input_values || {};
        isDefault.value = props.preset.is_default;
        hasChanges.value = false;
    }
}

// Watch for preset changes
watch(() => props.preset, initializeForm, { immediate: true });
watch(() => props.show, (show) => {
    if (show) {
        initializeForm();
    }
});

// Track changes
watch([name, model, temperature, maxTokens, topP, frequencyPenalty, presencePenalty, stopSequences, inputValues, isDefault], () => {
    if (props.isCreating) {
        hasChanges.value = name.value.trim().length > 0;
    } else if (props.preset) {
        hasChanges.value =
            name.value !== props.preset.name ||
            model.value !== props.preset.model ||
            temperature.value !== props.preset.temperature ||
            maxTokens.value !== props.preset.max_tokens ||
            topP.value !== props.preset.top_p ||
            frequencyPenalty.value !== props.preset.frequency_penalty ||
            presencePenalty.value !== props.preset.presence_penalty ||
            JSON.stringify(stopSequences.value) !== JSON.stringify(props.preset.stop_sequences || []) ||
            JSON.stringify(inputValues.value) !== JSON.stringify(props.preset.input_values || {}) ||
            isDefault.value !== props.preset.is_default;
    }
}, { deep: true });

// Form validation
const isValid = computed(() => {
    return name.value.trim().length > 0;
});

// Modal title
const modalTitle = computed(() => {
    if (props.isCreating) return 'New Preset';
    return props.preset?.name || 'Edit Preset';
});

// Get prompt inputs (if available)
const promptInputs = computed(() => {
    return props.prompt?.inputs || [];
});

// Add stop sequence
function addStopSequence() {
    if (newStopSequence.value.trim() && !stopSequences.value.includes(newStopSequence.value.trim())) {
        stopSequences.value.push(newStopSequence.value.trim());
        newStopSequence.value = '';
    }
}

// Remove stop sequence
function removeStopSequence(index: number) {
    stopSequences.value.splice(index, 1);
}

// Update input value
function updateInputValue(inputName: string, value: unknown) {
    inputValues.value = {
        ...inputValues.value,
        [inputName]: value,
    };
}

// Handle save (create or update)
async function handleSave() {
    if (!isValid.value || !props.prompt) return;

    isLoading.value = true;

    const data: PresetFormData = {
        name: name.value.trim(),
        model: model.value || null,
        temperature: temperature.value,
        max_tokens: maxTokens.value,
        top_p: topP.value,
        frequency_penalty: frequencyPenalty.value,
        presence_penalty: presencePenalty.value,
        stop_sequences: stopSequences.value.length > 0 ? stopSequences.value : null,
        input_values: Object.keys(inputValues.value).length > 0 ? inputValues.value : null,
        is_default: isDefault.value,
    };

    if (props.isCreating) {
        const created = await createPreset(props.prompt.id, data);
        isLoading.value = false;

        if (created) {
            showToastMessage('success', 'Created!', 'Preset created successfully');
            emit('created', created);
            emit('close');
        } else {
            showToastMessage('danger', 'Error', 'Failed to create preset');
        }
    } else if (props.preset) {
        const updated = await updatePreset(props.preset.id, data);
        isLoading.value = false;

        if (updated) {
            hasChanges.value = false;
            showToastMessage('success', 'Saved!', 'Preset updated successfully');
            emit('updated', updated);
        } else {
            showToastMessage('danger', 'Error', 'Failed to update preset');
        }
    }
}

// Handle set as default
async function handleSetDefault() {
    if (!props.preset) return;

    isLoading.value = true;
    const updated = await setPresetAsDefault(props.preset.id);
    isLoading.value = false;

    if (updated) {
        isDefault.value = true;
        showToastMessage('success', 'Updated!', 'Preset set as default');
        emit('updated', updated);
    } else {
        showToastMessage('danger', 'Error', 'Failed to set preset as default');
    }
}

// Handle delete
async function handleDelete() {
    if (!props.preset) return;

    const confirmed = await showConfirm({
        title: 'Delete Preset',
        message: `Are you sure you want to delete "${props.preset.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });

    if (!confirmed) return;

    isLoading.value = true;
    const success = await deletePreset(props.preset.id);
    isLoading.value = false;

    if (success) {
        showToastMessage('success', 'Deleted!', 'Preset deleted successfully');
        emit('deleted', props.preset.id);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to delete preset');
    }
}

// Handle close - check for unsaved changes
async function handleClose() {
    if (hasChanges.value) {
        const confirmed = await showConfirm({
            title: 'Unsaved Changes',
            message: 'You have unsaved changes. Are you sure you want to close?',
            confirmText: 'Discard',
            confirmVariant: 'warning',
        });

        if (!confirmed) return;
    }
    emit('close');
}
</script>

<template>
    <Modal :show="show" size="2xl" :scrollable="true" @close="handleClose">
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ modalTitle }}
                </h2>
                <Badge v-if="preset?.is_default" variant="info">Default</Badge>
                <Badge v-if="hasChanges && !isCreating" variant="warning">Unsaved</Badge>
                <Badge v-if="prompt" variant="secondary">{{ prompt.name }}</Badge>
            </div>
        </template>

        <Motion
            :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, y: 8 }"
            :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, y: 0 }"
            :transition="quickSpringConfig"
        >
            <div class="space-y-6">
                <!-- Name Field -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <Input
                        v-model="name"
                        placeholder="e.g., High Creativity, Fast Generation"
                        class="w-full"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Give your preset a descriptive name
                    </p>
                </div>

                <!-- Model Field -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Model
                    </label>
                    <Input
                        v-model="model"
                        placeholder="e.g., gpt-4, claude-3-opus"
                        class="w-full"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Leave empty to use the default model from your AI connection
                    </p>
                </div>

                <!-- Model Settings Section -->
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Model Settings</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Temperature -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Temperature
                            </label>
                            <input
                                v-model.number="temperature"
                                type="number"
                                min="0"
                                max="2"
                                step="0.1"
                                placeholder="0.7"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                0 = focused, 2 = creative
                            </p>
                        </div>

                        <!-- Max Tokens -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Max Tokens
                            </label>
                            <input
                                v-model.number="maxTokens"
                                type="number"
                                min="1"
                                placeholder="2048"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                        </div>

                        <!-- Top P -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Top P
                            </label>
                            <input
                                v-model.number="topP"
                                type="number"
                                min="0"
                                max="1"
                                step="0.1"
                                placeholder="1"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                        </div>

                        <!-- Frequency Penalty -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Frequency Penalty
                            </label>
                            <input
                                v-model.number="frequencyPenalty"
                                type="number"
                                min="-2"
                                max="2"
                                step="0.1"
                                placeholder="0"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                        </div>

                        <!-- Presence Penalty -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Presence Penalty
                            </label>
                            <input
                                v-model.number="presencePenalty"
                                type="number"
                                min="-2"
                                max="2"
                                step="0.1"
                                placeholder="0"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Stop Sequences -->
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-2 font-medium text-zinc-900 dark:text-white">Stop Sequences</h3>
                    <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Sequences where the model should stop generating
                    </p>

                    <!-- Current stop sequences -->
                    <div v-if="stopSequences.length > 0" class="mb-3 flex flex-wrap gap-2">
                        <span
                            v-for="(seq, index) in stopSequences"
                            :key="index"
                            class="inline-flex items-center gap-1 rounded-full bg-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                        >
                            <code class="text-xs">{{ seq }}</code>
                            <button
                                type="button"
                                class="ml-1 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                                @click="removeStopSequence(index)"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </div>

                    <!-- Add new stop sequence -->
                    <div class="flex gap-2">
                        <input
                            v-model="newStopSequence"
                            type="text"
                            placeholder="Enter stop sequence..."
                            class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            @keydown.enter.prevent="addStopSequence"
                        />
                        <Button variant="ghost" size="sm" @click="addStopSequence">
                            Add
                        </Button>
                    </div>
                </div>

                <!-- Input Values (if prompt has inputs) -->
                <div v-if="promptInputs.length > 0" class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-2 font-medium text-zinc-900 dark:text-white">Saved Input Values</h3>
                    <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Pre-fill prompt inputs with saved values
                    </p>

                    <div class="space-y-4">
                        <div v-for="input in promptInputs" :key="input.id">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ input.label || input.name }}
                                <span v-if="input.is_required" class="text-red-500">*</span>
                            </label>

                            <!-- Text input -->
                            <input
                                v-if="input.type === 'text' || input.type === 'number'"
                                :type="input.type"
                                :value="inputValues[input.name] as string || ''"
                                :placeholder="input.placeholder || ''"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                @input="updateInputValue(input.name, ($event.target as HTMLInputElement).value)"
                            />

                            <!-- Textarea -->
                            <textarea
                                v-else-if="input.type === 'textarea'"
                                :value="inputValues[input.name] as string || ''"
                                :placeholder="input.placeholder || ''"
                                rows="3"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                @input="updateInputValue(input.name, ($event.target as HTMLTextAreaElement).value)"
                            />

                            <!-- Select -->
                            <select
                                v-else-if="input.type === 'select'"
                                :value="inputValues[input.name] as string || ''"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                @change="updateInputValue(input.name, ($event.target as HTMLSelectElement).value)"
                            >
                                <option value="">Select...</option>
                                <option v-for="opt in input.options" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>

                            <!-- Checkbox -->
                            <div v-else-if="input.type === 'checkbox'" class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="!!inputValues[input.name]"
                                    class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                                    @change="updateInputValue(input.name, ($event.target as HTMLInputElement).checked)"
                                />
                            </div>

                            <p v-if="input.description" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ input.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Default Toggle -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="isDefault"
                        class="relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                        :class="isDefault ? 'bg-violet-500' : 'bg-zinc-300 dark:bg-zinc-600'"
                        @click="isDefault = !isDefault"
                    >
                        <span
                            class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform"
                            :class="isDefault ? 'translate-x-5' : 'translate-x-0'"
                        />
                    </button>
                    <div>
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Set as default preset
                        </span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Default presets are automatically applied when using this prompt
                        </p>
                    </div>
                </div>

                <!-- Action Buttons (for editing, not creating) -->
                <div v-if="!isCreating && preset" class="flex gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <Button
                        v-if="!preset.is_default"
                        variant="ghost"
                        size="sm"
                        :disabled="isLoading"
                        @click="handleSetDefault"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Set as Default
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="text-red-600 hover:text-red-700 dark:text-red-400"
                        :disabled="isLoading"
                        @click="handleDelete"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </Button>
                </div>
            </div>
        </Motion>

        <template #footer>
            <Button variant="ghost" @click="handleClose">
                Cancel
            </Button>
            <Button
                :disabled="!isValid || (!hasChanges && !isCreating) || isLoading"
                @click="handleSave"
            >
                <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isCreating ? 'Create Preset' : 'Save Changes' }}
            </Button>
        </template>
    </Modal>

    <!-- Toast notification -->
    <Toast
        v-if="toast.show"
        :variant="toast.variant"
        :title="toast.title"
        :duration="4000"
        position="top-right"
        @close="toast.show = false"
    >
        {{ toast.message }}
    </Toast>
</template>
