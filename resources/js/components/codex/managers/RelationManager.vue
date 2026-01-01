<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Modal from '@/components/ui/Modal.vue';
import Select from '@/components/ui/Select.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed } from 'vue';
import CodexTypeIcon from './CodexTypeIcon.vue';

interface RelationEntry {
    id: number;
    name: string;
    type: string;
}

interface Relation {
    id: number;
    relation_type: string;
    label: string | null;
    is_bidirectional: boolean;
    target?: RelationEntry;
    source?: RelationEntry;
    direction?: 'outgoing' | 'incoming';
}

interface AvailableEntry {
    id: number;
    type: string;
    name: string;
    aliases: string[];
}

const props = defineProps<{
    entryId: number;
    novelId: number;
    outgoingRelations: Relation[];
    incomingRelations: Relation[];
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const localOutgoing = ref<Relation[]>([...props.outgoingRelations]);
const localIncoming = ref<Relation[]>([...props.incomingRelations]);
const showAddModal = ref(false);
const availableEntries = ref<AvailableEntry[]>([]);
const loadingEntries = ref(false);
const error = ref<string | null>(null);
const loading = ref(false);

// Form state
const selectedEntryId = ref<number | null>(null);
const relationType = ref('');
const relationLabel = ref('');
const isBidirectional = ref(false);

const commonRelationTypes = [
    { value: 'parent_of', label: 'Parent of' },
    { value: 'child_of', label: 'Child of' },
    { value: 'sibling_of', label: 'Sibling of' },
    { value: 'spouse_of', label: 'Spouse of' },
    { value: 'friend_of', label: 'Friend of' },
    { value: 'enemy_of', label: 'Enemy of' },
    { value: 'mentor_of', label: 'Mentor of' },
    { value: 'works_for', label: 'Works for' },
    { value: 'owns', label: 'Owns' },
    { value: 'located_in', label: 'Located in' },
    { value: 'contains', label: 'Contains' },
    { value: 'related_to', label: 'Related to' },
];

const allRelations = computed(() => [
    ...localOutgoing.value.map((r) => ({ ...r, direction: 'outgoing' as const })),
    ...localIncoming.value.map((r) => ({ ...r, direction: 'incoming' as const })),
]);

const openAddModal = async () => {
    showAddModal.value = true;
    loadingEntries.value = true;

    try {
        const response = await axios.get(`/api/novels/${props.novelId}/codex`);
        // Filter out current entry
        availableEntries.value = response.data.entries.filter((e: AvailableEntry) => e.id !== props.entryId);
    } catch {
        error.value = 'Failed to load entries';
    } finally {
        loadingEntries.value = false;
    }
};

const closeModal = () => {
    showAddModal.value = false;
    selectedEntryId.value = null;
    relationType.value = '';
    relationLabel.value = '';
    isBidirectional.value = false;
    error.value = null;
};

const addRelation = async () => {
    if (!selectedEntryId.value || !relationType.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/codex/${props.entryId}/relations`, {
            target_entry_id: selectedEntryId.value,
            relation_type: relationType.value,
            label: relationLabel.value || null,
            is_bidirectional: isBidirectional.value,
        });

        localOutgoing.value.push(response.data.relation);
        closeModal();
        emit('updated');
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add relation';
    } finally {
        loading.value = false;
    }
};

const deleteRelation = async (relation: Relation) => {
    loading.value = true;
    error.value = null;

    try {
        await axios.delete(`/api/codex/relations/${relation.id}`);

        if (relation.direction === 'outgoing') {
            localOutgoing.value = localOutgoing.value.filter((r) => r.id !== relation.id);
        } else {
            localIncoming.value = localIncoming.value.filter((r) => r.id !== relation.id);
        }
        emit('updated');
    } catch {
        error.value = 'Failed to delete relation';
    } finally {
        loading.value = false;
    }
};

/**
 * Swap relation direction (source ↔ target).
 * Sprint 15 (US-12.14): Allows fixing relation direction mistakes.
 */
const swapRelation = async (relation: Relation) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/codex/relations/${relation.id}/swap`);

        if (response.data.success) {
            // Reload relations to get updated state
            emit('updated');
        }
    } catch {
        error.value = 'Failed to swap relation direction';
    } finally {
        loading.value = false;
    }
};

