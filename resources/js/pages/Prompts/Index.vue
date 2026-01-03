<script setup lang="ts">
import PromptCard from '@/components/prompts/PromptCard.vue';
import PromptEditor from '@/components/prompts/PromptEditor.vue';
import PromptImportModal from '@/components/prompts/PromptImportModal.vue';
import Button from '@/components/ui/buttons/Button.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import type { Prompt, PromptFormData } from '@/composables/usePrompts';
import { usePrompts } from '@/composables/usePrompts';
import { usePromptSharing } from '@/composables/usePromptSharing';
import { useToast } from '@/composables/useToast';
import { Head, Link } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { Motion } from 'motion-v';
import { ref, computed } from 'vue';

interface Props {
    prompts: Prompt[];
    types: Record<string, string>;
    statistics: {
        user_prompts: number;
        system_prompts: number;
        total: number;
        by_type: Record<string, number>;
        total_usage: number;
    };
    filters: {
        type: string | null;
        search: string | null;
    };
}

const props = defineProps<Props>();

const { createPrompt, updatePrompt, deletePrompt, clonePrompt } = usePrompts();
const { exportToClipboard } = usePromptSharing();
const { showToast } = useToast();

// Local state
const localPrompts = ref<Prompt[]>(props.prompts);
const selectedPrompt = ref<Prompt | null>(null);
const isCreating = ref(false);
const searchQuery = ref(props.filters.search || '');
const selectedType = ref<string | null>(props.filters.type || null);
const isLoading = ref(false);

// New dropdown and import modal state
const showNewDropdown = ref(false);
const newDropdownRef = ref<HTMLElement | null>(null);
const showImportModal = ref(false);

onClickOutside(newDropdownRef, () => {
    showNewDropdown.value = false;
});

// Type options for filter
const typeOptions = computed(() => {
    return [
        { value: null, label: 'All Types' },
        ...Object.entries(props.types).map(([value, label]) => ({ value, label })),
    ];
});

// Filtered prompts
const filteredPrompts = computed(() => {
    let result = localPrompts.value;

    if (selectedType.value) {
        result = result.filter((p) => p.type === selectedType.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (p) => p.name.toLowerCase().includes(query) || p.description?.toLowerCase().includes(query),
        );
    }

    return result;
});

// Group prompts by type
const groupedPrompts = computed(() => {
    const groups: Record<string, { system: Prompt[]; user: Prompt[] }> = {};

    for (const [type] of Object.entries(props.types)) {
        groups[type] = { system: [], user: [] };
    }

    for (const prompt of filteredPrompts.value) {
        if (groups[prompt.type]) {
            if (prompt.is_system) {
                groups[prompt.type].system.push(prompt);
            } else {
                groups[prompt.type].user.push(prompt);
            }
        }
    }

    return groups;
});

// Select a prompt for editing/viewing
function selectPrompt(prompt: Prompt) {
    selectedPrompt.value = prompt;
    isCreating.value = false;
}

// Start creating a new prompt
function startCreating() {
    selectedPrompt.value = null;
    isCreating.value = true;
}

// Handle prompt created
async function handlePromptCreated(data: PromptFormData) {
    isLoading.value = true;
    const newPrompt = await createPrompt(data);
    isLoading.value = false;

    if (newPrompt) {
        localPrompts.value.push(newPrompt);
        selectedPrompt.value = newPrompt;
        isCreating.value = false;
        showToast({ message: 'Prompt created successfully', type: 'success' });
    }
}

// Handle prompt updated
async function handlePromptUpdated(id: number, data: Partial<PromptFormData>) {
    isLoading.value = true;
    const updated = await updatePrompt(id, data);
    isLoading.value = false;

    if (updated) {
        const index = localPrompts.value.findIndex((p) => p.id === id);
        if (index !== -1) {
            localPrompts.value[index] = updated;
            selectedPrompt.value = updated;
        }
        showToast({ message: 'Prompt updated successfully', type: 'success' });
    }
}

// Handle prompt deleted
async function handlePromptDeleted(id: number) {
    isLoading.value = true;
    const success = await deletePrompt(id);
    isLoading.value = false;

    if (success) {
        localPrompts.value = localPrompts.value.filter((p) => p.id !== id);
        selectedPrompt.value = null;
        showToast({ message: 'Prompt deleted successfully', type: 'success' });
    }
}

// Handle prompt cloned
async function handlePromptCloned(id: number) {
    isLoading.value = true;
    const cloned = await clonePrompt(id);
    isLoading.value = false;

    if (cloned) {
        localPrompts.value.push(cloned);
        selectedPrompt.value = cloned;
        showToast({ message: 'Prompt cloned successfully', type: 'success' });
    }
}

// Handle prompt exported
async function handlePromptExported(id: number) {
    const success = await exportToClipboard(id);
    if (success) {
        showToast({ message: 'Prompt copied to clipboard', type: 'success' });
    } else {
        showToast({ message: 'Failed to export prompt', type: 'error' });
    }
}

// Handle prompt imported
function handlePromptImported(prompt: Prompt) {
    localPrompts.value.push(prompt);
    selectedPrompt.value = prompt;
    showToast({ message: 'Prompt imported successfully', type: 'success' });
}

