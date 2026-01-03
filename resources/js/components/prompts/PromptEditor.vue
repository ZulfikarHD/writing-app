<script setup lang="ts">
import type { Prompt, PromptFormData, ModelSettings } from '@/composables/usePrompts';
import { useConfirm } from '@/composables/useConfirm';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import { Motion } from 'motion-v';
import { ref, watch, computed } from 'vue';

// Tab components
import TabGeneral from './editor/TabGeneral.vue';
import TabInstructions from './editor/TabInstructions.vue';
import TabAdvanced from './editor/TabAdvanced.vue';
import TabDescription from './editor/TabDescription.vue';
import PromptPreviewPanel from './editor/PromptPreviewPanel.vue';

// Types from tab components
import type { PromptMessage } from './editor/TabInstructions.vue';
import type { PromptInputDef, PromptComponentRef } from './editor/TabAdvanced.vue';

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
const { isReducedMotion, quickSpringConfig } = usePerformanceMode();

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
const activeTab = ref<TabId>('general');

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

// Initialize/reset form
function initializeForm() {
    if (props.prompt) {
        name.value = props.prompt.name;
        description.value = props.prompt.description || '';
        type.value = props.prompt.type;
        systemMessage.value = props.prompt.system_message || '';
        userMessage.value = props.prompt.user_message || '';
        messages.value = (props.prompt as any).messages || [];
        modelSettings.value = props.prompt.model_settings || {};
        // TODO: Load inputs and components when API is ready
        inputs.value = [];
        components.value = [];
    } else {
        name.value = '';
        description.value = '';
        type.value = 'chat';
        systemMessage.value = '';
        userMessage.value = '';
        messages.value = [];
        modelSettings.value = {};
        inputs.value = [];
        components.value = [];
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

    // Add messages if any
    if (messages.value.length > 0) {
        (data as any).messages = messages.value;
    }

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

// Handle variable insertion
function handleInsertVariable(variable: string, field: 'system' | 'user') {
    if (field === 'system') {
        systemMessage.value += variable;
    } else {
        userMessage.value += variable;
    }
}

// Handle component insertion
function handleInsertComponent(componentName: string) {
    // Insert component into system message at cursor position
    systemMessage.value += `[[${componentName}]]`;
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

            <!-- Read-only notice for system prompts -->
            <div
                v-if="prompt?.is_system"
                class="mt-4 flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>This is a system prompt and cannot be edited. Click <strong>"Clone"</strong> to create your own customized version.</span>
            </div>

            <!-- Tabs -->
            <div class="mt-4 flex gap-1 overflow-x-auto">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap"
                    :class="[
                        activeTab === tab.id
                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800',
                    ]"
                    @click="activeTab = tab.id"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                    </svg>
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
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
                    :is-editable="isEditable"
                    :is-creating="isCreating"
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
                    :is-editable="isEditable"
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
                    :is-editable="isEditable"
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
                    :is-editable="isEditable"
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
