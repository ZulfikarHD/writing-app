<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import { Motion } from 'motion-v';
import { usePersonas } from '@/composables/usePersonas';
import { useConfirm } from '@/composables/useConfirm';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import type { Persona, PersonaFormData, InteractionType } from '@/composables/usePersonas';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    persona: Persona | null;
    isCreating?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', persona: Persona): void;
    (e: 'updated', persona: Persona): void;
    (e: 'deleted', id: number): void;
    (e: 'archived', id: number): void;
}>();

const { isReducedMotion, quickSpringConfig } = usePerformanceMode();
const { createPersona, updatePersona, deletePersona, archivePersona } = usePersonas();
const { confirm: showConfirm } = useConfirm();

// Form state
const name = ref('');
const description = ref('');
const systemMessage = ref('');
const selectedInteractionTypes = ref<InteractionType[]>([]);
const projectIds = ref<number[] | null>(null);
const isDefault = ref(false);

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

// Interaction type options
const interactionTypeOptions: { value: InteractionType; label: string }[] = [
    { value: 'chat', label: 'Workshop Chat' },
    { value: 'prose', label: 'Scene Beat Completion' },
    { value: 'replacement', label: 'Text Replacement' },
    { value: 'summary', label: 'Scene Summarization' },
];

// Initialize form from persona
function initializeForm() {
    if (props.isCreating) {
        // Reset for new persona
        name.value = '';
        description.value = '';
        systemMessage.value = '';
        selectedInteractionTypes.value = [];
        projectIds.value = null;
        isDefault.value = false;
        hasChanges.value = false;
    } else if (props.persona) {
        name.value = props.persona.name;
        description.value = props.persona.description || '';
        systemMessage.value = props.persona.system_message;
        selectedInteractionTypes.value = props.persona.interaction_types || [];
        projectIds.value = props.persona.project_ids;
        isDefault.value = props.persona.is_default;
        hasChanges.value = false;
    }
}

// Watch for persona changes
watch(() => props.persona, initializeForm, { immediate: true });
watch(() => props.show, (show) => {
    if (show) {
        initializeForm();
    }
});

// Track changes
watch([name, description, systemMessage, selectedInteractionTypes, projectIds, isDefault], () => {
    if (props.isCreating) {
        hasChanges.value = name.value.trim().length > 0 || systemMessage.value.trim().length > 0;
    } else if (props.persona) {
        hasChanges.value =
            name.value !== props.persona.name ||
            description.value !== (props.persona.description || '') ||
            systemMessage.value !== props.persona.system_message ||
            JSON.stringify(selectedInteractionTypes.value) !== JSON.stringify(props.persona.interaction_types || []) ||
            JSON.stringify(projectIds.value) !== JSON.stringify(props.persona.project_ids) ||
            isDefault.value !== props.persona.is_default;
    }
}, { deep: true });

// Form validation
const isValid = computed(() => {
    return name.value.trim().length > 0 && systemMessage.value.trim().length > 0;
});

// Modal title
const modalTitle = computed(() => {
    if (props.isCreating) return 'New Persona';
    return props.persona?.name || 'Edit Persona';
});

// Toggle interaction type selection
function toggleInteractionType(type: InteractionType) {
    const index = selectedInteractionTypes.value.indexOf(type);
    if (index === -1) {
        selectedInteractionTypes.value.push(type);
    } else {
        selectedInteractionTypes.value.splice(index, 1);
    }
}

// Check if interaction type is selected
function isTypeSelected(type: InteractionType): boolean {
    return selectedInteractionTypes.value.includes(type);
}

