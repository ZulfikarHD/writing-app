<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Card from '@/components/ui/layout/Card.vue';
import Input from '@/components/ui/forms/Input.vue';
import Toast from '@/components/ui/feedback/Toast.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface Alias {
    id: number;
    alias: string;
}

interface Detail {
    id: number;
    label: string;
    value: string;
    sort_order: number;
}

interface EntryData {
    id: number;
    type: string;
    name: string;
    description: string | null;
    thumbnail_path: string | null;
    ai_context_mode: string;
    is_archived: boolean;
    created_at: string;
    updated_at: string;
    aliases: Alias[];
    details: Detail[];
}

const props = defineProps<{
    series: { id: number; title: string };
    entry: EntryData;
    types: string[];
}>();

const toast = ref({
    show: false,
    variant: 'success' as 'info' | 'success' | 'warning' | 'danger',
    title: '',
    message: '',
});

const showToast = (variant: 'info' | 'success' | 'warning' | 'danger', title: string, message: string) => {
    toast.value = { show: true, variant, title, message };
};

// Edit mode
const editing = ref(false);
const editForm = ref({
    name: props.entry.name,
    description: props.entry.description || '',
    type: props.entry.type,
});

const saveEdit = async () => {
    try {
        await axios.patch(`/api/series-codex/${props.entry.id}`, editForm.value);
        router.reload();
        editing.value = false;
        showToast('success', 'Saved', 'Entry updated successfully.');
    } catch {
        showToast('danger', 'Error', 'Failed to save changes.');
    }
};

// Alias management
const newAlias = ref('');
const addingAlias = ref(false);

const addAlias = async () => {
    if (!newAlias.value.trim()) return;
    addingAlias.value = true;
    try {
        await axios.post(`/api/series-codex/${props.entry.id}/aliases`, {
            alias: newAlias.value.trim(),
        });
        router.reload();
        newAlias.value = '';
        showToast('success', 'Added', 'Alias added successfully.');
    } catch {
        showToast('danger', 'Error', 'Failed to add alias.');
    } finally {
        addingAlias.value = false;
    }
};

const deleteAlias = async (aliasId: number) => {
    try {
        await axios.delete(`/api/series-codex/${props.entry.id}/aliases/${aliasId}`);
        router.reload();
        showToast('success', 'Deleted', 'Alias removed.');
    } catch {
        showToast('danger', 'Error', 'Failed to remove alias.');
    }
};

// Detail management
const showAddDetailModal = ref(false);
const newDetail = ref({ label: '', value: '' });

const addDetail = async () => {
    if (!newDetail.value.label.trim() || !newDetail.value.value.trim()) return;
    try {
        await axios.post(`/api/series-codex/${props.entry.id}/details`, {
            label: newDetail.value.label.trim(),
            value: newDetail.value.value.trim(),
        });
        router.reload();
        showAddDetailModal.value = false;
        newDetail.value = { label: '', value: '' };
        showToast('success', 'Added', 'Detail added successfully.');
    } catch {
        showToast('danger', 'Error', 'Failed to add detail.');
    }
};

const deleteDetail = async (detailId: number) => {
    try {
        await axios.delete(`/api/series-codex/${props.entry.id}/details/${detailId}`);
        router.reload();
        showToast('success', 'Deleted', 'Detail removed.');
    } catch {
        showToast('danger', 'Error', 'Failed to remove detail.');
    }
};

// Delete entry
const deleting = ref(false);

