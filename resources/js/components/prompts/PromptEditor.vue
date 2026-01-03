<script setup lang="ts">
import type { Prompt, PromptFormData, ModelSettings } from '@/composables/usePrompts';
import { useConfirm } from '@/composables/useConfirm';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import { ref, watch, computed } from 'vue';

interface Props {
    prompt: Prompt | null;
    types: Record<string, string>;
    isCreating: boolean;
    isLoading: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    save: [data: PromptFormData];
    cancel: [];
    delete: [];
    clone: [];
}>();

const { showConfirm } = useConfirm();

// Form state
const name = ref('');
const description = ref('');
const type = ref<'chat' | 'prose' | 'replacement' | 'summary'>('chat');
const systemMessage = ref('');
const userMessage = ref('');
const modelSettings = ref<ModelSettings>({});
const activeTab = ref<'content' | 'settings' | 'preview'>('content');

// Initialize/reset form
function initializeForm() {
    if (props.prompt) {
        name.value = props.prompt.name;
        description.value = props.prompt.description || '';
        type.value = props.prompt.type;
        systemMessage.value = props.prompt.system_message || '';
        userMessage.value = props.prompt.user_message || '';
        modelSettings.value = props.prompt.model_settings || {};
    } else {
        name.value = '';
        description.value = '';
        type.value = 'chat';
        systemMessage.value = '';
        userMessage.value = '';
        modelSettings.value = {};
    }
}

// Watch for changes in prompt
watch(() => props.prompt, initializeForm, { immediate: true });
watch(() => props.isCreating, initializeForm);

// Form validation
const isValid = computed(() => {
    return name.value.trim().length > 0;
});

// Check if prompt is editable
const isEditable = computed(() => {
    if (props.isCreating) return true;
    return props.prompt && !props.prompt.is_system;
});

// Handle save
function handleSave() {
    if (!isValid.value) return;

    const data: PromptFormData = {
        name: name.value.trim(),
        description: description.value.trim() || null,
        type: type.value,
        system_message: systemMessage.value.trim() || null,
        user_message: userMessage.value.trim() || null,
        model_settings: Object.keys(modelSettings.value).length > 0 ? modelSettings.value : null,
    };

    emit('save', data);
}

