<script setup lang="ts">
/**
 * BulkCreateModal - Create multiple codex entries at once
 *
 * Sprint 15 (US-12.12): Enables rapid codex setup by parsing multi-line
 * input in the format: "Name | Type | Description" (one per line)
 *
 * Key features:
 * - Two-step flow: Preview then Create
 * - Line-by-line validation with clear error messages
 * - Type suggestions for typos
 * - Duplicate detection
 * - Synchronous creation (no queue workers)
 *
 * @see https://www.novelcrafter.com/help/docs/codex/the-codex
 */
import Button from '@/components/ui/buttons/Button.vue';
import Modal from '@/components/ui/layout/Modal.vue';
import CodexTypeIcon from '../shared/CodexTypeIcon.vue';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

interface ParsedEntry {
    line: number;
    name: string;
    type: string;
    description: string | null;
}

interface ParseError {
    line: number;
    message: string;
    raw: string;
}

interface Warning {
    line: number;
    name: string;
    message: string;
}

interface CreatedEntry {
    id: number;
    name: string;
    type: string;
}

const props = defineProps<{
    show: boolean;
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created', entries: CreatedEntry[]): void;
}>();

// State
const step = ref<'input' | 'preview' | 'success'>('input');
const loading = ref(false);
const error = ref<string | null>(null);

// Input
const rawInput = ref('');

// Preview results
const parsedEntries = ref<ParsedEntry[]>([]);
const parseErrors = ref<ParseError[]>([]);
const warnings = ref<Warning[]>([]);

// Creation results
const createdEntries = ref<CreatedEntry[]>([]);
const skippedEntries = ref<{ name: string; reason: string }[]>([]);

// Options
const skipDuplicates = ref(true);

// Computed
const hasInput = computed(() => rawInput.value.trim().length > 0);
const lineCount = computed(() => {
    if (!rawInput.value.trim()) return 0;
    return rawInput.value.trim().split('\n').filter(line => line.trim() && !line.trim().startsWith('#')).length;
});

const canPreview = computed(() => hasInput.value && !loading.value);
const canCreate = computed(() => parsedEntries.value.length > 0 && parseErrors.value.length === 0 && !loading.value);

// Reset when modal closes/opens
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        resetForm();
    }
});

const resetForm = () => {
    step.value = 'input';
    rawInput.value = '';
    parsedEntries.value = [];
    parseErrors.value = [];
    warnings.value = [];
    createdEntries.value = [];
    skippedEntries.value = [];
    error.value = null;
    skipDuplicates.value = true;
};

const handlePreview = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/novels/${props.novelId}/codex/bulk-create`, {
            input: rawInput.value,
            preview: true,
            skip_duplicates: skipDuplicates.value,
        });

        if (response.data.success) {
            parsedEntries.value = response.data.entries || [];
            warnings.value = response.data.warnings || [];
            parseErrors.value = [];
            step.value = 'preview';
        }
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string; parse_errors?: ParseError[]; valid_count?: number } } };

        if (axiosError.response?.data?.parse_errors) {
            parseErrors.value = axiosError.response.data.parse_errors;
            parsedEntries.value = [];
            step.value = 'preview';
        } else {
            error.value = axiosError.response?.data?.message || 'Failed to parse input';
        }
    } finally {
        loading.value = false;
    }
};

const handleCreate = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/novels/${props.novelId}/codex/bulk-create`, {
            input: rawInput.value,
            preview: false,
            skip_duplicates: skipDuplicates.value,
        });

        if (response.data.success) {
            createdEntries.value = response.data.created || [];
            skippedEntries.value = response.data.skipped || [];
            step.value = 'success';

            // Emit created event for parent to refresh
            emit('created', createdEntries.value);
        }
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to create entries';
    } finally {
        loading.value = false;
    }
};

const handleBackToInput = () => {
    step.value = 'input';
    parseErrors.value = [];
    warnings.value = [];
};

const handleClose = () => {
    emit('close');
};

