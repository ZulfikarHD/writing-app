<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import axios from 'axios';
import { ref, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'imported'): void;
}>();

type Step = 'upload' | 'preview' | 'importing' | 'complete';

const step = ref<Step>('upload');
const file = ref<File | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

interface ValidEntry {
    index: number;
    name: string;
    type: string;
    aliases: number;
    details: number;
}

interface InvalidEntry {
    index: number;
    name: string;
    errors: string[];
}

interface ImportError {
    index: number;
    name?: string;
    error: string;
}

const preview = ref<{
    valid: ValidEntry[];
    invalid: InvalidEntry[];
    categories: number;
} | null>(null);

const importResult = ref<{
    imported: number;
    skipped: number;
    errors: ImportError[];
} | null>(null);

const fileInput = ref<HTMLInputElement | null>(null);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        file.value = target.files[0];
        error.value = null;
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        const droppedFile = event.dataTransfer.files[0];
        if (droppedFile.type === 'application/json' || droppedFile.name.endsWith('.json')) {
            file.value = droppedFile;
            error.value = null;
        } else {
            error.value = 'Please upload a JSON file';
        }
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
};

const previewImport = async () => {
    if (!file.value) return;

    loading.value = true;
    error.value = null;

    try {
        const formData = new FormData();
        formData.append('file', file.value);

        const response = await axios.post(
            `/api/novels/${props.novelId}/codex/import/preview`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        preview.value = response.data;
        step.value = 'preview';
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { error?: string; message?: string } } };
        error.value = axiosError.response?.data?.error || axiosError.response?.data?.message || 'Failed to preview import';
    } finally {
        loading.value = false;
    }
};

const executeImport = async () => {
    if (!file.value) return;

    step.value = 'importing';
    loading.value = true;
    error.value = null;

    try {
        const formData = new FormData();
        formData.append('file', file.value);

        const response = await axios.post(
            `/api/novels/${props.novelId}/codex/import`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        importResult.value = response.data;
        step.value = 'complete';
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { error?: string; message?: string } } };
        error.value = axiosError.response?.data?.error || axiosError.response?.data?.message || 'Failed to import entries';
        step.value = 'preview';
    } finally {
        loading.value = false;
    }
};

const reset = () => {
    step.value = 'upload';
    file.value = null;
    preview.value = null;
    importResult.value = null;
    error.value = null;
    loading.value = false;
};

const handleClose = () => {
    if (step.value === 'complete' && importResult.value && importResult.value.imported > 0) {
        emit('imported');
    }
    reset();
    emit('close');
};

const canPreview = computed(() => file.value !== null);
const canImport = computed(() => preview.value && preview.value.valid.length > 0);

const typeIcons: Record<string, string> = {
    character: '👤',
    location: '📍',
    item: '⚔️',
    lore: '📜',
    organization: '🏛️',
    subplot: '📖',
};
</script>