// Handle delete
async function handleDelete() {
    if (!props.prompt) return;

    const confirmed = await showConfirm({
        title: 'Delete Prompt',
        message: `Are you sure you want to delete "${props.prompt.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });

    if (confirmed) {
        emit('delete');
    }
}

// Get placeholder for user message based on type
function getUserMessagePlaceholder() {
    switch (type.value) {
        case 'chat':
            return 'Enter the user message template (optional)...';
        case 'prose':
            return 'Please write prose for the following scene beat:\n\n{{beat}}\n\nContext:\n{{context}}';
        case 'replacement':
            return 'Please transform the following text:\n\n{{selection}}';
        case 'summary':
            return 'Please summarize this scene:\n\n{{scene_content}}';
        default:
            return 'Enter the user message template...';
    }
}

// Available variables for current type
const availableVariables = computed(() => {
    switch (type.value) {
        case 'chat':
            return ['{{codex}}', '{{context}}', '{{user_input}}'];
        case 'prose':
            return ['{{beat}}', '{{context}}', '{{codex}}', '{{previous_text}}'];
        case 'replacement':
            return ['{{selection}}', '{{context}}', '{{codex}}'];
        case 'summary':
            return ['{{scene_content}}', '{{codex}}'];
        default:
            return [];
    }
});

// Insert variable at cursor
function insertVariable(variable: string, field: 'system' | 'user') {
    if (field === 'system') {
        systemMessage.value += variable;
    } else {
        userMessage.value += variable;
    }
}
</script>

<template>
    <div class="flex h-full flex-col">
        <!-- Header -->
        <div class="border-b border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                        {{ isCreating ? 'Create Prompt' : prompt?.name }}
                    </h2>
                    <Badge v-if="prompt?.is_system" variant="secondary">System Prompt</Badge>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="prompt && !prompt.is_system" variant="ghost" size="sm" @click="emit('clone')">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        Clone
                    </Button>
                    <Button v-if="prompt && !prompt.is_system" variant="ghost" size="sm" class="text-red-600 hover:text-red-700 dark:text-red-400" @click="handleDelete">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-4 flex gap-1">
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'content'
                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                    ]"
                    @click="activeTab = 'content'"
                >
                    Content
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'settings'
                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                    ]"
                    @click="activeTab = 'settings'"
                >
                    Settings
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'preview'
                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                    ]"
                    @click="activeTab = 'preview'"
                >
                    Preview
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Content Tab -->
            <div v-show="activeTab === 'content'" class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="name"
                        type="text"
                        :disabled="!isEditable"
                        placeholder="Enter prompt name..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Description
                    </label>
                    <input
                        v-model="description"
                        type="text"
                        :disabled="!isEditable"
                        placeholder="Brief description of what this prompt does..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    />
                </div>

                <!-- Type -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="type"
                        :disabled="!isEditable || !isCreating"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option v-for="(label, value) in types" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Determines where and how this prompt can be used.
                    </p>
                </div>

                <!-- System Message -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            System Message
                        </label>
                        <div v-if="isEditable" class="flex gap-1">
                            <button
                                v-for="variable in availableVariables"
                                :key="variable"
                                type="button"
                                class="rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                @click="insertVariable(variable, 'system')"
                            >
                                {{ variable }}
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="systemMessage"
                        :disabled="!isEditable"
                        rows="8"
                        placeholder="Enter system instructions for the AI..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>
                </div>

                <!-- User Message Template -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            User Message Template
                        </label>
                        <div v-if="isEditable" class="flex gap-1">
                            <button
                                v-for="variable in availableVariables"
                                :key="variable"
                                type="button"
                                class="rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                @click="insertVariable(variable, 'user')"
                            >
                                {{ variable }}
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="userMessage"
                        :disabled="!isEditable"
                        rows="6"
                        :placeholder="getUserMessagePlaceholder()"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>
                </div>
            </div>

            <!-- Settings Tab -->
            <div v-show="activeTab === 'settings'" class="space-y-6">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 font-medium text-zinc-900 dark:text-white">Model Settings</h3>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                        Override model settings for this prompt. Leave empty to use defaults.
                    </p>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Temperature -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Temperature
                            </label>
                            <input
                                v-model.number="modelSettings.temperature"
                                type="number"
                                :disabled="!isEditable"
                                min="0"
                                max="2"
                                step="0.1"
                                placeholder="0.7"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                0 = focused, 2 = creative
                            </p>
                        </div>

                        <!-- Max Tokens -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Max Tokens
                            </label>
                            <input
                                v-model.number="modelSettings.max_tokens"
                                type="number"
                                :disabled="!isEditable"
                                min="1"
                                placeholder="2048"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Maximum response length
                            </p>
                        </div>

                        <!-- Top P -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Top P
                            </label>
                            <input
                                v-model.number="modelSettings.top_p"
                                type="number"
                                :disabled="!isEditable"
                                min="0"
                                max="1"
                                step="0.1"
                                placeholder="1"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Nucleus sampling threshold
                            </p>
                        </div>

                        <!-- Frequency Penalty -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Frequency Penalty
                            </label>
                            <input
                                v-model.number="modelSettings.frequency_penalty"
                                type="number"
                                :disabled="!isEditable"
                                min="-2"
                                max="2"
                                step="0.1"
                                placeholder="0"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Reduce word repetition
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Tab -->
            <div v-show="activeTab === 'preview'" class="space-y-6">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 font-medium text-zinc-900 dark:text-white">Prompt Preview</h3>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                        This is how your prompt will appear when sent to the AI. Variables will be replaced with actual content at runtime.
                    </p>

                    <div class="space-y-4">
                        <!-- System Message Preview -->
                        <div v-if="systemMessage">
                            <div class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                System
                            </div>
                            <div class="rounded-lg bg-zinc-100 p-4 font-mono text-sm whitespace-pre-wrap dark:bg-zinc-800">
                                {{ systemMessage }}
                            </div>
                        </div>

                        <!-- User Message Preview -->
                        <div v-if="userMessage">
                            <div class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                User
                            </div>
                            <div class="rounded-lg bg-blue-50 p-4 font-mono text-sm whitespace-pre-wrap dark:bg-blue-950/30">
                                {{ userMessage }}
                            </div>
                        </div>

                        <div v-if="!systemMessage && !userMessage" class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No content to preview. Add a system message or user message template.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div v-if="isEditable" class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-end gap-3">
                <Button variant="ghost" @click="emit('cancel')">Cancel</Button>
                <Button :disabled="!isValid || isLoading" @click="handleSave">
                    <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isCreating ? 'Create Prompt' : 'Save Changes' }}
                </Button>
            </div>
        </div>
    </div>
</template>
