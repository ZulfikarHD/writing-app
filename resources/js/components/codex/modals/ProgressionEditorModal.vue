<script setup lang="ts">
/**
 * ProgressionEditorModal - Add codex progressions from the editor
 *
 * Sprint 15 (US-12.9): Allows writers to add character/location progressions
 * without leaving their writing flow. Triggered via /progression slash command
 * or Cmd+Shift+P keyboard shortcut.
 *
 * Key features:
 * - Searchable codex entry dropdown
 * - Auto-filled scene ID from current editor context
 * - Addition or Replace mode for detail updates
 * - Synchronous save (no queue workers)
 *
 * @see https://www.novelcrafter.com/help/docs/codex/progressions-additions
 */
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import Modal from '@/components/ui/layout/Modal.vue';
import Textarea from '@/components/ui/forms/Textarea.vue';
import CodexTypeIcon from '../shared/CodexTypeIcon.vue';
import axios from 'axios';
import { ref, watch, computed } from 'vue';

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    aliases: string[];
}

interface Detail {
    id: number;
    key_name: string;
}

const props = defineProps<{
    show: boolean;
    novelId: number;
    sceneId?: number; // Auto-filled from editor context
    sceneName?: string; // Display name for linked scene
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', data: { entryId: number; entryName: string }): void;
}>();

// State
const loading = ref(false);
const loadingEntries = ref(false);
const error = ref<string | null>(null);
const entries = ref<CodexEntry[]>([]);
const entryDetails = ref<Detail[]>([]);
const loadingDetails = ref(false);

// Form state
const searchQuery = ref('');
const selectedEntryId = ref<number | null>(null);
const formNote = ref('');
const formTimestamp = ref('');
const formNewValue = ref('');
const formMode = ref<'addition' | 'replace'>('addition');
const formDetailId = ref<number | null>(null);

// Computed: filtered entries based on search
const filteredEntries = computed(() => {
    if (!searchQuery.value) return entries.value;

    const query = searchQuery.value.toLowerCase();
    return entries.value.filter(entry =>
        entry.name.toLowerCase().includes(query) ||
        entry.aliases.some(alias => alias.toLowerCase().includes(query))
    );
});

// Get selected entry data
const selectedEntry = computed(() =>
    entries.value.find(e => e.id === selectedEntryId.value)
);

const canSubmit = computed(() =>
    selectedEntryId.value !== null && formNote.value.trim().length > 0
);

// Load entries when modal opens
watch(() => props.show, async (isOpen) => {
    if (isOpen) {
        resetForm();
        await loadEntries();
    }
});

// Load entry details when entry is selected
watch(selectedEntryId, async (entryId) => {
    if (entryId) {
        await loadEntryDetails(entryId);
    } else {
        entryDetails.value = [];
        formDetailId.value = null;
    }
});

const resetForm = () => {
    searchQuery.value = '';
    selectedEntryId.value = null;
    formNote.value = '';
    formTimestamp.value = '';
    formNewValue.value = '';
    formMode.value = 'addition';
    formDetailId.value = null;
    error.value = null;
    entryDetails.value = [];
};

const loadEntries = async () => {
    loadingEntries.value = true;
    try {
        const response = await axios.get(`/api/novels/${props.novelId}/codex`);
        entries.value = response.data.entries || [];
    } catch {
        error.value = 'Failed to load codex entries';
    } finally {
        loadingEntries.value = false;
    }
};

const loadEntryDetails = async (entryId: number) => {
    loadingDetails.value = true;
    try {
        const response = await axios.get(`/api/codex/${entryId}/details`);
        entryDetails.value = response.data.details || [];
    } catch {
        // Silently fail - details are optional
        entryDetails.value = [];
    } finally {
        loadingDetails.value = false;
    }
};

const selectEntry = (entryId: number) => {
    selectedEntryId.value = entryId;
    searchQuery.value = '';
};

const clearSelection = () => {
    selectedEntryId.value = null;
    entryDetails.value = [];
    formDetailId.value = null;
};

