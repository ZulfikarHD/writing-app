<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Modal from '@/components/ui/layout/Modal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import { usePromptSharing, type PromptImportPreview } from '@/composables/usePromptSharing';
import type { Prompt } from '@/composables/usePrompts';

interface Props {
    isOpen: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    imported: [prompt: Prompt];
}>();

const {
    isImporting,
    isPreviewing,
    error,
    previewFromClipboard,
    previewFromString,
    importFromString,
    clearError,
} = usePromptSharing();

const step = ref<'input' | 'preview'>('input');
const encodedInput = ref('');
const preview = ref<PromptImportPreview | null>(null);
const currentEncoded = ref('');

// Reset state when modal opens/closes
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        step.value = 'input';
        encodedInput.value = '';
        preview.value = null;
        currentEncoded.value = '';
        clearError();
    }
});

// Type label mapping
const typeLabels: Record<string, string> = {
    chat: 'Workshop Chat',
    prose: 'Scene Beat Completion',
    replacement: 'Text Replacement',
    summary: 'Scene Summarization',
};

const previewTypeLabel = computed(() => {
    if (!preview.value) return '';
    return typeLabels[preview.value.type] || preview.value.type;
});

async function handleReadFromClipboard() {
    clearError();
    const result = await previewFromClipboard();
    if (result) {
        preview.value = result;
        // Get clipboard content for later import
        try {
            currentEncoded.value = await navigator.clipboard.readText();
        } catch {
            // Fallback
        }
        step.value = 'preview';
    }
}

async function handlePreviewInput() {
    if (!encodedInput.value.trim()) return;
    
    clearError();
    const result = await previewFromString(encodedInput.value.trim());
    if (result) {
        preview.value = result;
        currentEncoded.value = encodedInput.value.trim();
        step.value = 'preview';
    }
}

async function handleImport() {
    if (!currentEncoded.value) return;
    
    clearError();
    const prompt = await importFromString(currentEncoded.value);
    if (prompt) {
        emit('imported', prompt);
        emit('close');
    }
}

function handleBack() {
    step.value = 'input';
    preview.value = null;
    clearError();
}
</script>

<template>
    <Modal :is-open="isOpen" size="md" @close="emit('close')">
        <template #header>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                {{ step === 'input' ? 'Import Prompt' : 'Preview Import' }}
            </h2>
        </template>

        <div class="p-4">
            <!-- Error Message -->
            <div
                v-if="error"
                class="mb-4 flex items-start gap-2 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-950/30 dark:text-red-300"
            >
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ error }}</span>
            </div>

            <!-- Step 1: Input -->
            <div v-if="step === 'input'" class="space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Import a prompt that was shared with you. You can either paste the prompt data directly or read from your clipboard.
                </p>

                <!-- Read from Clipboard Button -->
                <Button
                    class="w-full"
                    :disabled="isPreviewing"
                    @click="handleReadFromClipboard"
                >
                    <svg v-if="isPreviewing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Read from Clipboard
                </Button>

                <div class="flex items-center gap-3 text-xs text-zinc-400">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span>or paste manually</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>

                <!-- Manual Input -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Prompt Data
                    </label>
                    <textarea
                        v-model="encodedInput"
                        rows="4"
                        placeholder="Paste the encoded prompt string here..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        The prompt data looks like a long string of random characters.
                    </p>
                </div>

                <Button
                    class="w-full"
                    variant="secondary"
                    :disabled="!encodedInput.trim() || isPreviewing"
                    @click="handlePreviewInput"
                >
                    <svg v-if="isPreviewing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Preview
                </Button>
            </div>

            <!-- Step 2: Preview -->
            <div v-else-if="step === 'preview' && preview" class="space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Review the prompt before adding it to your library.
                </p>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                {{ preview.name }}
                            </h3>
                            <p v-if="preview.description" class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ preview.description }}
                            </p>
                        </div>
                        <Badge variant="secondary">{{ previewTypeLabel }}</Badge>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4"
                                :class="preview.has_system_message ? 'text-green-500' : 'text-zinc-300 dark:text-zinc-600'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path v-if="preview.has_system_message" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-zinc-600 dark:text-zinc-400">System message</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4"
                                :class="preview.has_user_message ? 'text-green-500' : 'text-zinc-300 dark:text-zinc-600'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path v-if="preview.has_user_message" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-zinc-600 dark:text-zinc-400">User message</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4"
                                :class="preview.has_model_settings ? 'text-green-500' : 'text-zinc-300 dark:text-zinc-600'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path v-if="preview.has_model_settings" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-zinc-600 dark:text-zinc-400">Model settings</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-violet-100 text-[10px] font-medium text-violet-700 dark:bg-violet-950 dark:text-violet-300">
                                {{ preview.input_count }}
                            </span>
                            <span class="text-zinc-600 dark:text-zinc-400">Input fields</span>
                        </div>
                    </div>

                    <div v-if="preview.exported_at" class="mt-3 border-t border-zinc-200 pt-3 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        Exported {{ new Date(preview.exported_at).toLocaleDateString() }}
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between gap-3">
                <Button v-if="step === 'preview'" variant="ghost" @click="handleBack">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </Button>
                <div v-else></div>
                
                <div class="flex items-center gap-2">
                    <Button variant="ghost" @click="emit('close')">Cancel</Button>
                    <Button
                        v-if="step === 'preview'"
                        :disabled="isImporting"
                        @click="handleImport"
                    >
                        <svg v-if="isImporting" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Add to Library
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
