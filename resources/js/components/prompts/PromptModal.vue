<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { usePrompts } from '@/composables/usePrompts';
import { useConfirm } from '@/composables/useConfirm';
import type { Prompt, PromptFormData, ModelSettings } from '@/composables/usePrompts';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    prompt: Prompt | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated', prompt: Prompt): void;
    (e: 'cloned', prompt: Prompt): void;
    (e: 'deleted', id: number): void;
}>();

const { updatePrompt, deletePrompt, clonePrompt } = usePrompts();
const { showConfirm } = useConfirm();

// Form state
const name = ref('');
const description = ref('');
const type = ref<'chat' | 'prose' | 'replacement' | 'summary'>('chat');
const systemMessage = ref('');
const userMessage = ref('');
const modelSettings = ref<ModelSettings>({});
const activeTab = ref<'content' | 'settings' | 'preview'>('content');
const isLoading = ref(false);
const hasChanges = ref(false);

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

// Types label
const types: Record<string, string> = {
    chat: 'Workshop Chat',
    prose: 'Scene Beat Completion',
    replacement: 'Text Replacement',
    summary: 'Scene Summarization',
};

// Initialize form from prompt
function initializeForm() {
    if (props.prompt) {
        name.value = props.prompt.name;
        description.value = props.prompt.description || '';
        type.value = props.prompt.type;
        systemMessage.value = props.prompt.system_message || '';
        userMessage.value = props.prompt.user_message || '';
        modelSettings.value = props.prompt.model_settings || {};
        hasChanges.value = false;
    }
}

// Watch for prompt changes
watch(() => props.prompt, initializeForm, { immediate: true });
watch(() => props.show, (show) => {
    if (show) {
        initializeForm();
        activeTab.value = 'content';
    }
});

// Track changes
watch([name, description, systemMessage, userMessage, modelSettings], () => {
    if (props.prompt) {
        hasChanges.value =
            name.value !== props.prompt.name ||
            description.value !== (props.prompt.description || '') ||
            systemMessage.value !== (props.prompt.system_message || '') ||
            userMessage.value !== (props.prompt.user_message || '') ||
            JSON.stringify(modelSettings.value) !== JSON.stringify(props.prompt.model_settings || {});
    }
}, { deep: true });

// Check if prompt is editable
const isEditable = computed(() => {
    return props.prompt && !props.prompt.is_system;
});

// Form validation
const isValid = computed(() => {
    return name.value.trim().length > 0;
});

// Handle save
async function handleSave() {
    if (!props.prompt || !isValid.value || !isEditable.value) return;

    isLoading.value = true;

    const data: PromptFormData = {
        name: name.value.trim(),
        description: description.value.trim() || null,
        type: type.value,
        system_message: systemMessage.value.trim() || null,
        user_message: userMessage.value.trim() || null,
        model_settings: Object.keys(modelSettings.value).length > 0 ? modelSettings.value : null,
    };

    const updated = await updatePrompt(props.prompt.id, data);
    isLoading.value = false;

    if (updated) {
        hasChanges.value = false;
        showToastMessage('success', 'Saved!', 'Prompt updated successfully');
        emit('updated', updated);
    } else {
        showToastMessage('danger', 'Error', 'Failed to update prompt');
    }
}

// Handle clone/duplicate
async function handleClone() {
    if (!props.prompt) return;

    isLoading.value = true;
    const clonedName = props.prompt.is_system
        ? `${props.prompt.name} (Custom)`
        : `${props.prompt.name} (Copy)`;

    const cloned = await clonePrompt(props.prompt.id, clonedName);
    isLoading.value = false;

    if (cloned) {
        showToastMessage('success', 'Cloned!', `Created "${cloned.name}"`);
        emit('cloned', cloned);
    } else {
        showToastMessage('danger', 'Error', 'Failed to clone prompt');
    }
}

