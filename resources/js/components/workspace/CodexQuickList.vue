<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Input from '@/components/ui/forms/Input.vue';

interface CodexEntry {
    id: number;
    type: string;
    name: string;
    aliases: string[];
}

const props = defineProps<{
    novelId: number;
}>();

const emit = defineEmits<{
    (e: 'select', entryId: number): void;
    (e: 'create'): void;
}>();

const entries = ref<CodexEntry[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const searchQuery = ref('');
const selectedType = ref('');

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
    character: { label: 'Character', icon: '👤', color: 'text-purple-600 dark:text-purple-400' },
    location: { label: 'Location', icon: '📍', color: 'text-blue-600 dark:text-blue-400' },
    item: { label: 'Item', icon: '⚔️', color: 'text-amber-600 dark:text-amber-400' },
    lore: { label: 'Lore', icon: '📜', color: 'text-emerald-600 dark:text-emerald-400' },
    organization: { label: 'Organization', icon: '🏛️', color: 'text-rose-600 dark:text-rose-400' },
    subplot: { label: 'Subplot', icon: '📖', color: 'text-cyan-600 dark:text-cyan-400' },
};

const types = Object.keys(typeConfig);
const getTypeIcon = (type: string) => typeConfig[type]?.icon || '📄';

const fetchEntries = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/api/novels/${props.novelId}/codex/editor`);
        entries.value = response.data.entries;
    } catch {
        error.value = 'Failed to load entries';
    } finally {
        loading.value = false;
    }
};

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
                e.aliases.some((a) => a.toLowerCase().includes(query))
        );
    }

    return result.slice(0, 20); // Limit for performance
});

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

onMounted(fetchEntries);
watch(() => props.novelId, fetchEntries);

// Expose refresh method for parent to call
defineExpose({ refresh: fetchEntries });
</script>

<template>
    <div class="space-y-2">
        <!-- Search -->
        <Input
            v-model="searchQuery"
            placeholder="Search..."
            size="sm"
            class="text-xs"
        >
            <template #prefix>
                <svg class="h-3 w-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </template>
        </Input>

        <!-- Type Filter -->
        <div class="flex flex-wrap gap-1">
            <button
                type="button"
                :class="[
                    'rounded-full px-1.5 py-0.5 text-xs transition-colors',
                    selectedType === ''
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                        : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400',
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
                    'rounded-full px-1.5 py-0.5 text-xs transition-colors',
                    selectedType === type
                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                        : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-400',
                ]"
                :title="typeConfig[type]?.label"
                @click="selectedType = type"
            >
                {{ getTypeIcon(type) }}
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-4">
            <div class="h-4 w-4 animate-spin rounded-full border-2 border-violet-500 border-t-transparent" />
        </div>

        <!-- Error -->
        <div v-else-if="error" class="py-2 text-center text-xs text-red-500">
            {{ error }}
            <button class="ml-1 text-violet-600 hover:underline" @click="fetchEntries">Retry</button>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredEntries.length === 0" class="py-2 text-center text-xs text-zinc-500 dark:text-zinc-400">
            {{ searchQuery || selectedType ? 'No matches' : 'No entries yet' }}
        </div>

        <!-- Entries List -->
        <div v-else class="max-h-48 space-y-2 overflow-y-auto">
            <div v-for="(typeEntries, type) in entriesByType" :key="type">
                <div class="mb-1 flex items-center gap-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    <span>{{ getTypeIcon(type as string) }}</span>
                    <span>{{ typeConfig[type as string]?.label }}</span>
                </div>
                <div class="space-y-0.5">
                    <button
                        v-for="entry in typeEntries"
                        :key="entry.id"
                        type="button"
                        class="flex w-full items-center gap-2 rounded px-2 py-1 text-left text-xs transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        @click="emit('select', entry.id)"
                    >
                        <span class="truncate font-medium text-zinc-700 dark:text-zinc-300">
                            {{ entry.name }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Button -->
        <button
            type="button"
            class="flex w-full items-center justify-center gap-1 rounded-md border border-dashed border-zinc-300 px-2 py-1.5 text-xs text-zinc-500 transition-colors hover:border-violet-400 hover:text-violet-600 dark:border-zinc-600 dark:text-zinc-400 dark:hover:border-violet-500 dark:hover:text-violet-400"
            @click="emit('create')"
        >
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            New Entry
        </button>
    </div>
</template>