// Handle save (create or update)
async function handleSave() {
    if (!isValid.value) return;

    isLoading.value = true;

    const data: PersonaFormData = {
        name: name.value.trim(),
        description: description.value.trim() || null,
        system_message: systemMessage.value.trim(),
        interaction_types: selectedInteractionTypes.value.length > 0 ? selectedInteractionTypes.value : null,
        project_ids: projectIds.value,
        is_default: isDefault.value,
    };

    if (props.isCreating) {
        const created = await createPersona(data);
        isLoading.value = false;

        if (created) {
            showToastMessage('success', 'Created!', 'Persona created successfully');
            emit('created', created);
            emit('close');
        } else {
            showToastMessage('danger', 'Error', 'Failed to create persona');
        }
    } else if (props.persona) {
        const updated = await updatePersona(props.persona.id, data);
        isLoading.value = false;

        if (updated) {
            hasChanges.value = false;
            showToastMessage('success', 'Saved!', 'Persona updated successfully');
            emit('updated', updated);
        } else {
            showToastMessage('danger', 'Error', 'Failed to update persona');
        }
    }
}

// Handle archive
async function handleArchive() {
    if (!props.persona) return;

    const confirmed = await showConfirm({
        title: 'Archive Persona',
        message: `Are you sure you want to archive "${props.persona.name}"? You can restore it later.`,
        confirmText: 'Archive',
        confirmVariant: 'warning',
    });

    if (!confirmed) return;

    isLoading.value = true;
    const success = await archivePersona(props.persona.id);
    isLoading.value = false;

    if (success) {
        showToastMessage('success', 'Archived!', 'Persona archived successfully');
        emit('archived', props.persona.id);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to archive persona');
    }
}

// Handle delete
async function handleDelete() {
    if (!props.persona) return;

    const confirmed = await showConfirm({
        title: 'Delete Persona',
        message: `Are you sure you want to permanently delete "${props.persona.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });

    if (!confirmed) return;

    isLoading.value = true;
    const success = await deletePersona(props.persona.id);
    isLoading.value = false;

    if (success) {
        showToastMessage('success', 'Deleted!', 'Persona deleted successfully');
        emit('deleted', props.persona.id);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to delete persona');
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
                <Badge v-if="persona?.is_default" variant="info">Default</Badge>
                <Badge v-if="persona?.is_archived" variant="secondary">Archived</Badge>
                <Badge v-if="hasChanges && !isCreating" variant="warning">Unsaved</Badge>
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
                        placeholder="e.g., My Writing Style, J.K. Mystery"
                        class="w-full"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Give your persona a descriptive name
                    </p>
                </div>

                <!-- Description Field -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Description
                    </label>
                    <Input
                        v-model="description"
                        placeholder="Brief description of what this persona does"
                        class="w-full"
                    />
                </div>

                <!-- System Message Field -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        System Message <span class="text-red-500">*</span>
                    </label>
                    <Textarea
                        v-model="systemMessage"
                        placeholder="Instructions that will be injected into prompts. Markdown is supported."
                        :rows="8"
                        class="w-full font-mono text-sm"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        This message will be automatically included in prompts matching the selected interaction types.
                        Supports Markdown formatting.
                    </p>
                </div>

                <!-- Interaction Types -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Interaction Types
                    </label>
                    <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Select which prompt types this persona will apply to. Leave empty for all types.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in interactionTypeOptions"
                            :key="option.value"
                            type="button"
                            class="rounded-lg border px-3 py-2 text-sm font-medium transition-colors"
                            :class="[
                                isTypeSelected(option.value)
                                    ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-950/30 dark:text-violet-300'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600',
                            ]"
                            @click="toggleInteractionType(option.value)"
                        >
                            <span class="flex items-center gap-2">
                                <svg
                                    v-if="isTypeSelected(option.value)"
                                    class="h-4 w-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ option.label }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Project Scope Notice -->
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Project Scope
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                This persona will apply to all projects. Project-specific scoping will be available in a future update.
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
                            Set as default persona
                        </span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Default personas are always active for matching interaction types
                        </p>
                    </div>
                </div>

                <!-- Action Buttons (for editing, not creating) -->
                <div v-if="!isCreating && persona" class="flex gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="text-amber-600 hover:text-amber-700 dark:text-amber-400"
                        :disabled="isLoading"
                        @click="handleArchive"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Archive
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
                {{ isCreating ? 'Create Persona' : 'Save Changes' }}
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
