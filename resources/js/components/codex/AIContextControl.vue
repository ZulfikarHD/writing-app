<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps<{
    entryId: number;
    currentMode: string;
    readonly?: boolean;
}>();

const emit = defineEmits<{
    (e: 'updated', mode: string): void;
}>();

const contextModes = [
    { value: 'always', label: 'Always', description: 'Always include in AI context', icon: '🟢' },
    { value: 'detected', label: 'Detected', description: 'Include when mentioned in text', icon: '🔵' },
    { value: 'manual', label: 'Manual', description: 'Only when manually selected', icon: '🟡' },
    { value: 'never', label: 'Never', description: 'Never include in AI context', icon: '🔴' },
];

const selectedMode = ref(props.currentMode);
const loading = ref(false);
const error = ref<string | null>(null);

watch(
    () => props.currentMode,
    (newMode) => {
        selectedMode.value = newMode;
    },
);

const updateMode = async (mode: string) => {
    if (props.readonly || mode === selectedMode.value) return;

    loading.value = true;
    error.value = null;

    try {
        await axios.patch(`/api/codex/${props.entryId}`, {
            ai_context_mode: mode,
        });

        selectedMode.value = mode;
        emit('updated', mode);
    } catch {
        error.value = 'Failed to update AI context mode';
    } finally {
        loading.value = false;
    }
};

const getCurrentModeInfo = () => {
    return contextModes.find((m) => m.value === selectedMode.value) || contextModes[1];
};
</script>

<template>
    <div>
        <!-- Error Message -->
        <div v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
            {{ error }}
            <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="error = null">×</button>
        </div>

        <!-- Current Mode Display (for readonly or compact view) -->
        <div v-if="readonly" class="flex items-center gap-2 text-sm">
            <span>{{ getCurrentModeInfo().icon }}</span>
            <span class="font-medium text-zinc-900 dark:text-white">{{ getCurrentModeInfo().label }}</span>
            <span class="text-zinc-500 dark:text-zinc-400">- {{ getCurrentModeInfo().description }}</span>
        </div>

        <!-- Mode Selector -->
        <div v-else class="space-y-2">
            <button
                v-for="mode in contextModes"
                :key="mode.value"
                type="button"
                class="flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-all"
                :class="
                    selectedMode === mode.value
                        ? 'border-violet-500 bg-violet-50 dark:border-violet-500 dark:bg-violet-900/20'
                        : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'
                "
                :disabled="loading"
                @click="updateMode(mode.value)"
            >
                <span class="text-lg">{{ mode.icon }}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-zinc-900 dark:text-white">{{ mode.label }}</span>
                        <span
                            v-if="selectedMode === mode.value"
                            class="rounded-full bg-violet-100 px-1.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/50 dark:text-violet-300"
                        >
                            Active
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ mode.description }}</p>
                </div>
                <div v-if="loading && selectedMode === mode.value" class="animate-spin">
                    <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>
            </button>
        </div>

        <!-- Help Text -->
        <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
            This controls whether this entry's information is automatically included when generating AI content.
        </p>
    </div>
</template>
