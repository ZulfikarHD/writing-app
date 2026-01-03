<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { Motion } from 'motion-v';
import { usePrompts } from '@/composables/usePrompts';
import { useConfirm } from '@/composables/useConfirm';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import type { Prompt, PromptFormData, ModelSettings, PromptMessage } from '@/composables/usePrompts';
import { ref, watch, computed } from 'vue';

// Tab components
import TabGeneral from './editor/TabGeneral.vue';
import TabInstructions from './editor/TabInstructions.vue';
import TabAdvanced from './editor/TabAdvanced.vue';
import TabDescription from './editor/TabDescription.vue';
import PromptPreviewPanel from './editor/PromptPreviewPanel.vue';

// Types from tab components
import type { PromptInputDef, PromptComponentRef } from './editor/TabAdvanced.vue';

// Performance mode for animations
const { isReducedMotion, quickSpringConfig } = usePerformanceMode();

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
const { confirm: showConfirm } = useConfirm();

// Form state
const name = ref('');
const description = ref('');
const type = ref<'chat' | 'prose' | 'replacement' | 'summary'>('chat');
const systemMessage = ref('');
const userMessage = ref('');
const messages = ref<PromptMessage[]>([]);
const modelSettings = ref<ModelSettings>({});
const inputs = ref<PromptInputDef[]>([]);
const components = ref<PromptComponentRef[]>([]);

// Tab state
type TabId = 'general' | 'instructions' | 'advanced' | 'description' | 'preview';
const activeTab = ref<TabId>('instructions');

const tabs: { id: TabId; label: string; icon: string }[] = [
    {
        id: 'general',
        label: 'General',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        id: 'instructions',
        label: 'Instructions',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        id: 'advanced',
        label: 'Advanced',
        icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
    },
    {
        id: 'description',
        label: 'Description',
        icon: 'M4 6h16M4 12h16M4 18h7',
    },
    {
        id: 'preview',
        label: 'Preview',
        icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    },
];

// UI state
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
        messages.value = props.prompt.messages || [];
        modelSettings.value = props.prompt.model_settings || {};
        inputs.value = [];
        components.value = [];
        hasChanges.value = false;
    }
}

// Watch for prompt changes
watch(() => props.prompt, initializeForm, { immediate: true });
watch(() => props.show, (show) => {
    if (show) {
        initializeForm();
        activeTab.value = 'instructions';
    }
});

// Track changes
watch([name, description, systemMessage, userMessage, messages, modelSettings], () => {
    if (props.prompt) {
        hasChanges.value =
            name.value !== props.prompt.name ||
            description.value !== (props.prompt.description || '') ||
            systemMessage.value !== (props.prompt.system_message || '') ||
            userMessage.value !== (props.prompt.user_message || '') ||
            JSON.stringify(messages.value) !== JSON.stringify(props.prompt.messages || []) ||
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
        messages: messages.value.length > 0 ? messages.value : null,
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

// Handle variable insertion (from TabInstructions)
function handleInsertVariable(variable: string, field: 'system' | 'user') {
    if (field === 'system') {
        systemMessage.value += variable;
    } else {
        userMessage.value += variable;
    }
}

// Handle component insertion (from TabAdvanced)
function handleInsertComponent(componentName: string) {
    systemMessage.value += `[[${componentName}]]`;
}
</script>

<template>
    <Modal :show="show" size="3xl" :scrollable="true" @close="handleClose">
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
            <div class="flex gap-1 overflow-x-auto border-b border-zinc-200 dark:border-zinc-700">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="flex shrink-0 items-center gap-1.5 border-b-2 px-3 py-2 text-sm font-medium transition-colors whitespace-nowrap"
                    :class="[
                        activeTab === tab.id
                            ? 'border-violet-500 text-violet-600 dark:text-violet-400'
                            : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white',
                    ]"
                    @click="activeTab = tab.id"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                    </svg>
                    {{ tab.label }}
                </button>
            </div>

            <!-- General Tab -->
            <Motion
                v-if="activeTab === 'general'"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -8 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
            >
                <TabGeneral
                    :name="name"
                    :type="type"
                    :types="types"
                    :model-settings="modelSettings"
                    :is-editable="isEditable ?? false"
                    :is-creating="false"
                    @update:name="name = $event"
                    @update:type="type = $event"
                    @update:model-settings="modelSettings = $event"
                />
            </Motion>

            <!-- Instructions Tab -->
            <Motion
                v-if="activeTab === 'instructions'"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -8 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
            >
                <TabInstructions
                    :system-message="systemMessage"
                    :user-message="userMessage"
                    :messages="messages"
                    :prompt-type="type"
                    :is-editable="isEditable ?? false"
                    @update:system-message="systemMessage = $event"
                    @update:user-message="userMessage = $event"
                    @update:messages="messages = $event"
                    @insert-variable="handleInsertVariable"
                />
            </Motion>

            <!-- Advanced Tab -->
            <Motion
                v-if="activeTab === 'advanced'"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -8 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
            >
                <TabAdvanced
                    :inputs="inputs"
                    :components="components"
                    :is-editable="isEditable ?? false"
                    @update:inputs="inputs = $event"
                    @insert-component="handleInsertComponent"
                />
            </Motion>

            <!-- Description Tab -->
            <Motion
                v-if="activeTab === 'description'"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -8 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
            >
                <TabDescription
                    :description="description"
                    :is-editable="isEditable ?? false"
                    @update:description="description = $event"
                />
            </Motion>

            <!-- Preview Tab -->
            <Motion
                v-if="activeTab === 'preview'"
                :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, x: -8 }"
                :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, x: 0 }"
                :transition="quickSpringConfig"
            >
                <PromptPreviewPanel
                    :system-message="systemMessage"
                    :user-message="userMessage"
                    :messages="messages"
                    :prompt-type="type"
                />
            </Motion>
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
