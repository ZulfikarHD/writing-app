<script setup lang="ts">
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import Input from '@/components/ui/forms/Input.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import { Motion } from 'motion-v';
import { useComponents } from '@/composables/useComponents';
import { useConfirm } from '@/composables/useConfirm';
import { usePerformanceMode } from '@/composables/usePerformanceMode';
import type { PromptComponent, ComponentFormData } from '@/composables/useComponents';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    component: PromptComponent | null;
    isCreating?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', component: PromptComponent): void;
    (e: 'updated', component: PromptComponent): void;
    (e: 'deleted', id: number): void;
    (e: 'cloned', component: PromptComponent): void;
}>();

const { isReducedMotion, quickSpringConfig } = usePerformanceMode();
const {
    createComponent,
    updateComponent,
    deleteComponent,
    cloneComponent,
    copyIncludeCall,
    getIncludeSyntax,
    isValidName,
    isNameAvailable,
} = useComponents();
const { confirm: showConfirm } = useConfirm();

// Form state
const name = ref('');
const label = ref('');
const content = ref('');
const description = ref('');

// UI state
const isLoading = ref(false);
const hasChanges = ref(false);
const activeTab = ref<'instructions' | 'description'>('instructions');
const showCopiedBadge = ref(false);

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

// Initialize form from component
function initializeForm() {
    if (props.isCreating) {
        name.value = '';
        label.value = '';
        content.value = '';
        description.value = '';
        hasChanges.value = false;
        activeTab.value = 'instructions';
    } else if (props.component) {
        name.value = props.component.name;
        label.value = props.component.label;
        content.value = props.component.content;
        description.value = props.component.description || '';
        hasChanges.value = false;
        activeTab.value = 'instructions';
    }
}

// Watch for component changes
watch(() => props.component, initializeForm, { immediate: true });
watch(
    () => props.show,
    (show) => {
        if (show) {
            initializeForm();
        }
    }
);

// Track changes
watch([name, label, content, description], () => {
    if (props.isCreating) {
        hasChanges.value = name.value.trim().length > 0 || content.value.trim().length > 0;
    } else if (props.component) {
        hasChanges.value =
            name.value !== props.component.name ||
            label.value !== props.component.label ||
            content.value !== props.component.content ||
            description.value !== (props.component.description || '');
    }
});

// Name validation
const nameError = computed(() => {
    if (!name.value.trim()) return null;
    if (!isValidName(name.value)) {
        return 'Name must start with a letter or underscore, and contain only letters, numbers, and underscores';
    }
    if (!props.isCreating && props.component && name.value === props.component.name) {
        return null; // Same name is fine when editing
    }
    if (!isNameAvailable(name.value, props.component?.id)) {
        return 'A component with this name already exists';
    }
    return null;
});

// Form validation
const isValid = computed(() => {
    return (
        name.value.trim().length > 0 &&
        label.value.trim().length > 0 &&
        content.value.trim().length > 0 &&
        !nameError.value
    );
});

// Modal title
const modalTitle = computed(() => {
    if (props.isCreating) return 'New Component';
    return props.component?.label || 'Edit Component';
});

// Is system component
const isSystem = computed(() => {
    return props.component?.is_system === true;
});

// Can edit
const canEdit = computed(() => {
    return props.isCreating || !isSystem.value;
});

// Include syntax for copying
const includeSyntax = computed(() => {
    if (props.component) {
        return getIncludeSyntax(props.component);
    }
    return name.value ? `{include("${name.value}")}` : '';
});

// Handle copy include call
async function handleCopyInclude() {
    if (props.component) {
        const success = await copyIncludeCall(props.component);
        if (success) {
            showCopiedBadge.value = true;
            setTimeout(() => {
                showCopiedBadge.value = false;
            }, 2000);
        }
    } else if (name.value) {
        try {
            await navigator.clipboard.writeText(`{include("${name.value}")}`);
            showCopiedBadge.value = true;
            setTimeout(() => {
                showCopiedBadge.value = false;
            }, 2000);
        } catch {
            showToastMessage('danger', 'Error', 'Failed to copy to clipboard');
        }
    }
}

// Handle save (create or update)
async function handleSave() {
    if (!isValid.value || !canEdit.value) return;

    isLoading.value = true;

    const data: ComponentFormData = {
        name: name.value.trim(),
        label: label.value.trim(),
        content: content.value.trim(),
        description: description.value.trim() || null,
    };

    if (props.isCreating) {
        const created = await createComponent(data);
        isLoading.value = false;

        if (created) {
            showToastMessage('success', 'Created!', 'Component created successfully');
            emit('created', created);
            emit('close');
        } else {
            showToastMessage('danger', 'Error', 'Failed to create component');
        }
    } else if (props.component) {
        const updated = await updateComponent(props.component.id, data);
        isLoading.value = false;

        if (updated) {
            hasChanges.value = false;
            showToastMessage('success', 'Saved!', 'Component updated successfully');
            emit('updated', updated);
        } else {
            showToastMessage('danger', 'Error', 'Failed to update component');
        }
    }
}

