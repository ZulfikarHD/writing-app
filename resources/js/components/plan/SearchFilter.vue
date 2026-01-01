<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface Label {
    id: number;
    name: string;
    color: string;
    position: number;
}

interface FilterState {
    query: string;
    status: string | null;
    labelIds: number[];
}

const props = defineProps<{
    labels: Label[];
    modelValue: FilterState;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: FilterState): void;
    (e: 'search'): void;
}>();

const showFilters = ref(false);
const localQuery = ref(props.modelValue.query);
const localStatus = ref(props.modelValue.status);
const localLabelIds = ref<number[]>([...props.modelValue.labelIds]);

const statusOptions = [
    { value: null, label: 'All Statuses' },
    { value: 'draft', label: 'Draft' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'needs_revision', label: 'Needs Revision' },
];

const hasActiveFilters = computed(() => {
    return localStatus.value !== null || localLabelIds.value.length > 0;
});

const activeFilterCount = computed(() => {
    let count = 0;
    if (localStatus.value) count++;
    count += localLabelIds.value.length;
    return count;
});

const updateFilters = () => {
    emit('update:modelValue', {
        query: localQuery.value,
        status: localStatus.value,
        labelIds: localLabelIds.value,
    });
    emit('search');
};

const toggleLabel = (labelId: number) => {
    const index = localLabelIds.value.indexOf(labelId);
    if (index > -1) {
        localLabelIds.value.splice(index, 1);
    } else {
        localLabelIds.value.push(labelId);
    }
    updateFilters();
};

const clearFilters = () => {
    localStatus.value = null;
    localLabelIds.value = [];
    updateFilters();
};

// Debounce search input
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(localQuery, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 300);
});

watch(localStatus, updateFilters);
</script>

<template>
    <div class="space-y-3">
        <!-- Search Bar -->
        <div class="flex gap-2">
            <div class="relative flex-1">
                <svg
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="localQuery"
                    type="text"
                    placeholder="Search scenes..."
                    class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                />
            </div>

            <button
                type="button"
                :class="[
                    'flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-all active:scale-95',
                    showFilters || hasActiveFilters
                        ? 'border-violet-500 bg-violet-50 text-violet-700 dark:border-violet-400 dark:bg-violet-900/30 dark:text-violet-300'
                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                ]"
                @click="showFilters = !showFilters"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
                <span v-if="activeFilterCount > 0" class="rounded-full bg-violet-600 px-1.5 text-xs text-white">
                    {{ activeFilterCount }}
                </span>
            </button>
        </div>

        <!-- Filter Panel -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="showFilters" class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-zinc-900 dark:text-white">Filters</h4>
                    <button v-if="hasActiveFilters" type="button" class="text-xs text-violet-600 hover:underline dark:text-violet-400" @click="clearFilters">
                        Clear all
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="option in statusOptions"
                                :key="option.value ?? 'all'"
                                type="button"
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-medium transition-all active:scale-95',
                                    localStatus === option.value
                                        ? 'bg-violet-600 text-white'
                                        : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600',
                                ]"
                                @click="localStatus = option.value"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Labels Filter -->
                    <div v-if="labels.length > 0">
                        <label class="mb-2 block text-xs font-medium text-zinc-500 dark:text-zinc-400">Labels</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="label in labels"
                                :key="label.id"
                                type="button"
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-medium transition-all active:scale-95',
                                    localLabelIds.includes(label.id) ? 'ring-2 ring-offset-1' : 'opacity-70 hover:opacity-100',
                                ]"
                                :style="{
                                    backgroundColor: label.color + '20',
                                    color: label.color,
                                    '--tw-ring-color': label.color,
                                }"
                                @click="toggleLabel(label.id)"
                            >
                                {{ label.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
