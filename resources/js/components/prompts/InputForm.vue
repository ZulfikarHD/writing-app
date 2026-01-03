<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import Select from '@/components/ui/forms/Select.vue';
import { Motion } from 'motion-v';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import { ref, watch, computed } from 'vue';

export interface PromptInputDef {
    id: number | string;
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
    options?: { value: string; label: string }[];
    default_value?: string | null;
    placeholder?: string | null;
    description?: string | null;
    is_required: boolean;
    sort_order: number;
}

export interface InputValues {
    [key: string]: string | number | boolean;
}

const props = defineProps<{
    show: boolean;
    promptName: string;
    inputs: PromptInputDef[];
    initialValues?: InputValues;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit', values: InputValues): void;
}>();

const { isReducedMotion, quickSpringConfig } = usePerformanceMode();

// Form values
const values = ref<InputValues>({});

// Initialize form values
function initializeForm() {
    const newValues: InputValues = {};

    for (const input of props.inputs) {
        // Use initial value if provided, else use default value
        if (props.initialValues && props.initialValues[input.name] !== undefined) {
            newValues[input.name] = props.initialValues[input.name];
        } else if (input.default_value !== undefined && input.default_value !== null) {
            // Convert default value to appropriate type
            if (input.type === 'number') {
                newValues[input.name] = parseFloat(input.default_value) || 0;
            } else if (input.type === 'checkbox') {
                newValues[input.name] = input.default_value === 'true' || input.default_value === '1';
            } else {
                newValues[input.name] = input.default_value;
            }
        } else {
            // Set appropriate default based on type
            if (input.type === 'number') {
                newValues[input.name] = 0;
            } else if (input.type === 'checkbox') {
                newValues[input.name] = false;
            } else {
                newValues[input.name] = '';
            }
        }
    }

    values.value = newValues;
}

// Watch for show changes to initialize
watch(
    () => props.show,
    (show) => {
        if (show) {
            initializeForm();
        }
    },
    { immediate: true }
);

// Watch for inputs changes
watch(() => props.inputs, initializeForm, { deep: true });

// Validation
const validation = computed(() => {
    const errors: Record<string, string> = {};

    for (const input of props.inputs) {
        if (input.is_required) {
            const value = values.value[input.name];
            if (value === undefined || value === null || value === '') {
                errors[input.name] = `${input.label} is required`;
            }
        }
    }

    return {
        errors,
        isValid: Object.keys(errors).length === 0,
    };
});

// Get input value
function getValue(name: string): string | number | boolean {
    return values.value[name] ?? '';
}

// Set input value
function setValue(name: string, value: string | number | boolean) {
    values.value[name] = value;
}

// Handle number input
function handleNumberInput(name: string, event: Event) {
    const target = event.target as HTMLInputElement;
    setValue(name, parseFloat(target.value) || 0);
}

// Handle checkbox
function toggleCheckbox(name: string) {
    setValue(name, !getValue(name));
}

// Handle submit
function handleSubmit() {
    if (!validation.value.isValid) return;
    emit('submit', { ...values.value });
}

// Handle close
function handleClose() {
    emit('close');
}

// Sort inputs by sort_order
const sortedInputs = computed(() => {
    return [...props.inputs].sort((a, b) => a.sort_order - b.sort_order);
});
</script>

<template>
    <Modal :show="show" size="lg" :scrollable="true" @close="handleClose">
        <template #header>
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-pink-100 dark:bg-pink-900/30">
                    <svg class="h-4 w-4 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        Configure Prompt
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ promptName }}
                    </p>
                </div>
            </div>
        </template>

        <Motion
            :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, y: 8 }"
            :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, y: 0 }"
            :transition="quickSpringConfig"
        >
            <div class="space-y-5">
                <!-- Empty State -->
                <div v-if="sortedInputs.length === 0" class="py-8 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        This prompt has no configurable inputs.
                    </p>
                </div>

                <!-- Input Fields -->
                <div v-else class="space-y-4">
                    <div v-for="input in sortedInputs" :key="input.id" class="space-y-1.5">
                        <!-- Label -->
                        <label class="flex items-center gap-1 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ input.label }}
                            <span v-if="input.is_required" class="text-red-500">*</span>
                        </label>

                        <!-- Description -->
                        <p v-if="input.description" class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ input.description }}
                        </p>

                        <!-- Text Input -->
                        <Input
                            v-if="input.type === 'text'"
                            :model-value="String(getValue(input.name))"
                            :placeholder="input.placeholder || ''"
                            class="w-full"
                            :class="{ 'border-red-500': validation.errors[input.name] }"
                            @update:model-value="setValue(input.name, $event)"
                        />

                        <!-- Textarea -->
                        <Textarea
                            v-else-if="input.type === 'textarea'"
                            :model-value="String(getValue(input.name))"
                            :placeholder="input.placeholder || ''"
                            :rows="4"
                            class="w-full"
                            :class="{ 'border-red-500': validation.errors[input.name] }"
                            @update:model-value="setValue(input.name, $event)"
                        />

                        <!-- Select -->
                        <Select
                            v-else-if="input.type === 'select'"
                            :model-value="String(getValue(input.name))"
                            class="w-full"
                            :class="{ 'border-red-500': validation.errors[input.name] }"
                            @update:model-value="setValue(input.name, $event)"
                        >
                            <option value="">Select an option...</option>
                            <option
                                v-for="option in input.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </Select>

                        <!-- Number Input -->
                        <input
                            v-else-if="input.type === 'number'"
                            type="number"
                            :value="getValue(input.name)"
                            :placeholder="input.placeholder || ''"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-violet-400 dark:focus:ring-violet-400"
                            :class="{ 'border-red-500': validation.errors[input.name] }"
                            @input="handleNumberInput(input.name, $event)"
                        />

                        <!-- Checkbox -->
                        <div v-else-if="input.type === 'checkbox'" class="flex items-center gap-2">
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="!!getValue(input.name)"
                                class="relative h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                                :class="getValue(input.name) ? 'bg-violet-500' : 'bg-zinc-300 dark:bg-zinc-600'"
                                @click="toggleCheckbox(input.name)"
                            >
                                <span
                                    class="pointer-events-none absolute left-0.5 top-0.5 h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform"
                                    :class="getValue(input.name) ? 'translate-x-4' : 'translate-x-0'"
                                />
                            </button>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                {{ getValue(input.name) ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>

                        <!-- Error Message -->
                        <p v-if="validation.errors[input.name]" class="text-xs text-red-500">
                            {{ validation.errors[input.name] }}
                        </p>
                    </div>
                </div>

                <!-- Info Box -->
                <div v-if="sortedInputs.length > 0" class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            These values will be inserted into the prompt using the <code class="rounded bg-zinc-200 px-1 py-0.5 font-mono dark:bg-zinc-700">{input("name")}</code> syntax.
                        </p>
                    </div>
                </div>
            </div>
        </Motion>

        <template #footer>
            <Button variant="ghost" @click="handleClose">
                Cancel
            </Button>
            <Button
                :disabled="!validation.isValid"
                @click="handleSubmit"
            >
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Run Prompt
            </Button>
        </template>
    </Modal>
</template>