// Open import modal
function openImportModal() {
    showNewDropdown.value = false;
    showImportModal.value = true;
}

// Cancel creating/editing
function handleCancel() {
    isCreating.value = false;
    selectedPrompt.value = null;
}

// Get type icon
function getTypeIcon(type: string) {
    switch (type) {
        case 'chat':
            return 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
        case 'prose':
            return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
        case 'replacement':
            return 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15';
        case 'summary':
            return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01';
        default:
            return 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
    }
}
</script>

<template>
    <AuthenticatedLayout title="Prompt Library">
        <Head title="Prompt Library" />

        <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
            <!-- Sidebar - Prompt List -->
            <Motion
                :initial="{ opacity: 0, x: -20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="flex w-80 shrink-0 flex-col border-r border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
            >
                <!-- Header -->
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <Link
                        href="/settings"
                        class="mb-3 inline-flex items-center gap-1 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Settings
                    </Link>
                    <div class="flex items-center justify-between">
                        <h1 class="text-lg font-semibold text-zinc-900 dark:text-white">Prompt Library</h1>
                        <div ref="newDropdownRef" class="relative">
                            <Button size="sm" @click="showNewDropdown = !showNewDropdown">
                                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New
                                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </Button>
                            
                            <!-- New Dropdown -->
                            <div
                                v-if="showNewDropdown"
                                class="absolute right-0 top-full z-20 mt-1 w-48 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                    @click="showNewDropdown = false; startCreating()"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create New Prompt
                                </button>
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                    @click="openImportModal"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Import from Clipboard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="space-y-3 border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="relative">
                        <svg
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search prompts..."
                            class="w-full rounded-lg border border-zinc-300 bg-white py-2 pl-10 pr-4 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        />
                    </div>
                    <select
                        v-model="selectedType"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option v-for="option in typeOptions" :key="String(option.value)" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <!-- Prompts List -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div v-for="(typeLabel, type) in types" :key="type" class="mb-6">
                        <template v-if="!selectedType || selectedType === type">
                            <div
                                v-if="groupedPrompts[type]?.system.length || groupedPrompts[type]?.user.length"
                                class="mb-2"
                            >
                                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(type)" />
                                    </svg>
                                    {{ typeLabel }}
                                </div>
                            </div>

                            <!-- System Prompts -->
                            <div v-if="groupedPrompts[type]?.system.length" class="mb-2 space-y-1">
                                <PromptCard
                                    v-for="prompt in groupedPrompts[type].system"
                                    :key="prompt.id"
                                    :prompt="prompt"
                                    :selected="selectedPrompt?.id === prompt.id"
                                    @select="selectPrompt(prompt)"
                                    @clone="handlePromptCloned(prompt.id)"
                                    @export="handlePromptExported(prompt.id)"
                                />
                            </div>

                            <!-- User Prompts -->
                            <div v-if="groupedPrompts[type]?.user.length" class="space-y-1">
                                <PromptCard
                                    v-for="prompt in groupedPrompts[type].user"
                                    :key="prompt.id"
                                    :prompt="prompt"
                                    :selected="selectedPrompt?.id === prompt.id"
                                    @select="selectPrompt(prompt)"
                                    @clone="handlePromptCloned(prompt.id)"
                                    @delete="handlePromptDeleted(prompt.id)"
                                    @export="handlePromptExported(prompt.id)"
                                />
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="filteredPrompts.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center"
                    >
                        <svg class="mb-3 h-12 w-12 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No prompts found</p>
                        <Button size="sm" variant="ghost" class="mt-2" @click="startCreating()">
                            Create your first prompt
                        </Button>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ statistics.total }}</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Total</div>
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ statistics.user_prompts }}</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Custom</div>
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ statistics.total_usage }}</div>
                            <div class="text-zinc-500 dark:text-zinc-400">Uses</div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Main Content - Editor -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                class="flex-1 overflow-y-auto bg-zinc-50 dark:bg-zinc-950"
            >
                <!-- Editor/Create Form -->
                <PromptEditor
                    v-if="isCreating || selectedPrompt"
                    :prompt="selectedPrompt"
                    :types="types"
                    :is-creating="isCreating"
                    :is-loading="isLoading"
                    @save="isCreating ? handlePromptCreated($event) : handlePromptUpdated(selectedPrompt!.id, $event)"
                    @cancel="handleCancel"
                    @delete="handlePromptDeleted(selectedPrompt!.id)"
                    @clone="handlePromptCloned(selectedPrompt!.id)"
                />

                <!-- Empty State - No Selection -->
                <div
                    v-else
                    class="flex h-full flex-col items-center justify-center text-center"
                >
                    <div class="mb-4 rounded-full bg-zinc-100 p-4 dark:bg-zinc-800">
                        <svg class="h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h2 class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white">Welcome to the Prompt Library</h2>
                    <p class="mb-6 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                        Select a prompt from the sidebar to view or edit it, or create a new custom prompt to enhance your AI writing experience.
                    </p>
                    <Button @click="startCreating()">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Prompt
                    </Button>
                </div>
            </Motion>
        </div>

        <!-- Import Modal -->
        <PromptImportModal
            :is-open="showImportModal"
            @close="showImportModal = false"
            @imported="handlePromptImported"
        />
    </AuthenticatedLayout>
</template>
