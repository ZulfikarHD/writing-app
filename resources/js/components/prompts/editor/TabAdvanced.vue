<script setup lang="ts">
import { ref } from 'vue';
import Button from '@/components/ui/buttons/Button.vue';

export interface PromptInputDef {
    id: string;
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number' | 'checkbox';
    options?: { value: string; label: string }[];
    default_value?: string;
    placeholder?: string;
    description?: string;
    is_required: boolean;
    sort_order: number;
}

export interface PromptComponentRef {
    id: number;
    name: string;
    label: string;
    description?: string;
}

interface Props {
    inputs: PromptInputDef[];
    components: PromptComponentRef[];
    isEditable: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:inputs': [value: PromptInputDef[]];
    insertComponent: [componentName: string];
}>();

const expandedInputId = ref<string | null>(null);

const inputTypes = [
    { value: 'text', label: 'Text Input' },
    { value: 'textarea', label: 'Text Area' },
    { value: 'select', label: 'Dropdown Select' },
    { value: 'number', label: 'Number' },
    { value: 'checkbox', label: 'Checkbox' },
];

function generateId(): string {
    return `input_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

function addInput() {
    const newInput: PromptInputDef = {
        id: generateId(),
        name: '',
        label: '',
        type: 'text',
        is_required: false,
        sort_order: props.inputs.length,
    };
    emit('update:inputs', [...props.inputs, newInput]);
    expandedInputId.value = newInput.id;
}

function updateInput(id: string, updates: Partial<PromptInputDef>) {
    const updated = props.inputs.map((input) =>
        input.id === id ? { ...input, ...updates } : input,
    );
    emit('update:inputs', updated);
}

function removeInput(id: string) {
    emit('update:inputs', props.inputs.filter((input) => input.id !== id));
}

function toggleExpand(id: string) {
    expandedInputId.value = expandedInputId.value === id ? null : id;
}

function addSelectOption(inputId: string) {
    const input = props.inputs.find((i) => i.id === inputId);
    if (input) {
        const options = input.options || [];
        updateInput(inputId, {
            options: [...options, { value: '', label: '' }],
        });
    }
}

function updateSelectOption(inputId: string, index: number, field: 'value' | 'label', value: string) {
    const input = props.inputs.find((i) => i.id === inputId);
    if (input && input.options) {
        const newOptions = [...input.options];
        newOptions[index] = { ...newOptions[index], [field]: value };
        updateInput(inputId, { options: newOptions });
    }
}

function removeSelectOption(inputId: string, index: number) {
    const input = props.inputs.find((i) => i.id === inputId);
    if (input && input.options) {
        const newOptions = input.options.filter((_, i) => i !== index);
        updateInput(inputId, { options: newOptions });
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Prompt Inputs Section -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-zinc-900 dark:text-white">Prompt Inputs</h3>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        Define input fields that users fill before running the prompt
                    </p>
                </div>
                <Button v-if="isEditable" size="sm" variant="ghost" @click="addInput">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Input
                </Button>
            </div>

            <!-- Inputs List -->
            <div v-if="inputs.length > 0" class="space-y-2">
                <div
                    v-for="input in inputs"
                    :key="input.id"
                    class="rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <!-- Input Header -->
                    <div
                        class="flex cursor-pointer items-center justify-between px-3 py-2"
                        @click="toggleExpand(input.id)"
                    >
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4 text-zinc-400 transition-transform"
                                :class="{ 'rotate-90': expandedInputId === input.id }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ input.label || input.name || 'Unnamed Input' }}
                            </span>
                            <span class="rounded bg-zinc-200 px-1.5 py-0.5 text-[10px] text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                {{ input.type }}
                            </span>
                            <span v-if="input.is_required" class="text-[10px] text-red-500">Required</span>
                        </div>
                        <button
                            v-if="isEditable"
                            type="button"
                            class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                            @click.stop="removeInput(input.id)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Input Details (Expanded) -->
                    <div v-if="expandedInputId === input.id" class="border-t border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <!-- Name (variable name) -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Variable Name
                                </label>
                                <input
                                    :value="input.name"
                                    :disabled="!isEditable"
                                    type="text"
                                    placeholder="e.g., word_count"
                                    class="w-full rounded border border-zinc-300 bg-white px-2 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    @input="updateInput(input.id, { name: ($event.target as HTMLInputElement).value })"
                                />
                                <p class="mt-0.5 text-[10px] text-zinc-500">Use in prompt as: <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">{`{input("${input.name || 'name'}"})`}</code></p>
                            </div>

                            <!-- Label -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Display Label
                                </label>
                                <input
                                    :value="input.label"
                                    :disabled="!isEditable"
                                    type="text"
                                    placeholder="e.g., Word Count"
                                    class="w-full rounded border border-zinc-300 bg-white px-2 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    @input="updateInput(input.id, { label: ($event.target as HTMLInputElement).value })"
                                />
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Input Type
                                </label>
                                <select
                                    :value="input.type"
                                    :disabled="!isEditable"
                                    class="w-full rounded border border-zinc-300 bg-white px-2 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    @change="updateInput(input.id, { type: ($event.target as HTMLSelectElement).value as PromptInputDef['type'] })"
                                >
                                    <option v-for="t in inputTypes" :key="t.value" :value="t.value">
                                        {{ t.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Required -->
                            <div class="flex items-center gap-2">
                                <input
                                    :checked="input.is_required"
                                    :disabled="!isEditable"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500 disabled:cursor-not-allowed"
                                    @change="updateInput(input.id, { is_required: ($event.target as HTMLInputElement).checked })"
                                />
                                <label class="text-sm text-zinc-700 dark:text-zinc-300">Required field</label>
                            </div>

                            <!-- Default Value -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Default Value
                                </label>
                                <input
                                    :value="input.default_value"
                                    :disabled="!isEditable"
                                    type="text"
                                    placeholder="Optional default..."
                                    class="w-full rounded border border-zinc-300 bg-white px-2 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    @input="updateInput(input.id, { default_value: ($event.target as HTMLInputElement).value })"
                                />
                            </div>

                            <!-- Select Options (only for select type) -->
                            <div v-if="input.type === 'select'" class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Options
                                </label>
                                <div class="space-y-1.5">
                                    <div
                                        v-for="(option, idx) in input.options || []"
                                        :key="idx"
                                        class="flex items-center gap-2"
                                    >
                                        <input
                                            :value="option.value"
                                            :disabled="!isEditable"
                                            type="text"
                                            placeholder="Value"
                                            class="w-1/3 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                            @input="updateSelectOption(input.id, idx, 'value', ($event.target as HTMLInputElement).value)"
                                        />
                                        <input
                                            :value="option.label"
                                            :disabled="!isEditable"
                                            type="text"
                                            placeholder="Label"
                                            class="flex-1 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                            @input="updateSelectOption(input.id, idx, 'label', ($event.target as HTMLInputElement).value)"
                                        />
                                        <button
                                            v-if="isEditable"
                                            type="button"
                                            class="rounded p-1 text-zinc-400 hover:text-red-600"
                                            @click="removeSelectOption(input.id, idx)"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <button
                                        v-if="isEditable"
                                        type="button"
                                        class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400"
                                        @click="addSelectOption(input.id)"
                                    >
                                        + Add Option
                                    </button>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                    Help Text
                                </label>
                                <input
                                    :value="input.description"
                                    :disabled="!isEditable"
                                    type="text"
                                    placeholder="Brief description for users..."
                                    class="w-full rounded border border-zinc-300 bg-white px-2 py-1.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    @input="updateInput(input.id, { description: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                No inputs defined. Add inputs to let users customize prompts before running.
            </div>
        </div>

        <!-- Components Section -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3">
                <h3 class="font-medium text-zinc-900 dark:text-white">Included Components</h3>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Reusable text snippets inserted using <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">[[component_name]]</code> syntax
                </p>
            </div>

            <div v-if="components.length > 0" class="space-y-2">
                <div
                    v-for="comp in components"
                    :key="comp.id"
                    class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ comp.label }}</span>
                        <span class="ml-2 text-xs text-zinc-500 dark:text-zinc-400">[[{{ comp.name }}]]</span>
                    </div>
                    <button
                        v-if="isEditable"
                        type="button"
                        class="rounded px-2 py-1 text-xs text-violet-600 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950"
                        @click="emit('insertComponent', comp.name)"
                    >
                        Insert
                    </button>
                </div>
            </div>

            <div v-else class="py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                No components referenced. Use <code class="rounded bg-zinc-200 px-1 dark:bg-zinc-700">[[component_name]]</code> in your prompt to include them.
            </div>
        </div>
    </div>
</template>