const handleSubmit = async () => {
    if (!canSubmit.value) return;

    loading.value = true;
    error.value = null;

    try {
        await axios.post(`/api/codex/${selectedEntryId.value}/progressions`, {
            note: formNote.value.trim(),
            story_timestamp: formTimestamp.value.trim() || null,
            new_value: formNewValue.value.trim() || null,
            mode: formMode.value,
            codex_detail_id: formDetailId.value,
            scene_id: props.sceneId || null,
        });

        emit('saved', {
            entryId: selectedEntryId.value!,
            entryName: selectedEntry.value?.name || 'Unknown',
        });
        emit('close');
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add progression';
    } finally {
        loading.value = false;
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :model-value="show" title="Add Codex Progression" size="lg" @close="handleClose">
        <div class="space-y-4">
            <!-- Error message -->
            <div
                v-if="error"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ error }}
            </div>

            <!-- Scene context indicator -->
            <div v-if="sceneId" class="rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-900/20">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                    Linked to current scene:
                </p>
                <p class="mt-0.5 text-sm font-medium text-emerald-900 dark:text-emerald-100">
                    {{ sceneName || `Scene #${sceneId}` }}
                </p>
            </div>

            <!-- Entry Selection -->
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Codex Entry <span class="text-red-500">*</span>
                </label>

                <!-- Selected entry display -->
                <div v-if="selectedEntry" class="flex items-center gap-3 rounded-lg border border-violet-300 bg-violet-50 p-3 dark:border-violet-700 dark:bg-violet-900/20">
                    <CodexTypeIcon :type="selectedEntry.type" size="md" />
                    <div class="flex-1">
                        <p class="font-medium text-violet-900 dark:text-violet-100">{{ selectedEntry.name }}</p>
                        <p class="text-xs text-violet-600 dark:text-violet-400">{{ selectedEntry.type }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-violet-500 hover:bg-violet-100 dark:hover:bg-violet-800"
                        @click="clearSelection"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Entry search/select -->
                <div v-else>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search codex entries..."
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 pr-8 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        />
                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Entry list -->
                    <div v-if="loadingEntries" class="py-4 text-center text-sm text-zinc-500">
                        Loading entries...
                    </div>
                    <div v-else-if="filteredEntries.length > 0" class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <button
                            v-for="entry in filteredEntries.slice(0, 20)"
                            :key="entry.id"
                            type="button"
                            class="flex w-full items-center gap-3 border-b border-zinc-100 px-3 py-2 text-left transition-colors last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                            @click="selectEntry(entry.id)"
                        >
                            <CodexTypeIcon :type="entry.type" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ entry.name }}
                                </p>
                                <p v-if="entry.aliases.length > 0" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    aka {{ entry.aliases.slice(0, 3).join(', ') }}
                                </p>
                            </div>
                        </button>
                    </div>
                    <p v-else-if="searchQuery" class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        No entries found matching "{{ searchQuery }}"
                    </p>
                </div>
            </div>

            <!-- Note -->
            <Textarea
                v-model="formNote"
                label="What changed?"
                placeholder="Describe the progression or change..."
                :rows="3"
                required
            />

            <!-- Story Timestamp -->
            <Input
                v-model="formTimestamp"
                label="Story Timestamp (optional)"
                placeholder="e.g., Chapter 5, Year 3, Day 45"
            />

            <!-- Detail Selection (only if entry has details) -->
            <div v-if="selectedEntryId && entryDetails.length > 0">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Related Detail (optional)
                </label>
                <select
                    v-model="formDetailId"
                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option :value="null">None</option>
                    <option v-for="detail in entryDetails" :key="detail.id" :value="detail.id">
                        {{ detail.key_name }}
                    </option>
                </select>
            </div>

            <!-- New Value (only if detail selected) -->
            <Input
                v-if="formDetailId"
                v-model="formNewValue"
                label="New Value"
                placeholder="New value for the selected detail"
            />

            <!-- Mode Toggle -->
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Progression Mode
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        :class="[
                            'rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            formMode === 'addition'
                                ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-800',
                        ]"
                        @click="formMode = 'addition'"
                    >
                        <span class="block font-medium">Addition</span>
                        <span class="block text-xs opacity-75">Appends to description</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            formMode === 'replace'
                                ? 'border-amber-500 bg-amber-50 text-amber-700 dark:border-amber-400 dark:bg-amber-900/30 dark:text-amber-300'
                                : 'border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-800',
                        ]"
                        @click="formMode = 'replace'"
                    >
                        <span class="block font-medium">Replace</span>
                        <span class="block text-xs opacity-75">Overwrites detail value</span>
                    </button>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button variant="ghost" @click="handleClose">Cancel</Button>
                <Button
                    :loading="loading"
                    :disabled="!canSubmit || loading"
                    @click="handleSubmit"
                >
                    Add Progression
                </Button>
            </div>
        </template>
    </Modal>
</template>