<template>
    <Modal :show="show" title="Import Codex Entries" max-width="lg" @close="handleClose">
        <div class="min-h-[300px]">
            <!-- Step 1: Upload -->
            <div v-if="step === 'upload'" class="space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Upload a JSON file exported from another novel's Codex to import entries.
                </p>

                <!-- Drop Zone -->
                <div
                    class="rounded-lg border-2 border-dashed border-zinc-300 p-8 text-center transition-colors hover:border-violet-400 dark:border-zinc-600 dark:hover:border-violet-500"
                    @drop="handleDrop"
                    @dragover="handleDragOver"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".json,application/json"
                        class="hidden"
                        @change="handleFileSelect"
                    />

                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>

                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <button
                            type="button"
                            class="font-medium text-violet-600 hover:underline dark:text-violet-400"
                            @click="fileInput?.click()"
                        >
                            Click to upload
                        </button>
                        or drag and drop
                    </p>
                    <p class="mt-1 text-xs text-zinc-500">JSON files only (max 5MB)</p>

                    <div v-if="file" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-50 px-3 py-2 dark:bg-violet-900/20">
                        <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-violet-700 dark:text-violet-300">{{ file.name }}</span>
                        <button
                            type="button"
                            class="ml-1 text-violet-500 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-200"
                            @click.stop="file = null"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <!-- Step 2: Preview -->
            <div v-else-if="step === 'preview' && preview" class="space-y-4">
                <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-800">
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-white">Import Preview</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ file?.name }}</p>
                    </div>
                    <button
                        type="button"
                        class="text-sm text-violet-600 hover:underline dark:text-violet-400"
                        @click="reset"
                    >
                        Change file
                    </button>
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-900/20">
                        <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ preview.valid.length }}</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-500">Ready to import</p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-900/20">
                        <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ preview.invalid.length }}</p>
                        <p class="text-xs text-amber-600 dark:text-amber-500">Will be skipped</p>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-900 dark:bg-blue-900/20">
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ preview.categories }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-500">Categories</p>
                    </div>
                </div>

                <!-- Valid entries -->
                <div v-if="preview.valid.length > 0" class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Name</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            <tr v-for="entry in preview.valid" :key="entry.index">
                                <td class="px-3 py-2 font-medium text-zinc-900 dark:text-white">{{ entry.name }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center gap-1">
                                        <span>{{ typeIcons[entry.type] || '📄' }}</span>
                                        <span class="capitalize text-zinc-600 dark:text-zinc-400">{{ entry.type }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-zinc-500 dark:text-zinc-400">
                                    {{ entry.aliases }} aliases, {{ entry.details }} details
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Invalid entries -->
                <div v-if="preview.invalid.length > 0">
                    <p class="mb-2 text-sm font-medium text-amber-700 dark:text-amber-400">
                        The following entries will be skipped:
                    </p>
                    <div class="max-h-32 overflow-y-auto rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-900/20">
                        <ul class="space-y-1 text-sm text-amber-700 dark:text-amber-300">
                            <li v-for="entry in preview.invalid" :key="entry.index">
                                <strong>{{ entry.name }}</strong>: {{ entry.errors.join(', ') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <!-- Step 3: Importing -->
            <div v-else-if="step === 'importing'" class="flex flex-col items-center justify-center py-12">
                <div class="h-12 w-12 animate-spin rounded-full border-4 border-violet-500 border-t-transparent" />
                <p class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">Importing entries...</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Please wait while we process your file</p>
            </div>

            <!-- Step 4: Complete -->
            <div v-else-if="step === 'complete' && importResult" class="space-y-4">
                <div class="rounded-lg bg-emerald-50 p-6 text-center dark:bg-emerald-900/20">
                    <svg class="mx-auto h-12 w-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-lg font-medium text-emerald-700 dark:text-emerald-300">Import Complete!</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-center dark:border-emerald-900 dark:bg-emerald-900/20">
                        <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">{{ importResult.imported }}</p>
                        <p class="text-sm text-emerald-600 dark:text-emerald-500">Entries imported</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-3xl font-bold text-zinc-700 dark:text-zinc-300">{{ importResult.skipped }}</p>
                        <p class="text-sm text-zinc-500">Skipped</p>
                    </div>
                </div>

                <div v-if="importResult.errors.length > 0" class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-900/20">
                    <p class="mb-2 text-sm font-medium text-amber-700 dark:text-amber-400">Import errors:</p>
                    <ul class="space-y-1 text-sm text-amber-600 dark:text-amber-300">
                        <li v-for="err in importResult.errors" :key="err.index">
                            {{ err.name || `Entry ${err.index}` }}: {{ err.error }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button variant="ghost" @click="handleClose">
                    {{ step === 'complete' ? 'Close' : 'Cancel' }}
                </Button>
                <Button
                    v-if="step === 'upload'"
                    :loading="loading"
                    :disabled="!canPreview || loading"
                    @click="previewImport"
                >
                    Preview Import
                </Button>
                <Button
                    v-if="step === 'preview'"
                    :loading="loading"
                    :disabled="!canImport || loading"
                    @click="executeImport"
                >
                    Import {{ preview?.valid.length }} Entries
                </Button>
            </div>
        </template>
    </Modal>
</template>
