<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import Input from '@/components/ui/forms/Input.vue';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    description: string | null;
    ai_context_mode: string;
    aliases: string[];
    details: { key: string; value: string }[];
}

const props = defineProps<{
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insertEntry', entry: CodexEntry): void;
}>();

// State
const entries = ref<CodexEntry[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const searchQuery = ref('');
const selectedType = ref('');
const selectedEntry = ref<CodexEntry | null>(null);

// Type configuration
const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    location: { label: 'Location', icon: '📍', color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    item: { label: 'Item', icon: '⚔️', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    lore: { label: 'Lore', icon: '📜', color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    organization: { label: 'Organization', icon: '🏛️', color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
    subplot: { label: 'Subplot', icon: '📖', color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' },
};

const types = Object.keys(typeConfig);

const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';
const getTypeLabel = (type: string) => typeConfig[type]?.label || type;
const getTypeColor = (type: string) => typeConfig[type]?.color || 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

// Fetch entries
const fetchEntries = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/novels/${props.novelId}/codex/editor`);
        entries.value = response.data.entries;
    } catch {
        error.value = 'Failed to load codex entries';
    } finally {
        loading.value = false;
    }
};

// Filtered entries
const filteredEntries = computed(() => {
    let result = entries.value;

    if (selectedType.value) {
        result = result.filter((e) => e.type === selectedType.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (e) =>
                e.name.toLowerCase().includes(query) ||
                e.description?.toLowerCase().includes(query) ||
                e.aliases.some((a) => a.toLowerCase().includes(query)),
        );
    }

    return result;
});

// Group entries by type
const entriesByType = computed(() => {
    const grouped: Record<string, CodexEntry[]> = {};
    for (const entry of filteredEntries.value) {
        if (!grouped[entry.type]) {
            grouped[entry.type] = [];
        }
        grouped[entry.type].push(entry);
    }
    return grouped;
});

// Select an entry to view details
const selectEntry = (entry: CodexEntry) => {
    selectedEntry.value = entry;
};

// Go back to list view
const backToList = () => {
    selectedEntry.value = null;
};

// Insert entry name into editor
const insertName = () => {
    if (selectedEntry.value) {
        emit('insertEntry', selectedEntry.value);
    }
};

// Load entries on mount
onMounted(fetchEntries);

// Refresh when novelId changes
watch(() => props.novelId, fetchEntries);
</script>

<template>
    <div class="flex h-full flex-col bg-white dark:bg-zinc-900">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2">
                <button
                    v-if="selectedEntry"
                    type="button"
                    class="rounded p-1 text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    @click="backToList"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ selectedEntry ? selectedEntry.name : 'Codex' }}
                </h2>
            </div>
            <button
                type="button"
                class="rounded p-1 text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                @click="emit('close')"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Entry Detail View -->
        <template v-if="selectedEntry">
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Type Badge -->
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium" :class="getTypeColor(selectedEntry.type)">
                    {{ getTypeIcon(selectedEntry.type) }} {{ getTypeLabel(selectedEntry.type) }}
                </span>

                <!-- Description -->
                <div v-if="selectedEntry.description" class="mt-4">
                    <h3 class="mb-1 text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Description</h3>
                    <p class="whitespace-pre-wrap text-sm text-zinc-700 dark:text-zinc-300">
                        {{ selectedEntry.description }}
                    </p>
                </div>

                <!-- Aliases -->
                <div v-if="selectedEntry.aliases.length > 0" class="mt-4">
                    <h3 class="mb-1 text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Aliases</h3>
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="alias in selectedEntry.aliases"
                            :key="alias"
                            class="inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400"
                        >
                            {{ alias }}
                        </span>
                    </div>
                </div>

                <!-- Details -->
                <div v-if="selectedEntry.details.length > 0" class="mt-4">
                    <h3 class="mb-1 text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">Details</h3>
                    <dl class="space-y-1 text-sm">
                        <div v-for="detail in selectedEntry.details" :key="detail.key" class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ detail.key }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ detail.value }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- AI Context Mode -->
                <div class="mt-4">
                    <h3 class="mb-1 text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">AI Context</h3>
                    <span class="text-sm capitalize text-zinc-700 dark:text-zinc-300">
                        {{ selectedEntry.ai_context_mode.replace('_', ' ') }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex gap-2">
                    <Button variant="secondary" size="sm" class="flex-1" @click="insertName">
                        Insert Name
                    </Button>
                    <Button :href="`/codex/${selectedEntry.id}`" as="a" size="sm" variant="ghost">
                        View Full
                    </Button>
                </div>
            </div>
        </template>

        <!-- List View -->
        <template v-else>
            <!-- Search and Filter -->
            <div class="space-y-2 border-b border-zinc-200 p-3 dark:border-zinc-700">
                <Input v-model="searchQuery" placeholder="Search entries..." size="sm">
                    <template #prefix>
                        <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </template>
                </Input>

                <!-- Type Filter Pills -->
                <div class="flex flex-wrap gap-1">
                    <button
                        type="button"
                        :class="[
                            'rounded-full px-2 py-0.5 text-xs font-medium transition-colors',
                            selectedType === ''
                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700',
                        ]"
                        @click="selectedType = ''"
                    >
                        All
                    </button>
                    <button
                        v-for="type in types"
                        :key="type"
                        type="button"
                        :class="[
                            'rounded-full px-2 py-0.5 text-xs font-medium transition-colors',
                            selectedType === type
                                ? getTypeColor(type)
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700',
                        ]"
                        @click="selectedType = type"
                    >
                        {{ getTypeIcon(type) }}
                    </button>
                </div>
            </div>

            <!-- Entries List -->
            <div class="flex-1 overflow-y-auto p-2">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <div class="h-6 w-6 animate-spin rounded-full border-2 border-violet-500 border-t-transparent" />
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="p-4 text-center text-sm text-red-500 dark:text-red-400">
                    {{ error }}
                    <button type="button" class="ml-2 text-violet-600 hover:underline dark:text-violet-400" @click="fetchEntries">
                        Retry
                    </button>
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredEntries.length === 0" class="p-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ searchQuery || selectedType ? 'No matching entries found' : 'No codex entries yet' }}
                </div>

                <!-- Grouped Entries -->
                <div v-else class="space-y-4">
                    <div v-for="(typeEntries, type) in entriesByType" :key="type">
                        <h3 class="mb-1 flex items-center gap-1 px-1 text-xs font-medium text-zinc-500 uppercase dark:text-zinc-400">
                            <span>{{ getTypeIcon(type) }}</span>
                            <span>{{ getTypeLabel(type) }}</span>
                            <span class="text-zinc-400 dark:text-zinc-500">({{ typeEntries.length }})</span>
                        </h3>
                        <div class="space-y-1">
                            <button
                                v-for="entry in typeEntries"
                                :key="entry.id"
                                type="button"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="selectEntry(entry)"
                            >
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded text-sm" :class="getTypeColor(entry.type)">
                                    {{ getTypeIcon(entry.type) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ entry.name }}
                                    </p>
                                    <p v-if="entry.aliases.length > 0" class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ entry.aliases.slice(0, 2).join(', ') }}
                                        {{ entry.aliases.length > 2 ? '...' : '' }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-zinc-200 p-2 dark:border-zinc-700">
                <Button :href="`/novels/${novelId}/codex/create`" as="a" variant="ghost" size="sm" class="w-full justify-center">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Entry
                </Button>
            </div>
        </template>
    </div>
</template>