const getRelatedEntry = (relation: Relation) => {
    return relation.direction === 'outgoing' ? relation.target : relation.source;
};

const getRelationLabel = (relation: Relation) => {
    const label = relation.label || relation.relation_type.replace(/_/g, ' ');
    if (relation.direction === 'incoming') {
        // Try to invert the relationship for display
        const inversions: Record<string, string> = {
            parent_of: 'child of',
            child_of: 'parent of',
            mentor_of: 'student of',
            works_for: 'employer of',
            owns: 'owned by',
            located_in: 'contains',
            contains: 'located in',
        };
        return inversions[relation.relation_type] || label;
    }
    return label;
};
</script>

<template>
    <div>
        <!-- Error Message -->
        <div v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
            {{ error }}
            <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="error = null">×</button>
        </div>

        <!-- Relations List -->
        <div v-if="allRelations.length > 0" class="space-y-2">
            <div
                v-for="relation in allRelations"
                :key="`${relation.direction}-${relation.id}`"
                class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
            >
                <div class="flex items-center gap-3">
                    <CodexTypeIcon :type="getRelatedEntry(relation)!.type" size="md" />
                    <div>
                        <Link
                            :href="`/codex/${getRelatedEntry(relation)!.id}`"
                            class="font-medium text-zinc-900 hover:text-violet-600 dark:text-white dark:hover:text-violet-400"
                        >
                            {{ getRelatedEntry(relation)!.name }}
                        </Link>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ getRelationLabel(relation) }}
                            <span v-if="relation.is_bidirectional" class="ml-1 text-xs">(↔ bidirectional)</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <!-- Swap Direction Button (Sprint 15: US-12.14) -->
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 transition-colors hover:bg-violet-100 hover:text-violet-600 dark:hover:bg-violet-900/30 dark:hover:text-violet-400"
                        :disabled="loading"
                        title="Swap direction"
                        @click="swapRelation(relation)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </button>
                    <!-- Delete Button -->
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                        :disabled="loading"
                        title="Delete relation"
                        @click="deleteRelation(relation)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <Button size="sm" variant="ghost" class="mt-3" @click="openAddModal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Relation
        </Button>

        <!-- Empty State -->
        <p v-if="allRelations.length === 0" class="mt-2 text-sm text-zinc-400 italic dark:text-zinc-500">
            No relations defined yet. Connect this entry to other story elements.
        </p>

        <!-- Add Relation Modal -->
        <Modal v-model="showAddModal" title="Add Relation" @close="closeModal">
            <div class="space-y-4">
                <!-- Entry Selection -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Related Entry</label>
                    <div v-if="loadingEntries" class="py-4 text-center text-sm text-zinc-500">Loading entries...</div>
                    <select
                        v-else
                        v-model="selectedEntryId"
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option :value="null">Select an entry...</option>
                        <option v-for="entry in availableEntries" :key="entry.id" :value="entry.id">
                            {{ entry.name }} ({{ entry.type }})
                        </option>
                    </select>
                </div>

                <!-- Relation Type -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Relation Type</label>
                    <Select v-model="relationType" placeholder="Select relation type...">
                        <option v-for="type in commonRelationTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </Select>
                </div>

                <!-- Custom Label -->
                <Input v-model="relationLabel" label="Custom Label (optional)" placeholder="e.g., 'Best friend since childhood'" />

                <!-- Bidirectional Toggle -->
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        v-model="isBidirectional"
                        type="checkbox"
                        class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500"
                    />
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Bidirectional relation</span>
                </label>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">If checked, this relation will appear on both entries.</p>

                <!-- Error in modal -->
                <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                    {{ error }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="ghost" @click="closeModal">Cancel</Button>
                    <Button :loading="loading" :disabled="!selectedEntryId || !relationType || loading" @click="addRelation">Add Relation</Button>
                </div>
            </template>
        </Modal>
    </div>
</template>