const insertExample = () => {
    rawInput.value = `# Example entries (lines starting with # are ignored)
Alice | character | A young witch and the protagonist
Bob | character | Alice's mentor and father figure
The Dark Forest | location | A mysterious forest filled with ancient magic
Moonstone Staff | item | Alice's magical staff
Council of Elders | organization | The ruling body of witches`;
};

// Type configuration for display
const typeConfig: Record<string, { label: string; icon: string }> = {
    character: { label: 'Character', icon: '👤' },
    location: { label: 'Location', icon: '📍' },
    item: { label: 'Item', icon: '⚔️' },
    lore: { label: 'Lore', icon: '📜' },
    organization: { label: 'Organization', icon: '🏛️' },
    subplot: { label: 'Subplot', icon: '📖' },
};
</script>

<template>
    <Modal :model-value="show" title="Bulk Create Codex Entries" size="xl" @close="handleClose">
        <!-- Step 1: Input -->
        <div v-if="step === 'input'" class="space-y-4">
            <!-- Instructions -->
            <div class="rounded-lg bg-violet-50 p-3 dark:bg-violet-900/20">
                <p class="text-sm font-medium text-violet-900 dark:text-violet-100">
                    Enter one entry per line in the format:
                </p>
                <code class="mt-1 block text-xs text-violet-700 dark:text-violet-300">
                    Name | Type | Description (optional)
                </code>
                <p class="mt-2 text-xs text-violet-600 dark:text-violet-400">
                    Valid types: character, location, item, lore, organization, subplot
                </p>
            </div>

            <!-- Textarea -->
            <!-- Sprint 15: Mobile-optimized with responsive rows -->
            <div>
                <div class="mb-1 flex items-center justify-between">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Entries
                    </label>
                    <button
                        type="button"
                        class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                        @click="insertExample"
                    >
                        Insert Example
                    </button>
                </div>
                <textarea
                    v-model="rawInput"
                    placeholder="Alice | character | A young witch and the protagonist&#10;The Dark Forest | location | A mysterious forest&#10;Moonstone Staff | item | Alice's magical staff"
                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm"
                    :class="[
                        // Responsive: fewer rows on mobile for better UX
                        'min-h-[200px] sm:min-h-[300px]',
                    ]"
                />
                <div class="mt-1 flex flex-col gap-1 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between dark:text-zinc-400">
                    <span>{{ lineCount }} {{ lineCount === 1 ? 'entry' : 'entries' }} detected</span>
                    <span class="hidden sm:inline">Lines starting with # are ignored</span>
                </div>
                <!-- Mobile-only tip -->
                <p class="mt-2 text-xs text-zinc-400 sm:hidden dark:text-zinc-500">
                    💡 Tip: Use landscape mode for easier editing
                </p>
            </div>

            <!-- Options -->
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    v-model="skipDuplicates"
                    type="checkbox"
                    class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                />
                <span class="text-sm text-zinc-700 dark:text-zinc-300">Skip entries with duplicate names</span>
            </label>

            <!-- Error -->
            <div
                v-if="error"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ error }}
            </div>
        </div>

        <!-- Step 2: Preview -->
        <div v-else-if="step === 'preview'" class="space-y-4">
            <!-- Parse Errors -->
            <div v-if="parseErrors.length > 0">
                <h4 class="mb-2 text-sm font-medium text-red-700 dark:text-red-400">
                    {{ parseErrors.length }} {{ parseErrors.length === 1 ? 'Error' : 'Errors' }} Found
                </h4>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-900/20">
                    <div v-for="err in parseErrors" :key="err.line" class="text-sm">
                        <span class="font-medium text-red-700 dark:text-red-400">Line {{ err.line }}:</span>
                        <span class="text-red-600 dark:text-red-300"> {{ err.message }}</span>
                        <div class="mt-0.5 truncate font-mono text-xs text-red-500 dark:text-red-400">
                            {{ err.raw }}
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Fix the errors above and try again.
                </p>
            </div>

            <!-- Warnings -->
            <div v-if="warnings.length > 0 && parseErrors.length === 0">
                <h4 class="mb-2 text-sm font-medium text-amber-700 dark:text-amber-400">
                    {{ warnings.length }} {{ warnings.length === 1 ? 'Warning' : 'Warnings' }}
                </h4>
                <div class="max-h-32 space-y-1 overflow-y-auto rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-900/20">
                    <div v-for="warn in warnings" :key="`${warn.line}-${warn.name}`" class="text-xs">
                        <span class="font-medium text-amber-700 dark:text-amber-300">{{ warn.name }}:</span>
                        <span class="text-amber-600 dark:text-amber-400"> {{ warn.message }}</span>
                    </div>
                </div>
            </div>

            <!-- Valid Entries Preview -->
            <div v-if="parsedEntries.length > 0">
                <h4 class="mb-2 text-sm font-medium text-zinc-900 dark:text-white">
                    {{ parsedEntries.length }} {{ parsedEntries.length === 1 ? 'Entry' : 'Entries' }} Ready to Create
                </h4>
                <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-zinc-50 dark:bg-zinc-800">
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">#</th>
                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">Name</th>
                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">Type</th>
                                <th class="px-3 py-2 text-left font-medium text-zinc-600 dark:text-zinc-400">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            <tr v-for="entry in parsedEntries" :key="entry.line" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-3 py-2 text-zinc-400 dark:text-zinc-500">{{ entry.line }}</td>
                                <td class="px-3 py-2 font-medium text-zinc-900 dark:text-white">{{ entry.name }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-700">
                                        <span>{{ typeConfig[entry.type]?.icon || '📄' }}</span>
                                        <span class="capitalize">{{ entry.type }}</span>
                                    </span>
                                </td>
                                <td class="max-w-xs truncate px-3 py-2 text-zinc-600 dark:text-zinc-400">
                                    {{ entry.description || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Step 3: Success -->
        <div v-else-if="step === 'success'" class="space-y-4">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="mt-3 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ createdEntries.length }} {{ createdEntries.length === 1 ? 'Entry' : 'Entries' }} Created!
                </h3>
                <p v-if="skippedEntries.length > 0" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ skippedEntries.length }} skipped (duplicates)
                </p>
            </div>

            <!-- Created entries list -->
            <div v-if="createdEntries.length > 0" class="max-h-48 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div
                    v-for="entry in createdEntries"
                    :key="entry.id"
                    class="flex items-center gap-3 border-b border-zinc-100 px-3 py-2 last:border-b-0 dark:border-zinc-800"
                >
                    <CodexTypeIcon :type="entry.type" size="sm" />
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ entry.name }}</span>
                </div>
            </div>

            <!-- Skipped entries -->
            <div v-if="skippedEntries.length > 0" class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                <h4 class="text-xs font-medium text-amber-700 dark:text-amber-400">Skipped Entries:</h4>
                <ul class="mt-1 space-y-0.5">
                    <li v-for="skipped in skippedEntries" :key="skipped.name" class="text-xs text-amber-600 dark:text-amber-400">
                        {{ skipped.name }} — {{ skipped.reason }}
                    </li>
                </ul>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between">
                <!-- Left side -->
                <div>
                    <Button v-if="step === 'preview'" variant="ghost" size="sm" @click="handleBackToInput">
                        <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Edit
                    </Button>
                </div>

                <!-- Right side -->
                <div class="flex gap-2">
                    <Button variant="ghost" @click="handleClose">
                        {{ step === 'success' ? 'Close' : 'Cancel' }}
                    </Button>

                    <Button
                        v-if="step === 'input'"
                        :loading="loading"
                        :disabled="!canPreview"
                        @click="handlePreview"
                    >
                        Preview
                    </Button>

                    <Button
                        v-else-if="step === 'preview'"
                        :loading="loading"
                        :disabled="!canCreate"
                        @click="handleCreate"
                    >
                        Create {{ parsedEntries.length }} {{ parsedEntries.length === 1 ? 'Entry' : 'Entries' }}
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