const deleteEntry = async () => {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    deleting.value = true;
    try {
        await axios.delete(`/api/series-codex/${props.entry.id}`);
        router.visit(`/series/${props.series.id}/codex`);
    } catch {
        showToast('danger', 'Error', 'Failed to delete entry.');
        deleting.value = false;
    }
};

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    location: { label: 'Location', icon: '📍', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    item: { label: 'Item', icon: '⚔️', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    lore: { label: 'Lore', icon: '📜', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    organization: { label: 'Organization', icon: '🏛️', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
    subplot: { label: 'Subplot', icon: '📖', color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
};

const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <Head :title="`${entry.name} - Series Codex`" />

        <!-- Header -->
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="`/series/${series.id}/codex`"
                            class="flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to Codex
                        </Link>
                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-700" />
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ series.title }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button v-if="!editing" variant="ghost" size="sm" @click="editing = true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit
                        </Button>
                        <Button variant="ghost" size="sm" class="text-red-600 hover:text-red-700 dark:text-red-400" :disabled="deleting" @click="deleteEntry">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            Delete
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Entry Header -->
                    <Card>
                        <div v-if="!editing" class="flex items-start gap-4">
                            <div :class="['flex h-16 w-16 shrink-0 items-center justify-center rounded-lg text-3xl', getTypeColor(entry.type)]">
                                {{ getTypeIcon(entry.type) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ entry.name }}</h1>
                                        <span :class="['mt-1 inline-flex rounded-full px-2 py-1 text-xs font-medium', getTypeColor(entry.type)]">
                                            {{ getTypeLabel(entry.type) }}
                                        </span>
                                    </div>
                                </div>
                                <p v-if="entry.description" class="mt-3 whitespace-pre-wrap text-zinc-600 dark:text-zinc-300">
                                    {{ entry.description }}
                                </p>
                                <p v-else class="mt-3 italic text-zinc-400 dark:text-zinc-500">No description</p>
                            </div>
                        </div>

                        <!-- Edit Mode -->
                        <form v-else @submit.prevent="saveEdit" class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                                <select
                                    v-model="editForm.type"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                >
                                    <option v-for="type in types" :key="type" :value="type">
                                        {{ getTypeIcon(type) }} {{ getTypeLabel(type) }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                                <Input v-model="editForm.name" required />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                <textarea
                                    v-model="editForm.description"
                                    rows="4"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm transition-colors focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                />
                            </div>

                            <div class="flex justify-end gap-2">
                                <Button type="button" variant="ghost" @click="editing = false">Cancel</Button>
                                <Button type="submit">Save Changes</Button>
                            </div>
                        </form>
                    </Card>

                    <!-- Details -->
                    <Card>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Details</h2>
                            <Button size="sm" variant="ghost" @click="showAddDetailModal = true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Add
                            </Button>
                        </div>

                        <div v-if="entry.details.length > 0" class="space-y-3">
                            <div
                                v-for="detail in entry.details"
                                :key="detail.id"
                                class="group flex items-start justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                            >
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ detail.label }}</dt>
                                    <dd class="mt-1 text-zinc-900 dark:text-white">{{ detail.value }}</dd>
                                </div>
                                <button
                                    type="button"
                                    class="rounded p-1 text-zinc-400 opacity-0 transition-all hover:bg-zinc-100 hover:text-red-600 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-red-400"
                                    @click="deleteDetail(detail.id)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p v-else class="py-4 text-center text-sm text-zinc-400 dark:text-zinc-500">No details added yet</p>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Aliases -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Aliases</h2>

                        <div v-if="entry.aliases.length > 0" class="mb-4 flex flex-wrap gap-2">
                            <span
                                v-for="alias in entry.aliases"
                                :key="alias.id"
                                class="group inline-flex items-center gap-1 rounded-full bg-zinc-100 px-3 py-1 text-sm text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                            >
                                {{ alias.alias }}
                                <button
                                    type="button"
                                    class="ml-1 rounded-full p-0.5 text-zinc-400 opacity-0 transition-opacity hover:text-red-600 group-hover:opacity-100 dark:hover:text-red-400"
                                    @click="deleteAlias(alias.id)"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </div>

                        <form @submit.prevent="addAlias" class="flex gap-2">
                            <Input v-model="newAlias" placeholder="Add alias..." class="flex-1" />
                            <Button type="submit" size="sm" :disabled="!newAlias.trim() || addingAlias">
                                {{ addingAlias ? '...' : 'Add' }}
                            </Button>
                        </form>
                    </Card>

                    <!-- Info -->
                    <Card>
                        <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Information</h2>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">AI Context</dt>
                                <dd class="font-medium capitalize text-zinc-900 dark:text-white">{{ entry.ai_context_mode }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">
                                    {{ new Date(entry.created_at).toLocaleDateString() }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">
                                    {{ new Date(entry.updated_at).toLocaleDateString() }}
                                </dd>
                            </div>
                        </dl>
                    </Card>

                    <!-- Series Note -->
                    <div class="rounded-lg border border-violet-200 bg-violet-50 p-4 dark:border-violet-800 dark:bg-violet-900/20">
                        <p class="text-sm text-violet-700 dark:text-violet-300">
                            This entry is shared across all novels in the <strong>{{ series.title }}</strong> series.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Add Detail Modal -->
        <div v-if="showAddDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card class="mx-4 w-full max-w-md">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Add Detail</h3>

                <form @submit.prevent="addDetail" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Label</label>
                        <Input v-model="newDetail.label" placeholder="e.g., Height, Age, Occupation" required />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Value</label>
                        <Input v-model="newDetail.value" placeholder="Enter value" required />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="ghost" @click="showAddDetailModal = false">Cancel</Button>
                        <Button type="submit" :disabled="!newDetail.label.trim() || !newDetail.value.trim()">Add Detail</Button>
                    </div>
                </form>
            </Card>
        </div>

        <!-- Toast -->
        <Toast v-if="toast.show" :variant="toast.variant" :title="toast.title" :duration="5000" position="top-right" @close="toast.show = false">
            {{ toast.message }}
        </Toast>
    </div>
</template>
