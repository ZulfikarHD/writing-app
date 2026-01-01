<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import axios from 'axios';
import { ref } from 'vue';

interface Detail {
    id: number;
    key_name: string;
    value: string;
    sort_order: number;
}

const props = defineProps<{
    entryId: number;
    entryType: string;
    details: Detail[];
}>();

const emit = defineEmits<{
    (e: 'updated', details: Detail[]): void;
}>();

const localDetails = ref<Detail[]>([...props.details]);
const isAdding = ref(false);
const newKeyName = ref('');
const newValue = ref('');
const editingId = ref<number | null>(null);
const editingKeyName = ref('');
const editingValue = ref('');
const error = ref<string | null>(null);
const loading = ref(false);

// Suggested detail keys by type
const suggestedKeys: Record<string, string[]> = {
    character: ['Age', 'Gender', 'Hair Color', 'Eye Color', 'Height', 'Occupation', 'Personality', 'Motivation', 'Background'],
    location: ['Type', 'Climate', 'Population', 'Notable Features', 'History', 'Atmosphere'],
    item: ['Type', 'Origin', 'Powers', 'Material', 'Value', 'Current Owner'],
    lore: ['Era', 'Source', 'Significance', 'Related Events'],
    organization: ['Type', 'Founded', 'Leader', 'Members', 'Goals', 'Headquarters'],
    subplot: ['Status', 'Theme', 'Resolution', 'Key Characters'],
};

const suggestions = suggestedKeys[props.entryType] || [];

const addDetail = async () => {
    if (!newKeyName.value.trim() || !newValue.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/codex/${props.entryId}/details`, {
            key_name: newKeyName.value.trim(),
            value: newValue.value.trim(),
        });

        localDetails.value.push(response.data.detail);
        newKeyName.value = '';
        newValue.value = '';
        isAdding.value = false;
        emit('updated', localDetails.value);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add detail';
    } finally {
        loading.value = false;
    }
};

const startEdit = (detail: Detail) => {
    editingId.value = detail.id;
    editingKeyName.value = detail.key_name;
    editingValue.value = detail.value;
};

const cancelEdit = () => {
    editingId.value = null;
    editingKeyName.value = '';
    editingValue.value = '';
};

const saveEdit = async () => {
    if (!editingKeyName.value.trim() || !editingValue.value.trim() || !editingId.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.patch(`/api/codex/details/${editingId.value}`, {
            key_name: editingKeyName.value.trim(),
            value: editingValue.value.trim(),
        });

        const index = localDetails.value.findIndex((d) => d.id === editingId.value);
        if (index !== -1) {
            localDetails.value[index] = response.data.detail;
        }
        editingId.value = null;
        editingKeyName.value = '';
        editingValue.value = '';
        emit('updated', localDetails.value);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to update detail';
    } finally {
        loading.value = false;
    }
};

const deleteDetail = async (id: number) => {
    loading.value = true;
    error.value = null;

    try {
        await axios.delete(`/api/codex/details/${id}`);
        localDetails.value = localDetails.value.filter((d) => d.id !== id);
        emit('updated', localDetails.value);
    } catch {
        error.value = 'Failed to delete detail';
    } finally {
        loading.value = false;
    }
};

const selectSuggestion = (key: string) => {
    newKeyName.value = key;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        if (editingId.value) {
            cancelEdit();
        } else {
            isAdding.value = false;
            newKeyName.value = '';
            newValue.value = '';
        }
    }
};
</script>

<template>
    <div>
        <!-- Error Message -->
        <div v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
            {{ error }}
            <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="error = null">×</button>
        </div>

        <!-- Details Table -->
        <div v-if="localDetails.length > 0" class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    <tr v-for="detail in localDetails" :key="detail.id">
                        <!-- Edit Mode -->
                        <template v-if="editingId === detail.id">
                            <td class="px-3 py-2">
                                <Input v-model="editingKeyName" size="sm" placeholder="Key" @keydown="handleKeydown" />
                            </td>
                            <td class="px-3 py-2">
                                <Input v-model="editingValue" size="sm" placeholder="Value" @keydown="handleKeydown" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">
                                <button
                                    type="button"
                                    class="rounded p-1 text-green-600 transition-colors hover:bg-green-100 dark:text-green-400 dark:hover:bg-green-900/30"
                                    :disabled="loading"
                                    @click="saveEdit"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                    @click="cancelEdit"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </template>

                        <!-- View Mode -->
                        <template v-else>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ detail.key_name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ detail.value }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                                    @click="startEdit(detail)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-400 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                    @click="deleteDetail(detail.id)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add New Detail -->
        <div v-if="isAdding" class="mt-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attribute Name</label>
                    <Input v-model="newKeyName" placeholder="e.g., Age, Location, Type..." size="sm" @keydown="handleKeydown" />
                    <!-- Suggestions -->
                    <div v-if="suggestions.length > 0 && !newKeyName" class="mt-2 flex flex-wrap gap-1">
                        <button
                            v-for="suggestion in suggestions"
                            :key="suggestion"
                            type="button"
                            class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 transition-colors hover:bg-violet-100 hover:text-violet-700 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-violet-900/30 dark:hover:text-violet-300"
                            @click="selectSuggestion(suggestion)"
                        >
                            {{ suggestion }}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Value</label>
                    <Input v-model="newValue" placeholder="Enter value..." size="sm" @keydown="handleKeydown" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button size="sm" variant="ghost" @click="isAdding = false; newKeyName = ''; newValue = ''"> Cancel </Button>
                    <Button size="sm" :loading="loading" :disabled="!newKeyName.trim() || !newValue.trim() || loading" @click="addDetail"> Add Detail </Button>
                </div>
            </div>
        </div>

        <Button v-else size="sm" variant="ghost" class="mt-3" @click="isAdding = true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Detail
        </Button>

        <!-- Empty State -->
        <p v-if="localDetails.length === 0 && !isAdding" class="mt-2 text-sm text-zinc-400 italic dark:text-zinc-500">
            No details added yet. Add attributes like age, occupation, or other relevant information.
        </p>
    </div>
</template>