// Handle delete
async function handleDelete() {
    if (!props.prompt || props.prompt.is_system) return;

    const confirmed = await showConfirm({
        title: 'Delete Prompt',
        message: `Are you sure you want to delete "${props.prompt.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });

    if (!confirmed) return;

    isLoading.value = true;
    const success = await deletePrompt(props.prompt.id);
    isLoading.value = false;

    if (success) {
        showToastMessage('success', 'Deleted!', 'Prompt deleted successfully');
        emit('deleted', props.prompt.id);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to delete prompt');
    }
}

// Handle close - check for unsaved changes
async function handleClose() {
    if (hasChanges.value && isEditable.value) {
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
</script>

<template>
    <Modal :show="show" size="xl" :scrollable="true" @close="handleClose">
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ prompt?.name || 'Prompt' }}
                </h2>
                <Badge v-if="prompt?.is_system" variant="secondary">System</Badge>
                <Badge v-if="hasChanges && isEditable" variant="warning">Unsaved</Badge>
            </div>
        </template>

        <div v-if="prompt" class="space-y-4">
            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    <!-- Clone Button (for all prompts) -->
                    <Button
                        variant="ghost"
                        size="sm"
                        :disabled="isLoading"
                        @click="handleClone"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        {{ prompt.is_system ? 'Duplicate to Edit' : 'Clone' }}
                    </Button>

                    <!-- Delete Button (only for user prompts) -->
                    <Button
                        v-if="isEditable"
                        variant="ghost"
                        size="sm"
                        class="text-red-600 hover:text-red-700 dark:text-red-400"
                        :disabled="isLoading"
                        @click="handleDelete"
                    >
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

                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ types[prompt.type] }}
                </div>
            </div>

            <!-- Read-only notice for system prompts -->
            <div
                v-if="prompt.is_system"
                class="flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>This is a system prompt and cannot be edited. Click <strong>"Duplicate to Edit"</strong> to create your own customized version.</span>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'content'
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="activeTab = 'content'"
                >
                    Content
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'settings'
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="activeTab = 'settings'"
                >
                    Settings
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="[
                        activeTab === 'preview'
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="activeTab = 'preview'"
                >
                    Preview
                </button>
            </div>

            <!-- Content Tab -->
            <div v-show="activeTab === 'content'" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Name <span v-if="isEditable" class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="name"
                        type="text"
                        :disabled="!isEditable"
                        placeholder="Enter prompt name..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Description
                    </label>
                    <input
                        v-model="description"
                        type="text"
                        :disabled="!isEditable"
                        placeholder="Brief description..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                    />
                </div>

                <!-- System Message -->
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            System Message
                        </label>
                        <div v-if="isEditable" class="flex gap-1">
                            <button
                                v-for="variable in availableVariables"
                                :key="variable"
                                type="button"
                                class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                @click="insertVariable(variable, 'system')"
                            >
                                {{ variable }}
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="systemMessage"
                        :disabled="!isEditable"
                        rows="6"
                        placeholder="Enter system instructions for the AI..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                    ></textarea>
                </div>

                <!-- User Message Template -->
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            User Message Template
                        </label>
                        <div v-if="isEditable" class="flex gap-1">
                            <button
                                v-for="variable in availableVariables"
                                :key="variable"
                                type="button"
                                class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                @click="insertVariable(variable, 'user')"
                            >
                                {{ variable }}
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="userMessage"
                        :disabled="!isEditable"
                        rows="4"
                        :placeholder="getUserMessagePlaceholder()"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                    ></textarea>
                </div>
            </div>

            <!-- Settings Tab -->
            <div v-show="activeTab === 'settings'" class="space-y-4">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Model Settings</h3>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                        Override model settings for this prompt. Leave empty to use defaults.
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Temperature -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
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
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
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
                                v-model.number="modelSettings.max_tokens"
                                type="number"
                                :disabled="!isEditable"
                                min="1"
                                placeholder="2048"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                            />
                        </div>

                        <!-- Top P -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
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
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                            />
                        </div>

                        <!-- Frequency Penalty -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
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
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-900 dark:disabled:text-zinc-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Tab -->
            <div v-show="activeTab === 'preview'" class="space-y-4">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Prompt Preview</h3>
                    <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                        Variables will be replaced with actual content at runtime.
                    </p>

                    <div class="space-y-4">
                        <!-- System Message Preview -->
                        <div v-if="systemMessage">
                            <div class="mb-1.5 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                System
                            </div>
                            <div class="rounded-lg bg-white p-3 font-mono text-sm whitespace-pre-wrap dark:bg-zinc-900">
                                {{ systemMessage }}
                            </div>
                        </div>

                        <!-- User Message Preview -->
                        <div v-if="userMessage">
                            <div class="mb-1.5 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                User
                            </div>
                            <div class="rounded-lg bg-blue-50 p-3 font-mono text-sm whitespace-pre-wrap dark:bg-blue-950/30">
                                {{ userMessage }}
                            </div>
                        </div>

                        <div v-if="!systemMessage && !userMessage" class="py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No content to preview.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="handleClose">
                {{ isEditable && hasChanges ? 'Cancel' : 'Close' }}
            </Button>
            <Button
                v-if="isEditable"
                :disabled="!isValid || !hasChanges || isLoading"
                @click="handleSave"
            >
                <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Save Changes
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
