<script setup lang="ts">
import Button from '@/components/ui/buttons/Button.vue';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

type ShowMode = 'entries' | 'pov' | 'labels' | 'custom';
type EntryType = 'all' | 'character' | 'location' | 'item' | 'lore' | 'organization' | 'subplot';

interface Label {
    id: number;
    name: string;
    color: string;
}

interface Scene {
    id: number;
    chapter_id: number;
    title: string | null;
    summary: string | null;
    position: number;
    status: string;
    word_count: number;
    pov_character_id: number | null;
    labels: Label[];
}

interface Chapter {
    id: number;
    title: string;
    position: number;
    scenes: Scene[];
}

interface CodexEntry {
    id: number;
    name: string;
    type: string;
}

interface MatrixCell {
    scene_id: number;
    entry_id: number;
    mention_count: number;
    source: string;
}

interface MatrixData {
    scenes: Array<{
        id: number;
        title: string;
        chapter_title: string;
        chapter_position: number;
        position: number;
        status: string;
        pov_character_id: number | null;
        labels: Label[];
    }>;
    entries: CodexEntry[];
    mentions: MatrixCell[];
    characters: Array<{ id: number; name: string }>;
}

const props = defineProps<{
    novelId: number;
    chapters?: Chapter[];
    labels?: Label[];
    isSearching: boolean;
    hasFilters?: boolean;
}>();

const emit = defineEmits<{
    (e: 'sceneClick', scene: Scene): void;
    (e: 'refresh'): void;
}>();

// State
const showMode = ref<ShowMode>('entries');
const entryType = ref<EntryType>('character');
const matrixData = ref<MatrixData | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);
const isUpdating = ref<number | null>(null); // Track which cell is being updated

