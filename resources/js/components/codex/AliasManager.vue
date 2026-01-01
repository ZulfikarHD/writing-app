<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import axios from 'axios';
import { ref } from 'vue';

interface Alias {
    id: number;
    alias: string;
}

const props = defineProps<{
    entryId: number;
    aliases: Alias[];
}>();

const emit = defineEmits<{
    (e: 'updated', aliases: Alias[]): void;
}>();

const localAliases = ref<Alias[]>([...props.aliases]);
const newAlias = ref('');
const isAdding = ref(false);
const editingId = ref<number | null>(null);
const editingValue = ref('');
const error = ref<string | null>(null);
const loading = ref(false);

const addAlias = async () => {
    if (!newAlias.value.trim()) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/codex/${props.entryId}/aliases`, {
            alias: newAlias.value.trim(),
        });

        localAliases.value.push(response.data.alias);
        newAlias.value = '';
        isAdding.value = false;
        emit('updated', localAliases.value);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to add alias';
    } finally {
        loading.value = false;
    }
};

const startEdit = (alias: Alias) => {
    editingId.value = alias.id;
    editingValue.value = alias.alias;
};

const cancelEdit = () => {
    editingId.value = null;
    editingValue.value = '';
};

const saveEdit = async () => {
    if (!editingValue.value.trim() || !editingId.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.patch(`/api/codex/aliases/${editingId.value}`, {
            alias: editingValue.value.trim(),
        });

        const index = localAliases.value.findIndex((a) => a.id === editingId.value);
        if (index !== -1) {
            localAliases.value[index] = response.data.alias;
        }
        editingId.value = null;
        editingValue.value = '';
        emit('updated', localAliases.value);
    } catch (err: unknown) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Failed to update alias';
    } finally {
        loading.value = false;
    }
};

const deleteAlias = async (id: number) => {
    loading.value = true;
    error.value = null;

    try {
        await axios.delete(`/api/codex/aliases/${id}`);
        localAliases.value = localAliases.value.filter((a) => a.id !== id);
        emit('updated', localAliases.value);
    } catch {
        error.value = 'Failed to delete alias';
    } finally {
        loading.value = false;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (editingId.value) {
            saveEdit();
        } else {
            addAlias();
        }
    } else if (e.key === 'Escape') {
        if (editingId.value) {
            cancelEdit();
        } else {
            isAdding.value = false;
            newAlias.value = '';
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

        <!-- Aliases List -->
        <div class="space-y-2">
            <div
                v-for="alias in localAliases"
                :key="alias.id"
                class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700"
            >
                <!-- Edit Mode -->
                <template v-if="editingId === alias.id">
                    <Input v-model="editingValue" class="flex-1" size="sm" autofocus @keydown="handleKeydown" />
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
                </template>

                <!-- View Mode -->
                <template v-else>
                    <span class="flex-1 text-sm text-zinc-900 dark:text-white">{{ alias.alias }}</span>
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                        @click="startEdit(alias)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                        @click="deleteAlias(alias.id)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- Add New Alias -->
        <div v-if="isAdding" class="mt-3 flex items-center gap-2">
            <Input v-model="newAlias" placeholder="Enter alias..." class="flex-1" size="sm" autofocus @keydown="handleKeydown" />
            <Button size="sm" :loading="loading" :disabled="!newAlias.trim() || loading" @click="addAlias"> Add </Button>
            <Button size="sm" variant="ghost" @click="isAdding = false; newAlias = ''"> Cancel </Button>
        </div>

        <Button v-else size="sm" variant="ghost" class="mt-3" @click="isAdding = true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Alias
        </Button>

        <!-- Empty State -->
        <p v-if="localAliases.length === 0 && !isAdding" class="mt-2 text-sm text-zinc-400 italic dark:text-zinc-500">
            No aliases added yet. Aliases help AI recognize alternative names.
        </p>
    </div>
</template>