// Handle clone
async function handleClone() {
    if (!props.component) return;

    isLoading.value = true;
    const cloned = await cloneComponent(props.component.id);
    isLoading.value = false;

    if (cloned) {
        showToastMessage('success', 'Cloned!', 'Component cloned successfully');
        emit('cloned', cloned);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to clone component');
    }
}

// Handle delete
async function handleDelete() {
    if (!props.component || isSystem.value) return;

    const confirmed = await showConfirm({
        title: 'Delete Component',
        message: `Are you sure you want to delete "${props.component.label}"? This action cannot be undone. Any prompts using this component will no longer resolve it.`,
        confirmText: 'Delete',
        confirmVariant: 'danger',
    });

    if (!confirmed) return;

    isLoading.value = true;
    const success = await deleteComponent(props.component.id);
    isLoading.value = false;

    if (success) {
        showToastMessage('success', 'Deleted!', 'Component deleted successfully');
        emit('deleted', props.component.id);
        emit('close');
    } else {
        showToastMessage('danger', 'Error', 'Failed to delete component');
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

// Tab configuration
const tabs = [
    { id: 'instructions' as const, label: 'Instructions' },
    { id: 'description' as const, label: 'Description' },
];
</script>

<template>
    <Modal :show="show" size="2xl" :scrollable="true" @close="handleClose">
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ modalTitle }}
                </h2>
                <Badge v-if="isSystem" variant="info">System</Badge>
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
                        Name (Identifier) <span class="text-red-500">*</span>
                    </label>
                    <Input
                        v-model="name"
                        placeholder="e.g., writing_style, codex_context"
                        class="w-full font-mono"
                        :disabled="!canEdit"
                        :class="{ 'border-red-500': nameError }"
                    />
                    <p v-if="nameError" class="mt-1 text-xs text-red-500">
                        {{ nameError }}
                    </p>
                    <p v-else class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Used in prompts as <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono text-xs dark:bg-zinc-800">{{ includeSyntax || '{include("name")}' }}</code>
                    </p>
                </div>

                <!-- Label Field -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Display Label <span class="text-red-500">*</span>
                    </label>
                    <Input
                        v-model="label"
                        placeholder="e.g., Writing Style Guide, Codex Context"
                        class="w-full"
                        :disabled="!canEdit"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Human-readable name shown in the component list
                    </p>
                </div>

                <!-- Copy Include Call Button -->
                <div v-if="name.trim()" class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="handleCopyInclude">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy include() call
                    </Button>
                    <Badge v-if="showCopiedBadge" variant="success">Copied!</Badge>
                    <code class="rounded bg-zinc-100 px-2 py-1 font-mono text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        {{ includeSyntax }}
                    </code>
                </div>

                <!-- Tabs -->
                <div class="border-b border-zinc-200 dark:border-zinc-700">
                    <nav class="-mb-px flex space-x-4">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            type="button"
                            class="border-b-2 px-1 pb-3 text-sm font-medium transition-colors"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-violet-500 text-violet-600 dark:border-violet-400 dark:text-violet-400'
                                    : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300',
                            ]"
                            @click="activeTab = tab.id"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Instructions Tab -->
                <div v-show="activeTab === 'instructions'">
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Instructions <span class="text-red-500">*</span>
                    </label>
                    <Textarea
                        v-model="content"
                        placeholder="Enter the instructions that will be injected when this component is included in a prompt..."
                        :rows="12"
                        class="w-full font-mono text-sm"
                        :disabled="!canEdit"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        These instructions will replace the <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono dark:bg-zinc-800">{{ includeSyntax || '{include("...")}' }}</code> call when the prompt runs.
                        You can also use prompt functions like <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono dark:bg-zinc-800">{input("name")}</code> within components.
                    </p>
                </div>

                <!-- Description Tab -->
                <div v-show="activeTab === 'description'">
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Description
                    </label>
                    <Textarea
                        v-model="description"
                        placeholder="Optional description explaining what this component does and when to use it..."
                        :rows="6"
                        class="w-full"
                        :disabled="!canEdit"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        This description is for documentation only and is not sent to the AI.
                    </p>
                </div>

                <!-- System Component Notice -->
                <div v-if="isSystem" class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                                System Component
                            </p>
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                This is a system component and cannot be edited or deleted. You can clone it to create your own version.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (for editing, not creating) -->
                <div v-if="!isCreating && component" class="flex gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <Button
                        variant="ghost"
                        size="sm"
                        :disabled="isLoading"
                        @click="handleClone"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Clone
                    </Button>
                    <Button
                        v-if="!isSystem"
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
                v-if="canEdit"
                :disabled="!isValid || (!hasChanges && !isCreating) || isLoading"
                @click="handleSave"
            >
                <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isCreating ? 'Create Component' : 'Save Changes' }}
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