// Fetch matrix data from API
const fetchMatrixData = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/novels/${props.novelId}/plan/matrix`, {
            params: {
                show: showMode.value,
                entry_type: showMode.value === 'entries' ? entryType.value : undefined,
            },
        });
        matrixData.value = response.data;
    } catch (err) {
        console.error('Failed to fetch matrix data:', err);
        error.value = 'Failed to load matrix data. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

// Watch for mode changes
watch([showMode, entryType], () => {
    fetchMatrixData();
});

onMounted(() => {
    fetchMatrixData();
});

// Get mention data for a cell
const getMention = (sceneId: number, entryId: number): MatrixCell | undefined => {
    return matrixData.value?.mentions.find((m) => m.scene_id === sceneId && m.entry_id === entryId);
};

// Check if scene has label
const sceneHasLabel = (sceneId: number, labelId: number) => {
    const scene = matrixData.value?.scenes.find((s) => s.id === sceneId);
    return scene?.labels.some((l) => l.id === labelId);
};

// Status colors
const statusColors: Record<string, string> = {
    draft: 'bg-zinc-300',
    in_progress: 'bg-amber-400',
    completed: 'bg-green-400',
    needs_revision: 'bg-red-400',
};

// Entry type options
const entryTypeOptions = [
    { value: 'all', label: 'All Entries' },
    { value: 'character', label: 'Characters' },
    { value: 'location', label: 'Locations' },
    { value: 'item', label: 'Items' },
    { value: 'lore', label: 'Lore' },
    { value: 'organization', label: 'Organizations' },
    { value: 'subplot', label: 'Subplots' },
];

// Show mode options
const showModeOptions = [
    { value: 'entries', label: 'Codex Entries' },
    { value: 'pov', label: 'POV' },
    { value: 'labels', label: 'Labels' },
];

// Computed columns based on mode
const columns = computed(() => {
    if (!matrixData.value) return [];

    if (showMode.value === 'entries') {
        return matrixData.value.entries.map((e) => ({ id: e.id, name: e.name, type: e.type }));
    } else if (showMode.value === 'pov') {
        return matrixData.value.characters.map((c) => ({ id: c.id, name: c.name, type: 'pov' }));
    } else if (showMode.value === 'labels') {
        // Use props.labels if provided, otherwise return empty
        const labelList = props.labels || [];
        return labelList.map((l) => ({ id: l.id, name: l.name, type: 'label', color: l.color }));
    }

    return [];
});

// Navigate to scene
const navigateToScene = (sceneId: number) => {
    const scene = matrixData.value?.scenes.find((s) => s.id === sceneId);
    if (scene) {
        emit('sceneClick', scene as unknown as Scene);
    }
};

// Set POV for a scene (click-to-assign in POV mode)
const setScenePov = async (sceneId: number, characterId: number) => {
    if (isUpdating.value) return;
    isUpdating.value = sceneId;

    try {
        await axios.patch(`/api/scenes/${sceneId}/pov`, {
            pov_character_id: characterId,
            pov_type: '3rd_limited', // Default POV type
        });

        // Update local data
        if (matrixData.value) {
            const scene = matrixData.value.scenes.find((s) => s.id === sceneId);
            if (scene) {
                scene.pov_character_id = characterId;
            }
        }
    } catch (err) {
        console.error('Failed to set POV:', err);
        error.value = 'Failed to update POV. Please try again.';
    } finally {
        isUpdating.value = null;
    }
};

// Toggle label for a scene (click-to-assign in Labels mode)
const toggleSceneLabel = async (sceneId: number, labelId: number) => {
    if (isUpdating.value) return;
    isUpdating.value = sceneId;

    try {
        const scene = matrixData.value?.scenes.find((s) => s.id === sceneId);
        if (!scene) return;

        const currentLabelIds = scene.labels.map((l) => l.id);
        let newLabelIds: number[];

        if (currentLabelIds.includes(labelId)) {
            // Remove label
            newLabelIds = currentLabelIds.filter((id) => id !== labelId);
        } else {
            // Add label
            newLabelIds = [...currentLabelIds, labelId];
        }

        await axios.post(`/api/scenes/${sceneId}/labels/sync`, {
            label_ids: newLabelIds,
        });

        // Refetch to update local data
        await fetchMatrixData();
    } catch (err) {
        console.error('Failed to toggle label:', err);
        error.value = 'Failed to update label. Please try again.';
    } finally {
        isUpdating.value = null;
    }
};

</script>

<template>
    <div class="space-y-4">
        <!-- Matrix Controls -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Show mode selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show:</label>
                <select
                    v-model="showMode"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option v-for="option in showModeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Entry type filter (only when showing entries) -->
            <div v-if="showMode === 'entries'" class="flex items-center gap-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Type:</label>
                <select
                    v-model="entryType"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option v-for="option in entryTypeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading || isSearching" class="py-12 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-violet-600 border-r-transparent" />
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Loading matrix data...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-center dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
            <Button variant="secondary" size="sm" class="mt-2" @click="fetchMatrixData">Try Again</Button>
        </div>

        <!-- Matrix Table -->
        <div
            v-else-if="matrixData && columns.length > 0"
            class="overflow-x-auto rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
        >
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <!-- Header -->
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <!-- Sticky first column header -->
                        <th
                            class="sticky left-0 z-10 min-w-[200px] bg-zinc-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400"
                        >
                            Scene
                        </th>
                        <!-- Entry columns -->
                        <th
                            v-for="col in columns"
                            :key="col.id"
                            class="min-w-[100px] px-3 py-3 text-center text-xs font-medium text-zinc-700 dark:text-zinc-300"
                            :title="col.name"
                        >
                            <div class="flex flex-col items-center gap-1">
                                <span
                                    v-if="'color' in col"
                                    class="h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: (col as { color: string }).color }"
                                />
                                <span class="line-clamp-2 max-w-[80px]">{{ col.name }}</span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    <tr
                        v-for="scene in matrixData.scenes"
                        :key="scene.id"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30"
                    >
                        <!-- Scene info (sticky) -->
                        <td
                            class="sticky left-0 z-10 cursor-pointer bg-white px-4 py-3 dark:bg-zinc-800"
                            @click="navigateToScene(scene.id)"
                        >
                            <div class="flex items-center gap-2">
                                <span :class="['h-2 w-2 shrink-0 rounded-full', statusColors[scene.status] || 'bg-zinc-300']" />
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ scene.title || 'Untitled' }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ scene.chapter_title }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Data cells -->
                        <td v-for="col in columns" :key="col.id" class="px-3 py-3 text-center">
                            <!-- Entries mode: show mention dots (read-only) -->
                            <template v-if="showMode === 'entries'">
                                <div
                                    v-if="getMention(scene.id, col.id)"
                                    class="flex items-center justify-center"
                                    :title="`${getMention(scene.id, col.id)?.mention_count} mentions`"
                                >
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-100 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"
                                    >
                                        {{ getMention(scene.id, col.id)?.mention_count }}
                                    </span>
                                </div>
                                <span v-else class="text-zinc-300 dark:text-zinc-600">—</span>
                            </template>

                            <!-- POV mode: click to set POV -->
                            <template v-else-if="showMode === 'pov'">
                                <button
                                    type="button"
                                    class="flex h-8 w-full items-center justify-center rounded transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    :class="{ 'animate-pulse': isUpdating === scene.id }"
                                    :disabled="isUpdating !== null"
                                    @click="setScenePov(scene.id, col.id)"
                                >
                                    <span
                                        v-if="scene.pov_character_id === col.id"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span
                                        v-else
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-xs text-zinc-400 opacity-0 transition-opacity hover:opacity-100 dark:text-zinc-500"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </span>
                                </button>
                            </template>

                            <!-- Labels mode: click to toggle label -->
                            <template v-else-if="showMode === 'labels'">
                                <button
                                    type="button"
                                    class="flex h-8 w-full items-center justify-center rounded transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    :class="{ 'animate-pulse': isUpdating === scene.id }"
                                    :disabled="isUpdating !== null"
                                    @click="toggleSceneLabel(scene.id, col.id)"
                                >
                                    <span
                                        v-if="sceneHasLabel(scene.id, col.id)"
                                        class="h-4 w-4 rounded-full"
                                        :style="{ backgroundColor: 'color' in col ? (col as { color: string }).color : '#8b5cf6' }"
                                    />
                                    <span
                                        v-else
                                        class="flex h-4 w-4 items-center justify-center rounded-full border-2 border-dashed opacity-30 transition-opacity hover:opacity-60"
                                        :style="{ borderColor: 'color' in col ? (col as { color: string }).color : '#8b5cf6' }"
                                    />
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-lg border-2 border-dashed border-zinc-200 py-12 text-center dark:border-zinc-700">
            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No data to display</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                <template v-if="showMode === 'entries'">Add codex entries and write scenes to see them in the matrix</template>
                <template v-else-if="showMode === 'pov'">Add characters to see POV assignments</template>
                <template v-else-if="showMode === 'labels'">Add labels to organize your scenes</template>
            </p>
            <Button :href="`/novels/${novelId}/workspace?mode=codex`" as="a" class="mt-4">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Open Codex
            </Button>
        </div>
    </div>
</template>
